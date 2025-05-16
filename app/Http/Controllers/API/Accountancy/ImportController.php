<?php

namespace App\Http\Controllers\API\Accountancy;

use App\Traits\JsonReturner;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Validator;

use function PHPUnit\Framework\isEmpty;

class ImportController extends Controller
{
    use JsonReturner;

    function postKodeRekening(Request $request)
    {
        $validate = Validator::make($request->all(), [
            'year' => 'required|integer',
            'periode' => 'required|exists:ref_periode,id',
            'file' => 'required|file',
        ], [], [
            'periode' => 'Periode',
            'year' => 'Tahun',
            'file' => 'Berkas Neraca'
        ]);

        if ($validate->fails()) {
            return $this->validationResponse($validate->errors());
        }

        DB::beginTransaction();
        try {
            $now = now();
            $file = $request->file('file');
            $fileName = 'KodeRekeningAkuntansi-' . $request->year . '-' . $request->periode . '.' . $file->getClientOriginalExtension();
            $file->move('uploads', $fileName);

            $path = public_path('uploads/' . $fileName);
            $reader = new \PhpOffice\PhpSpreadsheet\Reader\Xlsx();
            $spreadsheet = $reader->load($path);
            $sheet = $spreadsheet->getActiveSheet();

            $allData = [];
            $highestRow = $sheet->getHighestRow();
            $highestColumn = $sheet->getHighestColumn();
            $highestColumnIndex = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::columnIndexFromString($highestColumn);
            $allData = $sheet->rangeToArray('A1:' . $highestColumn . $highestRow, null, true, true, true);
            $allData = collect($allData);
            $allData = $allData->where('A', '!=', null)
                ->where('A', '>=', '1')
                ->where('A', '!=', 'Kode Rekening')
                ->where('A', '!=', 'Dalam Rupiah')
                ->where('B', '!=', null)
                ->where('C', '!=', null)
                ->where('D', '!=', null)
                ->values();
            $returnData = [];
            foreach ($allData as $key => $input) {
                $fullcode = $input['A'];
                $code_1 = substr($fullcode, 0, 1);
                $code_1 = $code_1 == '' ? null : $code_1;

                $code_2 = substr($fullcode, 2, 1);
                $code_2 = $code_2 == '' ? null : $code_2;

                $code_3 = substr($fullcode, 4, 2);
                $code_3 = $code_3 == '' ? null : $code_3;

                $code_4 = substr($fullcode, 7, 2);
                $code_4 = $code_4 == '' ? null : $code_4;

                $code_5 = substr($fullcode, 10, 2);
                $code_5 = $code_5 == '' ? null : $code_5;

                $code_6 = substr($fullcode, 13, 4);
                $code_6 = $code_6 == '' ? null : $code_6;

                $uraian = $input['B'];

                if ($code_1 && $code_2 && !$code_3 && !$code_4 && !$code_5 && !$code_6) {
                    $parentRekening = DB::table('ref_kode_rekening_complete')
                        ->where('code_1', $code_1)
                        ->where('code_2', null)
                        ->where('code_3', null)
                        ->where('code_4', null)
                        ->where('code_5', null)
                        ->where('code_6', null)
                        ->first();
                } else if ($code_1 && $code_2 && $code_3 && !$code_4 && !$code_5 && !$code_6) {
                    $parentRekening = DB::table('ref_kode_rekening_complete')
                        ->where('code_1', $code_1)
                        ->where('code_2', $code_2)
                        ->where('code_3', null)
                        ->where('code_4', null)
                        ->where('code_5', null)
                        ->where('code_6', null)
                        ->first();
                } else if ($code_1 && $code_2 && $code_3 && $code_4 && !$code_5 && !$code_6) {
                    $parentRekening = DB::table('ref_kode_rekening_complete')
                        ->where('code_1', $code_1)
                        ->where('code_2', $code_2)
                        ->where('code_3', $code_3)
                        ->where('code_4', null)
                        ->where('code_5', null)
                        ->where('code_6', null)
                        ->first();
                } else if ($code_1 && $code_2 && $code_3 && $code_4 && $code_5 && !$code_6) {
                    $parentRekening = DB::table('ref_kode_rekening_complete')
                        ->where('code_1', $code_1)
                        ->where('code_2', $code_2)
                        ->where('code_3', $code_3)
                        ->where('code_4', $code_4)
                        ->where('code_5', null)
                        ->where('code_6', null)
                        ->first();
                } else if ($code_1 && $code_2 && $code_3 && $code_4 && $code_5 && $code_6) {
                    $parentRekening = DB::table('ref_kode_rekening_complete')
                        ->where('code_1', $code_1)
                        ->where('code_2', $code_2)
                        ->where('code_3', $code_3)
                        ->where('code_4', $code_4)
                        ->where('code_5', $code_5)
                        ->where('code_6', null)
                        ->first();
                }

                $checkExists = DB::table('ref_kode_rekening_complete')
                    ->where('code_1', $code_1)
                    ->where('code_2', $code_2)
                    ->where('code_3', $code_3)
                    ->where('code_4', $code_4)
                    ->where('code_5', $code_5)
                    ->where('code_6', $code_6)
                    ->first();
                if (!$checkExists) {
                    $newData = DB::table('ref_kode_rekening_complete')
                        ->insertGetId([
                            'parent_id' => $parentRekening->id ?? null,
                            'periode_id' => $request->periode,
                            'year' => $request->year,
                            'code_1' => $code_1,
                            'code_2' => $code_2,
                            'code_3' => $code_3,
                            'code_4' => $code_4,
                            'code_5' => $code_5,
                            'code_6' => $code_6,
                            'fullcode' => $fullcode,
                            'name' => $uraian,
                            'status' => 'active',
                            'created_at' => $now,
                            'updated_at' => $now
                        ]);
                } else {
                    DB::table('ref_kode_rekening_complete')
                        ->where('id', $checkExists->id)
                        ->update([
                            'parent_id' => $parentRekening->id ?? null,
                            'periode_id' => $request->periode,
                            'year' => $request->year,
                            'code_1' => $code_1,
                            'code_2' => $code_2,
                            'code_3' => $code_3,
                            'code_4' => $code_4,
                            'code_5' => $code_5,
                            'code_6' => $code_6,
                            'fullcode' => $fullcode,
                            'name' => $uraian,
                            'status' => 'active',
                            'updated_at' => $now
                        ]);
                }

                $returnData[] = [
                    'fullcode' => $fullcode,
                    'parent_id' => $parentRekening->id ?? null,
                    'code_1' => $code_1,
                    'code_2' => $code_2,
                    'code_3' => $code_3,
                    'code_4' => $code_4,
                    'code_5' => $code_5,
                    'code_6' => $code_6,
                    'uraian' => $uraian,
                ];
            }

            DB::commit();
            return $this->successResponse($returnData, 'Data Kode Rekening berhasil diimport');
        } catch (\Exception $e) {
            DB::rollBack();
            return $this->errorResponse($e->getMessage());
        }
    }

