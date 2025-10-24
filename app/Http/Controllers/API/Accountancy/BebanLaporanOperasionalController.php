<?php

namespace App\Http\Controllers\API\Accountancy;

use App\Traits\JsonReturner;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Validator;

class BebanLaporanOperasionalController extends Controller
{
    use JsonReturner;

    function calculateData(Request $request)
    {
        $validate = Validator::make($request->all(), [
            'year' => 'nullable|integer',
            'periode' => 'required|exists:ref_periode,id',
            'type' => 'required|string|in:pegawai,persediaan,jasa,pemeliharaan,perjadin,uang-jasa,hibah,subsidi',
        ], [], [
            'periode' => 'Periode',
            'year' => 'Tahun',
            'type' => 'Tipe'
        ]);
        if ($validate->fails()) {
            return $this->validationResponse($validate->errors());
        }

        $arrInstances = DB::table('instances')
            ->where('status', 'active')
            ->pluck('id');

        foreach ($arrInstances as $instance) {
            if ($request->type == 'pegawai') {
                $this->getPegawai($request->merge([
                    'instance' => $instance,
                    'year' => $request->year,
                    'periode' => $request->periode
                ]));
            } else if ($request->type == 'persediaan') {
                $this->getPersediaan($request->merge([
                    'instance' => $instance,
                    'year' => $request->year,
                    'periode' => $request->periode
                ]));
            } else if ($request->type == 'jasa') {
                $this->getJasa($request->merge([
                    'instance' => $instance,
                    'year' => $request->year,
                    'periode' => $request->periode
                ]));
            } else if ($request->type == 'pemeliharaan') {
                $this->getPemeliharaan($request->merge([
                    'instance' => $instance,
                    'year' => $request->year,
                    'periode' => $request->periode
                ]));
            } else if ($request->type == 'perjadin') {
                $this->getPerjadin($request->merge([
                    'instance' => $instance,
                    'year' => $request->year,
                    'periode' => $request->periode
                ]));
            } else if ($request->type == 'uang-jasa') {
                $this->getUangJasaDiserahkan($request->merge([
                    'instance' => $instance,
                    'year' => $request->year,
                    'periode' => $request->periode
                ]));
            } else if ($request->type == 'hibah') {
                $this->getHibah($request->merge([
                    'instance' => $instance,
                    'year' => $request->year,
                    'periode' => $request->periode
                ]));
            } else if ($request->type == 'subsidi') {
                $this->getSubsidi($request->merge([
                    'instance' => $instance,
                    'year' => $request->year,
                    'periode' => $request->periode
                ]));
            }
        }
        return $this->successResponse([], 'Data berhasil dihitung');
    }

    // Pegawai Start
    function getPegawai(Request $request)
    {
        $validate = Validator::make($request->all(), [
            'instance' => 'nullable|exists:instances,id',
            'year' => 'nullable|integer',
            'periode' => 'required|exists:ref_periode,id'
        ], [], [
            'instance' => 'Instance ID',
            'periode' => 'Periode'
        ]);
        if ($validate->fails()) {
            return $this->validationResponse($validate->errors());
        }

        try {
            $datas = [];
            if (!$request->instance) {
                $arrData = DB::table('acc_blo_pegawai')
                    ->where('periode_id', $request->periode)
                    ->where('year', $request->year)
                    ->pluck('kode_rekening_id');
                $arrData = collect($arrData)->unique()->values();

                $arrRekenings = DB::table('ref_kode_rekening_complete')
                    ->whereIn('id', $arrData)
                    ->orderBy('fullcode')
                    ->get();
                foreach ($arrRekenings as $rek) {
                    $data = DB::table('acc_blo_pegawai')
                        ->where('periode_id', $request->periode)
                        ->where('year', $request->year)
                        ->where('kode_rekening_id', $rek->id)
                        ->get();
                    $plusTotal = ($data->sum('saldo_awal') ?? 0) + ($data->sum('belanja_dibayar_dimuka_akhir') ?? 0) + ($data->sum('hutang') ?? 0) + ($data->sum('hibah') ?? 0) + ($data->sum('reklas_tambah_dari_rekening') ?? 0) + ($data->sum('reklas_tambah_dari_modal') ?? 0) + ($data->sum('plus_jukor') ?? 0);
                    $minTotal = ($data->sum('saldo_akhir') ?? 0) + ($data->sum('beban_tahun_lalu') ?? 0) + ($data->sum('belanja_dibayar_dimuka_awal') ?? 0) + ($data->sum('pembayaran_hutang') ?? 0) + ($data->sum('reklas_kurang_ke_rekening') ?? 0) + ($data->sum('reklas_kurang_ke_aset') ?? 0) + ($data->sum('atribusi') ?? 0) + ($data->sum('min_jukor') ?? 0);

                    $bebanLo = ($data->sum('realisasi_belanja') + $plusTotal) - $minTotal;
                    $datas[] = [
                        'id' => null,
                        'periode_id' => $request->periode,
                        'year' => $request->year,
                        'instance_id' => null,
                        'kode_rekening_id' => $rek->id,
                        'kode_rekening_fullcode' => $rek->fullcode,
                        'kode_rekening_name' => $rek->name,
                        'realisasi_belanja' => $data->sum('realisasi_belanja'),
                        'saldo_awal' => $data->sum('saldo_awal'),
                        'belanja_dibayar_dimuka_akhir' => $data->sum('belanja_dibayar_dimuka_akhir'),
                        'hutang' => $data->sum('hutang'),
                        'hibah' => $data->sum('hibah'),
                        'reklas_tambah_dari_rekening' => $data->sum('reklas_tambah_dari_rekening'),
                        'reklas_tambah_dari_modal' => $data->sum('reklas_tambah_dari_modal'),
                        'plus_jukor' => $data->sum('plus_jukor'),
                        'plus_total' => $plusTotal ?? 0,
                        'saldo_akhir' => $data->sum('saldo_akhir'),
                        'beban_tahun_lalu' => $data->sum('beban_tahun_lalu'),
                        'belanja_dibayar_dimuka_awal' => $data->sum('belanja_dibayar_dimuka_awal'),
                        'pembayaran_hutang' => $data->sum('pembayaran_hutang'),
                        'reklas_kurang_ke_rekening' => $data->sum('reklas_kurang_ke_rekening'),
                        'reklas_kurang_ke_aset' => $data->sum('reklas_kurang_ke_aset'),
                        'atribusi' => $data->sum('atribusi'),
                        'min_jukor' => $data->sum('min_jukor'),
                        'beban_lo' => $bebanLo ?? 0,
                        'kode_rekening' => $rek->fullcode,
                        'min_total' => $minTotal ?? 0,
                    ];
                }
            } elseif ($request->instance) {
                $arrLra = DB::table('acc_lra')
                    ->where('periode_id', $request->periode)
                    ->where('year', $request->year ?? date('Y'))
                    ->where('instance_id', $request->instance)
                    ->where('kode_rekening', 'ILIKE', '5.1.01.%')
                    ->whereRaw('LENGTH(kode_rekening) >= ?', [17])
                    ->orderBy('kode_rekening')
                    ->get();
                foreach ($arrLra as $lra) {
                    $kodeRekeningId = DB::table('ref_kode_rekening_complete')
                        ->where('fullcode', $lra->kode_rekening)
                        ->first()->id;
                    $data = DB::table('acc_blo_pegawai')
                        ->where('periode_id', $request->periode)
                        ->where('year', $request->year ?? date('Y'))
                        ->where('instance_id', $request->instance)
                        ->where('kode_rekening_id', $kodeRekeningId)
                        ->first();
                    if (!$data) {
                        $HutangBaru = DB::table('acc_htb_hutang_baru')
                            ->where('periode_id', $request->periode)
                            ->where('year', $request->year)
                            ->where('instance_id', $request->instance)
                            ->where('kode_rekening_id', $kodeRekeningId)
                            ->get()->sum('pegawai') ?? 0;
                        $PembayaranHutang = DB::table('acc_htb_pembayaran_hutang')
                            ->where('periode_id', $request->periode)
                            ->where('year', $request->year)
                            ->where('instance_id', $request->instance)
                            ->where('kode_rekening_id', $kodeRekeningId)
                            ->get()->sum('pegawai') ?? 0;

                        DB::table('acc_blo_pegawai')
                            ->insert([
                                'periode_id' => $request->periode,
                                'year' => $request->year ?? date('Y'),
                                'instance_id' => $request->instance,
                                'kode_rekening_id' => $kodeRekeningId,
                                'realisasi_belanja' => $lra->realisasi,
                                'saldo_awal' => 0,
                                'belanja_dibayar_dimuka_akhir' => 0,
                                'hutang' => $HutangBaru ?? 0,
                                'hibah' => 0,
                                'reklas_tambah_dari_rekening' => 0,
                                'reklas_tambah_dari_modal' => 0,
                                'plus_jukor' => 0,
                                'saldo_akhir' => 0,
                                'beban_tahun_lalu' => 0,
                                'belanja_dibayar_dimuka_awal' => 0,
                                'pembayaran_hutang' => $PembayaranHutang ?? 0,
                                'reklas_kurang_ke_rekening' => 0,
                                'reklas_kurang_ke_aset' => 0,
                                'atribusi' => 0,
                                'min_jukor' => 0,
                                'beban_lo' => 0,
                            ]);
                    }
                    $data = DB::table('acc_blo_pegawai')
                        ->where('periode_id', $request->periode)
                        ->where('year', $request->year ?? date('Y'))
                        ->where('instance_id', $request->instance)
                        ->where('kode_rekening_id', $kodeRekeningId)
                        ->first();
                    if ($data) {
                        $HutangBaru = DB::table('acc_htb_hutang_baru')
                            ->where('periode_id', $data->periode_id)
                            ->where('year', $data->year)
                            ->where('instance_id', $data->instance_id)
                            ->where('kode_rekening_id', $data->kode_rekening_id)
                            ->get()->sum('pegawai') ?? 0;
                        $data->hutang = $HutangBaru;
                        $PembayaranHutang = DB::table('acc_htb_pembayaran_hutang')
                            ->where('periode_id', $data->periode_id)
                            ->where('year', $data->year)
                            ->where('instance_id', $data->instance_id)
                            ->where('kode_rekening_id', $data->kode_rekening_id)
                            ->get()->sum('pegawai') ?? 0;
                        $data->pembayaran_hutang = $PembayaranHutang;

                        $plusTotalKeys = ['saldo_awal', 'belanja_dibayar_dimuka_akhir', 'hutang', 'hibah', 'reklas_tambah_dari_rekening', 'reklas_tambah_dari_modal', 'plus_jukor'];
                        $plusTotal = 0;
                        foreach ($plusTotalKeys as $key) {
                            $plusTotal += floatval($data->$key);
                        }
                        $minTotalKeys = ['saldo_akhir', 'beban_tahun_lalu', 'belanja_dibayar_dimuka_awal', 'pembayaran_hutang', 'reklas_kurang_ke_rekening', 'reklas_kurang_ke_aset', 'atribusi', 'min_jukor'];
                        $minTotal = 0;
                        foreach ($minTotalKeys as $key) {
                            $minTotal += floatval($data->$key);
                        }
                        $bebanLo = ($data->realisasi_belanja + $plusTotal) - $minTotal;

                        DB::table('acc_blo_pegawai')
                            ->where('id', $data->id)
                            ->update([
                                'realisasi_belanja' => $lra->realisasi,
                                'hutang' => $HutangBaru,
                                'pembayaran_hutang' => $PembayaranHutang,
                                'beban_lo' => $bebanLo
                            ]);

                        $instance = DB::table('instances')
                            ->where('id', $request->instance)
                            ->first();
                        $kodeRekening = DB::table('ref_kode_rekening_complete')
                            ->where('id', $data->kode_rekening_id)
                            ->first();
                        $datas[] = [
                            'id' => $data->id,
                            'periode_id' => $data->periode_id,
                            'year' => $data->year,
                            'instance_id' => $data->instance_id,
                            'instance_name' => $instance->name,
                            'kode_rekening_id' => $data->kode_rekening_id,
                            'kode_rekening_fullcode' => $kodeRekening->fullcode,
                            'kode_rekening_name' => $kodeRekening->name,
                            'realisasi_belanja' => $data->realisasi_belanja,
                            'saldo_awal' => $data->saldo_awal,
                            'belanja_dibayar_dimuka_akhir' => $data->belanja_dibayar_dimuka_akhir,
                            'hutang' => $hutangPegawai ?? $data->hutang,
                            'hibah' => $data->hibah,
                            'reklas_tambah_dari_rekening' => $data->reklas_tambah_dari_rekening,
                            'reklas_tambah_dari_modal' => $data->reklas_tambah_dari_modal,
                            'plus_jukor' => $data->plus_jukor,
                            'plus_total' => $plusTotal ?? 0,
                            'saldo_akhir' => $data->saldo_akhir,
                            'beban_tahun_lalu' => $data->beban_tahun_lalu,
                            'belanja_dibayar_dimuka_awal' => $data->belanja_dibayar_dimuka_awal,
                            'pembayaran_hutang' => $data->pembayaran_hutang,
                            'reklas_kurang_ke_rekening' => $data->reklas_kurang_ke_rekening,
                            'reklas_kurang_ke_aset' => $data->reklas_kurang_ke_aset,
                            'atribusi' => $data->atribusi,
                            'min_jukor' => $data->min_jukor,
                            'beban_lo' => $bebanLo ?? 0,
                            'kode_rekening' => $lra->kode_rekening,
                            'min_total' => $minTotal ?? 0,
                        ];
                    }
                }
            }

            return $this->successResponse($datas, 'Pemeliharaan berhasil diambil');
        } catch (\Exception $e) {
            return $this->errorResponse($e->getMessage() . ' ' . $e);
        }
    }

    function storePegawai(Request $request)
    {
        $validate = Validator::make($request->all(), [
            'periode' => 'required|exists:ref_periode,id',
            'year' => 'nullable|numeric',
            'data.*.instance_id' => 'required|exists:instances,id',
            'data.*.kode_rekening_id' => 'required|exists:ref_kode_rekening_complete,id',
        ], [], [
            'periode' => 'Periode',
            'data.*.instance_id' => 'Instance ID',
            'data.*.kode_rekening_id' => 'Kode Rekening ID',
        ]);
        if ($validate->fails()) {
            return $this->validationResponse($validate->errors());
        }

        DB::beginTransaction();
        try {
            $dateYear = $request->year ?? date('Y');
            $datas = $request->data;
            foreach ($datas as $input) {
                if ($input['id']) {
                    DB::table('acc_blo_pegawai')
                        ->where('id', $input['id'])
                        ->update([
                            'realisasi_belanja' => $input['realisasi_belanja'],
                            'saldo_awal' => $input['saldo_awal'],
                            'belanja_dibayar_dimuka_akhir' => $input['belanja_dibayar_dimuka_akhir'],
                            'hutang' => $input['hutang'],
                            'hibah' => $input['hibah'],
                            'reklas_tambah_dari_rekening' => $input['reklas_tambah_dari_rekening'],
                            'reklas_tambah_dari_modal' => $input['reklas_tambah_dari_modal'],
                            'plus_jukor' => $input['plus_jukor'],
                            'saldo_akhir' => $input['saldo_akhir'],
                            'beban_tahun_lalu' => $input['beban_tahun_lalu'],
                            'belanja_dibayar_dimuka_awal' => $input['belanja_dibayar_dimuka_awal'],
                            'pembayaran_hutang' => $input['pembayaran_hutang'],
                            'reklas_kurang_ke_rekening' => $input['reklas_kurang_ke_rekening'],
                            'reklas_kurang_ke_aset' => $input['reklas_kurang_ke_aset'],
                            'atribusi' => $input['atribusi'],
                            'min_jukor' => $input['min_jukor'],
                            'beban_lo' => $input['beban_lo'],
                        ]);
                } else {
                    DB::table('acc_blo_pegawai')->insert([
                        'periode_id' => $request->periode,
                        'year' => $dateYear,
                        'instance_id' => $input['instance_id'],
                        'kode_rekening_id' => $input['kode_rekening_id'],
                        'realisasi_belanja' => $input['realisasi_belanja'],
                        'saldo_awal' => $input['saldo_awal'],
                        'belanja_dibayar_dimuka_akhir' => $input['belanja_dibayar_dimuka_akhir'],
                        'hutang' => $input['hutang'],
                        'hibah' => $input['hibah'],
                        'reklas_tambah_dari_rekening' => $input['reklas_tambah_dari_rekening'],
                        'reklas_tambah_dari_modal' => $input['reklas_tambah_dari_modal'],
                        'plus_jukor' => $input['plus_jukor'],
                        'saldo_akhir' => $input['saldo_akhir'],
                        'beban_tahun_lalu' => $input['beban_tahun_lalu'],
                        'belanja_dibayar_dimuka_awal' => $input['belanja_dibayar_dimuka_awal'],
                        'pembayaran_hutang' => $input['pembayaran_hutang'],
                        'reklas_kurang_ke_rekening' => $input['reklas_kurang_ke_rekening'],
                        'reklas_kurang_ke_aset' => $input['reklas_kurang_ke_aset'],
                        'atribusi' => $input['atribusi'],
                        'min_jukor' => $input['min_jukor'],
                        'beban_lo' => $input['beban_lo'],
                    ]);
                }
            }

            DB::commit();
            return $this->successResponse([], 'Pemeliharaan berhasil disimpan');
        } catch (\Exception $e) {
            DB::rollBack();
            return $this->errorResponse($e->getMessage() . ' ' . $e->getLine());
        }
    }

