<?php

namespace App\Http\Controllers\API;

use App\Traits\JsonReturner;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use App\Models\Caram\Renja;
use App\Models\Caram\RenjaKegiatan;
use App\Models\Caram\RenjaSubKegiatan;
use App\Models\Caram\Renstra;
use App\Models\Caram\RenstraKegiatan;
use App\Models\Caram\RenstraSubKegiatan;
use App\Models\Caram\RPJMD;
use App\Models\Caram\RPJMDIndikator;
use App\Models\Ref\IndikatorKegiatan;
use App\Models\Ref\IndikatorSubKegiatan;
use App\Models\Ref\Kegiatan;
use App\Models\Ref\Periode;
use App\Models\Ref\Program;
use App\Models\Ref\Satuan;
use App\Models\Ref\SubKegiatan;
use App\Models\User;
use App\Notifications\GlobalNotification;
use Carbon\Carbon;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Validator;

class RenjaController extends Controller
{
    use JsonReturner;

    function uploadRekap5(Request $request)
    {
        set_time_limit(0);
        ini_set('max_input_time', 3600);

        $validate = Validator::make($request->all(), [
            'file' => 'required|file|mimes:xlsx,xls',
        ], [], [
            'file' => 'Berkas Excel',
        ]);
        if ($validate->fails()) {
            return $this->validationResponse($validate->errors());
        }
        $now = now();
        $user = auth()->user();

        DB::beginTransaction();
        try {
            $files = glob(storage_path('app/public/renja-rkp5/*'));
            foreach ($files as $file) {
                if (is_file($file)) {
                    unlink($file);
                }
            }

            $file = $request->file('file');
            $path = $file->store('renja-rkp5', 'public');
            $path = str_replace('public/', '', $path);
            $path = storage_path('app/public/' . $path);

            $reader = new \PhpOffice\PhpSpreadsheet\Reader\Xlsx();
            $spreadsheet = $reader->load($path);
            $sheet = $spreadsheet->getActiveSheet();

            if (
                $sheet->getCellByColumnAndRow(1, 1)->getValue() !== 'NO' &&
                $sheet->getCellByColumnAndRow(2, 1)->getValue() !== 'TAHUN' &&
                $sheet->getCellByColumnAndRow(3, 1)->getValue() !== 'KODE URUSAN' &&
                $sheet->getCellByColumnAndRow(4, 1)->getValue() !== 'NAMA URUSAN' &&
                $sheet->getCellByColumnAndRow(5, 1)->getValue() !== 'KODE SKPD' &&
                $sheet->getCellByColumnAndRow(6, 1)->getValue() !== 'NAMA SKPD' &&
                $sheet->getCellByColumnAndRow(7, 1)->getValue() !== 'KODE SUB UNIT' &&
                $sheet->getCellByColumnAndRow(8, 1)->getValue() !== 'NAMA SUB UNIT' &&
                $sheet->getCellByColumnAndRow(9, 1)->getValue() !== 'KODE BIDANG URUSAN' &&
                $sheet->getCellByColumnAndRow(10, 1)->getValue() !== 'NAMA BIDANG URUSAN' &&
                $sheet->getCellByColumnAndRow(11, 1)->getValue() !== 'KODE PROGRAM' &&
                $sheet->getCellByColumnAndRow(12, 1)->getValue() !== 'NAMA PROGRAM' &&
                $sheet->getCellByColumnAndRow(13, 1)->getValue() !== 'KODE KEGIATAN' &&
                $sheet->getCellByColumnAndRow(14, 1)->getValue() !== 'NAMA KEGIATAN' &&
                $sheet->getCellByColumnAndRow(15, 1)->getValue() !== 'KODE SUB KEGIATAN' &&
                $sheet->getCellByColumnAndRow(16, 1)->getValue() !== 'NAMA SUB KEGIATAN' &&
                $sheet->getCellByColumnAndRow(17, 1)->getValue() !== 'KODE SUMBER DANA' &&
                $sheet->getCellByColumnAndRow(18, 1)->getValue() !== 'NAMA SUMBER DANA' &&
                $sheet->getCellByColumnAndRow(19, 1)->getValue() !== 'KODE REKENING' &&
                $sheet->getCellByColumnAndRow(20, 1)->getValue() !== 'NAMA REKENING' &&
                $sheet->getCellByColumnAndRow(21, 1)->getValue() !== 'PAKET/KELOMPOK' &&
                $sheet->getCellByColumnAndRow(22, 1)->getValue() !== 'NAMA PAKET/KELOMPOK' &&
                $sheet->getCellByColumnAndRow(23, 1)->getValue() !== 'PAGU'
            ) {
                return $this->errorResponse('Format Excel tidak sesuai');
            }

            $allData = [];
            $highestRow = $sheet->getHighestRow();
            $highestColumn = $sheet->getHighestColumn();
            $highestColumnIndex = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::columnIndexFromString($highestColumn);
            $allData = $sheet->rangeToArray('A1:' . $highestColumn . $highestRow, null, true, true, true);
            $allData = collect($allData);
            $allData = $allData->skip(1)->values();

            $arrDataByKodeSKPD = $allData->groupBy('E');
            foreach ($arrDataByKodeSKPD as $excelKodeSkpd => $arrExcelSkpd) {
                // input data Sub Kegiatan Start ------------------------------------------------------
                $arrDataBySubKegiatans = $arrExcelSkpd->groupBy('O');
                foreach ($arrDataBySubKegiatans as $excelSubKegiatan => $arrExcelSubKegiatan) {
                    // input data
                    $paguAnggaran = 0;
                    foreach ($arrExcelSubKegiatan as $excelData) {
                        $paguAnggaran += (float) str_replace(',', '', $excelData['W']);
                    }

                    $instance = DB::table('instances')
                        ->where('code', $excelKodeSkpd)
                        ->first();
                    if (!$instance) {
                        continue; // Skip if instance not found
                    }

                    $subKegiatan = DB::table('ref_sub_kegiatan')
                        ->where('fullcode', $excelSubKegiatan)
                        ->where('instance_id', $instance->id)
                        ->first();
                    if (!$subKegiatan) {
                        continue; // Skip if sub_kegiatan not found
                    }

                    $data = DB::table('data_renja_detail_sub_kegiatan')
                        ->where('sub_kegiatan_id', $subKegiatan->id)
                        // ->where('instance_id', $instance->id)
                        ->where('year', $sheet->getCellByColumnAndRow(2, 2)->getValue())
                        ->oldest('renja_id')
                        ->first();
                    if ($data) {
                        DB::table('data_renja_detail_sub_kegiatan')
                            ->where('id', $data->id)
                            ->update([
                                'total_anggaran' => $paguAnggaran,
                                'updated_by' => $user->id ?? null,
                                'updated_at' => $now,
                            ]);
                    }
                }
                // input data Sub Kegiatan End ------------------------------------------------------

                // input data Kegiatan Start ------------------------------------------------------
                $arrDataByKegiatans = $arrExcelSkpd->groupBy('M');
                foreach ($arrDataByKegiatans as $excelKegiatan => $arrExcelKegiatan) {
                    // input data
                    $paguAnggaran = 0;
                    foreach ($arrExcelKegiatan as $excelData) {
                        $paguAnggaran += (float) str_replace(',', '', $excelData['W']);
                    }

                    $instance = DB::table('instances')
                        ->where('code', $excelKodeSkpd)
                        ->first();
                    if (!$instance) {
                        continue; // Skip if instance not found
                    }

                    $kegiatan = DB::table('ref_kegiatan')
                        ->where('fullcode', $excelKegiatan)
                        ->where('instance_id', $instance->id)
                        ->first();
                    if (!$kegiatan) {
                        continue; // Skip if kegiatan not found
                    }

                    $data = DB::table('data_renja_detail_kegiatan')
                        ->where('kegiatan_id', $kegiatan->id)
                        // ->where('instance_id', $instance->id)
                        ->where('year', $sheet->getCellByColumnAndRow(2, 2)->getValue())
                        ->oldest('renja_id')
                        ->first();
                    if ($data) {
                        DB::table('data_renja_detail_kegiatan')
                            ->where('id', $data->id)
                            ->update([
                                'total_anggaran' => $paguAnggaran,
                                'updated_by' => $user->id ?? null,
                                'updated_at' => $now,
                            ]);
                    }
                }
                // input data Kegiatan End ------------------------------------------------------

                // // input data Program Start ------------------------------------------------------
                // $arrDataByPrograms = $arrExcelSkpd->groupBy('K');
                // foreach ($arrDataByPrograms as $excelProgram => $arrExcelProgram) {
                //     // input data
                //     $paguAnggaran = 0;
                //     foreach ($arrExcelProgram as $excelData) {
                //         $paguAnggaran += (float) str_replace(',', '', $excelData['W']);
                //     }

                //     $instance = DB::table('instances')
                //         ->where('code', $excelKodeSkpd)
                //         ->first();
                //     if (!$instance) {
                //         continue; // Skip if instance not found
                //     }

                //     $program = DB::table('ref_program')
                //         ->where('fullcode', $excelProgram)
                //         ->where('instance_id', $instance->id)
                //         ->first();
                //     if (!$program) {
                //         continue; // Skip if program not found
                //     }

                //     $data = DB::table('data_renja')
                //         ->where('program_id', $program->id)
                //         ->where('instance_id', $instance->id)
                //         ->first();
                //     if ($data) {
                //         DB::table('data_renja')
                //             ->where('id', $data->id)
                //             ->update([
                //                 'total_anggaran' => $paguAnggaran,
                //                 'updated_by' => $user->id ?? null,
                //                 'updated_at' => $now,
                //             ]);
                //     }
                // }
                // // input data Program End ------------------------------------------------------
            }

            $messages = [
                'success' => 'Data berhasil diunggah dan diproses.',
                'file_name' => $file->getClientOriginalName(),
                'file_path' => $path,
            ];
            $logs = DB::table('sipd_upload_logs')
                ->insert([
                    'file_name' => $file->getClientOriginalName(),
                    'file_path' => $path,
                    'status' => 'success',
                    'message' => json_encode($messages, true),
                    'type' => 'renja_pagu',
                    'user_id' => $user->id ?? null,
                    'created_at' => $now,
                    'updated_at' => $now,
                ]);

            DB::commit();
            return $this->successResponse([], 'Data berhasil diunggah dan diproses.');
        } catch (\Throwable $e) {
            DB::rollBack();
            return $this->errorResponse($e->getMessage() . ' ' . $e->getFile() . ':' . $e->getLine());
        }
    }



