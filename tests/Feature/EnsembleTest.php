<?php

namespace Tests\Feature;

use App\Models\Ensemble;
use App\Models\EnsembleFolder;
use App\Models\MusicScore;
use App\Models\Rehearsal;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class EnsembleTest extends TestCase
{
    private User $user;

    private User $otherUser;

    private static array $tables = [
        'roles', 'users', 'categories', 'composers',
        'ensembles', 'ensemble_user', 'ensemble_folders',
        'rehearsals', 'model_has_roles', 'music_scores',
    ];

    protected function setUp(): void
    {
        parent::setUp();

        if (! Schema::hasTable('users')) {
            $this->createEnsembleTables();
        } else {
            // Clean all test tables before each test
            DB::statement('PRAGMA foreign_keys = OFF');
            foreach (self::$tables as $table) {
                DB::statement("DELETE FROM \"{$table}\"");
            }
            DB::statement('PRAGMA foreign_keys = ON');
        }

        $this->user = User::factory()->create(['email' => 'test@example.com']);
        $this->otherUser = User::factory()->create(['email' => 'other@example.com']);
    }

    private function createEnsembleTables(): void
    {
        Schema::create('roles', function ($table) {
            $table->id();
            $table->string('name');
            $table->timestamps();
        });

        Schema::create('users', function ($table) {
            $table->id();
            $table->string('name');
            $table->string('email')->unique();
            $table->timestamp('email_verified_at')->nullable();
            $table->string('password');
            $table->rememberToken();
            $table->softDeletes();
            $table->timestamps();
        });

        Schema::create('categories', function ($table) {
            $table->id();
            $table->string('name');
            $table->timestamps();
        });

        Schema::create('ensembles', function ($table) {
            $table->id();
            $table->string('name')->unique();
            $table->string('cif', 20)->unique();
            $table->text('description')->nullable();
            $table->foreignId('owner_id')->constrained('users');
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

        Schema::create('ensemble_folders', function ($table) {
            $table->id();
            $table->foreignId('ensemble_id')->constrained('ensembles')->cascadeOnDelete();
            $table->string('name');
            $table->string('path')->default('/');
            $table->foreignId('parent_id')->nullable()->constrained('ensemble_folders')->cascadeOnDelete();
            $table->timestamps();
        });

        Schema::create('rehearsals', function ($table) {
            $table->id();
            $table->foreignId('ensemble_id')->constrained('ensembles')->cascadeOnDelete();
            $table->string('title');
            $table->date('date');
            $table->time('time')->nullable();
            $table->string('location')->nullable();
            $table->foreignId('instructor_id')->nullable()->constrained('users')->nullOnDelete();
            $table->text('notes')->nullable();
            $table->timestamps();
        });

        Schema::create('model_has_roles', function ($table) {
            $table->morphs('model');
            $table->foreignId('role_id')->constrained('roles');
            $table->primary(['role_id', 'model_id', 'model_type']);
        });

        Schema::create('composers', function ($table) {
            $table->id();
            $table->string('name');
            $table->softDeletes();
            $table->timestamps();
        });

        Schema::create('files_s3_s', function ($table) {
            $table->id();
            $table->morphs('fileable');
            $table->string('path');
            $table->string('storagePlace')->default('Wasabi');
            $table->string('extension')->nullable();
            $table->timestamps();
        });

        Schema::create('fk_music_score_composer', function ($table) {
            $table->id();
            $table->foreignId('music_scores_id')->constrained()->nullOnDelete();
            $table->foreignId('composers_id')->constrained()->nullOnDelete();
            $table->timestamps();
        });

        Schema::create('music_scores', function ($table) {
            $table->id();
            $table->string('name')->unique();
            $table->text('description')->nullable();
            $table->unsignedBigInteger('owner_id');
            $table->foreign('owner_id')->references('id')->on('users')->cascadeOnDelete();
            $table->boolean('public')->default(true);
            $table->foreignId('category_id')->nullable()->constrained();
            $table->foreignId('composer_id')->nullable()->constrained();
            $table->foreignId('ensemble_id')->nullable()->constrained('ensembles')->nullOnDelete();
            $table->foreignId('uploaded_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('ensemble_folder_id')->nullable()->constrained('ensemble_folders')->nullOnDelete();
            $table->date('date')->nullable();
            $table->timestamps();
        });
    }

    public function test_unauthenticated_access_returns_401()
    {
        $response = $this->getJson('/api/ensembles');
        $response->assertStatus(401);
    }

    public function test_create_ensemble()
    {
        $response = $this->actingAs($this->user)->postJson('/api/ensembles', [
            'name' => 'Test Ensemble',
            'cif' => 'CIF12345',
            'description' => 'A test ensemble',
        ]);

        $response->assertStatus(201)->assertJson(['status' => true]);
    }

    public function test_create_ensemble_duplicate_name()
    {
        Ensemble::factory()->create(['name' => 'Test Ensemble']);

        $response = $this->actingAs($this->user)->postJson('/api/ensembles', [
            'name' => 'Test Ensemble',
            'cif' => 'DIFFERENT',
        ]);

        $response->assertStatus(422);
    }

    public function test_create_ensemble_duplicate_cif()
    {
        Ensemble::factory()->create(['cif' => 'CIF12345']);

        $response = $this->actingAs($this->user)->postJson('/api/ensembles', [
            'name' => 'Different Name',
            'cif' => 'CIF12345',
        ]);

        $response->assertStatus(422);
    }

    public function test_list_ensembles()
    {
        Ensemble::factory()->count(3)->create();

        $response = $this->actingAs($this->user)->getJson('/api/ensembles');

        $response->assertStatus(200)->assertJsonCount(3, 'data');
    }

    public function test_show_ensemble()
    {
        $ensemble = Ensemble::factory()->create(['owner_id' => $this->user->id]);
        $ensemble->members()->attach($this->user->id, ['role' => 'administrador']);

        $response = $this->actingAs($this->user)->getJson("/api/ensembles/{$ensemble->id}");

        $response->assertStatus(200)->assertJsonPath('data.name', $ensemble->name);
    }

    public function test_update_ensemble()
    {
        $ensemble = Ensemble::factory()->create(['owner_id' => $this->user->id]);

        $response = $this->actingAs($this->user)->putJson("/api/ensembles/{$ensemble->id}", [
            'name' => 'Updated Name',
        ]);

        $response->assertStatus(200);
    }

    public function test_non_owner_can_update_ensemble()
    {
        $ensemble = Ensemble::factory()->create(['owner_id' => $this->otherUser->id]);

        $response = $this->actingAs($this->user)->putJson("/api/ensembles/{$ensemble->id}", [
            'name' => 'Updated Name',
        ]);

        $response->assertStatus(200);
    }

    public function test_delete_ensemble()
    {
        $ensemble = Ensemble::factory()->create(['owner_id' => $this->user->id]);

        $response = $this->actingAs($this->user)->deleteJson("/api/ensembles/{$ensemble->id}");

        $response->assertStatus(200);
    }

    public function test_add_member()
    {
        $ensemble = Ensemble::factory()->create(['owner_id' => $this->user->id]);

        $response = $this->actingAs($this->user)->postJson("/api/ensembles/{$ensemble->id}/members", [
            'user_id' => $this->otherUser->id,
            'role' => 'maestro',
        ]);

        $response->assertStatus(201);
    }

    public function test_add_member_nonexistent_user()
    {
        $ensemble = Ensemble::factory()->create(['owner_id' => $this->user->id]);

        $response = $this->actingAs($this->user)->postJson("/api/ensembles/{$ensemble->id}/members", [
            'user_id' => 9999,
            'role' => 'maestro',
        ]);

        $response->assertStatus(422);
    }

    public function test_update_member_role()
    {
        $ensemble = Ensemble::factory()->create(['owner_id' => $this->user->id]);
        $ensemble->members()->attach($this->otherUser->id, ['role' => 'usuario']);

        $response = $this->actingAs($this->user)->putJson(
            "/api/ensembles/{$ensemble->id}/members/{$this->otherUser->id}",
            ['role' => 'archivero']
        );

        $response->assertStatus(200);
    }

    public function test_remove_member()
    {
        $ensemble = Ensemble::factory()->create(['owner_id' => $this->user->id]);
        $ensemble->members()->attach($this->otherUser->id, ['role' => 'usuario']);

        $response = $this->actingAs($this->user)->deleteJson(
            "/api/ensembles/{$ensemble->id}/members/{$this->otherUser->id}"
        );

        $response->assertStatus(200);
    }

    public function test_my_ensembles()
    {
        $ensemble = Ensemble::factory()->create(['owner_id' => $this->user->id]);
        $ensemble->members()->attach($this->user->id, ['role' => 'administrador']);

        $response = $this->actingAs($this->user)->getJson('/api/my-ensembles');

        $response->assertStatus(200)->assertJsonCount(1, 'data');
    }

    public function test_ensemble_status()
    {
        $response = $this->actingAs($this->user)->getJson('/api/user/ensemble-status');

        $response->assertStatus(200)->assertJsonStructure(['status', 'data' => ['is_ensemble_member']]);
    }

    public function test_create_folder()
    {
        $ensemble = Ensemble::factory()->create(['owner_id' => $this->user->id]);

        $response = $this->actingAs($this->user)->postJson(
            "/api/ensembles/{$ensemble->id}/folders",
            ['name' => 'Fantasía']
        );

        $response->assertStatus(201);
    }

    public function test_create_folder_nested()
    {
        $ensemble = Ensemble::factory()->create(['owner_id' => $this->user->id]);
        $parent = EnsembleFolder::factory()->create(['ensemble_id' => $ensemble->id]);

        $response = $this->actingAs($this->user)->postJson(
            "/api/ensembles/{$ensemble->id}/folders",
            ['name' => 'Subfolder', 'parent_id' => $parent->id]
        );

        $response->assertStatus(201);
    }

    public function test_list_folders()
    {
        $ensemble = Ensemble::factory()->create(['owner_id' => $this->user->id]);
        EnsembleFolder::factory()->count(3)->create(['ensemble_id' => $ensemble->id]);

        $response = $this->actingAs($this->user)->getJson(
            "/api/ensembles/{$ensemble->id}/folders"
        );

        $response->assertStatus(200)->assertJsonCount(3, 'data');
    }

    public function test_update_folder()
    {
        $ensemble = Ensemble::factory()->create(['owner_id' => $this->user->id]);
        $folder = EnsembleFolder::factory()->create(['ensemble_id' => $ensemble->id, 'name' => 'Old Name']);

        $response = $this->actingAs($this->user)->putJson(
            "/api/ensembles/folders/{$folder->id}",
            ['name' => 'New Name']
        );

        $response->assertStatus(200);
    }

    public function test_delete_folder()
    {
        $ensemble = Ensemble::factory()->create(['owner_id' => $this->user->id]);
        $folder = EnsembleFolder::factory()->create(['ensemble_id' => $ensemble->id]);

        $response = $this->actingAs($this->user)->deleteJson(
            "/api/ensembles/folders/{$folder->id}"
        );

        $response->assertStatus(200);
    }

    public function test_create_rehearsal()
    {
        $ensemble = Ensemble::factory()->create(['owner_id' => $this->user->id]);

        $response = $this->actingAs($this->user)->postJson(
            "/api/ensembles/{$ensemble->id}/rehearsals",
            [
                'title' => 'Ensayo General',
                'date' => '2026-07-20',
                'time' => '18:00',
                'location' => 'Sala 1',
            ]
        );

        $response->assertStatus(201);
    }

    public function test_list_rehearsals()
    {
        $ensemble = Ensemble::factory()->create(['owner_id' => $this->user->id]);
        Rehearsal::factory()->count(2)->create(['ensemble_id' => $ensemble->id]);

        $response = $this->actingAs($this->user)->getJson(
            "/api/ensembles/{$ensemble->id}/rehearsals"
        );

        $response->assertStatus(200)->assertJsonCount(2, 'data');
    }

    public function test_update_rehearsal()
    {
        $ensemble = Ensemble::factory()->create(['owner_id' => $this->user->id]);
        $rehearsal = Rehearsal::factory()->create(['ensemble_id' => $ensemble->id, 'title' => 'Original']);

        $response = $this->actingAs($this->user)->putJson(
            "/api/ensembles/rehearsals/{$rehearsal->id}",
            ['title' => 'Updated Title']
        );

        $response->assertStatus(200);
    }

    public function test_delete_rehearsal()
    {
        $ensemble = Ensemble::factory()->create(['owner_id' => $this->user->id]);
        $rehearsal = Rehearsal::factory()->create(['ensemble_id' => $ensemble->id]);

        $response = $this->actingAs($this->user)->deleteJson(
            "/api/ensembles/rehearsals/{$rehearsal->id}"
        );

        $response->assertStatus(200);
    }

    public function test_lookup_user_by_email()
    {
        $response = $this->actingAs($this->user)->getJson(
            '/api/users/lookup?email='.$this->otherUser->email
        );

        $response->assertStatus(200)->assertJsonPath('data.email', $this->otherUser->email);
    }

    public function test_lookup_user_not_found()
    {
        $response = $this->actingAs($this->user)->getJson(
            '/api/users/lookup?email=nonexistent@example.com'
        );

        $response->assertStatus(422);
    }

    public function test_scope_public_or_accessible_filters_correctly()
    {
        $ensemble = Ensemble::factory()->create(['owner_id' => $this->user->id]);
        $ensemble->members()->attach($this->user->id, ['role' => 'administrador']);

        MusicScore::factory()->create(['name' => 'No Ensemble', 'ensemble_id' => null, 'public' => true, 'owner_id' => $this->user->id]);
        MusicScore::factory()->create(['name' => 'Ensemble Public', 'ensemble_id' => $ensemble->id, 'public' => true, 'owner_id' => $this->user->id]);
        MusicScore::factory()->create(['name' => 'Ensemble Private', 'ensemble_id' => $ensemble->id, 'public' => false, 'owner_id' => $this->user->id]);
        MusicScore::factory()->create(['name' => 'Other Private', 'ensemble_id' => $ensemble->id, 'public' => false, 'owner_id' => $this->user->id]);

        // As a member: should see all 4 (null + ensemble ones)
        $results = \App\Models\MusicScore::publicOrAccessible($this->user)->get();
        $this->assertCount(4, $results);

        // As a non-member: should only see the one with null ensemble_id
        $results = \App\Models\MusicScore::publicOrAccessible($this->otherUser)->get();
        $this->assertCount(1, $results);
        $this->assertEquals('No Ensemble', $results->first()->name);
    }

    public function test_ensemble_scores_visible_to_member()
    {
        $ensemble = Ensemble::factory()->create(['owner_id' => $this->user->id]);
        $ensemble->members()->attach($this->user->id, ['role' => 'administrador']);

        MusicScore::factory()->count(3)->create([
            'ensemble_id' => $ensemble->id,
            'owner_id' => $this->user->id,
        ]);

        $response = $this->actingAs($this->user)->getJson("/api/ensembles/{$ensemble->id}/scores");

        $response->assertStatus(200)->assertJsonCount(3, 'data');
    }

    public function test_ensemble_scores_not_accessible_without_auth()
    {
        $ensemble = Ensemble::factory()->create();

        $response = $this->getJson("/api/ensembles/{$ensemble->id}/scores");

        $response->assertStatus(401);
    }

    public function test_ensemble_status_member_returns_true()
    {
        $ensemble = Ensemble::factory()->create(['owner_id' => $this->user->id]);
        $ensemble->members()->attach($this->user->id, ['role' => 'usuario', 'status' => true]);

        $response = $this->actingAs($this->user)->getJson('/api/user/ensemble-status');

        $response->assertStatus(200);
        $this->assertTrue($response->json('data.is_ensemble_member'));
    }

    public function test_ensemble_status_non_member_returns_false()
    {
        $response = $this->actingAs($this->user)->getJson('/api/user/ensemble-status');

        $response->assertStatus(200);
        $this->assertFalse($response->json('data.is_ensemble_member'));
    }

    // --- Edge cases ---

    public function test_create_ensemble_missing_name()
    {
        $response = $this->actingAs($this->user)->postJson('/api/ensembles', [
            'cif' => 'CIF12345',
        ]);

        $response->assertStatus(422)->assertJson(['status' => false]);
    }

    public function test_create_ensemble_missing_cif()
    {
        $response = $this->actingAs($this->user)->postJson('/api/ensembles', [
            'name' => 'Test Ensemble',
        ]);

        $response->assertStatus(422)->assertJson(['status' => false]);
    }

    public function test_create_ensemble_empty_body()
    {
        $response = $this->actingAs($this->user)->postJson('/api/ensembles', []);

        $response->assertStatus(422)->assertJson(['status' => false]);
    }

    public function test_create_ensemble_name_too_long()
    {
        $response = $this->actingAs($this->user)->postJson('/api/ensembles', [
            'name' => str_repeat('a', 256),
            'cif' => 'CIF12345',
        ]);

        $response->assertStatus(422)->assertJson(['status' => false]);
    }

    public function test_show_nonexistent_ensemble()
    {
        $response = $this->actingAs($this->user)->getJson('/api/ensembles/99999');

        $response->assertStatus(404);
    }

    public function test_update_nonexistent_ensemble()
    {
        $response = $this->actingAs($this->user)->putJson('/api/ensembles/99999', [
            'name' => 'Updated',
        ]);

        $response->assertStatus(404);
    }

    public function test_delete_nonexistent_ensemble()
    {
        $response = $this->actingAs($this->user)->deleteJson('/api/ensembles/99999');

        $response->assertStatus(404);
    }

    public function test_update_ensemble_duplicate_name()
    {
        $ensemble1 = Ensemble::factory()->create(['owner_id' => $this->user->id]);
        $ensemble2 = Ensemble::factory()->create(['owner_id' => $this->user->id, 'name' => 'Other Ensemble']);

        $response = $this->actingAs($this->user)->putJson("/api/ensembles/{$ensemble2->id}", [
            'name' => $ensemble1->name,
        ]);

        $response->assertStatus(422)->assertJson(['status' => false]);
    }

    public function test_add_member_already_member()
    {
        $ensemble = Ensemble::factory()->create(['owner_id' => $this->user->id]);
        $ensemble->members()->attach($this->otherUser->id, ['role' => 'usuario']);

        $response = $this->actingAs($this->user)->postJson("/api/ensembles/{$ensemble->id}/members", [
            'user_id' => $this->otherUser->id,
            'role' => 'maestro',
        ]);

        $response->assertStatus(409);
    }

    public function test_add_member_invalid_role()
    {
        $ensemble = Ensemble::factory()->create(['owner_id' => $this->user->id]);

        $response = $this->actingAs($this->user)->postJson("/api/ensembles/{$ensemble->id}/members", [
            'user_id' => $this->otherUser->id,
            'role' => 'invalid_role',
        ]);

        $response->assertStatus(422)->assertJson(['status' => false]);
    }

    public function test_update_member_nonexistent_user()
    {
        $ensemble = Ensemble::factory()->create(['owner_id' => $this->user->id]);

        $response = $this->actingAs($this->user)->putJson(
            "/api/ensembles/{$ensemble->id}/members/99999",
            ['role' => 'archivero']
        );

        $response->assertStatus(404);
    }

    public function test_create_folder_missing_name()
    {
        $ensemble = Ensemble::factory()->create(['owner_id' => $this->user->id]);

        $response = $this->actingAs($this->user)->postJson(
            "/api/ensembles/{$ensemble->id}/folders",
            []
        );

        $response->assertStatus(422)->assertJson(['status' => false]);
    }

    public function test_create_folder_invalid_parent()
    {
        $ensemble = Ensemble::factory()->create(['owner_id' => $this->user->id]);

        $response = $this->actingAs($this->user)->postJson(
            "/api/ensembles/{$ensemble->id}/folders",
            ['name' => 'Folder', 'parent_id' => 99999]
        );

        $response->assertStatus(422)->assertJson(['status' => false]);
    }

    public function test_create_rehearsal_missing_title()
    {
        $ensemble = Ensemble::factory()->create(['owner_id' => $this->user->id]);

        $response = $this->actingAs($this->user)->postJson(
            "/api/ensembles/{$ensemble->id}/rehearsals",
            ['date' => '2026-07-20']
        );

        $response->assertStatus(422)->assertJson(['status' => false]);
    }

    public function test_create_rehearsal_missing_date()
    {
        $ensemble = Ensemble::factory()->create(['owner_id' => $this->user->id]);

        $response = $this->actingAs($this->user)->postJson(
            "/api/ensembles/{$ensemble->id}/rehearsals",
            ['title' => 'Ensayo']
        );

        $response->assertStatus(422)->assertJson(['status' => false]);
    }

    public function test_ensemble_soft_delete()
    {
        $ensemble = Ensemble::factory()->create(['owner_id' => $this->user->id]);

        $this->actingAs($this->user)->deleteJson("/api/ensembles/{$ensemble->id}");

        $this->assertSoftDeleted($ensemble);
    }

    public function test_list_ensembles_shows_soft_deleted()
    {
        $ensemble = Ensemble::factory()->create(['owner_id' => $this->user->id]);
        $ensemble->delete();

        // Soft-deleted should NOT appear in index
        $response = $this->actingAs($this->user)->getJson('/api/ensembles');

        $response->assertStatus(200)->assertJsonCount(0, 'data');
    }

    public function test_my_ensembles_shows_only_own()
    {
        Ensemble::factory()->create(['owner_id' => $this->otherUser->id]);
        $ensemble = Ensemble::factory()->create(['owner_id' => $this->user->id]);
        $ensemble->members()->attach($this->user->id, ['role' => 'administrador']);

        $response = $this->actingAs($this->user)->getJson('/api/my-ensembles');

        $response->assertStatus(200)->assertJsonCount(1, 'data');
        $this->assertEquals($ensemble->id, $response->json('data.0.id'));
    }

    // ── Bulk Upload Scores ──

    public function test_bulk_upload_scores_requires_auth()
    {
        $ensemble = Ensemble::factory()->create();

        $response = $this->postJson("/api/ensembles/{$ensemble->id}/scores/bulk-upload", []);

        $response->assertStatus(401);
    }

    public function test_bulk_upload_scores_validation_no_files()
    {
        $ensemble = Ensemble::factory()->create(['owner_id' => $this->user->id]);

        $response = $this->actingAs($this->user)->postJson(
            "/api/ensembles/{$ensemble->id}/scores/bulk-upload",
            ['files' => []]
        );

        $response->assertStatus(422)->assertJson(['status' => false]);
    }

    public function test_bulk_upload_scores_validation_invalid_file_type()
    {
        Storage::fake('Wasabi');
        $ensemble = Ensemble::factory()->create(['owner_id' => $this->user->id]);

        $txtFile = UploadedFile::fake()->create('notes.txt', 100);

        $response = $this->actingAs($this->user)->post(
            "/api/ensembles/{$ensemble->id}/scores/bulk-upload",
            ['files' => [$txtFile]]
        );

        $response->assertStatus(422)->assertJson(['status' => false]);
    }

    public function test_bulk_upload_success()
    {
        Storage::fake('Wasabi');
        $ensemble = Ensemble::factory()->create(['owner_id' => $this->user->id]);

        $pdf1 = UploadedFile::fake()->create('Marcha_Real.pdf', 100, 'application/pdf');
        $pdf2 = UploadedFile::fake()->create('Sinfonia_No5.pdf', 100, 'application/pdf');

        $response = $this->actingAs($this->user)->post(
            "/api/ensembles/{$ensemble->id}/scores/bulk-upload",
            ['files' => [$pdf1, $pdf2]]
        );

        $response->assertStatus(200)->assertJson(['status' => true]);
        $data = $response->json('data');
        $this->assertCount(2, $data['uploaded']);
        $this->assertEmpty($data['errors']);

        $this->assertDatabaseHas('music_scores', ['name' => 'Marcha_Real', 'ensemble_id' => $ensemble->id]);
        $this->assertDatabaseHas('music_scores', ['name' => 'Sinfonia_No5', 'ensemble_id' => $ensemble->id]);
    }

    public function test_bulk_upload_with_folder()
    {
        Storage::fake('Wasabi');
        $ensemble = Ensemble::factory()->create(['owner_id' => $this->user->id]);
        $folder = EnsembleFolder::factory()->create(['ensemble_id' => $ensemble->id, 'name' => 'Clásico']);

        $pdf = UploadedFile::fake()->create('Beethoven.pdf', 100, 'application/pdf');

        $response = $this->actingAs($this->user)->post(
            "/api/ensembles/{$ensemble->id}/scores/bulk-upload",
            ['files' => [$pdf], 'ensemble_folder_id' => $folder->id]
        );

        $response->assertStatus(200)->assertJson(['status' => true]);
        $this->assertDatabaseHas('music_scores', [
            'name' => 'Beethoven',
            'ensemble_folder_id' => $folder->id,
        ]);
    }

    public function test_bulk_upload_folder_not_found()
    {
        $ensemble = Ensemble::factory()->create(['owner_id' => $this->user->id]);

        $pdf = UploadedFile::fake()->create('test.pdf', 100, 'application/pdf');

        $response = $this->actingAs($this->user)->post(
            "/api/ensembles/{$ensemble->id}/scores/bulk-upload",
            ['files' => [$pdf], 'ensemble_folder_id' => 99999]
        );

        $response->assertStatus(422)->assertJson(['status' => false]);
    }

    public function test_list_members_returns_member_list(): void
    {
        $ensemble = Ensemble::factory()->create(['owner_id' => $this->user->id]);
        $ensemble->members()->attach($this->user->id, ['role' => 'administrador']);
        $ensemble->members()->attach($this->otherUser->id, ['role' => 'maestro']);

        $response = $this->actingAs($this->user)
            ->getJson("/api/ensembles/{$ensemble->id}/members");

        $response->assertStatus(200)
            ->assertJsonPath('status', true)
            ->assertJsonCount(2, 'data');
    }

    public function test_list_members_requires_auth(): void
    {
        $ensemble = Ensemble::factory()->create();

        $this->getJson("/api/ensembles/{$ensemble->id}/members")
            ->assertStatus(401);
    }

    public function test_store_score_creates_single_score(): void
    {
        $ensemble = Ensemble::factory()->create(['owner_id' => $this->user->id]);
        $ensemble->members()->attach($this->user->id, ['role' => 'administrador']);

        $response = $this->actingAs($this->user)
            ->postJson("/api/ensembles/{$ensemble->id}/scores", [
                'name' => 'New Ensemble Score',
            ]);

        $response->assertStatus(201)
            ->assertJsonPath('status', true)
            ->assertJsonPath('data.name', 'New Ensemble Score')
            ->assertJsonPath('data.owner_id', $this->user->id);

        $this->assertDatabaseHas('music_scores', [
            'name' => 'New Ensemble Score',
            'ensemble_id' => $ensemble->id,
            'owner_id' => $this->user->id,
        ]);
    }

    public function test_store_score_requires_auth(): void
    {
        $ensemble = Ensemble::factory()->create();

        $this->postJson("/api/ensembles/{$ensemble->id}/scores", [
            'name' => 'Unauthorized',
        ])->assertStatus(401);
    }

    public function test_store_score_validation_error(): void
    {
        $ensemble = Ensemble::factory()->create(['owner_id' => $this->user->id]);
        $ensemble->members()->attach($this->user->id, ['role' => 'administrador']);

        $response = $this->actingAs($this->user)
            ->postJson("/api/ensembles/{$ensemble->id}/scores", []);

        $response->assertStatus(422)
            ->assertJsonPath('status', false);
    }
}