    function deletePegawai(Request $request)
    {
        $validate = Validator::make($request->all(), [
            'id' => 'required|exists:acc_blo_pegawai,id'
        ], [], [
            'id' => 'ID'
        ]);
        if ($validate->fails()) {
            return $this->validationResponse($validate->errors());
        }

        DB::beginTransaction();
        try {
            DB::table('acc_blo_pegawai')
                ->where('id', $request->id)
                ->delete();

            DB::commit();
            return $this->successResponse([], 'Pemeliharaan berhasil dihapus');
        } catch (\Exception $e) {
            DB::rollBack();
            return $this->errorResponse($e->getMessage() . ' ' . $e->getLine());
        }
    }
    // Pegawai End

    // Persediaan Start
    function getPersediaan(Request $request)
    {
        $validate = Validator::make($request->all(), [
            'instance' => 'nullable|exists:instances,id',
            'year' => 'nullable|integer',
            'periode' => 'required|exists:ref_periode,id'
        ], [], [
            'instance' => 'Instance ID',
            'periode' => 'Periode'
        ]);
        if ($validate->fails()) {
            return $this->validationResponse($validate->errors());
        }

        try {
            $datas = [];
            if (!$request->instance) {
                $arrData = DB::table('acc_blo_persediaan')
                    ->where('periode_id', $request->periode)
                    ->where('year', $request->year)
                    ->pluck('kode_rekening_id');
                $arrData = collect($arrData)->unique()->values();

                $arrRekenings = DB::table('ref_kode_rekening_complete')
                    ->whereIn('id', $arrData)
                    ->orderBy('fullcode')
                    ->get();
                foreach ($arrRekenings as $rek) {
                    $data = DB::table('acc_blo_persediaan')
                        ->where('periode_id', $request->periode)
                        ->where('year', $request->year)
                        ->where('kode_rekening_id', $rek->id)
                        ->get();
                    $plusTotal = ($data->sum('saldo_awal') ?? 0) + ($data->sum('belanja_dibayar_dimuka_akhir') ?? 0) + ($data->sum('hutang') ?? 0) + ($data->sum('hibah') ?? 0) + ($data->sum('reklas_tambah_dari_rekening') ?? 0) + ($data->sum('reklas_tambah_dari_modal') ?? 0) + ($data->sum('plus_jukor') ?? 0);
                    $minTotal = ($data->sum('saldo_akhir') ?? 0) + ($data->sum('beban_tahun_lalu') ?? 0) + ($data->sum('belanja_dibayar_dimuka_awal') ?? 0) + ($data->sum('pembayaran_hutang') ?? 0) + ($data->sum('reklas_kurang_ke_rekening') ?? 0) + ($data->sum('reklas_kurang_ke_aset') ?? 0) + ($data->sum('atribusi') ?? 0) + ($data->sum('min_jukor') ?? 0);

                    $bebanLo = ($data->sum('realisasi_belanja') + $plusTotal) - $minTotal;
                    $datas[] = [
                        'editable' => false,
                        'id' => null,
                        'periode_id' => $request->periode,
                        'year' => $request->year,
                        'instance_id' => null,
                        'kode_rekening_id' => $rek->id,
                        'kode_rekening_fullcode' => $rek->fullcode,
                        'kode_rekening_name' => $rek->name,
                        'realisasi_belanja' => $data->sum('realisasi_belanja'),
                        'saldo_awal' => $data->sum('saldo_awal'),
                        'belanja_dibayar_dimuka_akhir' => $data->sum('belanja_dibayar_dimuka_akhir'),
                        'hutang' => $data->sum('hutang'),
                        'hibah' => $data->sum('hibah'),
                        'reklas_tambah_dari_rekening' => $data->sum('reklas_tambah_dari_rekening'),
                        'reklas_tambah_dari_modal' => $data->sum('reklas_tambah_dari_modal'),
                        'plus_jukor' => $data->sum('plus_jukor'),
                        'plus_total' => $plusTotal ?? 0,
                        'saldo_akhir' => $data->sum('saldo_akhir'),
                        'beban_tahun_lalu' => $data->sum('beban_tahun_lalu'),
                        'belanja_dibayar_dimuka_awal' => $data->sum('belanja_dibayar_dimuka_awal'),
                        'pembayaran_hutang' => $data->sum('pembayaran_hutang'),
                        'reklas_kurang_ke_rekening' => $data->sum('reklas_kurang_ke_rekening'),
                        'reklas_kurang_ke_aset' => $data->sum('reklas_kurang_ke_aset'),
                        'atribusi' => $data->sum('atribusi'),
                        'min_jukor' => $data->sum('min_jukor'),
                        'beban_lo' => $bebanLo ?? 0,
                        'kode_rekening' => $rek->fullcode,
                        'min_total' => $minTotal ?? 0,
                    ];
                }
            } elseif ($request->instance) {
                $arrLra = DB::table('acc_lra')
                    ->where('periode_id', $request->periode)
                    ->where('year', $request->year ?? date('Y'))
                    ->where('instance_id', $request->instance)
                    ->where('kode_rekening', 'ILIKE', '5.1.02.01.%')
                    ->whereRaw('LENGTH(kode_rekening) >= ?', [17])
                    ->orderBy('kode_rekening')
                    ->get();
                $arrLra88 = DB::table('acc_lra')
                    ->where('periode_id', $request->periode)
                    ->where('year', $request->year ?? date('Y'))
                    ->where('instance_id', $request->instance)
                    ->where('kode_rekening', 'ILIKE', '5.1.02.88.%')
                    ->whereRaw('LENGTH(kode_rekening) >= ?', [17])
                    ->orderBy('kode_rekening')
                    ->get();
                $arrLra90 = DB::table('acc_lra')
                    ->where('periode_id', $request->periode)
                    ->where('year', $request->year ?? date('Y'))
                    ->where('instance_id', $request->instance)
                    ->where('kode_rekening', 'ILIKE', '5.1.02.90.%')
                    ->whereRaw('LENGTH(kode_rekening) >= ?', [17])
                    ->orderBy('kode_rekening')
                    ->get();
                $arrLra99 = DB::table('acc_lra')
                    ->where('periode_id', $request->periode)
                    ->where('year', $request->year ?? date('Y'))
                    ->where('instance_id', $request->instance)
                    ->where('kode_rekening', 'ILIKE', '5.1.02.99.%')
                    ->whereRaw('LENGTH(kode_rekening) >= ?', [17])
                    ->orderBy('kode_rekening')
                    ->get();

                // merge all data
                $arrLra = $arrLra->merge($arrLra88);
                $arrLra = $arrLra->merge($arrLra90);
                $arrLra = $arrLra->merge($arrLra99);

                foreach ($arrLra as $lra) {
                    $kodeRekeningId = DB::table('ref_kode_rekening_complete')
                        ->where('fullcode', $lra->kode_rekening)
                        ->first()->id ?? null;
                    $data = DB::table('acc_blo_persediaan')
                        ->where('periode_id', $request->periode)
                        ->where('year', $request->year ?? date('Y'))
                        ->where('instance_id', $request->instance)
                        ->where('kode_rekening_id', $kodeRekeningId)
                        ->first();
                    if (!$data && $kodeRekeningId) {
                        $HutangBaru = DB::table('acc_htb_hutang_baru')
                            ->where('periode_id', $request->periode)
                            ->where('year', $request->year)
                            ->where('instance_id', $request->instance)
                            ->where('kode_rekening_id', $kodeRekeningId)
                            ->get()->sum('persediaan') ?? 0;
                        $PembayaranHutang = DB::table('acc_htb_pembayaran_hutang')
                            ->where('periode_id', $request->periode)
                            ->where('year', $request->year)
                            ->where('instance_id', $request->instance)
                            ->where('kode_rekening_id', $kodeRekeningId)
                            ->get()->sum('persediaan') ?? 0;

                        $PersediaanHabisPakai = DB::table('acc_persediaan_barang_habis_pakai')
                            ->where('periode_id', $request->periode)
                            ->where('year', $request->year)
                            ->where('instance_id', $request->instance)
                            ->where('kode_rekening_id', $kodeRekeningId)
                            ->get();
                        $PersediaanUntukDijual = DB::table('acc_persediaan_belanja_persediaan_untuk_dijual')
                            ->where('periode_id', $request->periode)
                            ->where('year', $request->year)
                            ->where('instance_id', $request->instance)
                            ->where('kode_rekening_id', $kodeRekeningId)
                            ->get();
                        $saldoAwal = ($PersediaanHabisPakai->sum('saldo_awal') ?? 0) + ($PersediaanUntukDijual->sum('saldo_awal') ?? 0);
                        $saldoAkhir = ($PersediaanHabisPakai->sum('saldo_akhir') ?? 0) + ($PersediaanUntukDijual->sum('saldo_akhir') ?? 0);

                        DB::table('acc_blo_persediaan')
                            ->insert([
                                'periode_id' => $request->periode,
                                'year' => $request->year ?? date('Y'),
                                'instance_id' => $request->instance,
                                'kode_rekening_id' => $kodeRekeningId,
                                'realisasi_belanja' => $lra->realisasi,
                                'saldo_awal' => $saldoAwal ?? 0,
                                'belanja_dibayar_dimuka_akhir' => 0,
                                'hutang' => $HutangBaru ?? 0,
                                'hibah' => 0,
                                'reklas_tambah_dari_rekening' => 0,
                                'reklas_tambah_dari_modal' => 0,
                                'plus_jukor' => 0,
                                'saldo_akhir' => $saldoAkhir ?? 0,
                                'beban_tahun_lalu' => 0,
                                'belanja_dibayar_dimuka_awal' => 0,
                                'pembayaran_hutang' => $PembayaranHutang ?? 0,
                                'reklas_kurang_ke_rekening' => 0,
                                'reklas_kurang_ke_aset' => 0,
                                'atribusi' => 0,
                                'min_jukor' => 0,
                                'beban_lo' => 0,
                            ]);
                    }
                    if ($data) {
                        $HutangBaru = DB::table('acc_htb_hutang_baru')
                            ->where('periode_id', $data->periode_id)
                            ->where('year', $data->year)
                            ->where('instance_id', $data->instance_id)
                            ->where('kode_rekening_id', $data->kode_rekening_id)
                            ->get()->sum('persediaan') ?? 0;
                        $data->hutang = $HutangBaru;
                        $PembayaranHutang = DB::table('acc_htb_pembayaran_hutang')
                            ->where('periode_id', $data->periode_id)
                            ->where('year', $data->year)
                            ->where('instance_id', $data->instance_id)
                            ->where('kode_rekening_id', $data->kode_rekening_id)
                            ->get()->sum('persediaan') ?? 0;
                        $data->pembayaran_hutang = $PembayaranHutang;

                        $PersediaanHabisPakai = DB::table('acc_persediaan_barang_habis_pakai')
                            ->where('periode_id', $data->periode_id)
                            ->where('year', $data->year)
                            ->where('instance_id', $data->instance_id)
                            ->where('kode_rekening_id', $data->kode_rekening_id)
                            ->get();
                        $PersediaanUntukDijual = DB::table('acc_persediaan_belanja_persediaan_untuk_dijual')
                            ->where('periode_id', $data->periode_id)
                            ->where('year', $data->year)
                            ->where('instance_id', $data->instance_id)
                            ->where('kode_rekening_id', $data->kode_rekening_id)
                            ->get();
                        $saldoAwal = ($PersediaanHabisPakai->sum('saldo_awal') ?? 0) + ($PersediaanUntukDijual->sum('saldo_awal') ?? 0);
                        $saldoAkhir = ($PersediaanHabisPakai->sum('saldo_akhir') ?? 0) + ($PersediaanUntukDijual->sum('saldo_akhir') ?? 0);

                        $data->saldo_awal = $saldoAwal;
                        $data->saldo_akhir = $saldoAkhir;

                        $plusTotalKeys = ['saldo_awal', 'belanja_dibayar_dimuka_akhir', 'hutang', 'hibah', 'reklas_tambah_dari_rekening', 'reklas_tambah_dari_modal', 'plus_jukor'];
                        $plusTotal = 0;
                        foreach ($plusTotalKeys as $key) {
                            $plusTotal += floatval($data->$key);
                        }
                        $minTotalKeys = ['saldo_akhir', 'beban_tahun_lalu', 'belanja_dibayar_dimuka_awal', 'pembayaran_hutang', 'reklas_kurang_ke_rekening', 'reklas_kurang_ke_aset', 'atribusi', 'min_jukor'];
                        $minTotal = 0;
                        foreach ($minTotalKeys as $key) {
                            $minTotal += floatval($data->$key);
                        }
                        $bebanLo = ($data->realisasi_belanja + $plusTotal) - $minTotal;

                        $editable = false;
                        if ($lra->kode_rekening == '5.1.02.88.88.8888' || $lra->kode_rekening == '5.1.02.90.01.0001' || $lra->kode_rekening == '5.1.02.99.99.9999') {
                            $editable = true;
                        }
                        $realisasiBelanja = $data->realisasi_belanja;
                        if ($editable == false) {
                            $realisasiBelanja = $lra->realisasi;
                        }

                        DB::table('acc_blo_persediaan')
                            ->where('id', $data->id)
                            ->update([
                                'realisasi_belanja' => $realisasiBelanja,
                                'hutang' => $HutangBaru,
                                'pembayaran_hutang' => $PembayaranHutang,
                                'beban_lo' => $bebanLo,
                                'saldo_awal' => $saldoAwal,
                                'saldo_akhir' => $saldoAkhir
                            ]);
                        $instance = DB::table('instances')
                            ->where('id', $request->instance)
                            ->first();
                        $kodeRekening = DB::table('ref_kode_rekening_complete')
                            ->where('id', $data->kode_rekening_id)
                            ->first();
                        $datas[] = [
                            'editable' => $editable ?? false,
                            'id' => $data->id,
                            'periode_id' => $data->periode_id,
                            'year' => $data->year,
                            'instance_id' => $data->instance_id,
                            'instance_name' => $instance->name,
                            'kode_rekening_id' => $data->kode_rekening_id,
                            'kode_rekening_fullcode' => $kodeRekening->fullcode,
                            'kode_rekening_name' => $kodeRekening->name,
                            'realisasi_belanja' => $data->realisasi_belanja,
                            'saldo_awal' => $saldoAwal ?? $data->saldo_awal,
                            'belanja_dibayar_dimuka_akhir' => $data->belanja_dibayar_dimuka_akhir,
                            'hutang' => $HutangBaru ?? $data->hutang,
                            'hibah' => $data->hibah,
                            'reklas_tambah_dari_rekening' => $data->reklas_tambah_dari_rekening,
                            'reklas_tambah_dari_modal' => $data->reklas_tambah_dari_modal,
                            'plus_jukor' => $data->plus_jukor,
                            'plus_total' => $plusTotal ?? 0,
                            'saldo_akhir' => $saldoAkhir ?? $data->saldo_akhir,
                            'beban_tahun_lalu' => $data->beban_tahun_lalu,
                            'belanja_dibayar_dimuka_awal' => $data->belanja_dibayar_dimuka_awal,
                            'pembayaran_hutang' => $data->pembayaran_hutang,
                            'reklas_kurang_ke_rekening' => $data->reklas_kurang_ke_rekening,
                            'reklas_kurang_ke_aset' => $data->reklas_kurang_ke_aset,
                            'atribusi' => $data->atribusi,
                            'min_jukor' => $data->min_jukor,
                            'beban_lo' => $bebanLo ?? 0,
                            'kode_rekening' => $lra->kode_rekening,
                            'min_total' => $minTotal ?? 0,
                        ];
                    }
                }
            }

            return $this->successResponse($datas, 'Pemeliharaan berhasil diambil');
        } catch (\Exception $e) {
            return $this->errorResponse($e->getMessage() . ' ' . $e->getLine());
        }
    }

