<?php

namespace App\Jobs;

use App\Models\Accountancy\AccReport;
use Carbon\Carbon;
use Illuminate\Bus\Queueable;
use Illuminate\Support\Facades\DB;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Support\Facades\Cache;

class AccountancyReportNeraca implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $timeouts = 0;
    public $periode, $year, $instance;

    public function __construct($periode = null, $year = null, $instance = null)
    {
        $this->periode = $periode;
        $this->year = $year;
        $this->instance = $instance;
    }

    public function handle(): void
    {
        DB::beginTransaction();
        try {
            $arrInstances = DB::table('instances')
                ->when($this->instance, function ($query) {
                    return $query->whereIn('id', $this->instance);
                })
                ->get();
            $now = Carbon::now();
            $datas = [];

            foreach ($arrInstances as $instance) {
                // KODE REKENING 1 - ASET - START
                $rekeningAset = DB::table('ref_kode_rekening_complete')
                    ->where('code_1', '1')
                    ->where('code_2', null)
                    ->where('code_3', null)
                    ->where('code_4', null)
                    ->where('code_5', null)
                    ->where('code_6', null)
                    ->first();
                $preReport = DB::table('acc_pre_report')
                    ->where('instance_id', $instance->id)
                    ->where('year', $this->year)
                    ->where('periode_id', $this->periode)
                    ->where('kode_rekening_id', $rekeningAset->id)
                    ->first();

                $accReport = AccReport::where('instance_id', $instance->id)
                    ->where('year', $this->year)
                    ->where('periode_id', $this->periode)
                    ->where('kode_rekening_id', $rekeningAset->id)
                    ->where('type', 'neraca')
                    ->first();

                if (!$accReport && $instance->id) {
                    $accReport = new AccReport();
                    $accReport->periode_id = $this->periode;
                    $accReport->year = $this->year;
                    $accReport->instance_id = $instance->id;
                    $accReport->kode_rekening_id = $rekeningAset->id;
                    $accReport->fullcode = $rekeningAset->fullcode;
                    $accReport->type = 'neraca';
                    $accReport->saldo_awal = $preReport->saldo_awal ?? 0;
                    $accReport->saldo_akhir = 0;
                    $accReport->kenaikan_penurunan = 0;
                    $accReport->percent = 0;
                    $accReport->created_at = $now;
                    $accReport->save();
                } else if ($preReport->saldo_awal != $accReport->saldo_awal) {
                    $accReport->saldo_awal = $preReport->saldo_awal ?? 0;
                    $accReport->save();
                }

                $datas[] = [
                    'id' => $accReport->id ?? null,
                    'kode_rekening_id' => $rekeningAset->id,
                    'fullcode' => $rekeningAset->fullcode,
                    'code_1' => $rekeningAset->code_1,
                    'code_2' => $rekeningAset->code_2,
                    'code_3' => $rekeningAset->code_3,
                    'code_4' => $rekeningAset->code_4,
                    'code_5' => $rekeningAset->code_5,
                    'code_6' => $rekeningAset->code_6,
                    'name' => $rekeningAset->name,
                    'saldo_awal' => $preReport->saldo_awal ?? 0,
                    'saldo_akhir' => $saldoAkhir ?? ($accReport->saldo_akhir ?? 0),
                ];

                $arrRekeningAset = DB::table('ref_kode_rekening_complete')
                    ->where('code_1', '1')
                    ->whereIn('code_2', ['1', '2', '3', '5'])
                    ->orderBy('fullcode', 'asc')
                    ->get();
                foreach ($arrRekeningAset as $rekeningAset) {
                    $preReport = DB::table('acc_pre_report')
                        ->where('instance_id', $instance->id)
                        ->where('year', $this->year)
                        ->where('periode_id', $this->periode)
                        ->where('kode_rekening_id', $rekeningAset->id)
                        ->first();
                    $saldoAkhir = 0;

                    $accReport = AccReport::where('instance_id', $instance->id)
                        ->where('year', $this->year)
                        ->where('periode_id', $this->periode)
                        ->where('kode_rekening_id', $rekeningAset->id)
                        ->where('type', 'neraca')
                        ->first();

                    if ($accReport && $accReport->keterangan == 'Manual Update') {
                        $saldoAkhir = $accReport->saldo_akhir;
                    } else {
                        // 1.1.03 - 1.1.08 PIUTANG TARIKAN START
                        if ($rekeningAset->code_1 == 1 && $rekeningAset->code_2 == 1 && $rekeningAset->code_3 == '03') {
                            $saldoAkhir = DB::table('acc_plo_piutang')
                                ->where('type', 'pendapatan_pajak_daerah')
                                ->where('periode_id', $this->periode)
                                ->where('year', $this->year)
                                ->where('instance_id', $instance->id)
                                ->whereNull('deleted_at')
                                ->sum('saldo_akhir');
                        } else if ($rekeningAset->code_1 == 1 && $rekeningAset->code_2 == 1 && $rekeningAset->code_3 == '04') {
                            $saldoAkhir = DB::table('acc_plo_piutang')
                                ->where('type', 'hasil_retribusi_daerah')
                                ->where('periode_id', $this->periode)
                                ->where('year', $this->year)
                                ->where('instance_id', $instance->id)
                                ->whereNull('deleted_at')
                                ->sum('saldo_akhir');
                        } else if ($rekeningAset->code_1 == 1 && $rekeningAset->code_2 == 1 && $rekeningAset->code_3 == '05') {
                            $saldoAkhir = DB::table('acc_plo_piutang')
                                ->where('type', 'hasil_pengelolaan_kekayaan_daerah_yang_dipisahkan')
                                ->where('periode_id', $this->periode)
                                ->where('year', $this->year)
                                ->where('instance_id', $instance->id)
                                ->whereNull('deleted_at')
                                ->sum('saldo_akhir');
                        } else if ($rekeningAset->code_1 == 1 && $rekeningAset->code_2 == 1 && $rekeningAset->code_3 == '06') {
                            $saldoAkhir = DB::table('acc_plo_piutang')
                                ->where('type', 'lain_lain_pad_yang_sah')
                                ->where('periode_id', $this->periode)
                                ->where('year', $this->year)
                                ->where('instance_id', $instance->id)
                                ->whereNull('deleted_at')
                                ->sum('saldo_akhir');
                        } else if ($rekeningAset->code_1 == 1 && $rekeningAset->code_2 == 1 && $rekeningAset->code_3 == '07') {
                            $saldoAkhir = DB::table('acc_plo_piutang')
                                ->where('type', 'transfer_pemerintah_pusat')
                                ->where('periode_id', $this->periode)
                                ->where('year', $this->year)
                                ->where('instance_id', $instance->id)
                                ->whereNull('deleted_at')
                                ->sum('saldo_akhir');
                        } else if ($rekeningAset->code_1 == 1 && $rekeningAset->code_2 == 1 && $rekeningAset->code_3 == '08') {
                            $saldoAkhir = DB::table('acc_plo_piutang')
                                ->where('type', 'transfer_antar_daerah')
                                ->where('periode_id', $this->periode)
                                ->where('year', $this->year)
                                ->where('instance_id', $instance->id)
                                ->whereNull('deleted_at')
                                ->sum('saldo_akhir');
                        }
                        // 1.1.03 - 1.1.08 PIUTANG TARIKAN START

                        // 1.1.10 PENYISIHAN START
                        else if ($rekeningAset->code_1 == 1 && $rekeningAset->code_2 == 1 && $rekeningAset->code_3 == '10') {
                            $saldoAkhir = DB::table('acc_plo_penyisihan')
                                ->where('periode_id', $this->periode)
                                ->where('year', $this->year)
                                ->where('instance_id', $instance->id)
                                ->whereNull('deleted_at')
                                ->sum('jumlah');
                            $saldoAkhir = $saldoAkhir * -1;
                        }
                        // 1.1.10 PENYISIHAN END

                        // 1.1.11 BELANJA BAYAR DIMUKA START
                        else if ($rekeningAset->code_1 == 1 && $rekeningAset->code_2 == 1 && $rekeningAset->code_3 == '11') {
                            $saldoAkhir = DB::table('acc_belanja_bayar_dimuka')
                                ->where('periode_id', $this->periode)
                                ->where('year', $this->year)
                                ->where('instance_id', $instance->id)
                                ->whereNull('deleted_at')
                                ->sum('belum_jatuh_tempo');
                        }
                        // 1.1.11 BELANJA BAYAR DIMUKA END

                        // 1.1.12 PERSEDIAAN START
                        else if ($rekeningAset->code_1 == 1 && $rekeningAset->code_2 == 1 && $rekeningAset->code_3 == '12') {
                            $saldoAkhir = DB::table('acc_persediaan_barang_habis_pakai')
                                ->where('periode_id', $this->periode)
                                ->where('year', $this->year)
                                ->where('instance_id', $instance->id)
                                ->sum('saldo_akhir');
                            $saldoAkhir += DB::table('acc_persediaan_belanja_persediaan_untuk_dijual')
                                ->where('periode_id', $this->periode)
                                ->where('year', $this->year)
                                ->where('instance_id', $instance->id)
                                ->whereNull('deleted_at')
                                ->sum('saldo_akhir');
                        }
                        // 1.1.12 PERSEDIAAN END

                        // 1.1.12 PERSEDIAAN START
                        else if ($rekeningAset->code_1 == 1 && $rekeningAset->code_2 == 1 && $rekeningAset->code_3 == '13') {
                            $saldoAkhir = 0;
                        }
                        // 1.1.12 PERSEDIAAN END

                        // 1.3.01 REKON ASET TANAH START
                        else if ($rekeningAset->code_1 == 1 && $rekeningAset->code_2 == 3 && $rekeningAset->code_3 == '01') {
                            $saldoAkhir = DB::table('acc_rek_as_rekap_opd')
                                ->where('periode_id', $this->periode)
                                ->where('year', $this->year)
                                ->where('instance_id', $instance->id)
                                ->whereNull('deleted_at')
                                ->sum('tanah');
                        }
                        // 1.3.01 REKON ASET TANAH END

                        // 1.3.02 REKON ASET PERALATAN DAN MESIN START
                        else if ($rekeningAset->code_1 == 1 && $rekeningAset->code_2 == 3 && $rekeningAset->code_3 == '02') {
                            $saldoAkhir = DB::table('acc_rek_as_rekap_opd')
                                ->where('periode_id', $this->periode)
                                ->where('year', $this->year)
                                ->where('instance_id', $instance->id)
                                ->whereNull('deleted_at')
                                ->sum('peralatan_mesin');
                        }
                        // 1.3.02 REKON ASET PERALATAN DAN MESIN END

                        // 1.3.03 REKON ASET GEDUNG DAN BANGUNAN START
                        else if ($rekeningAset->code_1 == 1 && $rekeningAset->code_2 == 3 && $rekeningAset->code_3 == '03') {
                            $saldoAkhir = DB::table('acc_rek_as_rekap_opd')
                                ->where('periode_id', $this->periode)
                                ->where('year', $this->year)
                                ->where('instance_id', $instance->id)
                                ->whereNull('deleted_at')
                                ->sum('gedung_bangunan');
                        }
                        // 1.3.03 REKON ASET GEDUNG DAN BANGUNAN END

                        // 1.3.04 REKON ASET Jalan, Jaringan, dan Irigasi START
                        else if ($rekeningAset->code_1 == 1 && $rekeningAset->code_2 == 3 && $rekeningAset->code_3 == '04') {
                            $saldoAkhir = DB::table('acc_rek_as_rekap_opd')
                                ->where('periode_id', $this->periode)
                                ->where('year', $this->year)
                                ->where('instance_id', $instance->id)
                                ->whereNull('deleted_at')
                                ->sum('jalan_jaringan_irigasi');
                        }
                        // 1.3.04 REKON ASET Jalan, Jaringan, dan Irigasi END

                        // 1.3.05 REKON Aset Tetap Lainnya START
                        else if ($rekeningAset->code_1 == 1 && $rekeningAset->code_2 == 3 && $rekeningAset->code_3 == '05') {
                            $saldoAkhir = DB::table('acc_rek_as_rekap_opd')
                                ->where('periode_id', $this->periode)
                                ->where('year', $this->year)
                                ->where('instance_id', $instance->id)
                                ->whereNull('deleted_at')
                                ->sum('aset_tetap_lainnya');
                        }
                        // 1.3.05 REKON Aset Tetap Lainnya END

                        // 1.3.06 REKON Konstruksi Dalam Pengerjaan START
                        else if ($rekeningAset->code_1 == 1 && $rekeningAset->code_2 == 3 && $rekeningAset->code_3 == '06') {
                            $saldoAkhir = DB::table('acc_rek_as_rekap_opd')
                                ->where('periode_id', $this->periode)
                                ->where('year', $this->year)
                                ->where('instance_id', $instance->id)
                                ->whereNull('deleted_at')
                                ->sum('kdp');
                        }
                        // 1.3.06 REKON Konstruksi Dalam Pengerjaan END

                        // 1.3.07 REKON Akumulasi Penyusutan START
                        else if ($rekeningAset->code_1 == 1 && $rekeningAset->code_2 == 3 && $rekeningAset->code_3 == '07') {
                            $saldoAkhir = DB::table('acc_rek_as_penyusutan')
                                ->where('periode_id', $this->periode)
                                ->where('year', $this->year)
                                ->where('instance_id', $instance->id)
                                ->whereNull('deleted_at')
                                ->sum('akumulasi_penyusutan');
                            $saldoAkhir = $saldoAkhir * -1;
                        }
                        // 1.3.07 REKON Akumulasi Penyusutan END

                        // 1.5.03 REKON ASET TAK BERWUJUD START
                        else if ($rekeningAset->code_1 == 1 && $rekeningAset->code_2 == 5 && $rekeningAset->code_3 == '03') {
                            $saldoAkhir = DB::table('acc_rek_as_aset_tak_berwujud')
                                ->where('periode_id', $this->periode)
                                ->where('year', $this->year)
                                ->where('instance_id', $instance->id)
                                ->whereNull('deleted_at')
                                ->sum('saldo_akhir');
                        }
                        // 1.5.03 REKON ASET TAK BERWUJUD END

                        // 1.5.04 REKON ASET LAIN-LAIN START
                        else if ($rekeningAset->code_1 == 1 && $rekeningAset->code_2 == 5 && $rekeningAset->code_3 == '04') {
                            $saldoAkhir = DB::table('acc_rek_as_aset_lain_lain')
                                ->where('periode_id', $this->periode)
                                ->where('year', $this->year)
                                ->where('instance_id', $instance->id)
                                ->whereNull('deleted_at')
                                ->sum('saldo_akhir');
                        }
                        // 1.5.04 REKON ASET LAIN-LAIN END
                    }

                    if (!$accReport && $instance->id) {
                        $accReport = new AccReport();
                        $accReport->periode_id = $this->periode;
                        $accReport->year = $this->year;
                        $accReport->instance_id = $instance->id;
                        $accReport->kode_rekening_id = $rekeningAset->id;
                        $accReport->fullcode = $rekeningAset->fullcode;
                        $accReport->type = 'neraca';
                        $accReport->saldo_awal = $preReport->saldo_awal ?? 0;
                        $accReport->saldo_akhir = $saldoAkhir ?? 0;
                        $accReport->kenaikan_penurunan = isset($saldoAkhir) ? ($saldoAkhir - ($preReport->saldo_awal ?? 0)) : 0;
                        $accReport->percent = isset($saldoAkhir) ? (($preReport->saldo_awal ?? 0) == 0 ? 0 : (($saldoAkhir - ($preReport->saldo_awal ?? 0)) / ($preReport->saldo_awal ?? 0)) * 100) : 0;
                        $accReport->save();
                    } else {
                        $accReport->saldo_akhir = $saldoAkhir ?? 0;
                        $accReport->kenaikan_penurunan = isset($saldoAkhir) ? ($saldoAkhir - ($preReport->saldo_awal ?? 0)) : 0;
                        $accReport->percent = isset($saldoAkhir) ? (($preReport->saldo_awal ?? 0) == 0 ? 0 : (($saldoAkhir - ($preReport->saldo_awal ?? 0)) / ($preReport->saldo_awal ?? 0)) * 100) : 0;
                        $accReport->save();
                    }
                }

                // if ($preReport) {
                $datas[] = [
                    'id' => $insertToReport->id ?? null,
                    'kode_rekening_id' => $rekeningAset->id,
                    'fullcode' => $rekeningAset->fullcode,
                    'code_1' => $rekeningAset->code_1,
                    'code_2' => $rekeningAset->code_2,
                    'code_3' => $rekeningAset->code_3,
                    'code_4' => $rekeningAset->code_4,
                    'code_5' => $rekeningAset->code_5,
                    'code_6' => $rekeningAset->code_6,
                    'name' => $rekeningAset->name,
                    'saldo_awal' => $preReport->saldo_awal ?? 0,
                    'saldo_akhir' => $saldoAkhir ?? ($insertToReport->saldo_akhir ?? 0),
                ];
                // }
                // KODE REKENING 1 - ASET - END

                // KODE REKENING 2 - KEWAJIBAN - START
                $rekeningKewajiban = DB::table('ref_kode_rekening_complete')
                    ->where('code_1', '2')
                    ->where('code_2', null)
                    ->where('code_3', null)
                    ->where('code_4', null)
                    ->where('code_5', null)
                    ->where('code_6', null)
                    ->first();
                $preReport = DB::table('acc_pre_report')
                    ->where('instance_id', $instance->id)
                    ->where('year', $this->year)
                    ->where('periode_id', $this->periode)
                    ->where('kode_rekening_id', $rekeningKewajiban->id)
                    ->first();

                $accReport = AccReport::where('instance_id', $instance->id)
                    ->where('year', $this->year)
                    ->where('periode_id', $this->periode)
                    ->where('kode_rekening_id', $rekeningKewajiban->id)
                    ->where('type', 'neraca')
                    ->first();
                if (!$accReport && $instance->id) {
                    $accReport = new AccReport();
                    $accReport->periode_id = $this->periode;
                    $accReport->year = $this->year;
                    $accReport->instance_id = $instance->id;
                    $accReport->kode_rekening_id = $rekeningKewajiban->id;
                    $accReport->fullcode = $rekeningKewajiban->fullcode;
                    $accReport->type = 'neraca';
                    $accReport->saldo_awal = $preReport->saldo_awal ?? 0;
                    $accReport->saldo_akhir = 0;
                    $accReport->kenaikan_penurunan = 0;
                    $accReport->percent = 0;
                    $accReport->save();
                } else {
                    DB::table('acc_report')
                        ->where('instance_id', $instance->id)
                        ->where('year', $this->year)
                        ->where('periode_id', $this->periode)
                        ->where('kode_rekening_id', $rekeningKewajiban->id)
                        ->where('type', 'neraca')
                        ->update([
                            'saldo_awal' => $preReport->saldo_awal ?? 0,
                        ]);
                }

                $datas[] = [
                    'id' => $accReport->id ?? null,
                    'kode_rekening_id' => $rekeningKewajiban->id,
                    'fullcode' => $rekeningKewajiban->fullcode,
                    'code_1' => $rekeningKewajiban->code_1,
                    'code_2' => $rekeningKewajiban->code_2,
                    'code_3' => $rekeningKewajiban->code_3,
                    'code_4' => $rekeningKewajiban->code_4,
                    'code_5' => $rekeningKewajiban->code_5,
                    'code_6' => $rekeningKewajiban->code_6,
                    'name' => $rekeningKewajiban->name,
                    'saldo_awal' => $preReport->saldo_awal ?? 0,
                    'saldo_akhir' => $saldoAkhir ?? ($accReport->saldo_akhir ?? 0),
                ];

                $arrRekeningKewajiban = DB::table('ref_kode_rekening_complete')
                    ->where('code_1', '2')
                    ->whereIn('code_2', ['1', '2'])
                    ->orderBy('fullcode', 'asc')
                    ->get();
                foreach ($arrRekeningKewajiban as $rekeningKewajiban) {
                    $preReport = DB::table('acc_pre_report')
                        ->where('instance_id', $instance->id)
                        ->where('year', $this->year)
                        ->where('periode_id', $this->periode)
                        ->where('kode_rekening_id', $rekeningKewajiban->id)
                        ->first();
                    $accReport = AccReport::where('instance_id', $instance->id)
                        ->where('year', $this->year)
                        ->where('periode_id', $this->periode)
                        ->where('kode_rekening_id', $rekeningKewajiban->id)
                        ->where('type', 'neraca')
                        ->first();
                    $saldoAkhir = 0;
                    if ($accReport && $accReport->keterangan == 'Manual Update') {
                        $saldoAkhir = $accReport->saldo_akhir;
                    } else {
                        // 2.1.05 PENDAPATAN LO - PENDAPATAN DITERIMA DIMUKA START
                        if ($rekeningKewajiban->code_1 == 2 && $rekeningKewajiban->code_2 == 1 && $rekeningKewajiban->code_3 == '05') {
                            $saldoAkhir = DB::table('acc_plo_pdd')
                                ->where('periode_id', $this->periode)
                                ->where('year', $this->year)
                                ->where('instance_id', $instance->id)
                                ->whereNull('deleted_at')
                                ->sum('pendapatan_diterima_dimuka_akhir');
                        }
                        // 2.1.05 PENDAPATAN LO - PENDAPATAN DITERIMA DIMUKA END

                        // 2.1.06 HUTANG BELANJA START
                        else if ($rekeningKewajiban->code_1 == 2 && $rekeningKewajiban->code_2 == 1 && $rekeningKewajiban->code_3 == '06') {
                            // $saldoAkhir = DB::table('acc_hutang_belanja')
                            //     ->where('periode_id', $this->periode)
                            //     ->where('year', $this->year)
                            //     ->where('instance_id', $instance->id)
                            // ->whereNull('deleted_at')
                            //     ->sum('total_hutang_belanja');
                            // ------------- BELUM SELESAI BAGIAN HUTANG BELANJA -------------
                            $saldoAkhir = 0;
                        }
                        // 2.1.06 HUTANG BELANJA END
                    }


                    if (!$accReport && $instance->id) {
                        $accReport = new AccReport();
                        $accReport->periode_id = $this->periode;
                        $accReport->year = $this->year;
                        $accReport->instance_id = $instance->id;
                        $accReport->kode_rekening_id = $rekeningKewajiban->id;
                        $accReport->fullcode = $rekeningKewajiban->fullcode;
                        $accReport->type = 'neraca';
                        $accReport->saldo_awal = $preReport->saldo_awal ?? 0;
                        $accReport->saldo_akhir = $saldoAkhir ?? 0;
                        $accReport->kenaikan_penurunan = isset($saldoAkhir) ? ($saldoAkhir - ($preReport->saldo_awal ?? 0)) : 0;
                        $accReport->percent = isset($saldoAkhir) ? (($preReport->saldo_awal ?? 0) == 0 ? 0 : (($saldoAkhir - ($preReport->saldo_awal ?? 0)) / ($preReport->saldo_awal ?? 0)) * 100) : 0;
                        $accReport->save();
                    } else {
                        if (isset($saldoAkhir) && $accReport && $saldoAkhir != $accReport->saldo_akhir) {
                            $accReport->saldo_akhir = $saldoAkhir ?? 0;
                            $accReport->kenaikan_penurunan = isset($saldoAkhir) ? ($saldoAkhir - ($preReport->saldo_awal ?? 0)) : 0;
                            $accReport->percent = isset($saldoAkhir) ? (($preReport->saldo_awal ?? 0) == 0 ? 0 : (($saldoAkhir - ($preReport->saldo_awal ?? 0)) / ($preReport->saldo_awal ?? 0)) * 100) : 0;
                            $accReport->save();
                        }
                    }

                    // if ($preReport) {
                    $datas[] = [
                        'id' => $insertToReport->id ?? null,
                        'kode_rekening_id' => $rekeningKewajiban->id,
                        'fullcode' => $rekeningKewajiban->fullcode,
                        'code_1' => $rekeningKewajiban->code_1,
                        'code_2' => $rekeningKewajiban->code_2,
                        'code_3' => $rekeningKewajiban->code_3,
                        'code_4' => $rekeningKewajiban->code_4,
                        'code_5' => $rekeningKewajiban->code_5,
                        'code_6' => $rekeningKewajiban->code_6,
                        'name' => $rekeningKewajiban->name,
                        'saldo_awal' => $preReport->saldo_awal ?? 0,
                        'saldo_akhir' => $saldoAkhir ?? ($insertToReport->saldo_akhir ?? 0),
                    ];
                    // }
                }
                // KODE REKENING 2 - KEWAJIBAN - END

                // KODE REKENING 3 - EKUITAS - START
                $rekeningEkuitas = DB::table('ref_kode_rekening_complete')
                    ->where('code_1', '3')
                    ->where('code_2', null)
                    ->where('code_3', null)
                    ->where('code_4', null)
                    ->where('code_5', null)
                    ->where('code_6', null)
                    ->first();
                $preReport = DB::table('acc_pre_report')
                    ->where('instance_id', $instance->id)
                    ->where('year', $this->year)
                    ->where('periode_id', $this->periode)
                    ->where('kode_rekening_id', $rekeningEkuitas->id)
                    ->first();
                $accReport = AccReport::where('instance_id', $instance->id)
                    ->where('year', $this->year)
                    ->where('periode_id', $this->periode)
                    ->where('kode_rekening_id', $rekeningEkuitas->id)
                    ->where('type', 'neraca')
                    ->first();
                if (!$accReport && $instance->id) {
                    $accReport = new AccReport();
                    $accReport->periode_id = $this->periode;
                    $accReport->year = $this->year;
                    $accReport->instance_id = $instance->id;
                    $accReport->kode_rekening_id = $rekeningEkuitas->id;
                    $accReport->fullcode = $rekeningEkuitas->fullcode;
                    $accReport->type = 'neraca';
                    $accReport->saldo_awal = $preReport->saldo_awal ?? 0;
                    $accReport->saldo_akhir = 0;
                    $accReport->kenaikan_penurunan = 0;
                    $accReport->percent = 0;
                    $accReport->save();
                } else {
                    $accReport->saldo_awal = $preReport->saldo_awal ?? 0;
                    $accReport->save();
                }
                $saldoAkhir = 0;

                $datas[] = [
                    'id' => $insertToReport->id ?? null,
                    'kode_rekening_id' => $rekeningEkuitas->id,
                    'fullcode' => $rekeningEkuitas->fullcode,
                    'code_1' => $rekeningEkuitas->code_1,
                    'code_2' => $rekeningEkuitas->code_2,
                    'code_3' => $rekeningEkuitas->code_3,
                    'code_4' => $rekeningEkuitas->code_4,
                    'code_5' => $rekeningEkuitas->code_5,
                    'code_6' => $rekeningEkuitas->code_6,
                    'name' => $rekeningEkuitas->name,
                    'saldo_awal' => $preReport->saldo_awal ?? 0,
                    'saldo_akhir' => $saldoAkhir ?? ($insertToReport->saldo_akhir ?? 0),
                ];

                $arrRekeningEkuitas = DB::table('ref_kode_rekening_complete')
                    ->where('code_1', '3')
                    ->whereIn('code_2', ['1'])
                    ->orderBy('fullcode', 'asc')
                    ->get();

                foreach ($arrRekeningEkuitas as $rekeningEkuitas) {
                    $preReport = DB::table('acc_pre_report')
                        ->where('instance_id', $instance->id)
                        ->where('year', $this->year)
                        ->where('periode_id', $this->periode)
                        ->where('kode_rekening_id', $rekeningEkuitas->id)
                        ->first();
                    $accReport = AccReport::where('instance_id', $instance->id)
                        ->where('year', $this->year)
                        ->where('periode_id', $this->periode)
                        ->where('kode_rekening_id', $rekeningEkuitas->id)
                        ->where('type', 'neraca')
                        ->first();
                    $saldoAkhir = 0;
                    if ($accReport && $accReport->keterangan == 'Manual Update') {
                        $saldoAkhir = $accReport->saldo_akhir;
                    } else {
                        if ($rekeningEkuitas->code_1 == 3 && $rekeningEkuitas->code_2 == 1) {
                            $saldoAkhir = 0;
                        }
                    }

                    if (!$accReport && $instance->id) {
                        $accReport = new AccReport();
                        $accReport->periode_id = $this->periode;
                        $accReport->year = $this->year;
                        $accReport->instance_id = $instance->id;
                        $accReport->kode_rekening_id = $rekeningEkuitas->id;
                        $accReport->fullcode = $rekeningEkuitas->fullcode;
                        $accReport->type = 'neraca';
                        $accReport->saldo_awal = $preReport->saldo_awal ?? 0;
                        $accReport->saldo_akhir = 0;
                        $accReport->kenaikan_penurunan = 0;
                        $accReport->percent = 0;
                        $accReport->save();
                    } else {
                        if (isset($saldoAkhir) && $accReport && $saldoAkhir != $accReport->saldo_akhir) {
                            $accReport->saldo_akhir = $saldoAkhir ?? 0;
                            $accReport->kenaikan_penurunan = isset($saldoAkhir) ? ($saldoAkhir - ($preReport->saldo_awal ?? 0)) : 0;
                            $accReport->percent = isset($saldoAkhir) ? (($preReport->saldo_awal ?? 0) == 0 ? 0 : (($saldoAkhir - ($preReport->saldo_awal ?? 0)) / ($preReport->saldo_awal ?? 0)) * 100) : 0;
                            $accReport->save();
                        }
                    }

                    // if ($preReport) {
                    $datas[] = [
                        'id' => $insertToReport->id ?? null,
                        'kode_rekening_id' => $rekeningEkuitas->id,
                        'fullcode' => $rekeningEkuitas->fullcode,
                        'code_1' => $rekeningEkuitas->code_1,
                        'code_2' => $rekeningEkuitas->code_2,
                        'code_3' => $rekeningEkuitas->code_3,
                        'code_4' => $rekeningEkuitas->code_4,
                        'code_5' => $rekeningEkuitas->code_5,
                        'code_6' => $rekeningEkuitas->code_6,
                        'name' => $rekeningEkuitas->name,
                        'saldo_awal' => $preReport->saldo_awal ?? 0,
                        'saldo_akhir' => $saldoAkhir ?? ($insertToReport->saldo_akhir ?? 0),
                    ];
                    // }
                }
                // KODE REKENING 3 - EKUITAS - END

                // SUMMARIES START
                $datas = collect($datas);

                // sum level 3 to level 2
                $datas = $datas->map(function ($item) use ($datas, $instance) {
                    if ($item['code_3'] == null) {
                        $item['saldo_akhir'] = $datas
                            ->filter(function ($value, $key) use ($item) {
                                return strlen($value['fullcode']) == 6;
                            })
                            ->where('code_1', $item['code_1'])
                            ->where('code_2', $item['code_2'])
                            ->where('code_3', '!=', null)
                            ->sum('saldo_akhir');

                        $item['saldo_awal'] = $datas
                            ->filter(function ($value, $key) use ($item) {
                                return strlen($value['fullcode']) == 6;
                            })
                            ->where('code_1', $item['code_1'])
                            ->where('code_2', $item['code_2'])
                            ->where('code_3', '!=', null)
                            ->sum('saldo_awal');
                    }
                    // update to acc_report
                    $accReport = AccReport::where('instance_id', $instance->id)
                        ->where('year', $this->year)
                        ->where('periode_id', $this->periode)
                        ->where('kode_rekening_id', $item['kode_rekening_id'])
                        ->where('type', 'neraca')
                        ->first();
                    if ($accReport) {
                        $accReport->saldo_akhir = $item['saldo_akhir'];
                        $accReport->kenaikan_penurunan = $item['saldo_akhir'] - $item['saldo_awal'];
                        $accReport->percent = $item['saldo_awal'] == 0 ? 0 : (($item['saldo_akhir'] - $item['saldo_awal']) / $item['saldo_awal']) * 100;
                        $accReport->save();
                    }
                    return $item;
                });
                // sum level 2 to level 1
                $datas = $datas->map(function ($item) use ($datas, $instance) {
                    if ($item['code_2'] == null) {
                        $item['saldo_akhir'] = $datas
                            ->filter(function ($value, $key) use ($item) {
                                return strlen($value['fullcode']) == 3;
                            })
                            ->where('code_1', $item['code_1'])
                            ->where('code_2', '!=', null)
                            ->sum('saldo_akhir');

                        $item['saldo_awal'] = $datas
                            ->filter(function ($value, $key) use ($item) {
                                return strlen($value['fullcode']) == 3;
                            })
                            ->where('code_1', $item['code_1'])
                            ->where('code_2', '!=', null)
                            ->sum('saldo_awal');
                    }

                    $accReport = AccReport::where('instance_id', $instance->id)
                        ->where('year', $this->year)
                        ->where('periode_id', $this->periode)
                        ->where('kode_rekening_id', $item['kode_rekening_id'])
                        ->where('type', 'neraca')
                        ->first();
                    if ($accReport) {
                        $accReport->saldo_akhir = $item['saldo_akhir'];
                        $accReport->kenaikan_penurunan = $item['saldo_akhir'] - $item['saldo_awal'];
                        $accReport->percent = $item['saldo_awal'] == 0 ? 0 : (($item['saldo_akhir'] - $item['saldo_awal']) / $item['saldo_awal']) * 100;
                        $accReport->save();
                    }
                    return $item;
                });

                Cache::set('acc_report_neraca_updated_at.' . $instance->id, Carbon::now(), 60 * 24);
            }

            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            throw new \Exception($e->getMessage() . ' - ' . $e->getLine());
            dd($e->getMessage() . ' - ' . $e->getLine());
        }
    }

    public function failed(\Exception $e)
    {
        // Send user notification of failure, etc...
        dd($e->getMessage() . ' - ' . $e->getLine());
    }
}
