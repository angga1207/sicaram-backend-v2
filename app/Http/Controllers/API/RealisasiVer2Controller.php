<?php

namespace App\Http\Controllers\API;

use App\Traits\JsonReturner;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Validator;

class RealisasiVer2Controller extends Controller
{
    use JsonReturner;

    function list1(Request $request)
    {
        $validate = Validator::make($request->all(), [
            'instance_id' => 'required|exists:instances,id',
            'month' => 'required',
            'year' => 'required',
        ], [], [
            'instance_id' => 'Instance ID',
            'month' => 'Month',
            'year' => 'Year',
        ]);

        if ($validate->fails()) {
            return $this->errorResponse($validate->errors());
        }

        try {
            $instance = DB::table('instances')
                ->where('id', $request->instance_id)
                ->first();

            $allTargetKinerja = DB::table('data_target_kinerja')
                ->where('instance_id', $request->instance_id)
                ->where('month', $request->month)
                ->where('year', $request->year)
                ->whereNull('deleted_at')
                ->get();
            $totalPagu = 0;
            $typePagu = 'Pagu Perubahan';
            $tanggalPagu = $allTargetKinerja->pluck('tanggal_perubahan')->first();
            $totalPagu = $allTargetKinerja->sum('pagu_perubahan');
            if ($totalPagu == 0) {
                $totalPagu = $allTargetKinerja->sum('pagu_pergeseran_4');
                $typePagu = 'Pagu Pergeseran 4';
                $tanggalPagu = $allTargetKinerja->pluck('tanggal_pergeseran_4')->first();
            }
            if ($totalPagu == 0) {
                $totalPagu = $allTargetKinerja->sum('pagu_pergeseran_3');
                $typePagu = 'Pagu Pergeseran 3';
                $tanggalPagu = $allTargetKinerja->pluck('tanggal_pergeseran_3')->first();
            }
            if ($totalPagu == 0) {
                $totalPagu = $allTargetKinerja->sum('pagu_pergeseran_2');
                $typePagu = 'Pagu Pergeseran 2';
                $tanggalPagu = $allTargetKinerja->pluck('tanggal_pergeseran_2')->first();
            }
            if ($totalPagu == 0) {
                $totalPagu = $allTargetKinerja->sum('pagu_pergeseran_1');
                $typePagu = 'Pagu Pergeseran 1';
                $tanggalPagu = $allTargetKinerja->pluck('tanggal_pergeseran_1')->first();
            }
            if ($totalPagu == 0) {
                $totalPagu = $allTargetKinerja->sum('pagu_sipd');
                $typePagu = 'Pagu Induk';
                $tanggalPagu = null;
            }

            $allDataRealisasi = DB::table('data_realisasi')
                ->where('instance_id', $request->instance_id)
                ->where('month', $request->month)
                ->where('year', $request->year)
                ->whereNull('deleted_at')
                ->get();
            $totalRealisasi = $allDataRealisasi->sum('anggaran') + $allDataRealisasi->sum('anggaran_bulan_ini');
            $totalRealisasi = $totalRealisasi ?: 0;

            return $this->successResponse([
                'instance' => $instance,
                'total_pagu' => $totalPagu,
                'pagu_type' => $typePagu,
                'pagu_date' => $tanggalPagu,
                'total_realisasi' => $totalRealisasi,
            ]);
        } catch (\Exception $e) {
            return $this->errorResponse($e->getMessage() . ' ' . $e->getLine() . ' ' . $e->getFile());
        }
    }