    function postSaldoAwalNeraca(Request $request)
    {
        $validate = Validator::make($request->all(), [
            'instance' => 'required|exists:instances,id',
            'year' => 'required|integer',
            'periode' => 'required|exists:ref_periode,id',
            'file' => 'required|file',
        ], [], [
            'instance' => 'Perangkat Daerah',
            'periode' => 'Periode',
            'year' => 'Tahun',
            'file' => 'Berkas Neraca'
        ]);

        if ($validate->fails()) {
            return $this->validationResponse($validate->errors());
        }

        DB::beginTransaction();
        try {
            $now = now();
            $file = $request->file('file');
            $fileName = 'SaldoAwalNeraca-' . $request->year . '-' . $request->periode . '.' . $file->getClientOriginalExtension();
            $file->move('uploads', $fileName);

            $path = public_path('uploads/' . $fileName);
            $reader = new \PhpOffice\PhpSpreadsheet\Reader\Xlsx();
            $spreadsheet = $reader->load($path);
            $sheet = $spreadsheet->getActiveSheet();

            $allData = [];
            $highestRow = $sheet->getHighestRow();
            $highestColumn = $sheet->getHighestColumn();
            $highestColumnIndex = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::columnIndexFromString($highestColumn);
            $allData = $sheet->rangeToArray('A1:' . $highestColumn . $highestRow, null, true, true, true);
            $allData = collect($allData);
            $allData = $allData->where('A', '!=', null)
                ->where('A', '>=', '1')
                ->where('A', '!=', 'REKAPITULASI SALDO AWAL ASET 2024')
                ->where('A', '!=', 'Kode Rekening')
                ->where('A', '!=', 'Dalam Rupiah')
                ->where('B', '!=', null)
                ->where('C', '!=', null)
                ->where('D', '!=', null)
                ->values();
            foreach ($allData as $key => $input) {
                $saldoAwal = $input['D'];
                if (strpos($saldoAwal, '(') !== false) {
                    $saldoAwal = str_replace('(', '', $saldoAwal);
                    $saldoAwal = str_replace(')', '', $saldoAwal);
                    $saldoAwal = str_replace(',00', '', $saldoAwal);
                    $saldoAwal = str_replace('.', '', $saldoAwal);
                    $saldoAwal = str_replace(',', '.', $saldoAwal);
                    $saldoAwal = number_format((float)$saldoAwal, 2, '.', '');
                    $saldoAwal = $saldoAwal * -1;
                } else {
                    $saldoAwal = str_replace(',00', '', $saldoAwal);
                    $saldoAwal = str_replace('.', '', $saldoAwal);
                    $saldoAwal = str_replace(',', '.', $saldoAwal);
                    $saldoAwal = number_format((float)$saldoAwal, 2, '.', '');
                }

                $saldoAkhir = $input['C'];
                if (strpos($saldoAkhir, '(') !== false) {
                    $saldoAkhir = str_replace('(', '', $saldoAkhir);
                    $saldoAkhir = str_replace(')', '', $saldoAkhir);
                    $saldoAkhir = str_replace(',00', '', $saldoAkhir);
                    $saldoAkhir = str_replace('.', '', $saldoAkhir);
                    $saldoAkhir = str_replace(',', '.', $saldoAkhir);
                    $saldoAkhir = number_format((float)$saldoAkhir, 2, '.', '');
                    $saldoAkhir = $saldoAkhir * -1;
                } else {
                    $saldoAkhir = str_replace(',00', '', $saldoAkhir);
                    $saldoAkhir = str_replace('.', '', $saldoAkhir);
                    $saldoAkhir = str_replace(',', '.', $saldoAkhir);
                    $saldoAkhir = number_format((float)$saldoAkhir, 2, '.', '');
                }

                $rekening = DB::table('ref_kode_rekening_complete')
                    ->where('fullcode', $input['A'])
                    ->first();
                if ($rekening) {
                    $data = DB::table('acc_pre_report')
                        ->where('instance_id', $request->instance)
                        ->where('year', $request->year)
                        ->where('periode_id', $request->periode)
                        ->where('kode_rekening_id', $rekening->id)
                        ->where('type', 'neraca')
                        ->first();
                    if ($data) {
                        $data = DB::table('acc_pre_report')
                            ->where('id', $data->id)
                            ->update([
                                'type' => 'neraca',
                                'saldo_awal' => $saldoAwal,
                                'updated_at' => $now
                            ]);
                    } else if (!$data) {
                        $data = DB::table('acc_pre_report')
                            ->insertGetId([
                                'instance_id' => $request->instance,
                                'year' => $request->year,
                                'type' => 'neraca',
                                'periode_id' => $request->periode,
                                'kode_rekening_id' => $rekening->id,
                                'fullcode' => $input['A'],
                                'saldo_awal' => $saldoAwal,
                                'saldo_akhir' => 0,
                                'kenaikan_penurunan' => 0,
                                'percent' => 0,
                                'created_at' => $now,
                                'updated_at' => $now
                            ]);
                    }
                }
            }

            $datas = DB::table('acc_pre_report')
                ->where('instance_id', $request->instance)
                ->where('year', $request->year)
                ->where('periode_id', $request->periode)
                ->where('type', 'neraca')
                ->orderBy('fullcode', 'asc')
                ->get();
            foreach ($datas as $data) {
                $returnData[] = [
                    'fullcode' => $data->fullcode,
                    'uraian' => DB::table('ref_kode_rekening_complete')
                        ->where('id', $data->kode_rekening_id)
                        ->value('name'),
                    'saldo_awal' => $data->saldo_awal,
                    'saldo_akhir' => $data->saldo_akhir,
                    'kenaikan_penurunan' => $data->kenaikan_penurunan,
                    'percent' => $data->percent,
                ];
            }

            DB::commit();
            return $this->successResponse($returnData, 'Data Saldo Awal Neraca berhasil diimport');
        } catch (\Exception $e) {
            DB::rollBack();
            return $this->errorResponse($e->getMessage());
        }
    }

