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
        Schema::create('sales', function (Blueprint $table) {
            $table->id();
            $table->string('nama_pemesan');
            $table->string('id_pesanan');
            $table->string('nama_pesanan');
            $table->integer('harga_barang');
            $table->integer('quantity');
            $table->string('jenis_transaksi');
            $table->string('nama_bank');
            $table->string('kota');
            $table->string('negara');
            $table->string('id_bank');
            $table->string('diskon_tipe')->nullable();
            $table->integer('diskon_nilai')->nullable();
            $table->integer('total_quantity')->nullable();
            $table->integer('total_diskon')->nullable();
            $table->integer('total_harga')->nullable();
            $table->enum('status', ['Selesai', 'Belum approval', 'Dibatalkan']);
            $table->date('periode');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sales');
    }
};