    function storePersediaan(Request $request)
    {
        $validate = Validator::make($request->all(), [
            'periode' => 'required|exists:ref_periode,id',
            'year' => 'nullable|numeric',
            'data.*.instance_id' => 'required|exists:instances,id',
            'data.*.kode_rekening_id' => 'required|exists:ref_kode_rekening_complete,id',
        ], [], [
            'periode' => 'Periode',
            'data.*.instance_id' => 'Instance ID',
            'data.*.kode_rekening_id' => 'Kode Rekening ID',
        ]);
        if ($validate->fails()) {
            return $this->validationResponse($validate->errors());
        }

        DB::beginTransaction();
        try {
            $dateYear = $request->year ?? date('Y');
            $datas = $request->data;
            foreach ($datas as $input) {
                if ($input['id']) {
                    DB::table('acc_blo_persediaan')
                        ->where('id', $input['id'])
                        ->update([
                            'realisasi_belanja' => $input['realisasi_belanja'],
                            'saldo_awal' => $input['saldo_awal'],
                            'belanja_dibayar_dimuka_akhir' => $input['belanja_dibayar_dimuka_akhir'],
                            'hutang' => $input['hutang'],
                            'hibah' => $input['hibah'],
                            'reklas_tambah_dari_rekening' => $input['reklas_tambah_dari_rekening'],
                            'reklas_tambah_dari_modal' => $input['reklas_tambah_dari_modal'],
                            'plus_jukor' => $input['plus_jukor'],
                            'saldo_akhir' => $input['saldo_akhir'],
                            'beban_tahun_lalu' => $input['beban_tahun_lalu'],
                            'belanja_dibayar_dimuka_awal' => $input['belanja_dibayar_dimuka_awal'],
                            'pembayaran_hutang' => $input['pembayaran_hutang'],
                            'reklas_kurang_ke_rekening' => $input['reklas_kurang_ke_rekening'],
                            'reklas_kurang_ke_aset' => $input['reklas_kurang_ke_aset'],
                            'atribusi' => $input['atribusi'],
                            'min_jukor' => $input['min_jukor'],
                            'beban_lo' => $input['beban_lo'],
                        ]);
                } else {
                    DB::table('acc_blo_persediaan')->insert([
                        'periode_id' => $request->periode,
                        'year' => $dateYear,
                        'instance_id' => $input['instance_id'],
                        'kode_rekening_id' => $input['kode_rekening_id'],
                        'realisasi_belanja' => $input['realisasi_belanja'],
                        'saldo_awal' => $input['saldo_awal'],
                        'belanja_dibayar_dimuka_akhir' => $input['belanja_dibayar_dimuka_akhir'],
                        'hutang' => $input['hutang'],
                        'hibah' => $input['hibah'],
                        'reklas_tambah_dari_rekening' => $input['reklas_tambah_dari_rekening'],
                        'reklas_tambah_dari_modal' => $input['reklas_tambah_dari_modal'],
                        'plus_jukor' => $input['plus_jukor'],
                        'saldo_akhir' => $input['saldo_akhir'],
                        'beban_tahun_lalu' => $input['beban_tahun_lalu'],
                        'belanja_dibayar_dimuka_awal' => $input['belanja_dibayar_dimuka_awal'],
                        'pembayaran_hutang' => $input['pembayaran_hutang'],
                        'reklas_kurang_ke_rekening' => $input['reklas_kurang_ke_rekening'],
                        'reklas_kurang_ke_aset' => $input['reklas_kurang_ke_aset'],
                        'atribusi' => $input['atribusi'],
                        'min_jukor' => $input['min_jukor'],
                        'beban_lo' => $input['beban_lo'],
                    ]);
                }
            }

            DB::commit();
            return $this->successResponse([], 'Pemeliharaan berhasil disimpan');
        } catch (\Exception $e) {
            DB::rollBack();
            return $this->errorResponse($e->getMessage() . ' ' . $e->getLine());
        }
    }

    function deletePersediaan(Request $request)
    {
        $validate = Validator::make($request->all(), [
            'id' => 'required|exists:acc_blo_persediaan,id'
        ], [], [
            'id' => 'ID'
        ]);
        if ($validate->fails()) {
            return $this->validationResponse($validate->errors());
        }

        DB::beginTransaction();
        try {
            DB::table('acc_blo_persediaan')
                ->where('id', $request->id)
                ->delete();

            DB::commit();
            return $this->successResponse([], 'Pemeliharaan berhasil dihapus');
        } catch (\Exception $e) {
            DB::rollBack();
            return $this->errorResponse($e->getMessage() . ' ' . $e->getLine());
        }
    }
    // Persediaan End

    // Jasa Start
    function getJasa(Request $request)
    {
        $validate = Validator::make($request->all(), [
            'instance' => 'nullable|exists:instances,id',
            'year' => 'nullable|integer',
            'periode' => 'required|exists:ref_periode,id'
        ], [], [
            'instance' => 'Instance ID',
            'periode' => 'Periode'
        ]);
        if ($validate->fails()) {
            return $this->validationResponse($validate->errors());
        }

        try {
            $datas = [];
            if (!$request->instance) {
                $arrData = DB::table('acc_blo_jasa')
                    ->where('periode_id', $request->periode)
                    ->where('year', $request->year)
                    ->pluck('kode_rekening_id');
                $arrData = collect($arrData)->unique()->values();

                $arrRekenings = DB::table('ref_kode_rekening_complete')
                    ->whereIn('id', $arrData)
                    ->orderBy('fullcode')
                    ->get();
                foreach ($arrRekenings as $rek) {
                    $data = DB::table('acc_blo_jasa')
                        ->where('periode_id', $request->periode)
                        ->where('year', $request->year)
                        ->where('kode_rekening_id', $rek->id)
                        ->get();
                    $plusTotal = ($data->sum('saldo_awal') ?? 0) + ($data->sum('belanja_dibayar_dimuka_akhir') ?? 0) + ($data->sum('hutang') ?? 0) + ($data->sum('hibah') ?? 0) + ($data->sum('reklas_tambah_dari_rekening') ?? 0) + ($data->sum('reklas_tambah_dari_modal') ?? 0) + ($data->sum('plus_jukor') ?? 0);
                    $minTotal = ($data->sum('saldo_akhir') ?? 0) + ($data->sum('beban_tahun_lalu') ?? 0) + ($data->sum('belanja_dibayar_dimuka_awal') ?? 0) + ($data->sum('pembayaran_hutang') ?? 0) + ($data->sum('reklas_kurang_ke_rekening') ?? 0) + ($data->sum('reklas_kurang_ke_aset') ?? 0) + ($data->sum('atribusi') ?? 0) + ($data->sum('min_jukor') ?? 0);

                    $bebanLo = ($data->sum('realisasi_belanja') + $plusTotal) - $minTotal;
                    $datas[] = [
                        'editable' => false,
                        'id' => null,
                        'periode_id' => $request->periode,
                        'year' => $request->year,
                        'instance_id' => null,
                        'kode_rekening_id' => $rek->id,
                        'kode_rekening_fullcode' => $rek->fullcode,
                        'kode_rekening_name' => $rek->name,
                        'realisasi_belanja' => $data->sum('realisasi_belanja'),
                        'saldo_awal' => $data->sum('saldo_awal'),
                        'belanja_dibayar_dimuka_akhir' => $data->sum('belanja_dibayar_dimuka_akhir'),
                        'hutang' => $data->sum('hutang'),
                        'hibah' => $data->sum('hibah'),
                        'reklas_tambah_dari_rekening' => $data->sum('reklas_tambah_dari_rekening'),
                        'reklas_tambah_dari_modal' => $data->sum('reklas_tambah_dari_modal'),
                        'plus_jukor' => $data->sum('plus_jukor'),
                        'plus_total' => $plusTotal ?? 0,
                        'saldo_akhir' => $data->sum('saldo_akhir'),
                        'beban_tahun_lalu' => $data->sum('beban_tahun_lalu'),
                        'belanja_dibayar_dimuka_awal' => $data->sum('belanja_dibayar_dimuka_awal'),
                        'pembayaran_hutang' => $data->sum('pembayaran_hutang'),
                        'reklas_kurang_ke_rekening' => $data->sum('reklas_kurang_ke_rekening'),
                        'reklas_kurang_ke_aset' => $data->sum('reklas_kurang_ke_aset'),
                        'atribusi' => $data->sum('atribusi'),
                        'min_jukor' => $data->sum('min_jukor'),
                        'beban_lo' => $bebanLo ?? 0,
                        'kode_rekening' => $rek->fullcode,
                        'min_total' => $minTotal ?? 0,
                    ];
                }
            } elseif ($request->instance) {
                $arrLra = DB::table('acc_lra')
                    ->where('periode_id', $request->periode)
                    ->where('year', $request->year ?? date('Y'))
                    ->where('instance_id', $request->instance)
                    ->where('kode_rekening', 'ILIKE', '5.1.02.02.%')
                    ->whereRaw('LENGTH(kode_rekening) >= ?', [17])
                    ->orderBy('kode_rekening')
                    ->get();
                $arrLra88 = DB::table('acc_lra')
                    ->where('periode_id', $request->periode)
                    ->where('year', $request->year ?? date('Y'))
                    ->where('instance_id', $request->instance)
                    ->where('kode_rekening', 'ILIKE', '5.1.02.88.%')
                    ->whereRaw('LENGTH(kode_rekening) >= ?', [17])
                    ->orderBy('kode_rekening')
                    ->get();
                $arrLra90 = DB::table('acc_lra')
                    ->where('periode_id', $request->periode)
                    ->where('year', $request->year ?? date('Y'))
                    ->where('instance_id', $request->instance)
                    ->where('kode_rekening', 'ILIKE', '5.1.02.90.%')
                    ->whereRaw('LENGTH(kode_rekening) >= ?', [17])
                    ->orderBy('kode_rekening')
                    ->get();
                $arrLra99 = DB::table('acc_lra')
                    ->where('periode_id', $request->periode)
                    ->where('year', $request->year ?? date('Y'))
                    ->where('instance_id', $request->instance)
                    ->where('kode_rekening', 'ILIKE', '5.1.02.99.%')
                    ->whereRaw('LENGTH(kode_rekening) >= ?', [17])
                    ->orderBy('kode_rekening')
                    ->get();
                $arrLra = $arrLra->merge($arrLra88);
                $arrLra = $arrLra->merge($arrLra90);
                $arrLra = $arrLra->merge($arrLra99);
                foreach ($arrLra as $lra) {
                    $kodeRekeningId = DB::table('ref_kode_rekening_complete')
                        ->where('fullcode', $lra->kode_rekening)
                        ->first()->id ?? null;
                    $data = DB::table('acc_blo_jasa')
                        ->where('periode_id', $request->periode)
                        ->where('year', $request->year ?? date('Y'))
                        ->where('instance_id', $request->instance)
                        ->where('kode_rekening_id', $kodeRekeningId)
                        ->first();
                    if (!$data && $kodeRekeningId) {
                        $HutangBaru = DB::table('acc_htb_hutang_baru')
                            ->where('periode_id', $request->periode)
                            ->where('year', $request->year)
                            ->where('instance_id', $request->instance)
                            ->where('kode_rekening_id', $kodeRekeningId)
                            ->get()->sum('jasa') ?? 0;
                        $PembayaranHutang = DB::table('acc_htb_pembayaran_hutang')
                            ->where('periode_id', $request->periode)
                            ->where('year', $request->year)
                            ->where('instance_id', $request->instance)
                            ->where('kode_rekening_id', $kodeRekeningId)
                            ->get()->sum('jasa') ?? 0;

                        $BelanjaBayarDimuka = DB::table('acc_belanja_bayar_dimuka')
                            ->where('periode_id', $request->periode)
                            ->where('year', $request->year)
                            ->where('instance_id', $request->instance)
                            ->where('kode_rekening_id', $kodeRekeningId)
                            ->get()
                            ->sum('belum_jatuh_tempo') ?? 0;

                        DB::table('acc_blo_jasa')
                            ->insert([
                                'periode_id' => $request->periode,
                                'year' => $request->year ?? date('Y'),
                                'instance_id' => $request->instance,
                                'kode_rekening_id' => $kodeRekeningId,
                                'realisasi_belanja' => $lra->realisasi,
                                'saldo_awal' => 0,
                                'belanja_dibayar_dimuka_akhir' => $BelanjaBayarDimuka ?? 0,
                                'hutang' => $HutangBaru ?? 0,
                                'hibah' => 0,
                                'reklas_tambah_dari_rekening' => 0,
                                'reklas_tambah_dari_modal' => 0,
                                'plus_jukor' => 0,
                                'saldo_akhir' => 0,
                                'beban_tahun_lalu' => 0,
                                'belanja_dibayar_dimuka_awal' => 0,
                                'pembayaran_hutang' => $PembayaranHutang ?? 0,
                                'reklas_kurang_ke_rekening' => 0,
                                'reklas_kurang_ke_aset' => 0,
                                'atribusi' => 0,
                                'min_jukor' => 0,
                                'beban_lo' => 0,
                            ]);
                    }
                    if ($data) {
                        $HutangBaru = DB::table('acc_htb_hutang_baru')
                            ->where('periode_id', $data->periode_id)
                            ->where('year', $data->year)
                            ->where('instance_id', $data->instance_id)
                            ->where('kode_rekening_id', $data->kode_rekening_id)
                            ->get()->sum('jasa') ?? 0;
                        $data->hutang = $HutangBaru;
                        $PembayaranHutang = DB::table('acc_htb_pembayaran_hutang')
                            ->where('periode_id', $data->periode_id)
                            ->where('year', $data->year)
                            ->where('instance_id', $data->instance_id)
                            ->where('kode_rekening_id', $data->kode_rekening_id)
                            ->get()->sum('jasa') ?? 0;
                        $data->pembayaran_hutang = $PembayaranHutang;

                        $BelanjaBayarDimuka = DB::table('acc_belanja_bayar_dimuka')
                            ->where('periode_id', $data->periode_id)
                            ->where('year', $data->year)
                            ->where('instance_id', $data->instance_id)
                            ->where('kode_rekening_id', $data->kode_rekening_id)
                            ->get()->sum('belum_jatuh_tempo') ?? 0;
                        $data->belanja_dibayar_dimuka_akhir = $BelanjaBayarDimuka;

                        $plusTotalKeys = ['saldo_awal', 'belanja_dibayar_dimuka_akhir', 'hutang', 'hibah', 'reklas_tambah_dari_rekening', 'reklas_tambah_dari_modal', 'plus_jukor'];
                        $plusTotal = 0;
                        foreach ($plusTotalKeys as $key) {
                            $plusTotal += floatval($data->$key);
                        }
                        $minTotalKeys = ['saldo_akhir', 'beban_tahun_lalu', 'belanja_dibayar_dimuka_awal', 'pembayaran_hutang', 'reklas_kurang_ke_rekening', 'reklas_kurang_ke_aset', 'atribusi', 'min_jukor'];
                        $minTotal = 0;
                        foreach ($minTotalKeys as $key) {
                            $minTotal += floatval($data->$key);
                        }
                        $bebanLo = ($data->realisasi_belanja + $plusTotal) - $minTotal;

                        $editable = false;
                        if ($lra->kode_rekening == '5.1.02.88.88.8888' || $lra->kode_rekening == '5.1.02.90.01.0001' || $lra->kode_rekening == '5.1.02.99.99.9999') {
                            $editable = true;
                        }
                        $realisasiBelanja = $data->realisasi_belanja;
                        if ($editable == false) {
                            $realisasiBelanja = $lra->realisasi;
                        }

                        DB::table('acc_blo_jasa')
                            ->where('id', $data->id)
                            ->update([
                                'realisasi_belanja' => $realisasiBelanja,
                                'hutang' => $HutangBaru,
                                'pembayaran_hutang' => $PembayaranHutang,
                                'beban_lo' => $bebanLo
                            ]);

                        $instance = DB::table('instances')
                            ->where('id', $request->instance)
                            ->first();
                        $kodeRekening = DB::table('ref_kode_rekening_complete')
                            ->where('id', $data->kode_rekening_id)
                            ->first();
                        $datas[] = [
                            'editable' => $editable ?? false,
                            'id' => $data->id,
                            'periode_id' => $data->periode_id,
                            'year' => $data->year,
                            'instance_id' => $data->instance_id,
                            'instance_name' => $instance->name,
                            'kode_rekening_id' => $data->kode_rekening_id,
                            'kode_rekening_fullcode' => $kodeRekening->fullcode,
                            'kode_rekening_name' => $kodeRekening->name,
                            'realisasi_belanja' => $data->realisasi_belanja,
                            'saldo_awal' => $data->saldo_awal,
                            'belanja_dibayar_dimuka_akhir' => $BelanjaBayarDimuka ?? $data->belanja_dibayar_dimuka_akhir,
                            'hutang' => $HutangBaru ?? $data->hutang,
                            'hibah' => $data->hibah,
                            'reklas_tambah_dari_rekening' => $data->reklas_tambah_dari_rekening,
                            'reklas_tambah_dari_modal' => $data->reklas_tambah_dari_modal,
                            'plus_jukor' => $data->plus_jukor,
                            'plus_total' => $plusTotal ?? 0,
                            'saldo_akhir' => $data->saldo_akhir,
                            'beban_tahun_lalu' => $data->beban_tahun_lalu,
                            'belanja_dibayar_dimuka_awal' => $data->belanja_dibayar_dimuka_awal,
                            'pembayaran_hutang' => $data->pembayaran_hutang,
                            'reklas_kurang_ke_rekening' => $data->reklas_kurang_ke_rekening,
                            'reklas_kurang_ke_aset' => $data->reklas_kurang_ke_aset,
                            'atribusi' => $data->atribusi,
                            'min_jukor' => $data->min_jukor,
                            'beban_lo' => $bebanLo ?? 0,
                            'kode_rekening' => $lra->kode_rekening,
                            'min_total' => $minTotal ?? 0,
                        ];
                    }
                }
            }

            return $this->successResponse($datas, 'Pemeliharaan berhasil diambil');
        } catch (\Exception $e) {
            return $this->errorResponse($e->getMessage() . ' ' . $e->getLine());
        }
    }

