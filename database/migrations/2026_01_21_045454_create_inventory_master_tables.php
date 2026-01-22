<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('kategori', function (Blueprint $table) {
            $table->bigIncrements('kategori_id');
            $table->string('nama')->unique();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('merek', function (Blueprint $table) {
            $table->bigIncrements('merek_id');
            $table->string('nama')->unique();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('lokasi', function (Blueprint $table) {
            $table->bigIncrements('lokasi_id');
            $table->string('nama_ruangan');
            $table->string('kode_ruangan')->nullable()->unique();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('supplier', function (Blueprint $table) {
            $table->bigIncrements('supplier_id');
            $table->string('nama');
            $table->string('kontak')->nullable();
            $table->text('keterangan')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->index('nama');
        });

        Schema::create('barang', function (Blueprint $table) {
            $table->bigIncrements('barang_id');

            $table->string('kode_inventaris')->unique();
            $table->string('nama');

            $table->unsignedBigInteger('kategori_id');
            $table->unsignedBigInteger('merek_id')->nullable();
            $table->unsignedBigInteger('lokasi_id');
            $table->unsignedBigInteger('supplier_id')->nullable();

            $table->unsignedSmallInteger('tahun_pengadaan')->nullable();
            $table->unsignedBigInteger('nilai_aset')->nullable();

            $table->text('spesifikasi_singkat')->nullable();

            $table->enum('kondisi', ['baik', 'rusak_ringan', 'rusak_berat'])->default('baik');

            $table->unsignedInteger('stok_total')->default(0);
            $table->unsignedInteger('stok_tersedia')->default(0);
            $table->unsignedInteger('stok_dipinjam')->default(0);
            $table->unsignedInteger('min_stok')->default(0);

            $table->boolean('is_active')->default(true);

            $table->timestamps();

            $table->index('nama');
            $table->index('kondisi');
            $table->index(['kategori_id', 'lokasi_id']);

            $table->foreign('kategori_id')
                ->references('kategori_id')->on('kategori')
                ->onUpdate('cascade')
                ->onDelete('restrict');

            $table->foreign('merek_id')
                ->references('merek_id')->on('merek')
                ->onUpdate('cascade')
                ->onDelete('set null');

            $table->foreign('lokasi_id')
                ->references('lokasi_id')->on('lokasi')
                ->onUpdate('cascade')
                ->onDelete('restrict');

            $table->foreign('supplier_id')
                ->references('supplier_id')->on('supplier')
                ->onUpdate('cascade')
                ->onDelete('set null');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('barang');
        Schema::dropIfExists('supplier');
        Schema::dropIfExists('lokasi');
        Schema::dropIfExists('merek');
        Schema::dropIfExists('kategori');
    }
};
