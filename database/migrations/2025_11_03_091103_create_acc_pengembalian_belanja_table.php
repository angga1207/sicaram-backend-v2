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
        Schema::create('acc_pengembalian_belanja', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('periode_id')->index()->nullable();
            $table->year('year')->nullable();
            $table->integer('instance_id')->nullable();
            $table->integer('unit_id')->nullable();

            $table->date('tanggal_setor')->nullable();
            $table->integer('kode_rekening_id')->nullable();
            $table->text('uraian')->nullable();
            $table->string('jenis_spm')->nullable();
            $table->double('jumlah')->nullable();

            $table->text('keterangan')->nullable();
            $table->json('change_logs')->nullable();
            $table->integer('created_by')->nullable();
            $table->integer('updated_by')->nullable();
            $table->integer('deleted_by')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('created_by')->references('id')->on('users');
            $table->foreign('updated_by')->references('id')->on('users');
            $table->foreign('deleted_by')->references('id')->on('users');

            $table->index([
                'periode_id',
                'instance_id',
                'unit_id',
                'tanggal_setor',
                'kode_rekening_id',
                'jenis_spm',
                'created_by',
            ]);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('acc_pengembalian_belanja');
    }
};
