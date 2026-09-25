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
        Schema::create('user_devices', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('device_id')->index();
            $table->string('device_name')->nullable();
            $table->string('platform');
            $table->string('os_version')->nullable();
            $table->string('app_version')->nullable();
            $table->string('fingerprint_hash')->index();
            $table->string('last_ip')->nullable();
            $table->string('last_ip_country')->nullable();
            $table->boolean('is_trusted')->default(false);
            $table->boolean('is_roooted_or_jailbroken')->default(false);
            $table->timestamp('first_seen_at')->nullable();
            $table->timestamp('last_seen_at')->nullable();

            $table->timestamps();
            $table->unique(['user_id', 'device_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('user_devices');
    }
};