    function storeJasa(Request $request)
    {
        $validate = Validator::make($request->all(), [
            'periode' => 'required|exists:ref_periode,id',
            'year' => 'nullable|numeric',
            'data.*.instance_id' => 'required|exists:instances,id',
            'data.*.kode_rekening_id' => 'required|exists:ref_kode_rekening_complete,id',
        ], [], [
            'periode' => 'Periode',
            'data.*.instance_id' => 'Instance ID',
            'data.*.kode_rekening_id' => 'Kode Rekening ID',
        ]);
        if ($validate->fails()) {
            return $this->validationResponse($validate->errors());
        }

        DB::beginTransaction();
        try {
            $dateYear = $request->year ?? date('Y');
            $datas = $request->data;
            foreach ($datas as $input) {
                if ($input['id']) {
                    DB::table('acc_blo_jasa')
                        ->where('id', $input['id'])
                        ->update([
                            'realisasi_belanja' => $input['realisasi_belanja'],
                            'saldo_awal' => $input['saldo_awal'],
                            'belanja_dibayar_dimuka_akhir' => $input['belanja_dibayar_dimuka_akhir'],
                            'hutang' => $input['hutang'],
                            'hibah' => $input['hibah'],
                            'reklas_tambah_dari_rekening' => $input['reklas_tambah_dari_rekening'],
                            'reklas_tambah_dari_modal' => $input['reklas_tambah_dari_modal'],
                            'plus_jukor' => $input['plus_jukor'],
                            'saldo_akhir' => $input['saldo_akhir'],
                            'beban_tahun_lalu' => $input['beban_tahun_lalu'],
                            'belanja_dibayar_dimuka_awal' => $input['belanja_dibayar_dimuka_awal'],
                            'pembayaran_hutang' => $input['pembayaran_hutang'],
                            'reklas_kurang_ke_rekening' => $input['reklas_kurang_ke_rekening'],
                            'reklas_kurang_ke_aset' => $input['reklas_kurang_ke_aset'],
                            'atribusi' => $input['atribusi'],
                            'min_jukor' => $input['min_jukor'],
                            'beban_lo' => $input['beban_lo'],
                        ]);
                } else {
                    DB::table('acc_blo_jasa')->insert([
                        'periode_id' => $request->periode,
                        'year' => $dateYear,
                        'instance_id' => $input['instance_id'],
                        'kode_rekening_id' => $input['kode_rekening_id'],
                        'realisasi_belanja' => $input['realisasi_belanja'],
                        'saldo_awal' => $input['saldo_awal'],
                        'belanja_dibayar_dimuka_akhir' => $input['belanja_dibayar_dimuka_akhir'],
                        'hutang' => $input['hutang'],
                        'hibah' => $input['hibah'],
                        'reklas_tambah_dari_rekening' => $input['reklas_tambah_dari_rekening'],
                        'reklas_tambah_dari_modal' => $input['reklas_tambah_dari_modal'],
                        'plus_jukor' => $input['plus_jukor'],
                        'saldo_akhir' => $input['saldo_akhir'],
                        'beban_tahun_lalu' => $input['beban_tahun_lalu'],
                        'belanja_dibayar_dimuka_awal' => $input['belanja_dibayar_dimuka_awal'],
                        'pembayaran_hutang' => $input['pembayaran_hutang'],
                        'reklas_kurang_ke_rekening' => $input['reklas_kurang_ke_rekening'],
                        'reklas_kurang_ke_aset' => $input['reklas_kurang_ke_aset'],
                        'atribusi' => $input['atribusi'],
                        'min_jukor' => $input['min_jukor'],
                        'beban_lo' => $input['beban_lo'],
                    ]);
                }
            }

            DB::commit();
            return $this->successResponse([], 'Pemeliharaan berhasil disimpan');
        } catch (\Exception $e) {
            DB::rollBack();
            return $this->errorResponse($e->getMessage() . ' ' . $e->getLine());
        }
    }

    function deleteJasa(Request $request)
    {
        $validate = Validator::make($request->all(), [
            'id' => 'required|exists:acc_blo_jasa,id'
        ], [], [
            'id' => 'ID'
        ]);
        if ($validate->fails()) {
            return $this->validationResponse($validate->errors());
        }

        DB::beginTransaction();
        try {
            DB::table('acc_blo_jasa')
                ->where('id', $request->id)
                ->delete();

            DB::commit();
            return $this->successResponse([], 'Pemeliharaan berhasil dihapus');
        } catch (\Exception $e) {
            DB::rollBack();
            return $this->errorResponse($e->getMessage() . ' ' . $e->getLine());
        }
    }
    // Jasa End

    // Pemeliharaan Start
    function getPemeliharaan(Request $request)
    {
        $validate = Validator::make($request->all(), [
            'instance' => 'nullable|exists:instances,id',
            'year' => 'nullable|integer',
            'periode' => 'required|exists:ref_periode,id'
        ], [], [
            'instance' => 'Instance ID',
            'periode' => 'Periode'
        ]);
        if ($validate->fails()) {
            return $this->validationResponse($validate->errors());
        }

        try {
            $datas = [];
            if (!$request->instance) {
                $arrData = DB::table('acc_blo_pemeliharaan')
                    ->where('periode_id', $request->periode)
                    ->where('year', $request->year)
                    ->pluck('kode_rekening_id');
                $arrData = collect($arrData)->unique()->values();

                $arrRekenings = DB::table('ref_kode_rekening_complete')
                    ->whereIn('id', $arrData)
                    ->orderBy('fullcode')
                    ->get();
                foreach ($arrRekenings as $rek) {
                    $data = DB::table('acc_blo_pemeliharaan')
                        ->where('periode_id', $request->periode)
                        ->where('year', $request->year)
                        ->where('kode_rekening_id', $rek->id)
                        ->get();
                    $plusTotal = ($data->sum('saldo_awal') ?? 0) + ($data->sum('belanja_dibayar_dimuka_akhir') ?? 0) + ($data->sum('hutang') ?? 0) + ($data->sum('hibah') ?? 0) + ($data->sum('reklas_tambah_dari_rekening') ?? 0) + ($data->sum('reklas_tambah_dari_modal') ?? 0) + ($data->sum('plus_jukor') ?? 0);
                    $minTotal = ($data->sum('saldo_akhir') ?? 0) + ($data->sum('beban_tahun_lalu') ?? 0) + ($data->sum('belanja_dibayar_dimuka_awal') ?? 0) + ($data->sum('pembayaran_hutang') ?? 0) + ($data->sum('reklas_kurang_ke_rekening') ?? 0) + ($data->sum('reklas_kurang_ke_aset') ?? 0) + ($data->sum('atribusi') ?? 0) + ($data->sum('min_jukor') ?? 0);

                    $bebanLo = ($data->sum('realisasi_belanja') + $plusTotal) - $minTotal;
                    $datas[] = [
                        'editable' => false,
                        'id' => null,
                        'periode_id' => $request->periode,
                        'year' => $request->year,
                        'instance_id' => null,
                        'kode_rekening_id' => $rek->id,
                        'kode_rekening_fullcode' => $rek->fullcode,
                        'kode_rekening_name' => $rek->name,
                        'realisasi_belanja' => $data->sum('realisasi_belanja'),
                        'saldo_awal' => $data->sum('saldo_awal'),
                        'belanja_dibayar_dimuka_akhir' => $data->sum('belanja_dibayar_dimuka_akhir'),
                        'hutang' => $data->sum('hutang'),
                        'hibah' => $data->sum('hibah'),
                        'reklas_tambah_dari_rekening' => $data->sum('reklas_tambah_dari_rekening'),
                        'reklas_tambah_dari_modal' => $data->sum('reklas_tambah_dari_modal'),
                        'plus_jukor' => $data->sum('plus_jukor'),
                        'plus_total' => $plusTotal ?? 0,
                        'saldo_akhir' => $data->sum('saldo_akhir'),
                        'beban_tahun_lalu' => $data->sum('beban_tahun_lalu'),
                        'belanja_dibayar_dimuka_awal' => $data->sum('belanja_dibayar_dimuka_awal'),
                        'pembayaran_hutang' => $data->sum('pembayaran_hutang'),
                        'reklas_kurang_ke_rekening' => $data->sum('reklas_kurang_ke_rekening'),
                        'reklas_kurang_ke_aset' => $data->sum('reklas_kurang_ke_aset'),
                        'atribusi' => $data->sum('atribusi'),
                        'min_jukor' => $data->sum('min_jukor'),
                        'beban_lo' => $bebanLo ?? 0,
                        'kode_rekening' => $rek->fullcode,
                        'min_total' => $minTotal ?? 0,
                    ];
                }
            } elseif ($request->instance) {
                $arrLra = DB::table('acc_lra')
                    ->where('periode_id', $request->periode)
                    ->where('year', $request->year ?? date('Y'))
                    ->where('instance_id', $request->instance)
                    ->where('kode_rekening', 'ILIKE', '5.1.02.03.%')
                    ->whereRaw('LENGTH(kode_rekening) >= ?', [17])
                    ->orderBy('kode_rekening')
                    ->get();
                $arrLra88 = DB::table('acc_lra')
                    ->where('periode_id', $request->periode)
                    ->where('year', $request->year ?? date('Y'))
                    ->where('instance_id', $request->instance)
                    ->where('kode_rekening', 'ILIKE', '5.1.02.88.%')
                    ->whereRaw('LENGTH(kode_rekening) >= ?', [17])
                    ->orderBy('kode_rekening')
                    ->get();
                $arrLra90 = DB::table('acc_lra')
                    ->where('periode_id', $request->periode)
                    ->where('year', $request->year ?? date('Y'))
                    ->where('instance_id', $request->instance)
                    ->where('kode_rekening', 'ILIKE', '5.1.02.90.%')
                    ->whereRaw('LENGTH(kode_rekening) >= ?', [17])
                    ->orderBy('kode_rekening')
                    ->get();
                $arrLra99 = DB::table('acc_lra')
                    ->where('periode_id', $request->periode)
                    ->where('year', $request->year ?? date('Y'))
                    ->where('instance_id', $request->instance)
                    ->where('kode_rekening', 'ILIKE', '5.1.02.99.%')
                    ->whereRaw('LENGTH(kode_rekening) >= ?', [17])
                    ->orderBy('kode_rekening')
                    ->get();
                $arrLra = $arrLra->merge($arrLra88);
                $arrLra = $arrLra->merge($arrLra90);
                $arrLra = $arrLra->merge($arrLra99);
                foreach ($arrLra as $lra) {
                    $kodeRekeningId = DB::table('ref_kode_rekening_complete')
                        ->where('fullcode', $lra->kode_rekening)
                        ->first()->id ?? null;
                    $data = DB::table('acc_blo_pemeliharaan')
                        ->where('periode_id', $request->periode)
                        ->where('year', $request->year ?? date('Y'))
                        ->where('instance_id', $request->instance)
                        ->where('kode_rekening_id', $kodeRekeningId)
                        ->first();
                    if (!$data && $kodeRekeningId) {
                        $HutangBaru = DB::table('acc_htb_hutang_baru')
                            ->where('periode_id', $request->periode)
                            ->where('year', $request->year)
                            ->where('instance_id', $request->instance)
                            ->where('kode_rekening_id', $kodeRekeningId)
                            ->get()->sum('pemeliharaan') ?? 0;
                        $PembayaranHutang = DB::table('acc_htb_pembayaran_hutang')
                            ->where('periode_id', $request->periode)
                            ->where('year', $request->year)
                            ->where('instance_id', $request->instance)
                            ->where('kode_rekening_id', $kodeRekeningId)
                            ->get()->sum('pemeliharaan') ?? 0;

                        DB::table('acc_blo_pemeliharaan')
                            ->insert([
                                'periode_id' => $request->periode,
                                'year' => $request->year ?? date('Y'),
                                'instance_id' => $request->instance,
                                'kode_rekening_id' => $kodeRekeningId,
                                'realisasi_belanja' => $lra->realisasi,
                                'saldo_awal' => 0,
                                'belanja_dibayar_dimuka_akhir' => 0,
                                'hutang' => $hutangPemeliharaan ?? 0,
                                'hibah' => 0,
                                'reklas_tambah_dari_rekening' => 0,
                                'reklas_tambah_dari_modal' => 0,
                                'plus_jukor' => 0,
                                'saldo_akhir' => 0,
                                'beban_tahun_lalu' => 0,
                                'belanja_dibayar_dimuka_awal' => 0,
                                'pembayaran_hutang' => 0,
                                'reklas_kurang_ke_rekening' => 0,
                                'reklas_kurang_ke_aset' => 0,
                                'atribusi' => 0,
                                'min_jukor' => 0,
                                'beban_lo' => 0,
                            ]);
                    }
                    if ($data) {
                        $HutangBaru = DB::table('acc_htb_hutang_baru')
                            ->where('periode_id', $data->periode_id)
                            ->where('year', $data->year)
                            ->where('instance_id', $data->instance_id)
                            ->where('kode_rekening_id', $data->kode_rekening_id)
                            ->get()->sum('pemeliharaan') ?? 0;
                        $data->hutang = $HutangBaru;
                        $PembayaranHutang = DB::table('acc_htb_pembayaran_hutang')
                            ->where('periode_id', $data->periode_id)
                            ->where('year', $data->year)
                            ->where('instance_id', $data->instance_id)
                            ->where('kode_rekening_id', $data->kode_rekening_id)
                            ->get()->sum('pemeliharaan') ?? 0;
                        $data->pembayaran_hutang = $PembayaranHutang;

                        $plusTotalKeys = ['saldo_awal', 'belanja_dibayar_dimuka_akhir', 'hutang', 'hibah', 'reklas_tambah_dari_rekening', 'reklas_tambah_dari_modal', 'plus_jukor'];
                        $plusTotal = 0;
                        foreach ($plusTotalKeys as $key) {
                            $plusTotal += floatval($data->$key);
                        }
                        $minTotalKeys = ['saldo_akhir', 'beban_tahun_lalu', 'belanja_dibayar_dimuka_awal', 'pembayaran_hutang', 'reklas_kurang_ke_rekening', 'reklas_kurang_ke_aset', 'atribusi', 'min_jukor'];
                        $minTotal = 0;
                        foreach ($minTotalKeys as $key) {
                            $minTotal += floatval($data->$key);
                        }
                        $bebanLo = ($data->realisasi_belanja + $plusTotal) - $minTotal;

                        $editable = false;
                        if ($lra->kode_rekening == '5.1.02.88.88.8888' || $lra->kode_rekening == '5.1.02.90.01.0001' || $lra->kode_rekening == '5.1.02.99.99.9999') {
                            $editable = true;
                        }
                        $realisasiBelanja = $data->realisasi_belanja;
                        if ($editable == false) {
                            $realisasiBelanja = $lra->realisasi;
                        }

                        DB::table('acc_blo_pemeliharaan')
                            ->where('id', $data->id)
                            ->update([
                                'kode_rekening_id' => $kodeRekeningId,
                                'realisasi_belanja' => $realisasiBelanja,
                                'hutang' => $HutangBaru,
                                'pembayaran_hutang' => $PembayaranHutang,
                                'beban_lo' => $bebanLo
                            ]);
                        $instance = DB::table('instances')
                            ->where('id', $request->instance)
                            ->first();
                        $kodeRekening = DB::table('ref_kode_rekening_complete')
                            ->where('id', $data->kode_rekening_id)
                            ->first();
                        $datas[] = [
                            'editable' => $editable ?? false,
                            'id' => $data->id,
                            'periode_id' => $data->periode_id,
                            'year' => $data->year,
                            'instance_id' => $data->instance_id,
                            'instance_name' => $instance->name,
                            'kode_rekening_id' => $data->kode_rekening_id,
                            'kode_rekening_fullcode' => $kodeRekening->fullcode,
                            'kode_rekening_name' => $kodeRekening->name,
                            'realisasi_belanja' => $data->realisasi_belanja,
                            'saldo_awal' => $data->saldo_awal,
                            'belanja_dibayar_dimuka_akhir' => $data->belanja_dibayar_dimuka_akhir,
                            'hutang' => $HutangBaru ?? $data->hutang,
                            'hibah' => $data->hibah,
                            'reklas_tambah_dari_rekening' => $data->reklas_tambah_dari_rekening,
                            'reklas_tambah_dari_modal' => $data->reklas_tambah_dari_modal,
                            'plus_jukor' => $data->plus_jukor,
                            'plus_total' => $plusTotal ?? 0,
                            'saldo_akhir' => $data->saldo_akhir,
                            'beban_tahun_lalu' => $data->beban_tahun_lalu,
                            'belanja_dibayar_dimuka_awal' => $data->belanja_dibayar_dimuka_awal,
                            'pembayaran_hutang' => $data->pembayaran_hutang,
                            'reklas_kurang_ke_rekening' => $data->reklas_kurang_ke_rekening,
                            'reklas_kurang_ke_aset' => $data->reklas_kurang_ke_aset,
                            'atribusi' => $data->atribusi,
                            'min_jukor' => $data->min_jukor,
                            'beban_lo' => $bebanLo ?? 0,
                            'kode_rekening' => $lra->kode_rekening,
                            'min_total' => $minTotal ?? 0,
                        ];
                    }
                }
            }

            return $this->successResponse($datas, 'Pemeliharaan berhasil diambil');
        } catch (\Exception $e) {
            return $this->errorResponse($e->getMessage() . ' ' . $e->getLine());
        }
    }