    function list2(Request $request)
    {
        $validate = Validator::make($request->all(), [
            'instance_id' => 'required|exists:instances,id',
            'month' => 'required',
            'year' => 'required',
        ], [], [
            'instance_id' => 'Instance ID',
            'month' => 'Month',
            'year' => 'Year',
        ]);

        if ($validate->fails()) {
            return $this->errorResponse($validate->errors());
        }

        try {
            $arrProgram = DB::table('ref_program')
                ->where('instance_id', $request->instance_id)
                ->whereNull('deleted_at')
                ->orderBy('fullcode')
                ->get();

            if (auth()->user()->role_id == 9 && auth()->user()->instance_type == 'staff') {
                $progs = auth()->user()->MyPermissions()->pluck('program_id');
                $progs = collect($progs)->unique()->values();
                $arrProgram = DB::table('ref_program')
                    ->where('instance_id', $request->instance_id)
                    ->whereIn('id', $progs)
                    ->whereNull('deleted_at')
                    ->orderBy('fullcode')
                    ->get();
            }

            $datas = [];

            foreach ($arrProgram as $program) {
                $allTargetKinerja = DB::table('data_target_kinerja')
                    ->where('instance_id', $request->instance_id)
                    ->where('program_id', $program->id)
                    ->where('month', $request->month)
                    ->where('year', $request->year)
                    ->whereNull('deleted_at')
                    ->get();
                $allDataRealisasi = DB::table('data_realisasi')
                    ->where('instance_id', $request->instance_id)
                    ->where('program_id', $program->id)
                    ->where('month', $request->month)
                    ->where('year', $request->year)
                    ->whereNull('deleted_at')
                    ->get();

                $totalPagu = 0;
                $typePagu = 'Pagu Perubahan';
                $tanggalPagu = $allTargetKinerja->pluck('tanggal_perubahan')->first();
                $totalPagu = $allTargetKinerja->sum('pagu_perubahan');
                if ($totalPagu == 0) {
                    $totalPagu = $allTargetKinerja->sum('pagu_pergeseran_4');
                    $typePagu = 'Pagu Pergeseran 4';
                    $tanggalPagu = $allTargetKinerja->pluck('tanggal_pergeseran_4')->first();
                }
                if ($totalPagu == 0) {
                    $totalPagu = $allTargetKinerja->sum('pagu_pergeseran_3');
                    $typePagu = 'Pagu Pergeseran 3';
                    $tanggalPagu = $allTargetKinerja->pluck('tanggal_pergeseran_3')->first();
                }
                if ($totalPagu == 0) {
                    $totalPagu = $allTargetKinerja->sum('pagu_pergeseran_2');
                    $typePagu = 'Pagu Pergeseran 2';
                    $tanggalPagu = $allTargetKinerja->pluck('tanggal_pergeseran_2')->first();
                }
                if ($totalPagu == 0) {
                    $totalPagu = $allTargetKinerja->sum('pagu_pergeseran_1');
                    $typePagu = 'Pagu Pergeseran 1';
                    $tanggalPagu = $allTargetKinerja->pluck('tanggal_pergeseran_1')->first();
                }
                if ($totalPagu == 0) {
                    $totalPagu = $allTargetKinerja->sum('pagu_sipd');
                    $typePagu = 'Pagu Induk';
                    $tanggalPagu = null;
                }

                $totalRealisasi = $allDataRealisasi->sum('anggaran') + $allDataRealisasi->sum('anggaran_bulan_ini');
                $totalRealisasi = $totalRealisasi ?: 0;

                $datas[] = [
                    'program_id' => $program->id,
                    'program_name' => $program->name,
                    'program_fullcode' => $program->fullcode,
                    'total_pagu' => $totalPagu,
                    'pagu_type' => $typePagu,
                    'pagu_date' => $tanggalPagu,
                    'total_realisasi' => $totalRealisasi,
                ];
            }

            return $this->successResponse($datas);
        } catch (\Exception $e) {
            return $this->errorResponse($e->getMessage() . ' ' . $e->getLine() . ' ' . $e->getFile());
        }
    }

