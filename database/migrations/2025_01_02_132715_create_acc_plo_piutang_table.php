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
        Schema::create('acc_plo_piutang', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('periode_id')->index()->nullable();
            $table->year('year')->nullable();
            $table->integer('instance_id')->nullable();
            $table->integer('unit_id')->nullable();

            $table->string('type')->nullable();
            $table->integer('kode_rekening_id')->nullable();
            $table->double('saldo_awal')->nullable();
            $table->double('koreksi_saldo_awal')->nullable();
            $table->double('mutasi_debet')->nullable();
            $table->double('mutasi_kredit')->nullable();
            $table->double('saldo_akhir')->nullable();
            $table->double('umur_piutang_1')->nullable();
            $table->double('umur_piutang_2')->nullable();
            $table->double('umur_piutang_3')->nullable();
            $table->double('umur_piutang_4')->nullable();
            $table->double('piutang_bruto')->nullable();

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
        Schema::dropIfExists('acc_plo_piutang');
    }
};
