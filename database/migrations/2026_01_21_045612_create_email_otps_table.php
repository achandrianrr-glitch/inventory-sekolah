<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('email_otps', function (Blueprint $table) {
            $table->bigIncrements('otp_id');

            $table->string('email')->index();
            $table->string('otp_hash', 255);

            $table->timestamp('expires_at');
            $table->timestamp('verified_at')->nullable();

            $table->unsignedTinyInteger('attempt_count')->default(0);
            $table->unsignedTinyInteger('sent_count')->default(1);
            $table->timestamp('last_sent_at')->nullable();

            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();

            $table->timestamps();

            $table->index(['email', 'expires_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('email_otps');
    }
};
