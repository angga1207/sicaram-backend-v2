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
        Schema::create('acc_blo_subsidi', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('periode_id')->index()->nullable();
            $table->year('year')->nullable();
            $table->integer('instance_id')->nullable();
            $table->integer('unit_id')->nullable();

            $table->integer('kode_rekening_id')->nullable();
            $table->double('realisasi_belanja')->nullable();
            $table->double('saldo_awal')->nullable();
            $table->double('belanja_dibayar_dimuka_akhir')->nullable();
            $table->double('hutang')->nullable();
            $table->double('hibah')->nullable();
            $table->double('reklas_tambah_dari_rekening')->nullable();
            $table->double('reklas_tambah_dari_modal')->nullable();
            $table->double('plus_jukor')->nullable();
            $table->double('saldo_akhir')->nullable();
            $table->double('beban_tahun_lalu')->nullable();
            $table->double('belanja_dibayar_dimuka_awal')->nullable();
            $table->double('pembayaran_hutang')->nullable();
            $table->double('reklas_kurang_ke_rekening')->nullable();
            $table->double('reklas_kurang_ke_aset')->nullable();
            $table->double('atribusi')->nullable();
            $table->double('min_jukor')->nullable();
            $table->double('beban_lo')->nullable();

            $table->text('keterangan')->nullable();
            $table->json('change_logs')->nullable();
            $table->integer('created_by')->nullable();
            $table->integer('updated_by')->nullable();
            $table->integer('deleted_by')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('acc_blo_subsidi');
    }
};
