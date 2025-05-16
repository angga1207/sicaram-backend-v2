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

class AccountancyReportLO implements ShouldQueue
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
                // KODE REKENING 7 PENDAPATAN - START
                $rekeningPendapatan = DB::table('ref_kode_rekening_complete')
                    ->where('code_1', '7')
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
                    ->where('kode_rekening_id', $rekeningPendapatan->id)
                    ->first();

                $accReport = AccReport::where('instance_id', $instance->id)
                    ->where('year', $this->year)
                    ->where('periode_id', $this->periode)
                    ->where('kode_rekening_id', $rekeningPendapatan->id)
                    ->where('type', 'lo')
                    ->first();

                if (!$accReport && $instance) {
                    $accReport = new accReport();
                    $accReport->periode_id = $this->periode;
                    $accReport->year = $this->year;
                    $accReport->instance_id = $instance->id;
                    $accReport->kode_rekening_id = $rekeningPendapatan->id;
                    $accReport->fullcode = $rekeningPendapatan->fullcode;
                    $accReport->type = 'lo';
                    $accReport->saldo_awal = $preReport->saldo_awal ?? 0;
                    $accReport->saldo_akhir = 0;
                    $accReport->kenaikan_penurunan = 0;
                    $accReport->percent = 0;
                    $accReport->save();
                } else {
                    $accReport->saldo_awal = $preReport->saldo_awal ?? 0;
                    $accReport->save();
                }


                $datas[] = [
                    'id' => $accReport->id ?? null,
                    'kode_rekening_id' => $rekeningPendapatan->id,
                    'fullcode' => $rekeningPendapatan->fullcode,
                    'code_1' => $rekeningPendapatan->code_1,
                    'code_2' => $rekeningPendapatan->code_2,
                    'code_3' => $rekeningPendapatan->code_3,
                    'code_4' => $rekeningPendapatan->code_4,
                    'code_5' => $rekeningPendapatan->code_5,
                    'code_6' => $rekeningPendapatan->code_6,
                    'name' => $rekeningPendapatan->name,
                    'saldo_awal' => $preReport->saldo_awal ?? 0,
                    'saldo_akhir' => $saldoAkhir ?? ($accReport->saldo_akhir ?? 0),
                ];
                $arrRekeningPendapatan = DB::table('ref_kode_rekening_complete')
                    ->where('code_1', '7')
                    ->where(function ($query) use ($instance) {
                        if ($instance->id == 9) {
                            return $query->whereIn('code_2', ['1', '2', '3', '4']);
                        } else {
                            return $query->whereIn('code_2', ['1', '4']);
                        }
                    })
                    ->orderBy('fullcode', 'asc')
                    ->get();

                foreach ($arrRekeningPendapatan as $rekeningPendapatan) {
                    $preReport = DB::table('acc_pre_report')
                        ->where('instance_id', $instance->id)
                        ->where('year', $this->year)
                        ->where('periode_id', $this->periode)
                        ->where('kode_rekening_id', $rekeningPendapatan->id)
                        ->whereNull('deleted_at')
                        ->first();

                    $accReport = AccReport::where('instance_id', $instance->id)
                        ->where('year', $this->year)
                        ->where('periode_id', $this->periode)
                        ->where('kode_rekening_id', $rekeningPendapatan->id)
                        ->where('type', 'lo')
                        ->first();

                    $saldoAkhir = 0;

                    if ($accReport && $accReport->keterangan == 'Manual Update') {
                        $saldoAkhir = $accReport->saldo_akhir;
                    } else {
                        $kodeRek4 = $rekeningPendapatan->fullcode;
                        $kodeRek4 = substr_replace($kodeRek4, '4', 0, 1);

                        $rekeningRetribusi = DB::table('ref_kode_rekening_complete')
                            ->where('fullcode', $kodeRek4)
                            ->first();
                        if ($rekeningRetribusi) {
                            $saldoAkhir = DB::table('acc_plo_lo_ta')
                                ->where('periode_id', $this->periode)
                                ->where('year', $this->year)
                                ->where('instance_id', $instance->id)
                                ->where('kode_rekening_id', $rekeningRetribusi->id)
                                ->whereNull('deleted_at')
                                ->sum('laporan_operasional');
                        } else {
                            $saldoAkhir = 0;
                        }
                    }

                    if (!$accReport && $instance) {
                        $accReport = new accReport();
                        $accReport->periode_id = $this->periode;
                        $accReport->year = $this->year;
                        $accReport->instance_id = $instance->id;
                        $accReport->kode_rekening_id = $rekeningPendapatan->id;
                        $accReport->fullcode = $rekeningPendapatan->fullcode;
                        $accReport->type = 'lo';
                        $accReport->saldo_awal = $preReport->saldo_awal ?? 0;
                        $accReport->saldo_akhir = $saldoAkhir ?? 0;
                        $accReport->kenaikan_penurunan = isset($saldoAkhir) ? ($saldoAkhir - ($preReport->saldo_awal ?? 0)) : 0;
                        $accReport->percent = ($preReport->saldo_awal ?? 0) == 0 ? 0 : (($saldoAkhir - ($preReport->saldo_awal ?? 0)) / ($preReport->saldo_awal ?? 0)) * 100;
                        $accReport->save();
                    } else {
                        if (isset($saldoAkhir) && $accReport && $saldoAkhir != $accReport->saldo_akhir) {
                            $accReport->saldo_akhir = $saldoAkhir ?? 0;
                            $accReport->kenaikan_penurunan = isset($saldoAkhir) ? ($saldoAkhir - ($preReport->saldo_awal ?? 0)) : 0;
                            $accReport->percent = ($preReport->saldo_awal ?? 0) == 0 ? 0 : (($saldoAkhir - ($preReport->saldo_awal ?? 0)) / ($preReport->saldo_awal ?? 0)) * 100;
                            $accReport->save();
                        }
                    }

                    // if ($preReport) {
                    $datas[] = [
                        'id' => $accReport->id ?? null,
                        'kode_rekening_id' => $rekeningPendapatan->id,
                        'fullcode' => $rekeningPendapatan->fullcode,
                        'code_1' => $rekeningPendapatan->code_1,
                        'code_2' => $rekeningPendapatan->code_2,
                        'code_3' => $rekeningPendapatan->code_3,
                        'code_4' => $rekeningPendapatan->code_4,
                        'code_5' => $rekeningPendapatan->code_5,
                        'code_6' => $rekeningPendapatan->code_6,
                        'name' => $rekeningPendapatan->name,
                        'saldo_awal' => $preReport->saldo_awal ?? 0,
                        'saldo_akhir' => $saldoAkhir ?? ($accReport->saldo_akhir ?? 0),
                    ];
                    // }
                }
                // KODE REKENING 7 PENDAPATAN - END


                // KODE REKENING 8 BEBAN - START
                $rekeningBeban = DB::table('ref_kode_rekening_complete')
                    ->where('code_1', '8')
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
                    ->where('kode_rekening_id', $rekeningBeban->id)
                    ->where('type', 'lo')
                    ->whereNull('deleted_at')
                    ->first();


                $accReport = AccReport::where('instance_id', $instance->id)
                    ->where('year', $this->year)
                    ->where('periode_id', $this->periode)
                    ->where('kode_rekening_id', $rekeningBeban->id)
                    ->where('type', 'lo')
                    ->first();

                if (!$accReport && $instance) {
                    $accReport = new AccReport();
                    $accReport->periode_id = $this->periode;
                    $accReport->year = $this->year;
                    $accReport->instance_id = $instance->id;
                    $accReport->kode_rekening_id = $rekeningBeban->id;
                    $accReport->fullcode = $rekeningBeban->fullcode;
                    $accReport->type = 'lo';
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
                    'id' => $accReport->id ?? null,
                    'kode_rekening_id' => $rekeningBeban->id,
                    'fullcode' => $rekeningBeban->fullcode,
                    'code_1' => $rekeningBeban->code_1,
                    'code_2' => $rekeningBeban->code_2,
                    'code_3' => $rekeningBeban->code_3,
                    'code_4' => $rekeningBeban->code_4,
                    'code_5' => $rekeningBeban->code_5,
                    'code_6' => $rekeningBeban->code_6,
                    'name' => $rekeningBeban->name,
                    'saldo_awal' => $preReport->saldo_awal ?? 0,
                    'saldo_akhir' => $saldoAkhir ?? ($accReport->saldo_akhir ?? 0),
                ];
                $arrRekeningBeban = DB::table('ref_kode_rekening_complete')
                    ->where('code_1', '8')
                    ->whereIn('code_2', ['1', '2', '3', '5'])
                    ->orderBy('fullcode', 'asc')
                    ->whereNull('deleted_at')
                    ->get();

                foreach ($arrRekeningBeban as $rekeningBeban) {
                    $preReport = DB::table('acc_pre_report')
                        ->where('instance_id', $instance->id)
                        ->where('year', $this->year)
                        ->where('periode_id', $this->periode)
                        ->where('kode_rekening_id', $rekeningBeban->id)
                        ->whereNull('deleted_at')
                        ->first();

                    $accReport = AccReport::where('instance_id', $instance->id)
                        ->where('year', $this->year)
                        ->where('periode_id', $this->periode)
                        ->where('kode_rekening_id', $rekeningBeban->id)
                        ->where('type', 'lo')
                        ->first();

                    if ($accReport && $accReport->keterangan == 'Manual Update') {
                        $saldoAkhir = $accReport->saldo_akhir;
                    } else if ($accReport) {
                        $kodeRek5 = $rekeningBeban->fullcode;
                        $kodeRek5 = substr_replace($kodeRek5, '5', 0, 1);

                        $rek5 = DB::table('ref_kode_rekening_complete')
                            ->where('fullcode', $kodeRek5)
                            ->first();

                        if ($rek5) {
                            if ($rek5->code_2 == '1' && $rek5->code_3 == '01') {
                                $saldoAkhir = DB::table('acc_blo_pegawai')
                                    ->where('periode_id', $this->periode)
                                    ->where('year', $this->year)
                                    ->where('instance_id', $instance->id)
                                    ->where('kode_rekening_id', $rek5->id)
                                    ->whereNull('deleted_at')
                                    ->sum('beban_lo');

                                // } else if ($rek5->code_2 == '1' && $rek5->code_3 == '02') {
                                //     $saldoAkhir = DB::table('acc_blo_jasa')
                                //         ->where('periode_id', $this->periode)
                                //         ->where('year', $this->year)
                                //         ->when($instance, function ($query) use ($this) {
                                //             return $query->where('instance_id', $instance->id);
                                //         })
                                //         ->where('kode_rekening_id', $rek5->id)
                                //         ->sum('beban_lo');

                                // TURUN KE KE LEVEL 4 SESUAI DENGAN KERTAS KERJA BEBAN LAPORAN OPERSIONAL
                                // 8.1.02.**
                                // Lihat di Level 4

                            } elseif ($rek5->code_2 == '1' && $rek5->code_3 == '02' && $rek5->code_4 == '01') {
                                $saldoAkhir = DB::table('acc_blo_persediaan')
                                    ->where('periode_id', $this->periode)
                                    ->where('year', $this->year)
                                    ->where('instance_id', $instance->id)
                                    ->where('kode_rekening_id', $rek5->id)
                                    // ->whereNull('deleted_at')
                                    ->sum('beban_lo');
                            } elseif ($rek5->code_2 == '1' && $rek5->code_3 == '02' && $rek5->code_4 == '02') {
                                $saldoAkhir = DB::table('acc_blo_jasa')
                                    ->where('periode_id', $this->periode)
                                    ->where('year', $this->year)
                                    ->where('instance_id', $instance->id)
                                    ->where('kode_rekening_id', $rek5->id)
                                    // ->whereNull('deleted_at')
                                    // ->count();
                                    ->sum('beban_lo');
                            } elseif ($rek5->code_2 == '1' && $rek5->code_3 == '02' && $rek5->code_4 == '03') {
                                $saldoAkhir = DB::table('acc_blo_pemeliharaan')
                                    ->where('periode_id', $this->periode)
                                    ->where('year', $this->year)
                                    ->where('instance_id', $instance->id)
                                    ->where('kode_rekening_id', $rek5->id)
                                    // ->whereNull('deleted_at')
                                    ->sum('beban_lo');
                            } elseif ($rek5->code_2 == '1' && $rek5->code_3 == '02' && $rek5->code_4 == '04') {
                                $saldoAkhir = DB::table('acc_blo_perjadin')
                                    ->where('periode_id', $this->periode)
                                    ->where('year', $this->year)
                                    ->where('instance_id', $instance->id)
                                    ->where('kode_rekening_id', $rek5->id)
                                    ->sum('beban_lo');
                            } elseif ($rek5->code_2 == '1' && $rek5->code_3 == '02' && $rek5->code_4 == '05') {
                                $saldoAkhir = DB::table('acc_blo_uang_jasa_diserahkan')
                                    ->where('periode_id', $this->periode)
                                    ->where('year', $this->year)
                                    ->where('instance_id', $instance->id)
                                    ->where('kode_rekening_id', $rek5->id)
                                    // ->whereNull('deleted_at')
                                    ->sum('beban_lo');
                            } else {
                                $saldoAkhir = 0;
                            }
                        } else {
                            $saldoAkhir = 0;
                        }
                    }

                    if (!$accReport && $instance) {
                        $accReport = new AccReport();
                        $accReport->periode_id = $this->periode;
                        $accReport->year = $this->year;
                        $accReport->instance_id = $instance->id;
                        $accReport->kode_rekening_id = $rekeningBeban->id;
                        $accReport->fullcode = $rekeningBeban->fullcode;
                        $accReport->type = 'lo';
                        $accReport->saldo_awal = $preReport->saldo_awal ?? 0;
                        $accReport->saldo_akhir = $saldoAkhir ?? 0;
                        $accReport->kenaikan_penurunan = isset($saldoAkhir) ? ($saldoAkhir - ($preReport->saldo_awal ?? 0)) : 0;
                        $accReport->percent = ($preReport->saldo_awal ?? 0) == 0 ? 0 : (($saldoAkhir - ($preReport->saldo_awal ?? 0)) / ($preReport->saldo_awal ?? 0)) * 100;
                        $accReport->save();
                    } else {
                        if (isset($saldoAkhir) && $accReport && $saldoAkhir != $accReport->saldo_akhir) {
                            $accReport->saldo_akhir = $saldoAkhir ?? 0;
                            $accReport->kenaikan_penurunan = isset($saldoAkhir) ? ($saldoAkhir - ($preReport->saldo_awal ?? 0)) : 0;
                            $accReport->percent = ($preReport->saldo_awal ?? 0) == 0 ? 0 : (($saldoAkhir - ($preReport->saldo_awal ?? 0)) / ($preReport->saldo_awal ?? 0)) * 100;
                            $accReport->save();
                        }
                    }

                    // if ($preReport) {
                    $datas[] = [
                        'id' => $insertToReport->id ?? null,
                        'kode_rekening_id' => $rekeningBeban->id,
                        'fullcode' => $rekeningBeban->fullcode,
                        'code_1' => $rekeningBeban->code_1,
                        'code_2' => $rekeningBeban->code_2,
                        'code_3' => $rekeningBeban->code_3,
                        'code_4' => $rekeningBeban->code_4,
                        'code_5' => $rekeningBeban->code_5,
                        'code_6' => $rekeningBeban->code_6,
                        'name' => $rekeningBeban->name,
                        'saldo_awal' => $preReport->saldo_awal ?? 0,
                        'saldo_akhir' => $saldoAkhir ?? ($insertToReport->saldo_akhir ?? 0),
                    ];
                    // }
                }
                // KODE REKENING 8 BEBAN - END


                // SUMMARIES START
                $datas = collect($datas);

                // sum level 6 to level 5
                $datas = $datas->map(function ($item) use ($datas, $instance) {
                    if ($item['code_6'] == null) {
                        $item['saldo_akhir'] = $datas
                            ->filter(function ($value, $key) use ($item) {
                                return strlen($value['fullcode']) == 17;
                            })
                            ->where('code_1', $item['code_1'])
                            ->where('code_2', $item['code_2'])
                            ->where('code_3', $item['code_3'])
                            ->where('code_4', $item['code_4'])
                            ->where('code_5', $item['code_5'])
                            ->where('code_6', '!=', null)
                            ->sum('saldo_akhir');
                    }

                    // update to acc_report
                    $insertToReport = DB::table('acc_report')
                        ->where('instance_id', $instance->id)
                        ->where('year', $this->year)
                        ->where('periode_id', $this->periode)
                        ->where('kode_rekening_id', $item['kode_rekening_id'])
                        ->where('type', 'lo')
                        ->first();

                    $accReport = AccReport::where('instance_id', $instance->id)
                        ->where('year', $this->year)
                        ->where('periode_id', $this->periode)
                        ->where('kode_rekening_id', $item['kode_rekening_id'])
                        ->where('type', 'lo')
                        ->first();
                    if ($accReport) {
                        $accReport->saldo_akhir = $item['saldo_akhir'];
                        $accReport->kenaikan_penurunan = $item['saldo_akhir'] - $item['saldo_awal'];
                        $accReport->percent = $item['saldo_awal'] == 0 ? 0 : (($item['saldo_akhir'] - $item['saldo_awal']) / $item['saldo_awal']) * 100;
                        $accReport->save();
                    }
                    return $item;
                });

                // sum level 5 to level 4
                $datas = $datas->map(function ($item) use ($datas, $instance) {
                    if ($item['code_5'] == null) {
                        $item['saldo_akhir'] = $datas
                            ->filter(function ($value, $key) use ($item) {
                                return strlen($value['fullcode']) == 12;
                            })
                            ->where('code_1', $item['code_1'])
                            ->where('code_2', $item['code_2'])
                            ->where('code_3', $item['code_3'])
                            ->where('code_4', $item['code_4'])
                            ->where('code_5', '!=', null)
                            ->sum('saldo_akhir');
                    }

                    // update to acc_report
                    $accReport = AccReport::where('instance_id', $instance->id)
                        ->where('year', $this->year)
                        ->where('periode_id', $this->periode)
                        ->where('kode_rekening_id', $item['kode_rekening_id'])
                        ->where('type', 'lo')
                        ->first();
                    if ($accReport) {
                        $accReport->saldo_akhir = $item['saldo_akhir'];
                        $accReport->kenaikan_penurunan = $item['saldo_akhir'] - $item['saldo_awal'];
                        $accReport->percent = $item['saldo_awal'] == 0 ? 0 : (($item['saldo_akhir'] - $item['saldo_awal']) / $item['saldo_awal']) * 100;
                        $accReport->save();
                    }
                    return $item;
                });
                // sum level 4 to level 3
                $datas = $datas->map(function ($item) use ($datas, $instance) {
                    if ($item['code_4'] == null) {
                        $item['saldo_akhir'] = $datas
                            ->filter(function ($value, $key) use ($item) {
                                return strlen($value['fullcode']) == 9;
                            })
                            ->where('code_1', $item['code_1'])
                            ->where('code_2', $item['code_2'])
                            ->where('code_3', $item['code_3'])
                            ->where('code_4', '!=', null)
                            ->sum('saldo_akhir');
                    }

                    $accReport = AccReport::where('instance_id', $instance->id)
                        ->where('year', $this->year)
                        ->where('periode_id', $this->periode)
                        ->where('kode_rekening_id', $item['kode_rekening_id'])
                        ->where('type', 'lo')
                        ->first();
                    if ($accReport) {
                        $accReport->saldo_akhir = $item['saldo_akhir'];
                        $accReport->kenaikan_penurunan = $item['saldo_akhir'] - $item['saldo_awal'];
                        $accReport->percent = $item['saldo_awal'] == 0 ? 0 : (($item['saldo_akhir'] - $item['saldo_awal']) / $item['saldo_awal']) * 100;
                        $accReport->save();
                    }
                    return $item;
                });
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
                    }

                    $accReport = AccReport::where('instance_id', $instance->id)
                        ->where('year', $this->year)
                        ->where('periode_id', $this->periode)
                        ->where('kode_rekening_id', $item['kode_rekening_id'])
                        ->where('type', 'lo')
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
                    }

                    $accReport = AccReport::where('instance_id', $instance->id)
                        ->where('year', $this->year)
                        ->where('periode_id', $this->periode)
                        ->where('kode_rekening_id', $item['kode_rekening_id'])
                        ->where('type', 'lo')
                        ->first();

                    if ($accReport) {
                        $accReport->saldo_akhir = $item['saldo_akhir'];
                        $accReport->kenaikan_penurunan = $item['saldo_akhir'] - $item['saldo_awal'];
                        $accReport->percent = $item['saldo_awal'] == 0 ? 0 : (($item['saldo_akhir'] - $item['saldo_awal']) / $item['saldo_awal']) * 100;
                        $accReport->save();
                    }
                    return $item;
                });
                // SUMMARIES END
                // Cache for one day
                Cache::put('acc_report_lo_updated_at.' . $instance->id, Carbon::now(), 60 * 24);
            }

            DB::commit();
            // dd($datas);

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