    function listCaramRenja(Request $request)
    {
        $validate = Validator::make($request->all(), [
            'periode' => 'required|numeric|exists:ref_periode,id',
            'instance' => 'required|numeric|exists:instances,id',
            'program' => 'required|numeric|exists:ref_program,id',
            // 'year' => 'required'
            // 'renstra' => 'required|numeric|exists:data_renstra,id',
        ], [], [
            'periode' => 'Periode',
            'instance' => 'Perangkat Daerah',
            'program' => 'Program',
            // 'year' => 'Tahun',
            // 'renstra' => 'Renstra',
        ]);

        if ($validate->fails()) {
            return $this->validationResponse($validate->errors());
        }

        $user = auth()->user();
        $now = now();
        DB::beginTransaction();
        try {
            $datas = [];
            $renstra = Renstra::where('periode_id', $request->periode)
                ->where('instance_id', $request->instance)
                ->where('program_id', $request->program)
                ->first();
            if (!$renstra) {
                $renstra = new Renstra();
                $renstra->periode_id = $request->periode;
                $renstra->instance_id = $request->instance;
                $renstra->program_id = $request->program;
                $renstra->rpjmd_id = RPJMD::where('instance_id', $request->instance)
                    ->where('periode_id', $request->periode)
                    ->where('program_id', $request->program)
                    ->first()->id ?? null;
                $renstra->total_anggaran = 0;
                $renstra->total_kinerja = 0;
                $renstra->percent_anggaran = 0;
                $renstra->percent_kinerja = 0;
                $renstra->status = 'draft';
                $renstra->status_leader = 'draft';
                $renstra->created_by = $user->id ?? null;
                $renstra->save();
            }
            $renja = Renja::where('periode_id', $request->periode)
                ->where('instance_id', $request->instance)
                ->where('renstra_id', $renstra->id)
                ->first();
            if (!$renja) {
                $renja = new Renja();
                $renja->periode_id = $request->periode;
                $renja->instance_id = $request->instance;
                $renja->renstra_id = $renstra->id;
                $renja->program_id = $request->program;
                $renja->rpjmd_id = RPJMD::where('instance_id', $request->instance)
                    ->where('periode_id', $request->periode)
                    ->where('program_id', $request->program)
                    ->first()->id ?? null;
                $renja->total_anggaran = 0;
                $renja->total_kinerja = 0;
                $renja->percent_anggaran = 0;
                $renja->percent_kinerja = 0;
                $renja->status = 'draft';
                $renja->status_leader = 'draft';
                $renja->created_by = $user->id ?? null;
                $renja->save();
            }

            $periode = Periode::where('id', $request->periode)->first();
            $range = [];
            if ($periode) {
                $start = Carbon::parse($periode->start_date);
                $end = Carbon::parse($periode->end_date);
                for ($i = $start->year; $i <= $end->year; $i++) {
                    $range[] = $i;
                    $anggaran[$i] = null;
                }
            }

            foreach ($range as $year) {
                $program = Program::find($request->program);
                $indicators = [];
                $rpjmdIndicators = RPJMDIndikator::where('rpjmd_id', $renstra->rpjmd_id)
                    ->where('year', $year)
                    ->get();
                foreach ($rpjmdIndicators as $ind) {
                    $indicators[] = [
                        'id' => $ind->id,
                        'name' => $ind->name,
                        'value' => $ind->value,
                        'satuan_id' => $ind->satuan_id,
                        'satuan_name' => $ind->Satuan->name ?? null,
                    ];
                }
                $totalAnggaranRenstra = RenstraKegiatan::where('renstra_id', $renstra->id)
                    ->where('program_id', $program->id)
                    ->where('year', $year)
                    ->where('status', 'active')
                    ->get()->sum('total_anggaran');

                $renja->percent_anggaran = 100;
                $averagePercentKinerja = RenjaKegiatan::where('renja_id', $renja->id)
                    ->where('program_id', $program->id)
                    ->where('year', $year)
                    ->get()->avg('percent_kinerja');
                $renja->percent_kinerja = $averagePercentKinerja ?? 0;
                $renja->save();

                $totalAnggaranRenja = 0;
                $renjaKegiatan = RenjaKegiatan::where('renja_id', $renja->id)
                    ->where('program_id', $program->id)
                    ->where('year', $year)
                    ->where('status', 'active')
                    ->get();
                $totalAnggaranRenja = $renjaKegiatan->sum('total_anggaran');

                $datas[$year][] = [
                    'id' => $program->id,
                    'type' => 'program',
                    'rpjmd_id' => $renstra->rpjmd_id,
                    'rpjmd_data' => $renstra->RPJMD,
                    'indicators' => $indicators ?? null,

                    'anggaran_modal_renstra' => 0,
                    'anggaran_operasi_renstra' => 0,
                    'anggaran_transfer_renstra' => 0,
                    'anggaran_tidak_terduga_renstra' => 0,

                    'anggaran_modal_renja' => $renja->anggaran_modal,
                    'anggaran_operasi_renja' => $renja->anggaran_operasi,
                    'anggaran_transfer_renja' => $renja->anggaran_transfer,
                    'anggaran_tidak_terduga_renja' => $renja->anggaran_tidak_terduga,

                    'program_id' => $program->id,
                    'program_name' => $program->name,
                    'program_fullcode' => $program->fullcode,

                    'total_anggaran_renstra' => $totalAnggaranRenstra,
                    'total_anggaran_renja' => $totalAnggaranRenja,

                    'total_kinerja_renstra' => $renstra->total_kinerja,
                    'percent_anggaran_renstra' => $renstra->percent_anggaran,
                    'percent_kinerja_renstra' => $renstra->percent_kinerja,
                    'percent_anggaran_renja' => $renja->percent_anggaran,
                    'percent_kinerja_renja' => $renja->percent_kinerja,

                    'status_renstra' => $renja->status,
                    'status_renja' => $renja->status,
                    'created_by' => $renja->CreatedBy->fullname ?? '-',
                    'updated_by' => $renja->UpdatedBy->fullname ?? '-',
                    'created_at' => $renja->created_at,
                    'updated_at' => $renja->updated_at,
                ];


                if ($user->role_id == 9 && $user->instance_type == 'staff') {
                    $kegs = $user->MyPermissions()->pluck('kegiatan_id');
                    $kegs = collect($kegs)->unique()->values();
                    $kegiatans = Kegiatan::where('program_id', $program->id)
                        ->whereIn('id', $kegs)
                        ->where('status', 'active')
                        ->get();
                } else {
                    $kegiatans = Kegiatan::where('program_id', $program->id)
                        ->where('status', 'active')
                        ->get();
                }

                foreach ($kegiatans as $kegiatan) {
                    $renstraKegiatan = RenstraKegiatan::where('renstra_id', $renstra->id)
                        ->where('program_id', $program->id)
                        ->where('kegiatan_id', $kegiatan->id)
                        ->where('year', $year)
                        ->where('status', 'active')
                        ->first();
                    if (!$renstraKegiatan) {
                        $renstraKegiatan = new RenstraKegiatan();
                        $renstraKegiatan->renstra_id = $renstra->id;
                        $renstraKegiatan->program_id = $program->id;
                        $renstraKegiatan->kegiatan_id = $kegiatan->id;
                        $renstraKegiatan->year = $year;
                        $renstraKegiatan->anggaran_json = null;
                        $renstraKegiatan->kinerja_json = null;
                        $renstraKegiatan->satuan_json = null;
                        $renstraKegiatan->anggaran_modal = 0;
                        $renstraKegiatan->anggaran_operasi = 0;
                        $renstraKegiatan->anggaran_transfer = 0;
                        $renstraKegiatan->anggaran_tidak_terduga = 0;
                        $renstraKegiatan->total_anggaran = 0;
                        $renstraKegiatan->total_kinerja = 0;
                        $renstraKegiatan->percent_anggaran = 0;
                        $renstraKegiatan->percent_kinerja = 0;
                        $renstraKegiatan->status = 'active';
                        $renstraKegiatan->created_by = $user->id ?? null;
                        $renstraKegiatan->save();
                    }
                    $renjaKegiatan = RenjaKegiatan::where('renja_id', $renja->id)
                        ->where('program_id', $program->id)
                        ->where('kegiatan_id', $kegiatan->id)
                        ->where('year', $year)
                        ->first();

                    if (!$renjaKegiatan) {
                        $renjaKegiatan = new RenjaKegiatan();
                        $renjaKegiatan->renstra_id = $renstra->id;
                        $renjaKegiatan->renja_id = $renja->id;
                        $renjaKegiatan->program_id = $program->id;
                        $renjaKegiatan->kegiatan_id = $kegiatan->id;
                        $renjaKegiatan->year = $year;
                        $renjaKegiatan->anggaran_json = null;
                        $renjaKegiatan->kinerja_json = null;
                        $renjaKegiatan->satuan_json = null;
                        $renjaKegiatan->anggaran_modal = 0;
                        $renjaKegiatan->anggaran_operasi = 0;
                        $renjaKegiatan->anggaran_transfer = 0;
                        $renjaKegiatan->anggaran_tidak_terduga = 0;
                        $renjaKegiatan->total_anggaran = 0;
                        $renjaKegiatan->total_kinerja = 0;
                        $renjaKegiatan->percent_anggaran = 0;
                        $renjaKegiatan->percent_kinerja = 0;
                        $renjaKegiatan->status = 'active';
                        $renjaKegiatan->created_by = $user->id ?? null;
                        $renjaKegiatan->save();
                    }

                    $indicators = [];
                    $indikatorCons = DB::table('con_indikator_kinerja_kegiatan')
                        ->where('instance_id', $request->instance)
                        ->where('program_id', $program->id)
                        ->where('kegiatan_id', $kegiatan->id)
                        ->first();
                    if ($indikatorCons) {
                        $indikators = IndikatorKegiatan::where('pivot_id', $indikatorCons->id)
                            ->get();
                        if (count($indikators) > 0) {
                            foreach ($indikators as $keyIndic => $indi) {
                                $satuanIdRenstra = null;
                                $satuanNameRenstra = null;
                                if ($renstraKegiatan->satuan_json) {
                                    $satuanIdRenstra = json_decode($renstraKegiatan->satuan_json, true)[$keyIndic] ?? null;
                                    $satuanNameRenstra = Satuan::where('id', $satuanIdRenstra)->first()->name ?? null;
                                }
                                $satuanIdRenja = null;
                                $satuanNameRenja = null;
                                if ($renjaKegiatan->satuan_json) {
                                    $satuanIdRenja = json_decode($renjaKegiatan->satuan_json, true)[$keyIndic] ?? null;
                                    $satuanNameRenja = Satuan::where('id', $satuanIdRenja)->first()->name ?? null;
                                }
                                $indicators[$keyIndic] = [
                                    'id' => $indi->id,
                                    'name' => $indi->name,
                                    'value_renstra' => json_decode($renstraKegiatan->kinerja_json, true)[$keyIndic] ?? null,
                                    'satuan_id_renstra' => $satuanIdRenstra ?? null,
                                    'satuan_name_renstra' => $satuanNameRenstra ?? null,
                                    'value_renja' => json_decode($renjaKegiatan->kinerja_json, true)[$keyIndic] ?? null,
                                    'satuan_id_renja' => $satuanIdRenja ?? null,
                                    'satuan_name_renja' => $satuanNameRenja ?? null,
                                ];
                            }
                        }
                    }

                    $totalAnggaranRenstra = RenstraSubKegiatan::where('renstra_id', $renstra->id)
                        ->where('program_id', $program->id)
                        ->where('kegiatan_id', $kegiatan->id)
                        ->where('parent_id', $renstraKegiatan->id)
                        ->where('year', $year)
                        ->where('status', 'active')
                        ->get()
                        ->sum('total_anggaran');

                    // $renjaKegiatan->anggaran_modal = $renjaKegiatan->anggaran_modal;
                    // $renjaKegiatan->anggaran_operasi = $renjaKegiatan->anggaran_operasi;
                    // $renjaKegiatan->anggaran_transfer = $renjaKegiatan->anggaran_transfer;
                    // $renjaKegiatan->anggaran_tidak_terduga = $renjaKegiatan->anggaran_tidak_terduga;
                    // $renjaKegiatan->total_anggaran = $renjaKegiatan->total_anggaran;
                    // $renjaKegiatan->save();

                    $datas[$year][] = [
                        'id' => $kegiatan->id,
                        'type' => 'kegiatan',
                        'program_id' => $renstraKegiatan->program_id,
                        'program_name' => $program->name,
                        'program_fullcode' => $program->fullcode,
                        'kegiatan_id' => $kegiatan->id,
                        'kegiatan_name' => $kegiatan->name,
                        'kegiatan_fullcode' => $kegiatan->fullcode,
                        'indicators' => $indicators,

                        'anggaran_json' => $renstraKegiatan->anggaran_json,
                        'kinerja_json' => $renstraKegiatan->kinerja_json,
                        'satuan_json' => $renstraKegiatan->satuan_json,

                        'anggaran_modal_renstra' => $renstraKegiatan->anggaran_modal,
                        'anggaran_operasi_renstra' => $renstraKegiatan->anggaran_operasi,
                        'anggaran_transfer_renstra' => $renstraKegiatan->anggaran_transfer,
                        'anggaran_tidak_terduga_renstra' => $renstraKegiatan->anggaran_tidak_terduga,

                        'anggaran_modal_renja' => $renjaKegiatan->anggaran_modal,
                        'anggaran_operasi_renja' => $renjaKegiatan->anggaran_operasi,
                        'anggaran_transfer_renja' => $renjaKegiatan->anggaran_transfer,
                        'anggaran_tidak_terduga_renja' => $renjaKegiatan->anggaran_tidak_terduga,

                        'total_anggaran_renstra' => $renstraKegiatan->total_anggaran,
                        'total_anggaran_renja' => $renjaKegiatan->total_anggaran,

                        'total_kinerja' => $renstraKegiatan->total_kinerja,

                        'percent_anggaran_renstra' => $renstraKegiatan->percent_anggaran,
                        'percent_kinerja_renstra' => $renstraKegiatan->percent_kinerja,
                        'percent_anggaran_renja' => $renjaKegiatan->percent_anggaran,
                        'percent_kinerja_renja' => $renjaKegiatan->percent_kinerja,

                        'year' => $renjaKegiatan->year,
                        'status' => $renjaKegiatan->status,
                        'created_by' => $renjaKegiatan->CreatedBy->fullname ?? '-',
                        'updated_by' => $renjaKegiatan->UpdatedBy->fullname ?? '-',
                        'created_at' => $renjaKegiatan->created_at,
                        'updated_at' => $renjaKegiatan->updated_at,
                    ];


                    if ($user->role_id == 9 && $user->instance_type == 'staff') {
                        $subKegs = $user->MyPermissions()->pluck('sub_kegiatan_id');
                        $subKegs = collect($subKegs)->unique()->values();

                        $subKegiatans = SubKegiatan::where('program_id', $program->id)
                            ->where('kegiatan_id', $kegiatan->id)
                            ->whereIn('id', $subKegs)
                            ->where('status', 'active')
                            ->get();
                    } else {
                        $subKegiatans = SubKegiatan::where('program_id', $program->id)
                            ->where('kegiatan_id', $kegiatan->id)
                            ->where('status', 'active')
                            ->get();
                    }
                    foreach ($subKegiatans as $subKegiatan) {
                        $renstraSubKegiatan = RenstraSubKegiatan::where('renstra_id', $renstra->id)
                            ->where('program_id', $program->id)
                            ->where('kegiatan_id', $kegiatan->id)
                            ->where('sub_kegiatan_id', $subKegiatan->id)
                            // ->where('parent_id', $renstraKegiatan->id)
                            ->where('year', $year)
                            // ->where('status', 'active')
                            ->first();
                        if (!$renstraSubKegiatan) {
                            $renstraSubKegiatan = new RenstraSubKegiatan();
                            $renstraSubKegiatan->renstra_id = $renstra->id;
                            $renstraSubKegiatan->parent_id = $renstraKegiatan->id;
                            $renstraSubKegiatan->program_id = $program->id;
                            $renstraSubKegiatan->kegiatan_id = $kegiatan->id;
                            $renstraSubKegiatan->sub_kegiatan_id = $subKegiatan->id;
                            $renstraSubKegiatan->year = $year;
                            $renstraSubKegiatan->anggaran_json = null;
                            $renstraSubKegiatan->kinerja_json = null;
                            $renstraSubKegiatan->satuan_json = null;
                            $renstraSubKegiatan->anggaran_modal = 0;
                            $renstraSubKegiatan->anggaran_operasi = 0;
                            $renstraSubKegiatan->anggaran_transfer = 0;
                            $renstraSubKegiatan->anggaran_tidak_terduga = 0;
                            $renstraSubKegiatan->total_anggaran = 0;
                            $renstraSubKegiatan->total_kinerja = 0;
                            $renstraSubKegiatan->percent_anggaran = 0;
                            $renstraSubKegiatan->percent_kinerja = 0;
                            $renstraSubKegiatan->status = 'active';
                            $renstraSubKegiatan->created_by = $user->id ?? null;
                            $renstraSubKegiatan->save();
                        }

                        $renjaSubKegiatan = RenjaSubKegiatan::where('renja_id', $renja->id)
                            ->where('program_id', $program->id)
                            ->where('kegiatan_id', $kegiatan->id)
                            ->where('sub_kegiatan_id', $subKegiatan->id)
                            ->where('year', $year)
                            ->first();

                        if (!$renjaSubKegiatan) {
                            $renjaSubKegiatan = new RenjaSubKegiatan();
                            $renjaSubKegiatan->renstra_id = $renstra->id;
                            $renjaSubKegiatan->renja_id = $renja->id;
                            $renjaSubKegiatan->parent_id = $renjaKegiatan->id;
                            $renjaSubKegiatan->program_id = $program->id;
                            $renjaSubKegiatan->kegiatan_id = $kegiatan->id;
                            $renjaSubKegiatan->sub_kegiatan_id = $subKegiatan->id;
                            $renjaSubKegiatan->year = $year;
                            $renjaSubKegiatan->anggaran_json = null;
                            $renjaSubKegiatan->kinerja_json = null;
                            $renjaSubKegiatan->satuan_json = null;
                            $renjaSubKegiatan->anggaran_modal = 0;
                            $renjaSubKegiatan->anggaran_operasi = 0;
                            $renjaSubKegiatan->anggaran_transfer = 0;
                            $renjaSubKegiatan->anggaran_tidak_terduga = 0;
                            $renjaSubKegiatan->total_anggaran = 0;
                            $renjaSubKegiatan->total_kinerja = 0;
                            $renjaSubKegiatan->percent_anggaran = 0;
                            $renjaSubKegiatan->percent_kinerja = 0;
                            $renjaSubKegiatan->status = 'active';
                            $renjaSubKegiatan->created_by = $user->id ?? null;
                            $renjaSubKegiatan->save();
                        }

                        $renjaSubKegiatan->renja_id = $renja->id;
                        $renjaSubKegiatan->save();

                        $indicators = [];
                        $indikatorCons = DB::table('con_indikator_kinerja_sub_kegiatan')
                            ->where('instance_id', $request->instance)
                            ->where('program_id', $program->id)
                            ->where('kegiatan_id', $kegiatan->id)
                            ->where('sub_kegiatan_id', $subKegiatan->id)
                            ->where('deleted_at', null)
                            ->first();
                        if ($indikatorCons) {
                            $indikators = IndikatorSubKegiatan::where('pivot_id', $indikatorCons->id)
                                ->get();
                            if (count($indikators) > 0) {
                                foreach ($indikators as $keyIndic => $indi) {

                                    $satuanIdRenstra = null;
                                    $satuanNameRenstra = null;
                                    $satuanIdRenja = null;
                                    $satuanNameRenja = null;
                                    $arrSatuanIdsRenstra = $renstraSubKegiatan->satuan_json ?? null;
                                    if ($arrSatuanIdsRenstra) {
                                        $satuanIdRenstra = json_decode($arrSatuanIdsRenstra, true)[$keyIndic] ?? null;
                                        $satuanNameRenstra = Satuan::where('id', $satuanIdRenstra)->first()->name ?? null;
                                    }
                                    $arrSatuanIdsRenja = $renjaSubKegiatan->satuan_json ?? null;
                                    if ($arrSatuanIdsRenja) {
                                        $satuanIdRenja = json_decode($arrSatuanIdsRenja, true)[$keyIndic] ?? null;
                                        $satuanNameRenja = Satuan::where('id', $satuanIdRenja)->first()->name ?? null;
                                    }

                                    $value = null;
                                    $valueRenja = null;
                                    $arrKinerjaValues = $renstraSubKegiatan->kinerja_json ?? null;
                                    if ($arrKinerjaValues) {
                                        $value = json_decode($arrKinerjaValues, true)[$keyIndic] ?? null;
                                    }
                                    $arrKinerjaValuesRenja = $renjaSubKegiatan->kinerja_json ?? null;
                                    if ($arrKinerjaValuesRenja) {
                                        $valueRenja = json_decode($arrKinerjaValuesRenja, true)[$keyIndic] ?? null;
                                    }
                                    $indicators[$keyIndic] = [
                                        'id' => $indi->id,
                                        'name' => $indi->name,
                                        'value_renstra' => $value ?? null,
                                        'value_renja' => $valueRenja ?? null,
                                        'satuan_id_renstra' => $satuanIdRenstra ?? null,
                                        'satuan_name_renstra' => $satuanNameRenstra ?? null,
                                        'satuan_id_renja' => $satuanIdRenja ?? null,
                                        'satuan_name_renja' => $satuanNameRenja ?? null,
                                    ];
                                }
                            }
                        }
                        $datas[$year][] = [
                            'id' => $subKegiatan->id,
                            'type' => 'sub-kegiatan',
                            'program_id' => $program->id,
                            'program_name' => $program->name ?? null,
                            'program_fullcode' => $program->fullcode,
                            'kegiatan_id' => $kegiatan->id,
                            'kegiatan_name' => $kegiatan->name ?? null,
                            'kegiatan_fullcode' => $kegiatan->fullcode,
                            'sub_kegiatan_id' => $subKegiatan->id,
                            'sub_kegiatan_name' => $subKegiatan->name ?? null,
                            'sub_kegiatan_fullcode' => $subKegiatan->fullcode,
                            'indicators' => $indicators,

                            'anggaran_modal_renstra' => $renstraSubKegiatan->anggaran_modal ?? null,
                            'anggaran_operasi_renstra' => $renstraSubKegiatan->anggaran_operasi ?? null,
                            'anggaran_transfer_renstra' => $renstraSubKegiatan->anggaran_transfer ?? null,
                            'anggaran_tidak_terduga_renstra' => $renstraSubKegiatan->anggaran_tidak_terduga ?? null,

                            'anggaran_modal_renja' => $renjaSubKegiatan->anggaran_modal ?? null,
                            'anggaran_operasi_renja' => $renjaSubKegiatan->anggaran_operasi ?? null,
                            'anggaran_transfer_renja' => $renjaSubKegiatan->anggaran_transfer ?? null,
                            'anggaran_tidak_terduga_renja' => $renjaSubKegiatan->anggaran_tidak_terduga ?? null,

                            'total_anggaran_renstra' => $renstraSubKegiatan->total_anggaran ?? null,
                            'total_anggaran_renja' => $renjaSubKegiatan->total_anggaran ?? null,

                            'percent_anggaran_renstra' => $renstraSubKegiatan->percent_anggaran,
                            'percent_kinerja_renstra' => $renstraSubKegiatan->percent_kinerja,
                            'percent_anggaran_renja' => $renjaSubKegiatan->percent_anggaran,
                            'percent_kinerja_renja' => $renjaSubKegiatan->percent_kinerja,


                            'year' => $renjaSubKegiatan->year ?? null,
                            'status' => $renjaSubKegiatan->status ?? null,
                            'created_by' => $renjaSubKegiatan->CreatedBy->fullname ?? '-',
                            'updated_by' => $renjaSubKegiatan->UpdatedBy->fullname ?? '-',
                            'created_at' => $renjaSubKegiatan->created_at ?? null,
                            'updated_at' => $renjaSubKegiatan->updated_at ?? null,
                        ];
                    }
                }
            }
            $renstra = [
                'id' => $renstra->id,
                'rpjmd_id' => $renstra->rpjmd_id,
                'rpjmd_data' => $renstra->RPJMD,
                'program_id' => $renstra->program_id,
                'program_name' => $renstra->Program->name ?? null,
                'program_fullcode' => $renstra->Program->fullcode ?? null,
                'total_anggaran' => $renstra->total_anggaran,
                'total_kinerja' => $renstra->total_kinerja,
                'percent_anggaran' => $renstra->percent_anggaran,
                'percent_kinerja' => $renstra->percent_kinerja,
                'status' => $renstra->status,
                'status_leader' => $renstra->status_leader,
                'notes_verificator' => $renstra->notes_verificator,
                'created_by' => $renstra->created_by,
                'CreatedBy' => $renstra->CreatedBy->fullname ?? null,
                'updated_by' => $renstra->updated_by,
                'UpdatedBy' => $renstra->UpdatedBy->fullname ?? null,
                'created_at' => $renstra->created_at,
                'updated_at' => $renstra->updated_at,
            ];
            $renja = [
                'id' => $renja->id,
                'periode_id' => $renja->periode_id,
                'periode_data' => $renja->Periode->name ?? null,
                'instance_id' => $renja->instance_id,
                'instance_data' => $renja->Instance->name ?? null,
                'renstra_id' => $renja->renstra_id,
                'renstra_data' => $renja->Renstra->Program->name ?? null,
                'program_id' => $renja->program_id,
                'program_name' => $renja->Program->name ?? null,
                'program_fullcode' => $renja->Program->fullcode ?? null,
                'total_anggaran' => $renja->total_anggaran,
                'total_kinerja' => $renja->total_kinerja,
                'percent_anggaran' => $renja->percent_anggaran,
                'percent_kinerja' => $renja->percent_kinerja,
                'status' => $renja->status,
                'status_leader' => $renja->status_leader,
                'notes_verificator' => $renja->notes_verificator,
                'created_by' => $renja->created_by,
                'CreatedBy' => $renja->CreatedBy->fullname ?? null,
                'updated_by' => $renja->updated_by,
                'UpdatedBy' => $renja->UpdatedBy->fullname ?? null,
                'created_at' => $renja->created_at,
                'updated_at' => $renja->updated_at,
            ];
            DB::commit();
            return $this->successResponse([
                'renstra' => $renstra,
                'renja' => $renja,
                'datas' => $datas,
                'range' => $range,
            ], 'List Renstra');
        } catch (\Throwable $th) {
            DB::rollback();
            return $this->errorResponse($th->getMessage() . ' - ' . $th->getLine() . ' - ' . $th->getFile());
        }
    }