    function storePemeliharaan(Request $request)
    {
        $validate = Validator::make($request->all(), [
            'periode' => 'required|exists:ref_periode,id',
            'year' => 'nullable|numeric',
            'data.*.instance_id' => 'required|exists:instances,id',
            'data.*.kode_rekening_id' => 'required|exists:ref_kode_rekening_complete,id',
        ], [], [
            'periode' => 'Periode',
            'data.*.instance_id' => 'Instance ID',
            'data.*.kode_rekening_id' => 'Kode Rekening ID',
        ]);
        if ($validate->fails()) {
            return $this->validationResponse($validate->errors());
        }

        DB::beginTransaction();
        try {
            $dateYear = $request->year ?? date('Y');
            $datas = $request->data;
            foreach ($datas as $input) {
                if ($input['id']) {
                    DB::table('acc_blo_pemeliharaan')
                        ->where('id', $input['id'])
                        ->update([
                            'realisasi_belanja' => $input['realisasi_belanja'],
                            'saldo_awal' => $input['saldo_awal'],
                            'belanja_dibayar_dimuka_akhir' => $input['belanja_dibayar_dimuka_akhir'],
                            'hutang' => $input['hutang'],
                            'hibah' => $input['hibah'],
                            'reklas_tambah_dari_rekening' => $input['reklas_tambah_dari_rekening'],
                            'reklas_tambah_dari_modal' => $input['reklas_tambah_dari_modal'],
                            'plus_jukor' => $input['plus_jukor'],
                            'saldo_akhir' => $input['saldo_akhir'],
                            'beban_tahun_lalu' => $input['beban_tahun_lalu'],
                            'belanja_dibayar_dimuka_awal' => $input['belanja_dibayar_dimuka_awal'],
                            'pembayaran_hutang' => $input['pembayaran_hutang'],
                            'reklas_kurang_ke_rekening' => $input['reklas_kurang_ke_rekening'],
                            'reklas_kurang_ke_aset' => $input['reklas_kurang_ke_aset'],
                            'atribusi' => $input['atribusi'],
                            'min_jukor' => $input['min_jukor'],
                            'beban_lo' => $input['beban_lo'],
                        ]);
                } else {
                    DB::table('acc_blo_pemeliharaan')->insert([
                        'periode_id' => $request->periode,
                        'year' => $dateYear,
                        'instance_id' => $input['instance_id'],
                        'kode_rekening_id' => $input['kode_rekening_id'],
                        'realisasi_belanja' => $input['realisasi_belanja'],
                        'saldo_awal' => $input['saldo_awal'],
                        'belanja_dibayar_dimuka_akhir' => $input['belanja_dibayar_dimuka_akhir'],
                        'hutang' => $input['hutang'],
                        'hibah' => $input['hibah'],
                        'reklas_tambah_dari_rekening' => $input['reklas_tambah_dari_rekening'],
                        'reklas_tambah_dari_modal' => $input['reklas_tambah_dari_modal'],
                        'plus_jukor' => $input['plus_jukor'],
                        'saldo_akhir' => $input['saldo_akhir'],
                        'beban_tahun_lalu' => $input['beban_tahun_lalu'],
                        'belanja_dibayar_dimuka_awal' => $input['belanja_dibayar_dimuka_awal'],
                        'pembayaran_hutang' => $input['pembayaran_hutang'],
                        'reklas_kurang_ke_rekening' => $input['reklas_kurang_ke_rekening'],
                        'reklas_kurang_ke_aset' => $input['reklas_kurang_ke_aset'],
                        'atribusi' => $input['atribusi'],
                        'min_jukor' => $input['min_jukor'],
                        'beban_lo' => $input['beban_lo'],
                    ]);
                }
            }

            DB::commit();
            return $this->successResponse([], 'Pemeliharaan berhasil disimpan');
        } catch (\Exception $e) {
            DB::rollBack();
            return $this->errorResponse($e->getMessage() . ' ' . $e->getLine());
        }
    }

    function deletePemeliharaan(Request $request)
    {
        $validate = Validator::make($request->all(), [
            'id' => 'required|exists:acc_blo_pemeliharaan,id'
        ], [], [
            'id' => 'ID'
        ]);
        if ($validate->fails()) {
            return $this->validationResponse($validate->errors());
        }

        DB::beginTransaction();
        try {
            DB::table('acc_blo_pemeliharaan')
                ->where('id', $request->id)
                ->delete();

            DB::commit();
            return $this->successResponse([], 'Pemeliharaan berhasil dihapus');
        } catch (\Exception $e) {
            DB::rollBack();
            return $this->errorResponse($e->getMessage() . ' ' . $e->getLine());
        }
    }
    // Pemeliharaan End

    // Perjadin Start
    function getPerjadin(Request $request)
    {
        $validate = Validator::make($request->all(), [
            'instance' => 'nullable|exists:instances,id',
            'year' => 'nullable|integer',
            'periode' => 'required|exists:ref_periode,id'
        ], [], [
            'instance' => 'Instance ID',
            'periode' => 'Periode'
        ]);
        if ($validate->fails()) {
            return $this->validationResponse($validate->errors());
        }

        try {
            $datas = [];
            if (!$request->instance) {
                $arrData = DB::table('acc_blo_perjadin')
                    ->where('periode_id', $request->periode)
                    ->where('year', $request->year)
                    ->pluck('kode_rekening_id');
                $arrData = collect($arrData)->unique()->values();

                $arrRekenings = DB::table('ref_kode_rekening_complete')
                    ->whereIn('id', $arrData)
                    ->orderBy('fullcode')
                    ->get();
                foreach ($arrRekenings as $rek) {
                    $data = DB::table('acc_blo_perjadin')
                        ->where('periode_id', $request->periode)
                        ->where('year', $request->year)
                        ->where('kode_rekening_id', $rek->id)
                        ->get();
                    $plusTotal = ($data->sum('saldo_awal') ?? 0) + ($data->sum('belanja_dibayar_dimuka_akhir') ?? 0) + ($data->sum('hutang') ?? 0) + ($data->sum('hibah') ?? 0) + ($data->sum('reklas_tambah_dari_rekening') ?? 0) + ($data->sum('reklas_tambah_dari_modal') ?? 0) + ($data->sum('plus_jukor') ?? 0);
                    $minTotal = ($data->sum('saldo_akhir') ?? 0) + ($data->sum('beban_tahun_lalu') ?? 0) + ($data->sum('belanja_dibayar_dimuka_awal') ?? 0) + ($data->sum('pembayaran_hutang') ?? 0) + ($data->sum('reklas_kurang_ke_rekening') ?? 0) + ($data->sum('reklas_kurang_ke_aset') ?? 0) + ($data->sum('atribusi') ?? 0) + ($data->sum('min_jukor') ?? 0);

                    $bebanLo = ($data->sum('realisasi_belanja') + $plusTotal) - $minTotal;
                    $datas[] = [
                        'editable' => false,
                        'id' => null,
                        'periode_id' => $request->periode,
                        'year' => $request->year,
                        'instance_id' => null,
                        'kode_rekening_id' => $rek->id,
                        'kode_rekening_fullcode' => $rek->fullcode,
                        'kode_rekening_name' => $rek->name,
                        'realisasi_belanja' => $data->sum('realisasi_belanja'),
                        'saldo_awal' => $data->sum('saldo_awal'),
                        'belanja_dibayar_dimuka_akhir' => $data->sum('belanja_dibayar_dimuka_akhir'),
                        'hutang' => $data->sum('hutang'),
                        'hibah' => $data->sum('hibah'),
                        'reklas_tambah_dari_rekening' => $data->sum('reklas_tambah_dari_rekening'),
                        'reklas_tambah_dari_modal' => $data->sum('reklas_tambah_dari_modal'),
                        'plus_jukor' => $data->sum('plus_jukor'),
                        'plus_total' => $plusTotal ?? 0,
                        'saldo_akhir' => $data->sum('saldo_akhir'),
                        'beban_tahun_lalu' => $data->sum('beban_tahun_lalu'),
                        'belanja_dibayar_dimuka_awal' => $data->sum('belanja_dibayar_dimuka_awal'),
                        'pembayaran_hutang' => $data->sum('pembayaran_hutang'),
                        'reklas_kurang_ke_rekening' => $data->sum('reklas_kurang_ke_rekening'),
                        'reklas_kurang_ke_aset' => $data->sum('reklas_kurang_ke_aset'),
                        'atribusi' => $data->sum('atribusi'),
                        'min_jukor' => $data->sum('min_jukor'),
                        'beban_lo' => $bebanLo ?? 0,
                        'kode_rekening' => $rek->fullcode,
                        'min_total' => $minTotal ?? 0,
                    ];
                }
            } elseif ($request->instance) {
                $arrLra = DB::table('acc_lra')
                    ->where('periode_id', $request->periode)
                    ->where('year', $request->year ?? date('Y'))
                    ->where('instance_id', $request->instance)
                    ->where('kode_rekening', 'ILIKE', '5.1.02.04.%')
                    ->whereRaw('LENGTH(kode_rekening) >= ?', [17])
                    ->orderBy('kode_rekening')
                    ->get();
                $arrLra88 = DB::table('acc_lra')
                    ->where('periode_id', $request->periode)
                    ->where('year', $request->year ?? date('Y'))
                    ->where('instance_id', $request->instance)
                    ->where('kode_rekening', 'ILIKE', '5.1.02.88.%')
                    ->whereRaw('LENGTH(kode_rekening) >= ?', [17])
                    ->orderBy('kode_rekening')
                    ->get();
                $arrLra90 = DB::table('acc_lra')
                    ->where('periode_id', $request->periode)
                    ->where('year', $request->year ?? date('Y'))
                    ->where('instance_id', $request->instance)
                    ->where('kode_rekening', 'ILIKE', '5.1.02.90.%')
                    ->whereRaw('LENGTH(kode_rekening) >= ?', [17])
                    ->orderBy('kode_rekening')
                    ->get();
                $arrLra99 = DB::table('acc_lra')
                    ->where('periode_id', $request->periode)
                    ->where('year', $request->year ?? date('Y'))
                    ->where('instance_id', $request->instance)
                    ->where('kode_rekening', 'ILIKE', '5.1.02.99.%')
                    ->whereRaw('LENGTH(kode_rekening) >= ?', [17])
                    ->orderBy('kode_rekening')
                    ->get();
                $arrLra = $arrLra->merge($arrLra88);
                $arrLra = $arrLra->merge($arrLra90);
                $arrLra = $arrLra->merge($arrLra99);
                foreach ($arrLra as $lra) {
                    $kodeRekeningId = DB::table('ref_kode_rekening_complete')
                        ->where('fullcode', $lra->kode_rekening)
                        ->first()->id ?? null;
                    $data = DB::table('acc_blo_perjadin')
                        ->where('periode_id', $request->periode)
                        ->where('year', $request->year ?? date('Y'))
                        ->where('instance_id', $request->instance)
                        ->where('kode_rekening_id', $kodeRekeningId)
                        ->first();
                    if (!$data && $kodeRekeningId) {
                        $HutangBaru = DB::table('acc_htb_hutang_baru')
                            ->where('periode_id', $request->periode)
                            ->where('year', $request->year)
                            ->where('instance_id', $request->instance)
                            ->where('kode_rekening_id', $kodeRekeningId)
                            ->get()->sum('perjadin') ?? 0;
                        $PembayaranHutang = DB::table('acc_htb_pembayaran_hutang')
                            ->where('periode_id', $request->periode)
                            ->where('year', $request->year)
                            ->where('instance_id', $request->instance)
                            ->where('kode_rekening_id', $kodeRekeningId)
                            ->get()->sum('perjadin') ?? 0;

                        DB::table('acc_blo_perjadin')
                            ->insert([
                                'periode_id' => $request->periode,
                                'year' => $request->year ?? date('Y'),
                                'instance_id' => $request->instance,
                                'kode_rekening_id' => $kodeRekeningId,
                                'realisasi_belanja' => $lra->realisasi,
                                'saldo_awal' => 0,
                                'belanja_dibayar_dimuka_akhir' => 0,
                                'hutang' => 0,
                                'hibah' => 0,
                                'reklas_tambah_dari_rekening' => 0,
                                'reklas_tambah_dari_modal' => 0,
                                'plus_jukor' => 0,
                                'saldo_akhir' => 0,
                                'beban_tahun_lalu' => 0,
                                'belanja_dibayar_dimuka_awal' => 0,
                                'pembayaran_hutang' => 0,
                                'reklas_kurang_ke_rekening' => 0,
                                'reklas_kurang_ke_aset' => 0,
                                'atribusi' => 0,
                                'min_jukor' => 0,
                                'beban_lo' => 0,
                            ]);
                    }
                    if ($data) {
                        $HutangBaru = DB::table('acc_htb_hutang_baru')
                            ->where('periode_id', $data->periode_id)
                            ->where('year', $data->year)
                            ->where('instance_id', $data->instance_id)
                            ->where('kode_rekening_id', $data->kode_rekening_id)
                            ->get()->sum('perjadin') ?? 0;
                        $data->hutang = $HutangBaru;
                        $PembayaranHutang = DB::table('acc_htb_pembayaran_hutang')
                            ->where('periode_id', $data->periode_id)
                            ->where('year', $data->year)
                            ->where('instance_id', $data->instance_id)
                            ->where('kode_rekening_id', $data->kode_rekening_id)
                            ->get()->sum('perjadin') ?? 0;
                        $data->pembayaran_hutang = $PembayaranHutang;

                        $plusTotalKeys = ['saldo_awal', 'belanja_dibayar_dimuka_akhir', 'hutang', 'hibah', 'reklas_tambah_dari_rekening', 'reklas_tambah_dari_modal', 'plus_jukor'];
                        $plusTotal = 0;
                        foreach ($plusTotalKeys as $key) {
                            $plusTotal += floatval($data->$key);
                        }
                        $minTotalKeys = ['saldo_akhir', 'beban_tahun_lalu', 'belanja_dibayar_dimuka_awal', 'pembayaran_hutang', 'reklas_kurang_ke_rekening', 'reklas_kurang_ke_aset', 'atribusi', 'min_jukor'];
                        $minTotal = 0;
                        foreach ($minTotalKeys as $key) {
                            $minTotal += floatval($data->$key);
                        }
                        $bebanLo = ($data->realisasi_belanja + $plusTotal) - $minTotal;

                        $editable = false;
                        if ($lra->kode_rekening == '5.1.02.88.88.8888' || $lra->kode_rekening == '5.1.02.90.01.0001' || $lra->kode_rekening == '5.1.02.99.99.9999') {
                            $editable = true;
                        }
                        $realisasiBelanja = $data->realisasi_belanja;
                        if ($editable == false) {
                            $realisasiBelanja = $lra->realisasi;
                        }

                        DB::table('acc_blo_perjadin')
                            ->where('id', $data->id)
                            ->update([
                                'realisasi_belanja' => $realisasiBelanja,
                                'hutang' => $HutangBaru,
                                'pembayaran_hutang' => $PembayaranHutang,
                                'beban_lo' => $bebanLo
                            ]);

                        $instance = DB::table('instances')
                            ->where('id', $request->instance)
                            ->first();
                        $kodeRekening = DB::table('ref_kode_rekening_complete')
                            ->where('id', $data->kode_rekening_id)
                            ->first();
                        $datas[] = [
                            'editable' => $editable ?? false,
                            'id' => $data->id,
                            'periode_id' => $data->periode_id,
                            'year' => $data->year,
                            'instance_id' => $data->instance_id,
                            'instance_name' => $instance->name,
                            'kode_rekening_id' => $data->kode_rekening_id,
                            'kode_rekening_fullcode' => $kodeRekening->fullcode,
                            'kode_rekening_name' => $kodeRekening->name,
                            'realisasi_belanja' => $data->realisasi_belanja,
                            'saldo_awal' => $data->saldo_awal,
                            'belanja_dibayar_dimuka_akhir' => $data->belanja_dibayar_dimuka_akhir,
                            'hutang' => $HutangBaru ?? $data->hutang,
                            'hibah' => $data->hibah,
                            'reklas_tambah_dari_rekening' => $data->reklas_tambah_dari_rekening,
                            'reklas_tambah_dari_modal' => $data->reklas_tambah_dari_modal,
                            'plus_jukor' => $data->plus_jukor,
                            'plus_total' => $plusTotal ?? 0,
                            'saldo_akhir' => $data->saldo_akhir,
                            'beban_tahun_lalu' => $data->beban_tahun_lalu,
                            'belanja_dibayar_dimuka_awal' => $data->belanja_dibayar_dimuka_awal,
                            'pembayaran_hutang' => $data->pembayaran_hutang,
                            'reklas_kurang_ke_rekening' => $data->reklas_kurang_ke_rekening,
                            'reklas_kurang_ke_aset' => $data->reklas_kurang_ke_aset,
                            'atribusi' => $data->atribusi,
                            'min_jukor' => $data->min_jukor,
                            'beban_lo' => $bebanLo ?? 0,
                            'kode_rekening' => $lra->kode_rekening,
                            'min_total' => $minTotal ?? 0,
                        ];
                    }
                }
            }

            return $this->successResponse($datas, 'Pemeliharaan berhasil diambil');
        } catch (\Exception $e) {
            return $this->errorResponse($e->getMessage() . ' ' . $e->getLine());
        }
    }

