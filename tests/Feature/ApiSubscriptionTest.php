<?php

namespace Tests\Feature;

use App\Models\SubscriptionPlan;
use App\Models\User;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class ApiSubscriptionTest extends TestCase
{
    private User $user;

    private static array $tables = [
        'users', 'personal_access_tokens',
        'subscription_plan', 'subscribed_user', 'premium_trials',
        'ensembles', 'ensemble_user',
    ];

    protected function setUp(): void
    {
        parent::setUp();

        if (! Schema::hasTable('users')) {
            $this->createSubscriptionTables();
        } else {
            DB::statement('PRAGMA foreign_keys = OFF');
            foreach (self::$tables as $table) {
                DB::statement("DELETE FROM \"{$table}\"");
            }
            DB::statement('PRAGMA foreign_keys = ON');
        }

        $this->user = User::factory()->create(['email' => 'subs@test.com']);

        putenv('PAYPAL_SANDBOX_CLIENT_ID=dummy');
        putenv('PAYPAL_SANDBOX_CLIENT_SECRET=dummy');
        config(['paypal.sandbox.client_id' => 'dummy', 'paypal.sandbox.client_secret' => 'dummy']);
    }

    private function createSubscriptionTables(): void
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

        Schema::create('subscription_plan', function ($table) {
            $table->id();
            $table->string('name');
            $table->text('description')->nullable();
            $table->decimal('price', 8, 2)->default(0);
            $table->integer('type')->default(0);
            $table->boolean('status')->default(true);
            $table->date('start_date')->nullable();
            $table->date('end_date')->nullable();
            $table->string('plan_id')->nullable();
            $table->string('type_label')->nullable();
            $table->timestamps();
        });

        Schema::create('subscribed_user', function ($table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('subscription_plan_id')->nullable()->constrained('subscription_plan')->nullOnDelete();
            $table->string('paypal_subscription_id')->nullable();
            $table->timestamp('subscription_end_date')->nullable();
            $table->string('paypal_plan_id')->nullable();
            $table->timestamps();
        });

        Schema::create('premium_trials', function ($table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
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
    }

    private function authHeaders(): array
    {
        $token = $this->user->createToken('test')->plainTextToken;
        return ['Authorization' => "Bearer {$token}", 'Accept' => 'application/json'];
    }

    public function test_subscription_plans_list_requires_auth(): void
    {
        $this->getJson('/api/subscription/subscription-plans')
            ->assertStatus(401);
    }

    public function test_subscription_plans_list_returns_plans(): void
    {
        SubscriptionPlan::create([
            'name' => 'Free',
            'type' => 0,
            'price' => 0,
            'status' => 1,
            'start_date' => now(),
        ]);
        SubscriptionPlan::create([
            'name' => 'Premium',
            'type' => 2,
            'price' => 9.99,
            'status' => 1,
            'start_date' => now(),
        ]);

        $response = $this->withHeaders($this->authHeaders())
            ->getJson('/api/subscription/subscription-plans');

        $response->assertStatus(200)
            ->assertJsonPath('status', true)
            ->assertJsonPath('message', 'Subscription Plans');
    }

    public function test_subscribed_user_requires_auth(): void
    {
        $this->postJson('/api/subscription/subscribed-user', [
            'user_id' => 1,
            'subscription_plan_id' => 1,
        ])->assertStatus(401);
    }

    public function test_subscribe_user_to_plan(): void
    {
        $plan = SubscriptionPlan::create([
            'name' => 'Basic',
            'type' => 1,
            'price' => 4.99,
            'status' => 1,
            'start_date' => now(),
        ]);

        $response = $this->withHeaders($this->authHeaders())
            ->postJson('/api/subscription/subscribed-user', [
                'user_id' => $this->user->id,
                'subscription_plan_id' => $plan->id,
            ]);

        $response->assertStatus(200)
            ->assertJsonPath('status', true)
            ->assertJsonPath('message', 'User Subscribed To Plan');

        $this->assertDatabaseHas('subscribed_user', [
            'user_id' => $this->user->id,
            'subscription_plan_id' => $plan->id,
        ]);
    }

    public function test_subscribe_user_already_subscribed(): void
    {
        $plan = SubscriptionPlan::create([
            'name' => 'Basic',
            'type' => 1,
            'price' => 4.99,
            'status' => 1,
            'start_date' => now(),
        ]);

        $this->withHeaders($this->authHeaders())
            ->postJson('/api/subscription/subscribed-user', [
                'user_id' => $this->user->id,
                'subscription_plan_id' => $plan->id,
            ]);

        $response = $this->withHeaders($this->authHeaders())
            ->postJson('/api/subscription/subscribed-user', [
                'user_id' => $this->user->id,
                'subscription_plan_id' => $plan->id,
            ]);

        $response->assertStatus(200);
    }

    public function test_sync_subscribe_requires_auth(): void
    {
        $this->postJson('/api/inapp-subscription/sync-subscribe', ['type' => 1])
            ->assertStatus(401);
    }

    public function test_sync_subscribe_without_type_returns_400(): void
    {
        $response = $this->withHeaders($this->authHeaders())
            ->postJson('/api/inapp-subscription/sync-subscribe', []);

        $response->assertStatus(400)
            ->assertJsonPath('status', false)
            ->assertJsonPath('message', 'The plan type cannot be null.');
    }

    public function test_sync_subscribe_no_plan_found(): void
    {
        $response = $this->withHeaders($this->authHeaders())
            ->postJson('/api/inapp-subscription/sync-subscribe', ['type' => 1]);

        $response->assertStatus(404)
            ->assertJsonPath('status', false)
            ->assertJsonPath('message', 'No subscription plan found for the provided type.');
    }

    public function test_sync_subscribe_success(): void
    {
        SubscriptionPlan::create([
            'name' => 'Basic',
            'type' => 1,
            'price' => 4.99,
            'status' => 1,
            'start_date' => now(),
        ]);

        $response = $this->withHeaders($this->authHeaders())
            ->postJson('/api/inapp-subscription/sync-subscribe', ['type' => 1]);

        $response->assertStatus(200)
            ->assertJsonPath('status', true)
            ->assertJsonPath('message', 'User subscribed to the plan.')
            ->assertJsonStructure(['data' => ['subscription_name', 'is_paid', 'is_advertisement', 'is_favourite', 'annotation_limit', 'level', 'is_paypal', 'expiration_datetime', 'expired', 'now', 'next_month', 'candidate_premium_trial', 'is_ensemble_member']]);

        $this->assertDatabaseHas('subscribed_user', [
            'user_id' => $this->user->id,
        ]);
    }

    public function test_free_plan_subscription(): void
    {
        SubscriptionPlan::create([
            'name' => 'Free',
            'type' => 0,
            'price' => 0,
            'status' => 1,
            'start_date' => now(),
        ]);

        $response = $this->withHeaders($this->authHeaders())
            ->postJson('/api/inapp-subscription/sync-subscribe', ['type' => 0]);

        $response->assertStatus(200)
            ->assertJsonPath('status', true);
    }

    public function test_apply_premium_trial_requires_auth(): void
    {
        $this->postJson('/api/inapp-subscription/apply-premium-trial')
            ->assertStatus(401);
    }

    public function test_apply_premium_trial_no_plan(): void
    {
        $response = $this->withHeaders($this->authHeaders())
            ->postJson('/api/inapp-subscription/apply-premium-trial');

        $response->assertStatus(404)
            ->assertJsonPath('status', false);
    }

    public function test_apply_premium_trial_success(): void
    {
        SubscriptionPlan::create([
            'name' => 'Premium',
            'type' => 2,
            'price' => 9.99,
            'status' => 1,
            'start_date' => now(),
        ]);

        $response = $this->withHeaders($this->authHeaders())
            ->postJson('/api/inapp-subscription/apply-premium-trial');

        $response->assertStatus(200)
            ->assertJsonPath('status', true)
            ->assertJsonPath('message', 'User subscribed to the plan.')
            ->assertJsonStructure(['data' => ['subscription_name', 'is_paid', 'is_advertisement', 'is_favourite', 'annotation_limit', 'level', 'is_paypal', 'expiration_datetime', 'expired', 'now', 'next_month', 'candidate_premium_trial', 'is_ensemble_member']]);

        $this->assertDatabaseHas('premium_trials', [
            'user_id' => $this->user->id,
            'used_count' => 1,
        ]);

        $this->assertDatabaseHas('subscribed_user', [
            'user_id' => $this->user->id,
        ]);
    }

    public function test_apply_premium_trial_twice_fails(): void
    {
        SubscriptionPlan::create([
            'name' => 'Premium',
            'type' => 2,
            'price' => 9.99,
            'status' => 1,
            'start_date' => now(),
        ]);

        $this->withHeaders($this->authHeaders())
            ->postJson('/api/inapp-subscription/apply-premium-trial')
            ->assertStatus(200);

        $this->app['auth']->forgetGuards();

        $response = $this->withHeaders($this->authHeaders())
            ->postJson('/api/inapp-subscription/apply-premium-trial');

        $response->assertStatus(404)
            ->assertJsonPath('status', false);
    }
}
