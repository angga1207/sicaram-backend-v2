<?php

namespace App\Http\Controllers\API\Accountancy;

use App\Exports\Accountancy\KertasKerjaExport;
use App\Exports\Accountancy\KertasKerjaExportPadb;
use App\Exports\Report\Accountancy\LOExport;
use App\Exports\Report\Accountancy\LPEExport;
use App\Exports\Report\Accountancy\NeracaExport;
use App\Traits\JsonReturner;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Validator;
use Maatwebsite\Excel\Facades\Excel;

class ReportController extends Controller
{
    use JsonReturner;

    function downloadExcelAll(Request $request)
    {
        $validate = Validator::make($request->all(), [
            'data' => 'required',
        ]);
        if ($validate->fails()) {
            return $this->validationResponse($validate->errors());
        }

        if (!$request->data[0]['id']) {
            return $this->errorResponse('Tidak ada data untuk diexport!');
        }

        if ($request->params['category'] == 'kibs') {
            $datas = $request->data;
            if (isset($request->params['instance'])) {
                $instance = DB::table('instances')->where('id', $request->params['instance'])->first();
                $filename = $request->params['type'] . '_' . str()->slug($instance->alias) . '_' . $request->params['year'] . '.xlsx';
            } else {
                $filename = $request->params['type'] . '_kabupaten_ogan_ilir' . '_' . $request->params['year'] . '.xlsx';
            }
            $params = $request->params;
            Excel::store(new KertasKerjaExport($datas, $params), 'export-accountancy/' . $filename, 'public');
            return $this->successResponse([
                'path' => asset('storage/export-accountancy/' . $filename),
                'filename' => $filename,
            ]);
        } else if ($request->params['category'] == 'padb') {
            $datas = $request->data;
            if (isset($request->params['instance'])) {
                $instance = DB::table('instances')->where('id', $request->params['instance'])->first();
                $filename = $request->params['type'] . '_' . str()->slug($instance->alias) . '_' . $request->params['year'] . '.xlsx';
            } else {
                $filename = $request->params['type'] . '_kabupaten_ogan_ilir' . '_' . $request->params['year'] . '.xlsx';
            }
            $params = $request->params;
            Excel::store(new KertasKerjaExportPadb($datas, $params), 'export-accountancy/' . $filename, 'public');
            return $this->successResponse([
                'path' => asset('storage/export-accountancy/' . $filename),
                'filename' => $filename,
            ]);
        } else if ($request->params['category'] == 'belanja_bayar_dimuka') {
            $datas = $request->data;
            if (!isset($request->params['year'])) {
                $request->params['year'] = 2024;
            }
            if (isset($request->params['instance'])) {
                $instance = DB::table('instances')->where('id', $request->params['instance'])->first();
                $filename = $request->params['type'] . '_' . str()->slug($instance->alias) . '_' . $request->params['year'] . '.xlsx';
            } else {
                $filename = $request->params['type'] . '_kabupaten_ogan_ilir' . '_' . $request->params['year'] . '.xlsx';
            }
            $params = $request->params;
            Excel::store(new KertasKerjaExport($datas, $params), 'export-accountancy/' . $filename, 'public');
            return $this->successResponse([
                'path' => asset('storage/export-accountancy/' . $filename),
                'filename' => $filename,
            ]);
        } else if ($request->params['category'] == 'persediaan') {
            $datas = $request->data;
            if (isset($request->params['instance'])) {
                $instance = DB::table('instances')->where('id', $request->params['instance'])->first();
                $filename = $request->params['type'] . '_' . str()->slug($instance->alias) . '_' . $request->params['year'] . '.xlsx';
            } else {
                $filename = $request->params['type'] . '_persediaan_kabupaten_ogan_ilir' . '_' . $request->params['year'] . '.xlsx';
            }
            $params = $request->params;
            Excel::store(new KertasKerjaExport($datas, $params), 'export-accountancy/' . $filename, 'public');
            return $this->successResponse([
                'path' => asset('storage/export-accountancy/' . $filename),
                'filename' => $filename,
            ]);
        } else if ($request->params['category'] == 'hutang_belanja') {
            $datas = $request->data;
            if (isset($request->params['instance'])) {
                $instance = DB::table('instances')->where('id', $request->params['instance'])->first();
                $filename = $request->params['type'] . '_' . str()->slug($instance->alias) . '_' . $request->params['year'] . '.xlsx';
            } else {
                $filename = $request->params['type'] . '_kabupaten_ogan_ilir' . '_' . $request->params['year'] . '.xlsx';
            }
            $params = $request->params;
            Excel::store(new KertasKerjaExport($datas, $params), 'export-accountancy/' . $filename, 'public');
            return $this->successResponse([
                'path' => asset('storage/export-accountancy/' . $filename),
                'filename' => $filename,
            ]);
        } else if ($request->params['category'] == 'beban_lo') {
            $datas = $request->data;
            if (isset($request->params['instance'])) {
                $instance = DB::table('instances')->where('id', $request->params['instance'])->first();
                $filename = 'beban_lo_' . $request->params['type'] . '_' . str()->slug($instance->alias) . '_' . $request->params['year'] . '.xlsx';
            } else {
                $filename = 'beban_lo_' . $request->params['type'] . '_kabupaten_ogan_ilir' . '_' . $request->params['year'] . '.xlsx';
            }
            $params = $request->params;
            Excel::store(new KertasKerjaExport($datas, $params), 'export-accountancy/' . $filename, 'public');
            return $this->successResponse([
                'path' => asset('storage/export-accountancy/' . $filename),
                'filename' => $filename,
            ]);
        } else if ($request->params['category'] == 'pendapatan_lo') {
            $datas = $request->data;
            if (isset($request->params['instance'])) {
                $instance = DB::table('instances')->where('id', $request->params['instance'])->first();
                $filename = $request->params['type'] . '_' . str()->slug($instance->alias) . '_' . $request->params['year'] . '.xlsx';
            } else {
                $filename = $request->params['type'] . '_kabupaten_ogan_ilir' . '_' . $request->params['year'] . '.xlsx';
            }
            $params = $request->params;
            Excel::store(new KertasKerjaExport($datas, $params), 'export-accountancy/' . $filename, 'public');
            return $this->successResponse([
                'path' => asset('storage/export-accountancy/' . $filename),
                'filename' => $filename,
            ]);
        } else if ($request->params['category'] == 'pengembalian-belanja'){
            $datas = $request->data;
            if (isset($request->params['instance'])) {
                $instance = DB::table('instances')->where('id', $request->params['instance'])->first();
                $filename = $request->params['type'] . '_' . str()->slug($instance->alias) . '_' . $request->params['year'] . '.xlsx';
            } else {
                $filename = $request->params['type'] . '_kabupaten_ogan_ilir' . '_' . $request->params['year'] . '.xlsx';
            }
            $params = $request->params;
            Excel::store(new KertasKerjaExport($datas, $params), 'export-accountancy/' . $filename, 'public');
            return $this->successResponse([
                'path' => asset('storage/export-accountancy/' . $filename),
                'filename' => $filename,
            ]);
        }
    }

    function reportLRA(Request $request)
    {
        $validate = Validator::make($request->all(), [
            'instance' => 'nullable',
            'year' => 'required|integer',
            'periode' => 'required|exists:ref_periode,id',
            'level' => 'required|integer',
        ], [], [
            'instance' => 'Perangkat Daerah',
            'periode' => 'Periode',
            'year' => 'Tahun',
            'level' => 'Level',
        ]);

        if ($validate->fails()) {
            return $this->validationResponse($validate->errors());
        }

        try {
            $datas = [];
            if ($request->instance == 0) {
                $arrLraKodeRekening = DB::table('acc_lra')
                    ->where('year', $request->year)
                    ->orderBy('kode_rekening', 'asc')
                    ->whereNull('deleted_at')
                    ->pluck('kode_rekening');
                $arrLraKodeRekening = collect($arrLraKodeRekening)
                    ->unique()
                    ->values()
                    ->all();
                foreach ($arrLraKodeRekening as $kodeRek) {
                    $kodeRekening = DB::table('ref_kode_rekening_complete')
                        ->where('fullcode', $kodeRek)
                        ->first();
                    $arrLra = DB::table('acc_lra')
                        ->where('year', $request->year)
                        ->where('periode_id', $request->periode)
                        ->where('kode_rekening', $kodeRek)
                        ->whereNull('deleted_at')
                        ->get();
                    // $persentase = $arrLra->sum('realisasi_percentage') / $arrLra->count();
                    $datas[] = [
                        'id' => null,
                        'fullcode' => $kodeRekening->fullcode,
                        'code_1' => $kodeRekening->code_1,
                        'code_2' => $kodeRekening->code_2,
                        'code_3' => $kodeRekening->code_3,
                        'code_4' => $kodeRekening->code_4,
                        'code_5' => $kodeRekening->code_5,
                        'code_6' => $kodeRekening->code_6,
                        'name' => $kodeRekening->name,
                        'anggaran' => $arrLra->sum('anggaran'),
                        'realisasi' => $arrLra->sum('realisasi'),
                        'persentase' => $arrLra->sum('anggaran') == 0 ? 0 : ($arrLra->sum('realisasi') / $arrLra->sum('anggaran')) * 100,
                        'realisasi_last_year' => $arrLra->sum('realisasi_last_year'),
                    ];
                }
                return $this->successResponse($datas);
            } else if ($request->instance) {
                $arrLRA = DB::table('acc_lra')
                    ->where('year', $request->year)
                    ->where('periode_id', $request->periode)
                    ->where('instance_id', $request->instance)
                    ->whereNull('deleted_at')
                    ->orderBy('kode_rekening', 'asc')
                    ->get();

                foreach ($arrLRA as $lra) {
                    $kodeRekening = DB::table('ref_kode_rekening_complete')
                        ->where('fullcode', $lra->kode_rekening)
                        ->first();

                    $persentase = $lra->realisasi_percentage;
                    $datas[] = [
                        'id' => $lra->id,
                        'fullcode' => $lra->kode_rekening,
                        'code_1' => $kodeRekening->code_1,
                        'code_2' => $kodeRekening->code_2,
                        'code_3' => $kodeRekening->code_3,
                        'code_4' => $kodeRekening->code_4,
                        'code_5' => $kodeRekening->code_5,
                        'code_6' => $kodeRekening->code_6,
                        'name' => $kodeRekening->name,
                        'anggaran' => $lra->anggaran,
                        'realisasi' => $lra->realisasi,
                        'persentase' => $persentase,
                        'realisasi_last_year' => $lra->realisasi_last_year,
                    ];
                }
                return $this->successResponse($datas);
            }
        } catch (\Exception $e) {
            DB::rollBack();
            return $this->errorResponse($e->getMessage() . ' - ' . $e->getLine());
        }
    }

    function reportNeraca(Request $request)
    {
        $validate = Validator::make($request->all(), [
            'instance' => 'nullable',
            'year' => 'required|integer',
            'periode' => 'required|exists:ref_periode,id',
            'level' => 'required|integer',
        ], [], [
            'instance' => 'Perangkat Daerah',
            'periode' => 'Periode',
            'year' => 'Tahun',
            'level' => 'Level',
        ]);

        if ($validate->fails()) {
            return $this->validationResponse($validate->errors());
        }

        $level = $request->level ?? 6;
        $now = now();
        DB::beginTransaction();
        try {
            $datas = [];

            if ($request->instance == 0) {
                $datas = $this->_GetReportNeracaKabupaten($request->periode, $request->year, $level);
                return $this->successResponse($datas);
            } elseif ($request->instance) {
                $arrDatas = DB::table('acc_report')
                    ->where('instance_id', $request->instance)
                    ->where('year', $request->year)
                    ->where('periode_id', $request->periode)
                    ->where('type', 'neraca')
                    ->whereNull('deleted_at')
                    ->orderBy('fullcode', 'asc')
                    ->get();

                foreach ($arrDatas as $data) {
                    $kodeRekening = DB::table('ref_kode_rekening_complete')
                        ->where('id', $data->kode_rekening_id)
                        ->first();

                    $datas[] = [
                        'id' => $data->id,
                        'kode_rekening_id' => $data->kode_rekening_id,
                        'fullcode' => $kodeRekening->fullcode,
                        'code_1' => $kodeRekening->code_1,
                        'code_2' => $kodeRekening->code_2,
                        'code_3' => $kodeRekening->code_3,
                        'code_4' => $kodeRekening->code_4,
                        'code_5' => $kodeRekening->code_5,
                        'code_6' => $kodeRekening->code_6,
                        'name' => $kodeRekening->name,
                        'saldo_awal' => $data->saldo_awal,
                        'saldo_akhir' => $data->saldo_akhir,
                    ];
                }
                // DB::commit();
                $datas[] = [
                    'id' => null,
                    'kode_rekening_id' => null,
                    'fullcode' => null,
                    'code_1' => null,
                    'code_2' => null,
                    'code_3' => null,
                    'code_4' => null,
                    'code_5' => null,
                    'code_6' => null,
                    'name' => 'JUMLAH KEWAJIBAN DAN EKUITAS',
                    'saldo_awal' => collect($datas)->where('code_1', '2')->sum('saldo_awal') + collect($datas)->where('code_1', '3')->sum('saldo_awal'),
                    'saldo_akhir' => collect($datas)->where('code_1', '2')->sum('saldo_akhir') + collect($datas)->where('code_1', '3')->sum('saldo_akhir'),
                ];
                return $this->successResponse($datas);
            }
        } catch (\Exception $e) {
            DB::rollBack();
            return $this->errorResponse($e->getMessage() . ' - ' . $e->getLine());
        }
    }