    function detailCaramRenja($id, Request $request)
    {
        if ($request->type == 'kegiatan') {
            $validate = Validator::make($request->all(), [
                'periode' => 'required|numeric|exists:ref_periode,id',
                'instance' => 'required|numeric|exists:instances,id',
                'program' => 'required|numeric|exists:ref_program,id',
                'year' => 'required|numeric',
            ], [], [
                'periode' => 'Periode',
                'instance' => 'Perangkat Daerah',
                'program' => 'Program',
                'year' => 'Tahun',
            ]);

            if ($validate->fails()) {
                return $this->validationResponse($validate->errors());
            }

            $user = auth()->user();
            $now = now();
            DB::beginTransaction();
            try {
                $datas = [];
                $kegiatan = Kegiatan::find($id);
                if (!$kegiatan) {
                    return $this->errorResponse('Kegiatan tidak ditemukan');
                }

                $indicators = [];
                $anggaran = [];
                $conIndikator = DB::table('con_indikator_kinerja_kegiatan')
                    ->where('instance_id', $request->instance)
                    ->where('program_id', $request->program)
                    ->where('kegiatan_id', $kegiatan->id)
                    ->first();
                $arrIndikator = IndikatorKegiatan::where('pivot_id', $conIndikator->id)
                    ->get();
                $renjaDetail = RenjaKegiatan::where('program_id', $request->program)
                    ->where('kegiatan_id', $kegiatan->id)
                    ->where('year', $request->year)
                    // ->where('status', 'active')
                    ->first();
                foreach ($arrIndikator as $key => $indikator) {

                    if ($renjaDetail->kinerja_json) {
                        $value = json_decode($renjaDetail->kinerja_json, true)[$key] ?? null;
                    }
                    if ($renjaDetail->satuan_json) {
                        $satuanId = json_decode($renjaDetail->satuan_json, true)[$key] ?? null;
                        $satuanName = Satuan::where('id', $satuanId)->first()->name ?? null;
                    }
                    $indicators[] = [
                        'id_indikator' => $indikator->id,
                        'name' => $indikator->name,
                        'value' => $value ?? null,
                        'satuan_id' => $satuanId ?? null,
                        'satuan_name' => $satuanName ?? null,
                    ];
                }

                $anggaran = [
                    'total_anggaran' => $renjaDetail->total_anggaran,
                    'anggaran_modal' => $renjaDetail->anggaran_modal,
                    'anggaran_operasi' => $renjaDetail->anggaran_operasi,
                    'anggaran_transfer' => $renjaDetail->anggaran_transfer,
                    'anggaran_tidak_terduga' => $renjaDetail->anggaran_tidak_terduga,
                    'percent_anggaran' => $renjaDetail->percent_anggaran,
                    'percent_kinerja' => $renjaDetail->percent_kinerja,
                ];

                $datas = [
                    'id' => $kegiatan->id,
                    'id_renja_detail' => $renjaDetail->id,
                    'type' => 'kegiatan',
                    'program_id' => $renjaDetail->program_id,
                    'program_name' => $kegiatan->Program->name ?? null,
                    'program_fullcode' => $kegiatan->Program->fullcode ?? null,
                    'kegiatan_id' => $kegiatan->id,
                    'kegiatan_name' => $kegiatan->name ?? null,
                    'kegiatan_fullcode' => $kegiatan->fullcode,
                    'year' => $renjaDetail->year,
                    'indicators' => $indicators,
                    'anggaran' => $anggaran,
                    'total_anggaran' => $renjaDetail->total_anggaran,
                    'percent_anggaran' => $renjaDetail->percent_anggaran,
                    'percent_kinerja' => $renjaDetail->percent_kinerja,
                ];


                // DB::commit();
                return $this->successResponse($datas, 'Detail Kegiatan');
            } catch (\Throwable $th) {
                DB::rollback();
                return $this->errorResponse($th->getMessage() . ' - ' . $th->getLine());
            }
        }

        if ($request->type == 'subkegiatan') {
            $validate = Validator::make($request->all(), [
                'periode' => 'required|numeric|exists:ref_periode,id',
                'instance' => 'required|numeric|exists:instances,id',
                'program' => 'required|numeric|exists:ref_program,id',
                'year' => 'required|numeric',
            ], [], [
                'periode' => 'Periode',
                'instance' => 'Perangkat Daerah',
                'program' => 'Program',
                'year' => 'Tahun',
            ]);

            if ($validate->fails()) {
                return $this->validationResponse($validate->errors());
            }

            DB::beginTransaction();
            try {
                $datas = [];
                $subKegiatan = SubKegiatan::find($id);
                if (!$subKegiatan) {
                    return $this->errorResponse('Sub Kegiatan tidak ditemukan');
                }

                $indicators = [];
                $anggaran = [];
                $conIndikator = DB::table('con_indikator_kinerja_sub_kegiatan')
                    ->where('instance_id', $request->instance)
                    ->where('program_id', $request->program)
                    ->where('kegiatan_id', $subKegiatan->kegiatan_id)
                    ->where('sub_kegiatan_id', $subKegiatan->id)
                    ->first();
                $arrIndikator = IndikatorSubKegiatan::where('pivot_id', $conIndikator->id)
                    ->get();
                $renjaDetail = RenjaSubKegiatan::where('program_id', $request->program)
                    ->where('kegiatan_id', $subKegiatan->kegiatan_id)
                    ->where('sub_kegiatan_id', $subKegiatan->id)
                    ->where('year', $request->year)
                    ->where('status', 'active')
                    ->first();
                foreach ($arrIndikator as $key => $indikator) {

                    if ($renjaDetail->kinerja_json) {
                        $value = json_decode($renjaDetail->kinerja_json, true)[$key] ?? null;
                    }
                    if ($renjaDetail->satuan_json) {
                        $satuanId = json_decode($renjaDetail->satuan_json, true)[$key] ?? null;
                        $satuanName = Satuan::where('id', $satuanId)->first()->name ?? null;
                    }
                    $indicators[] = [
                        'id_indikator' => $indikator->id,
                        'name' => $indikator->name,
                        'value' => $value ?? null,
                        'satuan_id' => $satuanId ?? null,
                        'satuan_name' => $satuanName ?? null,
                    ];
                }

                $anggaran = [
                    'total_anggaran' => $renjaDetail->total_anggaran,
                    'anggaran_modal' => $renjaDetail->anggaran_modal,
                    'anggaran_operasi' => $renjaDetail->anggaran_operasi,
                    'anggaran_transfer' => $renjaDetail->anggaran_transfer,
                    'anggaran_tidak_terduga' => $renjaDetail->anggaran_tidak_terduga,
                    'percent_anggaran' => $renjaDetail->percent_anggaran,
                    'percent_kinerja' => $renjaDetail->percent_kinerja,
                ];

                $datas = [
                    'id' => $subKegiatan->id,
                    'id_renja_detail' => $renjaDetail->id,
                    'type' => 'sub-kegiatan',
                    'program_id' => $renjaDetail->program_id,
                    'program_name' => $subKegiatan->Program->name ?? null,
                    'program_fullcode' => $subKegiatan->Program->fullcode ?? null,
                    'kegiatan_id' => $renjaDetail->kegiatan_id,
                    'kegiatan_name' => $subKegiatan->Kegiatan->name ?? null,
                    'kegiatan_fullcode' => $subKegiatan->Kegiatan->fullcode,
                    'sub_kegiatan_id' => $subKegiatan->id,
                    'sub_kegiatan_name' => $subKegiatan->name ?? null,
                    'sub_kegiatan_fullcode' => $subKegiatan->fullcode,
                    'year' => $renjaDetail->year,
                    'indicators' => $indicators,
                    'anggaran' => $anggaran,
                    'total_anggaran' => $renjaDetail->total_anggaran,
                    'percent_anggaran' => $renjaDetail->percent_anggaran,
                    'percent_kinerja' => $renjaDetail->percent_kinerja,
                ];

                // DB::commit();
                return $this->successResponse($datas, 'Detail Sub Kegiatan');
            } catch (\Throwable $th) {
                DB::rollback();
                return $this->errorResponse($th->getMessage() . ' - ' . $th->getLine());
            }
        }

        return $this->errorResponse('Tipe tidak ditemukan');
    }

