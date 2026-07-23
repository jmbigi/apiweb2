<?php

declare(strict_types=1);

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class DemoDataSeeder extends Seeder
{
    public function run(): void
    {
        $now = now();
        $hasExistingData = DB::table('composers')->count() > 0;
        if (! $hasExistingData) {
            DB::statement('SET FOREIGN_KEY_CHECKS=0');
            DB::table('composers')->truncate();
            DB::table('fk_music_score_style_music')->truncate();
            DB::table('fk_music_score_composer')->truncate();
            DB::table('fk_music_score_instrument')->truncate();
            DB::table('ensembles')->truncate();
            DB::table('ensemble_folders')->truncate();
            DB::table('ensemble_user')->truncate();
            DB::table('rehearsals')->truncate();
            DB::statement('SET FOREIGN_KEY_CHECKS=1');

            $composerIds = [];
            $composers = [
                ['users_id' => 3, 'public_name' => 'Antonio Vivaldi', 'name' => 'Antonio', 'surname' => 'Vivaldi', 'vat_number' => null, 'street' => null, 'city' => 'Venecia', 'postal_code' => null, 'country' => 'Italia', 'notification_email' => null, 'telephone' => null, 'created_at' => $now, 'updated_at' => $now],
                ['users_id' => null, 'public_name' => 'Wolfgang A. Mozart', 'name' => 'Wolfgang', 'surname' => 'Mozart', 'vat_number' => null, 'street' => null, 'city' => 'Salzburgo', 'postal_code' => null, 'country' => 'Austria', 'notification_email' => null, 'telephone' => null, 'created_at' => $now, 'updated_at' => $now],
                ['users_id' => null, 'public_name' => 'Ludwig van Beethoven', 'name' => 'Ludwig', 'surname' => 'van Beethoven', 'vat_number' => null, 'street' => null, 'city' => 'Bonn', 'postal_code' => null, 'country' => 'Alemania', 'notification_email' => null, 'telephone' => null, 'created_at' => $now, 'updated_at' => $now],
                ['users_id' => null, 'public_name' => 'Johann S. Bach', 'name' => 'Johann', 'surname' => 'Bach', 'vat_number' => null, 'street' => null, 'city' => 'Eisenach', 'postal_code' => null, 'country' => 'Alemania', 'notification_email' => null, 'telephone' => null, 'created_at' => $now, 'updated_at' => $now],
                ['users_id' => null, 'public_name' => 'Frédéric Chopin', 'name' => 'Frédéric', 'surname' => 'Chopin', 'vat_number' => null, 'street' => null, 'city' => 'Varsovia', 'postal_code' => null, 'country' => 'Polonia', 'notification_email' => null, 'telephone' => null, 'created_at' => $now, 'updated_at' => $now],
                ['users_id' => null, 'public_name' => 'Giuseppe Verdi', 'name' => 'Giuseppe', 'surname' => 'Verdi', 'vat_number' => null, 'street' => null, 'city' => 'Parma', 'postal_code' => null, 'country' => 'Italia', 'notification_email' => null, 'telephone' => null, 'created_at' => $now, 'updated_at' => $now],
                ['users_id' => null, 'public_name' => 'Pyotr I. Tchaikovsky', 'name' => 'Pyotr', 'surname' => 'Tchaikovsky', 'vat_number' => null, 'street' => null, 'city' => 'Moscú', 'postal_code' => null, 'country' => 'Rusia', 'notification_email' => null, 'telephone' => null, 'created_at' => $now, 'updated_at' => $now],
            ];
            foreach ($composers as $c) {
                $composerIds[] = DB::table('composers')->insertGetId($c);
            }

            $scoreIds = DB::table('music_scores')->pluck('id');
            $styleIds = DB::table('style_musics')->pluck('id');
            $instrumentIds = DB::table('instruments')->pluck('id');

            foreach ($scoreIds as $sid) {
                $assignedStyles = $styleIds->random(min(rand(1, 3), $styleIds->count()));
                foreach ($assignedStyles as $stid) {
                    DB::table('fk_music_score_style_music')->insert([
                        'music_scores_id' => $sid,
                        'style_musics_id' => $stid,
                        'created_at' => $now,
                        'updated_at' => $now,
                    ]);
                }

                $assignedInstruments = $instrumentIds->random(min(rand(1, 2), $instrumentIds->count()));
                foreach ($assignedInstruments as $iid) {
                    DB::table('fk_music_score_instrument')->insert([
                        'music_scores_id' => $sid,
                        'instruments_id' => $iid,
                        'created_at' => $now,
                        'updated_at' => $now,
                    ]);
                }

                $assignedComposers = $composerIds;
                shuffle($assignedComposers);
                $take = min(rand(1, 2), count($assignedComposers));
                for ($i = 0; $i < $take; $i++) {
                    DB::table('fk_music_score_composer')->insert([
                        'music_scores_id' => $sid,
                        'composers_id' => $assignedComposers[$i],
                        'created_at' => $now,
                        'updated_at' => $now,
                    ]);
                }
            }

            $ensembleId = DB::table('ensembles')->insertGetId([
                'name' => 'Orquesta Sinfónica Demo',
                'cif' => 'DEMO12345',
                'description' => 'Agrupación de prueba para demostración',
                'owner_id' => 1,
                'status' => 1,
                'created_at' => $now,
                'updated_at' => $now,
            ]);

            $folderNames = ['Conciertos', 'Ensayos', 'Repertorio Clásico'];
            foreach ($folderNames as $name) {
                DB::table('ensemble_folders')->insert([
                    'ensemble_id' => $ensembleId,
                    'name' => $name,
                    'created_at' => $now,
                    'updated_at' => $now,
                ]);
            }

            foreach ([3, 4] as $uid) {
                DB::table('ensemble_user')->insert([
                    'ensemble_id' => $ensembleId,
                    'user_id' => $uid,
                    'role' => 'usuario',
                    'status' => 1,
                    'created_at' => $now,
                    'updated_at' => $now,
                ]);
            }

            $rehearsals = [
                ['title' => 'Ensayo general', 'date' => now()->addDays(3)->format('Y-m-d'), 'time' => '18:00:00', 'location' => 'Auditorio Nacional'],
                ['title' => 'Ensayo de cuerdas', 'date' => now()->addDays(5)->format('Y-m-d'), 'time' => '16:00:00', 'location' => 'Sala 1'],
                ['title' => 'Ensayo de vientos', 'date' => now()->addDays(7)->format('Y-m-d'), 'time' => '16:00:00', 'location' => 'Sala 2'],
            ];
            foreach ($rehearsals as $r) {
                DB::table('rehearsals')->insert([
                    'ensemble_id' => $ensembleId,
                    'title' => $r['title'],
                    'date' => $r['date'],
                    'time' => $r['time'],
                    'location' => $r['location'],
                    'instructor_id' => 1,
                    'notes' => null,
                    'status' => 1,
                    'created_at' => $now,
                    'updated_at' => $now,
                ]);
            }
        } // fin if !hasExistingData

        // 7. Subscription plans
        $planNames = ['Básico', 'Premium', 'Premium Plus'];
        if (DB::table('subscription_plan')->count() === 0) {
            $planPrices = [0, 9.99, 19.99];
            $planTypes = [0, 1, 1];
            $planTypeLabels = ['FREE', 'PREMIUM', 'PREMIUM_PLUS'];
            foreach ($planNames as $i => $name) {
                DB::table('subscription_plan')->insert([
                    'name' => $name,
                    'price' => $planPrices[$i],
                    'description' => $i === 0 ? 'Plan gratuito básico' : ($i === 1 ? 'Plan premium con todas las funciones' : 'Plan premium plus con características avanzadas'),
                    'type' => $planTypes[$i],
                    'type_label' => $planTypeLabels[$i],
                    'start_date' => $now->format('Y-m-d'),
                    'created_at' => $now,
                    'updated_at' => $now,
                ]);
            }
        }

        // 8. Link infos for faristol scores
        if (DB::table('link_infos')->count() === 0) {
            foreach ([21, 22, 23, 24, 25] as $sid) {
                DB::table('link_infos')->insert([
                    'url' => 'https://web.faristol.net/list',
                    'social_network' => 'web',
                    'music_scores_id' => $sid,
                    'created_at' => $now,
                    'updated_at' => $now,
                ]);
            }
        }

        // 9. Fav music score for composer user
        if (DB::table('fav_music_score')->count() === 0) {
            DB::table('fav_music_score')->insert([
                'user_id' => 3,
                'music_scores_id' => 21,
                'created_at' => $now,
                'updated_at' => $now,
            ]);
        }

        // 10. Premium trial for composer user
        if (DB::table('premium_trials')->count() === 0) {
            DB::table('premium_trials')->insert([
                'user_id' => 3,
                'used_count' => 1,
                'created_at' => $now,
                'updated_at' => $now,
            ]);
        }

        $composersCount = isset($composers) ? count($composers) : 0;
        $scoresCount = isset($scoreIds) ? $scoreIds->count() : 0;
        $rehearsalsCount = isset($rehearsals) ? count($rehearsals) : 0;
        $this->command->info('Demo data seeded: '.$composersCount.' compositores, '.$scoresCount.' scores vinculados, 1 ensemble, 3 carpetas, '.$rehearsalsCount.' ensayos, '.count($planNames).' planes.');
    }
}
