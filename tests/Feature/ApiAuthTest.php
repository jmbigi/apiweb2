<?php

namespace Tests\Feature;

use App\Models\Ensemble;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class ApiAuthTest extends TestCase
{
    private User $user;

    private static array $tables = [
        'users', 'personal_access_tokens', 'composers',
        'subscription_plan', 'subscribed_user', 'premium_trials',
        'ensembles', 'ensemble_user', 'roles', 'permissions',
        'role_user', 'permission_user', 'permission_role',
    ];

    protected function setUp(): void
    {
        parent::setUp();

        if (! Schema::hasTable('users')) {
            $this->createAuthTables();
        } else {
            DB::statement('PRAGMA foreign_keys = OFF');
            foreach (self::$tables as $table) {
                DB::statement("DELETE FROM \"{$table}\"");
            }
            DB::statement('PRAGMA foreign_keys = ON');
        }

        $this->user = User::factory()->create([
            'email' => 'test@example.com',
            'password' => Hash::make('password123'),
            'status' => 1,
        ]);
    }

    private function createAuthTables(): void
    {
        Schema::create('users', function ($table) {
            $table->id();
            $table->string('name');
            $table->string('email')->unique();
            $table->timestamp('email_verified_at')->nullable();
            $table->string('password');
            $table->rememberToken();
            $table->boolean('status')->default(1);
            $table->softDeletes();
            $table->timestamps();
        });

        Schema::create('personal_access_tokens', function ($table) {
            $table->id();
            $table->morphs('tokenable');
            $table->string('name');
            $table->string('token', 64)->unique();
            $table->text('abilities')->nullable();
            $table->timestamp('last_used_at')->nullable();
            $table->timestamp('expires_at')->nullable();
            $table->timestamps();
        });

        Schema::create('composers', function ($table) {
            $table->id();
            $table->string('public_name');
            $table->foreignId('users_id')->nullable()->constrained('users')->nullOnDelete();
            $table->softDeletes();
            $table->timestamps();
        });

        Schema::create('subscription_plan', function ($table) {
            $table->id();
            $table->string('name');
            $table->text('description')->nullable();
            $table->integer('type')->default(0);
            $table->boolean('status')->default(true);
            $table->date('start_date')->nullable();
            $table->date('end_date')->nullable();
            $table->timestamps();
        });

        Schema::create('subscribed_user', function ($table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('subscription_plan_id')->nullable()->constrained('subscription_plan')->nullOnDelete();
            $table->timestamps();
        });

        Schema::create('premium_trials', function ($table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->integer('used_count')->default(0);
            $table->timestamps();
        });

        Schema::create('ensembles', function ($table) {
            $table->id();
            $table->string('name')->unique();
            $table->string('cif', 20)->unique();
            $table->text('description')->nullable();
            $table->foreignId('owner_id')->constrained('users');
            $table->boolean('status')->default(1);
            $table->softDeletes();
            $table->timestamps();
        });

        Schema::create('ensemble_user', function ($table) {
            $table->foreignId('ensemble_id')->constrained('ensembles')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->string('role')->default('usuario');
            $table->boolean('status')->default(1);
            $table->timestamps();
            $table->primary(['ensemble_id', 'user_id']);
        });

        Schema::create('roles', function ($table) {
            $table->id();
            $table->string('name')->unique();
            $table->string('display_name')->nullable();
            $table->string('description')->nullable();
            $table->timestamps();
        });

        Schema::create('permissions', function ($table) {
            $table->id();
            $table->string('name')->unique();
            $table->string('display_name')->nullable();
            $table->string('description')->nullable();
            $table->timestamps();
        });

        Schema::create('role_user', function ($table) {
            $table->unsignedBigInteger('role_id');
            $table->unsignedBigInteger('user_id');
            $table->string('user_type');
            $table->foreign('role_id')->references('id')->on('roles')->onUpdate('cascade')->onDelete('cascade');
            $table->primary(['user_id', 'role_id', 'user_type']);
        });

        Schema::create('permission_user', function ($table) {
            $table->unsignedBigInteger('permission_id');
            $table->unsignedBigInteger('user_id');
            $table->string('user_type');
            $table->foreign('permission_id')->references('id')->on('permissions')->onUpdate('cascade')->onDelete('cascade');
            $table->primary(['user_id', 'permission_id', 'user_type']);
        });

        Schema::create('permission_role', function ($table) {
            $table->unsignedBigInteger('permission_id');
            $table->unsignedBigInteger('role_id');
            $table->foreign('permission_id')->references('id')->on('permissions')->onUpdate('cascade')->onDelete('cascade');
            $table->foreign('role_id')->references('id')->on('roles')->onUpdate('cascade')->onDelete('cascade');
            $table->primary(['permission_id', 'role_id']);
        });
    }

    public function test_login_with_valid_credentials_returns_token(): void
    {
        $response = $this->postJson('/api/auth/login', [
            'email' => 'test@example.com',
            'password' => 'password123',
        ]);

        $response->assertStatus(200)
            ->assertJsonStructure([
                'status', 'message', 'token', 'user_id', 'user_name',
            ])
            ->assertJsonPath('status', true);
    }

    public function test_login_with_invalid_password_returns_401(): void
    {
        $response = $this->postJson('/api/auth/login', [
            'email' => 'test@example.com',
            'password' => 'wrongpassword',
        ]);

        $response->assertStatus(401)
            ->assertJsonPath('status', false);
    }

    public function test_login_with_missing_email_returns_401(): void
    {
        $response = $this->postJson('/api/auth/login', [
            'password' => 'password123',
        ]);

        $response->assertStatus(401)
            ->assertJsonPath('status', false);
    }

    public function test_login_with_cif_returns_ensemble_data(): void
    {
        $ensemble = Ensemble::factory()->create([
            'owner_id' => $this->user->id,
            'cif' => 'CIF12345',
        ]);
        $ensemble->members()->attach($this->user->id, ['role' => 'administrador']);

        $response = $this->postJson('/api/auth/login', [
            'email' => 'test@example.com',
            'password' => 'password123',
            'cif' => 'CIF12345',
        ]);

        $response->assertStatus(200)
            ->assertJsonPath('status', true)
            ->assertJsonStructure(['ensemble' => ['id', 'name', 'cif', 'role']])
            ->assertJsonPath('ensemble.cif', 'CIF12345')
            ->assertJsonPath('ensemble.role', 'administrador');
    }

    public function test_login_with_invalid_cif_returns_401(): void
    {
        $response = $this->postJson('/api/auth/login', [
            'email' => 'test@example.com',
            'password' => 'password123',
            'cif' => 'NONEXISTENT',
        ]);

        $response->assertStatus(401)
            ->assertJsonPath('status', false);
    }

    public function test_login_with_cif_non_member_returns_403(): void
    {
        $ensemble = Ensemble::factory()->create([
            'owner_id' => $this->user->id,
            'cif' => 'CIF99999',
        ]);

        $response = $this->postJson('/api/auth/login', [
            'email' => 'test@example.com',
            'password' => 'password123',
            'cif' => 'CIF99999',
        ]);

        $response->assertStatus(403)
            ->assertJsonPath('status', false);
    }

    public function test_refresh_token_creates_new_token(): void
    {
        $loginResponse = $this->postJson('/api/auth/login', [
            'email' => 'test@example.com',
            'password' => 'password123',
        ]);
        $token = $loginResponse->json('token');

        $response = $this->withHeaders([
            'Authorization' => "Bearer {$token}",
            'Accept' => 'application/json',
        ])->postJson('/api/auth/token/refresh');

        $response->assertStatus(200)
            ->assertJsonPath('status', true)
            ->assertJsonStructure(['token']);
        $this->assertNotEquals($token, $response->json('token'));
    }

    public function test_logout_deletes_tokens(): void
    {
        $token = $this->user->createToken('test')->plainTextToken;
        $this->assertDatabaseHas('personal_access_tokens', [
            'tokenable_id' => $this->user->id,
        ]);

        $this->withHeaders([
            'Authorization' => "Bearer {$token}",
            'Accept' => 'application/json',
        ])->postJson('/api/auth/logout')->assertStatus(200)->assertJsonPath('status', true);

        $this->assertDatabaseMissing('personal_access_tokens', [
            'tokenable_id' => $this->user->id,
        ]);
    }

    public function test_get_user_returns_user_data(): void
    {
        $loginResponse = $this->postJson('/api/auth/login', [
            'email' => 'test@example.com',
            'password' => 'password123',
        ]);
        $token = $loginResponse->json('token');

        $response = $this->withHeaders([
            'Authorization' => "Bearer {$token}",
            'Accept' => 'application/json',
        ])->getJson('/api/auth/user/get/1');

        $response->assertStatus(200);
    }

    public function test_full_auth_flow(): void
    {
        $token = $this->user->createToken('test')->plainTextToken;
        $headers = fn($t) => ['Authorization' => "Bearer {$t}", 'Accept' => 'application/json'];

        $this->withHeaders($headers($token))
            ->getJson('/api/auth/user/get/1')->assertStatus(200);

        $refresh = $this->withHeaders($headers($token))
            ->postJson('/api/auth/token/refresh');
        $refresh->assertStatus(200);
        $newToken = $refresh->json('token');
        $this->assertNotNull($newToken);
        $this->assertNotEquals($token, $newToken);

        $this->assertDatabaseMissing('personal_access_tokens', [
            'token' => hash('sha256', explode('|', $token)[1] ?? $token),
        ]);
        $this->assertDatabaseHas('personal_access_tokens', [
            'tokenable_id' => $this->user->id,
        ]);

        $this->withHeaders($headers($newToken))
            ->getJson('/api/auth/user/get/1')->assertStatus(200);

        $this->withHeaders($headers($newToken))
            ->postJson('/api/auth/logout')->assertStatus(200);

        $this->assertDatabaseMissing('personal_access_tokens', [
            'tokenable_id' => $this->user->id,
        ]);
    }

    public function test_unauthenticated_access_returns_401(): void
    {
        $this->getJson('/api/auth/user/get/1')->assertStatus(401);
        $this->postJson('/api/auth/logout')->assertStatus(401);
        $this->postJson('/api/auth/token/refresh')->assertStatus(401);
    }
}