    function storePerjadin(Request $request)
    {
        $validate = Validator::make($request->all(), [
            'periode' => 'required|exists:ref_periode,id',
            'year' => 'nullable|numeric',
            'data.*.instance_id' => 'required|exists:instances,id',
            'data.*.kode_rekening_id' => 'required|exists:ref_kode_rekening_complete,id',
        ], [], [
            'periode' => 'Periode',
            'data.*.instance_id' => 'Instance ID',
            'data.*.kode_rekening_id' => 'Kode Rekening ID',
        ]);
        if ($validate->fails()) {
            return $this->validationResponse($validate->errors());
        }

        DB::beginTransaction();
        try {
            $dateYear = $request->year ?? date('Y');
            $datas = $request->data;
            foreach ($datas as $input) {
                if ($input['id']) {
                    DB::table('acc_blo_perjadin')
                        ->where('id', $input['id'])
                        ->update([
                            'realisasi_belanja' => $input['realisasi_belanja'],
                            'saldo_awal' => $input['saldo_awal'],
                            'belanja_dibayar_dimuka_akhir' => $input['belanja_dibayar_dimuka_akhir'],
                            'hutang' => $input['hutang'],
                            'hibah' => $input['hibah'],
                            'reklas_tambah_dari_rekening' => $input['reklas_tambah_dari_rekening'],
                            'reklas_tambah_dari_modal' => $input['reklas_tambah_dari_modal'],
                            'plus_jukor' => $input['plus_jukor'],
                            'saldo_akhir' => $input['saldo_akhir'],
                            'beban_tahun_lalu' => $input['beban_tahun_lalu'],
                            'belanja_dibayar_dimuka_awal' => $input['belanja_dibayar_dimuka_awal'],
                            'pembayaran_hutang' => $input['pembayaran_hutang'],
                            'reklas_kurang_ke_rekening' => $input['reklas_kurang_ke_rekening'],
                            'reklas_kurang_ke_aset' => $input['reklas_kurang_ke_aset'],
                            'atribusi' => $input['atribusi'],
                            'min_jukor' => $input['min_jukor'],
                            'beban_lo' => $input['beban_lo'],
                        ]);
                } else {
                    DB::table('acc_blo_perjadin')->insert([
                        'periode_id' => $request->periode,
                        'year' => $dateYear,
                        'instance_id' => $input['instance_id'],
                        'kode_rekening_id' => $input['kode_rekening_id'],
                        'realisasi_belanja' => $input['realisasi_belanja'],
                        'saldo_awal' => $input['saldo_awal'],
                        'belanja_dibayar_dimuka_akhir' => $input['belanja_dibayar_dimuka_akhir'],
                        'hutang' => $input['hutang'],
                        'hibah' => $input['hibah'],
                        'reklas_tambah_dari_rekening' => $input['reklas_tambah_dari_rekening'],
                        'reklas_tambah_dari_modal' => $input['reklas_tambah_dari_modal'],
                        'plus_jukor' => $input['plus_jukor'],
                        'saldo_akhir' => $input['saldo_akhir'],
                        'beban_tahun_lalu' => $input['beban_tahun_lalu'],
                        'belanja_dibayar_dimuka_awal' => $input['belanja_dibayar_dimuka_awal'],
                        'pembayaran_hutang' => $input['pembayaran_hutang'],
                        'reklas_kurang_ke_rekening' => $input['reklas_kurang_ke_rekening'],
                        'reklas_kurang_ke_aset' => $input['reklas_kurang_ke_aset'],
                        'atribusi' => $input['atribusi'],
                        'min_jukor' => $input['min_jukor'],
                        'beban_lo' => $input['beban_lo'],
                    ]);
                }
            }

            DB::commit();
            return $this->successResponse([], 'Pemeliharaan berhasil disimpan');
        } catch (\Exception $e) {
            DB::rollBack();
            return $this->errorResponse($e->getMessage() . ' ' . $e->getLine());
        }
    }

    function deletePerjadin(Request $request)
    {
        $validate = Validator::make($request->all(), [
            'id' => 'required|exists:acc_blo_perjadin,id'
        ], [], [
            'id' => 'ID'
        ]);
        if ($validate->fails()) {
            return $this->validationResponse($validate->errors());
        }

        DB::beginTransaction();
        try {
            DB::table('acc_blo_perjadin')
                ->where('id', $request->id)
                ->delete();

            DB::commit();
            return $this->successResponse([], 'Pemeliharaan berhasil dihapus');
        } catch (\Exception $e) {
            DB::rollBack();
            return $this->errorResponse($e->getMessage() . ' ' . $e->getLine());
        }
    }
    // Perjadin End

    // UangJasaDiserahkan Start
    function getUangJasaDiserahkan(Request $request)
    {
        $validate = Validator::make($request->all(), [
            'instance' => 'nullable|exists:instances,id',
            'year' => 'nullable|integer',
            'periode' => 'required|exists:ref_periode,id'
        ], [], [
            'instance' => 'Instance ID',
            'periode' => 'Periode'
        ]);
        if ($validate->fails()) {
            return $this->validationResponse($validate->errors());
        }

        try {
            $datas = [];
            if (!$request->instance) {
                $arrData = DB::table('acc_blo_uang_jasa_diserahkan')
                    ->where('periode_id', $request->periode)
                    ->where('year', $request->year)
                    ->pluck('kode_rekening_id');
                $arrData = collect($arrData)->unique()->values();

                $arrRekenings = DB::table('ref_kode_rekening_complete')
                    ->whereIn('id', $arrData)
                    ->orderBy('fullcode')
                    ->get();
                foreach ($arrRekenings as $rek) {
                    $data = DB::table('acc_blo_uang_jasa_diserahkan')
                        ->where('periode_id', $request->periode)
                        ->where('year', $request->year)
                        ->where('kode_rekening_id', $rek->id)
                        ->get();
                    $plusTotal = ($data->sum('saldo_awal') ?? 0) + ($data->sum('belanja_dibayar_dimuka_akhir') ?? 0) + ($data->sum('hutang') ?? 0) + ($data->sum('hibah') ?? 0) + ($data->sum('reklas_tambah_dari_rekening') ?? 0) + ($data->sum('reklas_tambah_dari_modal') ?? 0) + ($data->sum('plus_jukor') ?? 0);
                    $minTotal = ($data->sum('saldo_akhir') ?? 0) + ($data->sum('beban_tahun_lalu') ?? 0) + ($data->sum('belanja_dibayar_dimuka_awal') ?? 0) + ($data->sum('pembayaran_hutang') ?? 0) + ($data->sum('reklas_kurang_ke_rekening') ?? 0) + ($data->sum('reklas_kurang_ke_aset') ?? 0) + ($data->sum('atribusi') ?? 0) + ($data->sum('min_jukor') ?? 0);

                    $bebanLo = ($data->sum('realisasi_belanja') + $plusTotal) - $minTotal;
                    $datas[] = [
                        'id' => null,
                        'periode_id' => $request->periode,
                        'year' => $request->year,
                        'instance_id' => null,
                        'kode_rekening_id' => $rek->id,
                        'kode_rekening_fullcode' => $rek->fullcode,
                        'kode_rekening_name' => $rek->name,
                        'realisasi_belanja' => $data->sum('realisasi_belanja'),
                        'saldo_awal' => $data->sum('saldo_awal'),
                        'belanja_dibayar_dimuka_akhir' => $data->sum('belanja_dibayar_dimuka_akhir'),
                        'hutang' => $data->sum('hutang'),
                        'hibah' => $data->sum('hibah'),
                        'reklas_tambah_dari_rekening' => $data->sum('reklas_tambah_dari_rekening'),
                        'reklas_tambah_dari_modal' => $data->sum('reklas_tambah_dari_modal'),
                        'plus_jukor' => $data->sum('plus_jukor'),
                        'plus_total' => $plusTotal ?? 0,
                        'saldo_akhir' => $data->sum('saldo_akhir'),
                        'beban_tahun_lalu' => $data->sum('beban_tahun_lalu'),
                        'belanja_dibayar_dimuka_awal' => $data->sum('belanja_dibayar_dimuka_awal'),
                        'pembayaran_hutang' => $data->sum('pembayaran_hutang'),
                        'reklas_kurang_ke_rekening' => $data->sum('reklas_kurang_ke_rekening'),
                        'reklas_kurang_ke_aset' => $data->sum('reklas_kurang_ke_aset'),
                        'atribusi' => $data->sum('atribusi'),
                        'min_jukor' => $data->sum('min_jukor'),
                        'beban_lo' => $bebanLo ?? 0,
                        'kode_rekening' => $rek->fullcode,
                        'min_total' => $minTotal ?? 0,
                    ];
                }
            } elseif ($request->instance) {
                $arrLra = DB::table('acc_lra')
                    ->where('periode_id', $request->periode)
                    ->where('year', $request->year ?? date('Y'))
                    ->where('instance_id', $request->instance)
                    ->where('kode_rekening', 'ILIKE', '5.1.02.05.%')
                    ->whereRaw('LENGTH(kode_rekening) >= ?', [17])
                    ->orderBy('kode_rekening')
                    ->get();
                foreach ($arrLra as $lra) {
                    $kodeRekeningId = DB::table('ref_kode_rekening_complete')
                        ->where('fullcode', $lra->kode_rekening)
                        ->first()->id ?? null;
                    $data = DB::table('acc_blo_uang_jasa_diserahkan')
                        ->where('periode_id', $request->periode)
                        ->where('year', $request->year ?? date('Y'))
                        ->where('instance_id', $request->instance)
                        ->where('kode_rekening_id', $kodeRekeningId)
                        ->first();
                    if (!$data && $kodeRekeningId) {
                        $HutangBaru = DB::table('acc_htb_hutang_baru')
                            ->where('periode_id', $request->periode)
                            ->where('year', $request->year)
                            ->where('instance_id', $request->instance)
                            ->where('kode_rekening_id', $kodeRekeningId)
                            ->get()->sum('uang_jasa_diserahkan') ?? 0;
                        $PembayaranHutang = DB::table('acc_htb_pembayaran_hutang')
                            ->where('periode_id', $request->periode)
                            ->where('year', $request->year)
                            ->where('instance_id', $request->instance)
                            ->where('kode_rekening_id', $kodeRekeningId)
                            ->get()->sum('uang_jasa_diserahkan') ?? 0;

                        DB::table('acc_blo_uang_jasa_diserahkan')
                            ->insert([
                                'periode_id' => $request->periode,
                                'year' => $request->year ?? date('Y'),
                                'instance_id' => $request->instance,
                                'kode_rekening_id' => $kodeRekeningId,
                                'realisasi_belanja' => $lra->realisasi,
                                'saldo_awal' => 0,
                                'belanja_dibayar_dimuka_akhir' => 0,
                                'hutang' => $HutangBaru ?? 0,
                                'hibah' => 0,
                                'reklas_tambah_dari_rekening' => 0,
                                'reklas_tambah_dari_modal' => 0,
                                'plus_jukor' => 0,
                                'saldo_akhir' => 0,
                                'beban_tahun_lalu' => 0,
                                'belanja_dibayar_dimuka_awal' => 0,
                                'pembayaran_hutang' => $PembayaranHutang ?? 0,
                                'reklas_kurang_ke_rekening' => 0,
                                'reklas_kurang_ke_aset' => 0,
                                'atribusi' => 0,
                                'min_jukor' => 0,
                                'beban_lo' => 0,
                            ]);
                    }
                    if ($data) {
                        $HutangBaru = DB::table('acc_htb_hutang_baru')
                            ->where('periode_id', $data->periode_id)
                            ->where('year', $data->year)
                            ->where('instance_id', $data->instance_id)
                            ->where('kode_rekening_id', $data->kode_rekening_id)
                            ->get()->sum('uang_jasa_diserahkan') ?? 0;
                        $data->hutang = $HutangBaru;
                        $PembayaranHutang = DB::table('acc_htb_pembayaran_hutang')
                            ->where('periode_id', $data->periode_id)
                            ->where('year', $data->year)
                            ->where('instance_id', $data->instance_id)
                            ->where('kode_rekening_id', $data->kode_rekening_id)
                            ->get()->sum('uang_jasa_diserahkan') ?? 0;
                        $data->pembayaran_hutang = $PembayaranHutang;

                        $plusTotalKeys = ['saldo_awal', 'belanja_dibayar_dimuka_akhir', 'hutang', 'hibah', 'reklas_tambah_dari_rekening', 'reklas_tambah_dari_modal', 'plus_jukor'];
                        $plusTotal = 0;
                        foreach ($plusTotalKeys as $key) {
                            $plusTotal += floatval($data->$key);
                        }
                        $minTotalKeys = ['saldo_akhir', 'beban_tahun_lalu', 'belanja_dibayar_dimuka_awal', 'pembayaran_hutang', 'reklas_kurang_ke_rekening', 'reklas_kurang_ke_aset', 'atribusi', 'min_jukor'];
                        $minTotal = 0;
                        foreach ($minTotalKeys as $key) {
                            $minTotal += floatval($data->$key);
                        }
                        $bebanLo = ($data->realisasi_belanja + $plusTotal) - $minTotal;

                        // Tidak ada Data Hutang
                        $instance = DB::table('instances')
                            ->where('id', $request->instance)
                            ->first();
                        $kodeRekening = DB::table('ref_kode_rekening_complete')
                            ->where('id', $data->kode_rekening_id)
                            ->first();
                        $datas[] = [
                            'id' => $data->id,
                            'periode_id' => $data->periode_id,
                            'year' => $data->year,
                            'instance_id' => $data->instance_id,
                            'instance_name' => $instance->name,
                            'kode_rekening_id' => $data->kode_rekening_id,
                            'kode_rekening_fullcode' => $kodeRekening->fullcode,
                            'kode_rekening_name' => $kodeRekening->name,
                            'realisasi_belanja' => $data->realisasi_belanja,
                            'saldo_awal' => $data->saldo_awal,
                            'belanja_dibayar_dimuka_akhir' => $data->belanja_dibayar_dimuka_akhir,
                            'hutang' => $data->hutang,
                            'hibah' => $data->hibah,
                            'reklas_tambah_dari_rekening' => $data->reklas_tambah_dari_rekening,
                            'reklas_tambah_dari_modal' => $data->reklas_tambah_dari_modal,
                            'plus_jukor' => $data->plus_jukor,
                            'plus_total' => $plusTotal ?? 0,
                            'saldo_akhir' => $data->saldo_akhir,
                            'beban_tahun_lalu' => $data->beban_tahun_lalu,
                            'belanja_dibayar_dimuka_awal' => $data->belanja_dibayar_dimuka_awal,
                            'pembayaran_hutang' => $data->pembayaran_hutang,
                            'reklas_kurang_ke_rekening' => $data->reklas_kurang_ke_rekening,
                            'reklas_kurang_ke_aset' => $data->reklas_kurang_ke_aset,
                            'atribusi' => $data->atribusi,
                            'min_jukor' => $data->min_jukor,
                            'beban_lo' => $bebanLo ?? 0,
                            'kode_rekening' => $lra->kode_rekening,
                            'min_total' => $minTotal ?? 0,
                        ];
                    }
                }
            }

            return $this->successResponse($datas, 'Pemeliharaan berhasil diambil');
        } catch (\Exception $e) {
            return $this->errorResponse($e->getMessage() . ' ' . $e->getLine());
        }
    }

    function storeUangJasaDiserahkan(Request $request)
    {
        $validate = Validator::make($request->all(), [
            'periode' => 'required|exists:ref_periode,id',
            'year' => 'nullable|numeric',
            'data.*.instance_id' => 'required|exists:instances,id',
            'data.*.kode_rekening_id' => 'required|exists:ref_kode_rekening_complete,id',
        ], [], [
            'periode' => 'Periode',
            'data.*.instance_id' => 'Instance ID',
            'data.*.kode_rekening_id' => 'Kode Rekening ID',
        ]);
        if ($validate->fails()) {
            return $this->validationResponse($validate->errors());
        }

        DB::beginTransaction();
        try {
            $dateYear = $request->year ?? date('Y');
            $datas = $request->data;
            foreach ($datas as $input) {
                if ($input['id']) {
                    DB::table('acc_blo_uang_jasa_diserahkan')
                        ->where('id', $input['id'])
                        ->update([
                            'realisasi_belanja' => $input['realisasi_belanja'],
                            'saldo_awal' => $input['saldo_awal'],
                            'belanja_dibayar_dimuka_akhir' => $input['belanja_dibayar_dimuka_akhir'],
                            'hutang' => $input['hutang'],
                            'hibah' => $input['hibah'],
                            'reklas_tambah_dari_rekening' => $input['reklas_tambah_dari_rekening'],
                            'reklas_tambah_dari_modal' => $input['reklas_tambah_dari_modal'],
                            'plus_jukor' => $input['plus_jukor'],
                            'saldo_akhir' => $input['saldo_akhir'],
                            'beban_tahun_lalu' => $input['beban_tahun_lalu'],
                            'belanja_dibayar_dimuka_awal' => $input['belanja_dibayar_dimuka_awal'],
                            'pembayaran_hutang' => $input['pembayaran_hutang'],
                            'reklas_kurang_ke_rekening' => $input['reklas_kurang_ke_rekening'],
                            'reklas_kurang_ke_aset' => $input['reklas_kurang_ke_aset'],
                            'atribusi' => $input['atribusi'],
                            'min_jukor' => $input['min_jukor'],
                            'beban_lo' => $input['beban_lo'],
                        ]);
                } else {
                    DB::table('acc_blo_uang_jasa_diserahkan')->insert([
                        'periode_id' => $request->periode,
                        'year' => $dateYear,
                        'instance_id' => $input['instance_id'],
                        'kode_rekening_id' => $input['kode_rekening_id'],
                        'realisasi_belanja' => $input['realisasi_belanja'],
                        'saldo_awal' => $input['saldo_awal'],
                        'belanja_dibayar_dimuka_akhir' => $input['belanja_dibayar_dimuka_akhir'],
                        'hutang' => $input['hutang'],
                        'hibah' => $input['hibah'],
                        'reklas_tambah_dari_rekening' => $input['reklas_tambah_dari_rekening'],
                        'reklas_tambah_dari_modal' => $input['reklas_tambah_dari_modal'],
                        'plus_jukor' => $input['plus_jukor'],
                        'saldo_akhir' => $input['saldo_akhir'],
                        'beban_tahun_lalu' => $input['beban_tahun_lalu'],
                        'belanja_dibayar_dimuka_awal' => $input['belanja_dibayar_dimuka_awal'],
                        'pembayaran_hutang' => $input['pembayaran_hutang'],
                        'reklas_kurang_ke_rekening' => $input['reklas_kurang_ke_rekening'],
                        'reklas_kurang_ke_aset' => $input['reklas_kurang_ke_aset'],
                        'atribusi' => $input['atribusi'],
                        'min_jukor' => $input['min_jukor'],
                        'beban_lo' => $input['beban_lo'],
                    ]);
                }
            }

            DB::commit();
            return $this->successResponse([], 'Pemeliharaan berhasil disimpan');
        } catch (\Exception $e) {
            DB::rollBack();
            return $this->errorResponse($e->getMessage() . ' ' . $e->getLine());
        }
    }

