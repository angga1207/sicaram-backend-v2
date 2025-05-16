<?php

namespace App\Jobs;

use Carbon\Carbon;
use Exception;
use Illuminate\Bus\Queueable;
use Illuminate\Support\Facades\DB;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Contracts\Queue\ShouldBeUnique;

class ProcessRecap implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $periode, $year, $month, $instance;

    public function __construct($periode = null, $year = null, $month = null, $instance = null)
    {
        $this->periode = $periode;
        $this->year = $year;
        $this->month = $month;
        $this->instance = $instance;
    }

    public function handle(): void
    {
        try {
            $arrInstances = DB::table('instances')
                ->when($this->instance, function ($query) {
                    return $query->whereIn('id', [$this->instance]);
                })
                ->get();
            $arrPeriodes = DB::table('ref_periode')
                ->when($this->periode, function ($query) {
                    return $query->whereIn('id', [$this->periode]);
                })
                ->get();
            $arrMonths = [1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 11, 12];
            $arrMonths = collect($arrMonths);
            if ($this->month) {
                $arrMonths = collect([$this->month]);
            }
            $dateNow = Carbon::now();

            foreach ($arrPeriodes as $periode) {
                $startYear = Carbon::parse($periode->start_date)->format('Y');
                $endYear = Carbon::parse($periode->end_date)->format('Y');
                $arrYears = range($startYear, $endYear);
                $arrYears = collect($arrYears);
                if ($this->year) {
                    $arrYears = collect([$this->year]);
                }

                foreach ($arrYears as $year) {
                    foreach ($arrMonths as $month) {
                        foreach ($arrInstances as $instance) {
                            DB::beginTransaction();
                            try {
                                $targetKinerja = DB::table('data_target_kinerja')
                                    ->where('instance_id', $instance->id)
                                    ->where('year', $year)
                                    ->where('month', $month)
                                    ->get();

                                $paguPergeseran1 = $targetKinerja->sum('pagu_pergeseran_1') ?? 0;
                                $paguPergeseran2 = $targetKinerja->sum('pagu_pergeseran_2') ?? 0;
                                $paguPergeseran3 = $targetKinerja->sum('pagu_pergeseran_3') ?? 0;
                                $paguPergeseran4 = $targetKinerja->sum('pagu_pergeseran_4') ?? 0;
                                $paguPerubahan = $targetKinerja->sum('pagu_perubahan') ?? 0;
                                $paguAnggaran = $targetKinerja->sum('pagu_sipd') ?? 0;

                                if ($paguAnggaran > 0) {
                                    $realisasi = DB::table('data_realisasi_sub_kegiatan')
                                        ->where('instance_id', $instance->id)
                                        ->where('year', $year)
                                        ->where('month', $month)
                                        ->where('status', 'verified')
                                        ->get();

                                    $realisasiAnggaran = $realisasi->sum('realisasi_anggaran') ?? 0;
                                    $persentaseRealisasi = $paguAnggaran > 0 ? ($realisasiAnggaran / $paguAnggaran) * 100 : 0;
                                    $sisaAnggaran = $paguAnggaran - $realisasiAnggaran;
                                    $persentaseSisa = $paguAnggaran > 0 ? ($sisaAnggaran / $paguAnggaran) * 100 : 0;
                                    // persentase keuangan menggunakan data dasar pagu induk

                                    $realisasiKinerja = $realisasi->avg('persentase_realisasi_kinerja') ?? 0;
                                    $persentaseKinerja = $realisasiKinerja > 0 ? ($realisasiKinerja / 100) * 100 : 0;

                                    DB::table('instance_summary')
                                        ->updateOrInsert([
                                            'periode_id' => $periode->id,
                                            'year' => $year,
                                            'month' => $month,
                                            'instance_id' => $instance->id,
                                        ], [
                                            'pagu_anggaran' => $paguAnggaran ?? 0,
                                            'pagu_pergeseran_1' => $paguPergeseran1 ?? 0,
                                            'pagu_pergeseran_2' => $paguPergeseran2 ?? 0,
                                            'pagu_pergeseran_3' => $paguPergeseran3 ?? 0,
                                            'pagu_pergeseran_4' => $paguPergeseran4 ?? 0,
                                            'pagu_perubahan' => $paguPerubahan ?? 0,
                                            'realisasi_anggaran' => $realisasiAnggaran ?? 0,
                                            'persentase_realisasi' => $persentaseRealisasi ?? 0,
                                            'sisa_anggaran' => $sisaAnggaran ?? 0,
                                            'persentase_sisa' => $persentaseSisa ?? 0,
                                            'target_kinerja' => 100,
                                            'realisasi_kinerja' => $realisasiKinerja ?? 0,
                                            'persentase_kinerja' => $persentaseKinerja ?? 0,
                                            'tanggal_update' => $dateNow,
                                        ]);
                                } else {
                                    DB::table('instance_summary')
                                        ->updateOrInsert([
                                            'periode_id' => $periode->id,
                                            'year' => $year,
                                            'month' => $month,
                                            'instance_id' => $instance->id,
                                        ], [
                                            'pagu_anggaran' => 0,
                                            'pagu_perubahan' => $paguAnggaran ?? 0,
                                            'pagu_pergeseran_1' => $paguPergeseran1 ?? 0,
                                            'pagu_pergeseran_2' => $paguPergeseran2 ?? 0,
                                            'pagu_pergeseran_3' => $paguPergeseran3 ?? 0,
                                            'pagu_pergeseran_4' => $paguPergeseran4 ?? 0,
                                            'realisasi_anggaran' => 0,
                                            'persentase_realisasi' => 0,
                                            'sisa_anggaran' => 0,
                                            'persentase_sisa' => 0,
                                            'target_kinerja' => 0,
                                            'realisasi_kinerja' => 0,
                                            'persentase_kinerja' => 0,
                                            'tanggal_update' => $dateNow,
                                        ]);
                                }
                                DB::commit();
                            } catch (\Exception $e) {
                                DB::rollBack();
                                dd($e->getMessage());
                            }
                        }
                    }
                }
            }
        } catch (\Exception $e) {
            dd($e->getMessage());
        }
    }
}
