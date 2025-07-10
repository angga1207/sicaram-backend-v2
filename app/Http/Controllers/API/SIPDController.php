<?php

namespace App\Http\Controllers\API;

use App\Models\User;
use App\Models\Instance;
use App\Models\Ref\Periode;
use Illuminate\Support\Str;
use App\Traits\JsonReturner;
use Illuminate\Http\Request;
use App\Models\Data\Realisasi;
use App\Models\Ref\SubKegiatan;
use App\Models\Ref\KodeRekening;
use App\Models\Data\TargetKinerja;
use App\Models\Ref\KodeSumberDana;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use App\Models\Ref\Bidang;
use App\Models\Ref\Kegiatan;
use App\Models\Ref\Program;
use App\Models\Ref\Urusan;
use Illuminate\Support\Facades\Validator;

class SIPDController extends Controller
{
    use JsonReturner;

    function listLogs(Request $request)
    {
        $datas = [];
        $logs = DB::table('sipd_upload_logs')
            ->when($request->type, function ($query) use ($request) {
                return $query->whereIn('type', $request->type);
            })
            ->select('*')
            ->orderBy('created_at', 'desc')
            ->limit(20)
            ->get();

        foreach ($logs as $log) {
            $user = User::find($log->user_id);
            $messages = [];
            if ($log->message) {
                $messages = json_decode($log->message);
            }
            $datas[] = [
                'id' => $log->id,
                'created_at' => $log->created_at,
                'file_name' => $log->file_name,
                'message' => $messages,
                'status' => $log->status,
                'type' => $log->type,
                'author' => $user->fullname,
                'author_photo' => asset($user->photo),
            ];
        }
        return $this->successResponse($datas);
    }

    function getMonitorPagu(Request $request)
    {
        $validate = Validator::make($request->all(), [
            'periode' => 'required|numeric|exists:ref_periode,id',
            'instance' => 'required|numeric|exists:instances,id',
            'year' => 'required|numeric',
        ], [], [
            'periode' => 'Periode',
            'instance' => 'Perangkat Daerah',
            'year' => 'Tahun',
        ]);
        if ($validate->fails()) {
            return $this->validationResponse($validate->errors());
        }

        $reqTahun = $request->year;
        $reqInstance = $request->instance;

        $return = [];
        $datas = DB::table('data_target_kinerja')
            ->where('instance_id', $reqInstance)
            ->where('year', $reqTahun)
            ->get();

        $arrMonths = [1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 11, 12];

        $arrPrograms = DB::table('ref_program')
            ->where('instance_id', $reqInstance)
            ->where('periode_id', $request->periode)
            ->whereNull('deleted_at')
            ->get();

        foreach ($arrPrograms as $program) {
            $bundleData = [];
            foreach ($arrMonths as $month) {
                $totalPagu = 0;

                $totalPagu = collect($datas)
                    ->where('month', $month)
                    ->where('program_id', $program->id)
                    ->sum('pagu_sipd');
                $totalPergeseran1 = collect($datas)
                    ->where('month', $month)
                    ->where('program_id', $program->id)
                    ->sum('pagu_pergeseran_1');
                $datePergeseran1 = collect($datas)
                    ->where('month', $month)
                    ->where('program_id', $program->id)
                    ->max('tanggal_pergeseran_1');
                $totalPergeseran2 = collect($datas)
                    ->where('month', $month)
                    ->where('program_id', $program->id)
                    ->sum('pagu_pergeseran_2');
                $datePergeseran2 = collect($datas)
                    ->where('month', $month)
                    ->where('program_id', $program->id)
                    ->max('tanggal_pergeseran_2');
                $totalPergeseran3 = collect($datas)
                    ->where('month', $month)
                    ->where('program_id', $program->id)
                    ->sum('pagu_pergeseran_3');
                $datePergeseran3 = collect($datas)
                    ->where('month', $month)
                    ->where('program_id', $program->id)
                    ->max('tanggal_pergeseran_3');
                $totalPergeseran4 = collect($datas)
                    ->where('month', $month)
                    ->where('program_id', $program->id)
                    ->sum('pagu_pergeseran_4');
                $datePergeseran4 = collect($datas)
                    ->where('month', $month)
                    ->where('program_id', $program->id)
                    ->max('tanggal_pergeseran_4');
                $totalPerubahan = collect($datas)
                    ->where('month', $month)
                    ->where('program_id', $program->id)
                    ->sum('pagu_perubahan');
                $datePerubahan = collect($datas)
                    ->where('month', $month)
                    ->where('program_id', $program->id)
                    ->max('tanggal_perubahan');

                $bundleData[] = [
                    'month' => $month,
                    'pagu_induk' => $totalPagu,
                    'pagu_pergeseran_1' => $totalPergeseran1,
                    'tanggal_pergeseran_1' => $datePergeseran1,
                    'pagu_pergeseran_2' => $totalPergeseran2,
                    'tanggal_pergeseran_2' => $datePergeseran2,
                    'pagu_pergeseran_3' => $totalPergeseran3,
                    'tanggal_pergeseran_3' => $datePergeseran3,
                    'pagu_pergeseran_4' => $totalPergeseran4,
                    'tanggal_pergeseran_4' => $datePergeseran4,
                    'pagu_perubahan' => $totalPerubahan,
                    'tanggal_perubahan' => $datePerubahan,
                ];
            }

            $return[] = [
                'program_id' => $program->id,
                'fullcode' => $program->fullcode,
                'name' => $program->name,
                'data' => $bundleData,
            ];
        }

        // total data
        $bundleData = [];
        foreach ($arrMonths as $month) {
            $totalPagu = 0;
            $totalPagu = collect($datas)
                ->where('month', $month)
                ->sum('pagu_sipd');
            $totalPergeseran1 = collect($datas)
                ->where('month', $month)
                ->sum('pagu_pergeseran_1');
            $datePergeseran1 = collect($datas)
                ->where('month', $month)
                ->max('tanggal_pergeseran_1');
            $totalPergeseran2 = collect($datas)
                ->where('month', $month)
                ->sum('pagu_pergeseran_2');
            $datePergeseran2 = collect($datas)
                ->where('month', $month)
                ->max('tanggal_pergeseran_2');
            $totalPergeseran3 = collect($datas)
                ->where('month', $month)
                ->sum('pagu_pergeseran_3');
            $datePergeseran3 = collect($datas)
                ->where('month', $month)
                ->max('tanggal_pergeseran_3');
            $totalPergeseran4 = collect($datas)
                ->where('month', $month)
                ->sum('pagu_pergeseran_4');
            $datePergeseran4 = collect($datas)
                ->where('month', $month)
                ->max('tanggal_pergeseran_4');
            $totalPerubahan = collect($datas)
                ->where('month', $month)
                ->sum('pagu_perubahan');
            $datePerubahan = collect($datas)
                ->where('month', $month)
                ->max('tanggal_perubahan');

            $bundleData[] = [
                'month' => $month,
                'pagu_induk' => $totalPagu,
                'pagu_pergeseran_1' => $totalPergeseran1,
                'tanggal_pergeseran_1' => $datePergeseran1,
                'pagu_pergeseran_2' => $totalPergeseran2,
                'tanggal_pergeseran_2' => $datePergeseran2,
                'pagu_pergeseran_3' => $totalPergeseran3,
                'tanggal_pergeseran_3' => $datePergeseran3,
                'pagu_pergeseran_4' => $totalPergeseran4,
                'tanggal_pergeseran_4' => $datePergeseran4,
                'pagu_perubahan' => $totalPerubahan,
                'tanggal_perubahan' => $datePerubahan,
            ];
        }
        $return[] = [
            'program_id' => null,
            'fullcode' => null,
            'name' => 'Total',
            'data' => $bundleData,
        ];


        return $this->successResponse($return);
    }

