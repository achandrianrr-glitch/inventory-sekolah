<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('barang_masuk', function (Blueprint $table) {
            $table->bigIncrements('masuk_id');

            $table->dateTime('tanggal');
            $table->unsignedBigInteger('barang_id');
            $table->unsignedInteger('jumlah');
            $table->text('catatan')->nullable();

            $table->unsignedBigInteger('dibuat_oleh'); // users.user_id

            $table->timestamps();

            $table->index(['tanggal', 'barang_id']);

            $table->foreign('barang_id')
                ->references('barang_id')->on('barang')
                ->onUpdate('cascade')
                ->onDelete('restrict');

            $table->foreign('dibuat_oleh')
                ->references('user_id')->on('users')
                ->onUpdate('cascade')
                ->onDelete('restrict');
        });

        Schema::create('barang_keluar', function (Blueprint $table) {
            $table->bigIncrements('keluar_id');

            $table->dateTime('tanggal');
            $table->unsignedBigInteger('barang_id');
            $table->unsignedInteger('jumlah');
            $table->string('tujuan')->nullable();
            $table->text('catatan')->nullable();

            $table->unsignedBigInteger('dibuat_oleh'); // users.user_id

            $table->timestamps();

            $table->index(['tanggal', 'barang_id']);

            $table->foreign('barang_id')
                ->references('barang_id')->on('barang')
                ->onUpdate('cascade')
                ->onDelete('restrict');

            $table->foreign('dibuat_oleh')
                ->references('user_id')->on('users')
                ->onUpdate('cascade')
                ->onDelete('restrict');
        });

        Schema::create('peminjaman', function (Blueprint $table) {
            $table->bigIncrements('pinjam_id');

            $table->string('kode_pinjam')->unique();

            $table->unsignedBigInteger('user_id'); // peminjam
            $table->dateTime('tanggal_pinjam');
            $table->dateTime('tanggal_rencana_kembali');

            $table->enum('status', [
                'menunggu',
                'disetujui',
                'ditolak',
                'dipinjam',
                'menunggu_verifikasi_kembali',
                'dikembalikan',
                'terlambat',
            ])->default('menunggu');

            $table->text('catatan_user')->nullable();
            $table->text('catatan_admin')->nullable();

            $table->unsignedBigInteger('approved_by')->nullable();
            $table->dateTime('approved_at')->nullable();

            $table->timestamps();

            $table->index(['user_id', 'status']);
            $table->index(['tanggal_pinjam', 'tanggal_rencana_kembali']);

            $table->foreign('user_id')
                ->references('user_id')->on('users')
                ->onUpdate('cascade')
                ->onDelete('restrict');

            $table->foreign('approved_by')
                ->references('user_id')->on('users')
                ->onUpdate('cascade')
                ->onDelete('restrict');
        });

        Schema::create('peminjaman_item', function (Blueprint $table) {
            $table->bigIncrements('pinjam_item_id');

            $table->unsignedBigInteger('pinjam_id');
            $table->unsignedBigInteger('barang_id');
            $table->unsignedInteger('qty');

            $table->timestamps();

            $table->unique(['pinjam_id', 'barang_id']);

            $table->foreign('pinjam_id')
                ->references('pinjam_id')->on('peminjaman')
                ->onUpdate('cascade')
                ->onDelete('cascade');

            $table->foreign('barang_id')
                ->references('barang_id')->on('barang')
                ->onUpdate('cascade')
                ->onDelete('restrict');
        });

        Schema::create('pengembalian', function (Blueprint $table) {
            $table->bigIncrements('kembali_id');

            $table->unsignedBigInteger('pinjam_id');
            $table->dateTime('tanggal_pengajuan_kembali');

            $table->dateTime('tanggal_diterima')->nullable();

            $table->text('catatan_user')->nullable();
            $table->text('catatan_admin')->nullable();

            $table->unsignedBigInteger('diterima_by')->nullable();

            $table->enum('status', [
                'menunggu_verifikasi',
                'diterima',
                'perlu_revisi',
            ])->default('menunggu_verifikasi');

            $table->timestamps();

            $table->unique('pinjam_id');

            $table->foreign('pinjam_id')
                ->references('pinjam_id')->on('peminjaman')
                ->onUpdate('cascade')
                ->onDelete('cascade');

            $table->foreign('diterima_by')
                ->references('user_id')->on('users')
                ->onUpdate('cascade')
                ->onDelete('restrict');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pengembalian');
        Schema::dropIfExists('peminjaman_item');
        Schema::dropIfExists('peminjaman');
        Schema::dropIfExists('barang_keluar');
        Schema::dropIfExists('barang_masuk');
    }
};
