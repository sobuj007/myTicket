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
            $table->string('device_id', 191);
            // device Information 

            $table->string('device_brand')->nullable();
            $table->string('device_type')->nullable();
            $table->string('device_model')->nullable();
            // Oparating System 
            $table->string('os_version')->nullable();
            $table->string('os_name')->nullable();
            // browser System 
            $table->string('browser_version')->nullable();
            $table->string('browser_name')->nullable();
            // appkication information ............
            $table->string('platform')->nullable();
            $table->string('app_name')->nullable();
            $table->string('app_version')->nullable();
            $table->string('build_number')->nullable();
            // HTTP information ./................
            $table->text('user_agent')->nullable();
            $table->string('user_agent_hash,64')->nullable();
            $table->ipAddress('ip_address')->nullable();

            // Security ....................
            $table->boolean('is_trusted')->default(false);
            $table->boolean('is_blocked')->default(false);
            $table->boolean('is_active')->default(true);

            // Activity .........................   
            $table->timestamp('first_seen_at')->nullable();
            $table->timestamp('last_seen_at')->nullable();
            $table->timestamp('last_login_at')->nullable();
            $table->timestamp('removed_at')
                ->nullable();

            $table->string('ownhash')->nullable();

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