    // upload an untuk input target sebelum realisasi
    function uploadSubToRekening(Request $request)
    {
        // handle 524 error
        // ini_set('memory_limit', '2G');
        set_time_limit(0);
        ini_set('max_input_time', 3600);

        // upload_max_filesize = 100M
        // post_max_size = 100M
        // max_execution_time = 300
        // max_input_time = 300

        $validate = Validator::make($request->all(), [
            'file' => 'required|file|mimes:xlsx,xls',
        ], [], [
            'file' => 'Berkas Excel',
        ]);
        if ($validate->fails()) {
            return $this->validationResponse($validate->errors());
        }
        DB::beginTransaction();
        try {
            $files = glob(storage_path('app/public/rkp5/*'));
            foreach ($files as $file) {
                if (is_file($file)) {
                    unlink($file);
                }
            }

            $reqTahun = $request->year;
            $reqMonth = $request->month;

            $countMissingSubKegiatan = 0;
            $missingSubKegiatan = [];
            $messages = [];

            $file = $request->file('file');
            $path = $file->store('rkp5', 'public');
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

            $highestRow = $sheet->getHighestRow();
            $highestColumn = $sheet->getHighestColumn();
            $highestColumnIndex = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::columnIndexFromString($highestColumn);

            for ($row = 2; $row <= $highestRow; $row++) {
                $tahun = $sheet->getCellByColumnAndRow(2, $row)->getValue();
                $kodeUrusan = $sheet->getCellByColumnAndRow(3, $row)->getValue();
                $namaUrusan = $sheet->getCellByColumnAndRow(4, $row)->getValue();
                $kodeSkpd = $sheet->getCellByColumnAndRow(5, $row)->getValue();
                $namaSkpd = $sheet->getCellByColumnAndRow(6, $row)->getValue();
                $kodeSubUnit = $sheet->getCellByColumnAndRow(7, $row)->getValue();
                $namaSubUnit = $sheet->getCellByColumnAndRow(8, $row)->getValue();
                $kodeBidangUrusan = $sheet->getCellByColumnAndRow(9, $row)->getValue();
                $namaBidangUrusan = $sheet->getCellByColumnAndRow(10, $row)->getValue();
                $kodeProgram = $sheet->getCellByColumnAndRow(11, $row)->getValue();
                $namaProgram = $sheet->getCellByColumnAndRow(12, $row)->getValue();
                $kodeKegiatan = $sheet->getCellByColumnAndRow(13, $row)->getValue();
                $namaKegiatan = $sheet->getCellByColumnAndRow(14, $row)->getValue();
                $kodeSubKegiatan = $sheet->getCellByColumnAndRow(15, $row)->getValue();
                $namaSubKegiatan = $sheet->getCellByColumnAndRow(16, $row)->getValue();
                $kodeSumberDana = $sheet->getCellByColumnAndRow(17, $row)->getValue();
                $namaSumberDana = $sheet->getCellByColumnAndRow(18, $row)->getValue();
                $kodeRekening = $sheet->getCellByColumnAndRow(19, $row)->getValue();
                $namaRekening = $sheet->getCellByColumnAndRow(20, $row)->getValue();
                $jenis = $sheet->getCellByColumnAndRow(21, $row)->getValue();
                $namaPaket = $sheet->getCellByColumnAndRow(22, $row)->getValue();
                $pagu = $sheet->getCellByColumnAndRow(23, $row)->getValue();

                if ($kodeSkpd == '4.01.0.00.0.00.01.0000') {
                    $instance = Instance::where('code', $kodeSkpd)->first();
                } else {
                    $instance = Instance::where('code', $kodeSubUnit)->first();
                }
                // if (!$instance) {
                //     // return $this->errorResponse('Perangkat Daerah tidak ditemukan');
                //     continue;
                // }
                if ($instance) {
                    // Makai Get Karena Kode Sub Kegiatan Mungkin memiliki lebih dari satu sub kegiatan di database
                    $arrSubKegiatan = SubKegiatan::where('fullcode', $kodeSubKegiatan)
                        ->where('instance_id', $instance->id)
                        ->get();

                    $sumberDana = KodeSumberDana::where('fullcode', $kodeSumberDana)->first();
                    if (!$sumberDana && $kodeSumberDana) {
                        $fullcode = null;
                        $code1 = null;
                        $code2 = null;
                        $code3 = null;
                        $code4 = null;
                        $code5 = null;
                        $code6 = null;
                        if ($fullcode !== null) {
                            $fullcode = (string)$kodeSumberDana;
                            $code1 = substr($fullcode, 0, 1);
                            $code2 = substr($fullcode, 2, 1);
                            if ($code2 === '') {
                                $code2 = null;
                            }
                            $code3 = substr($fullcode, 4, 2);
                            if ($code3 === '') {
                                $code3 = null;
                            }
                            $code4 = substr($fullcode, 7, 2);
                            if ($code4 === '') {
                                $code4 = null;
                            }
                            $code5 = substr($fullcode, 10, 2);
                            if ($code5 === '') {
                                $code5 = null;
                            }
                            $code6 = substr($fullcode, 13, 4);
                            if ($code6 === '') {
                                $code6 = null;
                            }
                        }
                        $sumberDana = new KodeSumberDana();
                        $sumberDana->fullcode = $kodeSumberDana;
                        $sumberDana->name = $namaSumberDana;
                        $sumberDana->periode_id = 1;
                        $sumberDana->year = $tahun;
                        $sumberDana->code_1 = $code1;
                        $sumberDana->code_2 = $code2;
                        $sumberDana->code_3 = $code3;
                        $sumberDana->code_4 = $code4;
                        $sumberDana->code_5 = $code5;
                        $sumberDana->code_6 = $code6;
                        $sumberDana->created_by = auth()->user()->id;

                        $parent = KodeSumberDana::where('fullcode', substr($kodeSumberDana, 0, 4))->first();
                        if ($parent) {
                            $sumberDana->parent_id = $parent->id;
                        }
                        $sumberDana->save();
                    }

                    $rekening = KodeRekening::where('fullcode', $kodeRekening)->first() ?? null;
                    if (!$rekening) {
                        $expKodeRekening = Str::of($kodeRekening)->explode(".");
                        if ($expKodeRekening->count() == 6) {
                            $rekening = new KodeRekening();
                            $rekening->code_1 = $expKodeRekening[0];
                            $rekening->code_2 = $expKodeRekening[1];
                            $rekening->code_3 = $expKodeRekening[2];
                            $rekening->code_4 = $expKodeRekening[3];
                            $rekening->code_5 = $expKodeRekening[4];
                            $rekening->code_6 = $expKodeRekening[5];
                            $rekening->fullcode = $kodeRekening;
                            $rekening->name = $namaRekening;
                            $rekening->periode_id = $request->periode ?? 1;
                            $rekening->year = $tahun;
                            $rekening->parent_id = KodeRekening::where('code_1', $rekening->code_1)
                                ->where('code_2', $rekening->code_2)
                                ->where('code_3', $rekening->code_3)
                                ->where('code_4', $rekening->code_4)
                                ->where('code_5', $rekening->code_5)
                                ->first()->id ?? null;
                            $rekening->save();
                        }
                    }

                    if ($rekening) {
                        if ($arrSubKegiatan->count() > 0) {
                            foreach ($arrSubKegiatan as $subKegiatan) {
                                if ($subKegiatan) {
                                    $periode = Periode::whereYear('start_date', '<=', $tahun)
                                        ->whereYear('end_date', '>=', $tahun)
                                        ->first();

                                    $arrMonths = [1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 11, 12];
                                    $arrMonths = collect($arrMonths)->skip($reqMonth - 1);
                                    $arrMonths = $arrMonths->values()->toArray();

                                    foreach ($arrMonths as $month) {
                                        $targetKinerja = TargetKinerja::where('year', $tahun)
                                            ->where('month', $month)
                                            ->where('instance_id', $instance->id)
                                            ->where('sub_kegiatan_id', $subKegiatan->id)
                                            ->where('kode_rekening_id', $rekening->id)
                                            ->where('sumber_dana_id', $sumberDana->id)
                                            ->where('type', $jenis)
                                            ->where('nama_paket', $namaPaket)
                                            ->first();
                                        if (!$targetKinerja) {
                                            $targetKinerja = new TargetKinerja();
                                            $targetKinerja->year = $tahun;
                                            $targetKinerja->month = $month;
                                            $targetKinerja->instance_id = $instance->id;
                                            $targetKinerja->urusan_id = $subKegiatan->urusan_id ?? null;
                                            $targetKinerja->bidang_urusan_id = $subKegiatan->bidang_id ?? null;
                                            $targetKinerja->program_id = $subKegiatan->program_id ?? null;
                                            $targetKinerja->kegiatan_id = $subKegiatan->kegiatan_id ?? null;
                                            $targetKinerja->sub_kegiatan_id = $subKegiatan->id ?? null;
                                            $targetKinerja->created_by = auth()->user()->id;
                                            $targetKinerja->status = 'draft';
                                            $targetKinerja->status_leader = 'draft';
                                        }


                                        // Pengecualian Detail Start
                                        if ($rekening) {
                                            // Jika Kode 5.2
                                            if ($rekening->code_1 === '5' && $rekening->code_2 === '2') {
                                                $targetKinerja->is_detail = TRUE;
                                            }
                                            // Jika Kode 5.1.01, 5.1.03, 5.1.04
                                            if (
                                                $rekening->code_1 === '5' && $rekening->code_2 === '1' &&
                                                ($rekening->code_3 === '01' || $rekening->code_3 === '03' || $rekening->code_3 === '04')
                                            ) {

                                                $targetKinerja->is_detail = TRUE;
                                            }
                                        }

                                        $targetKinerja->sumber_dana_id = $sumberDana->id ?? null;
                                        $targetKinerja->kode_rekening_id = $rekening->id ?? null;
                                        $targetKinerja->pagu_sipd = $pagu ?? 0;
                                        // $targetKinerja->pagu_sebelum_pergeseran = $rekening->pagu_sebelum_pergeseran;
                                        // $targetKinerja->pagu_setelah_pergeseran = $rekening->pagu_setelah_pergeseran;
                                        // $targetKinerja->pagu_selisih = $rekening->pagu_selisih;

                                        $targetKinerja->periode_id = $periode->id;
                                        $targetKinerja->type = $jenis;
                                        $targetKinerja->nama_paket = $namaPaket;
                                        $targetKinerja->save();

                                        $realisasi = Realisasi::where('year', $tahun)
                                            ->where('month', $month)
                                            ->where('periode_id', $periode->id)
                                            ->where('instance_id', $instance->id)
                                            ->where('target_id', $targetKinerja->id)
                                            ->where('sub_kegiatan_id', $subKegiatan->id)
                                            ->where('kode_rekening_id', $rekening->id)
                                            ->where('sumber_dana_id', $sumberDana->id)
                                            ->where('type', $jenis)
                                            ->where('nama_paket', $namaPaket)
                                            ->first();
                                        if (!$realisasi) {
                                            $realisasi = new Realisasi();
                                            $realisasi->periode_id = $periode->id;
                                            $realisasi->year = $tahun;
                                            $realisasi->month = $month;
                                            $realisasi->instance_id = $instance->id;
                                            $realisasi->target_id = $targetKinerja->id;
                                            $realisasi->urusan_id = $subKegiatan->urusan_id ?? null;
                                            $realisasi->bidang_urusan_id = $subKegiatan->bidang_id ?? null;
                                            $realisasi->program_id = $subKegiatan->program_id ?? null;
                                            $realisasi->kegiatan_id = $subKegiatan->kegiatan_id ?? null;
                                            $realisasi->sub_kegiatan_id = $subKegiatan->id ?? null;
                                            $realisasi->kode_rekening_id = $rekening->id ?? null;
                                            $realisasi->sumber_dana_id = $sumberDana->id ?? null;
                                            $realisasi->type = $jenis;
                                            $realisasi->nama_paket = $namaPaket;
                                            $realisasi->created_by = auth()->user()->id;
                                            $realisasi->status = 'draft';
                                            $realisasi->status_leader = 'draft';
                                            $realisasi->save();
                                        }
                                    }
                                }
                            }
                        } else {
                            $countMissingSubKegiatan++;
                            $missingSubKegiatan[] = [
                                'kode_sub_kegiatan' => $kodeSubKegiatan,
                                'nama_sub_kegiatan' => $namaSubKegiatan,
                                'nama_instansi' => $namaSubUnit,
                            ];
                        }
                    }
                }
            }

            if (count($missingSubKegiatan) > 0) {
                $missingSubKegiatan = collect($missingSubKegiatan);
                // $missingSubKegiatan remove duplicate data
                $missingSubKegiatan = $missingSubKegiatan->unique('kode_sub_kegiatan');
                $missingSubKegiatan = $missingSubKegiatan->sortBy('kode_sub_kegiatan')->values()->all();

                $messages['message'] = 'Terdapat ' . count($missingSubKegiatan) . ' Sub Kegiatan yang tidak terdeteksi';
                $messages['missing_data'] = $missingSubKegiatan;
            }

            $logs = DB::table('sipd_upload_logs')
                ->insert([
                    'file_name' => $file->getClientOriginalName(),
                    'file_path' => $path,
                    'status' => 'success',
                    'message' => json_encode($messages),
                    'type' => 'target-belanja',
                    'user_id' => auth()->user()->id,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);

            DB::commit();
            return $this->successResponse($messages, 'Data Berhasil disimpan');
        } catch (\Throwable $th) {
            DB::rollback();
            return $this->errorResponse($th->getMessage() . ' -> ' . $th->getLine() . ' -> ' . $th->getFile());
        }
    }

    function uploadAPBDdariRekap5(Request $request)
    {
        // return $request->all();
        // set time limit to 0
        // set_time_limit(0);
        $validate = Validator::make($request->all(), [
            'periode' => 'required|numeric|exists:ref_periode,id',
            'file' => 'required|file|mimes:xlsx,xls',
            'year' => 'required|numeric',
            'month' => 'required|numeric|between:1,12',
            'monthTo' => 'required|numeric|between:1,12|min:' . $request->month,
        ], [], [
            'periode' => 'Periode',
            'file' => 'Berkas Excel',
            'year' => 'Tahun',
            'month' => 'Bulan',
            'monthTo' => 'Bulan',
        ]);
        if ($validate->fails()) {
            return $this->validationResponse($validate->errors());
        }

        DB::beginTransaction();
        try {
            $reqTahun = $request->year ?? date('Y');
            $reqMonth = $request->month ?? 1;
            $reqMonthTo = $request->monthTo ?? 12;
            $now = now();

            $files = glob(storage_path('app/public/rkp5/*'));
            foreach ($files as $file) {
                if (is_file($file)) {
                    unlink($file);
                }
            }

            $countDatas = 0;
            $countMissingSubKegiatan = 0;
            $missingSubKegiatan = [];
            $missingKodeRekening = [];
            $messages = [];

            $file = $request->file('file');
            $path = $file->store('rkp5', 'public');
            $path = str_replace('public/', '', $path);
            $path = storage_path('app/public/' . $path);

            $reader = new \PhpOffice\PhpSpreadsheet\Reader\Xlsx();
            $spreadsheet = $reader->load($path);
            $sheet = $spreadsheet->getActiveSheet();

            $allData = [];
            $highestRow = $sheet->getHighestRow();
            $highestColumn = $sheet->getHighestColumn();
            $highestColumnIndex = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::columnIndexFromString($highestColumn);
            $allData = $sheet->rangeToArray('A1:' . $highestColumn . $highestRow, null, true, true, true);
            $allData = collect($allData);
            $allData = $allData->skip(1)->values();


            $arrDataBySubKegiatans = $allData->groupBy('O');
            foreach ($arrDataBySubKegiatans as $excelSubKegiatan) {
                $arrDataByKodeRekenings = collect($excelSubKegiatan)->groupBy('S');

                foreach ($arrDataByKodeRekenings as $index => $xKodeRek) {
                    $pagu = 0;
                    foreach ($xKodeRek as $d) {
                        $extractedPagu = $d['W'];
                        // // $extractedPagu = str_replace('.', '', $d['W']);
                        // $extractedPagu = str_replace(',00', '', $extractedPagu);
                        // $extractedPagu = str_replace('-0', '', $extractedPagu);
                        // $extractedPagu = number_format((float)$extractedPagu, 2, '.', '');
                        $extractedPagu = (float)$extractedPagu;
                        $pagu += $extractedPagu;
                    }
                    $xcelSubKegiatan = $xKodeRek[0]['O'];
                    $xcelKodeRekening = $xKodeRek[0]['S'];

                    // AGAK RIBET UNTUK KODE NOMENKLATUR
                    $xcelInstanceCode = $xKodeRek[0]['G'];
                    $excelKodeSKPD_E = $xKodeRek[0]['E'];
                    if ($excelKodeSKPD_E == '4.01.0.00.0.00.01.0000') {
                        $instance = DB::table('instances')
                            ->where('code', '4.01.0.00.0.00.01.0000')
                            ->first();
                    } elseif ($excelKodeSKPD_E == '1.02.0.00.0.00.01.0000') {
                        $instance = DB::table('instances')
                            ->where('code', $xcelInstanceCode)
                            ->first();
                        if (!$instance) {
                            $instance = DB::table('instances')
                                ->where('code', '1.02.0.00.0.00.01.0000')
                                ->first();
                        }
                    } elseif (in_array($excelKodeSKPD_E, [
                        '7.01.0.00.0.00.06.0000', // PAYARAMAN
                        '7.01.0.00.0.00.05.0000', // TANJUNG BATU
                        '7.01.0.00.0.00.10.0000', // INDRALAYA
                        '7.01.0.00.0.00.12.0000', // INDRALAYA UTARA
                    ])) {
                        $instance = DB::table('instances')
                            ->where('code', $xcelInstanceCode)
                            ->first();
                        if (!$instance) {
                            $instance = DB::table('instances')
                                ->where('code', $excelKodeSKPD_E)
                                ->first();
                        }
                    } else {
                        $instance = DB::table('instances')
                            ->where('code', $xcelInstanceCode)
                            ->first();
                    }
                    // AGAK RIBET UNTUK KODE NOMENKLATUR

                    if ($instance) {
                        // input data target kinerja di sini !!!!!
                        $subKegiatan = null;
                        if ($xcelSubKegiatan) {
                            $subKegiatan = DB::table('ref_sub_kegiatan')
                                ->where('instance_id', $instance->id)
                                ->where('fullcode', $xcelSubKegiatan)
                                ->first();
                        }
                        if ($xcelKodeRekening) {
                            $kodeRekening = DB::table('ref_kode_rekening_complete')
                                ->where('fullcode', $xcelKodeRekening)
                                ->first();
                            if (!$kodeRekening) {
                                $kodeRekening = $this->_InputKodeRekeningBaru($xKodeRek[0]['S'], $xKodeRek[0]['T'], $request->periode ?? 1, $reqTahun);
                            }
                        }
                        if (!$kodeRekening) {
                            $missingKodeRekening[] = 'Kode Rekening ' . $xcelKodeRekening . ' tidak ditemukan';
                            continue;
                        }

                        $xcelSumberDana = $xKodeRek[0]['Q'];
                        if ($xcelSumberDana) {
                            $sumberDana = DB::table('ref_kode_sumber_dana')
                                ->where('fullcode', $xcelSumberDana)
                                ->first();
                            if (!$sumberDana) {
                                $fullcode = null;
                                $code1 = null;
                                $code2 = null;
                                $code3 = null;
                                $code4 = null;
                                $code5 = null;
                                $code6 = null;
                                if ($fullcode !== null) {
                                    $fullcode = (string)$xcelSumberDana;
                                    $code1 = substr($fullcode, 0, 1);
                                    $code2 = substr($fullcode, 2, 1);
                                    if ($code2 === '') {
                                        $code2 = null;
                                    }
                                    $code3 = substr($fullcode, 4, 2);
                                    if ($code3 === '') {
                                        $code3 = null;
                                    }
                                    $code4 = substr($fullcode, 7, 2);
                                    if ($code4 === '') {
                                        $code4 = null;
                                    }
                                    $code5 = substr($fullcode, 10, 2);
                                    if ($code5 === '') {
                                        $code5 = null;
                                    }
                                    $code6 = substr($fullcode, 13, 4);
                                    if ($code6 === '') {
                                        $code6 = null;
                                    }
                                }
                                $sumberDana = DB::table('ref_kode_sumber_dana')
                                    ->insertGetId([
                                        'fullcode' => $xcelSumberDana,
                                        'name' => $xKodeRek[0]['R'],
                                        'periode_id' => 1,
                                        'year' => $reqTahun,
                                        'code_1' => $code1,
                                        'code_2' => $code2,
                                        'code_3' => $code3,
                                        'code_4' => $code4,
                                        'code_5' => $code5,
                                        'code_6' => $code6,
                                        'created_by' => auth()->user()->id === 1 ? 6 : auth()->user()->id,
                                        'created_at' => $now,
                                        'updated_at' => $now,
                                    ]);

                                $sumberDana = DB::table('ref_kode_sumber_dana')
                                    ->where('fullcode', $xcelSumberDana)
                                    ->first();

                                $parent = DB::table('ref_kode_sumber_dana')
                                    ->where('fullcode', substr($xcelSumberDana, 0, 4))
                                    ->first();
                                if ($parent) {
                                    $sumberDana->parent_id = $parent->id;
                                    $sumberDana->save();
                                }
                            }
                        }
                        if (!$subKegiatan) {
                            $missingSubKegiatan[] = [
                                'kode_sub_kegiatan' => $xcelSubKegiatan,
                                'nama_sub_kegiatan' => $xKodeRek[0]['P'],
                            ];
                            $countMissingSubKegiatan++;
                            continue;
                        }

                        if ($subKegiatan) {
                            for ($month = $reqMonth; $month <= $reqMonthTo; $month++) {
                                $existTargetKinerja = DB::table('data_target_kinerja')
                                    ->where('year', $reqTahun)
                                    ->where('month', $month)
                                    ->where('instance_id', $subKegiatan->instance_id)
                                    ->where('sub_kegiatan_id', $subKegiatan->id)
                                    ->where('kode_rekening_id', $kodeRekening->id)
                                    ->where('sumber_dana_id', $sumberDana->id)
                                    ->where('type', $xKodeRek[0]['U'])
                                    ->first();

                                $targetKinerjaID = null;
                                if (!$existTargetKinerja) {
                                    $targetKinerja = DB::table('data_target_kinerja')
                                        ->insertGetId([
                                            'periode_id' => $request->periode,
                                            'year' => $reqTahun,
                                            'month' => $month,
                                            'instance_id' => $subKegiatan->instance_id,
                                            'urusan_id' => $subKegiatan->urusan_id,
                                            'bidang_urusan_id' => $subKegiatan->bidang_id,
                                            'program_id' => $subKegiatan->program_id,
                                            'kegiatan_id' => $subKegiatan->kegiatan_id,
                                            'sub_kegiatan_id' => $subKegiatan->id,
                                            'kode_rekening_id' => $kodeRekening->id,
                                            'sumber_dana_id' => $sumberDana->id,
                                            'type' => $xKodeRek[0]['U'] ?? null,
                                            'pagu_sipd' => $pagu,
                                            'is_detail' => false,
                                            'nama_paket' => $xKodeRek[0]['V'] ?? null,
                                            'status' => 'verified',
                                            'status_leader' => 'draft',
                                            'created_by' => auth()->user()->id === 1 ? 6 : auth()->user()->id,
                                            'created_at' => $now,
                                            'updated_at' => $now,
                                        ]);
                                    $targetKinerjaID = $targetKinerja;
                                } else {
                                    if ($request->type == 'pagu_induk') {
                                        $targetKinerja = DB::table('data_target_kinerja')
                                            ->where('year', $reqTahun)
                                            ->where('month', $month)
                                            ->where('instance_id', $subKegiatan->instance_id)
                                            ->where('sub_kegiatan_id', $subKegiatan->id)
                                            ->where('kode_rekening_id', $kodeRekening->id)
                                            ->where('sumber_dana_id', $sumberDana->id)
                                            ->where('type', $xKodeRek[0]['U'])
                                            ->update([
                                                'pagu_sipd' => $pagu,
                                                'updated_at' => $now,
                                                'status' => 'verified',
                                                'updated_by' => auth()->user()->id === 1 ? 6 : auth()->user()->id,
                                            ]);
                                    } else if ($request->type == 'pagu_perubahan') {
                                        $targetKinerja = DB::table('data_target_kinerja')
                                            ->where('year', $reqTahun)
                                            ->where('month', $month)
                                            ->where('instance_id', $subKegiatan->instance_id)
                                            ->where('sub_kegiatan_id', $subKegiatan->id)
                                            ->where('kode_rekening_id', $kodeRekening->id)
                                            ->where('sumber_dana_id', $sumberDana->id)
                                            ->where('type', $xKodeRek[0]['U'])
                                            ->update([
                                                'pagu_perubahan' => $pagu,
                                                'tanggal_perubahan' => $request->date,
                                                'updated_at' => $now,
                                                'status' => 'verified',
                                                'updated_by' => auth()->user()->id === 1 ? 6 : auth()->user()->id,
                                            ]);
                                    } else if ($request->type == 'pagu_pergeseran_1') {
                                        $targetKinerja = DB::table('data_target_kinerja')
                                            ->where('year', $reqTahun)
                                            ->where('month', $month)
                                            ->where('instance_id', $subKegiatan->instance_id)
                                            ->where('sub_kegiatan_id', $subKegiatan->id)
                                            ->where('kode_rekening_id', $kodeRekening->id)
                                            ->where('sumber_dana_id', $sumberDana->id)
                                            ->where('type', $xKodeRek[0]['U'])
                                            ->update([
                                                'pagu_pergeseran_1' => $pagu,
                                                'tanggal_pergeseran_1' => $request->date,
                                                'updated_at' => $now,
                                                'status' => 'verified',
                                                'updated_by' => auth()->user()->id === 1 ? 6 : auth()->user()->id,
                                            ]);
                                    } else if ($request->type == 'pagu_pergeseran_2') {
                                        $targetKinerja = DB::table('data_target_kinerja')
                                            ->where('year', $reqTahun)
                                            ->where('month', $month)
                                            ->where('instance_id', $subKegiatan->instance_id)
                                            ->where('sub_kegiatan_id', $subKegiatan->id)
                                            ->where('kode_rekening_id', $kodeRekening->id)
                                            ->where('sumber_dana_id', $sumberDana->id)
                                            ->where('type', $xKodeRek[0]['U'])
                                            ->update([
                                                'pagu_pergeseran_2' => $pagu,
                                                'tanggal_pergeseran_2' => $request->date,
                                                'updated_at' => $now,
                                                'status' => 'verified',
                                                'updated_by' => auth()->user()->id === 1 ? 6 : auth()->user()->id,
                                            ]);
                                    } else if ($request->type == 'pagu_pergeseran_3') {
                                        $targetKinerja = DB::table('data_target_kinerja')
                                            ->where('year', $reqTahun)
                                            ->where('month', $month)
                                            ->where('instance_id', $subKegiatan->instance_id)
                                            ->where('sub_kegiatan_id', $subKegiatan->id)
                                            ->where('kode_rekening_id', $kodeRekening->id)
                                            ->where('sumber_dana_id', $sumberDana->id)
                                            ->where('type', $xKodeRek[0]['U'])
                                            ->update([
                                                'pagu_pergeseran_3' => $pagu,
                                                'tanggal_pergeseran_3' => $request->date,
                                                'updated_at' => $now,
                                                'status' => 'verified',
                                                'updated_by' => auth()->user()->id === 1 ? 6 : auth()->user()->id,
                                            ]);
                                    } else if ($request->type == 'pagu_pergeseran_4') {
                                        $targetKinerja = DB::table('data_target_kinerja')
                                            ->where('year', $reqTahun)
                                            ->where('month', $month)
                                            ->where('instance_id', $subKegiatan->instance_id)
                                            ->where('sub_kegiatan_id', $subKegiatan->id)
                                            ->where('kode_rekening_id', $kodeRekening->id)
                                            ->where('sumber_dana_id', $sumberDana->id)
                                            ->where('type', $xKodeRek[0]['U'])
                                            ->update([
                                                'pagu_pergeseran_4' => $pagu,
                                                'tanggal_pergeseran_4' => $request->date,
                                                'updated_at' => $now,
                                                'status' => 'verified',
                                                'updated_by' => auth()->user()->id === 1 ? 6 : auth()->user()->id,
                                            ]);
                                    }

                                    $targetKinerjaID = $existTargetKinerja->id;
                                }

                                if ($targetKinerja) {
                                    $existRealisasi = DB::table('data_realisasi')
                                        ->where('year', $reqTahun)
                                        ->where('month', $month)
                                        ->where('target_id', $targetKinerjaID)
                                        ->where('instance_id', $subKegiatan->instance_id)
                                        ->where('sub_kegiatan_id', $subKegiatan->id)
                                        ->where('kode_rekening_id', $kodeRekening->id)
                                        ->where('sumber_dana_id', $sumberDana->id)
                                        ->where('type', $xKodeRek[0]['U'])
                                        ->first();
                                    if (!$existRealisasi) {
                                        DB::table('data_realisasi')
                                            ->insertGetId([
                                                'periode_id' => $request->periode,
                                                'year' => $reqTahun,
                                                'month' => $month,
                                                'instance_id' => $subKegiatan->instance_id,
                                                'target_id' => $targetKinerjaID,
                                                'urusan_id' => $subKegiatan->urusan_id,
                                                'bidang_urusan_id' => $subKegiatan->bidang_id,
                                                'program_id' => $subKegiatan->program_id,
                                                'kegiatan_id' => $subKegiatan->kegiatan_id,
                                                'sub_kegiatan_id' => $subKegiatan->id,
                                                'kode_rekening_id' => $kodeRekening->id,
                                                'sumber_dana_id' => $sumberDana->id,
                                                'type' => $xKodeRek[0]['U'] ?? null,
                                                'nama_paket' => $xKodeRek[0]['V'] ?? null,
                                                'anggaran' => 0,
                                                'anggaran_bulan_ini' => 0,
                                                'status' => 'draft',
                                                'status_leader' => 'draft',
                                                'created_by' => 1,
                                                'created_at' => $now,
                                                'updated_at' => $now,
                                            ]);
                                    }

                                    $existsDetailApbdSubKeg = DB::table('data_apbd_detail_sub_kegiatan')
                                        ->where('year', $reqTahun)
                                        ->where('month', $month)
                                        ->where('instance_id', $subKegiatan->instance_id)
                                        ->where('program_id', $subKegiatan->program_id)
                                        ->where('kegiatan_id', $subKegiatan->kegiatan_id)
                                        ->where('sub_kegiatan_id', $subKegiatan->id)
                                        ->first();
                                    if (!$existsDetailApbdSubKeg) {
                                        DB::table('data_apbd_detail_sub_kegiatan')
                                            ->insert([
                                                'instance_id' => $subKegiatan->instance_id,
                                                'program_id' => $subKegiatan->program_id,
                                                'kegiatan_id' => $subKegiatan->kegiatan_id,
                                                'sub_kegiatan_id' => $subKegiatan->id,
                                                'year' => $reqTahun,
                                                'month' => $month,
                                                'anggaran_modal' => $pagu,
                                                'total_anggaran' => $pagu,
                                                'percent_anggaran' => 100,
                                                'percent_kinerja' => 100,
                                                'status' => 'active',
                                                'created_by' => 1,
                                                'created_at' => $now,
                                                'updated_at' => $now,
                                            ]);
                                    }
                                }
                            }
                        }
                    }

                    $countDatas++;
                }
            }

            $messages = [
                'message' => 'Data Berhasil disimpan',
                'note' => $request->message ?? null,
                'datas_count' => count($allData),
                'missing_data' => $missingSubKegiatan,
                'missing_data_count' => $countMissingSubKegiatan,
                'missing_kode_rekening' => $missingKodeRekening,
            ];

            $logs = DB::table('sipd_upload_logs')
                ->insert([
                    'file_name' => $file->getClientOriginalName(),
                    'file_path' => $path,
                    'status' => 'success',
                    'message' => json_encode($messages, true),
                    'type' => 'apbd',
                    'user_id' => auth()->id(),
                    'created_at' => $now,
                    'updated_at' => now(),
                ]);

            DB::commit();
            // return $allData;
            return $this->successResponse($messages, 'Data Berhasil disimpan');
        } catch (\Throwable $th) {
            DB::rollback();
            $logs = DB::table('sipd_upload_logs')
                ->insert([
                    'file_name' => $file->getClientOriginalName(),
                    'file_path' => $path,
                    'status' => 'error',
                    'message' => $th->getMessage() . ' - ' . $th->getLine() . ' - ' . $th->getFile(),
                    'type' => 'apbd',
                    'user_id' => auth()->id(),
                    'created_at' => $now,
                    'updated_at' => now(),
                ]);
            DB::commit();

            return $this->errorResponse($th->getMessage() . ' - ' . $th->getLine() . ' - ' . $th->getFile());
        }
    }

    function _InputKodeRekeningBaru($kode, $nama, $reqPeriode, $reqTahun)
    {
        $expKodeRekening = Str::of($kode)->explode(".");
        if ($expKodeRekening->count() == 6) {
            $parent5 = KodeRekening::where('code_1', $expKodeRekening[0])
                ->where('code_2', $expKodeRekening[1])
                ->where('code_3', $expKodeRekening[2])
                ->where('code_4', $expKodeRekening[3])
                ->where('code_5', $expKodeRekening[4])
                ->first();
            if (!$parent5) {
                $parent4 = KodeRekening::where('code_1', $expKodeRekening[0])
                    ->where('code_2', $expKodeRekening[1])
                    ->where('code_3', $expKodeRekening[2])
                    ->where('code_4', $expKodeRekening[3])
                    ->first();
                if (!$parent4) {
                    $parent3 = KodeRekening::where('code_1', $expKodeRekening[0])
                        ->where('code_2', $expKodeRekening[1])
                        ->where('code_3', $expKodeRekening[2])
                        ->first();
                    if (!$parent3) {
                        $parent2 = KodeRekening::where('code_1', $expKodeRekening[0])
                            ->where('code_2', $expKodeRekening[1])
                            ->first();
                        if (!$parent2) {
                            $parent1 = KodeRekening::where('code_1', $expKodeRekening[0])
                                ->first();
                            if (!$parent1) {
                                $parent1 = new KodeRekening();
                                $parent1->code_1 = $expKodeRekening[0];
                                $parent1->code_2 = null;
                                $parent1->code_3 = null;
                                $parent1->code_4 = null;
                                $parent1->code_5 = null;
                                $parent1->code_6 = null;
                                $parent1->fullcode = $expKodeRekening[0];
                                $parent1->name = $nama;
                                $parent1->periode_id = $reqPeriode;
                                $parent1->year = $reqTahun;
                                $parent1->parent_id = null;
                                $parent1->save();
                            }
                            $parent2 = new KodeRekening();
                            $parent2->code_1 = $expKodeRekening[0];
                            $parent2->code_2 = $expKodeRekening[1];
                            $parent2->code_3 = null;
                            $parent2->code_4 = null;
                            $parent2->code_5 = null;
                            $parent2->code_6 = null;
                            $parent2->fullcode = $expKodeRekening[0] . '.' . $expKodeRekening[1];
                            $parent2->name = $nama;
                            $parent2->periode_id = $reqPeriode;
                            $parent2->year = $reqTahun;
                            $parent2->parent_id = $parent1->id;
                            $parent2->save();
                        }
                        $parent3 = new KodeRekening();
                        $parent3->code_1 = $expKodeRekening[0];
                        $parent3->code_2 = $expKodeRekening[1];
                        $parent3->code_3 = $expKodeRekening[2];
                        $parent3->code_4 = null;
                        $parent3->code_5 = null;
                        $parent3->code_6 = null;
                        $parent3->fullcode = $expKodeRekening[0] . '.' . $expKodeRekening[1] . '.' . $expKodeRekening[2];
                        $parent3->name = $nama;
                        $parent3->periode_id = $reqPeriode;
                        $parent3->year = $reqTahun;
                        $parent3->parent_id = $parent2->id;
                        $parent3->save();
                    }
                    $parent4 = new KodeRekening();
                    $parent4->code_1 = $expKodeRekening[0];
                    $parent4->code_2 = $expKodeRekening[1];
                    $parent4->code_3 = $expKodeRekening[2];
                    $parent4->code_4 = $expKodeRekening[3];
                    $parent4->code_5 = null;
                    $parent4->code_6 = null;
                    $parent4->fullcode = $expKodeRekening[0] . '.' . $expKodeRekening[1] . '.' . $expKodeRekening[2] . '.' . $expKodeRekening[3];
                    $parent4->name = $nama;
                    $parent4->periode_id = $reqPeriode;
                    $parent4->year = $reqTahun;
                    $parent4->parent_id = $parent3->id;
                    $parent4->save();
                }
                $parent5 = new KodeRekening();
                $parent5->code_1 = $expKodeRekening[0];
                $parent5->code_2 = $expKodeRekening[1];
                $parent5->code_3 = $expKodeRekening[2];
                $parent5->code_4 = $expKodeRekening[3];
                $parent5->code_5 = $expKodeRekening[4];
                $parent5->code_6 = null;
                $parent5->fullcode = $expKodeRekening[0] . '.' . $expKodeRekening[1] . '.' . $expKodeRekening[2] . '.' . $expKodeRekening[3] . '.' . $expKodeRekening[4];
                $parent5->name = $nama;
                $parent5->periode_id = $reqPeriode;
                $parent5->year = $reqTahun;
                $parent5->parent_id = $parent4->id;
                $parent5->save();
            }

            $rekening = new KodeRekening();
            $rekening->code_1 = $expKodeRekening[0];
            $rekening->code_2 = $expKodeRekening[1];
            $rekening->code_3 = $expKodeRekening[2];
            $rekening->code_4 = $expKodeRekening[3];
            $rekening->code_5 = $expKodeRekening[4];
            $rekening->code_6 = $expKodeRekening[5];
            $rekening->fullcode = $kode;
            $rekening->name = $nama;
            $rekening->periode_id = $reqPeriode;
            $rekening->year = $reqTahun;
            $rekening->parent_id = $parent5->id ?? null;
            $rekening->save();
            return $rekening;
        }
    }

    function uploadRekap5KeProgramKegiatan(Request $request)
    {
        set_time_limit(0);
        $validate = Validator::make($request->all(), [
            'periode' => 'required|numeric|exists:ref_periode,id',
            'file' => 'required|file|mimes:xlsx,xls',
        ], [], [
            'periode' => 'Periode',
            'file' => 'Berkas Excel',
        ]);
        if ($validate->fails()) {
            return $this->validationResponse($validate->errors());
        }

        $files = glob(storage_path('app/public/rkp5prgmkeg/*'));
        foreach ($files as $file) {
            if (is_file($file)) {
                unlink($file);
            }
        }
        DB::beginTransaction();
        try {
            $now = now();
            $periode = $request->periode;

            $files = glob(storage_path('app/public/rkp5prgmkeg/*'));
            foreach ($files as $file) {
                if (is_file($file)) {
                    unlink($file);
                }
            }

            $countMissingSubKegiatan = 0;
            $missingSubKegiatan = [];
            $messages = [];

            $file = $request->file('file');
            $path = $file->store('rkp5prgmkeg', 'public');
            $path = str_replace('public/', '', $path);
            $path = storage_path('app/public/' . $path);

            $reader = new \PhpOffice\PhpSpreadsheet\Reader\Xlsx();
            $spreadsheet = $reader->load($path);
            $sheet = $spreadsheet->getActiveSheet();

            $allData = [];
            $highestRow = $sheet->getHighestRow();
            $highestColumn = $sheet->getHighestColumn();
            $highestColumnIndex = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::columnIndexFromString($highestColumn);
            $allData = $sheet->rangeToArray('A1:' . $highestColumn . $highestRow, null, true, true, true);
            $allData = collect($allData);
            $headData = $allData->first();
            // check format excel
            if ($headData['A'] != 'NO' && $headData['B'] != 'TAHUN' && $headData['C'] != 'KODE URUSAN' && $headData['G'] != 'KODE SUB UNIT' && $headData['O'] != 'KODE SUB KEGIATAN' && $headData['P'] != 'NAMA SUB KEGIATAN' && $headData['S'] != 'KODE REKENING' && $headData['T'] != 'NAMA REKENING' && $headData['V'] != 'NAMA PAKET/KELOMPOK' && $headData['W'] != 'PAGU') {
                return $this->errorResponse('Format Excel tidak sesuai');
            }
            $allData = $allData->skip(1)->values();

            $arrKodeSubUnit = collect($allData)->where('G', '!=', null)
                ->pluck('G')
                ->unique()
                ->values();
            if ($allData->where('E', '4.01.0.00.0.00.01.0000')->count() > 0) {
                $arrKodeSubUnit = $arrKodeSubUnit->push('4.01.0.00.0.00.01.0000'); // UNTUK SEKRETARIAT DAERAH (BAGIAN-BAGIAN)
            }


            $arrKodeUnit = collect($allData)->where('G', '!=', null)
                ->pluck('E')
                ->unique()
                ->values();
            foreach ($arrKodeUnit as $kodeUnit) {
                if (in_array($kodeUnit, [
                    '7.01.0.00.0.00.06.0000', // PAYARAMAN
                    '7.01.0.00.0.00.05.0000', // TANJUNG BATU
                    '7.01.0.00.0.00.10.0000', // INDRALAYA
                    '7.01.0.00.0.00.12.0000', // INDRALAYA UTARA
                ])) {
                    $arrKodeSubUnit[] = $arrKodeUnit[0];
                }
            }
            $arrKodeSubUnit = $arrKodeSubUnit->unique()->values();

            $arrInstances = DB::table('instances')->whereIn('code', $arrKodeSubUnit)->get();
            $dataInserted = [
                'urusan' => 0,
                'bidang' => 0,
                'program' => 0,
                'kegiatan' => 0,
                'sub_kegiatan' => 0,
                'kode_rekening' => 0,
                'sumber_dana' => 0,
            ];
            foreach ($arrInstances as $instance) {
                if ($instance->code == '4.01.0.00.0.00.01.0000') {
                    $arrSubKegiatanCodes = collect($allData)
                        ->where('E', $instance->code)
                        ->pluck('O')
                        ->unique();
                } else if (in_array($instance->code, [
                    '7.01.0.00.0.00.06.0000', // PAYARAMAN
                    '7.01.0.00.0.00.05.0000', // TANJUNG BATU
                    '7.01.0.00.0.00.10.0000', // INDRALAYA
                    '7.01.0.00.0.00.12.0000', // INDRALAYA UTARA
                ])) {
                    $arrSubKegiatanCodes = collect($allData)
                        ->where('G', $instance->code)
                        ->where('E', '!=', null)
                        ->pluck('O')
                        ->unique();
                } else {
                    $arrSubKegiatanCodes = collect($allData)
                        ->where('G', $instance->code)
                        ->where('E', '!=', null)
                        ->pluck('O')
                        ->unique();
                }
                $arrSubKegiatanInput = $allData->keyBy('O')->only($arrSubKegiatanCodes)->values();
                // check database
                foreach ($arrSubKegiatanInput as $inputSubKeg) {
                    $urusan = DB::table('ref_urusan')
                        ->where('fullcode', str()->squish($inputSubKeg['C']))
                        ->first();
                    if (!$urusan) {
                        $urusan = new Urusan();
                        $urusan->code = str()->squish($inputSubKeg['C']);
                        $urusan->fullcode = str()->squish($inputSubKeg['C']);
                        $urusan->name = str()->squish($inputSubKeg['D']);
                        $urusan->periode_id = $periode;
                        $urusan->status = 'active';
                        $urusan->created_by = auth()->user()->id;
                        $urusan->save();

                        $dataInserted['urusan'] += 1;
                    }
                    $bidang = DB::table('ref_bidang_urusan')
                        ->where('fullcode', str()->squish($inputSubKeg['I']))
                        ->first();
                    if (!$bidang) {
                        $code = str()->squish($inputSubKeg['I']);
                        $code = explode('.', $code);
                        $code = $code[1] ?? null;

                        $bidang = new Bidang();
                        $bidang->code = str()->squish($code);
                        $bidang->fullcode = str()->squish($inputSubKeg['I']);
                        $bidang->name = str()->squish($inputSubKeg['J']);
                        $bidang->urusan_id = $urusan->id;
                        $bidang->periode_id = $periode;
                        $bidang->status = 'active';
                        $bidang->created_by = auth()->user()->id;
                        $bidang->save();

                        $dataInserted['bidang'] += 1;
                    }
                    $program = DB::table('ref_program')
                        ->where('instance_id', $instance->id)
                        ->where('fullcode', str()->squish($inputSubKeg['K']))
                        ->first();
                    if (!$program) {
                        $code = str()->squish($inputSubKeg['K']);
                        $code = explode('.', $code);
                        $code = $code[2] ?? null;

                        $program = new Program();
                        $program->instance_id = $instance->id;
                        $program->urusan_id = $urusan->id;
                        $program->bidang_id = $bidang->id;
                        $program->code = $code;
                        $program->fullcode = str()->squish($inputSubKeg['K']);
                        $program->name = str()->squish($inputSubKeg['L']);
                        $program->periode_id = $periode;
                        $program->status = 'active';
                        $program->created_by = auth()->user()->id;
                        $program->save();

                        $dataInserted['program'] += 1;
                    }

                    $kegiatan = DB::table('ref_kegiatan')
                        ->where('instance_id', $instance->id)
                        ->where('fullcode', str()->squish($inputSubKeg['M']))
                        ->first();
                    if (!$kegiatan) {
                        $code = str()->squish($inputSubKeg['M']);
                        $code = explode('.', $code);
                        $code1 = $code[3] ?? null;
                        $code2 = $code[4] ?? null;

                        $kegiatan = new Kegiatan();
                        $kegiatan->instance_id = $instance->id;
                        $kegiatan->urusan_id = $urusan->id;
                        $kegiatan->bidang_id = $bidang->id;
                        $kegiatan->program_id = $program->id;
                        $kegiatan->code_1 = $code1;
                        $kegiatan->code_2 = $code2;
                        $kegiatan->fullcode = str()->squish($inputSubKeg['M']);
                        $kegiatan->name = str()->squish($inputSubKeg['N']);
                        $kegiatan->periode_id = $periode;
                        $kegiatan->status = 'active';
                        $kegiatan->created_by = auth()->user()->id;
                        $kegiatan->save();

                        $dataInserted['kegiatan'] += 1;
                    }

                    $subKegiatan = DB::table('ref_sub_kegiatan')
                        ->where('instance_id', $instance->id)
                        ->where('fullcode', str()->squish($inputSubKeg['O']))
                        ->first();
                    if (!$subKegiatan) {
                        $code = str()->squish($inputSubKeg['O']);
                        $code = explode('.', $code);
                        $code = $code[5] ?? null;

                        $subKegiatan = new SubKegiatan();
                        $subKegiatan->instance_id = $instance->id;
                        $subKegiatan->urusan_id = $urusan->id;
                        $subKegiatan->bidang_id = $bidang->id;
                        $subKegiatan->program_id = $program->id;
                        $subKegiatan->kegiatan_id = $kegiatan->id;
                        $subKegiatan->code = $code;
                        $subKegiatan->fullcode = str()->squish($inputSubKeg['O']);
                        $subKegiatan->name = str()->squish($inputSubKeg['P']);
                        $subKegiatan->periode_id = $periode;
                        $subKegiatan->status = 'active';
                        $subKegiatan->created_by = auth()->user()->id;
                        $subKegiatan->save();

                        $dataInserted['sub_kegiatan'] += 1;
                    }
                }
            }

            $messages = [
                'message' => 'Data Berhasil disimpan',
                'note' => $request->message ?? null,
                'datas_count' => count($allData),
                'datas_inserted' => $dataInserted,
                'missing_data' => $missingSubKegiatan,
                'missing_data_count' => $countMissingSubKegiatan,
            ];

            $logs = DB::table('sipd_upload_logs')
                ->insert([
                    'file_name' => $file->getClientOriginalName(),
                    'file_path' => $path,
                    'status' => 'success',
                    'message' => json_encode($messages),
                    'type' => 'rekap5prgmkeg',
                    'user_id' => auth()->id(),
                    'created_at' => $now,
                    'updated_at' => now(),
                ]);

            DB::commit();
            return $this->successResponse($messages, 'Data Berhasil disimpan');
        } catch (\Throwable $th) {
            DB::rollback();
            $logs = DB::table('sipd_upload_logs')
                ->insert([
                    'file_name' => $file->getClientOriginalName(),
                    'file_path' => $path,
                    'status' => 'error',
                    'message' => $th->getMessage() . ' - ' . $th->getLine() . ' - ' . $th->getFile(),
                    'type' => 'rekap5prgmkeg',
                    'user_id' => auth()->id(),
                    'created_at' => $now,
                    'updated_at' => now(),
                ]);
            DB::commit();
            return $this->errorResponse($th->getMessage() . ' - ' . $th->getLine() . ' - ' . $th->getFile());
        }
    }
}
