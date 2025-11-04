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
        Schema::create('acc_plo_lo_ta', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('periode_id')->index()->nullable();
            $table->year('year')->nullable();
            $table->integer('instance_id')->nullable();
            $table->integer('unit_id')->nullable();

            $table->string('type')->nullable();
            $table->integer('kode_rekening_id')->nullable();
            $table->double('anggaran_perubahan')->nullable();
            $table->double('lra')->nullable();
            $table->double('lra_percent')->nullable();
            $table->double('piutang_awal')->nullable();
            $table->double('piutang_akhir')->nullable();
            $table->double('pdd_awal')->nullable();
            $table->double('pdd_akhir')->nullable();
            $table->double('laporan_operasional')->nullable();
            $table->double('laporan_operasional_percent')->nullable();
            $table->double('penambahan_pengurangan_lo')->nullable();
            $table->double('reklas_koreksi_lo')->nullable();
            $table->double('perbedaan_lo_lra')->nullable();

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
        Schema::dropIfExists('acc_plo_lo_ta');
    }
};
