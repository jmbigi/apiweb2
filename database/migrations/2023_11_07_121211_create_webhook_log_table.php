<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('webhook_log', function (Blueprint $table) {
            $table->id();
            $table->string('response')->nullable();
            $table->string('plan_id')->nullable();
            $table->string('subscription_id')->nullable();
            $table->string('order_id')->nullable();
            $table->string('event_type')->nullable();
            $table->string('status')->nullable();
            $table->timestamps();
        });
    }

    
};
