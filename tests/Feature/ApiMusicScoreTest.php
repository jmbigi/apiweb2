<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\Composer;
use App\Models\MusicScore;
use App\Models\Instrument;
use App\Models\StyleMusic;
use App\Models\FamilyInstruments;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class ApiMusicScoreTest extends TestCase
{
    private User $user;

    private static array $tables = [
        'users', 'personal_access_tokens', 'composers',
        'music_scores', 'instruments', 'style_musics', 'family_instruments',
        'fav_music_score', 'fk_music_score_composer',
        'fk_music_score_instrument', 'fk_music_score_style_music',
        'log_display_music_scores', 'files_s3_s', 'link_infos',
    ];

    protected function setUp(): void
    {
        parent::setUp();

        if (! Schema::hasTable('users')) {
            $this->createMusicScoreTables();
        } else {
            DB::statement('PRAGMA foreign_keys = OFF');
            foreach (self::$tables as $table) {
                DB::statement("DELETE FROM \"{$table}\"");
            }
            DB::statement('PRAGMA foreign_keys = ON');
        }

        $this->user = User::factory()->create(['email' => 'user@test.com']);

        if (! FamilyInstruments::where('name', 'Not categorized')->exists()) {
            FamilyInstruments::create(['name' => 'Not categorized']);
        }

        putenv('WAS_ACCESS_KEY_ID=dummy');
        putenv('WAS_SECRET_ACCESS_KEY=dummy');
        putenv('WAS_BUCKET=dummy');
        putenv('WAS_ENDPOINT=https://s3.dummy.com');
        config(['filesystems.disks.Wasabi' => [
            'driver' => 's3',
            'key' => 'dummy',
            'secret' => 'dummy',
            'region' => 'us-west-1',
            'bucket' => 'dummy',
            'endpoint' => 'https://s3.dummy.com',
        ]]);
    }

    private function createMusicScoreTables(): void
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

        Schema::create('music_scores', function ($table) {
            $table->id();
            $table->string('name')->unique();
            $table->text('description')->nullable();
            $table->unsignedBigInteger('owner_id');
            $table->foreign('owner_id')->references('id')->on('users')->cascadeOnDelete();
            $table->boolean('status')->default(1);
            $table->date('date')->nullable();
            $table->timestamps();
        });

        Schema::create('instruments', function ($table) {
            $table->id();
            $table->string('name')->unique();
            $table->unsignedBigInteger('family_instruments_id')->nullable();
            $table->timestamp('request')->nullable();
            $table->timestamp('approved')->nullable();
            $table->timestamps();
        });

        Schema::create('style_musics', function ($table) {
            $table->id();
            $table->string('name')->unique();
            $table->timestamp('request')->nullable();
            $table->timestamp('approved')->nullable();
            $table->timestamps();
        });

        Schema::create('log_display_music_scores', function ($table) {
            $table->id();
            $table->foreignId('users_id')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('music_scores_id')->nullable()->constrained('music_scores')->nullOnDelete();
            $table->timestamps();
        });

        Schema::create('fav_music_score', function ($table) {
            $table->id();
            $table->foreignId('music_scores_id')->nullable()->constrained('music_scores')->nullOnDelete();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });

        Schema::create('fk_music_score_composer', function ($table) {
            $table->id();
            $table->foreignId('music_scores_id')->nullable()->constrained('music_scores')->nullOnDelete();
            $table->foreignId('composers_id')->nullable()->constrained('composers')->nullOnDelete();
            $table->timestamps();
        });

        Schema::create('fk_music_score_instrument', function ($table) {
            $table->id();
            $table->foreignId('music_scores_id')->nullable()->constrained('music_scores')->nullOnDelete();
            $table->foreignId('instruments_id')->nullable()->constrained('instruments')->nullOnDelete();
            $table->timestamps();
        });

        Schema::create('fk_music_score_style_music', function ($table) {
            $table->id();
            $table->foreignId('music_scores_id')->nullable()->constrained('music_scores')->nullOnDelete();
            $table->foreignId('style_musics_id')->nullable()->constrained('style_musics')->nullOnDelete();
            $table->timestamps();
        });

        Schema::create('files_s3_s', function ($table) {
            $table->id();
            $table->string('path');
            $table->string('storagePlace')->default('Wasabi');
            $table->string('extension')->nullable();
            $table->unsignedBigInteger('fileable_id');
            $table->string('fileable_type');
            $table->timestamps();
        });

        Schema::create('link_infos', function ($table) {
            $table->id();
            $table->string('url');
            $table->string('social_network')->nullable();
            $table->foreignId('music_scores_id')->constrained('music_scores')->onDelete('cascade');
            $table->timestamps();
        });

        Schema::create('family_instruments', function ($table) {
            $table->id();
            $table->string('name');
            $table->timestamp('request')->nullable();
            $table->timestamp('approved')->nullable();
            $table->timestamps();
        });
    }

    public function test_public_list_returns_all_scores(): void
    {
        MusicScore::factory()->count(3)->create(['owner_id' => $this->user->id]);

        $response = $this->getJson('/api/music-score/list');

        $response->assertStatus(200)
            ->assertJsonPath('status', true)
            ->assertJsonCount(3, 'data');
    }

    public function test_public_list_with_no_scores_returns_empty(): void
    {
        $response = $this->getJson('/api/music-score/list');

        $response->assertStatus(200)
            ->assertJsonPath('status', true);
    }

    public function test_get_single_score(): void
    {
        $score = MusicScore::factory()->create([
            'owner_id' => $this->user->id,
            'name' => 'Test Sonata',
        ]);

        $response = $this->getJson("/api/music-score/get/{$score->id}");

        $response->assertStatus(200)
            ->assertJsonPath('status', true)
            ->assertJsonPath('data.0.name', 'Test Sonata');
    }

    public function test_get_nonexistent_score_returns_empty(): void
    {
        $response = $this->getJson('/api/music-score/get/99999');

        $response->assertStatus(200);
    }

    public function test_filtered_list_by_name(): void
    {
        MusicScore::factory()->create(['owner_id' => $this->user->id, 'name' => 'Alpha Sonata']);
        MusicScore::factory()->create(['owner_id' => $this->user->id, 'name' => 'Beta Symphony']);

        $response = $this->getJson('/api/music-score/list-filtered?name=Alpha');

        $response->assertStatus(200)
            ->assertJsonPath('status', true);
    }

    public function test_fav_music_score_requires_auth(): void
    {
        $this->getJson('/api/music-score/fav-music-score?music_score_id=1')
            ->assertStatus(401);
    }

    public function test_add_and_remove_favorite(): void
    {
        $score = MusicScore::factory()->create(['owner_id' => $this->user->id]);
        $token = $this->user->createToken('test')->plainTextToken;

        $headers = ['Authorization' => "Bearer {$token}", 'Accept' => 'application/json'];

        $this->withHeaders($headers)
            ->getJson("/api/music-score/fav-music-score?music_score_id={$score->id}")
            ->assertStatus(200)
            ->assertJsonPath('status', true);

        $this->assertDatabaseHas('fav_music_score', [
            'music_scores_id' => $score->id,
            'user_id' => $this->user->id,
        ]);

        $this->withHeaders($headers)
            ->getJson("/api/music-score/remove-fav-music-score?music_score_id={$score->id}")
            ->assertStatus(200)
            ->assertJsonPath('status', true);

        $this->assertDatabaseMissing('fav_music_score', [
            'music_scores_id' => $score->id,
            'user_id' => $this->user->id,
        ]);
    }

    public function test_user_favorites_list(): void
    {
        $score = MusicScore::factory()->create(['owner_id' => $this->user->id]);
        $token = $this->user->createToken('test')->plainTextToken;

        $headers = ['Authorization' => "Bearer {$token}", 'Accept' => 'application/json'];
        $this->withHeaders($headers)
            ->getJson("/api/music-score/fav-music-score?music_score_id={$score->id}")
            ->assertStatus(200);

        $response = $this->withHeaders($headers)
            ->getJson('/api/music-score/user-fav-music-score');

        $response->assertStatus(200)
            ->assertJsonPath('status', true);
    }

    public function test_protected_endpoints_return_401_without_auth(): void
    {
        $this->getJson('/api/music-score/getMusicScorePdf/1')->assertStatus(401);
        $this->postJson('/api/music-score/getPdfContent')->assertStatus(401);
        $this->getJson('/api/music-score/composer')->assertStatus(401);
        $this->getJson('/api/music-score/user-fav-music-score')->assertStatus(401);
    }

    public function test_composer_music_returns_data(): void
    {
        $composer = Composer::create(['public_name' => 'Mozart']);
        $score = MusicScore::factory()->create(['owner_id' => $this->user->id]);
        $score->composers()->attach($composer->id);
        $token = $this->user->createToken('test')->plainTextToken;

        $response = $this->withHeaders([
            'Authorization' => "Bearer {$token}",
            'Accept' => 'application/json',
        ])->getJson("/api/music-score/composer?id={$composer->id}");

        $response->assertStatus(200)
            ->assertJsonPath('status', true);
    }

    public function test_create_score_requires_auth(): void
    {
        $this->postJson('/api/music-score/create', [
            'name' => 'Test Score',
        ])->assertStatus(401);
    }

    public function test_create_score_validation_error(): void
    {
        $token = $this->user->createToken('test')->plainTextToken;
        $headers = ['Authorization' => "Bearer {$token}", 'Accept' => 'application/json'];

        $response = $this->withHeaders($headers)
            ->postJson('/api/music-score/create', ['name' => 'Test']);

        $response->assertStatus(401)
            ->assertJsonPath('status', false)
            ->assertJsonPath('message', 'validation error');
    }

    public function test_create_score_with_pdf(): void
    {
        Storage::fake('Wasabi');

        $instrument = Instrument::create(['name' => 'Piano']);
        $style = StyleMusic::create(['name' => 'Classical']);

        $token = $this->user->createToken('test')->plainTextToken;
        $headers = ['Authorization' => "Bearer {$token}", 'Accept' => 'application/json'];

        $pdf = UploadedFile::fake()->create('score.pdf', 100, 'application/pdf');

        $response = $this->withHeaders($headers)
            ->post('/api/music-score/create', [
                'name' => 'New Test Sonata',
                'pdf' => $pdf,
                'instrument_id' => json_encode([$instrument->id]),
                'style_id' => json_encode([$style->id]),
                'links' => 'https://example.com/score',
            ]);

        $response->assertStatus(200)
            ->assertJsonPath('status', true)
            ->assertJsonPath('message', 'Music Score Created');

        $this->assertDatabaseHas('music_scores', [
            'name' => 'New Test Sonata',
        ]);

        $this->assertDatabaseHas('files_s3_s', [
            'fileable_type' => 'App\Models\MusicScore',
            'extension' => 'pdf',
        ]);

        $this->assertDatabaseHas('link_infos', [
            'url' => 'https://example.com/score',
        ]);
    }

    public function test_create_score_with_cover_image(): void
    {
        Storage::fake('Wasabi');

        $instrument = Instrument::create(['name' => 'Violin']);
        $style = StyleMusic::create(['name' => 'Romantic']);

        $token = $this->user->createToken('test')->plainTextToken;
        $headers = ['Authorization' => "Bearer {$token}", 'Accept' => 'application/json'];

        $pdf = UploadedFile::fake()->create('romantic.pdf', 100, 'application/pdf');
        $cover = UploadedFile::fake()->image('cover.jpg', 100, 100);

        $response = $this->withHeaders($headers)
            ->post('/api/music-score/create', [
                'name' => 'Romantic Symphony',
                'pdf' => $pdf,
                'cover' => $cover,
                'instrument_id' => json_encode(['Violin']),
                'style_id' => json_encode([$style->id]),
                'links' => 'https://example.com/romantic',
            ]);

        $response->assertStatus(200)
            ->assertJsonPath('status', true)
            ->assertJsonPath('message', 'Music Score Created');

        $this->assertDatabaseHas('music_scores', [
            'name' => 'Romantic Symphony',
        ]);

        $this->assertDatabaseHas('files_s3_s', [
            'extension' => 'pdf',
        ]);
    }

    public function test_update_score_successfully(): void
    {
        Storage::fake('Wasabi');

        $instrument = Instrument::create(['name' => 'Guitar']);
        $style = StyleMusic::create(['name' => 'Jazz']);
        $pdf = UploadedFile::fake()->create('test.pdf', 100, 'application/pdf');

        $token = $this->user->createToken('test')->plainTextToken;
        $headers = ['Authorization' => "Bearer {$token}", 'Accept' => 'application/json'];

        $createResponse = $this->withHeaders($headers)->post('/api/music-score/create', [
            'name' => 'Original Piece',
            'pdf' => $pdf,
            'instrument_id' => json_encode([$instrument->id]),
            'style_id' => json_encode([$style->id]),
            'links' => 'https://example.com/original',
        ]);

        $createResponse->assertStatus(200);
        $scoreId = MusicScore::where('name', 'Original Piece')->first()->id;

        $updateResponse = $this->withHeaders($headers)->post("/api/music-score/update/{$scoreId}", [
            'id' => $scoreId,
            'name' => 'Updated Piece',
            'instrument_id' => json_encode([$instrument->id]),
            'style_id' => json_encode([$style->id]),
            'links' => 'https://example.com/updated',
        ]);

        $updateResponse->assertStatus(200)
            ->assertJsonPath('status', true);

        $this->assertDatabaseHas('music_scores', [
            'id' => $scoreId,
            'name' => 'Updated Piece',
        ]);
    }

    public function test_delete_score_successfully(): void
    {
        Storage::fake('Wasabi');

        $instrument = Instrument::create(['name' => 'Drums']);
        $style = StyleMusic::create(['name' => 'Rock']);
        $pdf = UploadedFile::fake()->create('delete.pdf', 100, 'application/pdf');

        $token = $this->user->createToken('test')->plainTextToken;
        $headers = ['Authorization' => "Bearer {$token}", 'Accept' => 'application/json'];

        $createResponse = $this->withHeaders($headers)->post('/api/music-score/create', [
            'name' => 'Delete Me',
            'pdf' => $pdf,
            'instrument_id' => json_encode([$instrument->id]),
            'style_id' => json_encode([$style->id]),
            'links' => 'https://example.com/delete',
        ]);

        $createResponse->assertStatus(200);
        $scoreId = MusicScore::where('name', 'Delete Me')->first()->id;

        $deleteResponse = $this->withHeaders($headers)->delete("/api/music-score/delete/{$scoreId}", [
            'id' => $scoreId,
        ]);

        $deleteResponse->assertStatus(200)
            ->assertJsonPath('status', true)
            ->assertJsonPath('message', 'Music Score Deleted');

        $this->assertDatabaseMissing('music_scores', ['id' => $scoreId]);
    }

    public function test_instruments_list_returns_data(): void
    {
        \App\Models\Instrument::create(['name' => 'Guitar', 'request' => now(), 'approved' => now()]);
        \App\Models\Instrument::create(['name' => 'Piano', 'request' => now(), 'approved' => now()]);

        $response = $this->getJson('/api/instruments/list');

        $response->assertStatus(200)
            ->assertJsonCount(2, 'data');
    }

    public function test_style_music_list_returns_data(): void
    {
        \App\Models\StyleMusic::create(['name' => 'Classical', 'request' => now(), 'approved' => now()]);
        \App\Models\StyleMusic::create(['name' => 'Jazz', 'request' => now(), 'approved' => now()]);

        $response = $this->getJson('/api/style-music/list');

        $response->assertStatus(200)
            ->assertJsonCount(2, 'data');
    }

    public function test_create_instrument_successfully(): void
    {
        $token = $this->user->createToken('test')->plainTextToken;
        $headers = ['Authorization' => "Bearer {$token}", 'Accept' => 'application/json'];

        $response = $this->withHeaders($headers)
            ->postJson('/api/instruments/create', [
                'instrument_name' => 'New Test Instrument',
            ]);

        $response->assertStatus(200)
            ->assertJsonPath('status', true);

        $this->assertDatabaseHas('instruments', [
            'name' => 'New Test Instrument',
        ]);
    }

    public function test_create_instrument_requires_auth(): void
    {
        $this->postJson('/api/instruments/create', [
            'instrument_name' => 'Hacked',
        ])->assertStatus(401);
    }

    public function test_instrument_suggest_requires_auth(): void
    {
        $this->postJson('/api/instruments/suggest', [
            'name' => 'Test',
            'family_instruments_id' => 1,
        ])->assertStatus(401);
    }

    public function test_instrument_suggest_successfully(): void
    {
        $token = $this->user->createToken('test')->plainTextToken;
        $headers = ['Authorization' => "Bearer {$token}", 'Accept' => 'application/json'];
        $familyId = \App\Models\FamilyInstruments::where('name', 'Not categorized')->value('id');

        $response = $this->withHeaders($headers)
            ->postJson('/api/instruments/suggest', [
                'name' => 'Suggested Instrument',
                'family_instruments_id' => $familyId,
            ]);

        $response->assertStatus(200)
            ->assertJsonPath('status', true)
            ->assertJsonPath('message', 'Saved suggested');

        $this->assertDatabaseHas('instruments', ['name' => 'Suggested Instrument']);
    }

    public function test_instrument_family_suggest_successfully(): void
    {
        $token = $this->user->createToken('test')->plainTextToken;
        $headers = ['Authorization' => "Bearer {$token}", 'Accept' => 'application/json'];

        $response = $this->withHeaders($headers)
            ->postJson('/api/instruments/family/suggest', [
                'name' => 'New Family',
            ]);

        $response->assertStatus(200)
            ->assertJsonPath('status', true)
            ->assertJsonPath('message', 'Saved suggested');

        $this->assertDatabaseHas('family_instruments', ['name' => 'New Family']);
    }

    public function test_style_music_suggest_requires_auth(): void
    {
        $this->postJson('/api/style-music/suggest', [
            'name' => 'Test Style',
        ])->assertStatus(401);
    }

    public function test_style_music_suggest_successfully(): void
    {
        $token = $this->user->createToken('test')->plainTextToken;
        $headers = ['Authorization' => "Bearer {$token}", 'Accept' => 'application/json'];

        $response = $this->withHeaders($headers)
            ->postJson('/api/style-music/suggest', [
                'name' => 'Suggested Style',
            ]);

        $response->assertStatus(200)
            ->assertJsonPath('status', true)
            ->assertJsonPath('message', 'Saved suggested');

        $this->assertDatabaseHas('style_musics', ['name' => 'Suggested Style']);
    }
}