    function saveCaramRenja($id, Request $request)
    {
        $user = auth()->user();
        $now = now();
        if ($request->type == 'kegiatan') {
            $validate = Validator::make($request->all(), [
                'periode' => 'required|numeric|exists:ref_periode,id',
                'instance' => 'required|numeric|exists:instances,id',
                'program' => 'required|numeric|exists:ref_program,id',
                'year' => 'required|numeric',
            ], [], [
                'periode' => 'Periode',
                'instance' => 'Perangkat Daerah',
                'program' => 'Program',
                'year' => 'Tahun',
            ]);
            if ($validate->fails()) {
                return $this->validationResponse($validate->errors());
            }

            DB::beginTransaction();
            try {
                $data = RenjaKegiatan::find($request->data['id_renja_detail']);
                $renja = Renja::find($data->renja_id);

                if ($user->role_id !== 1) {
                    if ($renja->status == 'verified') {
                        return $this->errorResponse('Renja sudah diverifikasi');
                    }
                }

                $data->anggaran_modal = $request->data['anggaran']['anggaran_modal'] ?? 0;
                $data->anggaran_operasi = $request->data['anggaran']['anggaran_operasi'] ?? 0;
                $data->anggaran_transfer = $request->data['anggaran']['anggaran_transfer'] ?? 0;
                $data->anggaran_tidak_terduga = $request->data['anggaran']['anggaran_tidak_terduga'] ?? 0;
                $data->total_anggaran = $request->data['total_anggaran'] ?? 0;

                $kinerjaArray = [];
                $satuanArray = [];
                $indicators = $request->data['indicators'];
                foreach ($indicators as $indi) {
                    $kinerjaArray[] = $indi['value'] ?? null;
                    $satuanArray[] = $indi['satuan_id'] ?? null;
                }
                $data->kinerja_json = json_encode($kinerjaArray, true);
                $data->satuan_json = json_encode($satuanArray, true);

                $percentAnggaran = 0;
                if ($request->data['percent_anggaran'] > 100) {
                    $percentAnggaran = 100;
                } elseif ($request->data['percent_anggaran'] < 0) {
                    $percentAnggaran = 0;
                } else {
                    $percentAnggaran = $request->data['percent_anggaran'];
                }
                $data->percent_anggaran = $percentAnggaran;

                $percentKinerja = 0;
                if ($request->data['percent_kinerja'] > 100) {
                    $percentKinerja = 100;
                } elseif ($request->data['percent_kinerja'] < 0) {
                    $percentKinerja = 0;
                } else {
                    $percentKinerja = $request->data['percent_kinerja'];
                }
                $data->percent_kinerja = $percentKinerja;
                $data->updated_by = $user->id ?? null;
                $data->save();

                $renja->updated_by = $user->id ?? null;
                $renja->updated_at = $now;
                $renja->save();

                DB::commit();
                return $this->successResponse($data, 'Data Renstra Berhasil disimpan');
            } catch (\Throwable $th) {
                DB::rollback();
                return $this->errorResponse($th->getMessage() . ' - ' . $th->getLine());
            }
        }

        if ($request->type == 'subkegiatan') {
            $validate = Validator::make($request->all(), [
                'periode' => 'required|numeric|exists:ref_periode,id',
                'instance' => 'required|numeric|exists:instances,id',
                'program' => 'required|numeric|exists:ref_program,id',
                'year' => 'required|numeric',
            ], [], [
                'periode' => 'Periode',
                'instance' => 'Perangkat Daerah',
                'program' => 'Program',
                'year' => 'Tahun',
            ]);
            if ($validate->fails()) {
                return $this->validationResponse($validate->errors());
            }
            DB::beginTransaction();
            try {
                $data = RenjaSubKegiatan::find($request->data['id_renja_detail']);
                $renja = Renja::find($data->renja_id);
                if (!$renja) {
                    return $this->errorResponse('Renja tidak ditemukan');
                }
                if ($user->role_id !== 1) {
                    if ($renja->status == 'verified') {
                        return $this->errorResponse('Renja sudah diverifikasi');
                    }
                }

                $data->anggaran_modal = $request->data['anggaran']['anggaran_modal'] ?? 0;
                $data->anggaran_operasi = $request->data['anggaran']['anggaran_operasi'] ?? 0;
                $data->anggaran_transfer = $request->data['anggaran']['anggaran_transfer'] ?? 0;
                $data->anggaran_tidak_terduga = $request->data['anggaran']['anggaran_tidak_terduga'] ?? 0;
                $data->total_anggaran = $request->data['total_anggaran'] ?? 0;

                $kinerjaArray = [];
                $satuanArray = [];
                $indicators = $request->data['indicators'];
                foreach ($indicators as $indi) {
                    $kinerjaArray[] = $indi['value'] ?? null;
                    $satuanArray[] = $indi['satuan_id'] ?? null;
                }
                $data->kinerja_json = json_encode($kinerjaArray, true);
                $data->satuan_json = json_encode($satuanArray, true);

                $percentAnggaran = 0;
                if ($request->data['percent_anggaran'] > 100) {
                    $percentAnggaran = 100;
                } elseif ($request->data['percent_anggaran'] < 0) {
                    $percentAnggaran = 0;
                } else {
                    $percentAnggaran = $request->data['percent_anggaran'];
                }
                $data->percent_anggaran = $percentAnggaran;

                $percentKinerja = 0;
                if ($request->data['percent_kinerja'] > 100) {
                    $percentKinerja = 100;
                } elseif ($request->data['percent_kinerja'] < 0) {
                    $percentKinerja = 0;
                } else {
                    $percentKinerja = $request->data['percent_kinerja'];
                }
                // $data->percent_kinerja = $percentKinerja;
                $data->percent_kinerja = 100;
                $data->updated_by = $user->id ?? null;
                $data->save();

                $renja->updated_by = $user->id ?? null;
                $renja->updated_at = Carbon::now();
                $renja->save();

                DB::commit();
                return $this->successResponse($data, 'Data Renja Berhasil disimpan');
            } catch (\Throwable $th) {
                DB::rollback();
                return $this->errorResponse($th->getMessage() . ' - ' . $th->getLine());
            }
        }
    }