    function list3(Request $request)
    {
        $validate = Validator::make($request->all(), [
            'instance_id' => 'required|exists:instances,id',
            'program_id' => 'required|exists:ref_program,id',
            'month' => 'required',
            'year' => 'required',
        ], [], [
            'instance_id' => 'Instance ID',
            'program_id' => 'Program ID',
            'month' => 'Month',
            'year' => 'Year',
        ]);

        if ($validate->fails()) {
            return $this->errorResponse($validate->errors());
        }

        try {
            $program = DB::table('ref_program')
                ->where('id', $request->program_id)
                ->where('instance_id', $request->instance_id)
                ->whereNull('deleted_at')
                ->first();
            $arrKegiatan = DB::table('ref_kegiatan')
                ->where('instance_id', $request->instance_id)
                ->where('program_id', $request->program_id)
                ->whereNull('deleted_at')
                ->orderBy('fullcode')
                ->get();

            if (auth()->user()->role_id == 9 && auth()->user()->instance_type == 'staff') {
                $kegs = auth()->user()->MyPermissions()->pluck('kegiatan_id');
                $kegs = collect($kegs)->unique()->values();
                $arrKegiatan = DB::table('ref_kegiatan')
                    ->whereIn('id', $kegs)
                    ->where('instance_id', $request->instance_id)
                    ->where('program_id', $request->program_id)
                    ->whereNull('deleted_at')
                    ->orderBy('fullcode')
                    ->get();
            }
            $datas = [];
            foreach ($arrKegiatan as $kegiatan) {
                $allTargetKinerja = DB::table('data_target_kinerja')
                    ->where('instance_id', $request->instance_id)
                    ->where('program_id', $request->program_id)
                    ->where('kegiatan_id', $kegiatan->id)
                    ->where('month', $request->month)
                    ->where('year', $request->year)
                    ->whereNull('deleted_at')
                    ->get();
                $allDataRealisasi = DB::table('data_realisasi')
                    ->where('instance_id', $request->instance_id)
                    ->where('program_id', $request->program_id)
                    ->where('kegiatan_id', $kegiatan->id)
                    ->where('month', $request->month)
                    ->where('year', $request->year)
                    ->get();

                $totalPagu = 0;
                $typePagu = 'Pagu Perubahan';
                $tanggalPagu = $allTargetKinerja->pluck('tanggal_perubahan')->first();
                $totalPagu = $allTargetKinerja->sum('pagu_perubahan');
                if ($totalPagu == 0) {
                    $totalPagu = $allTargetKinerja->sum('pagu_pergeseran_4');
                    $typePagu = 'Pagu Pergeseran 4';
                    $tanggalPagu = $allTargetKinerja->pluck('tanggal_pergeseran_4')->first();
                }
                if ($totalPagu == 0) {
                    $totalPagu = $allTargetKinerja->sum('pagu_pergeseran_3');
                    $typePagu = 'Pagu Pergeseran 3';
                    $tanggalPagu = $allTargetKinerja->pluck('tanggal_pergeseran_3')->first();
                }
                if ($totalPagu == 0) {
                    $totalPagu = $allTargetKinerja->sum('pagu_pergeseran_2');
                    $typePagu = 'Pagu Pergeseran 2';
                    $tanggalPagu = $allTargetKinerja->pluck('tanggal_pergeseran_2')->first();
                }
                if ($totalPagu == 0) {
                    $totalPagu = $allTargetKinerja->sum('pagu_pergeseran_1');
                    $typePagu = 'Pagu Pergeseran 1';
                    $tanggalPagu = $allTargetKinerja->pluck('tanggal_pergeseran_1')->first();
                }
                if ($totalPagu == 0) {
                    $totalPagu = $allTargetKinerja->sum('pagu_sipd');
                    $typePagu = 'Pagu Induk';
                    $tanggalPagu = null;
                }

                $totalRealisasi = $allDataRealisasi->sum('anggaran') + $allDataRealisasi->sum('anggaran_bulan_ini');
                $totalRealisasi = $totalRealisasi ?: 0;

                $datas[] = [
                    'program_id' => $program->id,
                    'program_name' => $program->name,
                    'program_fullcode' => $program->fullcode,
                    'kegiatan_id' => $kegiatan->id,
                    'kegiatan_name' => $kegiatan->name,
                    'kegiatan_fullcode' => $kegiatan->fullcode,
                    'total_pagu' => $totalPagu,
                    'pagu_type' => $typePagu,
                    'pagu_date' => $tanggalPagu,
                    'total_realisasi' => $totalRealisasi,
                ];
            }

            return $this->successResponse($datas);
        } catch (\Exception $e) {
            return $this->errorResponse($e->getMessage() . ' ' . $e->getLine() . ' ' . $e->getFile());
        }
    }