    function postSaldoAwalLO(Request $request)
    {
        $validate = Validator::make($request->all(), [
            'instance' => 'required|exists:instances,id',
            'year' => 'required|integer',
            'periode' => 'required|exists:ref_periode,id',
            'file' => 'required|file',
        ], [], [
            'instance' => 'Perangkat Daerah',
            'periode' => 'Periode',
            'year' => 'Tahun',
            'file' => 'Berkas Neraca'
        ]);

        if ($validate->fails()) {
            return $this->validationResponse($validate->errors());
        }

        DB::beginTransaction();
        try {
            $now = now();
            $file = $request->file('file');
            $fileName = 'SaldoAwalLO-' . $request->year . '-' . $request->periode . '.' . $file->getClientOriginalExtension();
            $file->move('uploads', $fileName);

            $path = public_path('uploads/' . $fileName);
            $reader = new \PhpOffice\PhpSpreadsheet\Reader\Xlsx();
            $spreadsheet = $reader->load($path);
            $sheet = $spreadsheet->getActiveSheet();

            $allData = [];
            $highestRow = $sheet->getHighestRow();
            $highestColumn = $sheet->getHighestColumn();
            $highestColumnIndex = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::columnIndexFromString($highestColumn);
            $allData = $sheet->rangeToArray('A1:' . $highestColumn . $highestRow, null, true, true, true);
            $allData = collect($allData);
            $allData = $allData->where('A', '!=', null)
                ->where('A', '>=', '1')
                ->where('A', '!=', 'REKAPITULASI SALDO AWAL ASET 2024')
                ->where('A', '!=', 'Kode Rekening')
                ->where('A', '!=', 'Dalam Rupiah')
                ->where('B', '!=', null)
                ->where('C', '!=', null)
                ->where('D', '!=', null)
                ->values();
            foreach ($allData as $key => $input) {
                $saldoAwal = $input['D'];
                if (strpos($saldoAwal, '(') !== false) {
                    $saldoAwal = str_replace('(', '', $saldoAwal);
                    $saldoAwal = str_replace(')', '', $saldoAwal);
                    $saldoAwal = str_replace(',00', '', $saldoAwal);
                    $saldoAwal = str_replace('.', '', $saldoAwal);
                    $saldoAwal = str_replace(',', '.', $saldoAwal);
                    $saldoAwal = number_format((float)$saldoAwal, 2, '.', '');
                    $saldoAwal = $saldoAwal * -1;
                } else {
                    $saldoAwal = str_replace(',00', '', $saldoAwal);
                    $saldoAwal = str_replace('.', '', $saldoAwal);
                    $saldoAwal = str_replace(',', '.', $saldoAwal);
                    $saldoAwal = number_format((float)$saldoAwal, 2, '.', '');
                }

                $saldoAkhir = $input['C'];
                if (strpos($saldoAkhir, '(') !== false) {
                    $saldoAkhir = str_replace('(', '', $saldoAkhir);
                    $saldoAkhir = str_replace(')', '', $saldoAkhir);
                    $saldoAkhir = str_replace(',00', '', $saldoAkhir);
                    $saldoAkhir = str_replace('.', '', $saldoAkhir);
                    $saldoAkhir = str_replace(',', '.', $saldoAkhir);
                    $saldoAkhir = number_format((float)$saldoAkhir, 2, '.', '');
                    $saldoAkhir = $saldoAkhir * -1;
                } else {
                    $saldoAkhir = str_replace(',00', '', $saldoAkhir);
                    $saldoAkhir = str_replace('.', '', $saldoAkhir);
                    $saldoAkhir = str_replace(',', '.', $saldoAkhir);
                    $saldoAkhir = number_format((float)$saldoAkhir, 2, '.', '');
                }

                $rekening = DB::table('ref_kode_rekening_complete')
                    ->where('fullcode', $input['A'])
                    ->first();
                if ($rekening) {
                    $data = DB::table('acc_pre_report')
                        ->where('instance_id', $request->instance)
                        ->where('year', $request->year)
                        ->where('periode_id', $request->periode)
                        ->where('kode_rekening_id', $rekening->id)
                        ->where('type', 'lo')
                        ->first();
                    if ($data) {
                        $data = DB::table('acc_pre_report')
                            ->where('id', $data->id)
                            ->update([
                                'type' => 'lo',
                                'saldo_awal' => $saldoAwal,
                                'updated_at' => $now
                            ]);
                    } else if (!$data) {
                        $data = DB::table('acc_pre_report')
                            ->insertGetId([
                                'instance_id' => $request->instance,
                                'year' => $request->year,
                                'type' => 'lo',
                                'periode_id' => $request->periode,
                                'kode_rekening_id' => $rekening->id,
                                'fullcode' => $input['A'],
                                'saldo_awal' => $saldoAwal,
                                'saldo_akhir' => 0,
                                'kenaikan_penurunan' => 0,
                                'percent' => 0,
                                'created_at' => $now,
                                'updated_at' => $now
                            ]);
                    }
                }
            }

            $datas = DB::table('acc_pre_report')
                ->where('instance_id', $request->instance)
                ->where('year', $request->year)
                ->where('periode_id', $request->periode)
                ->where('type', 'lo')
                ->orderBy('fullcode', 'asc')
                ->get();
            foreach ($datas as $data) {
                $returnData[] = [
                    'fullcode' => $data->fullcode,
                    'uraian' => DB::table('ref_kode_rekening_complete')
                        ->where('id', $data->kode_rekening_id)
                        ->value('name'),
                    'saldo_awal' => $data->saldo_awal,
                    'saldo_akhir' => $data->saldo_akhir,
                    'kenaikan_penurunan' => $data->kenaikan_penurunan,
                    'percent' => $data->percent,
                ];
            }

            DB::commit();
            return $this->successResponse($returnData, 'Data Saldo Awal Neraca berhasil diimport');
        } catch (\Exception $e) {
            DB::rollBack();
            return $this->errorResponse($e->getMessage());
        }
    }
}