    function listCaramRenjaNotes($id, Request $request)
    {
        $validate = Validator::make($request->all(), [
            'periode' => 'required|numeric|exists:ref_periode,id',
            'instance' => 'required|numeric|exists:instances,id',
            'program' => 'required|numeric|exists:ref_program,id',
            // 'renja' => 'required|numeric|exists:data_renja,id',
        ], [], [
            'periode' => 'Periode',
            'instance' => 'Perangkat Daerah',
            'program' => 'Program',
            // 'renja' => 'Renja',
        ]);

        if ($validate->fails()) {
            return $this->validationResponse($validate->errors());
        }

        $datas = [];
        $notes = DB::table('notes_renja')
            ->where('renja_id', $id)
            ->orderBy('created_at', 'asc')
            ->limit(10)
            ->get();
        foreach ($notes as $note) {
            $user = User::find($note->user_id);
            $datas[] = [
                'id' => $note->id,
                'user_id' => $note->user_id,
                'user_name' => $user->fullname ?? null,
                'user_photo' => asset($user->photo) ?? null,
                'message' => $note->message,
                'status' => $note->status,
                'type' => $note->type,
                'created_at' => $note->created_at,
                'updated_at' => $note->updated_at,
            ];
        }

        return $this->successResponse($datas, 'List Renstra');
    }

