<?php

namespace Tests\Feature;

use App\Models\Composer;
use App\Models\ComposerRequest;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class ApiComposerTest extends TestCase
{
    private User $user;

    private static array $tables = [
        'users', 'personal_access_tokens', 'composers',
        'composer_request', 'composer_status', 'request_status',
        'roles', 'role_user', 'permissions', 'permission_role', 'permission_user',
    ];

    protected function setUp(): void
    {
        parent::setUp();

        if (! Schema::hasTable('users')) {
            $this->createComposerTables();
        } else {
            DB::statement('PRAGMA foreign_keys = OFF');
            foreach (self::$tables as $table) {
                DB::statement("DELETE FROM \"{$table}\"");
            }
            DB::statement('PRAGMA foreign_keys = ON');
        }

        $this->user = User::factory()->create(['email' => 'composer@test.com']);
    }

    private function createComposerTables(): void
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
            $table->string('name')->nullable();
            $table->string('surname')->nullable();
            $table->string('vat_number')->nullable();
            $table->string('street')->nullable();
            $table->string('city')->nullable();
            $table->string('postal_code')->nullable();
            $table->string('country')->nullable();
            $table->string('notification_email')->nullable();
            $table->string('telephone')->nullable();
            $table->foreignId('users_id')->nullable()->constrained('users')->nullOnDelete();
            $table->softDeletes();
            $table->timestamps();
        });

        Schema::create('composer_status', function ($table) {
            $table->id();
            $table->string('name');
            $table->timestamps();
        });

        Schema::create('request_status', function ($table) {
            $table->id();
            $table->string('name');
            $table->timestamps();
        });

        Schema::create('composer_request', function ($table) {
            $table->id();
            $table->foreignId('composers_id')->nullable()->constrained('composers')->nullOnDelete();
            $table->foreignId('composer_status_id')->nullable()->constrained('composer_status')->nullOnDelete();
            $table->foreignId('request_status_id')->nullable()->constrained('request_status')->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();
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

    public function test_public_list_returns_composers(): void
    {
        Composer::create(['public_name' => 'Mozart', 'users_id' => $this->user->id]);

        $response = $this->getJson('/api/composer/list');

        $response->assertStatus(200)
            ->assertJsonPath('status', true);
    }

    public function test_public_list_filtered_by_name(): void
    {
        Composer::create(['public_name' => 'Mozart', 'users_id' => $this->user->id]);
        Composer::create(['public_name' => 'Beethoven', 'users_id' => $this->user->id]);

        $response = $this->getJson('/api/composer/list?name=Mozart');

        $response->assertStatus(200)
            ->assertJsonPath('status', true);
    }

    public function test_public_show_composer(): void
    {
        $composer = Composer::create(['public_name' => 'Mozart', 'users_id' => $this->user->id]);

        $response = $this->getJson("/api/composer/{$composer->id}");

        $response->assertStatus(200)
            ->assertJsonPath('status', true)
            ->assertJsonPath('data.public_name', 'Mozart');
    }

    public function test_show_nonexistent_composer_returns_404(): void
    {
        $this->getJson('/api/composer/99999')
            ->assertStatus(404);
    }

    public function test_create_composer_requires_auth(): void
    {
        $this->postJson('/api/composer/create', [
            'name' => 'Wolfgang',
            'surname' => 'Mozart',
            'public_name' => 'Mozart',
            'vat_number' => 'VAT123',
            'street' => 'Street 1',
            'city' => 'Vienna',
            'postal_code' => '1010',
            'country' => 'Austria',
            'telephone' => '123456789',
        ])->assertStatus(401);
    }

    public function test_create_composer_successfully(): void
    {
        $token = $this->user->createToken('test')->plainTextToken;
        $headers = ['Authorization' => "Bearer {$token}", 'Accept' => 'application/json'];

        $response = $this->withHeaders($headers)->postJson('/api/composer/create', [
            'user_id' => $this->user->id,
            'name' => 'Wolfgang',
            'surname' => 'Mozart',
            'public_name' => 'Mozart',
            'vat_number' => 'VAT123',
            'street' => 'Street 1',
            'city' => 'Vienna',
            'postal_code' => '1010',
            'country' => 'Austria',
            'telephone' => '123456789',
        ]);

        $response->assertStatus(200)
            ->assertJsonPath('status', true)
            ->assertJsonPath('message', 'Composer Request Created');

        $this->assertDatabaseHas('composers', [
            'public_name' => 'Mozart',
            'vat_number' => 'VAT123',
        ]);
    }

    public function test_create_composer_validation_error(): void
    {
        $token = $this->user->createToken('test')->plainTextToken;
        $headers = ['Authorization' => "Bearer {$token}", 'Accept' => 'application/json'];

        $response = $this->withHeaders($headers)->postJson('/api/composer/create', [
            'user_id' => $this->user->id,
            'name' => 'Wolfgang',
        ]);

        $response->assertStatus(401)
            ->assertJsonPath('status', false)
            ->assertJsonPath('message', 'validation error');
    }

    public function test_create_composer_user_already_has_role(): void
    {
        $role = \DB::table('roles')->insertGetId([
            'name' => 'composer',
            'display_name' => 'Composer',
        ]);
        \DB::table('role_user')->insert([
            'role_id' => $role,
            'user_id' => $this->user->id,
            'user_type' => get_class($this->user),
        ]);

        $token = $this->user->createToken('test')->plainTextToken;
        $headers = ['Authorization' => "Bearer {$token}", 'Accept' => 'application/json'];

        $response = $this->withHeaders($headers)->postJson('/api/composer/create', [
            'user_id' => $this->user->id,
            'name' => 'Wolfgang',
            'surname' => 'Mozart',
            'public_name' => 'Mozart',
            'vat_number' => 'VAT999',
            'street' => 'Street 1',
            'city' => 'Vienna',
            'postal_code' => '1010',
            'country' => 'Austria',
            'telephone' => '123456789',
        ]);

        $response->assertStatus(401)
            ->assertJsonPath('status', false);
    }

    public function test_update_composer(): void
    {
        $composer = Composer::create([
            'public_name' => 'Old Name',
            'users_id' => $this->user->id,
            'name' => 'Old',
            'surname' => 'Name',
            'vat_number' => 'VAT111',
            'street' => 'Old St',
            'city' => 'Old City',
            'postal_code' => '0000',
            'country' => 'Old Country',
            'telephone' => '000000000',
        ]);

        $token = $this->user->createToken('test')->plainTextToken;
        $headers = ['Authorization' => "Bearer {$token}", 'Accept' => 'application/json'];

        $response = $this->withHeaders($headers)->postJson('/api/composer/update/' . $composer->id, [
            'id' => $composer->id,
            'user_id' => $this->user->id,
            'name' => 'Wolfgang',
            'surname' => 'Mozart',
            'public_name' => 'Mozart',
            'vat_number' => 'VAT111',
            'street' => 'Updated St',
            'city' => 'Vienna',
            'postal_code' => '1010',
            'country' => 'Austria',
            'notification_email' => 'mozart@test.com',
            'telephone' => '123456789',
        ]);

        $response->assertStatus(200)
            ->assertJsonPath('status', true)
            ->assertJsonPath('message', 'Composer Updated');

        $this->assertDatabaseHas('composers', [
            'id' => $composer->id,
            'public_name' => 'Mozart',
            'city' => 'Vienna',
        ]);
    }

    public function test_delete_composer(): void
    {
        $composer = Composer::create([
            'public_name' => 'Beethoven',
            'users_id' => $this->user->id,
        ]);

        $token = $this->user->createToken('test')->plainTextToken;
        $headers = ['Authorization' => "Bearer {$token}", 'Accept' => 'application/json'];

        $response = $this->withHeaders($headers)->deleteJson('/api/composer/delete/' . $composer->id);

        $response->assertStatus(200)
            ->assertJsonPath('status', true)
            ->assertJsonPath('message', 'Composer Deleted');

        $this->assertSoftDeleted('composers', ['id' => $composer->id]);
    }

    public function test_protected_endpoints_require_auth(): void
    {
        $this->postJson('/api/composer/update/1', ['id' => 1, 'user_id' => 1, 'name' => 'a', 'surname' => 'b', 'public_name' => 'c', 'vat_number' => 'd', 'street' => 'e', 'city' => 'f', 'postal_code' => 'g', 'country' => 'h', 'notification_email' => 'a@b.com', 'telephone' => '1'])->assertStatus(401);
        $this->deleteJson('/api/composer/delete/1')->assertStatus(401);
    }

    public function test_composer_request_list_returns_data(): void
    {
        $composer = Composer::create([
            'public_name' => 'Test Composer',
            'users_id' => $this->user->id,
        ]);
        $composerStatus = \App\Models\ComposerStatus::create(['name' => 'Pending']);
        $requestStatus = \App\Models\RequestStatus::create(['name' => 'Open']);

        ComposerRequest::create([
            'composers_id' => $composer->id,
            'composer_status_id' => $composerStatus->id,
            'request_status_id' => $requestStatus->id,
        ]);

        $response = $this->actingAs($this->user)
            ->getJson('/api/composer-request/list');

        $response->assertStatus(200)
            ->assertJsonPath('status', true)
            ->assertJsonCount(1, 'data');
    }

    public function test_composer_request_get_returns_single(): void
    {
        $composer = Composer::create([
            'public_name' => 'Single Composer',
            'users_id' => $this->user->id,
        ]);
        $composerStatus = \App\Models\ComposerStatus::create(['name' => 'Approved']);
        $requestStatus = \App\Models\RequestStatus::create(['name' => 'Closed']);

        $request = ComposerRequest::create([
            'composers_id' => $composer->id,
            'composer_status_id' => $composerStatus->id,
            'request_status_id' => $requestStatus->id,
        ]);

        $response = $this->actingAs($this->user)
            ->getJson("/api/composer-request/get/{$request->id}");

        $response->assertStatus(200)
            ->assertJsonPath('status', true)
            ->assertJsonPath('data.id', $request->id);
    }

    public function test_composer_request_requires_auth(): void
    {
        $this->getJson('/api/composer-request/list')->assertStatus(401);
        $this->getJson('/api/composer-request/get/1')->assertStatus(401);
        $this->deleteJson('/api/composer-request/delete/1')->assertStatus(401);
        $this->postJson('/api/composer-request/update-status/1', [])->assertStatus(401);
    }

    public function test_composer_request_delete_soft_deletes(): void
    {
        $composer = Composer::create(['public_name' => 'Del', 'users_id' => $this->user->id]);
        $cs = \App\Models\ComposerStatus::create(['name' => 'P']);
        $rs = \App\Models\RequestStatus::create(['name' => 'O']);
        $request = ComposerRequest::create([
            'composers_id' => $composer->id,
            'composer_status_id' => $cs->id,
            'request_status_id' => $rs->id,
        ]);

        $response = $this->actingAs($this->user)
            ->deleteJson("/api/composer-request/delete/{$request->id}");

        $response->assertStatus(200)
            ->assertJsonPath('status', true)
            ->assertJsonPath('message', 'Composer Request Deleted');

        $this->assertSoftDeleted('composer_request', ['id' => $request->id]);
    }

    public function test_composer_request_update_status(): void
    {
        \App\Models\Role::create(['name' => 'composer', 'display_name' => 'Composer']);
        $composer = Composer::create(['public_name' => 'Upd', 'users_id' => $this->user->id]);
        $cs1 = \App\Models\ComposerStatus::create(['name' => 'Initial']);
        $cs2 = \App\Models\ComposerStatus::create(['name' => 'Approved']);
        $rs = \App\Models\RequestStatus::create(['name' => 'Open']);
        $request = ComposerRequest::create([
            'composers_id' => $composer->id,
            'composer_status_id' => $cs1->id,
            'request_status_id' => $rs->id,
        ]);

        $response = $this->actingAs($this->user)
            ->postJson("/api/composer-request/update-status/{$request->id}", [
                'composer_status_id' => $cs2->id,
            ]);

        $response->assertStatus(200)
            ->assertJsonPath('status', true)
            ->assertJsonPath('message', 'Composer Status Updated');

        $this->assertDatabaseHas('composer_request', [
            'id' => $request->id,
            'composer_status_id' => $cs2->id,
        ]);
    }
}