    function deleteUangJasaDiserahkan(Request $request)
    {
        $validate = Validator::make($request->all(), [
            'id' => 'required|exists:acc_blo_uang_jasa_diserahkan,id'
        ], [], [
            'id' => 'ID'
        ]);
        if ($validate->fails()) {
            return $this->validationResponse($validate->errors());
        }

        DB::beginTransaction();
        try {
            DB::table('acc_blo_uang_jasa_diserahkan')
                ->where('id', $request->id)
                ->delete();

            DB::commit();
            return $this->successResponse([], 'Pemeliharaan berhasil dihapus');
        } catch (\Exception $e) {
            DB::rollBack();
            return $this->errorResponse($e->getMessage() . ' ' . $e->getLine());
        }
    }
    // UangJasaDiserahkan End

    // Hibah Start
    function getHibah(Request $request)
    {
        $validate = Validator::make($request->all(), [
            'instance' => 'nullable|exists:instances,id',
            'year' => 'nullable|integer',
            'periode' => 'required|exists:ref_periode,id'
        ], [], [
            'instance' => 'Instance ID',
            'periode' => 'Periode'
        ]);
        if ($validate->fails()) {
            return $this->validationResponse($validate->errors());
        }

        try {
            $datas = [];
            if (!$request->instance) {
                $arrData = DB::table('acc_blo_hibah')
                    ->where('periode_id', $request->periode)
                    ->where('year', $request->year)
                    ->pluck('kode_rekening_id');
                $arrData = collect($arrData)->unique()->values();

                $arrRekenings = DB::table('ref_kode_rekening_complete')
                    ->whereIn('id', $arrData)
                    ->orderBy('fullcode')
                    ->get();
                foreach ($arrRekenings as $rek) {
                    $data = DB::table('acc_blo_hibah')
                        ->where('periode_id', $request->periode)
                        ->where('year', $request->year)
                        ->where('kode_rekening_id', $rek->id)
                        ->get();
                    $plusTotal = ($data->sum('saldo_awal') ?? 0) + ($data->sum('belanja_dibayar_dimuka_akhir') ?? 0) + ($data->sum('hutang') ?? 0) + ($data->sum('hibah') ?? 0) + ($data->sum('reklas_tambah_dari_rekening') ?? 0) + ($data->sum('reklas_tambah_dari_modal') ?? 0) + ($data->sum('plus_jukor') ?? 0);
                    $minTotal = ($data->sum('saldo_akhir') ?? 0) + ($data->sum('beban_tahun_lalu') ?? 0) + ($data->sum('belanja_dibayar_dimuka_awal') ?? 0) + ($data->sum('pembayaran_hutang') ?? 0) + ($data->sum('reklas_kurang_ke_rekening') ?? 0) + ($data->sum('reklas_kurang_ke_aset') ?? 0) + ($data->sum('atribusi') ?? 0) + ($data->sum('min_jukor') ?? 0);

                    $bebanLo = ($data->sum('realisasi_belanja') + $plusTotal) - $minTotal;
                    $datas[] = [
                        'id' => null,
                        'periode_id' => $request->periode,
                        'year' => $request->year,
                        'instance_id' => null,
                        'kode_rekening_id' => $rek->id,
                        'kode_rekening_fullcode' => $rek->fullcode,
                        'kode_rekening_name' => $rek->name,
                        'realisasi_belanja' => $data->sum('realisasi_belanja'),
                        'saldo_awal' => $data->sum('saldo_awal'),
                        'belanja_dibayar_dimuka_akhir' => $data->sum('belanja_dibayar_dimuka_akhir'),
                        'hutang' => $data->sum('hutang'),
                        'hibah' => $data->sum('hibah'),
                        'reklas_tambah_dari_rekening' => $data->sum('reklas_tambah_dari_rekening'),
                        'reklas_tambah_dari_modal' => $data->sum('reklas_tambah_dari_modal'),
                        'plus_jukor' => $data->sum('plus_jukor'),
                        'plus_total' => $plusTotal ?? 0,
                        'saldo_akhir' => $data->sum('saldo_akhir'),
                        'beban_tahun_lalu' => $data->sum('beban_tahun_lalu'),
                        'belanja_dibayar_dimuka_awal' => $data->sum('belanja_dibayar_dimuka_awal'),
                        'pembayaran_hutang' => $data->sum('pembayaran_hutang'),
                        'reklas_kurang_ke_rekening' => $data->sum('reklas_kurang_ke_rekening'),
                        'reklas_kurang_ke_aset' => $data->sum('reklas_kurang_ke_aset'),
                        'atribusi' => $data->sum('atribusi'),
                        'min_jukor' => $data->sum('min_jukor'),
                        'beban_lo' => $bebanLo ?? 0,
                        'kode_rekening' => $rek->fullcode,
                        'min_total' => $minTotal ?? 0,
                    ];
                }
            } elseif ($request->instance) {
                $arrLra = DB::table('acc_lra')
                    ->where('periode_id', $request->periode)
                    ->where('year', $request->year ?? date('Y'))
                    ->where('instance_id', $request->instance)
                    ->where('kode_rekening', 'ILIKE', '5.1.05.%')
                    ->whereRaw('LENGTH(kode_rekening) >= ?', [17])
                    ->orderBy('kode_rekening')
                    ->get();
                foreach ($arrLra as $lra) {
                    $kodeRekeningId = DB::table('ref_kode_rekening_complete')
                        ->where('fullcode', $lra->kode_rekening)
                        ->first()->id ?? null;
                    $data = DB::table('acc_blo_hibah')
                        ->where('periode_id', $request->periode)
                        ->where('year', $request->year ?? date('Y'))
                        ->where('instance_id', $request->instance)
                        ->where('kode_rekening_id', $kodeRekeningId)
                        ->first();
                    if (!$data && $kodeRekeningId) {
                        $HutangBaru = DB::table('acc_htb_hutang_baru')
                            ->where('periode_id', $request->periode)
                            ->where('year', $request->year)
                            ->where('instance_id', $request->instance)
                            ->where('kode_rekening_id', $kodeRekeningId)
                            ->get()->sum('hibah') ?? 0;
                        $PembayaranHutang = DB::table('acc_htb_pembayaran_hutang')
                            ->where('periode_id', $request->periode)
                            ->where('year', $request->year)
                            ->where('instance_id', $request->instance)
                            ->where('kode_rekening_id', $kodeRekeningId)
                            ->get()->sum('hibah') ?? 0;

                        DB::table('acc_blo_hibah')
                            ->insert([
                                'periode_id' => $request->periode,
                                'year' => $request->year ?? date('Y'),
                                'instance_id' => $request->instance,
                                'kode_rekening_id' => $kodeRekeningId,
                                'realisasi_belanja' => $lra->realisasi,
                                'saldo_awal' => 0,
                                'belanja_dibayar_dimuka_akhir' => 0,
                                'hutang' => $HutangBaru ?? 0,
                                'hibah' => 0,
                                'reklas_tambah_dari_rekening' => 0,
                                'reklas_tambah_dari_modal' => 0,
                                'plus_jukor' => 0,
                                'saldo_akhir' => 0,
                                'beban_tahun_lalu' => 0,
                                'belanja_dibayar_dimuka_awal' => 0,
                                'pembayaran_hutang' => $PembayaranHutang ?? 0,
                                'reklas_kurang_ke_rekening' => 0,
                                'reklas_kurang_ke_aset' => 0,
                                'atribusi' => 0,
                                'min_jukor' => 0,
                                'beban_lo' => 0,
                            ]);
                    }
                    if ($data) {
                        $HutangBaru = DB::table('acc_htb_hutang_baru')
                            ->where('periode_id', $data->periode_id)
                            ->where('year', $data->year)
                            ->where('instance_id', $data->instance_id)
                            ->where('kode_rekening_id', $data->kode_rekening_id)
                            ->get()->sum('hibah') ?? 0;
                        $data->hutang = $HutangBaru;
                        $PembayaranHutang = DB::table('acc_htb_pembayaran_hutang')
                            ->where('periode_id', $data->periode_id)
                            ->where('year', $data->year)
                            ->where('instance_id', $data->instance_id)
                            ->where('kode_rekening_id', $data->kode_rekening_id)
                            ->get()->sum('hibah') ?? 0;
                        $data->pembayaran_hutang = $PembayaranHutang;

                        $plusTotalKeys = ['saldo_awal', 'belanja_dibayar_dimuka_akhir', 'hutang', 'hibah', 'reklas_tambah_dari_rekening', 'reklas_tambah_dari_modal', 'plus_jukor'];
                        $plusTotal = 0;
                        foreach ($plusTotalKeys as $key) {
                            $plusTotal += floatval($data->$key);
                        }
                        $minTotalKeys = ['saldo_akhir', 'beban_tahun_lalu', 'belanja_dibayar_dimuka_awal', 'pembayaran_hutang', 'reklas_kurang_ke_rekening', 'reklas_kurang_ke_aset', 'atribusi', 'min_jukor'];
                        $minTotal = 0;
                        foreach ($minTotalKeys as $key) {
                            $minTotal += floatval($data->$key);
                        }
                        $bebanLo = ($data->realisasi_belanja + $plusTotal) - $minTotal;

                        DB::table('acc_blo_hibah')
                            ->where('id', $data->id)
                            ->update([
                                'realisasi_belanja' => $lra->realisasi,
                                'hutang' => $HutangBaru,
                                'pembayaran_hutang' => $PembayaranHutang,
                                'beban_lo' => $bebanLo
                            ]);

                        $instance = DB::table('instances')
                            ->where('id', $request->instance)
                            ->first();
                        $kodeRekening = DB::table('ref_kode_rekening_complete')
                            ->where('id', $data->kode_rekening_id)
                            ->first();
                        $datas[] = [
                            'id' => $data->id,
                            'periode_id' => $data->periode_id,
                            'year' => $data->year,
                            'instance_id' => $data->instance_id,
                            'instance_name' => $instance->name,
                            'kode_rekening_id' => $data->kode_rekening_id,
                            'kode_rekening_fullcode' => $kodeRekening->fullcode,
                            'kode_rekening_name' => $kodeRekening->name,
                            'realisasi_belanja' => $data->realisasi_belanja,
                            'saldo_awal' => $data->saldo_awal,
                            'belanja_dibayar_dimuka_akhir' => $data->belanja_dibayar_dimuka_akhir,
                            'hutang' => $HutangBaru ?? $data->hutang,
                            'hibah' => $data->hibah,
                            'reklas_tambah_dari_rekening' => $data->reklas_tambah_dari_rekening,
                            'reklas_tambah_dari_modal' => $data->reklas_tambah_dari_modal,
                            'plus_jukor' => $data->plus_jukor,
                            'plus_total' => $plusTotal ?? 0,
                            'saldo_akhir' => $data->saldo_akhir,
                            'beban_tahun_lalu' => $data->beban_tahun_lalu,
                            'belanja_dibayar_dimuka_awal' => $data->belanja_dibayar_dimuka_awal,
                            'pembayaran_hutang' => $data->pembayaran_hutang,
                            'reklas_kurang_ke_rekening' => $data->reklas_kurang_ke_rekening,
                            'reklas_kurang_ke_aset' => $data->reklas_kurang_ke_aset,
                            'atribusi' => $data->atribusi,
                            'min_jukor' => $data->min_jukor,
                            'beban_lo' => $bebanLo ?? 0,
                            'kode_rekening' => $lra->kode_rekening,
                            'min_total' => $minTotal ?? 0,
                        ];
                    }
                }
                $unListed = DB::table('acc_blo_hibah')
                    ->where('periode_id', $request->periode)
                    ->where('year', $request->year)
                    ->where('instance_id', $request->instance)
                    ->whereNotIn('id', collect($datas)->pluck('id')->toArray())
                    ->get();
                foreach ($unListed as $un) {
                    $instance = DB::table('instances')
                        ->where('id', $request->instance)
                        ->first();
                    $kodeRekening = DB::table('ref_kode_rekening_complete')
                        ->where('id', $un->kode_rekening_id)
                        ->first();
                    $datas[] = [
                        'id' => $un->id,
                        'periode_id' => $un->periode_id,
                        'year' => $un->year,
                        'instance_id' => $un->instance_id,
                        'instance_name' => $instance->name,
                        'kode_rekening_id' => $un->kode_rekening_id,
                        'kode_rekening_fullcode' => $kodeRekening->fullcode,
                        'kode_rekening_name' => $kodeRekening->name,
                        'realisasi_belanja' => $un->realisasi_belanja,
                        'saldo_awal' => $un->saldo_awal,
                        'belanja_dibayar_dimuka_akhir' => $un->belanja_dibayar_dimuka_akhir,
                        'hutang' => $un->hutang,
                        'hibah' => $un->hibah,
                        'reklas_tambah_dari_rekening' => $un->reklas_tambah_dari_rekening,
                        'reklas_tambah_dari_modal' => $un->reklas_tambah_dari_modal,
                        'plus_jukor' => $un->plus_jukor,
                        'plus_total' => $un->saldo_awal + $un->belanja_dibayar_dimuka_akhir + $un->hutang + $un->hibah + $un->reklas_tambah_dari_rekening + $un->reklas_tambah_dari_modal + $un->plus_jukor,
                        'saldo_akhir' => $un->saldo_akhir,
                        'beban_tahun_lalu' => $un->beban_tahun_lalu,
                        'belanja_dibayar_dimuka_awal' => $un->belanja_dibayar_dimuka_awal,
                        'pembayaran_hutang' => $un->pembayaran_hutang,
                        'reklas_kurang_ke_rekening' => $un->reklas_kurang_ke_rekening,
                        'reklas_kurang_ke_aset' => $un->reklas_kurang_ke_aset,
                        'atribusi' => $un->atribusi,
                        'min_jukor' => $un->min_jukor,
                        'beban_lo' => $un->beban_lo,
                        'kode_rekening' => DB::table('ref_kode_rekening_complete')->where('id', $un->kode_rekening_id)->first()->fullcode,
                        'min_total' => $un->beban_tahun_lalu + $un->belanja_dibayar_dimuka_awal + $un->pembayaran_hutang + $un->reklas_kurang_ke_rekening + $un->reklas_kurang_ke_aset + $un->atribusi + $un->min_jukor,
                    ];
                }
            }

            return $this->successResponse($datas, 'Pemeliharaan berhasil diambil');
        } catch (\Exception $e) {
            return $this->errorResponse($e->getMessage() . ' ' . $e->getLine());
        }
    }

