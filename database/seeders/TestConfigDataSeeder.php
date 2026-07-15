<?php

namespace Database\Seeders;

use App\Models\SubscriptionPlan;
use Exception;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class TestConfigDataSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::beginTransaction();
        try {
            $subscriptionPlan = SubscriptionPlan::firstWhere("name", "FREE");
            if ($subscriptionPlan == null) {
                SubscriptionPlan::insert([
                    'name' => 'FREE',
                    'description' => 'FREE',
                    'price' => 0.00,
                    'status' => 1,
                    'created_at' => now(),
                    'updated_at' => now(),
                    'start_date' => '2023-01-01',
                    'end_date' => '2033-01-01',
                    'plan_id' => 0,
                    'type' => 1,
                    'type_label' => 'FREE',
                ], ['name'], ['description', 'price', 'status', 'created_at', 'updated_at', 'start_date', 'end_date', 'plan_id', 'type', 'type_label']);
                dump("SubscriptionPlan added.");
            } else {
                dump("SubscriptionPlan already present. Id: " . $subscriptionPlan->id);
                //dump($subscriptionPlan);
            }
            // Commiteamos cambios
            DB::commit();
        } catch (Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }
}
