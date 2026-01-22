<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('kerusakan', function (Blueprint $table) {
            $table->bigIncrements('rusak_id');

            $table->string('kode_ticket')->unique();
            $table->unsignedBigInteger('barang_id');

            $table->unsignedBigInteger('pelapor_user_id')->nullable();

            $table->enum('level', ['ringan', 'sedang', 'berat'])->default('ringan');
            $table->text('deskripsi');

            $table->enum('status', ['menunggu', 'proses', 'selesai'])->default('menunggu');

            $table->unsignedBigInteger('handler_user_id')->nullable();

            $table->timestamps();

            $table->index(['status', 'level']);

            $table->foreign('barang_id')
                ->references('barang_id')->on('barang')
                ->onUpdate('cascade')
                ->onDelete('restrict');

            $table->foreign('pelapor_user_id')
                ->references('user_id')->on('users')
                ->onUpdate('cascade')
                ->onDelete('restrict');

            $table->foreign('handler_user_id')
                ->references('user_id')->on('users')
                ->onUpdate('cascade')
                ->onDelete('restrict');
        });

        Schema::create('opname', function (Blueprint $table) {
            $table->bigIncrements('opname_id');

            $table->dateTime('tanggal');
            $table->enum('status', ['draft', 'diverifikasi'])->default('draft');

            $table->unsignedBigInteger('dibuat_oleh');
            $table->unsignedBigInteger('diverifikasi_oleh')->nullable();

            $table->timestamps();

            $table->index(['tanggal', 'status']);

            $table->foreign('dibuat_oleh')
                ->references('user_id')->on('users')
                ->onUpdate('cascade')
                ->onDelete('restrict');

            $table->foreign('diverifikasi_oleh')
                ->references('user_id')->on('users')
                ->onUpdate('cascade')
                ->onDelete('restrict');
        });

        Schema::create('opname_item', function (Blueprint $table) {
            $table->bigIncrements('opname_item_id');

            $table->unsignedBigInteger('opname_id');
            $table->unsignedBigInteger('barang_id');

            $table->unsignedInteger('stok_sistem');
            $table->unsignedInteger('stok_fisik');
            $table->integer('selisih');

            $table->text('catatan')->nullable();

            $table->timestamps();

            $table->unique(['opname_id', 'barang_id']);

            $table->foreign('opname_id')
                ->references('opname_id')->on('opname')
                ->onUpdate('cascade')
                ->onDelete('cascade');

            $table->foreign('barang_id')
                ->references('barang_id')->on('barang')
                ->onUpdate('cascade')
                ->onDelete('restrict');
        });

        Schema::create('activity_logs', function (Blueprint $table) {
            $table->bigIncrements('log_id');

            $table->unsignedBigInteger('actor_user_id')->nullable();

            $table->string('aksi');
            $table->string('objek_tipe');
            $table->string('objek_id');

            $table->json('data_before')->nullable();
            $table->json('data_after')->nullable();

            $table->string('ip_address', 45)->nullable();

            $table->timestamps();

            $table->index(['actor_user_id', 'aksi']);
            $table->index(['objek_tipe', 'objek_id']);

            $table->foreign('actor_user_id')
                ->references('user_id')->on('users')
                ->onUpdate('cascade')
                ->onDelete('set null');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('activity_logs');
        Schema::dropIfExists('opname_item');
        Schema::dropIfExists('opname');
        Schema::dropIfExists('kerusakan');
    }
};