    function list4(Request $request)
    {
        $validate = Validator::make($request->all(), [
            'instance_id' => 'required|exists:instances,id',
            'kegiatan_id' => 'required|exists:ref_kegiatan,id',
            'month' => 'required',
            'year' => 'required',
        ], [], [
            'instance_id' => 'Instance ID',
            'kegiatan_id' => 'kegiatan ID',
            'month' => 'Month',
            'year' => 'Year',
        ]);

        if ($validate->fails()) {
            return $this->errorResponse($validate->errors());
        }

        try {
            $kegiatan = DB::table('ref_kegiatan')
                ->where('id', $request->kegiatan_id)
                ->where('instance_id', $request->instance_id)
                ->whereNull('deleted_at')
                ->first();
            $program = DB::table('ref_program')
                ->where('id', $kegiatan->program_id)
                ->where('instance_id', $request->instance_id)
                ->whereNull('deleted_at')
                ->first();
            $arrSubKegiatan = DB::table('ref_sub_kegiatan')
                ->where('instance_id', $request->instance_id)
                ->where('program_id', $kegiatan->program_id)
                ->where('kegiatan_id', $kegiatan->id)
                ->whereNull('deleted_at')
                ->orderBy('fullcode')
                ->get();

            if (auth()->user()->role_id == 9 && auth()->user()->instance_type == 'staff') {
                $subKegs = auth()->user()->MyPermissions()->pluck('sub_kegiatan_id');
                $subKegs = collect($subKegs)->unique()->values();

                $arrSubKegiatan = DB::table('ref_sub_kegiatan')
                    ->whereIn('id', $subKegs)
                    ->where('instance_id', $request->instance_id)
                    ->where('program_id', $kegiatan->program_id)
                    ->where('kegiatan_id', $kegiatan->id)
                    ->whereNull('deleted_at')
                    ->orderBy('fullcode')
                    ->get();
            }
            $datas = [];
            foreach ($arrSubKegiatan as $subKegiatan) {
                $allTargetKinerja = DB::table('data_target_kinerja')
                    ->where('instance_id', $request->instance_id)
                    ->where('program_id', $kegiatan->program_id)
                    ->where('kegiatan_id', $kegiatan->id)
                    ->where('sub_kegiatan_id', $subKegiatan->id)
                    ->where('month', $request->month)
                    ->where('year', $request->year)
                    ->whereNull('deleted_at')
                    ->get();
                $allDataRealisasi = DB::table('data_realisasi')
                    ->where('instance_id', $request->instance_id)
                    ->where('program_id', $kegiatan->program_id)
                    ->where('kegiatan_id', $kegiatan->id)
                    ->where('sub_kegiatan_id', $subKegiatan->id)
                    ->where('month', $request->month)
                    ->where('year', $request->year)
                    ->get();

                $totalPagu = 0;
                $typePagu = 'Pagu Perubahan';
                $tanggalPagu = $allTargetKinerja->pluck('tanggal_perubahan')->first();
                $totalPagu = $allTargetKinerja->sum('pagu_perubahan');
                if ($totalPagu == 0) {
                    $totalPagu = $allTargetKinerja->sum('pagu_pergeseran_4');
                    $typePagu = 'Pagu Pergeseran 4';
                    $tanggalPagu = $allTargetKinerja->pluck('tanggal_pergeseran_4')->first();
                }
                if ($totalPagu == 0) {
                    $totalPagu = $allTargetKinerja->sum('pagu_pergeseran_3');
                    $typePagu = 'Pagu Pergeseran 3';
                    $tanggalPagu = $allTargetKinerja->pluck('tanggal_pergeseran_3')->first();
                }
                if ($totalPagu == 0) {
                    $totalPagu = $allTargetKinerja->sum('pagu_pergeseran_2');
                    $typePagu = 'Pagu Pergeseran 2';
                    $tanggalPagu = $allTargetKinerja->pluck('tanggal_pergeseran_2')->first();
                }
                if ($totalPagu == 0) {
                    $totalPagu = $allTargetKinerja->sum('pagu_pergeseran_1');
                    $typePagu = 'Pagu Pergeseran 1';
                    $tanggalPagu = $allTargetKinerja->pluck('tanggal_pergeseran_1')->first();
                }
                if ($totalPagu == 0) {
                    $totalPagu = $allTargetKinerja->sum('pagu_sipd');
                    $typePagu = 'Pagu Induk';
                    $tanggalPagu = null;
                }

                $totalRealisasi = $allDataRealisasi->sum('anggaran') + $allDataRealisasi->sum('anggaran_bulan_ini');
                $totalRealisasi = $totalRealisasi ?: 0;

                // STATUS BELUM DIKERJAKAN

                $datas[] = [
                    'program_id' => $program->id,
                    'program_name' => $program->name,
                    'program_fullcode' => $program->fullcode,
                    'kegiatan_id' => $kegiatan->id,
                    'kegiatan_name' => $kegiatan->name,
                    'kegiatan_fullcode' => $kegiatan->fullcode,
                    'sub_kegiatan_id' => $subKegiatan->id,
                    'sub_kegiatan_name' => $subKegiatan->name,
                    'sub_kegiatan_fullcode' => $subKegiatan->fullcode,
                    'total_pagu' => $totalPagu,
                    'pagu_type' => $typePagu,
                    'pagu_date' => $tanggalPagu,
                    'total_realisasi' => $totalRealisasi,
                ];
            }

            return $this->successResponse($datas);
        } catch (\Exception $e) {
            return $this->errorResponse($e->getMessage() . ' ' . $e->getLine() . ' ' . $e->getFile());
        }
    }
}