    function postCaramRenjaNotes($id, Request $request)
    {
        $validate = Validator::make($request->all(), [
            'periode' => 'required|numeric|exists:ref_periode,id',
            'instance' => 'required|numeric|exists:instances,id',
            'program' => 'required|numeric|exists:ref_program,id',
            // 'renja' => 'required|numeric|exists:data_renja,id',
            'message' => 'required|string',
            'status' => 'required|string',
        ], [], [
            'periode' => 'Periode',
            'instance' => 'Perangkat Daerah',
            'program' => 'Program',
            // 'renja' => 'Renja',
            'message' => 'Pesan',
            'status' => 'Status',
        ]);

        if ($validate->fails()) {
            return $this->validationResponse($validate->errors());
        }

        $user = auth()->user();
        $now = now();
        DB::beginTransaction();
        try {
            $renja = Renja::find($id);
            if (!$renja) {
                return $this->errorResponse('Renja tidak ditemukan');
            }
            if ($user->role_id == 9) {
                $type = 'request';
                $renja->status = $request->status;
                $renja->save();

                // send notification
                $users = User::where('role_id', 6)->get();
                Notification::send($users, new GlobalNotification(
                    'sent',
                    $renja->id,
                    $user->id,
                    $users->pluck('id')->toArray(),
                    '/renja/' . $renja->instance_id . '&program=' . $renja->program_id,
                    'Permintaan Verifikasi Renstra Perubahan',
                    'Permintaan Verifikasi Renstra Perubahan dari ' . $user->fullname,
                    [
                        'type' => 'renja',
                        'renja_id' => $renja->id,
                        'instance_id' => $renja->instance_id,
                        'program_id' => $renja->program_id,
                        'uri' => '/renja/' . $renja->instance_id . '&program=' . $renja->program_id,
                    ]
                ));
            } else {
                $type = 'return';
                $renja->status = $request->status;
                $renja->notes_verificator = $request->message;
                $renja->save();

                // send notification
                $users = User::where('role_id', 9)
                    ->where('instance_id', $renja->instance_id)
                    ->get();
                Notification::send($users, new GlobalNotification(
                    'sent',
                    $renja->id,
                    $user->id,
                    $users->pluck('id')->toArray(),
                    '/renja/' . $renja->instance_id . '&program=' . $renja->program_id,
                    'Verifikasi Renstra Perubahan',
                    $user->fullname . ' telah memberikan verifikasi Renstra Perubahan',
                    [
                        'type' => 'renja',
                        'renja_id' => $renja->id,
                        'instance_id' => $renja->instance_id,
                        'program_id' => $renja->program_id,
                        'uri' => '/renja/' . $renja->instance_id . '&program=' . $renja->program_id,
                    ]
                ));
            }
            $note = DB::table('notes_renja')
                ->insert([
                    'renja_id' => $id,
                    'user_id' => $user->id,
                    'message' => $request->message,
                    'status' => $request->status,
                    'type' => $type ?? null,
                    'created_at' => $now,
                    'updated_at' => $now,
                ]);

            DB::commit();
            return $this->successResponse($note, 'Pesan Berhasil dikirim');
        } catch (\Throwable $th) {
            DB::rollback();
            return $this->errorResponse($th->getMessage() . ' - ' . $th->getLine());
        }
    }
}