    function _GetReportNeracaKabupaten($periode, $year, $level)
    {
        $now = now();
        $datas = [];

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
            ->where('year', $year)
            ->where('periode_id', $periode)
            ->where('kode_rekening_id', $rekeningAset->id)
            ->get();
        $saldoAwal = $preReport->sum('saldo_awal');

        $insertToReport = DB::table('acc_report')
            ->where('year', $year)
            ->where('periode_id', $periode)
            ->where('kode_rekening_id', $rekeningAset->id)
            ->where('type', 'neraca')
            ->get();
        $saldoAkhir = $insertToReport->sum('saldo_akhir');

        $datas[] = [
            'id' => null,
            'kode_rekening_id' => $rekeningAset->id,
            'fullcode' => $rekeningAset->fullcode,
            'code_1' => $rekeningAset->code_1,
            'code_2' => $rekeningAset->code_2,
            'code_3' => $rekeningAset->code_3,
            'code_4' => $rekeningAset->code_4,
            'code_5' => $rekeningAset->code_5,
            'code_6' => $rekeningAset->code_6,
            'name' => $rekeningAset->name,
            'saldo_awal' => $saldoAwal ?? 0,
            'saldo_akhir' => $saldoAkhir ?? 0,
        ];

        $arrRekeningAset = DB::table('ref_kode_rekening_complete')
            ->where('code_1', '1')
            ->whereIn('code_2', ['1', '2', '3', '5'])
            ->when($level, function ($query) use ($level) {
                if ($level == 6) {
                    return $query;
                } else if ($level == 5) {
                    return $query->where('code_6', null);
                } else if ($level == 4) {
                    return $query->where('code_5', null);
                } else if ($level == 3) {
                    return $query->where('code_4', null);
                } else if ($level == 2) {
                    return $query->where('code_3', null);
                } else if ($level == 1) {
                    return $query->where('code_2', null);
                }
            })
            ->orderBy('fullcode', 'asc')
            ->get();

        foreach ($arrRekeningAset as $rekeningAset) {
            $preReport = DB::table('acc_pre_report')
                ->where('year', $year)
                ->where('periode_id', $periode)
                ->where('kode_rekening_id', $rekeningAset->id)
                ->get();
            $saldoAwal = $preReport->sum('saldo_awal');

            $insertToReport = DB::table('acc_report')
                ->where('year', $year)
                ->where('periode_id', $periode)
                ->where('kode_rekening_id', $rekeningAset->id)
                ->where('type', 'neraca')
                ->get();
            $saldoAkhir = $insertToReport->sum('saldo_akhir');

            $datas[] = [
                'id' => null,
                'kode_rekening_id' => $rekeningAset->id,
                'fullcode' => $rekeningAset->fullcode,
                'code_1' => $rekeningAset->code_1,
                'code_2' => $rekeningAset->code_2,
                'code_3' => $rekeningAset->code_3,
                'code_4' => $rekeningAset->code_4,
                'code_5' => $rekeningAset->code_5,
                'code_6' => $rekeningAset->code_6,
                'name' => $rekeningAset->name,
                'saldo_awal' => $saldoAwal ?? 0,
                'saldo_akhir' => $saldoAkhir ?? 0,
            ];
        }
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
            ->where('year', $year)
            ->where('periode_id', $periode)
            ->where('kode_rekening_id', $rekeningKewajiban->id)
            ->get();
        $saldoAwal = $preReport->sum('saldo_awal');

        $insertToReport = DB::table('acc_report')
            ->where('year', $year)
            ->where('periode_id', $periode)
            ->where('kode_rekening_id', $rekeningKewajiban->id)
            ->where('type', 'neraca')
            ->get();
        $saldoAkhir = $insertToReport->sum('saldo_akhir');
        $datas[] = [
            'id' => null,
            'kode_rekening_id' => $rekeningKewajiban->id,
            'fullcode' => $rekeningKewajiban->fullcode,
            'code_1' => $rekeningKewajiban->code_1,
            'code_2' => $rekeningKewajiban->code_2,
            'code_3' => $rekeningKewajiban->code_3,
            'code_4' => $rekeningKewajiban->code_4,
            'code_5' => $rekeningKewajiban->code_5,
            'code_6' => $rekeningKewajiban->code_6,
            'name' => $rekeningKewajiban->name,
            'saldo_awal' => $saldoAwal ?? 0,
            'saldo_akhir' => $saldoAkhir ?? 0,
        ];

        $arrRekeningKewajiban = DB::table('ref_kode_rekening_complete')
            ->where('code_1', '2')
            ->whereIn('code_2', ['1', '2'])
            ->when($level, function ($query) use ($level) {
                if ($level == 6) {
                    return $query;
                } else if ($level == 5) {
                    return $query->where('code_6', null);
                } else if ($level == 4) {
                    return $query->where('code_5', null);
                } else if ($level == 3) {
                    return $query->where('code_4', null);
                } else if ($level == 2) {
                    return $query->where('code_3', null);
                } else if ($level == 1) {
                    return $query->where('code_2', null);
                }
            })
            ->orderBy('fullcode', 'asc')
            ->get();

        foreach ($arrRekeningKewajiban as $rekeningKewajiban) {
            $preReport = DB::table('acc_pre_report')
                ->where('year', $year)
                ->where('periode_id', $periode)
                ->where('kode_rekening_id', $rekeningKewajiban->id)
                ->get();
            $saldoAwal = $preReport->sum('saldo_awal');
            $insertToReport = DB::table('acc_report')
                ->where('year', $year)
                ->where('periode_id', $periode)
                ->where('kode_rekening_id', $rekeningKewajiban->id)
                ->where('type', 'neraca')
                ->get();
            $saldoAkhir = $insertToReport->sum('saldo_akhir');

            $datas[] = [
                'id' => null,
                'kode_rekening_id' => $rekeningKewajiban->id,
                'fullcode' => $rekeningKewajiban->fullcode,
                'code_1' => $rekeningKewajiban->code_1,
                'code_2' => $rekeningKewajiban->code_2,
                'code_3' => $rekeningKewajiban->code_3,
                'code_4' => $rekeningKewajiban->code_4,
                'code_5' => $rekeningKewajiban->code_5,
                'code_6' => $rekeningKewajiban->code_6,
                'name' => $rekeningKewajiban->name,
                'saldo_awal' => $saldoAwal ?? 0,
                'saldo_akhir' => $saldoAkhir ?? 0,
            ];
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
            ->where('year', $year)
            ->where('periode_id', $periode)
            ->where('kode_rekening_id', $rekeningEkuitas->id)
            ->get();
        $saldoAwal = $preReport->sum('saldo_awal');
        $insertToReport = DB::table('acc_report')
            ->where('year', $year)
            ->where('periode_id', $periode)
            ->where('kode_rekening_id', $rekeningEkuitas->id)
            ->where('type', 'neraca')
            ->get();
        $saldoAkhir = $insertToReport->sum('saldo_akhir');

        $datas[] = [
            'id' => null,
            'kode_rekening_id' => $rekeningEkuitas->id,
            'fullcode' => $rekeningEkuitas->fullcode,
            'code_1' => $rekeningEkuitas->code_1,
            'code_2' => $rekeningEkuitas->code_2,
            'code_3' => $rekeningEkuitas->code_3,
            'code_4' => $rekeningEkuitas->code_4,
            'code_5' => $rekeningEkuitas->code_5,
            'code_6' => $rekeningEkuitas->code_6,
            'name' => $rekeningEkuitas->name,
            'saldo_awal' => $saldoAwal ?? 0,
            'saldo_akhir' => $saldoAkhir ?? 0,
        ];


        $arrRekeningEkuitas = DB::table('ref_kode_rekening_complete')
            ->where('code_1', '3')
            ->whereIn('code_2', ['1'])
            ->when($level, function ($query) use ($level) {
                if ($level == 6) {
                    return $query;
                } else if ($level == 5) {
                    return $query->where('code_6', null);
                } else if ($level == 4) {
                    return $query->where('code_5', null);
                } else if ($level == 3) {
                    return $query->where('code_4', null);
                } else if ($level == 2) {
                    return $query->where('code_3', null);
                } else if ($level == 1) {
                    return $query->where('code_2', null);
                }
            })
            ->orderBy('fullcode', 'asc')
            ->get();
        foreach ($arrRekeningEkuitas as $rekeningEkuitas) {
            $preReport = DB::table('acc_pre_report')
                ->where('year', $year)
                ->where('periode_id', $periode)
                ->where('kode_rekening_id', $rekeningEkuitas->id)
                ->get();
            $saldoAwal = $preReport->sum('saldo_awal');
            $insertToReport = DB::table('acc_report')
                ->where('year', $year)
                ->where('periode_id', $periode)
                ->where('kode_rekening_id', $rekeningEkuitas->id)
                ->where('type', 'neraca')
                ->get();
            $saldoAkhir = $insertToReport->sum('saldo_akhir');

            $datas[] = [
                'id' => null,
                'kode_rekening_id' => $rekeningEkuitas->id,
                'fullcode' => $rekeningEkuitas->fullcode,
                'code_1' => $rekeningEkuitas->code_1,
                'code_2' => $rekeningEkuitas->code_2,
                'code_3' => $rekeningEkuitas->code_3,
                'code_4' => $rekeningEkuitas->code_4,
                'code_5' => $rekeningEkuitas->code_5,
                'code_6' => $rekeningEkuitas->code_6,
                'name' => $rekeningEkuitas->name,
                'saldo_awal' => $saldoAwal ?? 0,
                'saldo_akhir' => $saldoAkhir ?? 0,
            ];
        }
        // KODE REKENING 3 - EKUITAS - END

        $datas[] = [
            'id' => null,
            'kode_rekening_id' => null,
            'fullcode' => null,
            'code_1' => null,
            'code_2' => null,
            'code_3' => null,
            'code_4' => null,
            'code_5' => null,
            'code_6' => null,
            'name' => 'JUMLAH KEWAJIBAN DAN EKUITAS',
            'saldo_awal' => collect($datas)->where('code_1', '2')->sum('saldo_awal') + collect($datas)->where('code_1', '3')->sum('saldo_awal'),
            'saldo_akhir' => collect($datas)->where('code_1', '2')->sum('saldo_akhir') + collect($datas)->where('code_1', '3')->sum('saldo_akhir'),
        ];
        return $datas;
    }

    function saveSingleNeraca($id, Request $request)
    {
        $validate = Validator::make($request->all(), [
            'saldo_awal' => 'required|numeric',
            'saldo_akhir' => 'required|numeric',
        ], [], [
            'saldo_awal' => 'Saldo Awal',
            'saldo_akhir' => 'Saldo Akhir',
        ]);

        if ($validate->fails()) {
            return $this->validationResponse($validate->errors());
        }

        $now = now();

        DB::beginTransaction();
        try {
            $update = DB::table('acc_report')
                ->where('id', $id)
                ->update([
                    'saldo_awal' => $request->saldo_awal,
                    'saldo_akhir' => $request->saldo_akhir,
                    'kenaikan_penurunan' => $request->saldo_akhir - $request->saldo_awal,
                    'percent' => $request->saldo_awal == 0 ? 0 : (($request->saldo_akhir - $request->saldo_awal) / $request->saldo_awal) * 100,
                    'keterangan' => 'Manual Update',
                    'updated_at' => $now,
                ]);

            DB::commit();
            return $this->successResponse($update);
        } catch (\Exception $e) {
            DB::rollBack();
            return $this->errorResponse($e->getMessage() . ' - ' . $e->getLine());
        }
    }

    function downloadExcelNeraca(Request $request)
    {
        $validate = Validator::make($request->all(), [
            'instance' => 'nullable',
            'year' => 'required|integer',
            'periode' => 'required|exists:ref_periode,id',
            'level' => 'required|integer',
        ], [], [
            'instance' => 'Perangkat Daerah',
            'periode' => 'Periode',
            'year' => 'Tahun',
            'level' => 'Level',
        ]);

        if ($validate->fails()) {
            return $this->validationResponse($validate->errors());
        }

        $level = $request->level;

        try {
            $datas = $request->data;
            if ($request->instance) {
                $instance = DB::table('instances')->where('id', $request->instance)->first();
                $filename = $level . '-laporan-neraca-' . str()->slug($instance->alias) . '-' . $request->year . '.xlsx';
            } else {
                $filename = $level . '-laporan-neraca-kabupaten-ogan-ilir-' . $request->year . '.xlsx';
            }
            Excel::store(new NeracaExport($datas, $request->year), $filename, 'public');

            return $this->successResponse(asset('storage/' . $filename));
        } catch (\Exception $e) {
            return $this->errorResponse($e->getMessage() . ' - ' . $e->getLine());
        }
    }

    function reportLO(Request $request)
    {
        $validate = Validator::make($request->all(), [
            'instance' => 'nullable',
            'year' => 'required|integer',
            'periode' => 'required|exists:ref_periode,id',
            'level' => 'required|integer',
        ], [], [
            'instance' => 'Perangkat Daerah',
            'periode' => 'Periode',
            'year' => 'Tahun',
            'level' => 'Level',
        ]);

        if ($validate->fails()) {
            return $this->validationResponse($validate->errors());
        }

        $level = $request->level ?? 6;
        $now = now();

        DB::beginTransaction();
        try {
            $datas = [];
            if ($request->instance == 0) {
                $datas = $this->_GetReportLOKabupaten($request->periode, $request->year, $level);
                return $this->successResponse($datas);
            } elseif ($request->instance && $request->instance != 0) {
                $arrDatas = DB::table('acc_report')
                    ->where('instance_id', $request->instance)
                    ->where('year', $request->year)
                    ->where('periode_id', $request->periode)
                    ->where('type', 'lo')
                    ->whereNull('deleted_at')
                    ->orderBy('fullcode', 'asc')
                    ->get();

                foreach ($arrDatas as $data) {
                    $kodeRekening = DB::table('ref_kode_rekening_complete')
                        ->where('id', $data->kode_rekening_id)
                        ->first();

                    $datas[] = [
                        'id' => $data->id,
                        'kode_rekening_id' => $data->kode_rekening_id,
                        'fullcode' => $kodeRekening->fullcode,
                        'code_1' => $kodeRekening->code_1,
                        'code_2' => $kodeRekening->code_2,
                        'code_3' => $kodeRekening->code_3,
                        'code_4' => $kodeRekening->code_4,
                        'code_5' => $kodeRekening->code_5,
                        'code_6' => $kodeRekening->code_6,
                        'name' => $kodeRekening->name,
                        'saldo_awal' => $data->saldo_awal,
                        'saldo_akhir' => $data->saldo_akhir,
                    ];
                }
                DB::commit();

                $datas[] = [
                    'id' => null,
                    'kode_rekening_id' => null,
                    'fullcode' => null,
                    'code_1' => null,
                    'code_2' => null,
                    'code_3' => null,
                    'code_4' => null,
                    'code_5' => null,
                    'code_6' => null,
                    'name' => 'SURPLUS/DEFISIT-LO',
                    'saldo_awal' => collect($datas)->where('code_1', '7')->sum('saldo_awal') - collect($datas)->where('code_1', '8')->sum('saldo_awal'),
                    'saldo_akhir' => collect($datas)->where('code_1', '7')->sum('saldo_akhir') - collect($datas)->where('code_1', '8')->sum('saldo_akhir'),
                ];
                return $this->successResponse($datas);
            }
        } catch (\Exception $e) {
            DB::rollBack();
            return $this->errorResponse($e->getMessage() . ' - ' . $e->getLine());
        }
    }

    function _GetReportLOKabupaten($periode, $year, $level)
    {
        $now = now();
        $datas = [];
        $rekeningPendapatan = DB::table('ref_kode_rekening_complete')
            ->where('code_1', '7')
            ->where('code_2', null)
            ->where('code_3', null)
            ->where('code_4', null)
            ->where('code_5', null)
            ->where('code_6', null)
            ->first();
        $preReport = DB::table('acc_pre_report')
            ->where('year', $year)
            ->where('periode_id', $periode)
            ->where('kode_rekening_id', $rekeningPendapatan->id)
            ->get();
        $saldoAwal = $preReport->sum('saldo_awal');

        $insertToReport = DB::table('acc_report')
            ->where('year', $year)
            ->where('periode_id', $periode)
            ->where('kode_rekening_id', $rekeningPendapatan->id)
            ->where('type', 'lo')
            ->whereNull('deleted_at')
            ->get();
        $saldoAkhir = $insertToReport->sum('saldo_akhir');

        $datas[] = [
            'id' => null,
            'kode_rekening_id' => $rekeningPendapatan->id,
            'fullcode' => $rekeningPendapatan->fullcode,
            'code_1' => $rekeningPendapatan->code_1,
            'code_2' => $rekeningPendapatan->code_2,
            'code_3' => $rekeningPendapatan->code_3,
            'code_4' => $rekeningPendapatan->code_4,
            'code_5' => $rekeningPendapatan->code_5,
            'code_6' => $rekeningPendapatan->code_6,
            'name' => $rekeningPendapatan->name,
            'saldo_awal' => $saldoAwal ?? 0,
            'saldo_akhir' => $saldoAkhir ?? 0,
        ];


        $arrRekeningPendapatan = DB::table('ref_kode_rekening_complete')
            ->where('code_1', '7')
            // 7.1 utk semua OPD 7.2 & 7.3 hanya utk OPD tertentu (lupo [bpkad bae])
            ->whereIn('code_2', ['1', '4'])
            ->when($level, function ($query) use ($level) {
                if ($level == 6) {
                    return $query;
                } else if ($level == 5) {
                    return $query->where('code_6', null);
                } else if ($level == 4) {
                    return $query->where('code_5', null);
                } else if ($level == 3) {
                    return $query->where('code_4', null);
                } else if ($level == 2) {
                    return $query->where('code_3', null);
                } else if ($level == 1) {
                    return $query->where('code_2', null);
                }
            })
            ->orderBy('fullcode', 'asc')
            ->get();

        foreach ($arrRekeningPendapatan as $rekeningPendapatan) {
            $preReport = DB::table('acc_pre_report')
                ->where('year', $year)
                ->where('periode_id', $periode)
                ->where('kode_rekening_id', $rekeningPendapatan->id)
                ->whereNull('deleted_at')
                ->get();
            $saldoAwal = $preReport->sum('saldo_awal');

            $insertToReport = DB::table('acc_report')
                ->where('year', $year)
                ->where('periode_id', $periode)
                ->where('kode_rekening_id', $rekeningPendapatan->id)
                ->whereNull('deleted_at')
                ->where('type', 'lo')
                ->get();
            $saldoAkhir = $insertToReport->sum('saldo_akhir');

            $datas[] = [
                'id' => null,
                'kode_rekening_id' => $rekeningPendapatan->id,
                'fullcode' => $rekeningPendapatan->fullcode,
                'code_1' => $rekeningPendapatan->code_1,
                'code_2' => $rekeningPendapatan->code_2,
                'code_3' => $rekeningPendapatan->code_3,
                'code_4' => $rekeningPendapatan->code_4,
                'code_5' => $rekeningPendapatan->code_5,
                'code_6' => $rekeningPendapatan->code_6,
                'name' => $rekeningPendapatan->name,
                'saldo_awal' => $saldoAwal ?? 0,
                'saldo_akhir' => $saldoAkhir ?? 0,
            ];
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
            ->where('year', $year)
            ->where('periode_id', $periode)
            ->where('kode_rekening_id', $rekeningBeban->id)
            ->where('type', 'lo')
            ->whereNull('deleted_at')
            ->get();
        $saldoAwal = $preReport->sum('saldo_awal');

        $insertToReport = DB::table('acc_report')
            ->where('year', $year)
            ->where('periode_id', $periode)
            ->where('kode_rekening_id', $rekeningBeban->id)
            ->where('type', 'lo')
            ->whereNull('deleted_at')
            ->get();
        $saldoAkhir = $insertToReport->sum('saldo_akhir');

        $datas[] = [
            'id' => null,
            'kode_rekening_id' => $rekeningBeban->id,
            'fullcode' => $rekeningBeban->fullcode,
            'code_1' => $rekeningBeban->code_1,
            'code_2' => $rekeningBeban->code_2,
            'code_3' => $rekeningBeban->code_3,
            'code_4' => $rekeningBeban->code_4,
            'code_5' => $rekeningBeban->code_5,
            'code_6' => $rekeningBeban->code_6,
            'name' => $rekeningBeban->name,
            'saldo_awal' => $saldoAwal ?? 0,
            'saldo_akhir' => $saldoAkhir ?? 0,
        ];

        $arrRekeningBeban = DB::table('ref_kode_rekening_complete')
            ->where('code_1', '8')
            ->whereIn('code_2', ['1', '2', '3', '5'])
            ->when($level, function ($query) use ($level) {
                if ($level == 6) {
                    return $query;
                } else if ($level == 5) {
                    return $query->where('code_6', null);
                } else if ($level == 4) {
                    return $query->where('code_5', null);
                } else if ($level == 3) {
                    return $query->where('code_4', null);
                } else if ($level == 2) {
                    return $query->where('code_3', null);
                } else if ($level == 1) {
                    return $query->where('code_2', null);
                }
            })
            ->orderBy('fullcode', 'asc')
            ->get();
        foreach ($arrRekeningBeban as $rekeningBeban) {
            $preReport = DB::table('acc_pre_report')
                ->where('year', $year)
                ->where('periode_id', $periode)
                ->where('kode_rekening_id', $rekeningBeban->id)
                ->whereNull('deleted_at')
                ->get();
            $saldoAwal = $preReport->sum('saldo_awal');

            $insertToReport = DB::table('acc_report')
                ->where('year', $year)
                ->where('periode_id', $periode)
                ->where('kode_rekening_id', $rekeningBeban->id)
                ->where('type', 'lo')
                ->whereNull('deleted_at')
                ->get();
            $saldoAkhir = $insertToReport->sum('saldo_akhir');

            $datas[] = [
                'id' => null,
                'kode_rekening_id' => $rekeningBeban->id,
                'fullcode' => $rekeningBeban->fullcode,
                'code_1' => $rekeningBeban->code_1,
                'code_2' => $rekeningBeban->code_2,
                'code_3' => $rekeningBeban->code_3,
                'code_4' => $rekeningBeban->code_4,
                'code_5' => $rekeningBeban->code_5,
                'code_6' => $rekeningBeban->code_6,
                'name' => $rekeningBeban->name,
                'saldo_awal' => $saldoAwal ?? 0,
                'saldo_akhir' => $saldoAkhir ?? 0,
            ];
        }
        // KODE REKENING 8 BEBAN - END

        $datas[] = [
            'id' => null,
            'kode_rekening_id' => null,
            'fullcode' => null,
            'code_1' => null,
            'code_2' => null,
            'code_3' => null,
            'code_4' => null,
            'code_5' => null,
            'code_6' => null,
            'name' => 'SURPLUS/DEFISIT-LO',
            'saldo_awal' => collect($datas)->where('code_1', '7')->sum('saldo_awal') - collect($datas)->where('code_1', '8')->sum('saldo_awal'),
            'saldo_akhir' => collect($datas)->where('code_1', '7')->sum('saldo_akhir') - collect($datas)->where('code_1', '8')->sum('saldo_akhir'),
        ];
        return $datas;
    }

    function saveSingleLO($id, Request $request)
    {
        $validate = Validator::make($request->all(), [
            'saldo_akhir' => 'required|numeric',
        ], [], [
            'saldo_akhir' => 'Saldo Akhir',
        ]);

        if ($validate->fails()) {
            return $this->validationResponse($validate->errors());
        }

        $now = now();

        DB::beginTransaction();
        try {
            $update = DB::table('acc_report')
                ->where('id', $id)
                ->update([
                    'saldo_akhir' => $request->saldo_akhir,
                    'kenaikan_penurunan' => $request->saldo_akhir - $request->saldo_awal,
                    'percent' => $request->saldo_awal == 0 ? 0 : (($request->saldo_akhir - $request->saldo_awal) / $request->saldo_awal) * 100,
                    'keterangan' => 'Manual Update',
                    'updated_at' => $now,
                ]);

            DB::commit();
            return $this->successResponse($update);
        } catch (\Exception $e) {
            DB::rollBack();
            return $this->errorResponse($e->getMessage() . ' - ' . $e->getLine());
        }
    }

    function downloadExcelLO(Request $request)
    {
        $validate = Validator::make($request->all(), [
            'instance' => 'nullable',
            'year' => 'required|integer',
            'periode' => 'required|exists:ref_periode,id',
            'level' => 'required|integer',
        ], [], [
            'instance' => 'Perangkat Daerah',
            'periode' => 'Periode',
            'year' => 'Tahun',
            'level' => 'Level',
        ]);

        if ($validate->fails()) {
            return $this->validationResponse($validate->errors());
        }

        $level = $request->level;

        try {
            $datas = $request->data;
            if ($request->instance) {
                $instance = DB::table('instances')->where('id', $request->instance)->first();
                $filename = $level . '-laporan-operasional-' . str()->slug($instance->alias) . '-' . $request->year . '.xlsx';
            } else {
                $filename = $level . '-laporan-operasional-kabupaten-ogan-ilir-' . $request->year . '.xlsx';
            }
            Excel::store(new LOExport($datas, $request->year), $filename, 'public');

            return $this->successResponse(asset('storage/' . $filename));
        } catch (\Exception $e) {
            return $this->errorResponse($e->getMessage() . ' - ' . $e->getLine());
        }
    }

    function reportLPE(Request $request)
    {
        $validate = Validator::make($request->all(), [
            'instance' => 'nullable',
            'year' => 'required|integer',
            'periode' => 'required|exists:ref_periode,id',
        ], [], [
            'instance' => 'Perangkat Daerah',
            'periode' => 'Periode',
            'year' => 'Tahun',
        ]);

        if ($validate->fails()) {
            return $this->validationResponse($validate->errors());
        }

        $now = now();
        DB::beginTransaction();
        try {
            $datas = [];

            if ($request->instance == 0) {
                $datas = $this->_GetReportLPEKabupaten($request->periode, $request->year);
                return $this->successResponse($datas);
            } elseif ($request->instance && $request->instance != 0) {
                $arrUraian = [
                    [
                        'id' => 1,
                        'level' => 1,
                        'parent' => null,
                        'name' => 'Ekuitas Awal',
                    ],
                    [
                        'id' => 2,
                        'level' => 1,
                        'parent' => null,
                        'name' => 'Surplus / Defisit LO',
                    ],
                    [
                        'id' => 3,
                        'level' => 1,
                        'parent' => null,
                        'name' => 'RK PKKD',
                    ],
                    [
                        'id' => 4,
                        'level' => 1,
                        'parent' => null,
                        'name' => 'Dampak Kumulatif Perubahan Kebijakan / Kesalahan Mendasar',
                    ],
                    [
                        'id' => 5,
                        'level' => 2,
                        'parent' => 4,
                        'name' => 'Koreksi Nilai Persediaan',
                    ],
                    [
                        'id' => 6,
                        'level' => 2,
                        'parent' => 4,
                        'name' => 'Koreksi Selisih Revaluasi Aset Tetap',
                    ],
                    [
                        'id' => 7,
                        'level' => 2,
                        'parent' => 4,
                        'name' => 'Lain-lain',
                    ],
                    [
                        'id' => 8,
                        'level' => 1,
                        'parent' => null,
                        'name' => 'Ekuitas Akhir',
                    ],
                ];

                foreach ($arrUraian as $uraian) {
                    $saldoAkhir = 0;
                    $notes = null;
                    if ($uraian['id'] == 3) {
                        $LRAPendapatan = DB::table('acc_lra')
                            ->where('periode_id', $request->periode)
                            ->where('year', $request->year)
                            ->where('instance_id', $request->instance)
                            ->where('kode_rekening', '4')
                            ->first();
                        $LRABelanja = DB::table('acc_lra')
                            ->where('periode_id', $request->periode)
                            ->where('year', $request->year)
                            ->where('instance_id', $request->instance)
                            ->where('kode_rekening', '5')
                            ->first();
                        $LRAPembiayaan = DB::table('acc_lra')
                            ->where('periode_id', $request->periode)
                            ->where('year', $request->year)
                            ->where('instance_id', $request->instance)
                            ->where('kode_rekening', '6')
                            ->first();
                        $saldoAkhir = (floatval($LRAPendapatan->realisasi ?? 0)) - (floatval($LRABelanja->realisasi ?? 0)) + (floatval($LRAPembiayaan->realisasi ?? 0));
                        $saldoAkhir = $saldoAkhir * -1;
                        $notes = '(LRA) Realisasi Pendapatan - (LRA) Realisasi Belanja + (LRA) Realisasi Pembiayaan';
                    }
                    if ($uraian['id'] == 2) {
                        $accReportKode7 = DB::table('acc_report')
                            ->where('periode_id', $request->periode)
                            ->where('year', $request->year)
                            ->where('instance_id', $request->instance)
                            ->where('fullcode', '7')
                            ->where('type', 'lo')
                            ->first();
                        $accReportKode8 = DB::table('acc_report')
                            ->where('periode_id', $request->periode)
                            ->where('year', $request->year)
                            ->where('instance_id', $request->instance)
                            ->where('fullcode', '8')
                            ->where('type', 'lo')
                            ->first();

                        $saldoAkhir = floatval($accReportKode7->saldo_akhir ?? 0) - floatval($accReportKode8->saldo_akhir ?? 0);
                        $notes = '(Laporan LO) Rek 7 Pendapatan - (Laporan LO) Rek 8 Beban';
                    }
                    if ($uraian['id'] == 8) {
                        $saldoAkhir = $datas[0]['saldo_akhir'] + $datas[1]['saldo_akhir'] + $datas[2]['saldo_akhir'] + $datas[3]['saldo_akhir'] + $datas[4]['saldo_akhir'] + $datas[5]['saldo_akhir'] + $datas[6]['saldo_akhir'];
                        $notes = 'Ekuitas Awal + Surplus / Defisit LO + RK PKKD + Dampak Kumulatif Perubahan Kebijakan / Kesalahan Mendasar';
                    }

                    $checkExists = DB::table('acc_report')
                        ->where('type', 'lpe')
                        ->where('year', $request->year)
                        ->where('periode_id', $request->periode)
                        ->where('instance_id', $request->instance)
                        ->where('fullcode', $uraian['name'])
                        ->first();

                    if (!$checkExists) {
                        DB::table('acc_report')
                            ->insert([
                                'instance_id' => $request->instance,
                                'year' => $request->year,
                                'periode_id' => $request->periode,
                                'fullcode' => $uraian['name'],
                                'type' => 'lpe',
                                'saldo_awal' => 0,
                                'saldo_akhir' => $saldoAkhir ?? 0,
                                'kenaikan_penurunan' => $saldoAkhir ?? 0,
                                'percent' => 0,
                                // 'keterangan' => $notes,
                                'created_at' => $now,
                                'updated_at' => $now,
                            ]);
                    } else if ($checkExists->keterangan != 'Manual Update') {
                        DB::table('acc_report')
                            ->where('type', 'lpe')
                            ->where('year', $request->year)
                            ->where('periode_id', $request->periode)
                            ->where('instance_id', $request->instance)
                            ->where('fullcode', $uraian['name'])
                            ->update([
                                'saldo_akhir' => $saldoAkhir ?? 0,
                                'kenaikan_penurunan' => $saldoAkhir ?? 0,
                                'percent' => 0,
                                // 'keterangan' => $notes,
                                'updated_at' => $now,
                            ]);
                    } else if ($checkExists->keterangan == 'Manual Update' && ($uraian['id'] == 2 || $uraian['id'] == 3)) {
                        DB::table('acc_report')
                            ->where('type', 'lpe')
                            ->where('year', $request->year)
                            ->where('periode_id', $request->periode)
                            ->where('instance_id', $request->instance)
                            ->where('fullcode', $uraian['name'])
                            ->update([
                                'saldo_akhir' => $saldoAkhir ?? 0,
                                'kenaikan_penurunan' => $saldoAkhir ?? 0,
                                'percent' => 0,
                                // 'keterangan' => $notes,
                                'updated_at' => $now,
                            ]);
                    }

                    $checkExists = DB::table('acc_report')
                        ->where('type', 'lpe')
                        ->where('year', $request->year)
                        ->where('periode_id', $request->periode)
                        ->where('instance_id', $request->instance)
                        ->where('fullcode', $uraian['name'])
                        ->first();

                    if ($checkExists->keterangan == 'Manual Update') {
                        $datas[] = [
                            'id' => $checkExists->id ?? null,
                            'uraian' => $checkExists->fullcode ?? $uraian['name'],
                            'level' => $uraian['level'],
                            'parent' => $uraian['parent'],
                            'saldo_awal' => $checkExists->saldo_awal,
                            'saldo_akhir' => $checkExists->saldo_akhir,
                            'notes' => $notes,
                        ];
                    } else {
                        $datas[] = [
                            'id' => $checkExists->id ?? null,
                            'uraian' => $uraian['name'],
                            'level' => $uraian['level'],
                            'parent' => $uraian['parent'],
                            'saldo_awal' => $checkExists->saldo_awal ?? 0,
                            'saldo_akhir' => $saldoAkhir ?? 0,
                            'notes' => $notes,
                        ];
                    }
                }

                DB::commit();
                return $this->successResponse($datas);
            }
        } catch (\Exception $e) {
            DB::rollBack();
            return $this->errorResponse($e->getMessage() . ' - ' . $e->getLine());
        }
    }

    function saveReportLPE(Request $request)
    {
        $validate = Validator::make($request->all(), [
            'data.*.id' => 'required|exists:acc_report,id',
            'data.*.saldo_awal' => 'required|numeric',
            'data.*.saldo_akhir' => 'required|numeric',
        ], [], [
            'data.*.id' => 'Data ID',
            'data.*.saldo_awal' => 'Saldo Awal',
            'data.*.saldo_akhir' => 'Saldo Akhir',
        ]);

        if ($validate->fails()) {
            return $this->validationResponse($validate->errors());
        }
        DB::beginTransaction();
        try {
            $now = now();
            $datas = $request->data;
            foreach ($datas as $data) {
                $exists = DB::table('acc_report')
                    ->where('id', $data['id'])
                    ->where('type', 'lpe')
                    ->first();
                if ($exists) {
                    DB::table('acc_report')
                        ->where('id', $data['id'])
                        ->update([
                            'saldo_awal' => $data['saldo_awal'],
                            'saldo_akhir' => $data['saldo_akhir'],
                            'keterangan' => 'Manual Update',
                        ]);
                }
            }
            DB::commit();
            return $this->successResponse($datas);
        } catch (\Exception $e) {
            DB::rollBack();
            return $this->errorResponse($e->getMessage() . ' - ' . $e->getLine());
        }
    }

    function resetReportLPE(Request $request)
    {
        $validate = Validator::make($request->all(), [
            'instance' => 'required|exists:instances,id',
            'periode' => 'required|exists:ref_periode,id',
            'year' => 'required|numeric',
        ], [], [
            'instance' => 'Instansi ID',
            'periode' => 'Periode',
            'year' => 'Tahun',
        ]);

        if ($validate->fails()) {
            return $this->validationResponse($validate->errors());
        }

        DB::beginTransaction();
        try {
            DB::table('acc_report')
                ->where('instance_id', $request->instance)
                ->where('periode_id', $request->periode)
                ->where('year', $request->year)
                ->where('type', 'lpe')
                ->update([
                    'keterangan' => null,
                    'saldo_awal' => 0,
                    'saldo_akhir' => 0,
                ]);
            DB::commit();

            return $this->successResponse('Data berhasil direset');
        } catch (\Exception $e) {
            DB::rollBack();
            return $this->errorResponse($e->getMessage() . ' - ' . $e->getLine());
        }
    }

    function downloadExcelLPE(Request $request)
    {
        $validate = Validator::make($request->all(), [
            'instance' => 'nullable',
            'year' => 'required|integer',
            'periode' => 'required|exists:ref_periode,id',
        ], [], [
            'instance' => 'Perangkat Daerah',
            'periode' => 'Periode',
            'year' => 'Tahun',
        ]);

        if ($validate->fails()) {
            return $this->validationResponse($validate->errors());
        }

        try {
            $datas = $request->data;
            if ($request->instance) {
                $instance = DB::table('instances')->where('id', $request->instance)->first();
                $filename = 'lpe-' . str()->slug($instance->alias) . '-' . $request->year . '.xlsx';
            } else {
                $filename = 'lpe-kabupaten-ogan-ilir-' . $request->year . '.xlsx';
            }
            Excel::store(new LPEExport($datas, $request->year), $filename, 'public');

            return $this->successResponse(asset('storage/' . $filename));
        } catch (\Exception $e) {
            return $this->errorResponse($e->getMessage() . ' - ' . $e->getLine());
        }
    }

    function _GetReportLPEKabupaten($periode, $year)
    {
        $now = now();
        $datas = [];
        $arrUraian = [
            [
                'id' => 1,
                'level' => 1,
                'parent' => null,
                'name' => 'Ekuitas Awal',
            ],
            [
                'id' => 2,
                'level' => 1,
                'parent' => null,
                'name' => 'Surplus / Defisit LO',
            ],
            [
                'id' => 3,
                'level' => 1,
                'parent' => null,
                'name' => 'RK PKKD',
            ],
            [
                'id' => 4,
                'level' => 1,
                'parent' => null,
                'name' => 'Dampak Kumulatif Perubahan Kebijakan / Kesalahan Mendasar',
            ],
            [
                'id' => 5,
                'level' => 2,
                'parent' => 4,
                'name' => 'Koreksi Nilai Persediaan',
            ],
            [
                'id' => 6,
                'level' => 2,
                'parent' => 4,
                'name' => 'Koreksi Selisih Revaluasi Aset Tetap',
            ],
            [
                'id' => 7,
                'level' => 2,
                'parent' => 4,
                'name' => 'Lain-lain',
            ],
            [
                'id' => 8,
                'level' => 1,
                'parent' => null,
                'name' => 'Ekuitas Akhir',
            ],
        ];

        foreach ($arrUraian as $uraian) {
            // $saldoAkhir = 0;
            $notes = null;
            if ($uraian['id'] == 3) {
                // $LRAPendapatan = DB::table('acc_lra')
                //     ->where('periode_id', $periode)
                //     ->where('year', $year)
                //     ->where('kode_rekening', '4')
                //     ->get();
                // $realisasiPendapatan = $LRAPendapatan->sum('realisasi');
                // $LRABelanja = DB::table('acc_lra')
                //     ->where('periode_id', $periode)
                //     ->where('year', $year)
                //     ->where('kode_rekening', '5')
                //     ->get();
                // $realisasiBelanja = $LRABelanja->sum('realisasi');
                // $LRAPembiayaan = DB::table('acc_lra')
                //     ->where('periode_id', $periode)
                //     ->where('year', $year)
                //     ->where('kode_rekening', '6')
                //     ->get();
                // $realisasiPembiayaan = $LRAPembiayaan->sum('realisasi');
                // $saldoAkhir = (floatval($realisasiPendapatan ?? 0)) - (floatval($realisasiBelanja ?? 0)) + (floatval($realisasiPembiayaan ?? 0));
                // $saldoAkhir = $saldoAkhir * -1;
                $notes = '(LRA) Realisasi Pendapatan - (LRA) Realisasi Belanja + (LRA) Realisasi Pembiayaan';
            }
            if ($uraian['id'] == 2) {
                // $accReportKode7 = DB::table('acc_report')
                //     ->where('periode_id', $periode)
                //     ->where('year', $year)
                //     ->where('fullcode', '7')
                //     ->where('type', 'lo')
                //     ->get();
                // $saldoAkhirKode7 = $accReportKode7->sum('saldo_akhir');
                // $accReportKode8 = DB::table('acc_report')
                //     ->where('periode_id', $periode)
                //     ->where('year', $year)
                //     ->where('fullcode', '8')
                //     ->where('type', 'lo')
                //     ->get();
                // $saldoAkhirKode8 = $accReportKode8->sum('saldo_akhir');

                // $saldoAkhir = floatval($saldoAkhirKode7 ?? 0) - floatval($saldoAkhirKode8 ?? 0);
                $notes = '(Laporan LO) Rek 7 Pendapatan - (Laporan LO) Rek 8 Beban';
            }
            if ($uraian['id'] == 8) {
                // $saldoAkhir = $datas[0]['saldo_akhir'] + $datas[1]['saldo_akhir'] + $datas[2]['saldo_akhir'] + $datas[3]['saldo_akhir'] + $datas[4]['saldo_akhir'] + $datas[5]['saldo_akhir'] + $datas[6]['saldo_akhir'];
                $notes = 'Ekuitas Awal + Surplus / Defisit LO + RK PKKD + Dampak Kumulatif Perubahan Kebijakan / Kesalahan Mendasar';
            }

            $checkExists = DB::table('acc_report')
                ->where('type', 'lpe')
                ->where('year', $year)
                ->where('periode_id', $periode)
                ->where('fullcode', $uraian['name'])
                ->get();

            $datas[] = [
                'id' => null,
                'uraian' => $uraian['name'],
                'level' => $uraian['level'],
                'parent' => $uraian['parent'],
                // 'saldo_awal' => 0,
                // 'saldo_akhir' => $saldoAkhir ?? 0,
                'saldo_awal' => $checkExists->sum('saldo_awal') ?? 0,
                'saldo_akhir' => $checkExists->sum('saldo_akhir') ?? 0,
                'notes' => $notes,
            ];
        }

        return $datas;
    }

    // function reportNeraca(Request $request)
    // {
    //     $validate = Validator::make($request->all(), [
    //         'instance' => 'nullable',
    //         'year' => 'required|integer',
    //         'periode' => 'required|exists:ref_periode,id',
    //         'level' => 'required|integer',
    //     ], [], [
    //         'instance' => 'Perangkat Daerah',
    //         'periode' => 'Periode',
    //         'year' => 'Tahun',
    //         'level' => 'Level',
    //     ]);

    //     if ($validate->fails()) {
    //         return $this->validationResponse($validate->errors());
    //     }

    //     $level = $request->level ?? 6;
    //     $now = now();
    //     DB::beginTransaction();
    //     try {
    //         $datas = [];

    //         if ($request->instance == 0) {
    //             $datas = $this->_GetReportNeracaKabupaten($request->periode, $request->year, $level);
    //             return $this->successResponse($datas);
    //         } elseif ($request->instance) {
    //             // KODE REKENING 1 - ASET - START
    //             $rekeningAset = DB::table('ref_kode_rekening_complete')
    //                 ->where('code_1', '1')
    //                 ->where('code_2', null)
    //                 ->where('code_3', null)
    //                 ->where('code_4', null)
    //                 ->where('code_5', null)
    //                 ->where('code_6', null)
    //                 ->first();
    //             $preReport = DB::table('acc_pre_report')
    //                 ->when($request->instance, function ($query) use ($request) {
    //                     return $query->where('instance_id', $request->instance);
    //                 })
    //                 ->where('year', $request->year)
    //                 ->where('periode_id', $request->periode)
    //                 ->where('kode_rekening_id', $rekeningAset->id)
    //                 ->first();

    //             $insertToReport = DB::table('acc_report')
    //                 ->where('instance_id', $request->instance)
    //                 ->where('year', $request->year)
    //                 ->where('periode_id', $request->periode)
    //                 ->where('kode_rekening_id', $rekeningAset->id)
    //                 ->where('type', 'neraca')
    //                 ->first();
    //             if (!$insertToReport && $request->instance) {
    //                 DB::table('acc_report')->insert([
    //                     'periode_id' => $request->periode,
    //                     'year' => $request->year,
    //                     'instance_id' => $request->instance,
    //                     'kode_rekening_id' => $rekeningAset->id,
    //                     'fullcode' => $rekeningAset->fullcode,
    //                     'type' => 'neraca',
    //                     'saldo_awal' => $preReport->saldo_awal ?? 0,
    //                     'saldo_akhir' => 0,
    //                     'kenaikan_penurunan' => 0,
    //                     'percent' => 0,
    //                     'created_at' => $now,
    //                     'updated_at' => $now,
    //                 ]);
    //             } else {
    //                 DB::table('acc_report')
    //                     ->where('instance_id', $request->instance)
    //                     ->where('year', $request->year)
    //                     ->where('periode_id', $request->periode)
    //                     ->where('kode_rekening_id', $rekeningAset->id)
    //                     ->where('type', 'neraca')
    //                     ->update([
    //                         'saldo_awal' => $preReport->saldo_awal ?? 0,
    //                     ]);
    //             }

    //             $datas[] = [
    //                 'id' => $insertToReport->id ?? null,
    //                 'kode_rekening_id' => $rekeningAset->id,
    //                 'fullcode' => $rekeningAset->fullcode,
    //                 'code_1' => $rekeningAset->code_1,
    //                 'code_2' => $rekeningAset->code_2,
    //                 'code_3' => $rekeningAset->code_3,
    //                 'code_4' => $rekeningAset->code_4,
    //                 'code_5' => $rekeningAset->code_5,
    //                 'code_6' => $rekeningAset->code_6,
    //                 'name' => $rekeningAset->name,
    //                 'saldo_awal' => $preReport->saldo_awal ?? 0,
    //                 'saldo_akhir' => $saldoAkhir ?? ($insertToReport->saldo_akhir ?? 0),
    //             ];
    //             $arrRekeningAset = DB::table('ref_kode_rekening_complete')
    //                 ->where('code_1', '1')
    //                 ->whereIn('code_2', ['1', '2', '3', '5'])
    //                 ->when($level, function ($query) use ($level) {
    //                     if ($level == 6) {
    //                         return $query;
    //                     } else if ($level == 5) {
    //                         return $query->where('code_6', null);
    //                     } else if ($level == 4) {
    //                         return $query->where('code_5', null);
    //                     } else if ($level == 3) {
    //                         return $query->where('code_4', null);
    //                     } else if ($level == 2) {
    //                         return $query->where('code_3', null);
    //                     } else if ($level == 1) {
    //                         return $query->where('code_2', null);
    //                     }
    //                 })
    //                 ->orderBy('fullcode', 'asc')
    //                 ->get();
    //             foreach ($arrRekeningAset as $rekeningAset) {
    //                 $preReport = DB::table('acc_pre_report')
    //                     ->when($request->instance, function ($query) use ($request) {
    //                         return $query->where('instance_id', $request->instance);
    //                     })
    //                     ->where('year', $request->year)
    //                     ->where('periode_id', $request->periode)
    //                     ->where('kode_rekening_id', $rekeningAset->id)
    //                     ->first();
    //                 $saldoAkhir = 0;

    //                 $insertToReport = DB::table('acc_report')
    //                     ->where('instance_id', $request->instance)
    //                     ->where('year', $request->year)
    //                     ->where('periode_id', $request->periode)
    //                     ->where('kode_rekening_id', $rekeningAset->id)
    //                     ->where('type', 'neraca')
    //                     ->first();

    //                 if ($insertToReport && $insertToReport->keterangan == 'Manual Update') {
    //                     $saldoAkhir = $insertToReport->saldo_akhir;
    //                 } else {
    //                     // 1.1.03 - 1.1.08 PIUTANG TARIKAN START
    //                     if ($rekeningAset->code_1 == 1 && $rekeningAset->code_2 == 1 && $rekeningAset->code_3 == '03') {
    //                         $saldoAkhir = DB::table('acc_plo_piutang')
    //                             ->where('type', 'pendapatan_pajak_daerah')
    //                             ->where('periode_id', $request->periode)
    //                             ->where('year', $request->year)
    //                             ->when($request->instance, function ($query) use ($request) {
    //                                 return $query->where('instance_id', $request->instance);
    //                             })
    //                             ->whereNull('deleted_at')
    //                             ->sum('saldo_akhir');
    //                     } else if ($rekeningAset->code_1 == 1 && $rekeningAset->code_2 == 1 && $rekeningAset->code_3 == '04') {
    //                         $saldoAkhir = DB::table('acc_plo_piutang')
    //                             ->where('type', 'hasil_retribusi_daerah')
    //                             ->where('periode_id', $request->periode)
    //                             ->where('year', $request->year)
    //                             ->when($request->instance, function ($query) use ($request) {
    //                                 return $query->where('instance_id', $request->instance);
    //                             })
    //                             ->whereNull('deleted_at')
    //                             ->sum('saldo_akhir');
    //                     } else if ($rekeningAset->code_1 == 1 && $rekeningAset->code_2 == 1 && $rekeningAset->code_3 == '05') {
    //                         $saldoAkhir = DB::table('acc_plo_piutang')
    //                             ->where('type', 'hasil_pengelolaan_kekayaan_daerah_yang_dipisahkan')
    //                             ->where('periode_id', $request->periode)
    //                             ->where('year', $request->year)
    //                             ->when($request->instance, function ($query) use ($request) {
    //                                 return $query->where('instance_id', $request->instance);
    //                             })
    //                             ->whereNull('deleted_at')
    //                             ->sum('saldo_akhir');
    //                     } else if ($rekeningAset->code_1 == 1 && $rekeningAset->code_2 == 1 && $rekeningAset->code_3 == '06') {
    //                         $saldoAkhir = DB::table('acc_plo_piutang')
    //                             ->where('type', 'lain_lain_pad_yang_sah')
    //                             ->where('periode_id', $request->periode)
    //                             ->where('year', $request->year)
    //                             ->when($request->instance, function ($query) use ($request) {
    //                                 return $query->where('instance_id', $request->instance);
    //                             })
    //                             ->whereNull('deleted_at')
    //                             ->sum('saldo_akhir');
    //                     } else if ($rekeningAset->code_1 == 1 && $rekeningAset->code_2 == 1 && $rekeningAset->code_3 == '07') {
    //                         $saldoAkhir = DB::table('acc_plo_piutang')
    //                             ->where('type', 'transfer_pemerintah_pusat')
    //                             ->where('periode_id', $request->periode)
    //                             ->where('year', $request->year)
    //                             ->when($request->instance, function ($query) use ($request) {
    //                                 return $query->where('instance_id', $request->instance);
    //                             })
    //                             ->whereNull('deleted_at')
    //                             ->sum('saldo_akhir');
    //                     } else if ($rekeningAset->code_1 == 1 && $rekeningAset->code_2 == 1 && $rekeningAset->code_3 == '08') {
    //                         $saldoAkhir = DB::table('acc_plo_piutang')
    //                             ->where('type', 'transfer_antar_daerah')
    //                             ->where('periode_id', $request->periode)
    //                             ->where('year', $request->year)
    //                             ->when($request->instance, function ($query) use ($request) {
    //                                 return $query->where('instance_id', $request->instance);
    //                             })
    //                             ->whereNull('deleted_at')
    //                             ->sum('saldo_akhir');
    //                     }
    //                     // 1.1.03 - 1.1.08 PIUTANG TARIKAN START

    //                     // 1.1.10 PENYISIHAN START
    //                     else if ($rekeningAset->code_1 == 1 && $rekeningAset->code_2 == 1 && $rekeningAset->code_3 == '10') {
    //                         $saldoAkhir = DB::table('acc_plo_penyisihan')
    //                             ->where('periode_id', $request->periode)
    //                             ->where('year', $request->year)
    //                             ->when($request->instance, function ($query) use ($request) {
    //                                 return $query->where('instance_id', $request->instance);
    //                             })
    //                             ->whereNull('deleted_at')
    //                             ->sum('jumlah');
    //                         $saldoAkhir = $saldoAkhir * -1;
    //                     }
    //                     // 1.1.10 PENYISIHAN END

    //                     // 1.1.11 BELANJA BAYAR DIMUKA START
    //                     else if ($rekeningAset->code_1 == 1 && $rekeningAset->code_2 == 1 && $rekeningAset->code_3 == '11') {
    //                         $saldoAkhir = DB::table('acc_belanja_bayar_dimuka')
    //                             ->where('periode_id', $request->periode)
    //                             ->where('year', $request->year)
    //                             ->when($request->instance, function ($query) use ($request) {
    //                                 return $query->where('instance_id', $request->instance);
    //                             })
    //                             ->whereNull('deleted_at')
    //                             ->sum('belum_jatuh_tempo');
    //                     }
    //                     // 1.1.11 BELANJA BAYAR DIMUKA END

    //                     // 1.1.12 PERSEDIAAN START
    //                     else if ($rekeningAset->code_1 == 1 && $rekeningAset->code_2 == 1 && $rekeningAset->code_3 == '12') {
    //                         $saldoAkhir = DB::table('acc_persediaan_barang_habis_pakai')
    //                             ->where('periode_id', $request->periode)
    //                             ->where('year', $request->year)
    //                             ->when($request->instance, function ($query) use ($request) {
    //                                 return $query->where('instance_id', $request->instance);
    //                             })
    //                             ->sum('saldo_akhir');
    //                         $saldoAkhir += DB::table('acc_persediaan_belanja_persediaan_untuk_dijual')
    //                             ->where('periode_id', $request->periode)
    //                             ->where('year', $request->year)
    //                             ->when($request->instance, function ($query) use ($request) {
    //                                 return $query->where('instance_id', $request->instance);
    //                             })
    //                             ->whereNull('deleted_at')
    //                             ->sum('saldo_akhir');
    //                     }
    //                     // 1.1.12 PERSEDIAAN END

    //                     // 1.1.12 PERSEDIAAN START
    //                     else if ($rekeningAset->code_1 == 1 && $rekeningAset->code_2 == 1 && $rekeningAset->code_3 == '13') {
    //                         $saldoAkhir = 0;
    //                     }
    //                     // 1.1.12 PERSEDIAAN END

    //                     // 1.3.01 REKON ASET TANAH START
    //                     else if ($rekeningAset->code_1 == 1 && $rekeningAset->code_2 == 3 && $rekeningAset->code_3 == '01') {
    //                         $saldoAkhir = DB::table('acc_rek_as_rekap_opd')
    //                             ->where('periode_id', $request->periode)
    //                             ->where('year', $request->year)
    //                             ->when($request->instance, function ($query) use ($request) {
    //                                 return $query->where('instance_id', $request->instance);
    //                             })
    //                             ->sum('tanah');
    //                     }
    //                     // 1.3.01 REKON ASET TANAH END

    //                     // 1.3.02 REKON ASET PERALATAN DAN MESIN START
    //                     else if ($rekeningAset->code_1 == 1 && $rekeningAset->code_2 == 3 && $rekeningAset->code_3 == '02') {
    //                         $saldoAkhir = DB::table('acc_rek_as_rekap_opd')
    //                             ->where('periode_id', $request->periode)
    //                             ->where('year', $request->year)
    //                             ->when($request->instance, function ($query) use ($request) {
    //                                 return $query->where('instance_id', $request->instance);
    //                             })
    //                             ->whereNull('deleted_at')
    //                             ->sum('peralatan_mesin');
    //                     }
    //                     // 1.3.02 REKON ASET PERALATAN DAN MESIN END

    //                     // 1.3.03 REKON ASET GEDUNG DAN BANGUNAN START
    //                     else if ($rekeningAset->code_1 == 1 && $rekeningAset->code_2 == 3 && $rekeningAset->code_3 == '03') {
    //                         $saldoAkhir = DB::table('acc_rek_as_rekap_opd')
    //                             ->where('periode_id', $request->periode)
    //                             ->where('year', $request->year)
    //                             ->when($request->instance, function ($query) use ($request) {
    //                                 return $query->where('instance_id', $request->instance);
    //                             })
    //                             ->whereNull('deleted_at')
    //                             ->sum('gedung_bangunan');
    //                     }
    //                     // 1.3.03 REKON ASET GEDUNG DAN BANGUNAN END

    //                     // 1.3.04 REKON ASET Jalan, Jaringan, dan Irigasi START
    //                     else if ($rekeningAset->code_1 == 1 && $rekeningAset->code_2 == 3 && $rekeningAset->code_3 == '04') {
    //                         $saldoAkhir = DB::table('acc_rek_as_rekap_opd')
    //                             ->where('periode_id', $request->periode)
    //                             ->where('year', $request->year)
    //                             ->when($request->instance, function ($query) use ($request) {
    //                                 return $query->where('instance_id', $request->instance);
    //                             })
    //                             ->whereNull('deleted_at')
    //                             ->sum('jalan_jaringan_irigasi');
    //                     }
    //                     // 1.3.04 REKON ASET Jalan, Jaringan, dan Irigasi END

    //                     // 1.3.05 REKON Aset Tetap Lainnya START
    //                     else if ($rekeningAset->code_1 == 1 && $rekeningAset->code_2 == 3 && $rekeningAset->code_3 == '05') {
    //                         $saldoAkhir = DB::table('acc_rek_as_rekap_opd')
    //                             ->where('periode_id', $request->periode)
    //                             ->where('year', $request->year)
    //                             ->when($request->instance, function ($query) use ($request) {
    //                                 return $query->where('instance_id', $request->instance);
    //                             })
    //                             ->whereNull('deleted_at')
    //                             ->sum('aset_tetap_lainnya');
    //                     }
    //                     // 1.3.05 REKON Aset Tetap Lainnya END

    //                     // 1.3.06 REKON Konstruksi Dalam Pengerjaan START
    //                     else if ($rekeningAset->code_1 == 1 && $rekeningAset->code_2 == 3 && $rekeningAset->code_3 == '06') {
    //                         $saldoAkhir = DB::table('acc_rek_as_rekap_opd')
    //                             ->where('periode_id', $request->periode)
    //                             ->where('year', $request->year)
    //                             ->when($request->instance, function ($query) use ($request) {
    //                                 return $query->where('instance_id', $request->instance);
    //                             })
    //                             ->whereNull('deleted_at')
    //                             ->sum('kdp');
    //                     }
    //                     // 1.3.06 REKON Konstruksi Dalam Pengerjaan END

    //                     // 1.3.07 REKON Akumulasi Penyusutan START
    //                     else if ($rekeningAset->code_1 == 1 && $rekeningAset->code_2 == 3 && $rekeningAset->code_3 == '07') {
    //                         $saldoAkhir = DB::table('acc_rek_as_penyusutan')
    //                             ->where('periode_id', $request->periode)
    //                             ->where('year', $request->year)
    //                             ->when($request->instance, function ($query) use ($request) {
    //                                 return $query->where('instance_id', $request->instance);
    //                             })
    //                             ->whereNull('deleted_at')
    //                             ->sum('akumulasi_penyusutan');
    //                         $saldoAkhir = $saldoAkhir * -1;
    //                     }
    //                     // 1.3.07 REKON Akumulasi Penyusutan END

    //                     // 1.5.03 REKON ASET TAK BERWUJUD START
    //                     else if ($rekeningAset->code_1 == 1 && $rekeningAset->code_2 == 5 && $rekeningAset->code_3 == '03') {
    //                         $saldoAkhir = DB::table('acc_rek_as_aset_tak_berwujud')
    //                             ->where('periode_id', $request->periode)
    //                             ->where('year', $request->year)
    //                             ->when($request->instance, function ($query) use ($request) {
    //                                 return $query->where('instance_id', $request->instance);
    //                             })
    //                             ->whereNull('deleted_at')
    //                             ->sum('saldo_akhir');
    //                     }
    //                     // 1.5.03 REKON ASET TAK BERWUJUD END

    //                     // 1.5.04 REKON ASET LAIN-LAIN START
    //                     else if ($rekeningAset->code_1 == 1 && $rekeningAset->code_2 == 5 && $rekeningAset->code_3 == '04') {
    //                         $saldoAkhir = DB::table('acc_rek_as_aset_lain_lain')
    //                             ->where('periode_id', $request->periode)
    //                             ->where('year', $request->year)
    //                             ->when($request->instance, function ($query) use ($request) {
    //                                 return $query->where('instance_id', $request->instance);
    //                             })
    //                             ->whereNull('deleted_at')
    //                             ->sum('saldo_akhir');
    //                     }
    //                     // 1.5.04 REKON ASET LAIN-LAIN END
    //                 }

    //                 if (!$insertToReport && $request->instance) {
    //                     DB::table('acc_report')->insert([
    //                         'periode_id' => $request->periode,
    //                         'year' => $request->year,
    //                         'instance_id' => $request->instance,
    //                         'kode_rekening_id' => $rekeningAset->id,
    //                         'fullcode' => $rekeningAset->fullcode,
    //                         'type' => 'neraca',
    //                         'saldo_awal' => $preReport->saldo_awal ?? 0,
    //                         'saldo_akhir' => $saldoAkhir ?? 0,
    //                         'kenaikan_penurunan' => isset($saldoAkhir) ? ($saldoAkhir - ($preReport->saldo_awal ?? 0)) : 0,
    //                         'percent' => isset($saldoAkhir) ? (($preReport->saldo_awal ?? 0) == 0 ? 0 : (($saldoAkhir - ($preReport->saldo_awal ?? 0)) / ($preReport->saldo_awal ?? 0)) * 100) : 0,
    //                         'created_at' => $now,
    //                         'updated_at' => $now,
    //                     ]);
    //                 } else {
    //                     if (isset($saldoAkhir) && $insertToReport && $saldoAkhir != $insertToReport->saldo_akhir && $level == 3) {
    //                         DB::table('acc_report')
    //                             // ->where('instance_id', $request->instance)
    //                             // ->where('year', $request->year)
    //                             // ->where('periode_id', $request->periode)
    //                             // ->where('kode_rekening_id', $rekeningAset->id)
    //                             ->where('id', $insertToReport->id)
    //                             ->whereNull('keterangan')
    //                             ->where('type', 'neraca')
    //                             ->update([
    //                                 'saldo_akhir' => $saldoAkhir ?? 0,
    //                                 'kenaikan_penurunan' => isset($saldoAkhir) ? ($saldoAkhir - ($preReport->saldo_awal ?? 0)) : 0,
    //                                 'percent' => ($preReport->saldo_awal ?? 0) == 0 ? 0 : (($saldoAkhir - ($preReport->saldo_awal ?? 0)) / ($preReport->saldo_awal ?? 0)) * 100,
    //                             ]);
    //                     }
    //                 }

    //                 // if ($preReport) {
    //                 $datas[] = [
    //                     'id' => $insertToReport->id ?? null,
    //                     'kode_rekening_id' => $rekeningAset->id,
    //                     'fullcode' => $rekeningAset->fullcode,
    //                     'code_1' => $rekeningAset->code_1,
    //                     'code_2' => $rekeningAset->code_2,
    //                     'code_3' => $rekeningAset->code_3,
    //                     'code_4' => $rekeningAset->code_4,
    //                     'code_5' => $rekeningAset->code_5,
    //                     'code_6' => $rekeningAset->code_6,
    //                     'name' => $rekeningAset->name,
    //                     'saldo_awal' => $preReport->saldo_awal ?? 0,
    //                     'saldo_akhir' => $saldoAkhir ?? ($insertToReport->saldo_akhir ?? 0),
    //                 ];
    //                 // }
    //             }
    //             // KODE REKENING 1 - ASET - END

    //             // KODE REKENING 2 - KEWAJIBAN - START
    //             $rekeningKewajiban = DB::table('ref_kode_rekening_complete')
    //                 ->where('code_1', '2')
    //                 ->where('code_2', null)
    //                 ->where('code_3', null)
    //                 ->where('code_4', null)
    //                 ->where('code_5', null)
    //                 ->where('code_6', null)
    //                 ->first();
    //             $preReport = DB::table('acc_pre_report')
    //                 ->where('instance_id', $request->instance)
    //                 ->where('year', $request->year)
    //                 ->where('periode_id', $request->periode)
    //                 ->where('kode_rekening_id', $rekeningKewajiban->id)
    //                 ->first();

    //             $insertToReport = DB::table('acc_report')
    //                 ->where('instance_id', $request->instance)
    //                 ->where('year', $request->year)
    //                 ->where('periode_id', $request->periode)
    //                 ->where('kode_rekening_id', $rekeningKewajiban->id)
    //                 ->where('type', 'neraca')
    //                 ->first();
    //             if (!$insertToReport && $request->instance) {
    //                 DB::table('acc_report')->insert([
    //                     'periode_id' => $request->periode,
    //                     'year' => $request->year,
    //                     'instance_id' => $request->instance,
    //                     'kode_rekening_id' => $rekeningKewajiban->id,
    //                     'fullcode' => $rekeningKewajiban->fullcode,
    //                     'type' => 'neraca',
    //                     'saldo_awal' => $preReport->saldo_awal ?? 0,
    //                     'saldo_akhir' => 0,
    //                     'kenaikan_penurunan' => 0,
    //                     'percent' => 0,
    //                     'created_at' => $now,
    //                     'updated_at' => $now,
    //                 ]);
    //             } else {
    //                 DB::table('acc_report')
    //                     ->where('instance_id', $request->instance)
    //                     ->where('year', $request->year)
    //                     ->where('periode_id', $request->periode)
    //                     ->where('kode_rekening_id', $rekeningKewajiban->id)
    //                     ->where('type', 'neraca')
    //                     ->update([
    //                         'saldo_awal' => $preReport->saldo_awal ?? 0,
    //                     ]);
    //             }

    //             $datas[] = [
    //                 'id' => $insertToReport->id ?? null,
    //                 'kode_rekening_id' => $rekeningKewajiban->id,
    //                 'fullcode' => $rekeningKewajiban->fullcode,
    //                 'code_1' => $rekeningKewajiban->code_1,
    //                 'code_2' => $rekeningKewajiban->code_2,
    //                 'code_3' => $rekeningKewajiban->code_3,
    //                 'code_4' => $rekeningKewajiban->code_4,
    //                 'code_5' => $rekeningKewajiban->code_5,
    //                 'code_6' => $rekeningKewajiban->code_6,
    //                 'name' => $rekeningKewajiban->name,
    //                 'saldo_awal' => $preReport->saldo_awal ?? 0,
    //                 'saldo_akhir' => $saldoAkhir ?? ($insertToReport->saldo_akhir ?? 0),
    //             ];

    //             $arrRekeningKewajiban = DB::table('ref_kode_rekening_complete')
    //                 ->where('code_1', '2')
    //                 ->whereIn('code_2', ['1', '2'])
    //                 ->when($level, function ($query) use ($level) {
    //                     if ($level == 6) {
    //                         return $query;
    //                     } else if ($level == 5) {
    //                         return $query->where('code_6', null);
    //                     } else if ($level == 4) {
    //                         return $query->where('code_5', null);
    //                     } else if ($level == 3) {
    //                         return $query->where('code_4', null);
    //                     } else if ($level == 2) {
    //                         return $query->where('code_3', null);
    //                     } else if ($level == 1) {
    //                         return $query->where('code_2', null);
    //                     }
    //                 })
    //                 ->orderBy('fullcode', 'asc')
    //                 ->get();
    //             foreach ($arrRekeningKewajiban as $rekeningKewajiban) {
    //                 $preReport = DB::table('acc_pre_report')
    //                     ->when($request->instance, function ($query) use ($request) {
    //                         return $query->where('instance_id', $request->instance);
    //                     })
    //                     ->where('year', $request->year)
    //                     ->where('periode_id', $request->periode)
    //                     ->where('kode_rekening_id', $rekeningKewajiban->id)
    //                     ->first();
    //                 $insertToReport = DB::table('acc_report')
    //                     ->where('instance_id', $request->instance)
    //                     ->where('year', $request->year)
    //                     ->where('periode_id', $request->periode)
    //                     ->where('kode_rekening_id', $rekeningKewajiban->id)
    //                     ->where('type', 'neraca')
    //                     ->first();
    //                 $saldoAkhir = 0;
    //                 if ($insertToReport && $insertToReport->keterangan == 'Manual Update') {
    //                     $saldoAkhir = $insertToReport->saldo_akhir;
    //                 } else {
    //                     // 2.1.05 PENDAPATAN LO - PENDAPATAN DITERIMA DIMUKA START
    //                     if ($rekeningKewajiban->code_1 == 2 && $rekeningKewajiban->code_2 == 1 && $rekeningKewajiban->code_3 == '05') {
    //                         $saldoAkhir = DB::table('acc_plo_pdd')
    //                             ->where('periode_id', $request->periode)
    //                             ->where('year', $request->year)
    //                             ->when($request->instance, function ($query) use ($request) {
    //                                 return $query->where('instance_id', $request->instance);
    //                             })
    //                             ->whereNull('deleted_at')
    //                             ->sum('pendapatan_diterima_dimuka_akhir');
    //                     }
    //                     // 2.1.05 PENDAPATAN LO - PENDAPATAN DITERIMA DIMUKA END

    //                     // 2.1.06 HUTANG BELANJA START
    //                     else if ($rekeningKewajiban->code_1 == 2 && $rekeningKewajiban->code_2 == 1 && $rekeningKewajiban->code_3 == '06') {
    //                         // $saldoAkhir = DB::table('acc_hutang_belanja')
    //                         //     ->where('periode_id', $request->periode)
    //                         //     ->where('year', $request->year)
    //                         //     ->where('instance_id', $request->instance)
    //                         // ->whereNull('deleted_at')
    //                         //     ->sum('total_hutang_belanja');
    //                         // ------------- BELUM SELESAI BAGIAN HUTANG BELANJA -------------
    //                         $saldoAkhir = 0;
    //                     }
    //                     // 2.1.06 HUTANG BELANJA END
    //                 }


    //                 if (!$insertToReport && $request->instance) {
    //                     DB::table('acc_report')->insert([
    //                         'periode_id' => $request->periode,
    //                         'year' => $request->year,
    //                         'instance_id' => $request->instance,
    //                         'kode_rekening_id' => $rekeningKewajiban->id,
    //                         'fullcode' => $rekeningKewajiban->fullcode,
    //                         'type' => 'neraca',
    //                         'saldo_awal' => $preReport->saldo_awal ?? 0,
    //                         'saldo_akhir' => $saldoAkhir ?? 0,
    //                         'kenaikan_penurunan' => isset($saldoAkhir) ? ($saldoAkhir - ($preReport->saldo_awal ?? 0)) : 0,
    //                         'percent' => ($preReport->saldo_awal ?? 0) == 0 ? 0 : (($saldoAkhir - ($preReport->saldo_awal ?? 0)) / ($preReport->saldo_awal ?? 0)) * 100,
    //                         'created_at' => $now,
    //                         'updated_at' => $now,
    //                     ]);
    //                 } else {
    //                     if (isset($saldoAkhir) && $insertToReport && $saldoAkhir != $insertToReport->saldo_akhir && $level == 3) {
    //                         DB::table('acc_report')
    //                             // ->where('instance_id', $request->instance)
    //                             // ->where('year', $request->year)
    //                             // ->where('periode_id', $request->periode)
    //                             // ->where('kode_rekening_id', $rekeningKewajiban->id)
    //                             ->where('id', $insertToReport->id)
    //                             ->whereNull('keterangan')
    //                             ->where('type', 'neraca')
    //                             ->update([
    //                                 'saldo_akhir' => $saldoAkhir ?? 0,
    //                                 'kenaikan_penurunan' => isset($saldoAkhir) ? ($saldoAkhir - ($preReport->saldo_awal ?? 0)) : 0,
    //                                 'percent' => ($preReport->saldo_awal ?? 0) == 0 ? 0 : (($saldoAkhir - ($preReport->saldo_awal ?? 0)) / ($preReport->saldo_awal ?? 0)) * 100,
    //                             ]);
    //                     }
    //                 }

    //                 // if ($preReport) {
    //                 $datas[] = [
    //                     'id' => $insertToReport->id ?? null,
    //                     'kode_rekening_id' => $rekeningKewajiban->id,
    //                     'fullcode' => $rekeningKewajiban->fullcode,
    //                     'code_1' => $rekeningKewajiban->code_1,
    //                     'code_2' => $rekeningKewajiban->code_2,
    //                     'code_3' => $rekeningKewajiban->code_3,
    //                     'code_4' => $rekeningKewajiban->code_4,
    //                     'code_5' => $rekeningKewajiban->code_5,
    //                     'code_6' => $rekeningKewajiban->code_6,
    //                     'name' => $rekeningKewajiban->name,
    //                     'saldo_awal' => $preReport->saldo_awal ?? 0,
    //                     'saldo_akhir' => $saldoAkhir ?? ($insertToReport->saldo_akhir ?? 0),
    //                 ];
    //                 // }
    //             }
    //             // KODE REKENING 2 - KEWAJIBAN - END

    //             // KODE REKENING 3 - EKUITAS - START
    //             $rekeningEkuitas = DB::table('ref_kode_rekening_complete')
    //                 ->where('code_1', '3')
    //                 ->where('code_2', null)
    //                 ->where('code_3', null)
    //                 ->where('code_4', null)
    //                 ->where('code_5', null)
    //                 ->where('code_6', null)
    //                 ->first();
    //             $preReport = DB::table('acc_pre_report')
    //                 ->where('instance_id', $request->instance)
    //                 ->where('year', $request->year)
    //                 ->where('periode_id', $request->periode)
    //                 ->where('kode_rekening_id', $rekeningEkuitas->id)
    //                 ->first();
    //             $insertToReport = DB::table('acc_report')
    //                 ->where('instance_id', $request->instance)
    //                 ->where('year', $request->year)
    //                 ->where('periode_id', $request->periode)
    //                 ->where('kode_rekening_id', $rekeningEkuitas->id)
    //                 ->where('type', 'neraca')
    //                 ->first();
    //             if (!$insertToReport && $request->instance) {
    //                 DB::table('acc_report')->insert([
    //                     'periode_id' => $request->periode,
    //                     'year' => $request->year,
    //                     'instance_id' => $request->instance,
    //                     'kode_rekening_id' => $rekeningEkuitas->id,
    //                     'fullcode' => $rekeningEkuitas->fullcode,
    //                     'type' => 'neraca',
    //                     'saldo_awal' => $preReport->saldo_awal ?? 0,
    //                     'saldo_akhir' => 0,
    //                     'kenaikan_penurunan' => 0,
    //                     'percent' => 0,
    //                     'created_at' => $now,
    //                     'updated_at' => $now,
    //                 ]);
    //             } else {
    //                 DB::table('acc_report')
    //                     ->where('instance_id', $request->instance)
    //                     ->where('year', $request->year)
    //                     ->where('periode_id', $request->periode)
    //                     ->where('kode_rekening_id', $rekeningEkuitas->id)
    //                     ->where('type', 'neraca')
    //                     ->update([
    //                         'saldo_awal' => $preReport->saldo_awal ?? 0,
    //                     ]);
    //             }
    //             $saldoAkhir = 0;

    //             $datas[] = [
    //                 'id' => $insertToReport->id ?? null,
    //                 'kode_rekening_id' => $rekeningEkuitas->id,
    //                 'fullcode' => $rekeningEkuitas->fullcode,
    //                 'code_1' => $rekeningEkuitas->code_1,
    //                 'code_2' => $rekeningEkuitas->code_2,
    //                 'code_3' => $rekeningEkuitas->code_3,
    //                 'code_4' => $rekeningEkuitas->code_4,
    //                 'code_5' => $rekeningEkuitas->code_5,
    //                 'code_6' => $rekeningEkuitas->code_6,
    //                 'name' => $rekeningEkuitas->name,
    //                 'saldo_awal' => $preReport->saldo_awal ?? 0,
    //                 'saldo_akhir' => $saldoAkhir ?? ($insertToReport->saldo_akhir ?? 0),
    //             ];

    //             $arrRekeningEkuitas = DB::table('ref_kode_rekening_complete')
    //                 ->where('code_1', '3')
    //                 ->whereIn('code_2', ['1'])
    //                 ->when($level, function ($query) use ($level) {
    //                     if ($level == 6) {
    //                         return $query;
    //                     } else if ($level == 5) {
    //                         return $query->where('code_6', null);
    //                     } else if ($level == 4) {
    //                         return $query->where('code_5', null);
    //                     } else if ($level == 3) {
    //                         return $query->where('code_4', null);
    //                     } else if ($level == 2) {
    //                         return $query->where('code_3', null);
    //                     } else if ($level == 1) {
    //                         return $query->where('code_2', null);
    //                     }
    //                 })
    //                 ->orderBy('fullcode', 'asc')
    //                 ->get();

    //             foreach ($arrRekeningEkuitas as $rekeningEkuitas) {
    //                 $preReport = DB::table('acc_pre_report')
    //                     ->when($request->instance, function ($query) use ($request) {
    //                         return $query->where('instance_id', $request->instance);
    //                     })
    //                     ->where('year', $request->year)
    //                     ->where('periode_id', $request->periode)
    //                     ->where('kode_rekening_id', $rekeningEkuitas->id)
    //                     ->first();
    //                 $insertToReport = DB::table('acc_report')
    //                     ->where('instance_id', $request->instance)
    //                     ->where('year', $request->year)
    //                     ->where('periode_id', $request->periode)
    //                     ->where('kode_rekening_id', $rekeningEkuitas->id)
    //                     ->where('type', 'neraca')
    //                     ->first();
    //                 $saldoAkhir = 0;
    //                 if ($insertToReport && $insertToReport->keterangan == 'Manual Update') {
    //                     $saldoAkhir = $insertToReport->saldo_akhir;
    //                 } else {
    //                     if ($rekeningEkuitas->code_1 == 3 && $rekeningEkuitas->code_2 == 1) {
    //                         $saldoAkhir = 0;
    //                     }
    //                 }

    //                 if (!$insertToReport && $request->instance) {
    //                     DB::table('acc_report')->insert([
    //                         'periode_id' => $request->periode,
    //                         'year' => $request->year,
    //                         'instance_id' => $request->instance,
    //                         'kode_rekening_id' => $rekeningEkuitas->id,
    //                         'fullcode' => $rekeningEkuitas->fullcode,
    //                         'type' => 'neraca',
    //                         'saldo_awal' => $preReport->saldo_awal ?? 0,
    //                         'saldo_akhir' => $saldoAkhir ?? 0,
    //                         'kenaikan_penurunan' => isset($saldoAkhir) ? ($saldoAkhir - ($preReport->saldo_awal ?? 0)) : 0,
    //                         'percent' => ($preReport->saldo_awal ?? 0) == 0 ? 0 : (($saldoAkhir - ($preReport->saldo_awal ?? 0)) / ($preReport->saldo_awal ?? 0)) * 100,
    //                         'created_at' => $now,
    //                         'updated_at' => $now,
    //                     ]);
    //                 } else {
    //                     if (isset($saldoAkhir) && $insertToReport && $saldoAkhir != $insertToReport->saldo_akhir && $level == 3) {
    //                         DB::table('acc_report')
    //                             // ->where('instance_id', $request->instance)
    //                             // ->where('year', $request->year)
    //                             // ->where('periode_id', $request->periode)
    //                             // ->where('kode_rekening_id', $rekeningEkuitas->id)
    //                             ->where('id', $insertToReport->id)
    //                             ->whereNull('keterangan')
    //                             ->where('type', 'neraca')
    //                             ->update([
    //                                 'saldo_akhir' => $saldoAkhir ?? 0,
    //                                 'kenaikan_penurunan' => isset($saldoAkhir) ? ($saldoAkhir - ($preReport->saldo_awal ?? 0)) : 0,
    //                                 'percent' => ($preReport->saldo_awal ?? 0) == 0 ? 0 : (($saldoAkhir - ($preReport->saldo_awal ?? 0)) / ($preReport->saldo_awal ?? 0)) * 100,
    //                             ]);
    //                     }
    //                 }

    //                 // if ($preReport) {
    //                 $datas[] = [
    //                     'id' => $insertToReport->id ?? null,
    //                     'kode_rekening_id' => $rekeningEkuitas->id,
    //                     'fullcode' => $rekeningEkuitas->fullcode,
    //                     'code_1' => $rekeningEkuitas->code_1,
    //                     'code_2' => $rekeningEkuitas->code_2,
    //                     'code_3' => $rekeningEkuitas->code_3,
    //                     'code_4' => $rekeningEkuitas->code_4,
    //                     'code_5' => $rekeningEkuitas->code_5,
    //                     'code_6' => $rekeningEkuitas->code_6,
    //                     'name' => $rekeningEkuitas->name,
    //                     'saldo_awal' => $preReport->saldo_awal ?? 0,
    //                     'saldo_akhir' => $saldoAkhir ?? ($insertToReport->saldo_akhir ?? 0),
    //                 ];
    //                 // }
    //             }
    //             // KODE REKENING 3 - EKUITAS - END

    //             // SUMMARIES START
    //             $datas = collect($datas);

    //             // sum level 3 to level 2
    //             $datas = $datas->map(function ($item) use ($datas, $request) {
    //                 if ($item['code_3'] == null) {
    //                     $item['saldo_akhir'] = $datas
    //                         ->filter(function ($value, $key) use ($item) {
    //                             return strlen($value['fullcode']) == 6;
    //                         })
    //                         ->where('code_1', $item['code_1'])
    //                         ->where('code_2', $item['code_2'])
    //                         ->where('code_3', '!=', null)
    //                         ->sum('saldo_akhir');

    //                     $item['saldo_awal'] = $datas
    //                         ->filter(function ($value, $key) use ($item) {
    //                             return strlen($value['fullcode']) == 6;
    //                         })
    //                         ->where('code_1', $item['code_1'])
    //                         ->where('code_2', $item['code_2'])
    //                         ->where('code_3', '!=', null)
    //                         ->sum('saldo_awal');
    //                 }

    //                 // update to acc_report
    //                 $insertToReport = DB::table('acc_report')
    //                     ->where('instance_id', $request->instance)
    //                     ->where('year', $request->year)
    //                     ->where('periode_id', $request->periode)
    //                     ->where('kode_rekening_id', $item['kode_rekening_id'])
    //                     ->where('type', 'neraca')
    //                     ->first();
    //                 if ($insertToReport) {
    //                     DB::table('acc_report')
    //                         ->where('instance_id', $request->instance)
    //                         ->where('year', $request->year)
    //                         ->where('periode_id', $request->periode)
    //                         ->where('kode_rekening_id', $item['kode_rekening_id'])
    //                         ->where('type', 'neraca')
    //                         ->update([
    //                             'saldo_akhir' => $item['saldo_akhir'],
    //                             'kenaikan_penurunan' => $item['saldo_akhir'] - $item['saldo_awal'],
    //                             'percent' => $item['saldo_awal'] == 0 ? 0 : (($item['saldo_akhir'] - $item['saldo_awal']) / $item['saldo_awal']) * 100,
    //                         ]);
    //                 }
    //                 return $item;
    //             });
    //             // sum level 2 to level 1
    //             $datas = $datas->map(function ($item) use ($datas, $request) {
    //                 if ($item['code_2'] == null) {
    //                     $item['saldo_akhir'] = $datas
    //                         ->filter(function ($value, $key) use ($item) {
    //                             return strlen($value['fullcode']) == 3;
    //                         })
    //                         ->where('code_1', $item['code_1'])
    //                         ->where('code_2', '!=', null)
    //                         ->sum('saldo_akhir');

    //                     $item['saldo_awal'] = $datas
    //                         ->filter(function ($value, $key) use ($item) {
    //                             return strlen($value['fullcode']) == 3;
    //                         })
    //                         ->where('code_1', $item['code_1'])
    //                         ->where('code_2', '!=', null)
    //                         ->sum('saldo_awal');
    //                 }

    //                 // update to acc_report
    //                 $insertToReport = DB::table('acc_report')
    //                     ->where('instance_id', $request->instance)
    //                     ->where('year', $request->year)
    //                     ->where('periode_id', $request->periode)
    //                     ->where('kode_rekening_id', $item['kode_rekening_id'])
    //                     ->where('type', 'neraca')
    //                     ->first();
    //                 if ($insertToReport) {
    //                     DB::table('acc_report')
    //                         ->where('instance_id', $request->instance)
    //                         ->where('year', $request->year)
    //                         ->where('periode_id', $request->periode)
    //                         ->where('kode_rekening_id', $item['kode_rekening_id'])
    //                         ->where('type', 'neraca')
    //                         ->update([
    //                             'saldo_akhir' => $item['saldo_akhir'],
    //                             'kenaikan_penurunan' => $item['saldo_akhir'] - $item['saldo_awal'],
    //                             'percent' => $item['saldo_awal'] == 0 ? 0 : (($item['saldo_akhir'] - $item['saldo_awal']) / $item['saldo_awal']) * 100,
    //                         ]);
    //                 }
    //                 return $item;
    //             });

    //             // SUMMARIES START
    //             DB::commit();
    //             return $this->successResponse($datas);
    //         }
    //     } catch (\Exception $e) {
    //         DB::rollBack();
    //         return $this->errorResponse($e->getMessage() . ' - ' . $e->getLine());
    //     }
    // }

    // function reportLO(Request $request)
    // {
    //     $validate = Validator::make($request->all(), [
    //         'instance' => 'nullable',
    //         'year' => 'required|integer',
    //         'periode' => 'required|exists:ref_periode,id',
    //         'level' => 'required|integer',
    //     ], [], [
    //         'instance' => 'Perangkat Daerah',
    //         'periode' => 'Periode',
    //         'year' => 'Tahun',
    //         'level' => 'Level',
    //     ]);

    //     if ($validate->fails()) {
    //         return $this->validationResponse($validate->errors());
    //     }

    //     $level = $request->level ?? 6;
    //     $now = now();

    //     DB::beginTransaction();
    //     try {
    //         $datas = [];
    //         if ($request->instance == 0) {
    //             $datas = $this->_GetReportLOKabupaten($request->periode, $request->year, $level);
    //             return $this->successResponse($datas);
    //         } elseif ($request->instance && $request->instance != 0) {

    //             // check cache
    //             $cache = Cache::get('report_lo_' . $request->instance . '_' . $request->year . '_' . $request->periode);
    //             if ($cache) {
    //                 return $this->successResponse($cache);
    //             }

    //             // KODE REKENING 7 PENDAPATAN - START
    //             $rekeningPendapatan = DB::table('ref_kode_rekening_complete')
    //                 ->where('code_1', '7')
    //                 ->where('code_2', null)
    //                 ->where('code_3', null)
    //                 ->where('code_4', null)
    //                 ->where('code_5', null)
    //                 ->where('code_6', null)
    //                 ->first();
    //             $preReport = DB::table('acc_pre_report')
    //                 ->when($request->instance, function ($query) use ($request) {
    //                     return $query->where('instance_id', $request->instance);
    //                 })
    //                 ->where('year', $request->year)
    //                 ->where('periode_id', $request->periode)
    //                 ->where('kode_rekening_id', $rekeningPendapatan->id)
    //                 ->first();

    //             $insertToReport = DB::table('acc_report')
    //                 ->where('instance_id', $request->instance)
    //                 ->where('year', $request->year)
    //                 ->where('periode_id', $request->periode)
    //                 ->where('kode_rekening_id', $rekeningPendapatan->id)
    //                 ->where('type', 'lo')
    //                 ->first();
    //             if (!$insertToReport && $request->instance) {
    //                 DB::table('acc_report')->insert([
    //                     'periode_id' => $request->periode,
    //                     'year' => $request->year,
    //                     'instance_id' => $request->instance,
    //                     'kode_rekening_id' => $rekeningPendapatan->id,
    //                     'fullcode' => $rekeningPendapatan->fullcode,
    //                     'type' => 'lo',
    //                     'saldo_awal' => $preReport->saldo_awal ?? 0,
    //                     'saldo_akhir' => 0,
    //                     'kenaikan_penurunan' => 0,
    //                     'percent' => 0,
    //                     'created_at' => $now,
    //                     'updated_at' => $now,
    //                 ]);
    //             } else {
    //                 DB::table('acc_report')
    //                     ->where('instance_id', $request->instance)
    //                     ->where('year', $request->year)
    //                     ->where('periode_id', $request->periode)
    //                     ->where('kode_rekening_id', $rekeningPendapatan->id)
    //                     ->where('type', 'lo')
    //                     ->update([
    //                         'saldo_awal' => $preReport->saldo_awal ?? 0,
    //                     ]);
    //             }

    //             $datas[] = [
    //                 'id' => $insertToReport->id ?? null,
    //                 'kode_rekening_id' => $rekeningPendapatan->id,
    //                 'fullcode' => $rekeningPendapatan->fullcode,
    //                 'code_1' => $rekeningPendapatan->code_1,
    //                 'code_2' => $rekeningPendapatan->code_2,
    //                 'code_3' => $rekeningPendapatan->code_3,
    //                 'code_4' => $rekeningPendapatan->code_4,
    //                 'code_5' => $rekeningPendapatan->code_5,
    //                 'code_6' => $rekeningPendapatan->code_6,
    //                 'name' => $rekeningPendapatan->name,
    //                 'saldo_awal' => $preReport->saldo_awal ?? 0,
    //                 'saldo_akhir' => $saldoAkhir ?? ($insertToReport->saldo_akhir ?? 0),
    //             ];
    //             $arrRekeningPendapatan = DB::table('ref_kode_rekening_complete')
    //                 ->where('code_1', '7')
    //                 // 7.1 utk semua OPD 7.2 & 7.3 hanya utk OPD tertentu (lupo [bpkad bae])
    //                 ->when($request->instance, function ($query) use ($request) {
    //                     if ($request->instance == 9) {
    //                         return $query->whereIn('code_2', ['1', '2', '3', '4']);
    //                     } else {
    //                         return $query->whereIn('code_2', ['1', '4']);
    //                     }
    //                 })
    //                 ->when($level, function ($query) use ($level) {
    //                     if ($level == 6) {
    //                         return $query;
    //                     } else if ($level == 5) {
    //                         return $query->where('code_6', null);
    //                     } else if ($level == 4) {
    //                         return $query->where('code_5', null);
    //                     } else if ($level == 3) {
    //                         return $query->where('code_4', null);
    //                     } else if ($level == 2) {
    //                         return $query->where('code_3', null);
    //                     } else if ($level == 1) {
    //                         return $query->where('code_2', null);
    //                     }
    //                 })
    //                 ->orderBy('fullcode', 'asc')
    //                 ->get();

    //             foreach ($arrRekeningPendapatan as $rekeningPendapatan) {
    //                 $preReport = DB::table('acc_pre_report')
    //                     ->when($request->instance, function ($query) use ($request) {
    //                         return $query->where('instance_id', $request->instance);
    //                     })
    //                     ->where('year', $request->year)
    //                     ->where('periode_id', $request->periode)
    //                     ->where('kode_rekening_id', $rekeningPendapatan->id)
    //                     ->whereNull('deleted_at')
    //                     ->first();

    //                 $insertToReport = DB::table('acc_report')
    //                     ->where('instance_id', $request->instance)
    //                     ->where('year', $request->year)
    //                     ->where('periode_id', $request->periode)
    //                     ->where('kode_rekening_id', $rekeningPendapatan->id)
    //                     ->where('type', 'lo')
    //                     ->whereNull('deleted_at')
    //                     ->first();

    //                 $saldoAkhir = 0;

    //                 if ($insertToReport && $insertToReport->keterangan == 'Manual Update') {
    //                     $saldoAkhir = $insertToReport->saldo_akhir;
    //                 } else {
    //                     $kodeRek4 = $rekeningPendapatan->fullcode;
    //                     $kodeRek4 = substr_replace($kodeRek4, '4', 0, 1);

    //                     $rekeningRetribusi = DB::table('ref_kode_rekening_complete')
    //                         ->where('fullcode', $kodeRek4)
    //                         ->first();
    //                     if ($rekeningRetribusi) {
    //                         $saldoAkhir = DB::table('acc_plo_lo_ta')
    //                             ->where('periode_id', $request->periode)
    //                             ->where('year', $request->year)
    //                             ->when($request->instance, function ($query) use ($request) {
    //                                 return $query->where('instance_id', $request->instance);
    //                             })
    //                             ->where('kode_rekening_id', $rekeningRetribusi->id)
    //                             ->whereNull('deleted_at')
    //                             ->sum('laporan_operasional');
    //                     } else {
    //                         $saldoAkhir = 0;
    //                     }
    //                 }

    //                 if (!$insertToReport && $request->instance) {
    //                     DB::table('acc_report')->insert([
    //                         'periode_id' => $request->periode,
    //                         'year' => $request->year,
    //                         'instance_id' => $request->instance,
    //                         'kode_rekening_id' => $rekeningPendapatan->id,
    //                         'fullcode' => $rekeningPendapatan->fullcode,
    //                         'type' => 'lo',
    //                         'saldo_awal' => $preReport->saldo_awal ?? 0,
    //                         'saldo_akhir' => $saldoAkhir ?? 0,
    //                         'kenaikan_penurunan' => isset($saldoAkhir) ? ($saldoAkhir - ($preReport->saldo_awal ?? 0)) : 0,
    //                         'percent' => ($preReport->saldo_awal ?? 0) == 0 ? 0 : (($saldoAkhir - ($preReport->saldo_awal ?? 0)) / ($preReport->saldo_awal ?? 0)) * 100,
    //                         'created_at' => $now,
    //                         'updated_at' => $now,
    //                     ]);
    //                 } else {
    //                     if (isset($saldoAkhir) && $insertToReport && $saldoAkhir != $insertToReport->saldo_akhir && $level == 3) {
    //                         DB::table('acc_report')
    //                             ->where('id', $insertToReport->id)
    //                             ->whereNull('keterangan')
    //                             // ->where('keterangan','!=','Manual Update')
    //                             ->update([
    //                                 'saldo_akhir' => $saldoAkhir ?? 0,
    //                                 'kenaikan_penurunan' => isset($saldoAkhir) ? ($saldoAkhir - ($preReport->saldo_awal ?? 0)) : 0,
    //                                 'percent' => ($preReport->saldo_awal ?? 0) == 0 ? 0 : (($saldoAkhir - ($preReport->saldo_awal ?? 0)) / ($preReport->saldo_awal ?? 0)) * 100,
    //                             ]);
    //                     }
    //                 }

    //                 // if ($preReport) {
    //                 $datas[] = [
    //                     'id' => $insertToReport->id ?? null,
    //                     'kode_rekening_id' => $rekeningPendapatan->id,
    //                     'fullcode' => $rekeningPendapatan->fullcode,
    //                     'code_1' => $rekeningPendapatan->code_1,
    //                     'code_2' => $rekeningPendapatan->code_2,
    //                     'code_3' => $rekeningPendapatan->code_3,
    //                     'code_4' => $rekeningPendapatan->code_4,
    //                     'code_5' => $rekeningPendapatan->code_5,
    //                     'code_6' => $rekeningPendapatan->code_6,
    //                     'name' => $rekeningPendapatan->name,
    //                     'saldo_awal' => $preReport->saldo_awal ?? 0,
    //                     'saldo_akhir' => $saldoAkhir ?? ($insertToReport->saldo_akhir ?? 0),
    //                 ];
    //                 // }
    //             }
    //             // KODE REKENING 7 PENDAPATAN - END

    //             // KODE REKENING 8 BEBAN - START
    //             $rekeningBeban = DB::table('ref_kode_rekening_complete')
    //                 ->where('code_1', '8')
    //                 ->where('code_2', null)
    //                 ->where('code_3', null)
    //                 ->where('code_4', null)
    //                 ->where('code_5', null)
    //                 ->where('code_6', null)
    //                 ->first();
    //             $preReport = DB::table('acc_pre_report')
    //                 ->when($request->instance, function ($query) use ($request) {
    //                     return $query->where('instance_id', $request->instance);
    //                 })
    //                 ->where('year', $request->year)
    //                 ->where('periode_id', $request->periode)
    //                 ->where('kode_rekening_id', $rekeningBeban->id)
    //                 ->where('type', 'lo')
    //                 ->whereNull('deleted_at')
    //                 ->first();

    //             $insertToReport = DB::table('acc_report')
    //                 ->where('instance_id', $request->instance)
    //                 ->where('year', $request->year)
    //                 ->where('periode_id', $request->periode)
    //                 ->where('kode_rekening_id', $rekeningBeban->id)
    //                 ->where('type', 'lo')
    //                 ->whereNull('deleted_at')
    //                 ->first();

    //             if (!$insertToReport && $request->instance) {
    //                 DB::table('acc_report')->insert([
    //                     'periode_id' => $request->periode,
    //                     'year' => $request->year,
    //                     'instance_id' => $request->instance,
    //                     'kode_rekening_id' => $rekeningBeban->id,
    //                     'fullcode' => $rekeningBeban->fullcode,
    //                     'type' => 'lo',
    //                     'saldo_awal' => $preReport->saldo_awal ?? 0,
    //                     'saldo_akhir' => 0,
    //                     'kenaikan_penurunan' => 0,
    //                     'percent' => 0,
    //                     'created_at' => $now,
    //                     'updated_at' => $now,
    //                 ]);
    //             } else {
    //                 DB::table('acc_report')
    //                     ->where('instance_id', $request->instance)
    //                     ->where('year', $request->year)
    //                     ->where('periode_id', $request->periode)
    //                     ->where('kode_rekening_id', $rekeningBeban->id)
    //                     ->where('type', 'lo')
    //                     ->update([
    //                         'saldo_awal' => $preReport->saldo_awal ?? 0,
    //                     ]);
    //             }
    //             $saldoAkhir = 0;

    //             $datas[] = [
    //                 'id' => $insertToReport->id ?? null,
    //                 'kode_rekening_id' => $rekeningBeban->id,
    //                 'fullcode' => $rekeningBeban->fullcode,
    //                 'code_1' => $rekeningBeban->code_1,
    //                 'code_2' => $rekeningBeban->code_2,
    //                 'code_3' => $rekeningBeban->code_3,
    //                 'code_4' => $rekeningBeban->code_4,
    //                 'code_5' => $rekeningBeban->code_5,
    //                 'code_6' => $rekeningBeban->code_6,
    //                 'name' => $rekeningBeban->name,
    //                 'saldo_awal' => $preReport->saldo_awal ?? 0,
    //                 'saldo_akhir' => $saldoAkhir ?? ($insertToReport->saldo_akhir ?? 0),
    //             ];
    //             $arrRekeningBeban = DB::table('ref_kode_rekening_complete')
    //                 ->where('code_1', '8')
    //                 ->whereIn('code_2', ['1', '2', '3', '5'])
    //                 ->when($level, function ($query) use ($level) {
    //                     if ($level == 6) {
    //                         return $query;
    //                     } else if ($level == 5) {
    //                         return $query->where('code_6', null);
    //                     } else if ($level == 4) {
    //                         return $query->where('code_5', null);
    //                     } else if ($level == 3) {
    //                         return $query->where('code_4', null);
    //                     } else if ($level == 2) {
    //                         return $query->where('code_3', null);
    //                     } else if ($level == 1) {
    //                         return $query->where('code_2', null);
    //                     }
    //                 })
    //                 ->orderBy('fullcode', 'asc')
    //                 ->whereNull('deleted_at')
    //                 ->get();

    //             foreach ($arrRekeningBeban as $rekeningBeban) {
    //                 $preReport = DB::table('acc_pre_report')
    //                     ->when($request->instance, function ($query) use ($request) {
    //                         return $query->where('instance_id', $request->instance);
    //                     })
    //                     ->where('year', $request->year)
    //                     ->where('periode_id', $request->periode)
    //                     ->where('kode_rekening_id', $rekeningBeban->id)
    //                     ->whereNull('deleted_at')
    //                     ->first();

    //                 $insertToReport = DB::table('acc_report')
    //                     ->where('instance_id', $request->instance)
    //                     ->where('year', $request->year)
    //                     ->where('periode_id', $request->periode)
    //                     ->where('kode_rekening_id', $rekeningBeban->id)
    //                     ->where('type', 'lo')
    //                     ->whereNull('deleted_at')
    //                     ->first();

    //                 if ($insertToReport && $insertToReport->keterangan == 'Manual Update') {
    //                     $saldoAkhir = $insertToReport->saldo_akhir;
    //                 } else if ($insertToReport) {
    //                     $kodeRek5 = $rekeningBeban->fullcode;
    //                     $kodeRek5 = substr_replace($kodeRek5, '5', 0, 1);

    //                     $rek5 = DB::table('ref_kode_rekening_complete')
    //                         ->where('fullcode', $kodeRek5)
    //                         ->first();

    //                     if ($rek5) {
    //                         if ($rek5->code_2 == '1' && $rek5->code_3 == '01') {
    //                             $saldoAkhir = DB::table('acc_blo_pegawai')
    //                                 ->where('periode_id', $request->periode)
    //                                 ->where('year', $request->year)
    //                                 ->when($request->instance, function ($query) use ($request) {
    //                                     return $query->where('instance_id', $request->instance);
    //                                 })
    //                                 ->where('kode_rekening_id', $rek5->id)
    //                                 ->whereNull('deleted_at')
    //                                 ->sum('beban_lo');
    //                             // } else if ($rek5->code_2 == '1' && $rek5->code_3 == '02') {
    //                             //     $saldoAkhir = DB::table('acc_blo_jasa')
    //                             //         ->where('periode_id', $request->periode)
    //                             //         ->where('year', $request->year)
    //                             //         ->when($request->instance, function ($query) use ($request) {
    //                             //             return $query->where('instance_id', $request->instance);
    //                             //         })
    //                             //         ->where('kode_rekening_id', $rek5->id)
    //                             //         ->sum('beban_lo');

    //                             // TURUN KE KE LEVEL 4 SESUAI DENGAN KERTAS KERJA BEBAN LAPORAN OPERSIONAL
    //                             // 8.1.02.**
    //                             // Lihat di Level 4

    //                         } elseif ($rek5->code_2 == '1' && $rek5->code_3 == '02' && $rek5->code_4 == '01') {
    //                             $saldoAkhir = DB::table('acc_blo_persediaan')
    //                                 ->where('periode_id', $request->periode)
    //                                 ->where('year', $request->year)
    //                                 ->when($request->instance, function ($query) use ($request) {
    //                                     return $query->where('instance_id', $request->instance);
    //                                 })
    //                                 ->where('kode_rekening_id', $rek5->id)
    //                                 ->whereNull('deleted_at')
    //                                 ->sum('beban_lo');
    //                         } elseif ($rek5->code_2 == '1' && $rek5->code_3 == '02' && $rek5->code_4 == '02') {
    //                             $saldoAkhir = DB::table('acc_blo_jasa')
    //                                 ->where('periode_id', $request->periode)
    //                                 ->where('year', $request->year)
    //                                 ->when($request->instance, function ($query) use ($request) {
    //                                     return $query->where('instance_id', $request->instance);
    //                                 })
    //                                 ->where('kode_rekening_id', $rek5->id)
    //                                 ->whereNull('deleted_at')
    //                                 ->sum('beban_lo');
    //                         } elseif ($rek5->code_2 == '1' && $rek5->code_3 == '02' && $rek5->code_4 == '03') {
    //                             $saldoAkhir = DB::table('acc_blo_pemeliharaan')
    //                                 ->where('periode_id', $request->periode)
    //                                 ->where('year', $request->year)
    //                                 ->when($request->instance, function ($query) use ($request) {
    //                                     return $query->where('instance_id', $request->instance);
    //                                 })
    //                                 ->where('kode_rekening_id', $rek5->id)
    //                                 ->whereNull('deleted_at')
    //                                 ->sum('beban_lo');
    //                         } elseif ($rek5->code_2 == '1' && $rek5->code_3 == '02' && $rek5->code_4 == '04') {
    //                             $saldoAkhir = DB::table('acc_blo_perjadin')
    //                                 ->where('periode_id', $request->periode)
    //                                 ->where('year', $request->year)
    //                                 ->when($request->instance, function ($query) use ($request) {
    //                                     return $query->where('instance_id', $request->instance);
    //                                 })
    //                                 ->where('kode_rekening_id', $rek5->id)
    //                                 ->sum('beban_lo');
    //                         } elseif ($rek5->code_2 == '1' && $rek5->code_3 == '02' && $rek5->code_4 == '05') {
    //                             $saldoAkhir = DB::table('acc_blo_uang_jasa_diserahkan')
    //                                 ->where('periode_id', $request->periode)
    //                                 ->where('year', $request->year)
    //                                 ->when($request->instance, function ($query) use ($request) {
    //                                     return $query->where('instance_id', $request->instance);
    //                                 })
    //                                 ->where('kode_rekening_id', $rek5->id)
    //                                 ->whereNull('deleted_at')
    //                                 ->sum('beban_lo');
    //                         } else {
    //                             $saldoAkhir = 0;
    //                         }
    //                     } else {
    //                         $saldoAkhir = 0;
    //                     }
    //                 }

    //                 if (!$insertToReport && $request->instance) {
    //                     DB::table('acc_report')->insert([
    //                         'periode_id' => $request->periode,
    //                         'year' => $request->year,
    //                         'instance_id' => $request->instance,
    //                         'kode_rekening_id' => $rekeningBeban->id,
    //                         'fullcode' => $rekeningBeban->fullcode,
    //                         'type' => 'lo',
    //                         'saldo_awal' => $preReport->saldo_awal ?? 0,
    //                         'saldo_akhir' => $saldoAkhir ?? 0,
    //                         'kenaikan_penurunan' => isset($saldoAkhir) ? ($saldoAkhir - ($preReport->saldo_awal ?? 0)) : 0,
    //                         'percent' => ($preReport->saldo_awal ?? 0) == 0 ? 0 : (($saldoAkhir - ($preReport->saldo_awal ?? 0)) / ($preReport->saldo_awal ?? 0)) * 100,
    //                         'created_at' => $now,
    //                         'updated_at' => $now,
    //                     ]);
    //                 } else {
    //                     if (isset($saldoAkhir) && $insertToReport && $saldoAkhir != $insertToReport->saldo_akhir && $level == 3) {
    //                         DB::table('acc_report')
    //                             ->where('id', $insertToReport->id)
    //                             ->whereNull('keterangan')
    //                             // ->where('keterangan','!=','Manual Update')
    //                             ->where('type', 'lo')
    //                             ->update([
    //                                 'saldo_akhir' => $saldoAkhir ?? 0,
    //                                 'kenaikan_penurunan' => isset($saldoAkhir) ? ($saldoAkhir - ($preReport->saldo_awal ?? 0)) : 0,
    //                                 'percent' => ($preReport->saldo_awal ?? 0) == 0 ? 0 : (($saldoAkhir - ($preReport->saldo_awal ?? 0)) / ($preReport->saldo_awal ?? 0)) * 100,
    //                             ]);
    //                     }
    //                 }

    //                 // if ($preReport) {
    //                 $datas[] = [
    //                     'id' => $insertToReport->id ?? null,
    //                     'kode_rekening_id' => $rekeningBeban->id,
    //                     'fullcode' => $rekeningBeban->fullcode,
    //                     'code_1' => $rekeningBeban->code_1,
    //                     'code_2' => $rekeningBeban->code_2,
    //                     'code_3' => $rekeningBeban->code_3,
    //                     'code_4' => $rekeningBeban->code_4,
    //                     'code_5' => $rekeningBeban->code_5,
    //                     'code_6' => $rekeningBeban->code_6,
    //                     'name' => $rekeningBeban->name,
    //                     'saldo_awal' => $preReport->saldo_awal ?? 0,
    //                     'saldo_akhir' => $saldoAkhir ?? ($insertToReport->saldo_akhir ?? 0),
    //                 ];
    //                 // }
    //             }
    //             // KODE REKENING 8 BEBAN - END

    //             // SUMMARIES START
    //             $datas = collect($datas);

    //             // sum level 6 to level 5
    //             $datas = $datas->map(function ($item) use ($datas, $request) {
    //                 if ($item['code_6'] == null) {
    //                     $item['saldo_akhir'] = $datas
    //                         ->filter(function ($value, $key) use ($item) {
    //                             return strlen($value['fullcode']) == 17;
    //                         })
    //                         ->where('code_1', $item['code_1'])
    //                         ->where('code_2', $item['code_2'])
    //                         ->where('code_3', $item['code_3'])
    //                         ->where('code_4', $item['code_4'])
    //                         ->where('code_5', $item['code_5'])
    //                         ->where('code_6', '!=', null)
    //                         ->sum('saldo_akhir');
    //                 }

    //                 // update to acc_report
    //                 $insertToReport = DB::table('acc_report')
    //                     ->where('instance_id', $request->instance)
    //                     ->where('year', $request->year)
    //                     ->where('periode_id', $request->periode)
    //                     ->where('kode_rekening_id', $item['kode_rekening_id'])
    //                     ->where('type', 'lo')
    //                     ->first();
    //                 if ($insertToReport) {
    //                     DB::table('acc_report')
    //                         ->where('instance_id', $request->instance)
    //                         ->where('year', $request->year)
    //                         ->where('periode_id', $request->periode)
    //                         ->where('kode_rekening_id', $item['kode_rekening_id'])
    //                         ->where('type', 'lo')
    //                         ->update([
    //                             'saldo_akhir' => $item['saldo_akhir'],
    //                             'kenaikan_penurunan' => $item['saldo_akhir'] - $item['saldo_awal'],
    //                             'percent' => $item['saldo_awal'] == 0 ? 0 : (($item['saldo_akhir'] - $item['saldo_awal']) / $item['saldo_awal']) * 100,
    //                         ]);
    //                 }
    //                 return $item;
    //             });
    //             // sum level 5 to level 4
    //             $datas = $datas->map(function ($item) use ($datas, $request) {
    //                 if ($item['code_5'] == null) {
    //                     $item['saldo_akhir'] = $datas
    //                         ->filter(function ($value, $key) use ($item) {
    //                             return strlen($value['fullcode']) == 12;
    //                         })
    //                         ->where('code_1', $item['code_1'])
    //                         ->where('code_2', $item['code_2'])
    //                         ->where('code_3', $item['code_3'])
    //                         ->where('code_4', $item['code_4'])
    //                         ->where('code_5', '!=', null)
    //                         ->sum('saldo_akhir');

    //                     // $item['saldo_awal'] = $datas
    //                     //     ->filter(function ($value, $key) use ($item) {
    //                     //         return strlen($value['fullcode']) == 12;
    //                     //     })
    //                     //     ->where('code_1', $item['code_1'])
    //                     //     ->where('code_2', $item['code_2'])
    //                     //     ->where('code_3', $item['code_3'])
    //                     //     ->where('code_4', $item['code_4'])
    //                     //     ->where('code_5', '!=', null)
    //                     //     ->sum('saldo_awal');
    //                 }

    //                 // update to acc_report
    //                 $insertToReport = DB::table('acc_report')
    //                     ->where('instance_id', $request->instance)
    //                     ->where('year', $request->year)
    //                     ->where('periode_id', $request->periode)
    //                     ->where('kode_rekening_id', $item['kode_rekening_id'])
    //                     ->where('type', 'lo')
    //                     ->first();
    //                 if ($insertToReport) {
    //                     DB::table('acc_report')
    //                         ->where('instance_id', $request->instance)
    //                         ->where('year', $request->year)
    //                         ->where('periode_id', $request->periode)
    //                         ->where('kode_rekening_id', $item['kode_rekening_id'])
    //                         ->where('type', 'lo')
    //                         ->update([
    //                             'saldo_akhir' => $item['saldo_akhir'],
    //                             'kenaikan_penurunan' => $item['saldo_akhir'] - $item['saldo_awal'],
    //                             'percent' => $item['saldo_awal'] == 0 ? 0 : (($item['saldo_akhir'] - $item['saldo_awal']) / $item['saldo_awal']) * 100,
    //                         ]);
    //                 }
    //                 return $item;
    //             });
    //             // sum level 4 to level 3
    //             $datas = $datas->map(function ($item) use ($datas, $request) {
    //                 if ($item['code_4'] == null) {
    //                     $item['saldo_akhir'] = $datas
    //                         ->filter(function ($value, $key) use ($item) {
    //                             return strlen($value['fullcode']) == 9;
    //                         })
    //                         ->where('code_1', $item['code_1'])
    //                         ->where('code_2', $item['code_2'])
    //                         ->where('code_3', $item['code_3'])
    //                         ->where('code_4', '!=', null)
    //                         ->sum('saldo_akhir');

    //                     // $item['saldo_awal'] = $datas
    //                     //     ->filter(function ($value, $key) use ($item) {
    //                     //         return strlen($value['fullcode']) == 9;
    //                     //     })
    //                     //     ->where('code_1', $item['code_1'])
    //                     //     ->where('code_2', $item['code_2'])
    //                     //     ->where('code_3', $item['code_3'])
    //                     //     ->where('code_4', '!=', null)
    //                     //     ->sum('saldo_awal');
    //                 }

    //                 // update to acc_report
    //                 $insertToReport = DB::table('acc_report')
    //                     ->where('instance_id', $request->instance)
    //                     ->where('year', $request->year)
    //                     ->where('periode_id', $request->periode)
    //                     ->where('kode_rekening_id', $item['kode_rekening_id'])
    //                     ->where('type', 'lo')
    //                     ->first();
    //                 if ($insertToReport) {
    //                     DB::table('acc_report')
    //                         ->where('instance_id', $request->instance)
    //                         ->where('year', $request->year)
    //                         ->where('periode_id', $request->periode)
    //                         ->where('kode_rekening_id', $item['kode_rekening_id'])
    //                         ->where('type', 'lo')
    //                         ->update([
    //                             'saldo_akhir' => $item['saldo_akhir'],
    //                             'kenaikan_penurunan' => $item['saldo_akhir'] - $item['saldo_awal'],
    //                             'percent' => $item['saldo_awal'] == 0 ? 0 : (($item['saldo_akhir'] - $item['saldo_awal']) / $item['saldo_awal']) * 100,
    //                         ]);
    //                 }
    //                 return $item;
    //             });
    //             // sum level 3 to level 2
    //             $datas = $datas->map(function ($item) use ($datas, $request) {
    //                 if ($item['code_3'] == null) {
    //                     $item['saldo_akhir'] = $datas
    //                         ->filter(function ($value, $key) use ($item) {
    //                             return strlen($value['fullcode']) == 6;
    //                         })
    //                         ->where('code_1', $item['code_1'])
    //                         ->where('code_2', $item['code_2'])
    //                         ->where('code_3', '!=', null)
    //                         ->sum('saldo_akhir');

    //                     // $item['saldo_awal'] = $datas
    //                     //     ->filter(function ($value, $key) use ($item) {
    //                     //         return strlen($value['fullcode']) == 6;
    //                     //     })
    //                     //     ->where('code_1', $item['code_1'])
    //                     //     ->where('code_2', $item['code_2'])
    //                     //     ->where('code_3', '!=', null)
    //                     //     ->sum('saldo_awal');
    //                 }

    //                 // update to acc_report
    //                 $insertToReport = DB::table('acc_report')
    //                     ->where('instance_id', $request->instance)
    //                     ->where('year', $request->year)
    //                     ->where('periode_id', $request->periode)
    //                     ->where('kode_rekening_id', $item['kode_rekening_id'])
    //                     ->where('type', 'lo')
    //                     ->first();
    //                 if ($insertToReport) {
    //                     DB::table('acc_report')
    //                         ->where('instance_id', $request->instance)
    //                         ->where('year', $request->year)
    //                         ->where('periode_id', $request->periode)
    //                         ->where('kode_rekening_id', $item['kode_rekening_id'])
    //                         ->where('type', 'lo')
    //                         ->update([
    //                             'saldo_akhir' => $item['saldo_akhir'],
    //                             'kenaikan_penurunan' => $item['saldo_akhir'] - $item['saldo_awal'],
    //                             'percent' => $item['saldo_awal'] == 0 ? 0 : (($item['saldo_akhir'] - $item['saldo_awal']) / $item['saldo_awal']) * 100,
    //                         ]);
    //                 }
    //                 return $item;
    //             });
    //             // sum level 2 to level 1
    //             $datas = $datas->map(function ($item) use ($datas, $request) {
    //                 if ($item['code_2'] == null) {
    //                     $item['saldo_akhir'] = $datas
    //                         ->filter(function ($value, $key) use ($item) {
    //                             return strlen($value['fullcode']) == 3;
    //                         })
    //                         ->where('code_1', $item['code_1'])
    //                         ->where('code_2', '!=', null)
    //                         ->sum('saldo_akhir');

    //                     // $item['saldo_awal'] = $datas
    //                     //     ->filter(function ($value, $key) use ($item) {
    //                     //         return strlen($value['fullcode']) == 3;
    //                     //     })
    //                     //     ->where('code_1', $item['code_1'])
    //                     //     ->where('code_2', '!=', null)
    //                     //     ->sum('saldo_awal');
    //                 }

    //                 // update to acc_report
    //                 $insertToReport = DB::table('acc_report')
    //                     ->where('instance_id', $request->instance)
    //                     ->where('year', $request->year)
    //                     ->where('periode_id', $request->periode)
    //                     ->where('kode_rekening_id', $item['kode_rekening_id'])
    //                     ->where('type', 'lo')
    //                     ->first();
    //                 if ($insertToReport) {
    //                     DB::table('acc_report')
    //                         ->where('instance_id', $request->instance)
    //                         ->where('year', $request->year)
    //                         ->where('periode_id', $request->periode)
    //                         ->where('kode_rekening_id', $item['kode_rekening_id'])
    //                         ->where('type', 'lo')
    //                         ->update([
    //                             'saldo_akhir' => $item['saldo_akhir'],
    //                             'kenaikan_penurunan' => $item['saldo_akhir'] - $item['saldo_awal'],
    //                             'percent' => $item['saldo_awal'] == 0 ? 0 : (($item['saldo_akhir'] - $item['saldo_awal']) / $item['saldo_awal']) * 100,
    //                         ]);
    //                 }
    //                 return $item;
    //             });
    //             // SUMMARIES END

    //             // Cache datas for 5 minutes
    //             Cache::put('report_lo_' . $request->instance . '_' . $request->year . '_' . $request->periode, $datas, now()->addMinutes(3));

    //             DB::commit();
    //             return $this->successResponse($datas);
    //         }
    //     } catch (\Exception $e) {
    //         DB::rollBack();
    //         return $this->errorResponse($e->getMessage() . ' - ' . $e->getLine());
    //     }
    // }

}