    function storeHibah(Request $request)
    {
        $validate = Validator::make($request->all(), [
            'periode' => 'required|exists:ref_periode,id',
            'year' => 'nullable|numeric',
            'data.*.instance_id' => 'required|exists:instances,id',
            'data.*.kode_rekening_id' => 'required|exists:ref_kode_rekening_complete,id',
        ], [], [
            'periode' => 'Periode',
            'data.*.instance_id' => 'Instance ID',
            'data.*.kode_rekening_id' => 'Kode Rekening ID',
        ]);
        if ($validate->fails()) {
            return $this->validationResponse($validate->errors());
        }

        DB::beginTransaction();
        try {
            $dateYear = $request->year ?? date('Y');
            $datas = $request->data;
            foreach ($datas as $input) {
                if ($input['id']) {
                    DB::table('acc_blo_hibah')
                        ->where('id', $input['id'])
                        ->update([
                            'realisasi_belanja' => $input['realisasi_belanja'],
                            'saldo_awal' => $input['saldo_awal'],
                            'belanja_dibayar_dimuka_akhir' => $input['belanja_dibayar_dimuka_akhir'],
                            'hutang' => $input['hutang'],
                            'hibah' => $input['hibah'],
                            'reklas_tambah_dari_rekening' => $input['reklas_tambah_dari_rekening'],
                            'reklas_tambah_dari_modal' => $input['reklas_tambah_dari_modal'],
                            'plus_jukor' => $input['plus_jukor'],
                            'saldo_akhir' => $input['saldo_akhir'],
                            'beban_tahun_lalu' => $input['beban_tahun_lalu'],
                            'belanja_dibayar_dimuka_awal' => $input['belanja_dibayar_dimuka_awal'],
                            'pembayaran_hutang' => $input['pembayaran_hutang'],
                            'reklas_kurang_ke_rekening' => $input['reklas_kurang_ke_rekening'],
                            'reklas_kurang_ke_aset' => $input['reklas_kurang_ke_aset'],
                            'atribusi' => $input['atribusi'],
                            'min_jukor' => $input['min_jukor'],
                            'beban_lo' => $input['beban_lo'],
                        ]);
                } else {
                    DB::table('acc_blo_hibah')->insert([
                        'periode_id' => $request->periode,
                        'year' => $dateYear,
                        'instance_id' => $input['instance_id'],
                        'kode_rekening_id' => $input['kode_rekening_id'],
                        'realisasi_belanja' => $input['realisasi_belanja'],
                        'saldo_awal' => $input['saldo_awal'],
                        'belanja_dibayar_dimuka_akhir' => $input['belanja_dibayar_dimuka_akhir'],
                        'hutang' => $input['hutang'],
                        'hibah' => $input['hibah'],
                        'reklas_tambah_dari_rekening' => $input['reklas_tambah_dari_rekening'],
                        'reklas_tambah_dari_modal' => $input['reklas_tambah_dari_modal'],
                        'plus_jukor' => $input['plus_jukor'],
                        'saldo_akhir' => $input['saldo_akhir'],
                        'beban_tahun_lalu' => $input['beban_tahun_lalu'],
                        'belanja_dibayar_dimuka_awal' => $input['belanja_dibayar_dimuka_awal'],
                        'pembayaran_hutang' => $input['pembayaran_hutang'],
                        'reklas_kurang_ke_rekening' => $input['reklas_kurang_ke_rekening'],
                        'reklas_kurang_ke_aset' => $input['reklas_kurang_ke_aset'],
                        'atribusi' => $input['atribusi'],
                        'min_jukor' => $input['min_jukor'],
                        'beban_lo' => $input['beban_lo'],
                    ]);
                }
            }

            DB::commit();
            return $this->successResponse([], 'Pemeliharaan berhasil disimpan');
        } catch (\Exception $e) {
            DB::rollBack();
            return $this->errorResponse($e->getMessage() . ' ' . $e->getLine());
        }
    }

    function deleteHibah(Request $request)
    {
        $validate = Validator::make($request->all(), [
            'id' => 'required|exists:acc_blo_hibah,id'
        ], [], [
            'id' => 'ID'
        ]);
        if ($validate->fails()) {
            return $this->validationResponse($validate->errors());
        }

        DB::beginTransaction();
        try {
            DB::table('acc_blo_hibah')
                ->where('id', $request->id)
                ->delete();

            DB::commit();
            return $this->successResponse([], 'Pemeliharaan berhasil dihapus');
        } catch (\Exception $e) {
            DB::rollBack();
            return $this->errorResponse($e->getMessage() . ' ' . $e->getLine());
        }
    }
    // Hibah End

    // Subsidi Start
    function getSubsidi(Request $request)
    {
        $validate = Validator::make($request->all(), [
            'instance' => 'nullable|exists:instances,id',
            'year' => 'nullable|integer',
            'periode' => 'required|exists:ref_periode,id'
        ], [], [
            'instance' => 'Instance ID',
            'periode' => 'Periode'
        ]);
        if ($validate->fails()) {
            return $this->validationResponse($validate->errors());
        }

        try {
            $datas = [];
            if (!$request->instance) {
                $arrData = DB::table('acc_blo_subsidi')
                    ->where('periode_id', $request->periode)
                    ->where('year', $request->year)
                    ->pluck('kode_rekening_id');
                $arrData = collect($arrData)->unique()->values();

                $arrRekenings = DB::table('ref_kode_rekening_complete')
                    ->whereIn('id', $arrData)
                    ->orderBy('fullcode')
                    ->get();
                foreach ($arrRekenings as $rek) {
                    $data = DB::table('acc_blo_subsidi')
                        ->where('periode_id', $request->periode)
                        ->where('year', $request->year)
                        ->where('kode_rekening_id', $rek->id)
                        ->get();
                    $plusTotal = ($data->sum('saldo_awal') ?? 0) + ($data->sum('belanja_dibayar_dimuka_akhir') ?? 0) + ($data->sum('hutang') ?? 0) + ($data->sum('hibah') ?? 0) + ($data->sum('reklas_tambah_dari_rekening') ?? 0) + ($data->sum('reklas_tambah_dari_modal') ?? 0) + ($data->sum('plus_jukor') ?? 0);
                    $minTotal = ($data->sum('saldo_akhir') ?? 0) + ($data->sum('beban_tahun_lalu') ?? 0) + ($data->sum('belanja_dibayar_dimuka_awal') ?? 0) + ($data->sum('pembayaran_hutang') ?? 0) + ($data->sum('reklas_kurang_ke_rekening') ?? 0) + ($data->sum('reklas_kurang_ke_aset') ?? 0) + ($data->sum('atribusi') ?? 0) + ($data->sum('min_jukor') ?? 0);

                    $bebanLo = ($data->sum('realisasi_belanja') + $plusTotal) - $minTotal;
                    $datas[] = [
                        'id' => null,
                        'periode_id' => $request->periode,
                        'year' => $request->year,
                        'instance_id' => null,
                        'kode_rekening_id' => $rek->id,
                        'kode_rekening_fullcode' => $rek->fullcode,
                        'kode_rekening_name' => $rek->name,
                        'realisasi_belanja' => $data->sum('realisasi_belanja'),
                        'saldo_awal' => $data->sum('saldo_awal'),
                        'belanja_dibayar_dimuka_akhir' => $data->sum('belanja_dibayar_dimuka_akhir'),
                        'hutang' => $data->sum('hutang'),
                        'hibah' => $data->sum('hibah'),
                        'reklas_tambah_dari_rekening' => $data->sum('reklas_tambah_dari_rekening'),
                        'reklas_tambah_dari_modal' => $data->sum('reklas_tambah_dari_modal'),
                        'plus_jukor' => $data->sum('plus_jukor'),
                        'plus_total' => $plusTotal ?? 0,
                        'saldo_akhir' => $data->sum('saldo_akhir'),
                        'beban_tahun_lalu' => $data->sum('beban_tahun_lalu'),
                        'belanja_dibayar_dimuka_awal' => $data->sum('belanja_dibayar_dimuka_awal'),
                        'pembayaran_hutang' => $data->sum('pembayaran_hutang'),
                        'reklas_kurang_ke_rekening' => $data->sum('reklas_kurang_ke_rekening'),
                        'reklas_kurang_ke_aset' => $data->sum('reklas_kurang_ke_aset'),
                        'atribusi' => $data->sum('atribusi'),
                        'min_jukor' => $data->sum('min_jukor'),
                        'beban_lo' => $bebanLo ?? 0,
                        'kode_rekening' => $rek->fullcode,
                        'min_total' => $minTotal ?? 0,
                    ];
                }
            } elseif ($request->instance) {
                $arrLra = DB::table('acc_lra')
                    ->where('periode_id', $request->periode)
                    ->where('year', $request->year ?? date('Y'))
                    ->where('instance_id', $request->instance)
                    ->where('kode_rekening', 'ILIKE', '5.1.04.%')
                    ->whereRaw('LENGTH(kode_rekening) >= ?', [17])
                    ->orderBy('kode_rekening')
                    ->get();
                foreach ($arrLra as $lra) {
                    $kodeRekeningId = DB::table('ref_kode_rekening_complete')
                        ->where('fullcode', $lra->kode_rekening)
                        ->first()->id ?? null;
                    $data = DB::table('acc_blo_subsidi')
                        ->where('periode_id', $request->periode)
                        ->where('year', $request->year ?? date('Y'))
                        ->where('instance_id', $request->instance)
                        ->where('kode_rekening_id', $kodeRekeningId)
                        ->first();
                    if (!$data && $kodeRekeningId) {
                        // $hutangSubsidi = DB::table('acc_hutang_belanja')
                        //     ->where('periode_id', $request->periode)
                        //     ->where('year', $request->year)
                        //     ->where('instance_id', $request->instance)
                        //     ->where('kode_rekening_id', $kodeRekeningId)
                        //     ->get()->sum('subsidi) ?? 0;

                        DB::table('acc_blo_subsidi')
                            ->insert([
                                'periode_id' => $request->periode,
                                'year' => $request->year ?? date('Y'),
                                'instance_id' => $request->instance,
                                'kode_rekening_id' => $kodeRekeningId,
                                'realisasi_belanja' => $lra->realisasi,
                                'saldo_awal' => 0,
                                'belanja_dibayar_dimuka_akhir' => 0,
                                'hutang' => $hutangSubsidi ?? 0,
                                'hibah' => 0,
                                'reklas_tambah_dari_rekening' => 0,
                                'reklas_tambah_dari_modal' => 0,
                                'plus_jukor' => 0,
                                'saldo_akhir' => 0,
                                'beban_tahun_lalu' => 0,
                                'belanja_dibayar_dimuka_awal' => 0,
                                'pembayaran_hutang' => 0,
                                'reklas_kurang_ke_rekening' => 0,
                                'reklas_kurang_ke_aset' => 0,
                                'atribusi' => 0,
                                'min_jukor' => 0,
                                'beban_lo' => 0,
                            ]);
                    }
                    if ($data) {
                        // $hutangSubsidi = DB::table('acc_hutang_belanja')
                        //     ->where('periode_id', $data->periode_id)
                        //     ->where('year', $data->year)
                        //     ->where('instance_id', $data->instance_id)
                        //     ->where('kode_rekening_id', $data->kode_rekening_id)
                        //     ->get()->sum('subsidi) ?? 0;
                        // $data->hutang = $hutangSubsidi;

                        $plusTotalKeys = ['saldo_awal', 'belanja_dibayar_dimuka_akhir', 'hutang', 'hibah', 'reklas_tambah_dari_rekening', 'reklas_tambah_dari_modal', 'plus_jukor'];
                        $plusTotal = 0;
                        foreach ($plusTotalKeys as $key) {
                            $plusTotal += floatval($data->$key);
                        }
                        $minTotalKeys = ['saldo_akhir', 'beban_tahun_lalu', 'belanja_dibayar_dimuka_awal', 'pembayaran_hutang', 'reklas_kurang_ke_rekening', 'reklas_kurang_ke_aset', 'atribusi', 'min_jukor'];
                        $minTotal = 0;
                        foreach ($minTotalKeys as $key) {
                            $minTotal += floatval($data->$key);
                        }
                        $bebanLo = ($data->realisasi_belanja + $plusTotal) - $minTotal;

                        $instance = DB::table('instances')
                            ->where('id', $request->instance)
                            ->first();
                        $kodeRekening = DB::table('ref_kode_rekening_complete')
                            ->where('id', $data->kode_rekening_id)
                            ->first();
                        $datas[] = [
                            'id' => $data->id,
                            'periode_id' => $data->periode_id,
                            'year' => $data->year,
                            'instance_id' => $data->instance_id,
                            'instance_name' => $instance->name,
                            'kode_rekening_id' => $data->kode_rekening_id,
                            'kode_rekening_fullcode' => $kodeRekening->fullcode,
                            'kode_rekening_name' => $kodeRekening->name,
                            'realisasi_belanja' => $data->realisasi_belanja,
                            'saldo_awal' => $data->saldo_awal,
                            'belanja_dibayar_dimuka_akhir' => $data->belanja_dibayar_dimuka_akhir,
                            'hutang' => $data->hutang,
                            'hibah' => $data->hibah,
                            'reklas_tambah_dari_rekening' => $data->reklas_tambah_dari_rekening,
                            'reklas_tambah_dari_modal' => $data->reklas_tambah_dari_modal,
                            'plus_jukor' => $data->plus_jukor,
                            'plus_total' => $plusTotal ?? 0,
                            'saldo_akhir' => $data->saldo_akhir,
                            'beban_tahun_lalu' => $data->beban_tahun_lalu,
                            'belanja_dibayar_dimuka_awal' => $data->belanja_dibayar_dimuka_awal,
                            'pembayaran_hutang' => $data->pembayaran_hutang,
                            'reklas_kurang_ke_rekening' => $data->reklas_kurang_ke_rekening,
                            'reklas_kurang_ke_aset' => $data->reklas_kurang_ke_aset,
                            'atribusi' => $data->atribusi,
                            'min_jukor' => $data->min_jukor,
                            'beban_lo' => $bebanLo ?? 0,
                            'kode_rekening' => $lra->kode_rekening,
                            'min_total' => $minTotal ?? 0,
                        ];
                    }
                }
            }

            return $this->successResponse($datas, 'Pemeliharaan berhasil diambil');
        } catch (\Exception $e) {
            return $this->errorResponse($e->getMessage() . ' ' . $e->getLine());
        }
    }

    function storeSubsidi(Request $request)
    {
        $validate = Validator::make($request->all(), [
            'periode' => 'required|exists:ref_periode,id',
            'year' => 'nullable|numeric',
            'data.*.instance_id' => 'required|exists:instances,id',
            'data.*.kode_rekening_id' => 'required|exists:ref_kode_rekening_complete,id',
        ], [], [
            'periode' => 'Periode',
            'data.*.instance_id' => 'Instance ID',
            'data.*.kode_rekening_id' => 'Kode Rekening ID',
        ]);
        if ($validate->fails()) {
            return $this->validationResponse($validate->errors());
        }

        DB::beginTransaction();
        try {
            $dateYear = $request->year ?? date('Y');
            $datas = $request->data;
            foreach ($datas as $input) {
                if ($input['id']) {
                    DB::table('acc_blo_subsidi')
                        ->where('id', $input['id'])
                        ->update([
                            'realisasi_belanja' => $input['realisasi_belanja'],
                            'saldo_awal' => $input['saldo_awal'],
                            'belanja_dibayar_dimuka_akhir' => $input['belanja_dibayar_dimuka_akhir'],
                            'hutang' => $input['hutang'],
                            'hibah' => $input['hibah'],
                            'reklas_tambah_dari_rekening' => $input['reklas_tambah_dari_rekening'],
                            'reklas_tambah_dari_modal' => $input['reklas_tambah_dari_modal'],
                            'plus_jukor' => $input['plus_jukor'],
                            'saldo_akhir' => $input['saldo_akhir'],
                            'beban_tahun_lalu' => $input['beban_tahun_lalu'],
                            'belanja_dibayar_dimuka_awal' => $input['belanja_dibayar_dimuka_awal'],
                            'pembayaran_hutang' => $input['pembayaran_hutang'],
                            'reklas_kurang_ke_rekening' => $input['reklas_kurang_ke_rekening'],
                            'reklas_kurang_ke_aset' => $input['reklas_kurang_ke_aset'],
                            'atribusi' => $input['atribusi'],
                            'min_jukor' => $input['min_jukor'],
                            'beban_lo' => $input['beban_lo'],
                        ]);
                } else {
                    DB::table('acc_blo_subsidi')->insert([
                        'periode_id' => $request->periode,
                        'year' => $dateYear,
                        'instance_id' => $input['instance_id'],
                        'kode_rekening_id' => $input['kode_rekening_id'],
                        'realisasi_belanja' => $input['realisasi_belanja'],
                        'saldo_awal' => $input['saldo_awal'],
                        'belanja_dibayar_dimuka_akhir' => $input['belanja_dibayar_dimuka_akhir'],
                        'hutang' => $input['hutang'],
                        'hibah' => $input['hibah'],
                        'reklas_tambah_dari_rekening' => $input['reklas_tambah_dari_rekening'],
                        'reklas_tambah_dari_modal' => $input['reklas_tambah_dari_modal'],
                        'plus_jukor' => $input['plus_jukor'],
                        'saldo_akhir' => $input['saldo_akhir'],
                        'beban_tahun_lalu' => $input['beban_tahun_lalu'],
                        'belanja_dibayar_dimuka_awal' => $input['belanja_dibayar_dimuka_awal'],
                        'pembayaran_hutang' => $input['pembayaran_hutang'],
                        'reklas_kurang_ke_rekening' => $input['reklas_kurang_ke_rekening'],
                        'reklas_kurang_ke_aset' => $input['reklas_kurang_ke_aset'],
                        'atribusi' => $input['atribusi'],
                        'min_jukor' => $input['min_jukor'],
                        'beban_lo' => $input['beban_lo'],
                    ]);
                }
            }

            DB::commit();
            return $this->successResponse([], 'Pemeliharaan berhasil disimpan');
        } catch (\Exception $e) {
            DB::rollBack();
            return $this->errorResponse($e->getMessage() . ' ' . $e->getLine());
        }
    }

    function deleteSubsidi(Request $request)
    {
        $validate = Validator::make($request->all(), [
            'id' => 'required|exists:acc_blo_subsidi,id'
        ], [], [
            'id' => 'ID'
        ]);
        if ($validate->fails()) {
            return $this->validationResponse($validate->errors());
        }

        DB::beginTransaction();
        try {
            DB::table('acc_blo_subsidi')
                ->where('id', $request->id)
                ->delete();

            DB::commit();
            return $this->successResponse([], 'Pemeliharaan berhasil dihapus');
        } catch (\Exception $e) {
            DB::rollBack();
            return $this->errorResponse($e->getMessage() . ' ' . $e->getLine());
        }
    }
    // Subsidi End
}
