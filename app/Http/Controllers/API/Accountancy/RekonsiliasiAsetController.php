<?php

namespace App\Http\Controllers\API\Accountancy;

use App\Traits\JsonReturner;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use App\Models\Accountancy\RekonAset\AsetTakBerwujud;
use App\Models\Accountancy\RekonAset\KDP;
use App\Models\Accountancy\RekonAset\KibA;
use App\Models\Accountancy\RekonAset\KibB;
use App\Models\Accountancy\RekonAset\KibC;
use App\Models\Accountancy\RekonAset\KibD;
use App\Models\Accountancy\RekonAset\KibE;
use App\Models\Instance;
use App\Models\User;
use Illuminate\Support\Facades\Validator;

class RekonsiliasiAsetController extends Controller
{
    use JsonReturner;

    // Rekap Belanja Start
    function getRekapBelanja(Request $request)
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
            $instances = [];
            if (!$request->instance) {
                $instanceIds = DB::table('acc_lra')
                    ->select('instance_id')
                    ->groupBy('instance_id')
                    ->get();
                // $instances = Instance::whereIn('id', $instanceIds->pluck('instance_id'))->get();
                $instances = Instance::orderBy('name')->get();
            } elseif ($request->instance) {
                $instances = Instance::where('id', $request->instance)->get();
            }

            $values = [];
            foreach ($instances as $instance) {
                $tanah = DB::table('acc_lra')
                    ->where('instance_id', $instance->id)
                    ->where('kode_rekening', '5.2.01')
                    ->where('periode_id', $request->periode)
                    ->where('year', $request->year)
                    ->first()->realisasi ?? 0;
                $peralatanDanMesin = DB::table('acc_lra')
                    ->where('instance_id', $instance->id)
                    ->where('kode_rekening', '5.2.02')
                    ->where('periode_id', $request->periode)
                    ->where('year', $request->year)
                    ->first()->realisasi ?? 0;
                $gedungDanBangunan = DB::table('acc_lra')
                    ->where('instance_id', $instance->id)
                    ->where('kode_rekening', '5.2.03')
                    ->where('periode_id', $request->periode)
                    ->where('year', $request->year)
                    ->first()->realisasi ?? 0;
                $jalanJaringanIrigasi = DB::table('acc_lra')
                    ->where('instance_id', $instance->id)
                    ->where('kode_rekening', '5.2.04')
                    ->where('periode_id', $request->periode)
                    ->where('year', $request->year)
                    ->first()->realisasi ?? 0;
                $asetTetapLainnya = DB::table('acc_lra')
                    ->where('instance_id', $instance->id)
                    ->where('kode_rekening', '5.2.05')
                    ->where('periode_id', $request->periode)
                    ->where('year', $request->year)
                    ->first()->realisasi ?? 0;
                $assetLainLain = DB::table('acc_lra')
                    ->where('instance_id', $instance->id)
                    ->where('kode_rekening', '5.2.06')
                    ->where('periode_id', $request->periode)
                    ->where('year', $request->year)
                    ->first()->realisasi ?? 0;

                DB::table('acc_rek_as_rekap_belanja')
                    ->updateOrInsert([
                        'periode_id' => $request->periode,
                        'year' => $request->year,
                        'instance_id' => $instance->id,
                    ], [
                        'periode_id' => $request->periode,
                        'year' => $request->year,
                        'instance_id' => $instance->id,
                        'tanah' => $tanah,
                        'peralatan_mesin' => $peralatanDanMesin,
                        'gedung_bangunan' => $gedungDanBangunan,
                        'jalan_jaringan_irigasi' => $jalanJaringanIrigasi,
                        'aset_tetap_lainnya' => $asetTetapLainnya,
                        'aset_lain_lain' => $assetLainLain,
                    ]);
                $dataDB = DB::table('acc_rek_as_rekap_belanja')
                    ->where('instance_id', $instance->id)
                    ->where('year', $request->year)
                    ->where('periode_id', $request->periode)
                    ->first();

                $values[] = [
                    'data_id' => $dataDB->id,
                    'instance_name' => $instance->name,
                    'instance_id' => $instance->id,
                    'tanah' => $tanah ?? 0,
                    'peralatan_mesin' => $peralatanDanMesin ?? 0,
                    'gedung_bangunan' => $gedungDanBangunan ?? 0,
                    'jalan_jaringan_irigasi' => $jalanJaringanIrigasi ?? 0,
                    'aset_tetap_lainnya' => $asetTetapLainnya ?? 0,
                    'kdp' => $dataDB->kdp ?? 0,
                    'aset_lain_lain' => $assetLainLain ?? 0,
                ];
            }
            $datas['instances'] = $instances;
            $datas['datas'] = $values;

            return $this->successResponse($datas, 'Penyesuai Aset dan Beban berhasil diambil');
        } catch (\Exception $e) {
            return $this->errorResponse($e->getMessage() . ' ' . $e->getLine());
        }
    }

    function saveRekapBelanja(Request $request)
    {
        $validate = Validator::make($request->all(), [
            'instance' => 'required|exists:instances,id',
            'year' => 'required|integer',
            'periode' => 'required|exists:ref_periode,id',
            'data.tanah' => 'required|numeric',
            'data.peralatan_mesin' => 'required|numeric',
            'data.gedung_bangunan' => 'required|numeric',
            'data.jalan_jaringan_irigasi' => 'required|numeric',
            'data.aset_tetap_lainnya' => 'required|numeric',
            'data.aset_lain_lain' => 'required|numeric',
            'data.kdp' => 'required|numeric',
        ], [], [
            'instance' => 'Instance ID',
            'periode' => 'Periode',
            'data.tanah' => 'Tanah',
            'data.peralatan_mesin' => 'Peralatan dan Mesin',
            'data.gedung_bangunan' => 'Gedung dan Bangunan',
            'data.jalan_jaringan_irigasi' => 'Jalan, Jaringan, dan Irigasi',
            'data.aset_tetap_lainnya' => 'Aset Tetap Lainnya',
            'data.aset_lain_lain' => 'Aset Lain-lain',
            'data.kdp' => 'KDP',
        ]);
        if ($validate->fails()) {
            return $this->validationResponse($validate->errors());
        }

        DB::beginTransaction();
        try {
            DB::table('acc_rek_as_rekap_belanja')
                ->updateOrInsert([
                    'periode_id' => $request->periode,
                    'year' => $request->year,
                    'instance_id' => $request->instance,
                ], [
                    'periode_id' => $request->periode,
                    'year' => $request->year,
                    'instance_id' => $request->instance,
                    'tanah' => $request->data['tanah'],
                    'peralatan_mesin' => $request->data['peralatan_mesin'],
                    'gedung_bangunan' => $request->data['gedung_bangunan'],
                    'jalan_jaringan_irigasi' => $request->data['jalan_jaringan_irigasi'],
                    'aset_tetap_lainnya' => $request->data['aset_tetap_lainnya'],
                    'aset_lain_lain' => $request->data['aset_lain_lain'],
                    'kdp' => $request->data['kdp'],
                ]);

            DB::commit();
            return $this->successResponse(null, 'Data berhasil disimpan');
        } catch (\Exception $e) {
            DB::rollBack();
            return $this->errorResponse($e->getMessage() . ' ' . $e->getLine());
        }
    }
    // Rekap Belanja End

    // KIB A Start
    function getKibA(Request $request)
    {
        $validate = Validator::make($request->all(), [
            'instance' => 'nullable|exists:instances,id',
            'year' => 'required|integer',
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
            $instances = [];
            if (!$request->instance) {
                $instanceIds = DB::table('acc_lra')
                    ->select('instance_id')
                    ->groupBy('instance_id')
                    ->get();
                // $instances = Instance::whereIn('id', $instanceIds->pluck('instance_id'))->get();
                $instances = Instance::orderBy('name')->get();
            } elseif ($request->instance) {
                $instances = Instance::where('id', $request->instance)->get();
            }

            $values = [];
            foreach ($instances as $instance) {
                $dataLRA = DB::table('acc_lra')
                    ->where('instance_id', $instance->id)
                    ->where('year', $request->year)
                    ->where('periode_id', $request->periode)
                    ->where('kode_rekening', '5.2.01')
                    ->first();

                $getData = KibA::where('instance_id', $instance->id)
                    ->where('year', $request->year)
                    ->where('periode_id', $request->periode)
                    ->first();
                if (!$getData) {
                    DB::table('acc_rek_as_kib_a')->insert([
                        'periode_id' => $request->periode,
                        'year' => $request->year,
                        'instance_id' => $instance->id,
                        'plus_realisasi_belanja' => $dataLRA->realisasi ?? 0,
                    ]);

                    $getData = KibA::where('instance_id', $instance->id)
                        ->where('year', $request->year)
                        ->where('periode_id', $request->periode)
                        ->first();
                }
                // Hutang Belanja
                $HutangBaru = DB::table('acc_htb_hutang_baru')
                    ->where('instance_id', $instance->id)
                    ->where('year', $request->year)
                    ->where('periode_id', $request->periode)
                    ->sum('aset_tetap_tanah') ?? 0;
                $getData->plus_hutang_kegiatan = $HutangBaru ?? 0;
                $PembayaranHutang = DB::table('acc_htb_pembayaran_hutang')
                    ->where('instance_id', $instance->id)
                    ->where('year', $request->year)
                    ->where('periode_id', $request->periode)
                    ->sum('aset_tetap_tanah') ?? 0;
                $getData->min_pembayaran_utang = $PembayaranHutang ?? 0;

                // Atribusi
                $Atribusi = DB::table('acc_padb_atribusi')
                    ->where('instance_id', $instance->id)
                    ->where('year', $request->year)
                    ->where('periode_id', $request->periode)
                    ->sum('atri_aset_tetap_tanah') ?? 0;
                $getData->plus_atribusi = $Atribusi ?? 0;

                // Reklasifikasi KIB
                $ReklasKib = DB::table('acc_padb_penyesuaian_aset')
                    ->where('instance_id', $instance->id)
                    ->where('year', $request->year)
                    ->where('periode_id', $request->periode)
                    ->get();

                $plusReklasKey = 'plus_aset_tetap_tanah';
                $minReklasKey = 'min_aset_tetap_tanah';

                $getData->plus_reklasifikasi_kib_a = $ReklasKib->where($plusReklasKey, '>', 0)
                    ->where('min_aset_tetap_tanah', '>', 0)
                    ->sum($plusReklasKey) ?? 0;
                $getData->min_reklasifikasi_beban_kib_a = $ReklasKib->where($minReklasKey, '>', 0)
                    ->where('plus_aset_tetap_tanah', '>', 0)
                    ->sum($minReklasKey) ?? 0;

                $getData->plus_reklasifikasi_kib_b = $ReklasKib->where($plusReklasKey, '>', 0)
                    ->where('min_aset_tetap_tanah', '>', 0)
                    ->sum($plusReklasKey) ?? 0;
                $getData->min_reklasifikasi_beban_kib_b = $ReklasKib->where($minReklasKey, '>', 0)
                    ->where('plus_aset_tetap_peralatan_mesin', '>', 0)
                    ->sum($minReklasKey) ?? 0;

                $getData->plus_reklasifikasi_kib_c = $ReklasKib->where($plusReklasKey, '>', 0)
                    ->where('min_aset_tetap_gedung_bangunan', '>', 0)
                    ->sum($plusReklasKey) ?? 0;
                $getData->min_reklasifikasi_beban_kib_c = $ReklasKib->where($minReklasKey, '>', 0)
                    ->where('plus_aset_tetap_gedung_bangunan', '>', 0)
                    ->sum($minReklasKey) ?? 0;

                $getData->plus_reklasifikasi_kib_d = $ReklasKib->where($plusReklasKey, '>', 0)
                    ->where('min_aset_tetap_jalan_jaringan_irigasi', '>', 0)
                    ->sum($plusReklasKey) ?? 0;
                $getData->min_reklasifikasi_beban_kib_d = $ReklasKib->where($minReklasKey, '>', 0)
                    ->where('plus_aset_tetap_jalan_jaringan_irigasi', '>', 0)
                    ->sum($minReklasKey) ?? 0;

                $getData->plus_reklasifikasi_kib_e = $ReklasKib->where($plusReklasKey, '>', 0)
                    ->where('min_aset_tetap_lainnya', '>', 0)
                    ->sum($plusReklasKey) ?? 0;
                $getData->min_reklasifikasi_beban_kib_e = $ReklasKib->where($minReklasKey, '>', 0)
                    ->where('plus_aset_tetap_lainnya', '>', 0)
                    ->sum($minReklasKey) ?? 0;

                $getData->plus_reklasifikasi_kdp = $ReklasKib->where($plusReklasKey, '>', 0)
                    ->where('min_konstruksi_dalam_pekerjaan', '>', 0)
                    ->sum($plusReklasKey) ?? 0;
                $getData->min_reklasifikasi_beban_kdp = $ReklasKib->where($minReklasKey, '>', 0)
                    ->where('plus_konstruksi_dalam_pekerjaan', '>', 0)
                    ->sum($minReklasKey) ?? 0;

                $getData->plus_reklasifikasi_aset_lain_lain = $ReklasKib->where($plusReklasKey, '>', 0)
                    ->where('min_aset_lain_lain', '>', 0)
                    ->sum($plusReklasKey) ?? 0;
                $getData->min_reklasifikasi_beban_aset_lain_lain = $ReklasKib->where($minReklasKey, '>', 0)
                    ->where('plus_aset_lain_lain', '>', 0)
                    ->sum($minReklasKey) ?? 0;


                // Hibah Masuk
                $HibahMasuk = DB::table('acc_padb_tambahan_hibah_masuk')
                    ->where('instance_id', $instance->id)
                    ->where('year', $request->year)
                    ->where('periode_id', $request->periode)
                    ->sum('aset_tetap_tanah') ?? 0;
                $getData->plus_hibah_masuk = $HibahMasuk ?? 0;

                // Hibah Keluar
                $HibahKeluar = DB::table('acc_padb_tambahan_hibah_keluar')
                    ->where('instance_id', $instance->id)
                    ->where('year', $request->year)
                    ->where('periode_id', $request->periode)
                    ->sum('aset_tetap_tanah') ?? 0;
                $getData->min_reklasifikasi_beban_hibah = $HibahKeluar ?? 0;

                // Penilaian
                $PenilaianAset = DB::table('acc_padb_penilaian_aset')
                    ->where('instance_id', $instance->id)
                    ->where('year', $request->year)
                    ->where('periode_id', $request->periode)
                    ->sum('aset_tetap_tanah') ?? 0;
                $getData->plus_penilaian = $PenilaianAset ?? 0;

                // Mutasi OPD
                $MutasiOPDMasuk = DB::table('acc_padb_tambahan_mutasi_aset')
                    // ->where('instance_id', $instance->id)
                    ->where('to_instance_id', $instance->id)
                    ->where('year', $request->year)
                    ->where('periode_id', $request->periode)
                    ->get();
                $MutasiOPDKeluar = DB::table('acc_padb_tambahan_mutasi_aset')
                    // ->where('instance_id', $instance->id)
                    ->where('from_instance_id', $instance->id)
                    ->where('year', $request->year)
                    ->where('periode_id', $request->periode)
                    ->get();
                $getData->plus_mutasi_antar_opd = $MutasiOPDMasuk->sum('plus_aset_tetap_tanah') ?? 0;
                // $getData->min_mutasi_antar_opd = $MutasiOPDKeluar->sum('min_aset_tetap_tanah') ?? 0;
                $getData->min_mutasi_antar_opd = $MutasiOPDKeluar->sum('plus_aset_tetap_tanah') ?? 0;

                $minPenghapusan = DB::table('acc_padb_penghapusan_aset')
                    ->where('instance_id', $instance->id)
                    ->where('year', $request->year)
                    ->where('periode_id', $request->periode)
                    ->sum('aset_tetap_tanah') ?? 0;
                $minPenjualan = DB::table('acc_padb_penjualan_aset')
                    ->where('instance_id', $instance->id)
                    ->where('year', $request->year)
                    ->where('periode_id', $request->periode)
                    ->sum('aset_tetap_tanah') ?? 0;

                $getData->min_penghapusan = ($minPenghapusan + $minPenjualan) ?? 0;
                $getData->timestamps = false;
                $getData->save();

                $saldoAwal = $getData->saldo_awal ?? 0;

                $getData->plus_total = ($getData->plus_realisasi_belanja ?? 0) + ($getData->plus_hutang_kegiatan ?? 0) + ($getData->plus_atribusi ?? 0) + ($getData->plus_reklasifikasi_barang_habis_pakai ?? 0) + ($getData->plus_reklasifikasi_pemeliharaan ?? 0) + ($getData->plus_reklasifikasi_jasa ?? 0) + ($getData->plus_reklasifikasi_kib_a ?? 0) + ($getData->plus_reklasifikasi_kib_b ?? 0) + ($getData->plus_reklasifikasi_kib_c ?? 0) + ($getData->plus_reklasifikasi_kib_d ?? 0) + ($getData->plus_reklasifikasi_kib_e ?? 0) + ($getData->plus_reklasifikasi_kdp ?? 0) + ($getData->plus_reklasifikasi_aset_lain_lain ?? 0) + ($getData->plus_hibah_masuk ?? 0) + ($getData->plus_penilaian ?? 0) + ($getData->plus_mutasi_antar_opd ?? 0);
                $getData->min_total = ($getData->min_pembayaran_utang ?? 0) + ($getData->min_reklasifikasi_beban_persediaan ?? 0) + ($getData->min_reklasifikasi_beban_jasa ?? 0) + ($getData->min_reklasifikasi_beban_pemeliharaan ?? 0) + ($getData->min_reklasifikasi_beban_hibah ?? 0) + ($getData->min_reklasifikasi_beban_kib_a ?? 0) + ($getData->min_reklasifikasi_beban_kib_b ?? 0) + ($getData->min_reklasifikasi_beban_kib_c ?? 0) + ($getData->min_reklasifikasi_beban_kib_d ?? 0) + ($getData->min_reklasifikasi_beban_kib_e ?? 0) + ($getData->min_reklasifikasi_beban_kdp ?? 0) + ($getData->min_reklasifikasi_beban_aset_lain_lain ?? 0) + ($getData->min_penghapusan ?? 0) + ($getData->min_mutasi_antar_opd ?? 0) + ($getData->min_tptgr ?? 0);

                $calculateSaldoAkhir = $saldoAwal + ($getData->plus_total ?? 0) - ($getData->min_total ?? 0);

                DB::table('acc_rek_as_kib_a')
                    ->where('id', $getData->id)
                    ->update([
                        'saldo_akhir' => $calculateSaldoAkhir,
                    ]);

                $values[] = [
                    'data_id' => $getData->id,
                    'instance_id' => $instance->id,
                    'instance_name' => $instance->name,
                    'saldo_awal' => $saldoAwal ?? 0,
                    'saldo_akhir' => $calculateSaldoAkhir,

                    'plus_realisasi_belanja' => $dataLRA->realisasi ?? $getData->plus_realisasi_belanja ?? 0,
                    'plus_hutang_kegiatan' => $getData->plus_hutang_kegiatan ?? 0,
                    'plus_atribusi' => $getData->plus_atribusi ?? 0,
                    'plus_reklasifikasi_barang_habis_pakai' => $getData->plus_reklasifikasi_barang_habis_pakai ?? 0,
                    'plus_reklasifikasi_pemeliharaan' => $getData->plus_reklasifikasi_pemeliharaan ?? 0,
                    'plus_reklasifikasi_jasa' => $getData->plus_reklasifikasi_jasa ?? 0,
                    'plus_reklasifikasi_kib_a' => $getData->plus_reklasifikasi_kib_a ?? 0,
                    'plus_reklasifikasi_kib_b' => $getData->plus_reklasifikasi_kib_b ?? 0,
                    'plus_reklasifikasi_kib_c' => $getData->plus_reklasifikasi_kib_c ?? 0,
                    'plus_reklasifikasi_kib_d' => $getData->plus_reklasifikasi_kib_d ?? 0,
                    'plus_reklasifikasi_kib_e' => $getData->plus_reklasifikasi_kib_e ?? 0,
                    'plus_reklasifikasi_kdp' => $getData->plus_reklasifikasi_kdp ?? 0,
                    'plus_reklasifikasi_aset_lain_lain' => $getData->plus_reklasifikasi_aset_lain_lain ?? 0,
                    'plus_hibah_masuk' => $getData->plus_hibah_masuk ?? 0,
                    'plus_penilaian' => $getData->plus_penilaian ?? 0,
                    'plus_mutasi_antar_opd' => $getData->plus_mutasi_antar_opd ?? 0,
                    'plus_total' => $getData->plus_total ?? 0,

                    'min_pembayaran_utang' => $getData->min_pembayaran_utang ?? 0,
                    'min_reklasifikasi_beban_persediaan' => $getData->min_reklasifikasi_beban_persediaan ?? 0,
                    'min_reklasifikasi_beban_jasa' => $getData->min_reklasifikasi_beban_jasa ?? 0,
                    'min_reklasifikasi_beban_pemeliharaan' => $getData->min_reklasifikasi_beban_pemeliharaan ?? 0,
                    'min_reklasifikasi_beban_hibah' => $getData->min_reklasifikasi_beban_hibah ?? 0,
                    'min_reklasifikasi_beban_kib_a' => $getData->min_reklasifikasi_beban_kib_a ?? 0,
                    'min_reklasifikasi_beban_kib_b' => $getData->min_reklasifikasi_beban_kib_b ?? 0,
                    'min_reklasifikasi_beban_kib_c' => $getData->min_reklasifikasi_beban_kib_c ?? 0,
                    'min_reklasifikasi_beban_kib_d' => $getData->min_reklasifikasi_beban_kib_d ?? 0,
                    'min_reklasifikasi_beban_kib_e' => $getData->min_reklasifikasi_beban_kib_e ?? 0,
                    'min_reklasifikasi_beban_kdp' => $getData->min_reklasifikasi_beban_kdp ?? 0,
                    'min_reklasifikasi_beban_aset_lain_lain' => $getData->min_reklasifikasi_beban_aset_lain_lain ?? 0,
                    // 'min_penghapusan' => $getData->min_penghapusan ?? 0,
                    'min_penghapusan' => ($minPenghapusan + $minPenjualan) ?? 0,
                    'min_mutasi_antar_opd' => $getData->min_mutasi_antar_opd ?? 0,
                    'min_tptgr' => $getData->min_tptgr ?? 0,
                    'min_total' => $getData->min_total ?? 0,
                ];
            }

            $datas['instances'] = $instances;
            $datas['datas'] = $values;

            return $this->successResponse($datas, 'Penyesuai Aset dan Beban berhasil diambil');
        } catch (\Exception $e) {
            return $this->errorResponse($e->getMessage() . ' ' . $e->getLine());
        }
    }

    function saveKibA(Request $request)
    {
        $validate = Validator::make($request->all(), [
            'instance' => 'nullable|exists:instances,id',
            'year' => 'required|integer',
            'periode' => 'required|exists:ref_periode,id'
        ], [], [
            'instance' => 'Instance ID',
            'periode' => 'Periode'
        ]);
        if ($validate->fails()) {
            return $this->validationResponse($validate->errors());
        }

        $now = now();
        DB::beginTransaction();
        try {
            $datas = $request->data;
            foreach ($datas as $input) {
                DB::table('acc_rek_as_kib_a')->updateOrInsert([
                    'periode_id' => $request->periode,
                    'year' => $request->year,
                    'instance_id' => $input['instance_id'],
                ], [
                    'periode_id' => $request->periode,
                    'year' => $request->year,
                    'instance_id' => $input['instance_id'],
                    'saldo_awal' => $input['saldo_awal'],
                    'saldo_akhir' => $input['saldo_akhir'],
                    'plus_realisasi_belanja' => $input['plus_realisasi_belanja'],
                    'plus_hutang_kegiatan' => $input['plus_hutang_kegiatan'],
                    'plus_atribusi' => $input['plus_atribusi'],
                    'plus_reklasifikasi_barang_habis_pakai' => $input['plus_reklasifikasi_barang_habis_pakai'],
                    'plus_reklasifikasi_pemeliharaan' => $input['plus_reklasifikasi_pemeliharaan'],
                    'plus_reklasifikasi_jasa' => $input['plus_reklasifikasi_jasa'],
                    'plus_reklasifikasi_kib_a' => $input['plus_reklasifikasi_kib_a'],
                    'plus_reklasifikasi_kib_b' => $input['plus_reklasifikasi_kib_b'],
                    'plus_reklasifikasi_kib_c' => $input['plus_reklasifikasi_kib_c'],
                    'plus_reklasifikasi_kib_d' => $input['plus_reklasifikasi_kib_d'],
                    'plus_reklasifikasi_kib_e' => $input['plus_reklasifikasi_kib_e'],
                    'plus_reklasifikasi_kdp' => $input['plus_reklasifikasi_kdp'],
                    'plus_reklasifikasi_aset_lain_lain' => $input['plus_reklasifikasi_aset_lain_lain'],
                    'plus_hibah_masuk' => $input['plus_hibah_masuk'],
                    'plus_penilaian' => $input['plus_penilaian'],
                    'plus_mutasi_antar_opd' => $input['plus_mutasi_antar_opd'],
                    'min_pembayaran_utang' => $input['min_pembayaran_utang'],
                    'min_reklasifikasi_beban_persediaan' => $input['min_reklasifikasi_beban_persediaan'],
                    'min_reklasifikasi_beban_jasa' => $input['min_reklasifikasi_beban_jasa'],
                    'min_reklasifikasi_beban_pemeliharaan' => $input['min_reklasifikasi_beban_pemeliharaan'],
                    'min_reklasifikasi_beban_hibah' => $input['min_reklasifikasi_beban_hibah'],
                    'min_reklasifikasi_beban_kib_a' => $input['min_reklasifikasi_beban_kib_a'],
                    'min_reklasifikasi_beban_kib_b' => $input['min_reklasifikasi_beban_kib_b'],
                    'min_reklasifikasi_beban_kib_c' => $input['min_reklasifikasi_beban_kib_c'],
                    'min_reklasifikasi_beban_kib_d' => $input['min_reklasifikasi_beban_kib_d'],
                    'min_reklasifikasi_beban_kib_e' => $input['min_reklasifikasi_beban_kib_e'],
                    'min_reklasifikasi_beban_kdp' => $input['min_reklasifikasi_beban_kdp'],
                    'min_reklasifikasi_beban_aset_lain_lain' => $input['min_reklasifikasi_beban_aset_lain_lain'],
                    'min_penghapusan' => $input['min_penghapusan'],
                    'min_mutasi_antar_opd' => $input['min_mutasi_antar_opd'],
                    'min_tptgr' => $input['min_tptgr'],
                    'updated_by' => auth()->user()->id,
                    'updated_at' => $now,
                ]);
            }

            DB::commit();
            return $this->successResponse(null, 'Data berhasil disimpan');
        } catch (\Exception $e) {
            DB::rollBack();
            return $this->errorResponse($e->getMessage() . ' ' . $e->getLine());
        }
    }
    // KIB A End

    // KIB B Start
    function getKibB(Request $request)
    {
        $validate = Validator::make($request->all(), [
            'instance' => 'nullable|exists:instances,id',
            'year' => 'required|integer',
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
            $instances = [];
            if (!$request->instance) {
                $instanceIds = DB::table('acc_lra')
                    ->select('instance_id')
                    ->groupBy('instance_id')
                    ->get();
                // $instances = Instance::whereIn('id', $instanceIds->pluck('instance_id'))->get();
                $instances = Instance::orderBy('name')->get();
            } elseif ($request->instance) {
                $instances = Instance::where('id', $request->instance)->get();
            }

            $values = [];
            foreach ($instances as $instance) {
                $dataLRA = DB::table('acc_lra')
                    ->where('instance_id', $instance->id)
                    ->where('year', $request->year)
                    ->where('periode_id', $request->periode)
                    ->where('kode_rekening', '5.2.02')
                    ->first();

                $getData = KibB::where('instance_id', $instance->id)
                    ->where('year', $request->year)
                    ->where('periode_id', $request->periode)
                    ->first();
                if (!$getData) {
                    DB::table('acc_rek_as_kib_b')->insert([
                        'periode_id' => $request->periode,
                        'year' => $request->year,
                        'instance_id' => $instance->id,
                        'plus_realisasi_belanja' => $dataLRA->realisasi ?? 0,
                    ]);

                    $getData = KibB::where('instance_id', $instance->id)
                        ->where('year', $request->year)
                        ->where('periode_id', $request->periode)
                        ->first();
                }
                // Hutang Belanja
                $HutangBaru = DB::table('acc_htb_hutang_baru')
                    ->where('instance_id', $instance->id)
                    ->where('year', $request->year)
                    ->where('periode_id', $request->periode)
                    ->sum('aset_tetap_peralatan_mesin') ?? 0;
                $getData->plus_hutang_kegiatan = $HutangBaru ?? 0;
                $PembayaranHutang = DB::table('acc_htb_pembayaran_hutang')
                    ->where('instance_id', $instance->id)
                    ->where('year', $request->year)
                    ->where('periode_id', $request->periode)
                    ->sum('aset_tetap_peralatan_mesin') ?? 0;
                $getData->min_pembayaran_utang = $PembayaranHutang ?? 0;

                // Atribusi
                $Atribusi = DB::table('acc_padb_atribusi')
                    ->where('instance_id', $instance->id)
                    ->where('year', $request->year)
                    ->where('periode_id', $request->periode)
                    ->sum('atri_aset_tetap_peralatan_mesin') ?? 0;
                $getData->plus_atribusi = $Atribusi ?? 0;

                // Reklasifikasi KIB
                $ReklasKib = DB::table('acc_padb_penyesuaian_aset')
                    ->where('instance_id', $instance->id)
                    ->where('year', $request->year)
                    ->where('periode_id', $request->periode)
                    ->get();
                // $getData->plus_reklasifikasi_kib_a = $ReklasKib->sum('min_aset_tetap_tanah') ?? 0;
                // $getData->min_reklasifikasi_beban_kib_a = $ReklasKib->sum('plus_aset_tetap_tanah') ?? 0;
                // $getData->plus_reklasifikasi_kib_b = $ReklasKib->sum('min_aset_tetap_peralatan_mesin') ?? 0;
                // $getData->min_reklasifikasi_beban_kib_b = $ReklasKib->sum('plus_aset_tetap_peralatan_mesin') ?? 0;
                // $getData->plus_reklasifikasi_kib_c = $ReklasKib->sum('min_aset_tetap_gedung_bangunan') ?? 0;
                // $getData->min_reklasifikasi_beban_kib_c = $ReklasKib->sum('plus_aset_tetap_gedung_bangunan') ?? 0;
                // $getData->plus_reklasifikasi_kib_d = $ReklasKib->sum('min_aset_tetap_jalan_jaringan_irigasi') ?? 0;
                // $getData->min_reklasifikasi_beban_kib_d = $ReklasKib->sum('plus_aset_tetap_jalan_jaringan_irigasi') ?? 0;
                // $getData->plus_reklasifikasi_kib_e = $ReklasKib->sum('min_aset_tetap_lainnya') ?? 0;
                // $getData->min_reklasifikasi_beban_kib_e = $ReklasKib->sum('plus_aset_tetap_lainnya') ?? 0;
                // $getData->plus_reklasifikasi_kdp = $ReklasKib->sum('min_konstruksi_dalam_pekerjaan') ?? 0;
                // $getData->min_reklasifikasi_beban_kdp = $ReklasKib->sum('plus_konstruksi_dalam_pekerjaan') ?? 0;
                // $getData->plus_reklasifikasi_aset_lain_lain = $ReklasKib->sum('min_aset_lain_lain') ?? 0;
                // $getData->min_reklasifikasi_beban_aset_lain_lain = $ReklasKib->sum('plus_aset_lain_lain') ?? 0;
                $plusReklasKey = 'plus_aset_tetap_peralatan_mesin';
                $minReklasKey = 'min_aset_tetap_peralatan_mesin';

                $getData->plus_reklasifikasi_kib_a = $ReklasKib->where($plusReklasKey, '>', 0)
                    ->where('min_aset_tetap_tanah', '>', 0)
                    ->sum($plusReklasKey) ?? 0;
                $getData->min_reklasifikasi_beban_kib_a = $ReklasKib->where($minReklasKey, '>', 0)
                    ->where('plus_aset_tetap_tanah', '>', 0)
                    ->sum($minReklasKey) ?? 0;

                $getData->plus_reklasifikasi_kib_b = $ReklasKib->where($plusReklasKey, '>', 0)
                    ->where('min_aset_tetap_tanah', '>', 0)
                    ->sum($plusReklasKey) ?? 0;
                $getData->min_reklasifikasi_beban_kib_b = $ReklasKib->where($minReklasKey, '>', 0)
                    ->where('plus_aset_tetap_peralatan_mesin', '>', 0)
                    ->sum($minReklasKey) ?? 0;

                $getData->plus_reklasifikasi_kib_c = $ReklasKib->where($plusReklasKey, '>', 0)
                    ->where('min_aset_tetap_gedung_bangunan', '>', 0)
                    ->sum($plusReklasKey) ?? 0;
                $getData->min_reklasifikasi_beban_kib_c = $ReklasKib->where($minReklasKey, '>', 0)
                    ->where('plus_aset_tetap_gedung_bangunan', '>', 0)
                    ->sum($minReklasKey) ?? 0;

                $getData->plus_reklasifikasi_kib_d = $ReklasKib->where($plusReklasKey, '>', 0)
                    ->where('min_aset_tetap_jalan_jaringan_irigasi', '>', 0)
                    ->sum($plusReklasKey) ?? 0;
                $getData->min_reklasifikasi_beban_kib_d = $ReklasKib->where($minReklasKey, '>', 0)
                    ->where('plus_aset_tetap_jalan_jaringan_irigasi', '>', 0)
                    ->sum($minReklasKey) ?? 0;

                $getData->plus_reklasifikasi_kib_e = $ReklasKib->where($plusReklasKey, '>', 0)
                    ->where('min_aset_tetap_lainnya', '>', 0)
                    ->sum($plusReklasKey) ?? 0;
                $getData->min_reklasifikasi_beban_kib_e = $ReklasKib->where($minReklasKey, '>', 0)
                    ->where('plus_aset_tetap_lainnya', '>', 0)
                    ->sum($minReklasKey) ?? 0;

                $getData->plus_reklasifikasi_kdp = $ReklasKib->where($plusReklasKey, '>', 0)
                    ->where('min_konstruksi_dalam_pekerjaan', '>', 0)
                    ->sum($plusReklasKey) ?? 0;
                $getData->min_reklasifikasi_beban_kdp = $ReklasKib->where($minReklasKey, '>', 0)
                    ->where('plus_konstruksi_dalam_pekerjaan', '>', 0)
                    ->sum($minReklasKey) ?? 0;

                $getData->plus_reklasifikasi_aset_lain_lain = $ReklasKib->where($plusReklasKey, '>', 0)
                    ->where('min_aset_lain_lain', '>', 0)
                    ->sum($plusReklasKey) ?? 0;
                $getData->min_reklasifikasi_beban_aset_lain_lain = $ReklasKib->where($minReklasKey, '>', 0)
                    ->where('plus_aset_lain_lain', '>', 0)
                    ->sum($minReklasKey) ?? 0;


                // Hibah Masuk
                $HibahMasuk = DB::table('acc_padb_tambahan_hibah_masuk')
                    ->where('instance_id', $instance->id)
                    ->where('year', $request->year)
                    ->where('periode_id', $request->periode)
                    ->sum('aset_tetap_peralatan_mesin') ?? 0;
                $getData->plus_hibah_masuk = $HibahMasuk ?? 0;

                // Hibah Keluar
                $HibahKeluar = DB::table('acc_padb_tambahan_hibah_keluar')
                    ->where('instance_id', $instance->id)
                    ->where('year', $request->year)
                    ->where('periode_id', $request->periode)
                    ->sum('aset_tetap_peralatan_mesin') ?? 0;
                $getData->min_reklasifikasi_beban_hibah = $HibahKeluar ?? 0;

                // Penilaian
                $PenilaianAset = DB::table('acc_padb_penilaian_aset')
                    ->where('instance_id', $instance->id)
                    ->where('year', $request->year)
                    ->where('periode_id', $request->periode)
                    ->sum('aset_tetap_peralatan_mesin') ?? 0;
                $getData->plus_penilaian = $PenilaianAset ?? 0;

                // Mutasi OPD
                $MutasiOPDMasuk = DB::table('acc_padb_tambahan_mutasi_aset')
                    // ->where('instance_id', $instance->id)
                    ->where('to_instance_id', $instance->id)
                    ->where('year', $request->year)
                    ->where('periode_id', $request->periode)
                    ->get();
                $MutasiOPDKeluar = DB::table('acc_padb_tambahan_mutasi_aset')
                    // ->where('instance_id', $instance->id)
                    ->where('from_instance_id', $instance->id)
                    ->where('year', $request->year)
                    ->where('periode_id', $request->periode)
                    ->get();
                $getData->plus_mutasi_antar_opd = $MutasiOPDMasuk->sum('plus_aset_tetap_peralatan_mesin') ?? 0;
                $getData->min_mutasi_antar_opd = $MutasiOPDKeluar->sum('plus_aset_tetap_peralatan_mesin') ?? 0;

                $minPenghapusan = DB::table('acc_padb_penghapusan_aset')
                    ->where('instance_id', $instance->id)
                    ->where('year', $request->year)
                    ->where('periode_id', $request->periode)
                    ->sum('aset_tetap_peralatan_mesin') ?? 0;
                $minPenjualan = DB::table('acc_padb_penjualan_aset')
                    ->where('instance_id', $instance->id)
                    ->where('year', $request->year)
                    ->where('periode_id', $request->periode)
                    ->sum('aset_tetap_peralatan_mesin') ?? 0;

                $getData->min_penghapusan = ($minPenghapusan + $minPenjualan) ?? 0;
                $getData->timestamps = false;
                $getData->save();

                $saldoAwal = $getData->saldo_awal ?? 0;

                $getData->plus_total = ($getData->plus_realisasi_belanja ?? 0) + ($getData->plus_hutang_kegiatan ?? 0) + ($getData->plus_atribusi ?? 0) + ($getData->plus_reklasifikasi_barang_habis_pakai ?? 0) + ($getData->plus_reklasifikasi_pemeliharaan ?? 0) + ($getData->plus_reklasifikasi_jasa ?? 0) + ($getData->plus_reklasifikasi_kib_a ?? 0) + ($getData->plus_reklasifikasi_kib_b ?? 0) + ($getData->plus_reklasifikasi_kib_c ?? 0) + ($getData->plus_reklasifikasi_kib_d ?? 0) + ($getData->plus_reklasifikasi_kib_e ?? 0) + ($getData->plus_reklasifikasi_kdp ?? 0) + ($getData->plus_reklasifikasi_aset_lain_lain ?? 0) + ($getData->plus_hibah_masuk ?? 0) + ($getData->plus_penilaian ?? 0) + ($getData->plus_mutasi_antar_opd ?? 0);

                $getData->min_total = ($getData->min_pembayaran_utang ?? 0) + ($getData->min_reklasifikasi_beban_persediaan ?? 0) + ($getData->min_reklasifikasi_beban_jasa ?? 0) + ($getData->min_reklasifikasi_beban_pemeliharaan ?? 0) + ($getData->min_reklasifikasi_beban_hibah ?? 0) + ($getData->min_reklasifikasi_beban_kib_a ?? 0) + ($getData->min_reklasifikasi_beban_kib_b ?? 0) + ($getData->min_reklasifikasi_beban_kib_c ?? 0) + ($getData->min_reklasifikasi_beban_kib_d ?? 0) + ($getData->min_reklasifikasi_beban_kib_e ?? 0) + ($getData->min_reklasifikasi_beban_kdp ?? 0) + ($getData->min_reklasifikasi_beban_aset_lain_lain ?? 0) + ($getData->min_penghapusan ?? 0) + ($getData->min_mutasi_antar_opd ?? 0) + ($getData->min_tptgr ?? 0);

                $calculateSaldoAkhir = $saldoAwal + ($getData->plus_total ?? 0) - ($getData->min_total ?? 0);

                DB::table('acc_rek_as_kib_b')
                    ->where('id', $getData->id)
                    ->update([
                        'saldo_akhir' => $calculateSaldoAkhir,
                    ]);

                $values[] = [
                    'data_id' => $getData->id,
                    'instance_id' => $instance->id,
                    'instance_name' => $instance->name,
                    'saldo_awal' => $saldoAwal ?? 0,
                    'saldo_akhir' => $calculateSaldoAkhir,

                    'plus_realisasi_belanja' => $dataLRA->realisasi ?? $getData->plus_realisasi_belanja ?? 0,
                    'plus_hutang_kegiatan' => $getData->plus_hutang_kegiatan ?? 0,
                    'plus_atribusi' => $getData->plus_atribusi ?? 0,
                    'plus_reklasifikasi_barang_habis_pakai' => $getData->plus_reklasifikasi_barang_habis_pakai ?? 0,
                    'plus_reklasifikasi_pemeliharaan' => $getData->plus_reklasifikasi_pemeliharaan ?? 0,
                    'plus_reklasifikasi_jasa' => $getData->plus_reklasifikasi_jasa ?? 0,
                    'plus_reklasifikasi_kib_a' => $getData->plus_reklasifikasi_kib_a ?? 0,
                    'plus_reklasifikasi_kib_b' => $getData->plus_reklasifikasi_kib_b ?? 0,
                    'plus_reklasifikasi_kib_c' => $getData->plus_reklasifikasi_kib_c ?? 0,
                    'plus_reklasifikasi_kib_d' => $getData->plus_reklasifikasi_kib_d ?? 0,
                    'plus_reklasifikasi_kib_e' => $getData->plus_reklasifikasi_kib_e ?? 0,
                    'plus_reklasifikasi_kdp' => $getData->plus_reklasifikasi_kdp ?? 0,
                    'plus_reklasifikasi_aset_lain_lain' => $getData->plus_reklasifikasi_aset_lain_lain ?? 0,
                    'plus_hibah_masuk' => $getData->plus_hibah_masuk ?? 0,
                    'plus_penilaian' => $getData->plus_penilaian ?? 0,
                    'plus_mutasi_antar_opd' => $getData->plus_mutasi_antar_opd ?? 0,
                    'plus_total' => $getData->plus_total ?? 0,

                    'min_pembayaran_utang' => $getData->min_pembayaran_utang ?? 0,
                    'min_reklasifikasi_beban_persediaan' => $getData->min_reklasifikasi_beban_persediaan ?? 0,
                    'min_reklasifikasi_beban_jasa' => $getData->min_reklasifikasi_beban_jasa ?? 0,
                    'min_reklasifikasi_beban_pemeliharaan' => $getData->min_reklasifikasi_beban_pemeliharaan ?? 0,
                    'min_reklasifikasi_beban_hibah' => $getData->min_reklasifikasi_beban_hibah ?? 0,
                    'min_reklasifikasi_beban_kib_a' => $getData->min_reklasifikasi_beban_kib_a ?? 0,
                    'min_reklasifikasi_beban_kib_b' => $getData->min_reklasifikasi_beban_kib_b ?? 0,
                    'min_reklasifikasi_beban_kib_c' => $getData->min_reklasifikasi_beban_kib_c ?? 0,
                    'min_reklasifikasi_beban_kib_d' => $getData->min_reklasifikasi_beban_kib_d ?? 0,
                    'min_reklasifikasi_beban_kib_e' => $getData->min_reklasifikasi_beban_kib_e ?? 0,
                    'min_reklasifikasi_beban_kdp' => $getData->min_reklasifikasi_beban_kdp ?? 0,
                    'min_reklasifikasi_beban_aset_lain_lain' => $getData->min_reklasifikasi_beban_aset_lain_lain ?? 0,
                    'min_penghapusan' => $getData->min_penghapusan ?? 0,
                    'min_mutasi_antar_opd' => $getData->min_mutasi_antar_opd ?? 0,
                    'min_tptgr' => $getData->min_tptgr ?? 0,
                    'min_total' => $getData->min_total ?? 0,
                ];
            }

            $datas['instances'] = $instances;
            $datas['datas'] = $values;

            return $this->successResponse($datas, 'Penyesuai Aset dan Beban berhasil diambil');
        } catch (\Exception $e) {
            return $this->errorResponse($e->getMessage() . ' ' . $e->getLine());
        }
    }

    function saveKibB(Request $request)
    {
        $validate = Validator::make($request->all(), [
            'instance' => 'nullable|exists:instances,id',
            'year' => 'required|integer',
            'periode' => 'required|exists:ref_periode,id'
        ], [], [
            'instance' => 'Instance ID',
            'periode' => 'Periode'
        ]);
        if ($validate->fails()) {
            return $this->validationResponse($validate->errors());
        }

        $now = now();
        DB::beginTransaction();
        try {
            $datas = $request->data;
            foreach ($datas as $input) {
                DB::table('acc_rek_as_kib_b')->updateOrInsert([
                    'periode_id' => $request->periode,
                    'year' => $request->year,
                    'instance_id' => $input['instance_id'],
                ], [
                    'periode_id' => $request->periode,
                    'year' => $request->year,
                    'instance_id' => $input['instance_id'],
                    'saldo_awal' => $input['saldo_awal'],
                    'saldo_akhir' => $input['saldo_akhir'],
                    'plus_realisasi_belanja' => $input['plus_realisasi_belanja'],
                    'plus_hutang_kegiatan' => $input['plus_hutang_kegiatan'],
                    'plus_atribusi' => $input['plus_atribusi'],
                    'plus_reklasifikasi_barang_habis_pakai' => $input['plus_reklasifikasi_barang_habis_pakai'],
                    'plus_reklasifikasi_pemeliharaan' => $input['plus_reklasifikasi_pemeliharaan'],
                    'plus_reklasifikasi_jasa' => $input['plus_reklasifikasi_jasa'],
                    'plus_reklasifikasi_kib_a' => $input['plus_reklasifikasi_kib_a'],
                    'plus_reklasifikasi_kib_b' => $input['plus_reklasifikasi_kib_b'],
                    'plus_reklasifikasi_kib_c' => $input['plus_reklasifikasi_kib_c'],
                    'plus_reklasifikasi_kib_d' => $input['plus_reklasifikasi_kib_d'],
                    'plus_reklasifikasi_kib_e' => $input['plus_reklasifikasi_kib_e'],
                    'plus_reklasifikasi_kdp' => $input['plus_reklasifikasi_kdp'],
                    'plus_reklasifikasi_aset_lain_lain' => $input['plus_reklasifikasi_aset_lain_lain'],
                    'plus_hibah_masuk' => $input['plus_hibah_masuk'],
                    'plus_penilaian' => $input['plus_penilaian'],
                    'plus_mutasi_antar_opd' => $input['plus_mutasi_antar_opd'],
                    'min_pembayaran_utang' => $input['min_pembayaran_utang'],
                    'min_reklasifikasi_beban_persediaan' => $input['min_reklasifikasi_beban_persediaan'],
                    'min_reklasifikasi_beban_jasa' => $input['min_reklasifikasi_beban_jasa'],
                    'min_reklasifikasi_beban_pemeliharaan' => $input['min_reklasifikasi_beban_pemeliharaan'],
                    'min_reklasifikasi_beban_hibah' => $input['min_reklasifikasi_beban_hibah'],
                    'min_reklasifikasi_beban_kib_a' => $input['min_reklasifikasi_beban_kib_a'],
                    'min_reklasifikasi_beban_kib_b' => $input['min_reklasifikasi_beban_kib_b'],
                    'min_reklasifikasi_beban_kib_c' => $input['min_reklasifikasi_beban_kib_c'],
                    'min_reklasifikasi_beban_kib_d' => $input['min_reklasifikasi_beban_kib_d'],
                    'min_reklasifikasi_beban_kib_e' => $input['min_reklasifikasi_beban_kib_e'],
                    'min_reklasifikasi_beban_kdp' => $input['min_reklasifikasi_beban_kdp'],
                    'min_reklasifikasi_beban_aset_lain_lain' => $input['min_reklasifikasi_beban_aset_lain_lain'],
                    'min_penghapusan' => $input['min_penghapusan'],
                    'min_mutasi_antar_opd' => $input['min_mutasi_antar_opd'],
                    'min_tptgr' => $input['min_tptgr'],
                    'updated_by' => auth()->user()->id,
                    'updated_at' => $now,
                ]);
            }

            DB::commit();
            return $this->successResponse(null, 'Data berhasil disimpan');
        } catch (\Exception $e) {
            DB::rollBack();
            return $this->errorResponse($e->getMessage() . ' ' . $e->getLine());
        }
    }
    // KIB B End

    // KIB C Start
    function getKibC(Request $request)
    {
        $validate = Validator::make($request->all(), [
            'instance' => 'nullable|exists:instances,id',
            'year' => 'required|integer',
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
            $instances = [];
            if (!$request->instance) {
                $instanceIds = DB::table('acc_lra')
                    ->select('instance_id')
                    ->groupBy('instance_id')
                    ->get();
                // $instances = Instance::whereIn('id', $instanceIds->pluck('instance_id'))->get();
                $instances = Instance::orderBy('name')->get();
            } elseif ($request->instance) {
                $instances = Instance::where('id', $request->instance)->get();
            }

            $values = [];
            foreach ($instances as $instance) {
                $dataLRA = DB::table('acc_lra')
                    ->where('instance_id', $instance->id)
                    ->where('year', $request->year)
                    ->where('periode_id', $request->periode)
                    ->where('kode_rekening', '5.2.03')
                    ->first();

                $getData = KibC::where('instance_id', $instance->id)
                    ->where('year', $request->year)
                    ->where('periode_id', $request->periode)
                    ->first();
                if (!$getData) {
                    DB::table('acc_rek_as_kib_c')->insert([
                        'periode_id' => $request->periode,
                        'year' => $request->year,
                        'instance_id' => $instance->id,
                        'plus_realisasi_belanja' => $dataLRA->realisasi ?? 0,
                    ]);

                    $getData = KibC::where('instance_id', $instance->id)
                        ->where('year', $request->year)
                        ->where('periode_id', $request->periode)
                        ->first();
                }
                // Hutang Belanja
                $HutangBaru = DB::table('acc_htb_hutang_baru')
                    ->where('instance_id', $instance->id)
                    ->where('year', $request->year)
                    ->where('periode_id', $request->periode)
                    ->sum('aset_tetap_gedung_bangunan') ?? 0;
                $getData->plus_hutang_kegiatan = $HutangBaru ?? 0;
                $PembayaranHutang = DB::table('acc_htb_pembayaran_hutang')
                    ->where('instance_id', $instance->id)
                    ->where('year', $request->year)
                    ->where('periode_id', $request->periode)
                    ->sum('aset_tetap_gedung_bangunan') ?? 0;
                $getData->min_pembayaran_utang = $PembayaranHutang ?? 0;

                // Atribusi
                $Atribusi = DB::table('acc_padb_atribusi')
                    ->where('instance_id', $instance->id)
                    ->where('year', $request->year)
                    ->where('periode_id', $request->periode)
                    ->sum('atri_aset_tetap_gedung_bangunan') ?? 0;
                $getData->plus_atribusi = $Atribusi ?? 0;

                // Reklasifikasi KIB
                $ReklasKib = DB::table('acc_padb_penyesuaian_aset')
                    ->where('instance_id', $instance->id)
                    ->where('year', $request->year)
                    ->where('periode_id', $request->periode)
                    ->get();
                // $getData->plus_reklasifikasi_kib_a = $ReklasKib->sum('min_aset_tetap_tanah') ?? 0;
                // $getData->min_reklasifikasi_beban_kib_a = $ReklasKib->sum('plus_aset_tetap_tanah') ?? 0;
                // $getData->plus_reklasifikasi_kib_b = $ReklasKib->sum('min_aset_tetap_peralatan_mesin') ?? 0;
                // $getData->min_reklasifikasi_beban_kib_b = $ReklasKib->sum('plus_aset_tetap_peralatan_mesin') ?? 0;
                // $getData->plus_reklasifikasi_kib_c = $ReklasKib->sum('min_aset_tetap_gedung_bangunan') ?? 0;
                // $getData->min_reklasifikasi_beban_kib_c = $ReklasKib->sum('plus_aset_tetap_gedung_bangunan') ?? 0;
                // $getData->plus_reklasifikasi_kib_d = $ReklasKib->sum('min_aset_tetap_jalan_jaringan_irigasi') ?? 0;
                // $getData->min_reklasifikasi_beban_kib_d = $ReklasKib->sum('plus_aset_tetap_jalan_jaringan_irigasi') ?? 0;
                // $getData->plus_reklasifikasi_kib_e = $ReklasKib->sum('min_aset_tetap_lainnya') ?? 0;
                // $getData->min_reklasifikasi_beban_kib_e = $ReklasKib->sum('plus_aset_tetap_lainnya') ?? 0;
                // $getData->plus_reklasifikasi_kdp = $ReklasKib->sum('min_konstruksi_dalam_pekerjaan') ?? 0;
                // $getData->min_reklasifikasi_beban_kdp = $ReklasKib->sum('plus_konstruksi_dalam_pekerjaan') ?? 0;
                // $getData->plus_reklasifikasi_aset_lain_lain = $ReklasKib->sum('min_aset_lain_lain') ?? 0;
                // $getData->min_reklasifikasi_beban_aset_lain_lain = $ReklasKib->sum('plus_aset_lain_lain') ?? 0;
                $plusReklasKey = 'plus_aset_tetap_gedung_bangunan';
                $minReklasKey = 'min_aset_tetap_gedung_bangunan';

                $getData->plus_reklasifikasi_kib_a = $ReklasKib->where($plusReklasKey, '>', 0)
                    ->where('min_aset_tetap_tanah', '>', 0)
                    ->sum($plusReklasKey) ?? 0;
                $getData->min_reklasifikasi_beban_kib_a = $ReklasKib->where($minReklasKey, '>', 0)
                    ->where('plus_aset_tetap_tanah', '>', 0)
                    ->sum($minReklasKey) ?? 0;

                $getData->plus_reklasifikasi_kib_b = $ReklasKib->where($plusReklasKey, '>', 0)
                    ->where('min_aset_tetap_tanah', '>', 0)
                    ->sum($plusReklasKey) ?? 0;
                $getData->min_reklasifikasi_beban_kib_b = $ReklasKib->where($minReklasKey, '>', 0)
                    ->where('plus_aset_tetap_peralatan_mesin', '>', 0)
                    ->sum($minReklasKey) ?? 0;

                $getData->plus_reklasifikasi_kib_c = $ReklasKib->where($plusReklasKey, '>', 0)
                    ->where('min_aset_tetap_gedung_bangunan', '>', 0)
                    ->sum($plusReklasKey) ?? 0;
                $getData->min_reklasifikasi_beban_kib_c = $ReklasKib->where($minReklasKey, '>', 0)
                    ->where('plus_aset_tetap_gedung_bangunan', '>', 0)
                    ->sum($minReklasKey) ?? 0;

                $getData->plus_reklasifikasi_kib_d = $ReklasKib->where($plusReklasKey, '>', 0)
                    ->where('min_aset_tetap_jalan_jaringan_irigasi', '>', 0)
                    ->sum($plusReklasKey) ?? 0;
                $getData->min_reklasifikasi_beban_kib_d = $ReklasKib->where($minReklasKey, '>', 0)
                    ->where('plus_aset_tetap_jalan_jaringan_irigasi', '>', 0)
                    ->sum($minReklasKey) ?? 0;

                $getData->plus_reklasifikasi_kib_e = $ReklasKib->where($plusReklasKey, '>', 0)
                    ->where('min_aset_tetap_lainnya', '>', 0)
                    ->sum($plusReklasKey) ?? 0;
                $getData->min_reklasifikasi_beban_kib_e = $ReklasKib->where($minReklasKey, '>', 0)
                    ->where('plus_aset_tetap_lainnya', '>', 0)
                    ->sum($minReklasKey) ?? 0;

                $getData->plus_reklasifikasi_kdp = $ReklasKib->where($plusReklasKey, '>', 0)
                    ->where('min_konstruksi_dalam_pekerjaan', '>', 0)
                    ->sum($plusReklasKey) ?? 0;
                $getData->min_reklasifikasi_beban_kdp = $ReklasKib->where($minReklasKey, '>', 0)
                    ->where('plus_konstruksi_dalam_pekerjaan', '>', 0)
                    ->sum($minReklasKey) ?? 0;

                $getData->plus_reklasifikasi_aset_lain_lain = $ReklasKib->where($plusReklasKey, '>', 0)
                    ->where('min_aset_lain_lain', '>', 0)
                    ->sum($plusReklasKey) ?? 0;
                $getData->min_reklasifikasi_beban_aset_lain_lain = $ReklasKib->where($minReklasKey, '>', 0)
                    ->where('plus_aset_lain_lain', '>', 0)
                    ->sum($minReklasKey) ?? 0;

                // Hibah Masuk
                $HibahMasuk = DB::table('acc_padb_tambahan_hibah_masuk')
                    ->where('instance_id', $instance->id)
                    ->where('year', $request->year)
                    ->where('periode_id', $request->periode)
                    ->sum('aset_tetap_gedung_bangunan') ?? 0;
                $getData->plus_hibah_masuk = $HibahMasuk ?? 0;

                // Hibah Keluar
                // $HibahKeluar = DB::table('acc_padb_tambahan_hibah_keluar')
                //     ->where('instance_id', $instance->id)
                //     ->where('year', $request->year)
                //     ->where('periode_id', $request->periode)
                //     ->sum('aset_tetap_gedung_bangunan') ?? 0;
                // $getData->min_reklasifikasi_beban_hibah = $HibahKeluar ?? 0;

                // Penilaian
                $PenilaianAset = DB::table('acc_padb_penilaian_aset')
                    ->where('instance_id', $instance->id)
                    ->where('year', $request->year)
                    ->where('periode_id', $request->periode)
                    ->sum('aset_tetap_gedung_bangunan') ?? 0;
                $getData->plus_penilaian = $PenilaianAset ?? 0;

                // Mutasi OPD
                $MutasiOPDMasuk = DB::table('acc_padb_tambahan_mutasi_aset')
                    // ->where('instance_id', $instance->id)
                    ->where('to_instance_id', $instance->id)
                    ->where('year', $request->year)
                    ->where('periode_id', $request->periode)
                    ->get();
                $MutasiOPDKeluar = DB::table('acc_padb_tambahan_mutasi_aset')
                    // ->where('instance_id', $instance->id)
                    ->where('from_instance_id', $instance->id)
                    ->where('year', $request->year)
                    ->where('periode_id', $request->periode)
                    ->get();
                $getData->plus_mutasi_antar_opd = $MutasiOPDMasuk->sum('plus_aset_tetap_gedung_bangunan') ?? 0;
                $getData->min_mutasi_antar_opd = $MutasiOPDKeluar->sum('plus_aset_tetap_gedung_bangunan') ?? 0;

                $minPenghapusan = DB::table('acc_padb_penghapusan_aset')
                    ->where('instance_id', $instance->id)
                    ->where('year', $request->year)
                    ->where('periode_id', $request->periode)
                    ->sum('aset_tetap_gedung_bangunan') ?? 0;
                $minPenjualan = DB::table('acc_padb_penjualan_aset')
                    ->where('instance_id', $instance->id)
                    ->where('year', $request->year)
                    ->where('periode_id', $request->periode)
                    ->sum('aset_tetap_gedung_bangunan') ?? 0;

                $getData->min_penghapusan = ($minPenghapusan + $minPenjualan) ?? 0;
                $getData->timestamps = false;
                $getData->save();

                $saldoAwal = $getData->saldo_awal ?? 0;

                $getData->plus_total = ($getData->plus_realisasi_belanja ?? 0) + ($getData->plus_hutang_kegiatan ?? 0) + ($getData->plus_atribusi ?? 0) + ($getData->plus_reklasifikasi_barang_habis_pakai ?? 0) + ($getData->plus_reklasifikasi_pemeliharaan ?? 0) + ($getData->plus_reklasifikasi_jasa ?? 0) + ($getData->plus_reklasifikasi_kib_a ?? 0) + ($getData->plus_reklasifikasi_kib_b ?? 0) + ($getData->plus_reklasifikasi_kib_c ?? 0) + ($getData->plus_reklasifikasi_kib_d ?? 0) + ($getData->plus_reklasifikasi_kib_e ?? 0) + ($getData->plus_reklasifikasi_kdp ?? 0) + ($getData->plus_reklasifikasi_aset_lain_lain ?? 0) + ($getData->plus_hibah_masuk ?? 0) + ($getData->plus_penilaian ?? 0) + ($getData->plus_mutasi_antar_opd ?? 0);

                $getData->min_total = ($getData->min_pembayaran_utang ?? 0) + ($getData->min_reklasifikasi_beban_persediaan ?? 0) + ($getData->min_reklasifikasi_beban_jasa ?? 0) + ($getData->min_reklasifikasi_beban_pemeliharaan ?? 0) + ($getData->min_reklasifikasi_beban_hibah ?? 0) + ($getData->min_reklasifikasi_beban_kib_a ?? 0) + ($getData->min_reklasifikasi_beban_kib_b ?? 0) + ($getData->min_reklasifikasi_beban_kib_c ?? 0) + ($getData->min_reklasifikasi_beban_kib_d ?? 0) + ($getData->min_reklasifikasi_beban_kib_e ?? 0) + ($getData->min_reklasifikasi_beban_kdp ?? 0) + ($getData->min_reklasifikasi_beban_aset_lain_lain ?? 0) + ($getData->min_penghapusan ?? 0) + ($getData->min_mutasi_antar_opd ?? 0) + ($getData->min_tptgr ?? 0);

                $calculateSaldoAkhir = $saldoAwal + ($getData->plus_total ?? 0) - ($getData->min_total ?? 0);

                DB::table('acc_rek_as_kib_c')
                    ->where('id', $getData->id)
                    ->update([
                        'saldo_akhir' => $calculateSaldoAkhir,
                    ]);

                $values[] = [
                    'data_id' => $getData->id,
                    'instance_id' => $instance->id,
                    'instance_name' => $instance->name,
                    'saldo_awal' => $saldoAwal ?? 0,
                    'saldo_akhir' => $calculateSaldoAkhir,

                    'plus_realisasi_belanja' => $dataLRA->realisasi ?? $getData->plus_realisasi_belanja ?? 0,
                    'plus_hutang_kegiatan' => $getData->plus_hutang_kegiatan ?? 0,
                    'plus_atribusi' => $getData->plus_atribusi ?? 0,
                    'plus_reklasifikasi_barang_habis_pakai' => $getData->plus_reklasifikasi_barang_habis_pakai ?? 0,
                    'plus_reklasifikasi_pemeliharaan' => $getData->plus_reklasifikasi_pemeliharaan ?? 0,
                    'plus_reklasifikasi_jasa' => $getData->plus_reklasifikasi_jasa ?? 0,
                    'plus_reklasifikasi_kib_a' => $getData->plus_reklasifikasi_kib_a ?? 0,
                    'plus_reklasifikasi_kib_b' => $getData->plus_reklasifikasi_kib_b ?? 0,
                    'plus_reklasifikasi_kib_c' => $getData->plus_reklasifikasi_kib_c ?? 0,
                    'plus_reklasifikasi_kib_d' => $getData->plus_reklasifikasi_kib_d ?? 0,
                    'plus_reklasifikasi_kib_e' => $getData->plus_reklasifikasi_kib_e ?? 0,
                    'plus_reklasifikasi_kdp' => $getData->plus_reklasifikasi_kdp ?? 0,
                    'plus_reklasifikasi_aset_lain_lain' => $getData->plus_reklasifikasi_aset_lain_lain ?? 0,
                    'plus_hibah_masuk' => $getData->plus_hibah_masuk ?? 0,
                    'plus_penilaian' => $getData->plus_penilaian ?? 0,
                    'plus_mutasi_antar_opd' => $getData->plus_mutasi_antar_opd ?? 0,
                    'plus_total' => $getData->plus_total ?? 0,

                    'min_pembayaran_utang' => $getData->min_pembayaran_utang ?? 0,
                    'min_reklasifikasi_beban_persediaan' => $getData->min_reklasifikasi_beban_persediaan ?? 0,
                    'min_reklasifikasi_beban_jasa' => $getData->min_reklasifikasi_beban_jasa ?? 0,
                    'min_reklasifikasi_beban_pemeliharaan' => $getData->min_reklasifikasi_beban_pemeliharaan ?? 0,
                    'min_reklasifikasi_beban_hibah' => $getData->min_reklasifikasi_beban_hibah ?? 0,
                    'min_reklasifikasi_beban_kib_a' => $getData->min_reklasifikasi_beban_kib_a ?? 0,
                    'min_reklasifikasi_beban_kib_b' => $getData->min_reklasifikasi_beban_kib_b ?? 0,
                    'min_reklasifikasi_beban_kib_c' => $getData->min_reklasifikasi_beban_kib_c ?? 0,
                    'min_reklasifikasi_beban_kib_d' => $getData->min_reklasifikasi_beban_kib_d ?? 0,
                    'min_reklasifikasi_beban_kib_e' => $getData->min_reklasifikasi_beban_kib_e ?? 0,
                    'min_reklasifikasi_beban_kdp' => $getData->min_reklasifikasi_beban_kdp ?? 0,
                    'min_reklasifikasi_beban_aset_lain_lain' => $getData->min_reklasifikasi_beban_aset_lain_lain ?? 0,
                    'min_penghapusan' => $getData->min_penghapusan ?? 0,
                    'min_mutasi_antar_opd' => $getData->min_mutasi_antar_opd ?? 0,
                    'min_tptgr' => $getData->min_tptgr ?? 0,
                    'min_total' => $getData->min_total ?? 0,
                ];
            }

            $datas['instances'] = $instances;
            $datas['datas'] = $values;

            return $this->successResponse($datas, 'Penyesuai Aset dan Beban berhasil diambil');
        } catch (\Exception $e) {
            return $this->errorResponse($e->getMessage() . ' ' . $e->getLine());
        }
    }

    function saveKibC(Request $request)
    {
        $validate = Validator::make($request->all(), [
            'instance' => 'nullable|exists:instances,id',
            'year' => 'required|integer',
            'periode' => 'required|exists:ref_periode,id'
        ], [], [
            'instance' => 'Instance ID',
            'periode' => 'Periode'
        ]);
        if ($validate->fails()) {
            return $this->validationResponse($validate->errors());
        }

        $now = now();
        DB::beginTransaction();
        try {
            $datas = $request->data;
            foreach ($datas as $input) {
                DB::table('acc_rek_as_kib_c')->updateOrInsert([
                    'periode_id' => $request->periode,
                    'year' => $request->year,
                    'instance_id' => $input['instance_id'],
                ], [
                    'periode_id' => $request->periode,
                    'year' => $request->year,
                    'instance_id' => $input['instance_id'],
                    'saldo_awal' => $input['saldo_awal'],
                    'saldo_akhir' => $input['saldo_akhir'],
                    'plus_realisasi_belanja' => $input['plus_realisasi_belanja'],
                    'plus_hutang_kegiatan' => $input['plus_hutang_kegiatan'],
                    'plus_atribusi' => $input['plus_atribusi'],
                    'plus_reklasifikasi_barang_habis_pakai' => $input['plus_reklasifikasi_barang_habis_pakai'],
                    'plus_reklasifikasi_pemeliharaan' => $input['plus_reklasifikasi_pemeliharaan'],
                    'plus_reklasifikasi_jasa' => $input['plus_reklasifikasi_jasa'],
                    'plus_reklasifikasi_kib_a' => $input['plus_reklasifikasi_kib_a'],
                    'plus_reklasifikasi_kib_b' => $input['plus_reklasifikasi_kib_b'],
                    'plus_reklasifikasi_kib_c' => $input['plus_reklasifikasi_kib_c'],
                    'plus_reklasifikasi_kib_d' => $input['plus_reklasifikasi_kib_d'],
                    'plus_reklasifikasi_kib_e' => $input['plus_reklasifikasi_kib_e'],
                    'plus_reklasifikasi_kdp' => $input['plus_reklasifikasi_kdp'],
                    'plus_reklasifikasi_aset_lain_lain' => $input['plus_reklasifikasi_aset_lain_lain'],
                    'plus_hibah_masuk' => $input['plus_hibah_masuk'],
                    'plus_penilaian' => $input['plus_penilaian'],
                    'plus_mutasi_antar_opd' => $input['plus_mutasi_antar_opd'],
                    'min_pembayaran_utang' => $input['min_pembayaran_utang'],
                    'min_reklasifikasi_beban_persediaan' => $input['min_reklasifikasi_beban_persediaan'],
                    'min_reklasifikasi_beban_jasa' => $input['min_reklasifikasi_beban_jasa'],
                    'min_reklasifikasi_beban_pemeliharaan' => $input['min_reklasifikasi_beban_pemeliharaan'],
                    'min_reklasifikasi_beban_hibah' => $input['min_reklasifikasi_beban_hibah'],
                    'min_reklasifikasi_beban_kib_a' => $input['min_reklasifikasi_beban_kib_a'],
                    'min_reklasifikasi_beban_kib_b' => $input['min_reklasifikasi_beban_kib_b'],
                    'min_reklasifikasi_beban_kib_c' => $input['min_reklasifikasi_beban_kib_c'],
                    'min_reklasifikasi_beban_kib_d' => $input['min_reklasifikasi_beban_kib_d'],
                    'min_reklasifikasi_beban_kib_e' => $input['min_reklasifikasi_beban_kib_e'],
                    'min_reklasifikasi_beban_kdp' => $input['min_reklasifikasi_beban_kdp'],
                    'min_reklasifikasi_beban_aset_lain_lain' => $input['min_reklasifikasi_beban_aset_lain_lain'],
                    'min_penghapusan' => $input['min_penghapusan'],
                    'min_mutasi_antar_opd' => $input['min_mutasi_antar_opd'],
                    'min_tptgr' => $input['min_tptgr'],
                    'updated_by' => auth()->user()->id,
                    'updated_at' => $now,
                ]);
            }

            DB::commit();
            return $this->successResponse(null, 'Data berhasil disimpan');
        } catch (\Exception $e) {
            DB::rollBack();
            return $this->errorResponse($e->getMessage() . ' ' . $e->getLine());
        }
    }
    // KIB C End

    // KIB D Start
    function getKibD(Request $request)
    {
        $validate = Validator::make($request->all(), [
            'instance' => 'nullable|exists:instances,id',
            'year' => 'required|integer',
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
            $instances = [];
            if (!$request->instance) {
                $instanceIds = DB::table('acc_lra')
                    ->select('instance_id')
                    ->groupBy('instance_id')
                    ->get();
                // $instances = Instance::whereIn('id', $instanceIds->pluck('instance_id'))->get();
                $instances = Instance::orderBy('name')->get();
            } elseif ($request->instance) {
                $instances = Instance::where('id', $request->instance)->get();
            }

            $values = [];
            foreach ($instances as $instance) {
                $dataLRA = DB::table('acc_lra')
                    ->where('instance_id', $instance->id)
                    ->where('year', $request->year)
                    ->where('periode_id', $request->periode)
                    ->where('kode_rekening', '5.2.04')
                    ->first();

                $getData = KibD::where('instance_id', $instance->id)
                    ->where('year', $request->year)
                    ->where('periode_id', $request->periode)
                    ->first();
                if (!$getData) {
                    DB::table('acc_rek_as_kib_d')->insert([
                        'periode_id' => $request->periode,
                        'year' => $request->year,
                        'instance_id' => $instance->id,
                        'plus_realisasi_belanja' => $dataLRA->realisasi ?? 0,
                    ]);

                    $getData = KibD::where('instance_id', $instance->id)
                        ->where('year', $request->year)
                        ->where('periode_id', $request->periode)
                        ->first();
                }
                // Hutang Belanja
                $HutangBaru = DB::table('acc_htb_hutang_baru')
                    ->where('instance_id', $instance->id)
                    ->where('year', $request->year)
                    ->where('periode_id', $request->periode)
                    ->sum('aset_tetap_jalan_jaringan_irigasi') ?? 0;
                $getData->plus_hutang_kegiatan = $HutangBaru ?? 0;
                $PembayaranHutang = DB::table('acc_htb_pembayaran_hutang')
                    ->where('instance_id', $instance->id)
                    ->where('year', $request->year)
                    ->where('periode_id', $request->periode)
                    ->sum('aset_tetap_jalan_jaringan_irigasi') ?? 0;
                $getData->min_pembayaran_utang = $PembayaranHutang ?? 0;

                // Atribusi
                $Atribusi = DB::table('acc_padb_atribusi')
                    ->where('instance_id', $instance->id)
                    ->where('year', $request->year)
                    ->where('periode_id', $request->periode)
                    ->sum('atri_aset_tetap_jalan_jaringan_irigasi') ?? 0;
                $getData->plus_atribusi = $Atribusi ?? 0;

                // Reklasifikasi KIB
                $ReklasKib = DB::table('acc_padb_penyesuaian_aset')
                    ->where('instance_id', $instance->id)
                    ->where('year', $request->year)
                    ->where('periode_id', $request->periode)
                    ->get();
                // $getData->plus_reklasifikasi_kib_a = $ReklasKib->sum('min_aset_tetap_tanah') ?? 0;
                // $getData->min_reklasifikasi_beban_kib_a = $ReklasKib->sum('plus_aset_tetap_tanah') ?? 0;
                // $getData->plus_reklasifikasi_kib_b = $ReklasKib->sum('min_aset_tetap_peralatan_mesin') ?? 0;
                // $getData->min_reklasifikasi_beban_kib_b = $ReklasKib->sum('plus_aset_tetap_peralatan_mesin') ?? 0;
                // $getData->plus_reklasifikasi_kib_c = $ReklasKib->sum('min_aset_tetap_gedung_bangunan') ?? 0;
                // $getData->min_reklasifikasi_beban_kib_c = $ReklasKib->sum('plus_aset_tetap_gedung_bangunan') ?? 0;
                // $getData->plus_reklasifikasi_kib_d = $ReklasKib->sum('min_aset_tetap_jalan_jaringan_irigasi') ?? 0;
                // $getData->min_reklasifikasi_beban_kib_d = $ReklasKib->sum('plus_aset_tetap_jalan_jaringan_irigasi') ?? 0;
                // $getData->plus_reklasifikasi_kib_e = $ReklasKib->sum('min_aset_tetap_lainnya') ?? 0;
                // $getData->min_reklasifikasi_beban_kib_e = $ReklasKib->sum('plus_aset_tetap_lainnya') ?? 0;
                // $getData->plus_reklasifikasi_kdp = $ReklasKib->sum('min_konstruksi_dalam_pekerjaan') ?? 0;
                // $getData->min_reklasifikasi_beban_kdp = $ReklasKib->sum('plus_konstruksi_dalam_pekerjaan') ?? 0;
                // $getData->plus_reklasifikasi_aset_lain_lain = $ReklasKib->sum('min_aset_lain_lain') ?? 0;
                // $getData->min_reklasifikasi_beban_aset_lain_lain = $ReklasKib->sum('plus_aset_lain_lain') ?? 0;
                $plusReklasKey = 'plus_aset_tetap_jalan_jaringan_irigasi';
                $minReklasKey = 'min_aset_tetap_jalan_jaringan_irigasi';

                $getData->plus_reklasifikasi_kib_a = $ReklasKib->where($plusReklasKey, '>', 0)
                    ->where('min_aset_tetap_tanah', '>', 0)
                    ->sum($plusReklasKey) ?? 0;
                $getData->min_reklasifikasi_beban_kib_a = $ReklasKib->where($minReklasKey, '>', 0)
                    ->where('plus_aset_tetap_tanah', '>', 0)
                    ->sum($minReklasKey) ?? 0;

                $getData->plus_reklasifikasi_kib_b = $ReklasKib->where($plusReklasKey, '>', 0)
                    ->where('min_aset_tetap_tanah', '>', 0)
                    ->sum($plusReklasKey) ?? 0;
                $getData->min_reklasifikasi_beban_kib_b = $ReklasKib->where($minReklasKey, '>', 0)
                    ->where('plus_aset_tetap_peralatan_mesin', '>', 0)
                    ->sum($minReklasKey) ?? 0;

                $getData->plus_reklasifikasi_kib_c = $ReklasKib->where($plusReklasKey, '>', 0)
                    ->where('min_aset_tetap_gedung_bangunan', '>', 0)
                    ->sum($plusReklasKey) ?? 0;
                $getData->min_reklasifikasi_beban_kib_c = $ReklasKib->where($minReklasKey, '>', 0)
                    ->where('plus_aset_tetap_gedung_bangunan', '>', 0)
                    ->sum($minReklasKey) ?? 0;

                $getData->plus_reklasifikasi_kib_d = $ReklasKib->where($plusReklasKey, '>', 0)
                    ->where('min_aset_tetap_jalan_jaringan_irigasi', '>', 0)
                    ->sum($plusReklasKey) ?? 0;
                $getData->min_reklasifikasi_beban_kib_d = $ReklasKib->where($minReklasKey, '>', 0)
                    ->where('plus_aset_tetap_jalan_jaringan_irigasi', '>', 0)
                    ->sum($minReklasKey) ?? 0;

                $getData->plus_reklasifikasi_kib_e = $ReklasKib->where($plusReklasKey, '>', 0)
                    ->where('min_aset_tetap_lainnya', '>', 0)
                    ->sum($plusReklasKey) ?? 0;
                $getData->min_reklasifikasi_beban_kib_e = $ReklasKib->where($minReklasKey, '>', 0)
                    ->where('plus_aset_tetap_lainnya', '>', 0)
                    ->sum($minReklasKey) ?? 0;

                $getData->plus_reklasifikasi_kdp = $ReklasKib->where($plusReklasKey, '>', 0)
                    ->where('min_konstruksi_dalam_pekerjaan', '>', 0)
                    ->sum($plusReklasKey) ?? 0;
                $getData->min_reklasifikasi_beban_kdp = $ReklasKib->where($minReklasKey, '>', 0)
                    ->where('plus_konstruksi_dalam_pekerjaan', '>', 0)
                    ->sum($minReklasKey) ?? 0;

                $getData->plus_reklasifikasi_aset_lain_lain = $ReklasKib->where($plusReklasKey, '>', 0)
                    ->where('min_aset_lain_lain', '>', 0)
                    ->sum($plusReklasKey) ?? 0;
                $getData->min_reklasifikasi_beban_aset_lain_lain = $ReklasKib->where($minReklasKey, '>', 0)
                    ->where('plus_aset_lain_lain', '>', 0)
                    ->sum($minReklasKey) ?? 0;

                // Hibah Masuk
                $HibahMasuk = DB::table('acc_padb_tambahan_hibah_masuk')
                    ->where('instance_id', $instance->id)
                    ->where('year', $request->year)
                    ->where('periode_id', $request->periode)
                    ->sum('aset_tetap_jalan_jaringan_irigasi') ?? 0;
                $getData->plus_hibah_masuk = $HibahMasuk ?? 0;

                // Hibah Keluar
                $HibahKeluar = DB::table('acc_padb_tambahan_hibah_keluar')
                    ->where('instance_id', $instance->id)
                    ->where('year', $request->year)
                    ->where('periode_id', $request->periode)
                    ->sum('aset_tetap_jalan_jaringan_irigasi') ?? 0;
                $getData->min_reklasifikasi_beban_hibah = $HibahKeluar ?? 0;

                // Penilaian
                $PenilaianAset = DB::table('acc_padb_penilaian_aset')
                    ->where('instance_id', $instance->id)
                    ->where('year', $request->year)
                    ->where('periode_id', $request->periode)
                    ->sum('aset_tetap_jalan_jaringan_irigasi') ?? 0;
                $getData->plus_penilaian = $PenilaianAset ?? 0;

                // Mutasi OPD
                $MutasiOPDMasuk = DB::table('acc_padb_tambahan_mutasi_aset')
                    // ->where('instance_id', $instance->id)
                    ->where('to_instance_id', $instance->id)
                    ->where('year', $request->year)
                    ->where('periode_id', $request->periode)
                    ->get();
                $MutasiOPDKeluar = DB::table('acc_padb_tambahan_mutasi_aset')
                    // ->where('instance_id', $instance->id)
                    ->where('from_instance_id', $instance->id)
                    ->where('year', $request->year)
                    ->where('periode_id', $request->periode)
                    ->get();
                $getData->plus_mutasi_antar_opd = $MutasiOPDMasuk->sum('plus_aset_tetap_jalan_jaringan_irigasi') ?? 0;
                $getData->min_mutasi_antar_opd = $MutasiOPDKeluar->sum('plus_aset_tetap_jalan_jaringan_irigasi') ?? 0;

                $minPenghapusan = DB::table('acc_padb_penghapusan_aset')
                    ->where('instance_id', $instance->id)
                    ->where('year', $request->year)
                    ->where('periode_id', $request->periode)
                    ->sum('aset_tetap_jalan_jaringan_irigasi') ?? 0;
                $minPenjualan = DB::table('acc_padb_penjualan_aset')
                    ->where('instance_id', $instance->id)
                    ->where('year', $request->year)
                    ->where('periode_id', $request->periode)
                    ->sum('aset_tetap_jalan_jaringan_irigasi') ?? 0;

                $getData->min_penghapusan = ($minPenghapusan + $minPenjualan) ?? 0;
                $getData->timestamps = false;
                $getData->save();

                $saldoAwal = $getData->saldo_awal ?? 0;

                $getData->plus_total = ($getData->plus_realisasi_belanja ?? 0) + ($getData->plus_hutang_kegiatan ?? 0) + ($getData->plus_atribusi ?? 0) + ($getData->plus_reklasifikasi_barang_habis_pakai ?? 0) + ($getData->plus_reklasifikasi_pemeliharaan ?? 0) + ($getData->plus_reklasifikasi_jasa ?? 0) + ($getData->plus_reklasifikasi_kib_a ?? 0) + ($getData->plus_reklasifikasi_kib_b ?? 0) + ($getData->plus_reklasifikasi_kib_c ?? 0) + ($getData->plus_reklasifikasi_kib_d ?? 0) + ($getData->plus_reklasifikasi_kib_e ?? 0) + ($getData->plus_reklasifikasi_kdp ?? 0) + ($getData->plus_reklasifikasi_aset_lain_lain ?? 0) + ($getData->plus_hibah_masuk ?? 0) + ($getData->plus_penilaian ?? 0) + ($getData->plus_mutasi_antar_opd ?? 0);

                $getData->min_total = ($getData->min_pembayaran_utang ?? 0) + ($getData->min_reklasifikasi_beban_persediaan ?? 0) + ($getData->min_reklasifikasi_beban_jasa ?? 0) + ($getData->min_reklasifikasi_beban_pemeliharaan ?? 0) + ($getData->min_reklasifikasi_beban_hibah ?? 0) + ($getData->min_reklasifikasi_beban_kib_a ?? 0) + ($getData->min_reklasifikasi_beban_kib_b ?? 0) + ($getData->min_reklasifikasi_beban_kib_c ?? 0) + ($getData->min_reklasifikasi_beban_kib_d ?? 0) + ($getData->min_reklasifikasi_beban_kib_e ?? 0) + ($getData->min_reklasifikasi_beban_kdp ?? 0) + ($getData->min_reklasifikasi_beban_aset_lain_lain ?? 0) + ($getData->min_penghapusan ?? 0) + ($getData->min_mutasi_antar_opd ?? 0) + ($getData->min_tptgr ?? 0);

                $calculateSaldoAkhir = $saldoAwal + ($getData->plus_total ?? 0) - ($getData->min_total ?? 0);

                DB::table('acc_rek_as_kib_d')
                    ->where('id', $getData->id)
                    ->update([
                        'saldo_akhir' => $calculateSaldoAkhir,
                    ]);

                $values[] = [
                    'data_id' => $getData->id,
                    'instance_id' => $instance->id,
                    'instance_name' => $instance->name,
                    'saldo_awal' => $saldoAwal ?? 0,
                    'saldo_akhir' => $calculateSaldoAkhir,

                    'plus_realisasi_belanja' => $dataLRA->realisasi ?? $getData->plus_realisasi_belanja ?? 0,
                    'plus_hutang_kegiatan' => $getData->plus_hutang_kegiatan ?? 0,
                    'plus_atribusi' => $getData->plus_atribusi ?? 0,
                    'plus_reklasifikasi_barang_habis_pakai' => $getData->plus_reklasifikasi_barang_habis_pakai ?? 0,
                    'plus_reklasifikasi_pemeliharaan' => $getData->plus_reklasifikasi_pemeliharaan ?? 0,
                    'plus_reklasifikasi_jasa' => $getData->plus_reklasifikasi_jasa ?? 0,
                    'plus_reklasifikasi_kib_a' => $getData->plus_reklasifikasi_kib_a ?? 0,
                    'plus_reklasifikasi_kib_b' => $getData->plus_reklasifikasi_kib_b ?? 0,
                    'plus_reklasifikasi_kib_c' => $getData->plus_reklasifikasi_kib_c ?? 0,
                    'plus_reklasifikasi_kib_d' => $getData->plus_reklasifikasi_kib_d ?? 0,
                    'plus_reklasifikasi_kib_e' => $getData->plus_reklasifikasi_kib_e ?? 0,
                    'plus_reklasifikasi_kdp' => $getData->plus_reklasifikasi_kdp ?? 0,
                    'plus_reklasifikasi_aset_lain_lain' => $getData->plus_reklasifikasi_aset_lain_lain ?? 0,
                    'plus_hibah_masuk' => $getData->plus_hibah_masuk ?? 0,
                    'plus_penilaian' => $getData->plus_penilaian ?? 0,
                    'plus_mutasi_antar_opd' => $getData->plus_mutasi_antar_opd ?? 0,
                    'plus_total' => $getData->plus_total ?? 0,

                    'min_pembayaran_utang' => $getData->min_pembayaran_utang ?? 0,
                    'min_reklasifikasi_beban_persediaan' => $getData->min_reklasifikasi_beban_persediaan ?? 0,
                    'min_reklasifikasi_beban_jasa' => $getData->min_reklasifikasi_beban_jasa ?? 0,
                    'min_reklasifikasi_beban_pemeliharaan' => $getData->min_reklasifikasi_beban_pemeliharaan ?? 0,
                    'min_reklasifikasi_beban_hibah' => $getData->min_reklasifikasi_beban_hibah ?? 0,
                    'min_reklasifikasi_beban_kib_a' => $getData->min_reklasifikasi_beban_kib_a ?? 0,
                    'min_reklasifikasi_beban_kib_b' => $getData->min_reklasifikasi_beban_kib_b ?? 0,
                    'min_reklasifikasi_beban_kib_c' => $getData->min_reklasifikasi_beban_kib_c ?? 0,
                    'min_reklasifikasi_beban_kib_d' => $getData->min_reklasifikasi_beban_kib_d ?? 0,
                    'min_reklasifikasi_beban_kib_e' => $getData->min_reklasifikasi_beban_kib_e ?? 0,
                    'min_reklasifikasi_beban_kdp' => $getData->min_reklasifikasi_beban_kdp ?? 0,
                    'min_reklasifikasi_beban_aset_lain_lain' => $getData->min_reklasifikasi_beban_aset_lain_lain ?? 0,
                    'min_penghapusan' => $getData->min_penghapusan ?? 0,
                    'min_mutasi_antar_opd' => $getData->min_mutasi_antar_opd ?? 0,
                    'min_tptgr' => $getData->min_tptgr ?? 0,
                    'min_total' => $getData->min_total ?? 0,
                ];
            }

            $datas['instances'] = $instances;
            $datas['datas'] = $values;

            return $this->successResponse($datas, 'Penyesuai Aset dan Beban berhasil diambil');
        } catch (\Exception $e) {
            return $this->errorResponse($e->getMessage() . ' ' . $e->getLine());
        }
    }

    function saveKibD(Request $request)
    {
        $validate = Validator::make($request->all(), [
            'instance' => 'nullable|exists:instances,id',
            'year' => 'required|integer',
            'periode' => 'required|exists:ref_periode,id'
        ], [], [
            'instance' => 'Instance ID',
            'periode' => 'Periode'
        ]);
        if ($validate->fails()) {
            return $this->validationResponse($validate->errors());
        }

        $now = now();
        DB::beginTransaction();
        try {
            $datas = $request->data;
            foreach ($datas as $input) {
                DB::table('acc_rek_as_kib_d')->updateOrInsert([
                    'periode_id' => $request->periode,
                    'year' => $request->year,
                    'instance_id' => $input['instance_id'],
                ], [
                    'periode_id' => $request->periode,
                    'year' => $request->year,
                    'instance_id' => $input['instance_id'],
                    'saldo_awal' => $input['saldo_awal'],
                    'saldo_akhir' => $input['saldo_akhir'],
                    'plus_realisasi_belanja' => $input['plus_realisasi_belanja'],
                    'plus_hutang_kegiatan' => $input['plus_hutang_kegiatan'],
                    'plus_atribusi' => $input['plus_atribusi'],
                    'plus_reklasifikasi_barang_habis_pakai' => $input['plus_reklasifikasi_barang_habis_pakai'],
                    'plus_reklasifikasi_pemeliharaan' => $input['plus_reklasifikasi_pemeliharaan'],
                    'plus_reklasifikasi_jasa' => $input['plus_reklasifikasi_jasa'],
                    'plus_reklasifikasi_kib_a' => $input['plus_reklasifikasi_kib_a'],
                    'plus_reklasifikasi_kib_b' => $input['plus_reklasifikasi_kib_b'],
                    'plus_reklasifikasi_kib_c' => $input['plus_reklasifikasi_kib_c'],
                    'plus_reklasifikasi_kib_d' => $input['plus_reklasifikasi_kib_d'],
                    'plus_reklasifikasi_kib_e' => $input['plus_reklasifikasi_kib_e'],
                    'plus_reklasifikasi_kdp' => $input['plus_reklasifikasi_kdp'],
                    'plus_reklasifikasi_aset_lain_lain' => $input['plus_reklasifikasi_aset_lain_lain'],
                    'plus_hibah_masuk' => $input['plus_hibah_masuk'],
                    'plus_penilaian' => $input['plus_penilaian'],
                    'plus_mutasi_antar_opd' => $input['plus_mutasi_antar_opd'],
                    'min_pembayaran_utang' => $input['min_pembayaran_utang'],
                    'min_reklasifikasi_beban_persediaan' => $input['min_reklasifikasi_beban_persediaan'],
                    'min_reklasifikasi_beban_jasa' => $input['min_reklasifikasi_beban_jasa'],
                    'min_reklasifikasi_beban_pemeliharaan' => $input['min_reklasifikasi_beban_pemeliharaan'],
                    'min_reklasifikasi_beban_hibah' => $input['min_reklasifikasi_beban_hibah'],
                    'min_reklasifikasi_beban_kib_a' => $input['min_reklasifikasi_beban_kib_a'],
                    'min_reklasifikasi_beban_kib_b' => $input['min_reklasifikasi_beban_kib_b'],
                    'min_reklasifikasi_beban_kib_c' => $input['min_reklasifikasi_beban_kib_c'],
                    'min_reklasifikasi_beban_kib_d' => $input['min_reklasifikasi_beban_kib_d'],
                    'min_reklasifikasi_beban_kib_e' => $input['min_reklasifikasi_beban_kib_e'],
                    'min_reklasifikasi_beban_kdp' => $input['min_reklasifikasi_beban_kdp'],
                    'min_reklasifikasi_beban_aset_lain_lain' => $input['min_reklasifikasi_beban_aset_lain_lain'],
                    'min_penghapusan' => $input['min_penghapusan'],
                    'min_mutasi_antar_opd' => $input['min_mutasi_antar_opd'],
                    'min_tptgr' => $input['min_tptgr'],
                    'updated_by' => auth()->user()->id,
                    'updated_at' => $now,
                ]);
            }

            DB::commit();
            return $this->successResponse(null, 'Data berhasil disimpan');
        } catch (\Exception $e) {
            DB::rollBack();
            return $this->errorResponse($e->getMessage() . ' ' . $e->getLine());
        }
    }
    // KIB D End

    // KIB E Start
    function getKibE(Request $request)
    {
        $validate = Validator::make($request->all(), [
            'instance' => 'nullable|exists:instances,id',
            'year' => 'required|integer',
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
            $instances = [];
            if (!$request->instance) {
                $instanceIds = DB::table('acc_lra')
                    ->select('instance_id')
                    ->groupBy('instance_id')
                    ->get();
                // $instances = Instance::whereIn('id', $instanceIds->pluck('instance_id'))->get();
                $instances = Instance::orderBy('name')->get();
            } elseif ($request->instance) {
                $instances = Instance::where('id', $request->instance)->get();
            }

            $values = [];
            foreach ($instances as $instance) {
                $dataLRA = DB::table('acc_lra')
                    ->where('instance_id', $instance->id)
                    ->where('year', $request->year)
                    ->where('periode_id', $request->periode)
                    ->where('kode_rekening', '5.2.05')
                    ->first();

                $getData = KibE::where('instance_id', $instance->id)
                    ->where('year', $request->year)
                    ->where('periode_id', $request->periode)
                    ->first();
                if (!$getData) {
                    DB::table('acc_rek_as_kib_e')->insert([
                        'periode_id' => $request->periode,
                        'year' => $request->year,
                        'instance_id' => $instance->id,
                        'plus_realisasi_belanja' => $dataLRA->realisasi ?? 0,
                    ]);

                    $getData = KibE::where('instance_id', $instance->id)
                        ->where('year', $request->year)
                        ->where('periode_id', $request->periode)
                        ->first();
                }
                // Hutang Belanja
                $HutangBaru = DB::table('acc_htb_hutang_baru')
                    ->where('instance_id', $instance->id)
                    ->where('year', $request->year)
                    ->where('periode_id', $request->periode)
                    ->sum('aset_tetap_lainnya') ?? 0;
                $getData->plus_hutang_kegiatan = $HutangBaru ?? 0;
                $PembayaranHutang = DB::table('acc_htb_pembayaran_hutang')
                    ->where('instance_id', $instance->id)
                    ->where('year', $request->year)
                    ->where('periode_id', $request->periode)
                    ->sum('aset_tetap_lainnya') ?? 0;
                $getData->min_pembayaran_utang = $PembayaranHutang ?? 0;

                // Atribusi
                $Atribusi = DB::table('acc_padb_atribusi')
                    ->where('instance_id', $instance->id)
                    ->where('year', $request->year)
                    ->where('periode_id', $request->periode)
                    ->sum('atri_aset_tetap_tetap_lainnya') ?? 0;
                $getData->plus_atribusi = $Atribusi ?? 0;

                // Reklasifikasi KIB
                $ReklasKib = DB::table('acc_padb_penyesuaian_aset')
                    ->where('instance_id', $instance->id)
                    ->where('year', $request->year)
                    ->where('periode_id', $request->periode)
                    ->get();
                // $getData->plus_reklasifikasi_kib_a = $ReklasKib->sum('min_aset_tetap_tanah') ?? 0;
                // $getData->min_reklasifikasi_beban_kib_a = $ReklasKib->sum('plus_aset_tetap_tanah') ?? 0;
                // $getData->plus_reklasifikasi_kib_b = $ReklasKib->sum('min_aset_tetap_peralatan_mesin') ?? 0;
                // $getData->min_reklasifikasi_beban_kib_b = $ReklasKib->sum('plus_aset_tetap_peralatan_mesin') ?? 0;
                // $getData->plus_reklasifikasi_kib_c = $ReklasKib->sum('min_aset_tetap_gedung_bangunan') ?? 0;
                // $getData->min_reklasifikasi_beban_kib_c = $ReklasKib->sum('plus_aset_tetap_gedung_bangunan') ?? 0;
                // $getData->plus_reklasifikasi_kib_d = $ReklasKib->sum('min_aset_tetap_jalan_jaringan_irigasi') ?? 0;
                // $getData->min_reklasifikasi_beban_kib_d = $ReklasKib->sum('plus_aset_tetap_jalan_jaringan_irigasi') ?? 0;
                // $getData->plus_reklasifikasi_kib_e = $ReklasKib->sum('min_aset_tetap_lainnya') ?? 0;
                // $getData->min_reklasifikasi_beban_kib_e = $ReklasKib->sum('plus_aset_tetap_lainnya') ?? 0;
                // $getData->plus_reklasifikasi_kdp = $ReklasKib->sum('min_konstruksi_dalam_pekerjaan') ?? 0;
                // $getData->min_reklasifikasi_beban_kdp = $ReklasKib->sum('plus_konstruksi_dalam_pekerjaan') ?? 0;
                // $getData->plus_reklasifikasi_aset_lain_lain = $ReklasKib->sum('min_aset_lain_lain') ?? 0;
                // $getData->min_reklasifikasi_beban_aset_lain_lain = $ReklasKib->sum('plus_aset_lain_lain') ?? 0;
                $plusReklasKey = 'plus_aset_tetap_lainnya';
                $minReklasKey = 'min_aset_tetap_lainnya';

                $getData->plus_reklasifikasi_kib_a = $ReklasKib->where($plusReklasKey, '>', 0)
                    ->where('min_aset_tetap_tanah', '>', 0)
                    ->sum($plusReklasKey) ?? 0;
                $getData->min_reklasifikasi_beban_kib_a = $ReklasKib->where($minReklasKey, '>', 0)
                    ->where('plus_aset_tetap_tanah', '>', 0)
                    ->sum($minReklasKey) ?? 0;

                $getData->plus_reklasifikasi_kib_b = $ReklasKib->where($plusReklasKey, '>', 0)
                    ->where('min_aset_tetap_tanah', '>', 0)
                    ->sum($plusReklasKey) ?? 0;
                $getData->min_reklasifikasi_beban_kib_b = $ReklasKib->where($minReklasKey, '>', 0)
                    ->where('plus_aset_tetap_peralatan_mesin', '>', 0)
                    ->sum($minReklasKey) ?? 0;

                $getData->plus_reklasifikasi_kib_c = $ReklasKib->where($plusReklasKey, '>', 0)
                    ->where('min_aset_tetap_gedung_bangunan', '>', 0)
                    ->sum($plusReklasKey) ?? 0;
                $getData->min_reklasifikasi_beban_kib_c = $ReklasKib->where($minReklasKey, '>', 0)
                    ->where('plus_aset_tetap_gedung_bangunan', '>', 0)
                    ->sum($minReklasKey) ?? 0;

                $getData->plus_reklasifikasi_kib_d = $ReklasKib->where($plusReklasKey, '>', 0)
                    ->where('min_aset_tetap_jalan_jaringan_irigasi', '>', 0)
                    ->sum($plusReklasKey) ?? 0;
                $getData->min_reklasifikasi_beban_kib_d = $ReklasKib->where($minReklasKey, '>', 0)
                    ->where('plus_aset_tetap_jalan_jaringan_irigasi', '>', 0)
                    ->sum($minReklasKey) ?? 0;

                $getData->plus_reklasifikasi_kib_e = $ReklasKib->where($plusReklasKey, '>', 0)
                    ->where('min_aset_tetap_lainnya', '>', 0)
                    ->sum($plusReklasKey) ?? 0;
                $getData->min_reklasifikasi_beban_kib_e = $ReklasKib->where($minReklasKey, '>', 0)
                    ->where('plus_aset_tetap_lainnya', '>', 0)
                    ->sum($minReklasKey) ?? 0;

                $getData->plus_reklasifikasi_kdp = $ReklasKib->where($plusReklasKey, '>', 0)
                    ->where('min_konstruksi_dalam_pekerjaan', '>', 0)
                    ->sum($plusReklasKey) ?? 0;
                $getData->min_reklasifikasi_beban_kdp = $ReklasKib->where($minReklasKey, '>', 0)
                    ->where('plus_konstruksi_dalam_pekerjaan', '>', 0)
                    ->sum($minReklasKey) ?? 0;

                $getData->plus_reklasifikasi_aset_lain_lain = $ReklasKib->where($plusReklasKey, '>', 0)
                    ->where('min_aset_lain_lain', '>', 0)
                    ->sum($plusReklasKey) ?? 0;
                $getData->min_reklasifikasi_beban_aset_lain_lain = $ReklasKib->where($minReklasKey, '>', 0)
                    ->where('plus_aset_lain_lain', '>', 0)
                    ->sum($minReklasKey) ?? 0;

                // Hibah Masuk
                $HibahMasuk = DB::table('acc_padb_tambahan_hibah_masuk')
                    ->where('instance_id', $instance->id)
                    ->where('year', $request->year)
                    ->where('periode_id', $request->periode)
                    ->sum('aset_tetap_lainnya') ?? 0;
                $getData->plus_hibah_masuk = $HibahMasuk ?? 0;

                // Hibah Keluar
                $HibahKeluar = DB::table('acc_padb_tambahan_hibah_keluar')
                    ->where('instance_id', $instance->id)
                    ->where('year', $request->year)
                    ->where('periode_id', $request->periode)
                    ->sum('aset_tetap_lainnya') ?? 0;
                $getData->min_reklasifikasi_beban_hibah = $HibahKeluar ?? 0;

                // Penilaian
                $PenilaianAset = DB::table('acc_padb_penilaian_aset')
                    ->where('instance_id', $instance->id)
                    ->where('year', $request->year)
                    ->where('periode_id', $request->periode)
                    ->sum('aset_tetap_lainnya') ?? 0;
                $getData->plus_penilaian = $PenilaianAset ?? 0;

                // Mutasi OPD
                $MutasiOPDMasuk = DB::table('acc_padb_tambahan_mutasi_aset')
                    // ->where('instance_id', $instance->id)
                    ->where('to_instance_id', $instance->id)
                    ->where('year', $request->year)
                    ->where('periode_id', $request->periode)
                    ->get();
                $MutasiOPDKeluar = DB::table('acc_padb_tambahan_mutasi_aset')
                    // ->where('instance_id', $instance->id)
                    ->where('from_instance_id', $instance->id)
                    ->where('year', $request->year)
                    ->where('periode_id', $request->periode)
                    ->get();
                $getData->plus_mutasi_antar_opd = $MutasiOPDMasuk->sum('plus_aset_tetap_lainnya') ?? 0;
                $getData->min_mutasi_antar_opd = $MutasiOPDKeluar->sum('plus_aset_tetap_lainnya') ?? 0;

                $minPenghapusan = DB::table('acc_padb_penghapusan_aset')
                    ->where('instance_id', $instance->id)
                    ->where('year', $request->year)
                    ->where('periode_id', $request->periode)
                    ->sum('aset_tetap_lainnya') ?? 0;
                $minPenjualan = DB::table('acc_padb_penjualan_aset')
                    ->where('instance_id', $instance->id)
                    ->where('year', $request->year)
                    ->where('periode_id', $request->periode)
                    ->sum('aset_tetap_lainnya') ?? 0;

                $getData->min_penghapusan = ($minPenghapusan + $minPenjualan) ?? 0;
                $getData->timestamps = false;
                $getData->save();

                $saldoAwal = $getData->saldo_awal ?? 0;

                $getData->plus_total = ($getData->plus_realisasi_belanja ?? 0) + ($getData->plus_hutang_kegiatan ?? 0) + ($getData->plus_atribusi ?? 0) + ($getData->plus_reklasifikasi_barang_habis_pakai ?? 0) + ($getData->plus_reklasifikasi_pemeliharaan ?? 0) + ($getData->plus_reklasifikasi_jasa ?? 0) + ($getData->plus_reklasifikasi_kib_a ?? 0) + ($getData->plus_reklasifikasi_kib_b ?? 0) + ($getData->plus_reklasifikasi_kib_c ?? 0) + ($getData->plus_reklasifikasi_kib_d ?? 0) + ($getData->plus_reklasifikasi_kib_e ?? 0) + ($getData->plus_reklasifikasi_kdp ?? 0) + ($getData->plus_reklasifikasi_aset_lain_lain ?? 0) + ($getData->plus_hibah_masuk ?? 0) + ($getData->plus_penilaian ?? 0) + ($getData->plus_mutasi_antar_opd ?? 0);
                $getData->min_total = ($getData->min_pembayaran_utang ?? 0) + ($getData->min_reklasifikasi_beban_persediaan ?? 0) + ($getData->min_reklasifikasi_beban_jasa ?? 0) + ($getData->min_reklasifikasi_beban_pemeliharaan ?? 0) + ($getData->min_reklasifikasi_beban_hibah ?? 0) + ($getData->min_reklasifikasi_beban_kib_a ?? 0) + ($getData->min_reklasifikasi_beban_kib_b ?? 0) + ($getData->min_reklasifikasi_beban_kib_c ?? 0) + ($getData->min_reklasifikasi_beban_kib_d ?? 0) + ($getData->min_reklasifikasi_beban_kib_e ?? 0) + ($getData->min_reklasifikasi_beban_kdp ?? 0) + ($getData->min_reklasifikasi_beban_aset_lain_lain ?? 0) + ($getData->min_penghapusan ?? 0) + ($getData->min_mutasi_antar_opd ?? 0) + ($getData->min_tptgr ?? 0);

                $calculateSaldoAkhir = $saldoAwal + ($getData->plus_total ?? 0) - ($getData->min_total ?? 0);

                DB::table('acc_rek_as_kib_e')
                    ->where('id', $getData->id)
                    ->update([
                        'saldo_akhir' => $calculateSaldoAkhir,
                    ]);

                $values[] = [
                    'data_id' => $getData->id,
                    'instance_id' => $instance->id,
                    'instance_name' => $instance->name,
                    'saldo_awal' => $saldoAwal ?? 0,
                    'saldo_akhir' => $calculateSaldoAkhir,

                    'plus_realisasi_belanja' => $dataLRA->realisasi ?? $getData->plus_realisasi_belanja ?? 0,
                    'plus_hutang_kegiatan' => $getData->plus_hutang_kegiatan ?? 0,
                    'plus_atribusi' => $getData->plus_atribusi ?? 0,
                    'plus_reklasifikasi_barang_habis_pakai' => $getData->plus_reklasifikasi_barang_habis_pakai ?? 0,
                    'plus_reklasifikasi_pemeliharaan' => $getData->plus_reklasifikasi_pemeliharaan ?? 0,
                    'plus_reklasifikasi_jasa' => $getData->plus_reklasifikasi_jasa ?? 0,
                    'plus_reklasifikasi_kib_a' => $getData->plus_reklasifikasi_kib_a ?? 0,
                    'plus_reklasifikasi_kib_b' => $getData->plus_reklasifikasi_kib_b ?? 0,
                    'plus_reklasifikasi_kib_c' => $getData->plus_reklasifikasi_kib_c ?? 0,
                    'plus_reklasifikasi_kib_d' => $getData->plus_reklasifikasi_kib_d ?? 0,
                    'plus_reklasifikasi_kib_e' => $getData->plus_reklasifikasi_kib_e ?? 0,
                    'plus_reklasifikasi_kdp' => $getData->plus_reklasifikasi_kdp ?? 0,
                    'plus_reklasifikasi_aset_lain_lain' => $getData->plus_reklasifikasi_aset_lain_lain ?? 0,
                    'plus_hibah_masuk' => $getData->plus_hibah_masuk ?? 0,
                    'plus_penilaian' => $getData->plus_penilaian ?? 0,
                    'plus_mutasi_antar_opd' => $getData->plus_mutasi_antar_opd ?? 0,
                    'plus_total' => $getData->plus_total ?? 0,

                    'min_pembayaran_utang' => $getData->min_pembayaran_utang ?? 0,
                    'min_reklasifikasi_beban_persediaan' => $getData->min_reklasifikasi_beban_persediaan ?? 0,
                    'min_reklasifikasi_beban_jasa' => $getData->min_reklasifikasi_beban_jasa ?? 0,
                    'min_reklasifikasi_beban_pemeliharaan' => $getData->min_reklasifikasi_beban_pemeliharaan ?? 0,
                    'min_reklasifikasi_beban_hibah' => $getData->min_reklasifikasi_beban_hibah ?? 0,
                    'min_reklasifikasi_beban_kib_a' => $getData->min_reklasifikasi_beban_kib_a ?? 0,
                    'min_reklasifikasi_beban_kib_b' => $getData->min_reklasifikasi_beban_kib_b ?? 0,
                    'min_reklasifikasi_beban_kib_c' => $getData->min_reklasifikasi_beban_kib_c ?? 0,
                    'min_reklasifikasi_beban_kib_d' => $getData->min_reklasifikasi_beban_kib_d ?? 0,
                    'min_reklasifikasi_beban_kib_e' => $getData->min_reklasifikasi_beban_kib_e ?? 0,
                    'min_reklasifikasi_beban_kdp' => $getData->min_reklasifikasi_beban_kdp ?? 0,
                    'min_reklasifikasi_beban_aset_lain_lain' => $getData->min_reklasifikasi_beban_aset_lain_lain ?? 0,
                    'min_penghapusan' => $getData->min_penghapusan ?? 0,
                    'min_mutasi_antar_opd' => $getData->min_mutasi_antar_opd ?? 0,
                    'min_tptgr' => $getData->min_tptgr ?? 0,
                    'min_total' => $getData->min_total ?? 0,
                ];
            }

            $datas['instances'] = $instances;
            $datas['datas'] = $values;

            return $this->successResponse($datas, 'Penyesuai Aset dan Beban berhasil diambil');
        } catch (\Exception $e) {
            return $this->errorResponse($e->getMessage() . ' ' . $e->getLine());
        }
    }

    function saveKibE(Request $request)
    {
        $validate = Validator::make($request->all(), [
            'instance' => 'nullable|exists:instances,id',
            'year' => 'required|integer',
            'periode' => 'required|exists:ref_periode,id'
        ], [], [
            'instance' => 'Instance ID',
            'periode' => 'Periode'
        ]);
        if ($validate->fails()) {
            return $this->validationResponse($validate->errors());
        }

        $now = now();
        DB::beginTransaction();
        try {
            $datas = $request->data;
            foreach ($datas as $input) {
                DB::table('acc_rek_as_kib_e')->updateOrInsert([
                    'periode_id' => $request->periode,
                    'year' => $request->year,
                    'instance_id' => $input['instance_id'],
                ], [
                    'periode_id' => $request->periode,
                    'year' => $request->year,
                    'instance_id' => $input['instance_id'],
                    'saldo_awal' => $input['saldo_awal'],
                    'saldo_akhir' => $input['saldo_akhir'],
                    'plus_realisasi_belanja' => $input['plus_realisasi_belanja'],
                    'plus_hutang_kegiatan' => $input['plus_hutang_kegiatan'],
                    'plus_atribusi' => $input['plus_atribusi'],
                    'plus_reklasifikasi_barang_habis_pakai' => $input['plus_reklasifikasi_barang_habis_pakai'],
                    'plus_reklasifikasi_pemeliharaan' => $input['plus_reklasifikasi_pemeliharaan'],
                    'plus_reklasifikasi_jasa' => $input['plus_reklasifikasi_jasa'],
                    'plus_reklasifikasi_kib_a' => $input['plus_reklasifikasi_kib_a'],
                    'plus_reklasifikasi_kib_b' => $input['plus_reklasifikasi_kib_b'],
                    'plus_reklasifikasi_kib_c' => $input['plus_reklasifikasi_kib_c'],
                    'plus_reklasifikasi_kib_d' => $input['plus_reklasifikasi_kib_d'],
                    'plus_reklasifikasi_kib_e' => $input['plus_reklasifikasi_kib_e'],
                    'plus_reklasifikasi_kdp' => $input['plus_reklasifikasi_kdp'],
                    'plus_reklasifikasi_aset_lain_lain' => $input['plus_reklasifikasi_aset_lain_lain'],
                    'plus_hibah_masuk' => $input['plus_hibah_masuk'],
                    'plus_penilaian' => $input['plus_penilaian'],
                    'plus_mutasi_antar_opd' => $input['plus_mutasi_antar_opd'],
                    'min_pembayaran_utang' => $input['min_pembayaran_utang'],
                    'min_reklasifikasi_beban_persediaan' => $input['min_reklasifikasi_beban_persediaan'],
                    'min_reklasifikasi_beban_jasa' => $input['min_reklasifikasi_beban_jasa'],
                    'min_reklasifikasi_beban_pemeliharaan' => $input['min_reklasifikasi_beban_pemeliharaan'],
                    'min_reklasifikasi_beban_hibah' => $input['min_reklasifikasi_beban_hibah'],
                    'min_reklasifikasi_beban_kib_a' => $input['min_reklasifikasi_beban_kib_a'],
                    'min_reklasifikasi_beban_kib_b' => $input['min_reklasifikasi_beban_kib_b'],
                    'min_reklasifikasi_beban_kib_c' => $input['min_reklasifikasi_beban_kib_c'],
                    'min_reklasifikasi_beban_kib_d' => $input['min_reklasifikasi_beban_kib_d'],
                    'min_reklasifikasi_beban_kib_e' => $input['min_reklasifikasi_beban_kib_e'],
                    'min_reklasifikasi_beban_kdp' => $input['min_reklasifikasi_beban_kdp'],
                    'min_reklasifikasi_beban_aset_lain_lain' => $input['min_reklasifikasi_beban_aset_lain_lain'],
                    'min_penghapusan' => $input['min_penghapusan'],
                    'min_mutasi_antar_opd' => $input['min_mutasi_antar_opd'],
                    'min_tptgr' => $input['min_tptgr'],
                    'updated_by' => auth()->user()->id,
                    'updated_at' => $now,
                ]);
            }

            DB::commit();
            return $this->successResponse(null, 'Data berhasil disimpan');
        } catch (\Exception $e) {
            DB::rollBack();
            return $this->errorResponse($e->getMessage() . ' ' . $e->getLine());
        }
    }
    // KIB E End

    // Aset Lain-lain Start
    function getAsetLainLain(Request $request)
    {
        $validate = Validator::make($request->all(), [
            'instance' => 'nullable|exists:instances,id',
            'year' => 'required|integer',
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
            $instances = [];
            if (!$request->instance) {
                $instanceIds = DB::table('acc_lra')
                    ->select('instance_id')
                    ->groupBy('instance_id')
                    ->get();
                // $instances = Instance::whereIn('id', $instanceIds->pluck('instance_id'))->get();
                $instances = Instance::orderBy('name')->get();
            } elseif ($request->instance) {
                $instances = Instance::where('id', $request->instance)->get();
            }

            $values = [];
            foreach ($instances as $instance) {
                $dataLRA = DB::table('acc_lra')
                    ->where('instance_id', $instance->id)
                    ->where('year', $request->year)
                    ->where('periode_id', $request->periode)
                    ->where('kode_rekening', '5.2.06')
                    ->first();

                $getData = DB::table('acc_rek_as_aset_lain_lain')
                    ->where('instance_id', $instance->id)
                    ->where('year', $request->year)
                    ->where('periode_id', $request->periode)
                    ->first();
                if (!$getData) {
                    DB::table('acc_rek_as_aset_lain_lain')->insert([
                        'periode_id' => $request->periode,
                        'year' => $request->year,
                        'instance_id' => $instance->id,
                        // 'plus_realisasi_belanja' => $dataLRA->realisasi ?? 0,
                    ]);

                    $getData = DB::table('acc_rek_as_aset_lain_lain')
                        ->where('instance_id', $instance->id)
                        ->where('year', $request->year)
                        ->where('periode_id', $request->periode)
                        ->first();
                }

                $saldoAwal = $getData->saldo_awal ?? 0;

                $getData->plus_total = ($getData->plus_realisasi_belanja ?? 0) + ($getData->plus_hutang_kegiatan ?? 0) + ($getData->plus_atribusi ?? 0) + ($getData->plus_reklasifikasi_barang_habis_pakai ?? 0) + ($getData->plus_reklasifikasi_pemeliharaan ?? 0) + ($getData->plus_reklasifikasi_jasa ?? 0) + ($getData->plus_reklasifikasi_kib_a ?? 0) + ($getData->plus_reklasifikasi_kib_b ?? 0) + ($getData->plus_reklasifikasi_kib_c ?? 0) + ($getData->plus_reklasifikasi_kib_d ?? 0) + ($getData->plus_reklasifikasi_kib_e ?? 0) + ($getData->plus_reklasifikasi_kdp ?? 0) + ($getData->plus_reklasifikasi_aset_lain_lain ?? 0) + ($getData->plus_hibah_masuk ?? 0) + ($getData->plus_penilaian ?? 0) + ($getData->plus_mutasi_antar_opd ?? 0);
                $getData->min_total = ($getData->min_pembayaran_utang ?? 0) + ($getData->min_reklasifikasi_beban_persediaan ?? 0) + ($getData->min_reklasifikasi_beban_jasa ?? 0) + ($getData->min_reklasifikasi_beban_pemeliharaan ?? 0) + ($getData->min_reklasifikasi_beban_hibah ?? 0) + ($getData->min_reklasifikasi_beban_kib_a ?? 0) + ($getData->min_reklasifikasi_beban_kib_b ?? 0) + ($getData->min_reklasifikasi_beban_kib_c ?? 0) + ($getData->min_reklasifikasi_beban_kib_d ?? 0) + ($getData->min_reklasifikasi_beban_kib_e ?? 0) + ($getData->min_reklasifikasi_beban_kdp ?? 0) + ($getData->min_reklasifikasi_beban_aset_lain_lain ?? 0) + ($getData->min_penghapusan ?? 0) + ($getData->min_mutasi_antar_opd ?? 0) + ($getData->min_tptgr ?? 0);

                $calculateSaldoAkhir = $saldoAwal + ($getData->plus_total ?? 0) - ($getData->min_total ?? 0);


                DB::table('acc_rek_as_aset_lain_lain')
                    ->where('id', $getData->id)
                    ->update([
                        'saldo_akhir' => $calculateSaldoAkhir,
                    ]);

                $values[] = [
                    'data_id' => $getData->id,
                    'instance_id' => $instance->id,
                    'instance_name' => $instance->name,
                    'saldo_awal' => $saldoAwal ?? 0,
                    'saldo_akhir' => $calculateSaldoAkhir,

                    // 'plus_realisasi_belanja' => $dataLRA->realisasi ?? $getData->plus_realisasi_belanja ?? 0,
                    'plus_realisasi_belanja' => $getData->plus_realisasi_belanja ?? 0,
                    'plus_hutang_kegiatan' => $getData->plus_hutang_kegiatan ?? 0,
                    'plus_atribusi' => $getData->plus_atribusi ?? 0,
                    'plus_reklasifikasi_barang_habis_pakai' => $getData->plus_reklasifikasi_barang_habis_pakai ?? 0,
                    'plus_reklasifikasi_pemeliharaan' => $getData->plus_reklasifikasi_pemeliharaan ?? 0,
                    'plus_reklasifikasi_jasa' => $getData->plus_reklasifikasi_jasa ?? 0,
                    'plus_reklasifikasi_kib_a' => $getData->plus_reklasifikasi_kib_a ?? 0,
                    'plus_reklasifikasi_kib_b' => $getData->plus_reklasifikasi_kib_b ?? 0,
                    'plus_reklasifikasi_kib_c' => $getData->plus_reklasifikasi_kib_c ?? 0,
                    'plus_reklasifikasi_kib_d' => $getData->plus_reklasifikasi_kib_d ?? 0,
                    'plus_reklasifikasi_kib_e' => $getData->plus_reklasifikasi_kib_e ?? 0,
                    'plus_reklasifikasi_kdp' => $getData->plus_reklasifikasi_kdp ?? 0,
                    'plus_reklasifikasi_aset_lain_lain' => $getData->plus_reklasifikasi_aset_lain_lain ?? 0,
                    'plus_hibah_masuk' => $getData->plus_hibah_masuk ?? 0,
                    'plus_penilaian' => $getData->plus_penilaian ?? 0,
                    'plus_mutasi_antar_opd' => $getData->plus_mutasi_antar_opd ?? 0,
                    'plus_total' => $getData->plus_total ?? 0,

                    'min_pembayaran_utang' => $getData->min_pembayaran_utang ?? 0,
                    'min_reklasifikasi_beban_persediaan' => $getData->min_reklasifikasi_beban_persediaan ?? 0,
                    'min_reklasifikasi_beban_jasa' => $getData->min_reklasifikasi_beban_jasa ?? 0,
                    'min_reklasifikasi_beban_pemeliharaan' => $getData->min_reklasifikasi_beban_pemeliharaan ?? 0,
                    'min_reklasifikasi_beban_hibah' => $getData->min_reklasifikasi_beban_hibah ?? 0,
                    'min_reklasifikasi_beban_kib_a' => $getData->min_reklasifikasi_beban_kib_a ?? 0,
                    'min_reklasifikasi_beban_kib_b' => $getData->min_reklasifikasi_beban_kib_b ?? 0,
                    'min_reklasifikasi_beban_kib_c' => $getData->min_reklasifikasi_beban_kib_c ?? 0,
                    'min_reklasifikasi_beban_kib_d' => $getData->min_reklasifikasi_beban_kib_d ?? 0,
                    'min_reklasifikasi_beban_kib_e' => $getData->min_reklasifikasi_beban_kib_e ?? 0,
                    'min_reklasifikasi_beban_kdp' => $getData->min_reklasifikasi_beban_kdp ?? 0,
                    'min_reklasifikasi_beban_aset_lain_lain' => $getData->min_reklasifikasi_beban_aset_lain_lain ?? 0,
                    'min_penghapusan' => $getData->min_penghapusan ?? 0,
                    'min_mutasi_antar_opd' => $getData->min_mutasi_antar_opd ?? 0,
                    'min_tptgr' => $getData->min_tptgr ?? 0,
                    'min_total' => $getData->min_total ?? 0,
                ];
            }

            $datas['instances'] = $instances;
            $datas['datas'] = $values;

            return $this->successResponse($datas, 'Penyesuai Aset dan Beban berhasil diambil');
        } catch (\Exception $e) {
            return $this->errorResponse($e->getMessage() . ' ' . $e->getLine());
        }
    }

    function saveAsetLainLain(Request $request)
    {
        $validate = Validator::make($request->all(), [
            'instance' => 'nullable|exists:instances,id',
            'year' => 'required|integer',
            'periode' => 'required|exists:ref_periode,id'
        ], [], [
            'instance' => 'Instance ID',
            'periode' => 'Periode'
        ]);
        if ($validate->fails()) {
            return $this->validationResponse($validate->errors());
        }

        $now = now();
        DB::beginTransaction();
        try {
            $datas = $request->data;
            foreach ($datas as $input) {
                DB::table('acc_rek_as_aset_lain_lain')->updateOrInsert([
                    'periode_id' => $request->periode,
                    'year' => $request->year,
                    'instance_id' => $input['instance_id'],
                ], [
                    'periode_id' => $request->periode,
                    'year' => $request->year,
                    'instance_id' => $input['instance_id'],
                    'saldo_awal' => $input['saldo_awal'],
                    'saldo_akhir' => $input['saldo_akhir'],
                    'plus_realisasi_belanja' => $input['plus_realisasi_belanja'],
                    'plus_hutang_kegiatan' => $input['plus_hutang_kegiatan'],
                    'plus_atribusi' => $input['plus_atribusi'],
                    'plus_reklasifikasi_barang_habis_pakai' => $input['plus_reklasifikasi_barang_habis_pakai'],
                    'plus_reklasifikasi_pemeliharaan' => $input['plus_reklasifikasi_pemeliharaan'],
                    'plus_reklasifikasi_jasa' => $input['plus_reklasifikasi_jasa'],
                    'plus_reklasifikasi_kib_a' => $input['plus_reklasifikasi_kib_a'],
                    'plus_reklasifikasi_kib_b' => $input['plus_reklasifikasi_kib_b'],
                    'plus_reklasifikasi_kib_c' => $input['plus_reklasifikasi_kib_c'],
                    'plus_reklasifikasi_kib_d' => $input['plus_reklasifikasi_kib_d'],
                    'plus_reklasifikasi_kib_e' => $input['plus_reklasifikasi_kib_e'],
                    'plus_reklasifikasi_kdp' => $input['plus_reklasifikasi_kdp'],
                    'plus_reklasifikasi_aset_lain_lain' => $input['plus_reklasifikasi_aset_lain_lain'],
                    'plus_hibah_masuk' => $input['plus_hibah_masuk'],
                    'plus_penilaian' => $input['plus_penilaian'],
                    'plus_mutasi_antar_opd' => $input['plus_mutasi_antar_opd'],
                    'min_pembayaran_utang' => $input['min_pembayaran_utang'],
                    'min_reklasifikasi_beban_persediaan' => $input['min_reklasifikasi_beban_persediaan'],
                    'min_reklasifikasi_beban_jasa' => $input['min_reklasifikasi_beban_jasa'],
                    'min_reklasifikasi_beban_pemeliharaan' => $input['min_reklasifikasi_beban_pemeliharaan'],
                    'min_reklasifikasi_beban_hibah' => $input['min_reklasifikasi_beban_hibah'],
                    'min_reklasifikasi_beban_kib_a' => $input['min_reklasifikasi_beban_kib_a'],
                    'min_reklasifikasi_beban_kib_b' => $input['min_reklasifikasi_beban_kib_b'],
                    'min_reklasifikasi_beban_kib_c' => $input['min_reklasifikasi_beban_kib_c'],
                    'min_reklasifikasi_beban_kib_d' => $input['min_reklasifikasi_beban_kib_d'],
                    'min_reklasifikasi_beban_kib_e' => $input['min_reklasifikasi_beban_kib_e'],
                    'min_reklasifikasi_beban_kdp' => $input['min_reklasifikasi_beban_kdp'],
                    'min_reklasifikasi_beban_aset_lain_lain' => $input['min_reklasifikasi_beban_aset_lain_lain'],
                    'min_penghapusan' => $input['min_penghapusan'],
                    'min_mutasi_antar_opd' => $input['min_mutasi_antar_opd'],
                    'min_tptgr' => $input['min_tptgr'],
                    'updated_by' => auth()->user()->id,
                    'updated_at' => $now,
                ]);
            }

            DB::commit();
            return $this->successResponse(null, 'Data berhasil disimpan');
        } catch (\Exception $e) {
            DB::rollBack();
            return $this->errorResponse($e->getMessage() . ' ' . $e->getLine());
        }
    }
    // Aset Lain-lain End

    // KDP Start
    function getKDP(Request $request)
    {
        $validate = Validator::make($request->all(), [
            'instance' => 'nullable|exists:instances,id',
            'year' => 'required|integer',
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
            $instances = [];
            if (!$request->instance) {
                $instanceIds = DB::table('acc_lra')
                    ->select('instance_id')
                    ->groupBy('instance_id')
                    ->get();
                // $instances = Instance::whereIn('id', $instanceIds->pluck('instance_id'))->get();
                $instances = Instance::orderBy('name')->get();
            } elseif ($request->instance) {
                $instances = Instance::where('id', $request->instance)->get();
            }

            $values = [];
            foreach ($instances as $instance) {
                $rekapBelanja = DB::table('acc_rek_as_rekap_belanja')
                    ->where('instance_id', $instance->id)
                    ->where('year', $request->year)
                    ->where('periode_id', $request->periode)
                    ->first();

                $getData = KDP::where('instance_id', $instance->id)
                    ->where('year', $request->year)
                    ->where('periode_id', $request->periode)
                    ->first();
                if (!$getData) {
                    DB::table('acc_rek_as_kdp')->insert([
                        'periode_id' => $request->periode,
                        'year' => $request->year,
                        'instance_id' => $instance->id,
                        'plus_realisasi_belanja' => $rekapBelanja->kdp ?? 0,
                    ]);

                    $getData = KDP::where('instance_id', $instance->id)
                        ->where('year', $request->year)
                        ->where('periode_id', $request->periode)
                        ->first();
                }
                $getData->plus_realisasi_belanja = $rekapBelanja->kdp ?? 0;

                // Hutang Belanja
                $HutangBaru = DB::table('acc_htb_hutang_baru')
                    ->where('instance_id', $instance->id)
                    ->where('year', $request->year)
                    ->where('periode_id', $request->periode)
                    ->sum('konstruksi_dalam_pekerjaan') ?? 0;
                $getData->plus_hutang_kegiatan = $HutangBaru ?? 0;
                $PembayaranHutang = DB::table('acc_htb_pembayaran_hutang')
                    ->where('instance_id', $instance->id)
                    ->where('year', $request->year)
                    ->where('periode_id', $request->periode)
                    ->sum('konstruksi_dalam_pekerjaan') ?? 0;
                $getData->min_pembayaran_utang = $PembayaranHutang ?? 0;

                // Atribusi
                $Atribusi = DB::table('acc_padb_atribusi')
                    ->where('instance_id', $instance->id)
                    ->where('year', $request->year)
                    ->where('periode_id', $request->periode)
                    ->sum('atri_konstruksi_dalam_pekerjaan') ?? 0;
                $getData->plus_atribusi = $Atribusi ?? 0;

                // Reklasifikasi KIB
                $ReklasKib = DB::table('acc_padb_penyesuaian_aset')
                    ->where('instance_id', $instance->id)
                    ->where('year', $request->year)
                    ->where('periode_id', $request->periode)
                    ->get();
                // $getData->plus_reklasifikasi_kib_a = $ReklasKib->sum('min_aset_tetap_tanah') ?? 0;
                // $getData->min_reklasifikasi_beban_kib_a = $ReklasKib->sum('plus_aset_tetap_tanah') ?? 0;
                // $getData->plus_reklasifikasi_kib_b = $ReklasKib->sum('min_aset_tetap_peralatan_mesin') ?? 0;
                // $getData->min_reklasifikasi_beban_kib_b = $ReklasKib->sum('plus_aset_tetap_peralatan_mesin') ?? 0;
                // $getData->plus_reklasifikasi_kib_c = $ReklasKib->sum('min_aset_tetap_gedung_bangunan') ?? 0;
                // $getData->min_reklasifikasi_beban_kib_c = $ReklasKib->sum('plus_aset_tetap_gedung_bangunan') ?? 0;
                // $getData->plus_reklasifikasi_kib_d = $ReklasKib->sum('min_aset_tetap_jalan_jaringan_irigasi') ?? 0;
                // $getData->min_reklasifikasi_beban_kib_d = $ReklasKib->sum('plus_aset_tetap_jalan_jaringan_irigasi') ?? 0;
                // $getData->plus_reklasifikasi_kib_e = $ReklasKib->sum('min_aset_tetap_lainnya') ?? 0;
                // $getData->min_reklasifikasi_beban_kib_e = $ReklasKib->sum('plus_aset_tetap_lainnya') ?? 0;
                // $getData->plus_reklasifikasi_kdp = $ReklasKib->sum('min_konstruksi_dalam_pekerjaan') ?? 0;
                // $getData->min_reklasifikasi_beban_kdp = $ReklasKib->sum('plus_konstruksi_dalam_pekerjaan') ?? 0;
                // $getData->plus_reklasifikasi_aset_lain_lain = $ReklasKib->sum('min_aset_lain_lain') ?? 0;
                // $getData->min_reklasifikasi_beban_aset_lain_lain = $ReklasKib->sum('plus_aset_lain_lain') ?? 0;

                $plusReklasKey = 'plus_konstruksi_dalam_pekerjaan';
                $minReklasKey = 'min_konstruksi_dalam_pekerjaan';

                $getData->plus_reklasifikasi_kib_a = $ReklasKib->where($plusReklasKey, '>', 0)
                    ->where('min_aset_tetap_tanah', '>', 0)
                    ->sum($plusReklasKey) ?? 0;
                $getData->min_reklasifikasi_beban_kib_a = $ReklasKib->where($minReklasKey, '>', 0)
                    ->where('plus_aset_tetap_tanah', '>', 0)
                    ->sum($minReklasKey) ?? 0;

                $getData->plus_reklasifikasi_kib_b = $ReklasKib->where($plusReklasKey, '>', 0)
                    ->where('min_aset_tetap_tanah', '>', 0)
                    ->sum($plusReklasKey) ?? 0;
                $getData->min_reklasifikasi_beban_kib_b = $ReklasKib->where($minReklasKey, '>', 0)
                    ->where('plus_aset_tetap_peralatan_mesin', '>', 0)
                    ->sum($minReklasKey) ?? 0;

                $getData->plus_reklasifikasi_kib_c = $ReklasKib->where($plusReklasKey, '>', 0)
                    ->where('min_aset_tetap_gedung_bangunan', '>', 0)
                    ->sum($plusReklasKey) ?? 0;
                $getData->min_reklasifikasi_beban_kib_c = $ReklasKib->where($minReklasKey, '>', 0)
                    ->where('plus_aset_tetap_gedung_bangunan', '>', 0)
                    ->sum($minReklasKey) ?? 0;

                $getData->plus_reklasifikasi_kib_d = $ReklasKib->where($plusReklasKey, '>', 0)
                    ->where('min_aset_tetap_jalan_jaringan_irigasi', '>', 0)
                    ->sum($plusReklasKey) ?? 0;
                $getData->min_reklasifikasi_beban_kib_d = $ReklasKib->where($minReklasKey, '>', 0)
                    ->where('plus_aset_tetap_jalan_jaringan_irigasi', '>', 0)
                    ->sum($minReklasKey) ?? 0;

                $getData->plus_reklasifikasi_kib_e = $ReklasKib->where($plusReklasKey, '>', 0)
                    ->where('min_aset_tetap_lainnya', '>', 0)
                    ->sum($plusReklasKey) ?? 0;
                $getData->min_reklasifikasi_beban_kib_e = $ReklasKib->where($minReklasKey, '>', 0)
                    ->where('plus_aset_tetap_lainnya', '>', 0)
                    ->sum($minReklasKey) ?? 0;

                $getData->plus_reklasifikasi_kdp = $ReklasKib->where($plusReklasKey, '>', 0)
                    ->where('min_konstruksi_dalam_pekerjaan', '>', 0)
                    ->sum($plusReklasKey) ?? 0;
                $getData->min_reklasifikasi_beban_kdp = $ReklasKib->where($minReklasKey, '>', 0)
                    ->where('plus_konstruksi_dalam_pekerjaan', '>', 0)
                    ->sum($minReklasKey) ?? 0;

                $getData->plus_reklasifikasi_aset_lain_lain = $ReklasKib->where($plusReklasKey, '>', 0)
                    ->where('min_aset_lain_lain', '>', 0)
                    ->sum($plusReklasKey) ?? 0;
                $getData->min_reklasifikasi_beban_aset_lain_lain = $ReklasKib->where($minReklasKey, '>', 0)
                    ->where('plus_aset_lain_lain', '>', 0)
                    ->sum($minReklasKey) ?? 0;

                // Hibah Masuk
                $HibahMasuk = DB::table('acc_padb_tambahan_hibah_masuk')
                    ->where('instance_id', $instance->id)
                    ->where('year', $request->year)
                    ->where('periode_id', $request->periode)
                    ->sum('konstruksi_dalam_pekerjaan') ?? 0;
                $getData->plus_hibah_masuk = $HibahMasuk ?? 0;

                // Hibah Keluar
                $HibahKeluar = DB::table('acc_padb_tambahan_hibah_keluar')
                    ->where('instance_id', $instance->id)
                    ->where('year', $request->year)
                    ->where('periode_id', $request->periode)
                    ->sum('konstruksi_dalam_pekerjaan') ?? 0;
                $getData->min_reklasifikasi_beban_hibah = $HibahKeluar ?? 0;

                // Penilaian
                $PenilaianAset = DB::table('acc_padb_penilaian_aset')
                    ->where('instance_id', $instance->id)
                    ->where('year', $request->year)
                    ->where('periode_id', $request->periode)
                    ->sum('konstruksi_dalam_pekerjaan') ?? 0;
                $getData->plus_penilaian = $PenilaianAset ?? 0;

                // Mutasi OPD
                $MutasiOPDMasuk = DB::table('acc_padb_tambahan_mutasi_aset')
                    // ->where('instance_id', $instance->id)
                    ->where('to_instance_id', $instance->id)
                    ->where('year', $request->year)
                    ->where('periode_id', $request->periode)
                    ->get();
                $MutasiOPDKeluar = DB::table('acc_padb_tambahan_mutasi_aset')
                    // ->where('instance_id', $instance->id)
                    ->where('from_instance_id', $instance->id)
                    ->where('year', $request->year)
                    ->where('periode_id', $request->periode)
                    ->get();
                $getData->plus_mutasi_antar_opd = $MutasiOPDMasuk->sum('plus_kdp') ?? 0;
                $getData->min_mutasi_antar_opd = $MutasiOPDKeluar->sum('plus_kdp') ?? 0;

                $minPenghapusan = DB::table('acc_padb_penghapusan_aset')
                    ->where('instance_id', $instance->id)
                    ->where('year', $request->year)
                    ->where('periode_id', $request->periode)
                    ->sum('konstruksi_dalam_pekerjaan') ?? 0;
                $minPenjualan = DB::table('acc_padb_penjualan_aset')
                    ->where('instance_id', $instance->id)
                    ->where('year', $request->year)
                    ->where('periode_id', $request->periode)
                    ->sum('konstruksi_dalam_pekerjaan') ?? 0;

                $getData->min_penghapusan = ($minPenghapusan + $minPenjualan) ?? 0;
                $getData->timestamps = false;
                $getData->save();

                $saldoAwal = $getData->saldo_awal ?? 0;

                $getData->plus_total = ($getData->plus_realisasi_belanja ?? 0) + ($getData->plus_hutang_kegiatan ?? 0) + ($getData->plus_atribusi ?? 0) + ($getData->plus_reklasifikasi_barang_habis_pakai ?? 0) + ($getData->plus_reklasifikasi_pemeliharaan ?? 0) + ($getData->plus_reklasifikasi_jasa ?? 0) + ($getData->plus_reklasifikasi_kib_a ?? 0) + ($getData->plus_reklasifikasi_kib_b ?? 0) + ($getData->plus_reklasifikasi_kib_c ?? 0) + ($getData->plus_reklasifikasi_kib_d ?? 0) + ($getData->plus_reklasifikasi_kib_e ?? 0) + ($getData->plus_reklasifikasi_kdp ?? 0) + ($getData->plus_reklasifikasi_aset_lain_lain ?? 0) + ($getData->plus_hibah_masuk ?? 0) + ($getData->plus_penilaian ?? 0) + ($getData->plus_mutasi_antar_opd ?? 0);

                $getData->min_total = ($getData->min_pembayaran_utang ?? 0) + ($getData->min_reklasifikasi_beban_persediaan ?? 0) + ($getData->min_reklasifikasi_beban_jasa ?? 0) + ($getData->min_reklasifikasi_beban_pemeliharaan ?? 0) + ($getData->min_reklasifikasi_beban_hibah ?? 0) + ($getData->min_reklasifikasi_beban_kib_a ?? 0) + ($getData->min_reklasifikasi_beban_kib_b ?? 0) + ($getData->min_reklasifikasi_beban_kib_c ?? 0) + ($getData->min_reklasifikasi_beban_kib_d ?? 0) + ($getData->min_reklasifikasi_beban_kib_e ?? 0) + ($getData->min_reklasifikasi_beban_kdp ?? 0) + ($getData->min_reklasifikasi_beban_aset_lain_lain ?? 0) + ($getData->min_penghapusan ?? 0) + ($getData->min_mutasi_antar_opd ?? 0) + ($getData->min_tptgr ?? 0);

                $calculateSaldoAkhir = $saldoAwal + ($getData->plus_total ?? 0) - ($getData->min_total ?? 0);

                DB::table('acc_rek_as_kdp')
                    ->where('id', $getData->id)
                    ->update([
                        'saldo_akhir' => $calculateSaldoAkhir,
                    ]);

                $values[] = [
                    'data_id' => $getData->id,
                    'instance_id' => $instance->id,
                    'instance_name' => $instance->name,
                    'saldo_awal' => $saldoAwal ?? 0,
                    'saldo_akhir' => $calculateSaldoAkhir,

                    'plus_realisasi_belanja' => $rekapBelanja->kdp ?? $getData->plus_realisasi_belanja ?? 0,
                    'plus_hutang_kegiatan' => $getData->plus_hutang_kegiatan ?? 0,
                    'plus_atribusi' => $getData->plus_atribusi ?? 0,
                    'plus_reklasifikasi_barang_habis_pakai' => $getData->plus_reklasifikasi_barang_habis_pakai ?? 0,
                    'plus_reklasifikasi_pemeliharaan' => $getData->plus_reklasifikasi_pemeliharaan ?? 0,
                    'plus_reklasifikasi_jasa' => $getData->plus_reklasifikasi_jasa ?? 0,
                    'plus_reklasifikasi_kib_a' => $getData->plus_reklasifikasi_kib_a ?? 0,
                    'plus_reklasifikasi_kib_b' => $getData->plus_reklasifikasi_kib_b ?? 0,
                    'plus_reklasifikasi_kib_c' => $getData->plus_reklasifikasi_kib_c ?? 0,
                    'plus_reklasifikasi_kib_d' => $getData->plus_reklasifikasi_kib_d ?? 0,
                    'plus_reklasifikasi_kib_e' => $getData->plus_reklasifikasi_kib_e ?? 0,
                    'plus_reklasifikasi_kdp' => $getData->plus_reklasifikasi_kdp ?? 0,
                    'plus_reklasifikasi_aset_lain_lain' => $getData->plus_reklasifikasi_aset_lain_lain ?? 0,
                    'plus_hibah_masuk' => $getData->plus_hibah_masuk ?? 0,
                    'plus_penilaian' => $getData->plus_penilaian ?? 0,
                    'plus_mutasi_antar_opd' => $getData->plus_mutasi_antar_opd ?? 0,
                    'plus_total' => $getData->plus_total ?? 0,

                    'min_pembayaran_utang' => $getData->min_pembayaran_utang ?? 0,
                    'min_reklasifikasi_beban_persediaan' => $getData->min_reklasifikasi_beban_persediaan ?? 0,
                    'min_reklasifikasi_beban_jasa' => $getData->min_reklasifikasi_beban_jasa ?? 0,
                    'min_reklasifikasi_beban_pemeliharaan' => $getData->min_reklasifikasi_beban_pemeliharaan ?? 0,
                    'min_reklasifikasi_beban_hibah' => $getData->min_reklasifikasi_beban_hibah ?? 0,
                    'min_reklasifikasi_beban_kib_a' => $getData->min_reklasifikasi_beban_kib_a ?? 0,
                    'min_reklasifikasi_beban_kib_b' => $getData->min_reklasifikasi_beban_kib_b ?? 0,
                    'min_reklasifikasi_beban_kib_c' => $getData->min_reklasifikasi_beban_kib_c ?? 0,
                    'min_reklasifikasi_beban_kib_d' => $getData->min_reklasifikasi_beban_kib_d ?? 0,
                    'min_reklasifikasi_beban_kib_e' => $getData->min_reklasifikasi_beban_kib_e ?? 0,
                    'min_reklasifikasi_beban_kdp' => $getData->min_reklasifikasi_beban_kdp ?? 0,
                    'min_reklasifikasi_beban_aset_lain_lain' => $getData->min_reklasifikasi_beban_aset_lain_lain ?? 0,
                    'min_penghapusan' => $getData->min_penghapusan ?? 0,
                    'min_mutasi_antar_opd' => $getData->min_mutasi_antar_opd ?? 0,
                    'min_tptgr' => $getData->min_tptgr ?? 0,
                    'min_total' => $getData->min_total ?? 0,
                ];
            }

            $datas['instances'] = $instances;
            $datas['datas'] = $values;

            return $this->successResponse($datas, 'Penyesuai Aset dan Beban berhasil diambil');
        } catch (\Exception $e) {
            return $this->errorResponse($e->getMessage() . ' ' . $e->getLine());
        }
    }

    function saveKDP(Request $request)
    {
        $validate = Validator::make($request->all(), [
            'instance' => 'nullable|exists:instances,id',
            'year' => 'required|integer',
            'periode' => 'required|exists:ref_periode,id'
        ], [], [
            'instance' => 'Instance ID',
            'periode' => 'Periode'
        ]);
        if ($validate->fails()) {
            return $this->validationResponse($validate->errors());
        }

        $now = now();
        DB::beginTransaction();
        try {
            $datas = $request->data;
            foreach ($datas as $input) {
                DB::table('acc_rek_as_kdp')->updateOrInsert([
                    'periode_id' => $request->periode,
                    'year' => $request->year,
                    'instance_id' => $input['instance_id'],
                ], [
                    'periode_id' => $request->periode,
                    'year' => $request->year,
                    'instance_id' => $input['instance_id'],
                    'saldo_awal' => $input['saldo_awal'],
                    'saldo_akhir' => $input['saldo_akhir'],
                    'plus_realisasi_belanja' => $input['plus_realisasi_belanja'],
                    'plus_hutang_kegiatan' => $input['plus_hutang_kegiatan'],
                    'plus_atribusi' => $input['plus_atribusi'],
                    'plus_reklasifikasi_barang_habis_pakai' => $input['plus_reklasifikasi_barang_habis_pakai'],
                    'plus_reklasifikasi_pemeliharaan' => $input['plus_reklasifikasi_pemeliharaan'],
                    'plus_reklasifikasi_jasa' => $input['plus_reklasifikasi_jasa'],
                    'plus_reklasifikasi_kib_a' => $input['plus_reklasifikasi_kib_a'],
                    'plus_reklasifikasi_kib_b' => $input['plus_reklasifikasi_kib_b'],
                    'plus_reklasifikasi_kib_c' => $input['plus_reklasifikasi_kib_c'],
                    'plus_reklasifikasi_kib_d' => $input['plus_reklasifikasi_kib_d'],
                    'plus_reklasifikasi_kib_e' => $input['plus_reklasifikasi_kib_e'],
                    'plus_reklasifikasi_kdp' => $input['plus_reklasifikasi_kdp'],
                    'plus_reklasifikasi_aset_lain_lain' => $input['plus_reklasifikasi_aset_lain_lain'],
                    'plus_hibah_masuk' => $input['plus_hibah_masuk'],
                    'plus_penilaian' => $input['plus_penilaian'],
                    'plus_mutasi_antar_opd' => $input['plus_mutasi_antar_opd'],
                    'min_pembayaran_utang' => $input['min_pembayaran_utang'],
                    'min_reklasifikasi_beban_persediaan' => $input['min_reklasifikasi_beban_persediaan'],
                    'min_reklasifikasi_beban_jasa' => $input['min_reklasifikasi_beban_jasa'],
                    'min_reklasifikasi_beban_pemeliharaan' => $input['min_reklasifikasi_beban_pemeliharaan'],
                    'min_reklasifikasi_beban_hibah' => $input['min_reklasifikasi_beban_hibah'],
                    'min_reklasifikasi_beban_kib_a' => $input['min_reklasifikasi_beban_kib_a'],
                    'min_reklasifikasi_beban_kib_b' => $input['min_reklasifikasi_beban_kib_b'],
                    'min_reklasifikasi_beban_kib_c' => $input['min_reklasifikasi_beban_kib_c'],
                    'min_reklasifikasi_beban_kib_d' => $input['min_reklasifikasi_beban_kib_d'],
                    'min_reklasifikasi_beban_kib_e' => $input['min_reklasifikasi_beban_kib_e'],
                    'min_reklasifikasi_beban_kdp' => $input['min_reklasifikasi_beban_kdp'],
                    'min_reklasifikasi_beban_aset_lain_lain' => $input['min_reklasifikasi_beban_aset_lain_lain'],
                    'min_penghapusan' => $input['min_penghapusan'],
                    'min_mutasi_antar_opd' => $input['min_mutasi_antar_opd'],
                    'min_tptgr' => $input['min_tptgr'],
                    'updated_by' => auth()->user()->id,
                    'updated_at' => $now,
                ]);
            }

            DB::commit();
            return $this->successResponse(null, 'Data berhasil disimpan');
        } catch (\Exception $e) {
            DB::rollBack();
            return $this->errorResponse($e->getMessage() . ' ' . $e->getLine());
        }
    }
    // KDP End

    // AsetTakBerwujud Start
    function getAsetTakBerwujud(Request $request)
    {
        $validate = Validator::make($request->all(), [
            'instance' => 'nullable|exists:instances,id',
            'year' => 'required|integer',
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
            $instances = [];
            if (!$request->instance) {
                $instanceIds = DB::table('acc_lra')
                    ->select('instance_id')
                    ->groupBy('instance_id')
                    ->get();
                // $instances = Instance::whereIn('id', $instanceIds->pluck('instance_id'))->get();
                $instances = Instance::orderBy('name')->get();
            } elseif ($request->instance) {
                $instances = Instance::where('id', $request->instance)->get();
            }

            $values = [];
            foreach ($instances as $instance) {
                $dataLRA = DB::table('acc_lra')
                    ->where('instance_id', $instance->id)
                    ->where('year', $request->year)
                    ->where('periode_id', $request->periode)
                    ->where('kode_rekening', '5.2.06')
                    ->first();

                $getData = AsetTakBerwujud::where('instance_id', $instance->id)
                    ->where('year', $request->year)
                    ->where('periode_id', $request->periode)
                    ->first();
                if (!$getData) {
                    DB::table('acc_rek_as_aset_tak_berwujud')->insert([
                        'periode_id' => $request->periode,
                        'year' => $request->year,
                        'instance_id' => $instance->id,
                        'plus_realisasi_belanja' => $dataLRA->realisasi ?? 0,
                    ]);

                    $getData = AsetTakBerwujud::where('instance_id', $instance->id)
                        ->where('year', $request->year)
                        ->where('periode_id', $request->periode)
                        ->first();
                }

                $getData->plus_realisasi_belanja = $dataLRA->realisasi ?? 0;

                // Hutang Belanja
                $HutangBaru = DB::table('acc_htb_hutang_baru')
                    ->where('instance_id', $instance->id)
                    ->where('year', $request->year)
                    ->where('periode_id', $request->periode)
                    ->sum('aset_lain_lain') ?? 0;
                $getData->plus_hutang_kegiatan = $HutangBaru ?? 0;
                $PembayaranHutang = DB::table('acc_htb_pembayaran_hutang')
                    ->where('instance_id', $instance->id)
                    ->where('year', $request->year)
                    ->where('periode_id', $request->periode)
                    ->sum('aset_lain_lain') ?? 0;
                $getData->min_pembayaran_utang = $PembayaranHutang ?? 0;

                // Atribusi
                $Atribusi = DB::table('acc_padb_atribusi')
                    ->where('instance_id', $instance->id)
                    ->where('year', $request->year)
                    ->where('periode_id', $request->periode)
                    ->sum('atri_aset_lain_lain') ?? 0;
                $getData->plus_atribusi = $Atribusi ?? 0;

                // Reklasifikasi KIB
                $ReklasKib = DB::table('acc_padb_penyesuaian_aset')
                    ->where('instance_id', $instance->id)
                    ->where('year', $request->year)
                    ->where('periode_id', $request->periode)
                    ->get();
                // $getData->plus_reklasifikasi_kib_a = $ReklasKib->sum('min_aset_tetap_tanah') ?? 0;
                // $getData->min_reklasifikasi_beban_kib_a = $ReklasKib->sum('plus_aset_tetap_tanah') ?? 0;
                // $getData->plus_reklasifikasi_kib_b = $ReklasKib->sum('min_aset_tetap_peralatan_mesin') ?? 0;
                // $getData->min_reklasifikasi_beban_kib_b = $ReklasKib->sum('plus_aset_tetap_peralatan_mesin') ?? 0;
                // $getData->plus_reklasifikasi_kib_c = $ReklasKib->sum('min_aset_tetap_gedung_bangunan') ?? 0;
                // $getData->min_reklasifikasi_beban_kib_c = $ReklasKib->sum('plus_aset_tetap_gedung_bangunan') ?? 0;
                // $getData->plus_reklasifikasi_kib_d = $ReklasKib->sum('min_aset_tetap_jalan_jaringan_irigasi') ?? 0;
                // $getData->min_reklasifikasi_beban_kib_d = $ReklasKib->sum('plus_aset_tetap_jalan_jaringan_irigasi') ?? 0;
                // $getData->plus_reklasifikasi_kib_e = $ReklasKib->sum('min_aset_tetap_lainnya') ?? 0;
                // $getData->min_reklasifikasi_beban_kib_e = $ReklasKib->sum('plus_aset_tetap_lainnya') ?? 0;
                // $getData->plus_reklasifikasi_kdp = $ReklasKib->sum('min_konstruksi_dalam_pekerjaan') ?? 0;
                // $getData->min_reklasifikasi_beban_kdp = $ReklasKib->sum('plus_konstruksi_dalam_pekerjaan') ?? 0;
                // $getData->plus_reklasifikasi_aset_lain_lain = $ReklasKib->sum('min_aset_lain_lain') ?? 0;
                // $getData->min_reklasifikasi_beban_aset_lain_lain = $ReklasKib->sum('plus_aset_lain_lain') ?? 0;

                $plusReklasKey = 'plus_aset_lain_lain';
                $minReklasKey = 'min_aset_lain_lain';

                $getData->plus_reklasifikasi_kib_a = $ReklasKib->where($plusReklasKey, '>', 0)
                    ->where('min_aset_tetap_tanah', '>', 0)
                    ->sum($plusReklasKey) ?? 0;
                $getData->min_reklasifikasi_beban_kib_a = $ReklasKib->where($minReklasKey, '>', 0)
                    ->where('plus_aset_tetap_tanah', '>', 0)
                    ->sum($minReklasKey) ?? 0;

                $getData->plus_reklasifikasi_kib_b = $ReklasKib->where($plusReklasKey, '>', 0)
                    ->where('min_aset_tetap_peralatan_mesin', '>', 0)
                    ->sum($plusReklasKey) ?? 0;
                $getData->min_reklasifikasi_beban_kib_b = $ReklasKib->where($minReklasKey, '>', 0)
                    ->where('plus_aset_tetap_peralatan_mesin', '>', 0)
                    ->sum($minReklasKey) ?? 0;

                $getData->plus_reklasifikasi_kib_c = $ReklasKib->where($plusReklasKey, '>', 0)
                    ->where('min_aset_tetap_gedung_bangunan', '>', 0)
                    ->sum($plusReklasKey) ?? 0;
                $getData->min_reklasifikasi_beban_kib_c = $ReklasKib->where($minReklasKey, '>', 0)
                    ->where('plus_aset_tetap_gedung_bangunan', '>', 0)
                    ->sum($minReklasKey) ?? 0;

                $getData->plus_reklasifikasi_kib_d = $ReklasKib->where($plusReklasKey, '>', 0)
                    ->where('min_aset_tetap_jalan_jaringan_irigasi', '>', 0)
                    ->sum($plusReklasKey) ?? 0;
                $getData->min_reklasifikasi_beban_kib_d = $ReklasKib->where($minReklasKey, '>', 0)
                    ->where('plus_aset_tetap_jalan_jaringan_irigasi', '>', 0)
                    ->sum($minReklasKey) ?? 0;

                $getData->plus_reklasifikasi_kib_e = $ReklasKib->where($plusReklasKey, '>', 0)
                    ->where('min_aset_tetap_lainnya', '>', 0)
                    ->sum($plusReklasKey) ?? 0;
                $getData->min_reklasifikasi_beban_kib_e = $ReklasKib->where($minReklasKey, '>', 0)
                    ->where('plus_aset_tetap_lainnya', '>', 0)
                    ->sum($minReklasKey) ?? 0;

                $getData->plus_reklasifikasi_kdp = $ReklasKib->where($plusReklasKey, '>', 0)
                    ->where('min_konstruksi_dalam_pekerjaan', '>', 0)
                    ->sum($plusReklasKey) ?? 0;
                $getData->min_reklasifikasi_beban_kdp = $ReklasKib->where($minReklasKey, '>', 0)
                    ->where('plus_konstruksi_dalam_pekerjaan', '>', 0)
                    ->sum($minReklasKey) ?? 0;

                $getData->plus_reklasifikasi_aset_lain_lain = $ReklasKib->where($plusReklasKey, '>', 0)
                    ->where('min_aset_lain_lain', '>', 0)
                    ->sum($plusReklasKey) ?? 0;
                $getData->min_reklasifikasi_beban_aset_lain_lain = $ReklasKib->where($minReklasKey, '>', 0)
                    ->where('plus_aset_lain_lain', '>', 0)
                    ->sum($minReklasKey) ?? 0;

                // Hibah Masuk
                $HibahMasuk = DB::table('acc_padb_tambahan_hibah_masuk')
                    ->where('instance_id', $instance->id)
                    ->where('year', $request->year)
                    ->where('periode_id', $request->periode)
                    ->sum('aset_lainnya') ?? 0;
                $getData->plus_hibah_masuk = $HibahMasuk ?? 0;

                // Hibah Keluar
                $HibahKeluar = DB::table('acc_padb_tambahan_hibah_keluar')
                    ->where('instance_id', $instance->id)
                    ->where('year', $request->year)
                    ->where('periode_id', $request->periode)
                    ->sum('aset_lainnya') ?? 0;
                $getData->min_reklasifikasi_beban_hibah = $HibahKeluar ?? 0;

                // Penilaian
                $PenilaianAset = DB::table('acc_padb_penilaian_aset')
                    ->where('instance_id', $instance->id)
                    ->where('year', $request->year)
                    ->where('periode_id', $request->periode)
                    ->sum('aset_lainnya') ?? 0;
                $getData->plus_penilaian = $PenilaianAset ?? 0;

                // Mutasi OPD
                $MutasiOPDMasuk = DB::table('acc_padb_tambahan_mutasi_aset')
                    // ->where('instance_id', $instance->id)
                    ->where('to_instance_id', $instance->id)
                    ->where('year', $request->year)
                    ->where('periode_id', $request->periode)
                    ->get();
                $MutasiOPDKeluar = DB::table('acc_padb_tambahan_mutasi_aset')
                    // ->where('instance_id', $instance->id)
                    ->where('from_instance_id', $instance->id)
                    ->where('year', $request->year)
                    ->where('periode_id', $request->periode)
                    ->get();
                $getData->plus_mutasi_antar_opd = $MutasiOPDMasuk->sum('plus_aset_lainnya') ?? 0;
                $getData->min_mutasi_antar_opd = $MutasiOPDKeluar->sum('plus_aset_lainnya') ?? 0;

                $minPenghapusan = DB::table('acc_padb_penghapusan_aset')
                    ->where('instance_id', $instance->id)
                    ->where('year', $request->year)
                    ->where('periode_id', $request->periode)
                    ->sum('aset_lainnya') ?? 0;
                $minPenjualan = DB::table('acc_padb_penjualan_aset')
                    ->where('instance_id', $instance->id)
                    ->where('year', $request->year)
                    ->where('periode_id', $request->periode)
                    ->sum('aset_lainnya') ?? 0;

                $getData->min_penghapusan = ($minPenghapusan + $minPenjualan) ?? 0;
                $getData->timestamps = false;
                $getData->save();

                $saldoAwal = $getData->saldo_awal ?? 0;

                $getData->plus_total = ($getData->plus_realisasi_belanja ?? 0) + ($getData->plus_hutang_kegiatan ?? 0) + ($getData->plus_atribusi ?? 0) + ($getData->plus_reklasifikasi_barang_habis_pakai ?? 0) + ($getData->plus_reklasifikasi_pemeliharaan ?? 0) + ($getData->plus_reklasifikasi_jasa ?? 0) + ($getData->plus_reklasifikasi_kib_a ?? 0) + ($getData->plus_reklasifikasi_kib_b ?? 0) + ($getData->plus_reklasifikasi_kib_c ?? 0) + ($getData->plus_reklasifikasi_kib_d ?? 0) + ($getData->plus_reklasifikasi_kib_e ?? 0) + ($getData->plus_reklasifikasi_kdp ?? 0) + ($getData->plus_reklasifikasi_aset_lain_lain ?? 0) + ($getData->plus_hibah_masuk ?? 0) + ($getData->plus_penilaian ?? 0) + ($getData->plus_mutasi_antar_opd ?? 0);

                $getData->min_total = ($getData->min_pembayaran_utang ?? 0) + ($getData->min_reklasifikasi_beban_persediaan ?? 0) + ($getData->min_reklasifikasi_beban_jasa ?? 0) + ($getData->min_reklasifikasi_beban_pemeliharaan ?? 0) + ($getData->min_reklasifikasi_beban_hibah ?? 0) + ($getData->min_reklasifikasi_beban_kib_a ?? 0) + ($getData->min_reklasifikasi_beban_kib_b ?? 0) + ($getData->min_reklasifikasi_beban_kib_c ?? 0) + ($getData->min_reklasifikasi_beban_kib_d ?? 0) + ($getData->min_reklasifikasi_beban_kib_e ?? 0) + ($getData->min_reklasifikasi_beban_kdp ?? 0) + ($getData->min_reklasifikasi_beban_aset_lain_lain ?? 0) + ($getData->min_penghapusan ?? 0) + ($getData->min_mutasi_antar_opd ?? 0) + ($getData->min_tptgr ?? 0);

                $calculateSaldoAkhir = $saldoAwal + ($getData->plus_total ?? 0) - ($getData->min_total ?? 0);

                DB::table('acc_rek_as_aset_tak_berwujud')
                    ->where('id', $getData->id)
                    ->update([
                        'saldo_akhir' => $calculateSaldoAkhir,
                    ]);

                $values[] = [
                    'data_id' => $getData->id,
                    'instance_id' => $instance->id,
                    'instance_name' => $instance->name,
                    'saldo_awal' => $saldoAwal ?? 0,
                    'saldo_akhir' => $calculateSaldoAkhir,

                    'plus_realisasi_belanja' => $dataLRA->realisasi ?? $getData->plus_realisasi_belanja ?? 0,
                    // 'plus_realisasi_belanja' => $getData->plus_realisasi_belanja ?? 0,
                    'plus_hutang_kegiatan' => $getData->plus_hutang_kegiatan ?? 0,
                    'plus_atribusi' => $getData->plus_atribusi ?? 0,
                    'plus_reklasifikasi_barang_habis_pakai' => $getData->plus_reklasifikasi_barang_habis_pakai ?? 0,
                    'plus_reklasifikasi_pemeliharaan' => $getData->plus_reklasifikasi_pemeliharaan ?? 0,
                    'plus_reklasifikasi_jasa' => $getData->plus_reklasifikasi_jasa ?? 0,
                    'plus_reklasifikasi_kib_a' => $getData->plus_reklasifikasi_kib_a ?? 0,
                    'plus_reklasifikasi_kib_b' => $getData->plus_reklasifikasi_kib_b ?? 0,
                    'plus_reklasifikasi_kib_c' => $getData->plus_reklasifikasi_kib_c ?? 0,
                    'plus_reklasifikasi_kib_d' => $getData->plus_reklasifikasi_kib_d ?? 0,
                    'plus_reklasifikasi_kib_e' => $getData->plus_reklasifikasi_kib_e ?? 0,
                    'plus_reklasifikasi_kdp' => $getData->plus_reklasifikasi_kdp ?? 0,
                    'plus_reklasifikasi_aset_lain_lain' => $getData->plus_reklasifikasi_aset_lain_lain ?? 0,
                    'plus_hibah_masuk' => $getData->plus_hibah_masuk ?? 0,
                    'plus_penilaian' => $getData->plus_penilaian ?? 0,
                    'plus_mutasi_antar_opd' => $getData->plus_mutasi_antar_opd ?? 0,
                    'plus_total' => $getData->plus_total ?? 0,

                    'min_pembayaran_utang' => $getData->min_pembayaran_utang ?? 0,
                    'min_reklasifikasi_beban_persediaan' => $getData->min_reklasifikasi_beban_persediaan ?? 0,
                    'min_reklasifikasi_beban_jasa' => $getData->min_reklasifikasi_beban_jasa ?? 0,
                    'min_reklasifikasi_beban_pemeliharaan' => $getData->min_reklasifikasi_beban_pemeliharaan ?? 0,
                    'min_reklasifikasi_beban_hibah' => $getData->min_reklasifikasi_beban_hibah ?? 0,
                    'min_reklasifikasi_beban_kib_a' => $getData->min_reklasifikasi_beban_kib_a ?? 0,
                    'min_reklasifikasi_beban_kib_b' => $getData->min_reklasifikasi_beban_kib_b ?? 0,
                    'min_reklasifikasi_beban_kib_c' => $getData->min_reklasifikasi_beban_kib_c ?? 0,
                    'min_reklasifikasi_beban_kib_d' => $getData->min_reklasifikasi_beban_kib_d ?? 0,
                    'min_reklasifikasi_beban_kib_e' => $getData->min_reklasifikasi_beban_kib_e ?? 0,
                    'min_reklasifikasi_beban_kdp' => $getData->min_reklasifikasi_beban_kdp ?? 0,
                    'min_reklasifikasi_beban_aset_lain_lain' => $getData->min_reklasifikasi_beban_aset_lain_lain ?? 0,
                    'min_penghapusan' => $getData->min_penghapusan ?? 0,
                    'min_mutasi_antar_opd' => $getData->min_mutasi_antar_opd ?? 0,
                    'min_tptgr' => $getData->min_tptgr ?? 0,
                    'min_total' => $getData->min_total ?? 0,
                ];
            }

            $datas['instances'] = $instances;
            $datas['datas'] = $values;

            return $this->successResponse($datas, 'Penyesuai Aset dan Beban berhasil diambil');
        } catch (\Exception $e) {
            return $this->errorResponse($e->getMessage() . ' ' . $e->getLine());
        }
    }

    function saveAsetTakBerwujud(Request $request)
    {
        $validate = Validator::make($request->all(), [
            'instance' => 'nullable|exists:instances,id',
            'year' => 'required|integer',
            'periode' => 'required|exists:ref_periode,id'
        ], [], [
            'instance' => 'Instance ID',
            'periode' => 'Periode'
        ]);
        if ($validate->fails()) {
            return $this->validationResponse($validate->errors());
        }

        $now = now();
        DB::beginTransaction();
        try {
            $datas = $request->data;
            foreach ($datas as $input) {
                DB::table('acc_rek_as_aset_tak_berwujud')->updateOrInsert([
                    'periode_id' => $request->periode,
                    'year' => $request->year,
                    'instance_id' => $input['instance_id'],
                ], [
                    'periode_id' => $request->periode,
                    'year' => $request->year,
                    'instance_id' => $input['instance_id'],
                    'saldo_awal' => $input['saldo_awal'],
                    'saldo_akhir' => $input['saldo_akhir'],
                    'plus_realisasi_belanja' => $input['plus_realisasi_belanja'],
                    'plus_hutang_kegiatan' => $input['plus_hutang_kegiatan'],
                    'plus_atribusi' => $input['plus_atribusi'],
                    'plus_reklasifikasi_barang_habis_pakai' => $input['plus_reklasifikasi_barang_habis_pakai'],
                    'plus_reklasifikasi_pemeliharaan' => $input['plus_reklasifikasi_pemeliharaan'],
                    'plus_reklasifikasi_jasa' => $input['plus_reklasifikasi_jasa'],
                    'plus_reklasifikasi_kib_a' => $input['plus_reklasifikasi_kib_a'],
                    'plus_reklasifikasi_kib_b' => $input['plus_reklasifikasi_kib_b'],
                    'plus_reklasifikasi_kib_c' => $input['plus_reklasifikasi_kib_c'],
                    'plus_reklasifikasi_kib_d' => $input['plus_reklasifikasi_kib_d'],
                    'plus_reklasifikasi_kib_e' => $input['plus_reklasifikasi_kib_e'],
                    'plus_reklasifikasi_kdp' => $input['plus_reklasifikasi_kdp'],
                    'plus_reklasifikasi_aset_lain_lain' => $input['plus_reklasifikasi_aset_lain_lain'],
                    'plus_hibah_masuk' => $input['plus_hibah_masuk'],
                    'plus_penilaian' => $input['plus_penilaian'],
                    'plus_mutasi_antar_opd' => $input['plus_mutasi_antar_opd'],
                    'min_pembayaran_utang' => $input['min_pembayaran_utang'],
                    'min_reklasifikasi_beban_persediaan' => $input['min_reklasifikasi_beban_persediaan'],
                    'min_reklasifikasi_beban_jasa' => $input['min_reklasifikasi_beban_jasa'],
                    'min_reklasifikasi_beban_pemeliharaan' => $input['min_reklasifikasi_beban_pemeliharaan'],
                    'min_reklasifikasi_beban_hibah' => $input['min_reklasifikasi_beban_hibah'],
                    'min_reklasifikasi_beban_kib_a' => $input['min_reklasifikasi_beban_kib_a'],
                    'min_reklasifikasi_beban_kib_b' => $input['min_reklasifikasi_beban_kib_b'],
                    'min_reklasifikasi_beban_kib_c' => $input['min_reklasifikasi_beban_kib_c'],
                    'min_reklasifikasi_beban_kib_d' => $input['min_reklasifikasi_beban_kib_d'],
                    'min_reklasifikasi_beban_kib_e' => $input['min_reklasifikasi_beban_kib_e'],
                    'min_reklasifikasi_beban_kdp' => $input['min_reklasifikasi_beban_kdp'],
                    'min_reklasifikasi_beban_aset_lain_lain' => $input['min_reklasifikasi_beban_aset_lain_lain'],
                    'min_penghapusan' => $input['min_penghapusan'],
                    'min_mutasi_antar_opd' => $input['min_mutasi_antar_opd'],
                    'min_tptgr' => $input['min_tptgr'],
                    'updated_by' => auth()->user()->id,
                    'updated_at' => $now,
                ]);
            }

            DB::commit();
            return $this->successResponse(null, 'Data berhasil disimpan');
        } catch (\Exception $e) {
            DB::rollBack();
            return $this->errorResponse($e->getMessage() . ' ' . $e->getLine());
        }
    }
    // AsetTakBerwujud End

    // RekapAsetLainnya Start
    function getRekapAsetLainnya(Request $request)
    {
        $validate = Validator::make($request->all(), [
            'instance' => 'nullable|exists:instances,id',
            'year' => 'required|integer',
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
            $instances = [];
            if (!$request->instance) {
                $instanceIds = DB::table('acc_lra')
                    ->select('instance_id')
                    ->groupBy('instance_id')
                    ->get();
                // $instances = Instance::whereIn('id', $instanceIds->pluck('instance_id'))->get();
                $instances = Instance::orderBy('name')->get();
            } elseif ($request->instance) {
                $instances = Instance::where('id', $request->instance)->get();
            }

            $values = [];
            foreach ($instances as $instance) {
                $getData = DB::table('acc_rek_as_aset_lainnya')
                    ->where('instance_id', $instance->id)
                    ->where('year', $request->year)
                    ->where('periode_id', $request->periode)
                    ->first();

                $asetTakBerwujud = DB::table('acc_rek_as_aset_tak_berwujud')
                    ->where('instance_id', $instance->id)
                    ->where('year', $request->year)
                    ->where('periode_id', $request->periode)
                    ->first();
                $asetLainLain = DB::table('acc_rek_as_aset_lain_lain')
                    ->where('instance_id', $instance->id)
                    ->where('year', $request->year)
                    ->where('periode_id', $request->periode)
                    ->first();

                if (!$getData) {
                    DB::table('acc_rek_as_aset_lainnya')->insert([
                        'periode_id' => $request->periode,
                        'year' => $request->year,
                        'instance_id' => $instance->id,

                        'saldo_awal' => ($asetTakBerwujud->saldo_awal ?? 0) + ($asetLainLain->saldo_awal ?? 0),
                        'saldo_akhir' => ($asetTakBerwujud->saldo_akhir ?? 0) + ($asetLainLain->saldo_akhir ?? 0),

                        'plus_realisasi_belanja' => ($asetTakBerwujud->plus_realisasi_belanja ?? 0) + ($asetLainLain->plus_realisasi_belanja ?? 0),
                        'plus_hutang_kegiatan' => ($asetTakBerwujud->plus_hutang_kegiatan ?? 0) + ($asetLainLain->plus_hutang_kegiatan ?? 0),
                        'plus_atribusi' => ($asetTakBerwujud->plus_atribusi ?? 0) + ($asetLainLain->plus_atribusi ?? 0),
                        'plus_reklasifikasi_barang_habis_pakai' => ($asetTakBerwujud->plus_reklasifikasi_barang_habis_pakai ?? 0) + ($asetLainLain->plus_reklasifikasi_barang_habis_pakai ?? 0),
                        'plus_reklasifikasi_pemeliharaan' => ($asetTakBerwujud->plus_reklasifikasi_pemeliharaan ?? 0) + ($asetLainLain->plus_reklasifikasi_pemeliharaan ?? 0),
                        'plus_reklasifikasi_jasa' => ($asetTakBerwujud->plus_reklasifikasi_jasa ?? 0) + ($asetLainLain->plus_reklasifikasi_jasa ?? 0),
                        'plus_reklasifikasi_kib_a' => ($asetTakBerwujud->plus_reklasifikasi_kib_a ?? 0) + ($asetLainLain->plus_reklasifikasi_kib_a ?? 0),
                        'plus_reklasifikasi_kib_b' => ($asetTakBerwujud->plus_reklasifikasi_kib_b ?? 0) + ($asetLainLain->plus_reklasifikasi_kib_b ?? 0),
                        'plus_reklasifikasi_kib_c' => ($asetTakBerwujud->plus_reklasifikasi_kib_c ?? 0) + ($asetLainLain->plus_reklasifikasi_kib_c ?? 0),
                        'plus_reklasifikasi_kib_d' => ($asetTakBerwujud->plus_reklasifikasi_kib_d ?? 0) + ($asetLainLain->plus_reklasifikasi_kib_d ?? 0),
                        'plus_reklasifikasi_kib_e' => ($asetTakBerwujud->plus_reklasifikasi_kib_e ?? 0) + ($asetLainLain->plus_reklasifikasi_kib_e ?? 0),
                        'plus_reklasifikasi_kdp' => ($asetTakBerwujud->plus_reklasifikasi_kdp ?? 0) + ($asetLainLain->plus_reklasifikasi_kdp ?? 0),
                        'plus_reklasifikasi_aset_lain_lain' => ($asetTakBerwujud->plus_reklasifikasi_aset_lain_lain ?? 0) + ($asetLainLain->plus_reklasifikasi_aset_lain_lain ?? 0),
                        'plus_hibah_masuk' => ($asetTakBerwujud->plus_hibah_masuk ?? 0) + ($asetLainLain->plus_hibah_masuk ?? 0),
                        'plus_penilaian' => ($asetTakBerwujud->plus_penilaian ?? 0) + ($asetLainLain->plus_penilaian ?? 0),
                        'plus_mutasi_antar_opd' => ($asetTakBerwujud->plus_mutasi_antar_opd ?? 0) + ($asetLainLain->plus_mutasi_antar_opd ?? 0),

                        'min_pembayaran_utang' => ($asetTakBerwujud->min_pembayaran_utang ?? 0) + ($asetLainLain->min_pembayaran_utang ?? 0),
                        'min_reklasifikasi_beban_persediaan' => ($asetTakBerwujud->min_reklasifikasi_beban_persediaan ?? 0) + ($asetLainLain->min_reklasifikasi_beban_persediaan ?? 0),
                        'min_reklasifikasi_beban_jasa' => ($asetTakBerwujud->min_reklasifikasi_beban_jasa ?? 0) + ($asetLainLain->min_reklasifikasi_beban_jasa ?? 0),
                        'min_reklasifikasi_beban_pemeliharaan' => ($asetTakBerwujud->min_reklasifikasi_beban_pemeliharaan ?? 0) + ($asetLainLain->min_reklasifikasi_beban_pemeliharaan ?? 0),
                        'min_reklasifikasi_beban_hibah' => ($asetTakBerwujud->min_reklasifikasi_beban_hibah ?? 0) + ($asetLainLain->min_reklasifikasi_beban_hibah ?? 0),
                        'min_reklasifikasi_beban_kib_a' => ($asetTakBerwujud->min_reklasifikasi_beban_kib_a ?? 0) + ($asetLainLain->min_reklasifikasi_beban_kib_a ?? 0),
                        'min_reklasifikasi_beban_kib_b' => ($asetTakBerwujud->min_reklasifikasi_beban_kib_b ?? 0) + ($asetLainLain->min_reklasifikasi_beban_kib_b ?? 0),
                        'min_reklasifikasi_beban_kib_c' => ($asetTakBerwujud->min_reklasifikasi_beban_kib_c ?? 0) + ($asetLainLain->min_reklasifikasi_beban_kib_c ?? 0),
                        'min_reklasifikasi_beban_kib_d' => ($asetTakBerwujud->min_reklasifikasi_beban_kib_d ?? 0) + ($asetLainLain->min_reklasifikasi_beban_kib_d ?? 0),
                        'min_reklasifikasi_beban_kib_e' => ($asetTakBerwujud->min_reklasifikasi_beban_kib_e ?? 0) + ($asetLainLain->min_reklasifikasi_beban_kib_e ?? 0),
                        'min_reklasifikasi_beban_kdp' => ($asetTakBerwujud->min_reklasifikasi_beban_kdp ?? 0) + ($asetLainLain->min_reklasifikasi_beban_kdp ?? 0),
                        'min_reklasifikasi_beban_aset_lain_lain' => ($asetTakBerwujud->min_reklasifikasi_beban_aset_lain_lain ?? 0) + ($asetLainLain->min_reklasifikasi_beban_aset_lain_lain ?? 0),
                        'min_penghapusan' => ($asetTakBerwujud->min_penghapusan ?? 0) + ($asetLainLain->min_penghapusan ?? 0),
                        'min_mutasi_antar_opd' => ($asetTakBerwujud->min_mutasi_antar_opd ?? 0) + ($asetLainLain->min_mutasi_antar_opd ?? 0),
                        'min_tptgr' => ($asetTakBerwujud->min_tptgr ?? 0) + ($asetLainLain->min_tptgr ?? 0),
                    ]);

                    $getData = DB::table('acc_rek_as_aset_lainnya')
                        ->where('instance_id', $instance->id)
                        ->where('year', $request->year)
                        ->where('periode_id', $request->periode)
                        ->first();
                } elseif ($getData) {
                    DB::table('acc_rek_as_aset_lainnya')
                        ->where('id', $getData->id)
                        ->update([
                            'saldo_awal' => ($asetTakBerwujud->saldo_awal ?? 0) + ($asetLainLain->saldo_awal ?? 0),
                            'saldo_akhir' => ($asetTakBerwujud->saldo_akhir ?? 0) + ($asetLainLain->saldo_akhir ?? 0),

                            'plus_realisasi_belanja' => ($asetTakBerwujud->plus_realisasi_belanja ?? 0) + ($asetLainLain->plus_realisasi_belanja ?? 0),
                            'plus_hutang_kegiatan' => ($asetTakBerwujud->plus_hutang_kegiatan ?? 0) + ($asetLainLain->plus_hutang_kegiatan ?? 0),
                            'plus_atribusi' => ($asetTakBerwujud->plus_atribusi ?? 0) + ($asetLainLain->plus_atribusi ?? 0),
                            'plus_reklasifikasi_barang_habis_pakai' => ($asetTakBerwujud->plus_reklasifikasi_barang_habis_pakai ?? 0) + ($asetLainLain->plus_reklasifikasi_barang_habis_pakai ?? 0),
                            'plus_reklasifikasi_pemeliharaan' => ($asetTakBerwujud->plus_reklasifikasi_pemeliharaan ?? 0) + ($asetLainLain->plus_reklasifikasi_pemeliharaan ?? 0),
                            'plus_reklasifikasi_jasa' => ($asetTakBerwujud->plus_reklasifikasi_jasa ?? 0) + ($asetLainLain->plus_reklasifikasi_jasa ?? 0),
                            'plus_reklasifikasi_kib_a' => ($asetTakBerwujud->plus_reklasifikasi_kib_a ?? 0) + ($asetLainLain->plus_reklasifikasi_kib_a ?? 0),
                            'plus_reklasifikasi_kib_b' => ($asetTakBerwujud->plus_reklasifikasi_kib_b ?? 0) + ($asetLainLain->plus_reklasifikasi_kib_b ?? 0),
                            'plus_reklasifikasi_kib_c' => ($asetTakBerwujud->plus_reklasifikasi_kib_c ?? 0) + ($asetLainLain->plus_reklasifikasi_kib_c ?? 0),
                            'plus_reklasifikasi_kib_d' => ($asetTakBerwujud->plus_reklasifikasi_kib_d ?? 0) + ($asetLainLain->plus_reklasifikasi_kib_d ?? 0),
                            'plus_reklasifikasi_kib_e' => ($asetTakBerwujud->plus_reklasifikasi_kib_e ?? 0) + ($asetLainLain->plus_reklasifikasi_kib_e ?? 0),
                            'plus_reklasifikasi_kdp' => ($asetTakBerwujud->plus_reklasifikasi_kdp ?? 0) + ($asetLainLain->plus_reklasifikasi_kdp ?? 0),
                            'plus_reklasifikasi_aset_lain_lain' => ($asetTakBerwujud->plus_reklasifikasi_aset_lain_lain ?? 0) + ($asetLainLain->plus_reklasifikasi_aset_lain_lain ?? 0),
                            'plus_hibah_masuk' => ($asetTakBerwujud->plus_hibah_masuk ?? 0) + ($asetLainLain->plus_hibah_masuk ?? 0),
                            'plus_penilaian' => ($asetTakBerwujud->plus_penilaian ?? 0) + ($asetLainLain->plus_penilaian ?? 0),
                            'plus_mutasi_antar_opd' => ($asetTakBerwujud->plus_mutasi_antar_opd ?? 0) + ($asetLainLain->plus_mutasi_antar_opd ?? 0),

                            'min_pembayaran_utang' => ($asetTakBerwujud->min_pembayaran_utang ?? 0) + ($asetLainLain->min_pembayaran_utang ?? 0),
                            'min_reklasifikasi_beban_persediaan' => ($asetTakBerwujud->min_reklasifikasi_beban_persediaan ?? 0) + ($asetLainLain->min_reklasifikasi_beban_persediaan ?? 0),
                            'min_reklasifikasi_beban_pemeliharaan' => ($asetTakBerwujud->min_reklasifikasi_beban_pemeliharaan ?? 0) + ($asetLainLain->min_reklasifikasi_beban_pemeliharaan ?? 0),
                            'min_reklasifikasi_beban_jasa' => ($asetTakBerwujud->min_reklasifikasi_beban_jasa ?? 0) + ($asetLainLain->min_reklasifikasi_beban_jasa ?? 0),
                            'min_reklasifikasi_beban_hibah' => ($asetTakBerwujud->min_reklasifikasi_beban_hibah ?? 0) + ($asetLainLain->min_reklasifikasi_beban_hibah ?? 0),
                            'min_reklasifikasi_beban_kib_a' => ($asetTakBerwujud->min_reklasifikasi_beban_kib_a ?? 0) + ($asetLainLain->min_reklasifikasi_beban_kib_a ?? 0),
                            'min_reklasifikasi_beban_kib_b' => ($asetTakBerwujud->min_reklasifikasi_beban_kib_b ?? 0) + ($asetLainLain->min_reklasifikasi_beban_kib_b ?? 0),
                            'min_reklasifikasi_beban_kib_c' => ($asetTakBerwujud->min_reklasifikasi_beban_kib_c ?? 0) + ($asetLainLain->min_reklasifikasi_beban_kib_c ?? 0),
                            'min_reklasifikasi_beban_kib_d' => ($asetTakBerwujud->min_reklasifikasi_beban_kib_d ?? 0) + ($asetLainLain->min_reklasifikasi_beban_kib_d ?? 0),
                            'min_reklasifikasi_beban_kib_e' => ($asetTakBerwujud->min_reklasifikasi_beban_kib_e ?? 0) + ($asetLainLain->min_reklasifikasi_beban_kib_e ?? 0),
                            'min_reklasifikasi_beban_kdp' => ($asetTakBerwujud->min_reklasifikasi_beban_kdp ?? 0) + ($asetLainLain->min_reklasifikasi_beban_kdp ?? 0),
                            'min_reklasifikasi_beban_aset_lain_lain' => ($asetTakBerwujud->min_reklasifikasi_beban_aset_lain_lain ?? 0) + ($asetLainLain->min_reklasifikasi_beban_aset_lain_lain ?? 0),
                            'min_penghapusan' => ($asetTakBerwujud->min_penghapusan ?? 0) + ($asetLainLain->min_penghapusan ?? 0),
                            'min_mutasi_antar_opd' => ($asetTakBerwujud->min_mutasi_antar_opd ?? 0) + ($asetLainLain->min_mutasi_antar_opd ?? 0),
                            'min_tptgr' => ($asetTakBerwujud->min_tptgr ?? 0) + ($asetLainLain->min_tptgr ?? 0),
                        ]);

                    $getData = DB::table('acc_rek_as_aset_lainnya')
                        ->where('instance_id', $instance->id)
                        ->where('year', $request->year)
                        ->where('periode_id', $request->periode)
                        ->first();
                }

                $saldoAwal = $getData->saldo_awal ?? 0;

                $getData->plus_total = ($getData->plus_realisasi_belanja ?? 0) + ($getData->plus_hutang_kegiatan ?? 0) + ($getData->plus_atribusi ?? 0) + ($getData->plus_reklasifikasi_barang_habis_pakai ?? 0) + ($getData->plus_reklasifikasi_pemeliharaan ?? 0) + ($getData->plus_reklasifikasi_jasa ?? 0) + ($getData->plus_reklasifikasi_kib_a ?? 0) + ($getData->plus_reklasifikasi_kib_b ?? 0) + ($getData->plus_reklasifikasi_kib_c ?? 0) + ($getData->plus_reklasifikasi_kib_d ?? 0) + ($getData->plus_reklasifikasi_kib_e ?? 0) + ($getData->plus_reklasifikasi_kdp ?? 0) + ($getData->plus_reklasifikasi_aset_lain_lain ?? 0) + ($getData->plus_hibah_masuk ?? 0) + ($getData->plus_penilaian ?? 0) + ($getData->plus_mutasi_antar_opd ?? 0);

                $getData->min_total = ($getData->min_pembayaran_utang ?? 0) + ($getData->min_reklasifikasi_beban_persediaan ?? 0) + ($getData->min_reklasifikasi_beban_jasa ?? 0) + ($getData->min_reklasifikasi_beban_pemeliharaan ?? 0) + ($getData->min_reklasifikasi_beban_hibah ?? 0) + ($getData->min_reklasifikasi_beban_kib_a ?? 0) + ($getData->min_reklasifikasi_beban_kib_b ?? 0) + ($getData->min_reklasifikasi_beban_kib_c ?? 0) + ($getData->min_reklasifikasi_beban_kib_d ?? 0) + ($getData->min_reklasifikasi_beban_kib_e ?? 0) + ($getData->min_reklasifikasi_beban_kdp ?? 0) + ($getData->min_reklasifikasi_beban_aset_lain_lain ?? 0) + ($getData->min_penghapusan ?? 0) + ($getData->min_mutasi_antar_opd ?? 0) + ($getData->min_tptgr ?? 0);

                $calculateSaldoAkhir = $saldoAwal + ($getData->plus_total ?? 0) - ($getData->min_total ?? 0);

                DB::table('acc_rek_as_aset_lainnya')
                    ->where('id', $getData->id)
                    ->update([
                        'saldo_akhir' => $calculateSaldoAkhir,
                    ]);

                $values[] = [
                    'data_id' => $getData->id,
                    'instance_id' => $instance->id,
                    'instance_name' => $instance->name,
                    // 'saldo_awal' => $saldoAwal ?? 0,
                    'saldo_awal' => $getData->saldo_awal ?? 0,
                    'saldo_akhir' => $calculateSaldoAkhir,

                    'plus_realisasi_belanja' => $getData->plus_realisasi_belanja ?? 0,
                    'plus_hutang_kegiatan' => $getData->plus_hutang_kegiatan ?? 0,
                    'plus_atribusi' => $getData->plus_atribusi ?? 0,
                    'plus_reklasifikasi_barang_habis_pakai' => $getData->plus_reklasifikasi_barang_habis_pakai ?? 0,
                    'plus_reklasifikasi_pemeliharaan' => $getData->plus_reklasifikasi_pemeliharaan ?? 0,
                    'plus_reklasifikasi_jasa' => $getData->plus_reklasifikasi_jasa ?? 0,
                    'plus_reklasifikasi_kib_a' => $getData->plus_reklasifikasi_kib_a ?? 0,
                    'plus_reklasifikasi_kib_b' => $getData->plus_reklasifikasi_kib_b ?? 0,
                    'plus_reklasifikasi_kib_c' => $getData->plus_reklasifikasi_kib_c ?? 0,
                    'plus_reklasifikasi_kib_d' => $getData->plus_reklasifikasi_kib_d ?? 0,
                    'plus_reklasifikasi_kib_e' => $getData->plus_reklasifikasi_kib_e ?? 0,
                    'plus_reklasifikasi_kdp' => $getData->plus_reklasifikasi_kdp ?? 0,
                    'plus_reklasifikasi_aset_lain_lain' => $getData->plus_reklasifikasi_aset_lain_lain ?? 0,
                    'plus_hibah_masuk' => $getData->plus_hibah_masuk ?? 0,
                    'plus_penilaian' => $getData->plus_penilaian ?? 0,
                    'plus_mutasi_antar_opd' => $getData->plus_mutasi_antar_opd ?? 0,
                    'plus_total' => $getData->plus_total ?? 0,

                    'min_pembayaran_utang' => $getData->min_pembayaran_utang ?? 0,
                    'min_reklasifikasi_beban_persediaan' => $getData->min_reklasifikasi_beban_persediaan ?? 0,
                    'min_reklasifikasi_beban_jasa' => $getData->min_reklasifikasi_beban_jasa ?? 0,
                    'min_reklasifikasi_beban_pemeliharaan' => $getData->min_reklasifikasi_beban_pemeliharaan ?? 0,
                    'min_reklasifikasi_beban_hibah' => $getData->min_reklasifikasi_beban_hibah ?? 0,
                    'min_reklasifikasi_beban_kib_a' => $getData->min_reklasifikasi_beban_kib_a ?? 0,
                    'min_reklasifikasi_beban_kib_b' => $getData->min_reklasifikasi_beban_kib_b ?? 0,
                    'min_reklasifikasi_beban_kib_c' => $getData->min_reklasifikasi_beban_kib_c ?? 0,
                    'min_reklasifikasi_beban_kib_d' => $getData->min_reklasifikasi_beban_kib_d ?? 0,
                    'min_reklasifikasi_beban_kib_e' => $getData->min_reklasifikasi_beban_kib_e ?? 0,
                    'min_reklasifikasi_beban_kdp' => $getData->min_reklasifikasi_beban_kdp ?? 0,
                    'min_reklasifikasi_beban_aset_lain_lain' => $getData->min_reklasifikasi_beban_aset_lain_lain ?? 0,
                    'min_penghapusan' => $getData->min_penghapusan ?? 0,
                    'min_mutasi_antar_opd' => $getData->min_mutasi_antar_opd ?? 0,
                    'min_tptgr' => $getData->min_tptgr ?? 0,
                    'min_total' => $getData->min_total ?? 0,
                ];
            }

            $datas['instances'] = $instances;
            $datas['datas'] = $values;

            return $this->successResponse($datas, 'Penyesuai Aset dan Beban berhasil diambil');
        } catch (\Exception $e) {
            return $this->errorResponse($e->getMessage() . ' ' . $e->getLine());
        }
    }

    function saveRekapAsetLainnya(Request $request)
    {
        $validate = Validator::make($request->all(), [
            'instance' => 'nullable|exists:instances,id',
            'year' => 'required|integer',
            'periode' => 'required|exists:ref_periode,id'
        ], [], [
            'instance' => 'Instance ID',
            'periode' => 'Periode'
        ]);
        if ($validate->fails()) {
            return $this->validationResponse($validate->errors());
        }

        $now = now();
        DB::beginTransaction();
        try {
            $datas = $request->data;
            foreach ($datas as $input) {
                DB::table('acc_rek_as_aset_lainnya')->updateOrInsert([
                    'periode_id' => $request->periode,
                    'year' => $request->year,
                    'instance_id' => $input['instance_id'],
                ], [
                    'periode_id' => $request->periode,
                    'year' => $request->year,
                    'instance_id' => $input['instance_id'],
                    'saldo_awal' => $input['saldo_awal'],
                    'saldo_akhir' => $input['saldo_akhir'],
                    'plus_realisasi_belanja' => $input['plus_realisasi_belanja'],
                    'plus_hutang_kegiatan' => $input['plus_hutang_kegiatan'],
                    'plus_atribusi' => $input['plus_atribusi'],
                    'plus_reklasifikasi_barang_habis_pakai' => $input['plus_reklasifikasi_barang_habis_pakai'],
                    'plus_reklasifikasi_pemeliharaan' => $input['plus_reklasifikasi_pemeliharaan'],
                    'plus_reklasifikasi_jasa' => $input['plus_reklasifikasi_jasa'],
                    'plus_reklasifikasi_kib_a' => $input['plus_reklasifikasi_kib_a'],
                    'plus_reklasifikasi_kib_b' => $input['plus_reklasifikasi_kib_b'],
                    'plus_reklasifikasi_kib_c' => $input['plus_reklasifikasi_kib_c'],
                    'plus_reklasifikasi_kib_d' => $input['plus_reklasifikasi_kib_d'],
                    'plus_reklasifikasi_kib_e' => $input['plus_reklasifikasi_kib_e'],
                    'plus_reklasifikasi_kdp' => $input['plus_reklasifikasi_kdp'],
                    'plus_reklasifikasi_aset_lain_lain' => $input['plus_reklasifikasi_aset_lain_lain'],
                    'plus_hibah_masuk' => $input['plus_hibah_masuk'],
                    'plus_penilaian' => $input['plus_penilaian'],
                    'plus_mutasi_antar_opd' => $input['plus_mutasi_antar_opd'],
                    'min_pembayaran_utang' => $input['min_pembayaran_utang'],
                    'min_reklasifikasi_beban_persediaan' => $input['min_reklasifikasi_beban_persediaan'],
                    'min_reklasifikasi_beban_jasa' => $input['min_reklasifikasi_beban_jasa'],
                    'min_reklasifikasi_beban_pemeliharaan' => $input['min_reklasifikasi_beban_pemeliharaan'],
                    'min_reklasifikasi_beban_hibah' => $input['min_reklasifikasi_beban_hibah'],
                    'min_reklasifikasi_beban_kib_a' => $input['min_reklasifikasi_beban_kib_a'],
                    'min_reklasifikasi_beban_kib_b' => $input['min_reklasifikasi_beban_kib_b'],
                    'min_reklasifikasi_beban_kib_c' => $input['min_reklasifikasi_beban_kib_c'],
                    'min_reklasifikasi_beban_kib_d' => $input['min_reklasifikasi_beban_kib_d'],
                    'min_reklasifikasi_beban_kib_e' => $input['min_reklasifikasi_beban_kib_e'],
                    'min_reklasifikasi_beban_kdp' => $input['min_reklasifikasi_beban_kdp'],
                    'min_reklasifikasi_beban_aset_lain_lain' => $input['min_reklasifikasi_beban_aset_lain_lain'],
                    'min_penghapusan' => $input['min_penghapusan'],
                    'min_mutasi_antar_opd' => $input['min_mutasi_antar_opd'],
                    'min_tptgr' => $input['min_tptgr'],
                    'updated_by' => auth()->user()->id,
                    'updated_at' => $now,
                ]);
            }

            DB::commit();
            return $this->successResponse(null, 'Data berhasil disimpan');
        } catch (\Exception $e) {
            DB::rollBack();
            return $this->errorResponse($e->getMessage() . ' ' . $e->getLine());
        }
    }
    // RekapAsetLainnya End

    // getRekapAsetTetap Start
    function getRekapAsetTetap(Request $request)
    {
        $validate = Validator::make($request->all(), [
            'instance' => 'nullable|exists:instances,id',
            'year' => 'required|integer',
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
            $instances = [];
            if (!$request->instance) {
                $instanceIds = DB::table('acc_lra')
                    ->select('instance_id')
                    ->groupBy('instance_id')
                    ->get();
                // $instances = Instance::whereIn('id', $instanceIds->pluck('instance_id'))->get();
                $instances = Instance::orderBy('name')->get();
            } elseif ($request->instance) {
                $instances = Instance::where('id', $request->instance)->get();
            }

            $values = [];
            foreach ($instances as $instance) {
                $getData = DB::table('acc_rek_as_rekap_aset_tetap')
                    ->where('instance_id', $instance->id)
                    ->where('year', $request->year)
                    ->where('periode_id', $request->periode)
                    ->first();

                $KibA = DB::table('acc_rek_as_kib_a')
                    ->where('instance_id', $instance->id)
                    ->where('year', $request->year)
                    ->where('periode_id', $request->periode)
                    ->first();
                $KibB = DB::table('acc_rek_as_kib_b')
                    ->where('instance_id', $instance->id)
                    ->where('year', $request->year)
                    ->where('periode_id', $request->periode)
                    ->first();
                $KibC = DB::table('acc_rek_as_kib_c')
                    ->where('instance_id', $instance->id)
                    ->where('year', $request->year)
                    ->where('periode_id', $request->periode)
                    ->first();
                $KibD = DB::table('acc_rek_as_kib_d')
                    ->where('instance_id', $instance->id)
                    ->where('year', $request->year)
                    ->where('periode_id', $request->periode)
                    ->first();
                $KibE = DB::table('acc_rek_as_kib_e')
                    ->where('instance_id', $instance->id)
                    ->where('year', $request->year)
                    ->where('periode_id', $request->periode)
                    ->first();
                $KDP = DB::table('acc_rek_as_kdp')
                    ->where('instance_id', $instance->id)
                    ->where('year', $request->year)
                    ->where('periode_id', $request->periode)
                    ->first();

                if (!$getData) {
                    DB::table('acc_rek_as_rekap_aset_tetap')->insert([
                        'periode_id' => $request->periode,
                        'year' => $request->year,
                        'instance_id' => $instance->id,
                    ]);

                    $getData = DB::table('acc_rek_as_rekap_aset_tetap')
                        ->where('instance_id', $instance->id)
                        ->where('year', $request->year)
                        ->where('periode_id', $request->periode)
                        ->first();
                }

                if ($getData) {
                    $getData->saldo_awal = ($KibA->saldo_awal ?? 0) + ($KibB->saldo_awal ?? 0) + ($KibC->saldo_awal ?? 0) + ($KibD->saldo_awal ?? 0) + ($KibE->saldo_awal ?? 0) + ($KDP->saldo_awal ?? 0);
                    //
                    $getData->saldo_akhir = ($KibA->saldo_akhir ?? 0) + ($KibB->saldo_akhir ?? 0) + ($KibC->saldo_akhir ?? 0) + ($KibD->saldo_akhir ?? 0) + ($KibE->saldo_akhir ?? 0) + ($KDP->saldo_akhir ?? 0);
                    //
                    $getData->plus_realisasi_belanja = ($KibA->plus_realisasi_belanja ?? 0) + ($KibB->plus_realisasi_belanja ?? 0) + ($KibC->plus_realisasi_belanja ?? 0) + ($KibD->plus_realisasi_belanja ?? 0) + ($KibE->plus_realisasi_belanja ?? 0) + ($KDP->plus_realisasi_belanja ?? 0);
                    //
                    $getData->plus_hutang_kegiatan = ($KibA->plus_hutang_kegiatan ?? 0) + ($KibB->plus_hutang_kegiatan ?? 0) + ($KibC->plus_hutang_kegiatan ?? 0) + ($KibD->plus_hutang_kegiatan ?? 0) + ($KibE->plus_hutang_kegiatan ?? 0) + ($KDP->plus_hutang_kegiatan ?? 0);
                    //
                    $getData->plus_atribusi = ($KibA->plus_atribusi ?? 0) + ($KibB->plus_atribusi ?? 0) + ($KibC->plus_atribusi ?? 0) + ($KibD->plus_atribusi ?? 0) + ($KibE->plus_atribusi ?? 0) + ($KDP->plus_atribusi ?? 0);
                    //
                    $getData->plus_reklasifikasi_barang_habis_pakai = ($KibA->plus_reklasifikasi_barang_habis_pakai ?? 0) + ($KibB->plus_reklasifikasi_barang_habis_pakai ?? 0) + ($KibC->plus_reklasifikasi_barang_habis_pakai ?? 0) + ($KibD->plus_reklasifikasi_barang_habis_pakai ?? 0) + ($KibE->plus_reklasifikasi_barang_habis_pakai ?? 0) + ($KDP->plus_reklasifikasi_barang_habis_pakai ?? 0);
                    //
                    $getData->plus_reklasifikasi_pemeliharaan = ($KibA->plus_reklasifikasi_pemeliharaan ?? 0) + ($KibB->plus_reklasifikasi_pemeliharaan ?? 0) + ($KibC->plus_reklasifikasi_pemeliharaan ?? 0) + ($KibD->plus_reklasifikasi_pemeliharaan ?? 0) + ($KibE->plus_reklasifikasi_pemeliharaan ?? 0) + ($KDP->plus_reklasifikasi_pemeliharaan ?? 0);
                    //
                    $getData->plus_reklasifikasi_jasa = ($KibA->plus_reklasifikasi_jasa ?? 0) + ($KibB->plus_reklasifikasi_jasa ?? 0) + ($KibC->plus_reklasifikasi_jasa ?? 0) + ($KibD->plus_reklasifikasi_jasa ?? 0) + ($KibE->plus_reklasifikasi_jasa ?? 0) + ($KDP->plus_reklasifikasi_jasa ?? 0);
                    //
                    $getData->plus_reklasifikasi_kib_a = ($KibA->plus_reklasifikasi_kib_a ?? 0) + ($KibB->plus_reklasifikasi_kib_a ?? 0) + ($KibC->plus_reklasifikasi_kib_a ?? 0) + ($KibD->plus_reklasifikasi_kib_a ?? 0) + ($KibE->plus_reklasifikasi_kib_a ?? 0) + ($KDP->plus_reklasifikasi_kib_a ?? 0);
                    //
                    $getData->plus_reklasifikasi_kib_b = ($KibA->plus_reklasifikasi_kib_b ?? 0) + ($KibB->plus_reklasifikasi_kib_b ?? 0) + ($KibC->plus_reklasifikasi_kib_b ?? 0) + ($KibD->plus_reklasifikasi_kib_b ?? 0) + ($KibE->plus_reklasifikasi_kib_b ?? 0) + ($KDP->plus_reklasifikasi_kib_b ?? 0);
                    //
                    $getData->plus_reklasifikasi_kib_c = ($KibA->plus_reklasifikasi_kib_c ?? 0) + ($KibB->plus_reklasifikasi_kib_c ?? 0) + ($KibC->plus_reklasifikasi_kib_c ?? 0) + ($KibD->plus_reklasifikasi_kib_c ?? 0) + ($KibE->plus_reklasifikasi_kib_c ?? 0) + ($KDP->plus_reklasifikasi_kib_c ?? 0);
                    //
                    $getData->plus_reklasifikasi_kib_d = ($KibA->plus_reklasifikasi_kib_d ?? 0) + ($KibB->plus_reklasifikasi_kib_d ?? 0) + ($KibC->plus_reklasifikasi_kib_d ?? 0) + ($KibD->plus_reklasifikasi_kib_d ?? 0) + ($KibE->plus_reklasifikasi_kib_d ?? 0) + ($KDP->plus_reklasifikasi_kib_d ?? 0);
                    //
                    $getData->plus_reklasifikasi_kib_e = ($KibA->plus_reklasifikasi_kib_e ?? 0) + ($KibB->plus_reklasifikasi_kib_e ?? 0) + ($KibC->plus_reklasifikasi_kib_e ?? 0) + ($KibD->plus_reklasifikasi_kib_e ?? 0) + ($KibE->plus_reklasifikasi_kib_e ?? 0) + ($KDP->plus_reklasifikasi_kib_e ?? 0);
                    //
                    $getData->plus_reklasifikasi_kdp = ($KibA->plus_reklasifikasi_kdp ?? 0) + ($KibB->plus_reklasifikasi_kdp ?? 0) + ($KibC->plus_reklasifikasi_kdp ?? 0) + ($KibD->plus_reklasifikasi_kdp ?? 0) + ($KibE->plus_reklasifikasi_kdp ?? 0) + ($KDP->plus_reklasifikasi_kdp ?? 0);
                    //
                    $getData->plus_reklasifikasi_aset_lain_lain = ($KibA->plus_reklasifikasi_aset_lain_lain ?? 0) + ($KibB->plus_reklasifikasi_aset_lain_lain ?? 0) + ($KibC->plus_reklasifikasi_aset_lain_lain ?? 0) + ($KibD->plus_reklasifikasi_aset_lain_lain ?? 0) + ($KibE->plus_reklasifikasi_aset_lain_lain ?? 0) + ($KDP->plus_reklasifikasi_aset_lain_lain ?? 0);
                    //
                    $getData->plus_hibah_masuk = ($KibA->plus_hibah_masuk ?? 0) + ($KibB->plus_hibah_masuk ?? 0) + ($KibC->plus_hibah_masuk ?? 0) + ($KibD->plus_hibah_masuk ?? 0) + ($KibE->plus_hibah_masuk ?? 0) + ($KDP->plus_hibah_masuk ?? 0);
                    //
                    $getData->plus_penilaian = ($KibA->plus_penilaian ?? 0) + ($KibB->plus_penilaian ?? 0) + ($KibC->plus_penilaian ?? 0) + ($KibD->plus_penilaian ?? 0) + ($KibE->plus_penilaian ?? 0) + ($KDP->plus_penilaian ?? 0);
                    //
                    $getData->plus_mutasi_antar_opd = ($KibA->plus_mutasi_antar_opd ?? 0) + ($KibB->plus_mutasi_antar_opd ?? 0) + ($KibC->plus_mutasi_antar_opd ?? 0) + ($KibD->plus_mutasi_antar_opd ?? 0) + ($KibE->plus_mutasi_antar_opd ?? 0) + ($KDP->plus_mutasi_antar_opd ?? 0);
                    //
                    $getData->plus_total = ($getData->plus_realisasi_belanja ?? 0) + ($getData->plus_hutang_kegiatan ?? 0) + ($getData->plus_atribusi ?? 0) + ($getData->plus_reklasifikasi_barang_habis_pakai ?? 0) + ($getData->plus_reklasifikasi_pemeliharaan ?? 0) + ($getData->plus_reklasifikasi_jasa ?? 0) + ($getData->plus_reklasifikasi_kib_a ?? 0) + ($getData->plus_reklasifikasi_kib_b ?? 0) + ($getData->plus_reklasifikasi_kib_c ?? 0) + ($getData->plus_reklasifikasi_kib_d ?? 0) + ($getData->plus_reklasifikasi_kib_e ?? 0) + ($getData->plus_reklasifikasi_kdp ?? 0) + ($getData->plus_reklasifikasi_aset_lain_lain ?? 0) + ($getData->plus_hibah_masuk ?? 0) + ($getData->plus_penilaian ?? 0) + ($getData->plus_mutasi_antar_opd ?? 0);
                    //
                    $getData->min_pembayaran_utang = ($KibA->min_pembayaran_utang ?? 0) + ($KibB->min_pembayaran_utang ?? 0) + ($KibC->min_pembayaran_utang ?? 0) + ($KibD->min_pembayaran_utang ?? 0) + ($KibE->min_pembayaran_utang ?? 0) + ($KDP->min_pembayaran_utang ?? 0);
                    //
                    $getData->min_reklasifikasi_beban_persediaan = ($KibA->min_reklasifikasi_beban_persediaan ?? 0) + ($KibB->min_reklasifikasi_beban_persediaan ?? 0) + ($KibC->min_reklasifikasi_beban_persediaan ?? 0) + ($KibD->min_reklasifikasi_beban_persediaan ?? 0) + ($KibE->min_reklasifikasi_beban_persediaan ?? 0) + ($KDP->min_reklasifikasi_beban_persediaan ?? 0);
                    //
                    $getData->min_reklasifikasi_beban_jasa = ($KibA->min_reklasifikasi_beban_jasa ?? 0) + ($KibB->min_reklasifikasi_beban_jasa ?? 0) + ($KibC->min_reklasifikasi_beban_jasa ?? 0) + ($KibD->min_reklasifikasi_beban_jasa ?? 0) + ($KibE->min_reklasifikasi_beban_jasa ?? 0) + ($KDP->min_reklasifikasi_beban_jasa ?? 0);
                    //
                    $getData->min_reklasifikasi_beban_pemeliharaan = ($KibA->min_reklasifikasi_beban_pemeliharaan ?? 0) + ($KibB->min_reklasifikasi_beban_pemeliharaan ?? 0) + ($KibC->min_reklasifikasi_beban_pemeliharaan ?? 0) + ($KibD->min_reklasifikasi_beban_pemeliharaan ?? 0) + ($KibE->min_reklasifikasi_beban_pemeliharaan ?? 0) + ($KDP->min_reklasifikasi_beban_pemeliharaan ?? 0);
                    //
                    $getData->min_reklasifikasi_beban_hibah = ($KibA->min_reklasifikasi_beban_hibah ?? 0) + ($KibB->min_reklasifikasi_beban_hibah ?? 0) + ($KibC->min_reklasifikasi_beban_hibah ?? 0) + ($KibD->min_reklasifikasi_beban_hibah ?? 0) + ($KibE->min_reklasifikasi_beban_hibah ?? 0) + ($KDP->min_reklasifikasi_beban_hibah ?? 0);
                    //
                    $getData->min_reklasifikasi_beban_kib_a = ($KibA->min_reklasifikasi_beban_kib_a ?? 0) + ($KibB->min_reklasifikasi_beban_kib_a ?? 0) + ($KibC->min_reklasifikasi_beban_kib_a ?? 0) + ($KibD->min_reklasifikasi_beban_kib_a ?? 0) + ($KibE->min_reklasifikasi_beban_kib_a ?? 0) + ($KDP->min_reklasifikasi_beban_kib_a ?? 0);
                    //
                    $getData->min_reklasifikasi_beban_kib_b = ($KibA->min_reklasifikasi_beban_kib_b ?? 0) + ($KibB->min_reklasifikasi_beban_kib_b ?? 0) + ($KibC->min_reklasifikasi_beban_kib_b ?? 0) + ($KibD->min_reklasifikasi_beban_kib_b ?? 0) + ($KibE->min_reklasifikasi_beban_kib_b ?? 0) + ($KDP->min_reklasifikasi_beban_kib_b ?? 0);
                    //
                    $getData->min_reklasifikasi_beban_kib_c = ($KibA->min_reklasifikasi_beban_kib_c ?? 0) + ($KibB->min_reklasifikasi_beban_kib_c ?? 0) + ($KibC->min_reklasifikasi_beban_kib_c ?? 0) + ($KibD->min_reklasifikasi_beban_kib_c ?? 0) + ($KibE->min_reklasifikasi_beban_kib_c ?? 0) + ($KDP->min_reklasifikasi_beban_kib_c ?? 0);
                    //
                    $getData->min_reklasifikasi_beban_kib_d = ($KibA->min_reklasifikasi_beban_kib_d ?? 0) + ($KibB->min_reklasifikasi_beban_kib_d ?? 0) + ($KibC->min_reklasifikasi_beban_kib_d ?? 0) + ($KibD->min_reklasifikasi_beban_kib_d ?? 0) + ($KibE->min_reklasifikasi_beban_kib_d ?? 0) + ($KDP->min_reklasifikasi_beban_kib_d ?? 0);
                    //
                    $getData->min_reklasifikasi_beban_kib_e = ($KibA->min_reklasifikasi_beban_kib_e ?? 0) + ($KibB->min_reklasifikasi_beban_kib_e ?? 0) + ($KibC->min_reklasifikasi_beban_kib_e ?? 0) + ($KibD->min_reklasifikasi_beban_kib_e ?? 0) + ($KibE->min_reklasifikasi_beban_kib_e ?? 0) + ($KDP->min_reklasifikasi_beban_kib_e ?? 0);
                    //
                    $getData->min_reklasifikasi_beban_kdp = ($KibA->min_reklasifikasi_beban_kdp ?? 0) + ($KibB->min_reklasifikasi_beban_kdp ?? 0) + ($KibC->min_reklasifikasi_beban_kdp ?? 0) + ($KibD->min_reklasifikasi_beban_kdp ?? 0) + ($KibE->min_reklasifikasi_beban_kdp ?? 0) + ($KDP->min_reklasifikasi_beban_kdp ?? 0);
                    //
                    $getData->min_reklasifikasi_beban_aset_lain_lain = ($KibA->min_reklasifikasi_beban_aset_lain_lain ?? 0) + ($KibB->min_reklasifikasi_beban_aset_lain_lain ?? 0) + ($KibC->min_reklasifikasi_beban_aset_lain_lain ?? 0) + ($KibD->min_reklasifikasi_beban_aset_lain_lain ?? 0) + ($KibE->min_reklasifikasi_beban_aset_lain_lain ?? 0) + ($KDP->min_reklasifikasi_beban_aset_lain_lain ?? 0);
                    //
                    $getData->min_penghapusan = ($KibA->min_penghapusan ?? 0) + ($KibB->min_penghapusan ?? 0) + ($KibC->min_penghapusan ?? 0) + ($KibD->min_penghapusan ?? 0) + ($KibE->min_penghapusan ?? 0) + ($KDP->min_penghapusan ?? 0);
                    //
                    $getData->min_mutasi_antar_opd = ($KibA->min_mutasi_antar_opd ?? 0) + ($KibB->min_mutasi_antar_opd ?? 0) + ($KibC->min_mutasi_antar_opd ?? 0) + ($KibD->min_mutasi_antar_opd ?? 0) + ($KibE->min_mutasi_antar_opd ?? 0) + ($KDP->min_mutasi_antar_opd ?? 0);
                    //
                    $getData->min_tptgr = ($KibA->min_tptgr ?? 0) + ($KibB->min_tptgr ?? 0) + ($KibC->min_tptgr ?? 0) + ($KibD->min_tptgr ?? 0) + ($KibE->min_tptgr ?? 0) + ($KDP->min_tptgr ?? 0);
                    //
                    $getData->min_total = ($getData->min_pembayaran_utang ?? 0) + ($getData->min_reklasifikasi_beban_persediaan ?? 0) + ($getData->min_reklasifikasi_beban_pemeliharaan ?? 0) + ($getData->min_reklasifikasi_beban_hibah ?? 0) + ($getData->min_reklasifikasi_beban_kib_a ?? 0) + ($getData->min_reklasifikasi_beban_kib_b ?? 0) + ($getData->min_reklasifikasi_beban_kib_c ?? 0) + ($getData->min_reklasifikasi_beban_kib_d ?? 0) + ($getData->min_reklasifikasi_beban_kib_e ?? 0) + ($getData->min_reklasifikasi_beban_kdp ?? 0) + ($getData->min_reklasifikasi_beban_aset_lain_lain ?? 0) + ($getData->min_penghapusan ?? 0) + ($getData->min_mutasi_antar_opd ?? 0) + ($getData->min_tptgr ?? 0);

                    DB::table('acc_rek_as_rekap_aset_tetap')
                        ->where('id', $getData->id)
                        ->update([
                            'saldo_awal' => $getData->saldo_awal,
                            'saldo_akhir' => $getData->saldo_akhir,
                            'plus_realisasi_belanja' => $getData->plus_realisasi_belanja,
                            'plus_hutang_kegiatan' => $getData->plus_hutang_kegiatan,
                            'plus_atribusi' => $getData->plus_atribusi,
                            'plus_reklasifikasi_barang_habis_pakai' => $getData->plus_reklasifikasi_barang_habis_pakai,
                            'plus_reklasifikasi_pemeliharaan' => $getData->plus_reklasifikasi_pemeliharaan,
                            'plus_reklasifikasi_jasa' => $getData->plus_reklasifikasi_jasa,
                            'plus_reklasifikasi_kib_a' => $getData->plus_reklasifikasi_kib_a,
                            'plus_reklasifikasi_kib_b' => $getData->plus_reklasifikasi_kib_b,
                            'plus_reklasifikasi_kib_c' => $getData->plus_reklasifikasi_kib_c,
                            'plus_reklasifikasi_kib_d' => $getData->plus_reklasifikasi_kib_d,
                            'plus_reklasifikasi_kib_e' => $getData->plus_reklasifikasi_kib_e,
                            'plus_reklasifikasi_kdp' => $getData->plus_reklasifikasi_kdp,
                            'plus_reklasifikasi_aset_lain_lain' => $getData->plus_reklasifikasi_aset_lain_lain,
                            'plus_hibah_masuk' => $getData->plus_hibah_masuk,
                            'plus_penilaian' => $getData->plus_penilaian,
                            'plus_mutasi_antar_opd' => $getData->plus_mutasi_antar_opd,
                            // 'plus_total' => $getData->plus_total,
                            'min_pembayaran_utang' => $getData->min_pembayaran_utang,
                            'min_reklasifikasi_beban_persediaan' => $getData->min_reklasifikasi_beban_persediaan,
                            'min_reklasifikasi_beban_jasa' => $getData->min_reklasifikasi_beban_jasa,
                            'min_reklasifikasi_beban_pemeliharaan' => $getData->min_reklasifikasi_beban_pemeliharaan,
                            'min_reklasifikasi_beban_hibah' => $getData->min_reklasifikasi_beban_hibah,
                            'min_reklasifikasi_beban_kib_a' => $getData->min_reklasifikasi_beban_kib_a,
                            'min_reklasifikasi_beban_kib_b' => $getData->min_reklasifikasi_beban_kib_b,
                            'min_reklasifikasi_beban_kib_c' => $getData->min_reklasifikasi_beban_kib_c,
                            'min_reklasifikasi_beban_kib_d' => $getData->min_reklasifikasi_beban_kib_d,
                            'min_reklasifikasi_beban_kib_e' => $getData->min_reklasifikasi_beban_kib_e,
                            'min_reklasifikasi_beban_kdp' => $getData->min_reklasifikasi_beban_kdp,
                            'min_reklasifikasi_beban_aset_lain_lain' => $getData->min_reklasifikasi_beban_aset_lain_lain,
                            'min_penghapusan' => $getData->min_penghapusan,
                            'min_mutasi_antar_opd' => $getData->min_mutasi_antar_opd,
                            'min_tptgr' => $getData->min_tptgr,
                            // 'min_total' => $getData->min_total,
                        ]);


                    $getData = DB::table('acc_rek_as_rekap_aset_tetap')
                        ->where('id', $getData->id)
                        ->first();
                }
                $values[] = [
                    'data_id' => $getData->id,
                    'instance_id' => $instance->id,
                    'instance_name' => $instance->name,
                    'saldo_awal' => $getData->saldo_awal ?? 0,
                    'saldo_akhir' => $getData->saldo_akhir ?? 0,

                    'plus_realisasi_belanja' => $getData->plus_realisasi_belanja ?? 0,
                    'plus_hutang_kegiatan' => $getData->plus_hutang_kegiatan ?? 0,
                    'plus_atribusi' => $getData->plus_atribusi ?? 0,
                    'plus_reklasifikasi_barang_habis_pakai' => $getData->plus_reklasifikasi_barang_habis_pakai ?? 0,
                    'plus_reklasifikasi_pemeliharaan' => $getData->plus_reklasifikasi_pemeliharaan ?? 0,
                    'plus_reklasifikasi_jasa' => $getData->plus_reklasifikasi_jasa ?? 0,
                    'plus_reklasifikasi_kib_a' => $getData->plus_reklasifikasi_kib_a ?? 0,
                    'plus_reklasifikasi_kib_b' => $getData->plus_reklasifikasi_kib_b ?? 0,
                    'plus_reklasifikasi_kib_c' => $getData->plus_reklasifikasi_kib_c ?? 0,
                    'plus_reklasifikasi_kib_d' => $getData->plus_reklasifikasi_kib_d ?? 0,
                    'plus_reklasifikasi_kib_e' => $getData->plus_reklasifikasi_kib_e ?? 0,
                    'plus_reklasifikasi_kdp' => $getData->plus_reklasifikasi_kdp ?? 0,
                    'plus_reklasifikasi_aset_lain_lain' => $getData->plus_reklasifikasi_aset_lain_lain ?? 0,
                    'plus_hibah_masuk' => $getData->plus_hibah_masuk ?? 0,
                    'plus_penilaian' => $getData->plus_penilaian ?? 0,
                    'plus_mutasi_antar_opd' => $getData->plus_mutasi_antar_opd ?? 0,
                    'plus_total' => $getData->plus_total ?? 0,

                    'min_pembayaran_utang' => $getData->min_pembayaran_utang ?? 0,
                    'min_reklasifikasi_beban_persediaan' => $getData->min_reklasifikasi_beban_persediaan ?? 0,
                    'min_reklasifikasi_beban_jasa' => $getData->min_reklasifikasi_beban_jasa ?? 0,
                    'min_reklasifikasi_beban_pemeliharaan' => $getData->min_reklasifikasi_beban_pemeliharaan ?? 0,
                    'min_reklasifikasi_beban_hibah' => $getData->min_reklasifikasi_beban_hibah ?? 0,
                    'min_reklasifikasi_beban_kib_a' => $getData->min_reklasifikasi_beban_kib_a ?? 0,
                    'min_reklasifikasi_beban_kib_b' => $getData->min_reklasifikasi_beban_kib_b ?? 0,
                    'min_reklasifikasi_beban_kib_c' => $getData->min_reklasifikasi_beban_kib_c ?? 0,
                    'min_reklasifikasi_beban_kib_d' => $getData->min_reklasifikasi_beban_kib_d ?? 0,
                    'min_reklasifikasi_beban_kib_e' => $getData->min_reklasifikasi_beban_kib_e ?? 0,
                    'min_reklasifikasi_beban_kdp' => $getData->min_reklasifikasi_beban_kdp ?? 0,
                    'min_reklasifikasi_beban_aset_lain_lain' => $getData->min_reklasifikasi_beban_aset_lain_lain ?? 0,
                    'min_penghapusan' => $getData->min_penghapusan ?? 0,
                    'min_mutasi_antar_opd' => $getData->min_mutasi_antar_opd ?? 0,
                    'min_tptgr' => $getData->min_tptgr ?? 0,
                    'min_total' => $getData->min_total ?? 0,

                ];
            }

            $datas['instances'] = $instances;
            $datas['datas'] = $values;

            return $this->successResponse($datas, 'Penyesuai Aset dan Beban berhasil diambil');
        } catch (\Exception $e) {
            return $this->errorResponse($e->getMessage() . ' ' . $e->getLine());
        }
    }
    // getRekapAsetTetap End

    // getRekapOPD Start
    function getRekapOPD(Request $request)
    {
        $validate = Validator::make($request->all(), [
            'instance' => 'nullable|exists:instances,id',
            'year' => 'required|integer',
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
            $instances = [];
            if (!$request->instance) {
                $instances = Instance::orderBy('name')->get();
            } elseif ($request->instance) {
                $instances = Instance::where('id', $request->instance)->get();
            }

            $values = [];
            foreach ($instances as $instance) {
                $getData = DB::table('acc_rek_as_rekap_opd')
                    ->where('instance_id', $instance->id)
                    ->where('year', $request->year)
                    ->where('periode_id', $request->periode)
                    ->first();

                $KibA = DB::table('acc_rek_as_kib_a')
                    ->where('instance_id', $instance->id)
                    ->where('year', $request->year)
                    ->where('periode_id', $request->periode)
                    ->first();
                $KibB = DB::table('acc_rek_as_kib_b')
                    ->where('instance_id', $instance->id)
                    ->where('year', $request->year)
                    ->where('periode_id', $request->periode)
                    ->first();
                $KibC = DB::table('acc_rek_as_kib_c')
                    ->where('instance_id', $instance->id)
                    ->where('year', $request->year)
                    ->where('periode_id', $request->periode)
                    ->first();
                $KibD = DB::table('acc_rek_as_kib_d')
                    ->where('instance_id', $instance->id)
                    ->where('year', $request->year)
                    ->where('periode_id', $request->periode)
                    ->first();
                $KibE = DB::table('acc_rek_as_kib_e')
                    ->where('instance_id', $instance->id)
                    ->where('year', $request->year)
                    ->where('periode_id', $request->periode)
                    ->first();
                $KDP = DB::table('acc_rek_as_kdp')
                    ->where('instance_id', $instance->id)
                    ->where('year', $request->year)
                    ->where('periode_id', $request->periode)
                    ->first();
                $AsetLainLain = DB::table('acc_rek_as_aset_lain_lain')
                    ->where('instance_id', $instance->id)
                    ->where('year', $request->year)
                    ->where('periode_id', $request->periode)
                    ->first();

                if (!$getData) {
                    DB::table('acc_rek_as_rekap_opd')->insert([
                        'periode_id' => $request->periode,
                        'year' => $request->year,
                        'instance_id' => $instance->id,
                    ]);

                    $getData = DB::table('acc_rek_as_rekap_opd')
                        ->where('instance_id', $instance->id)
                        ->where('year', $request->year)
                        ->where('periode_id', $request->periode)
                        ->first();
                }

                if ($getData) {
                    $getData->tanah_last_year = $KibA->saldo_awal ?? 0;
                    $getData->tanah = $KibA->saldo_akhir ?? 0;

                    $getData->peralatan_mesin_last_year = $KibB->saldo_awal ?? 0;
                    $getData->peralatan_mesin = $KibB->saldo_akhir ?? 0;

                    $getData->gedung_bangunan_last_year = $KibC->saldo_awal ?? 0;
                    $getData->gedung_bangunan = $KibC->saldo_akhir ?? 0;

                    $getData->jalan_jaringan_irigasi_last_year = $KibD->saldo_awal ?? 0;
                    $getData->jalan_jaringan_irigasi = $KibD->saldo_akhir ?? 0;

                    $getData->aset_tetap_lainnya_last_year = $KibE->saldo_awal ?? 0;
                    $getData->aset_tetap_lainnya = $KibE->saldo_akhir ?? 0;

                    $getData->kdp_last_year = $KDP->saldo_awal ?? 0;
                    $getData->kdp = $KDP->saldo_akhir ?? 0;

                    $getData->aset_lainnya_last_year = $AsetLainLain->saldo_awal ?? 0;
                    $getData->aset_lainnya = $AsetLainLain->saldo_akhir ?? 0;
                }

                DB::table('acc_rek_as_rekap_opd')
                    ->where('id', $getData->id)
                    ->update([
                        'tanah_last_year' => $getData->tanah_last_year,
                        'tanah' => $getData->tanah,

                        'peralatan_mesin_last_year' => $getData->peralatan_mesin_last_year,
                        'peralatan_mesin' => $getData->peralatan_mesin,

                        'gedung_bangunan_last_year' => $getData->gedung_bangunan_last_year,
                        'gedung_bangunan' => $getData->gedung_bangunan,

                        'jalan_jaringan_irigasi_last_year' => $getData->jalan_jaringan_irigasi_last_year,
                        'jalan_jaringan_irigasi' => $getData->jalan_jaringan_irigasi,

                        'aset_tetap_lainnya_last_year' => $getData->aset_tetap_lainnya_last_year,
                        'aset_tetap_lainnya' => $getData->aset_tetap_lainnya,

                        'kdp_last_year' => $getData->kdp_last_year,
                        'kdp' => $getData->kdp,

                        'aset_lainnya_last_year' => $getData->aset_lainnya_last_year,
                        'aset_lainnya' => $getData->aset_lainnya,
                    ]);
                $values[] = [
                    'data_id' => $getData->id,
                    'instance_id' => $instance->id,
                    'instance_name' => $instance->name,

                    'tanah' => $getData->tanah ?? 0,
                    'tanah_last_year' => $getData->tanah_last_year ?? 0,

                    'peralatan_mesin' => $getData->peralatan_mesin ?? 0,
                    'peralatan_mesin_last_year' => $getData->peralatan_mesin_last_year ?? 0,

                    'gedung_bangunan' => $getData->gedung_bangunan ?? 0,
                    'gedung_bangunan_last_year' => $getData->gedung_bangunan_last_year ?? 0,

                    'jalan_jaringan_irigasi' => $getData->jalan_jaringan_irigasi ?? 0,
                    'jalan_jaringan_irigasi_last_year' => $getData->jalan_jaringan_irigasi_last_year ?? 0,

                    'aset_tetap_lainnya' => $getData->aset_tetap_lainnya ?? 0,
                    'aset_tetap_lainnya_last_year' => $getData->aset_tetap_lainnya_last_year ?? 0,

                    'kdp' => $getData->kdp ?? 0,
                    'kdp_last_year' => $getData->kdp_last_year ?? 0,

                    'aset_lainnya' => $getData->aset_lainnya ?? 0,
                    'aset_lainnya_last_year' => $getData->aset_lainnya_last_year ?? 0,
                ];
            }

            $datas['instances'] = $instances;
            $datas['datas'] = $values;

            return $this->successResponse($datas, 'Penyesuai Aset dan Beban berhasil diambil');
        } catch (\Exception $e) {
            return $this->errorResponse($e->getMessage() . ' ' . $e->getLine());
        }
    }
    // getRekapOPD End

    // getRekap Start
    function getRekap(Request $request)
    {
        $validate = Validator::make($request->all(), [
            'instance' => 'nullable|exists:instances,id',
            'year' => 'required|integer',
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
            $instances = [];
            if (!$request->instance) {
                // $instanceIds = DB::table('acc_lra')
                //     ->select('instance_id')
                //     ->groupBy('instance_id')
                //     ->get();
                // $instances = Instance::whereIn('id', $instanceIds->pluck('instance_id'))->get();
                $instances = Instance::orderBy('name')->get();
            } elseif ($request->instance) {
                $instances = Instance::where('id', $request->instance)->get();
            }

            $values = [];
            $arrUraian = [
                'Tanah',
                'Peralatan dan Mesin',
                'Gedung dan Bangunan',
                'Jalan, Irigasi dan Jaringan',
                'Aset Tetap Lainnya',
                'Konstruksi Dalam Pekerjaan',
                'Akumulasi Penyusutan',
            ];

            // Tanah Start
            $SaldoAwal = DB::table('acc_rek_as_kib_a')
                ->where('year', $request->year)
                ->where('periode_id', $request->periode)
                ->whereIn('instance_id', $instances->pluck('id'))
                ->sum('saldo_awal');
            $MutasiTambah = DB::table('acc_rek_as_kib_a')
                ->where('year', $request->year)
                ->where('periode_id', $request->periode)
                ->whereIn('instance_id', $instances->pluck('id'))
                ->sum(DB::raw('plus_realisasi_belanja + plus_hutang_kegiatan + plus_atribusi + plus_reklasifikasi_barang_habis_pakai + plus_reklasifikasi_pemeliharaan + plus_reklasifikasi_jasa + plus_reklasifikasi_kib_a + plus_reklasifikasi_kib_b + plus_reklasifikasi_kib_c + plus_reklasifikasi_kib_d + plus_reklasifikasi_kib_e + plus_reklasifikasi_kdp + plus_reklasifikasi_aset_lain_lain + plus_hibah_masuk + plus_penilaian + plus_mutasi_antar_opd'));
            $MutasiKurang = DB::table('acc_rek_as_kib_a')
                ->where('year', $request->year)
                ->where('periode_id', $request->periode)
                ->whereIn('instance_id', $instances->pluck('id'))
                ->sum(DB::raw('min_pembayaran_utang + min_reklasifikasi_beban_persediaan + min_reklasifikasi_beban_pemeliharaan + min_reklasifikasi_beban_jasa + min_reklasifikasi_beban_hibah + min_reklasifikasi_beban_kib_a + min_reklasifikasi_beban_kib_b + min_reklasifikasi_beban_kib_c + min_reklasifikasi_beban_kib_d + min_reklasifikasi_beban_kib_e + min_reklasifikasi_beban_kdp + min_reklasifikasi_beban_aset_lain_lain + min_penghapusan + min_mutasi_antar_opd + min_tptgr'));
            $SaldoAkhir = DB::table('acc_rek_as_kib_a')
                ->where('year', $request->year)
                ->where('periode_id', $request->periode)
                ->whereIn('instance_id', $instances->pluck('id'))
                ->sum('saldo_akhir');
            $SaldoAkhir = $SaldoAwal + $MutasiTambah - $MutasiKurang;
            $values[] = [
                'uraian' => 'Tanah',
                'saldo_awal' => $SaldoAwal ?? 0,
                'mutasi_tambah' => $MutasiTambah ?? 0,
                'mutasi_kurang' => $MutasiKurang ?? 0,
                'saldo_akhir' => $SaldoAkhir ?? 0,
            ];
            // Tanah End

            // Peralatan dan Mesin Start
            $SaldoAwal = DB::table('acc_rek_as_kib_b')
                ->where('year', $request->year)
                ->where('periode_id', $request->periode)
                ->whereIn('instance_id', $instances->pluck('id'))
                ->sum('saldo_awal');
            $MutasiTambah = DB::table('acc_rek_as_kib_b')
                ->where('year', $request->year)
                ->where('periode_id', $request->periode)
                ->whereIn('instance_id', $instances->pluck('id'))
                ->sum(DB::raw('plus_realisasi_belanja + plus_hutang_kegiatan + plus_atribusi + plus_reklasifikasi_barang_habis_pakai + plus_reklasifikasi_pemeliharaan + plus_reklasifikasi_jasa + plus_reklasifikasi_kib_a + plus_reklasifikasi_kib_b + plus_reklasifikasi_kib_c + plus_reklasifikasi_kib_d + plus_reklasifikasi_kib_e + plus_reklasifikasi_kdp + plus_reklasifikasi_aset_lain_lain + plus_hibah_masuk + plus_penilaian + plus_mutasi_antar_opd'));
            $MutasiKurang = DB::table('acc_rek_as_kib_b')
                ->where('year', $request->year)
                ->where('periode_id', $request->periode)
                ->whereIn('instance_id', $instances->pluck('id'))
                ->sum(DB::raw('min_pembayaran_utang + min_reklasifikasi_beban_persediaan + min_reklasifikasi_beban_jasa + min_reklasifikasi_beban_pemeliharaan + min_reklasifikasi_beban_hibah + min_reklasifikasi_beban_kib_a + min_reklasifikasi_beban_kib_b + min_reklasifikasi_beban_kib_c + min_reklasifikasi_beban_kib_d + min_reklasifikasi_beban_kib_e + min_reklasifikasi_beban_kdp + min_reklasifikasi_beban_aset_lain_lain + min_penghapusan + min_mutasi_antar_opd + min_tptgr'));
            $SaldoAkhir = DB::table('acc_rek_as_kib_b')
                ->where('year', $request->year)
                ->where('periode_id', $request->periode)
                ->whereIn('instance_id', $instances->pluck('id'))
                ->sum('saldo_akhir');
            $SaldoAkhir = $SaldoAwal + $MutasiTambah - $MutasiKurang;
            $values[] = [
                'uraian' => 'Peralatan dan Mesin',
                'saldo_awal' => $SaldoAwal ?? 0,
                'mutasi_tambah' => $MutasiTambah ?? 0,
                'mutasi_kurang' => $MutasiKurang ?? 0,
                'saldo_akhir' => $SaldoAkhir ?? 0,
            ];
            // Peralatan dan Mesin Start

            // Gedung dan Bangunan Start
            $SaldoAwal = DB::table('acc_rek_as_kib_c')
                ->where('year', $request->year)
                ->where('periode_id', $request->periode)
                ->whereIn('instance_id', $instances->pluck('id'))
                ->sum('saldo_awal');
            $MutasiTambah = DB::table('acc_rek_as_kib_c')
                ->where('year', $request->year)
                ->where('periode_id', $request->periode)
                ->whereIn('instance_id', $instances->pluck('id'))
                ->sum(DB::raw('plus_realisasi_belanja + plus_hutang_kegiatan + plus_atribusi + plus_reklasifikasi_barang_habis_pakai + plus_reklasifikasi_pemeliharaan + plus_reklasifikasi_jasa + plus_reklasifikasi_kib_a + plus_reklasifikasi_kib_b + plus_reklasifikasi_kib_c + plus_reklasifikasi_kib_d + plus_reklasifikasi_kib_e + plus_reklasifikasi_kdp + plus_reklasifikasi_aset_lain_lain + plus_hibah_masuk + plus_penilaian + plus_mutasi_antar_opd'));
            $MutasiKurang = DB::table('acc_rek_as_kib_c')
                ->where('year', $request->year)
                ->where('periode_id', $request->periode)
                ->whereIn('instance_id', $instances->pluck('id'))
                ->sum(DB::raw('min_pembayaran_utang + min_reklasifikasi_beban_persediaan + min_reklasifikasi_beban_jasa + min_reklasifikasi_beban_pemeliharaan + min_reklasifikasi_beban_hibah + min_reklasifikasi_beban_kib_a + min_reklasifikasi_beban_kib_b + min_reklasifikasi_beban_kib_c + min_reklasifikasi_beban_kib_d + min_reklasifikasi_beban_kib_e + min_reklasifikasi_beban_kdp + min_reklasifikasi_beban_aset_lain_lain + min_penghapusan + min_mutasi_antar_opd + min_tptgr'));
            $SaldoAkhir = DB::table('acc_rek_as_kib_c')
                ->where('year', $request->year)
                ->where('periode_id', $request->periode)
                ->whereIn('instance_id', $instances->pluck('id'))
                ->sum('saldo_akhir');
            $SaldoAkhir = $SaldoAwal + $MutasiTambah - $MutasiKurang;
            $values[] = [
                'uraian' => 'Gedung dan Bangunan',
                'saldo_awal' => $SaldoAwal ?? 0,
                'mutasi_tambah' => $MutasiTambah ?? 0,
                'mutasi_kurang' => $MutasiKurang ?? 0,
                'saldo_akhir' => $SaldoAkhir ?? 0,
            ];
            // Gedung dan Bangunan Start

            // Jalan Irigasi dan Jaringan Start
            $SaldoAwal = DB::table('acc_rek_as_kib_d')
                ->where('year', $request->year)
                ->where('periode_id', $request->periode)
                ->whereIn('instance_id', $instances->pluck('id'))
                ->sum('saldo_awal');
            $MutasiTambah = DB::table('acc_rek_as_kib_d')
                ->where('year', $request->year)
                ->where('periode_id', $request->periode)
                ->whereIn('instance_id', $instances->pluck('id'))
                ->sum(DB::raw('plus_realisasi_belanja + plus_hutang_kegiatan + plus_atribusi + plus_reklasifikasi_barang_habis_pakai + plus_reklasifikasi_pemeliharaan + plus_reklasifikasi_jasa + plus_reklasifikasi_kib_a + plus_reklasifikasi_kib_b + plus_reklasifikasi_kib_c + plus_reklasifikasi_kib_d + plus_reklasifikasi_kib_e + plus_reklasifikasi_kdp + plus_reklasifikasi_aset_lain_lain + plus_hibah_masuk + plus_penilaian + plus_mutasi_antar_opd'));
            $MutasiKurang = DB::table('acc_rek_as_kib_d')
                ->where('year', $request->year)
                ->where('periode_id', $request->periode)
                ->whereIn('instance_id', $instances->pluck('id'))
                ->sum(DB::raw('min_pembayaran_utang + min_reklasifikasi_beban_persediaan + min_reklasifikasi_beban_jasa + min_reklasifikasi_beban_pemeliharaan + min_reklasifikasi_beban_hibah + min_reklasifikasi_beban_kib_a + min_reklasifikasi_beban_kib_b + min_reklasifikasi_beban_kib_c + min_reklasifikasi_beban_kib_d + min_reklasifikasi_beban_kib_e + min_reklasifikasi_beban_kdp + min_reklasifikasi_beban_aset_lain_lain + min_penghapusan + min_mutasi_antar_opd + min_tptgr'));
            $SaldoAkhir = DB::table('acc_rek_as_kib_d')
                ->where('year', $request->year)
                ->where('periode_id', $request->periode)
                ->whereIn('instance_id', $instances->pluck('id'))
                ->sum('saldo_akhir');
            $SaldoAkhir = $SaldoAwal + $MutasiTambah - $MutasiKurang;
            $values[] = [
                'uraian' => 'Jalan Irigasi dan Jaringan',
                'saldo_awal' => $SaldoAwal ?? 0,
                'mutasi_tambah' => $MutasiTambah ?? 0,
                'mutasi_kurang' => $MutasiKurang ?? 0,
                'saldo_akhir' => $SaldoAkhir ?? 0,
            ];
            // Jalan Irigasi dan Jaringan Start

            // Aset Tetap Lainnya Start
            $SaldoAwal = DB::table('acc_rek_as_kib_e')
                ->where('year', $request->year)
                ->where('periode_id', $request->periode)
                ->whereIn('instance_id', $instances->pluck('id'))
                ->sum('saldo_awal');
            $MutasiTambah = DB::table('acc_rek_as_kib_e')
                ->where('year', $request->year)
                ->where('periode_id', $request->periode)
                ->whereIn('instance_id', $instances->pluck('id'))
                ->sum(DB::raw('plus_realisasi_belanja + plus_hutang_kegiatan + plus_atribusi + plus_reklasifikasi_barang_habis_pakai + plus_reklasifikasi_pemeliharaan + plus_reklasifikasi_jasa + plus_reklasifikasi_kib_a + plus_reklasifikasi_kib_b + plus_reklasifikasi_kib_c + plus_reklasifikasi_kib_d + plus_reklasifikasi_kib_e + plus_reklasifikasi_kdp + plus_reklasifikasi_aset_lain_lain + plus_hibah_masuk + plus_penilaian + plus_mutasi_antar_opd'));
            $MutasiKurang = DB::table('acc_rek_as_kib_e')
                ->where('year', $request->year)
                ->where('periode_id', $request->periode)
                ->whereIn('instance_id', $instances->pluck('id'))
                ->sum(DB::raw('min_pembayaran_utang + min_reklasifikasi_beban_persediaan + min_reklasifikasi_beban_jasa + min_reklasifikasi_beban_pemeliharaan + min_reklasifikasi_beban_hibah + min_reklasifikasi_beban_kib_a + min_reklasifikasi_beban_kib_b + min_reklasifikasi_beban_kib_c + min_reklasifikasi_beban_kib_d + min_reklasifikasi_beban_kib_e + min_reklasifikasi_beban_kdp + min_reklasifikasi_beban_aset_lain_lain + min_penghapusan + min_mutasi_antar_opd + min_tptgr'));
            $SaldoAkhir = DB::table('acc_rek_as_kib_e')
                ->where('year', $request->year)
                ->where('periode_id', $request->periode)
                ->whereIn('instance_id', $instances->pluck('id'))
                ->sum('saldo_akhir');
            $SaldoAkhir = $SaldoAwal + $MutasiTambah - $MutasiKurang;
            $values[] = [
                'uraian' => 'Aset Tetap Lainnya',
                'saldo_awal' => $SaldoAwal ?? 0,
                'mutasi_tambah' => $MutasiTambah ?? 0,
                'mutasi_kurang' => $MutasiKurang ?? 0,
                'saldo_akhir' => $SaldoAkhir ?? 0,
            ];
            // Aset Tetap Lainnya Start

            // Konstruksi Dalam Pekerjaan Start
            $SaldoAwal = DB::table('acc_rek_as_kdp')
                ->where('year', $request->year)
                ->where('periode_id', $request->periode)
                ->whereIn('instance_id', $instances->pluck('id'))
                ->sum('saldo_awal');
            $MutasiTambah = DB::table('acc_rek_as_kdp')
                ->where('year', $request->year)
                ->where('periode_id', $request->periode)
                ->whereIn('instance_id', $instances->pluck('id'))
                ->sum(DB::raw('plus_realisasi_belanja + plus_hutang_kegiatan + plus_atribusi + plus_reklasifikasi_barang_habis_pakai + plus_reklasifikasi_pemeliharaan + plus_reklasifikasi_jasa + plus_reklasifikasi_kib_a + plus_reklasifikasi_kib_b + plus_reklasifikasi_kib_c + plus_reklasifikasi_kib_d + plus_reklasifikasi_kib_e + plus_reklasifikasi_kdp + plus_reklasifikasi_aset_lain_lain + plus_hibah_masuk + plus_penilaian + plus_mutasi_antar_opd'));
            $MutasiKurang = DB::table('acc_rek_as_kdp')
                ->where('year', $request->year)
                ->where('periode_id', $request->periode)
                ->whereIn('instance_id', $instances->pluck('id'))
                ->sum(DB::raw('min_pembayaran_utang + min_reklasifikasi_beban_persediaan + min_reklasifikasi_beban_jasa + min_reklasifikasi_beban_pemeliharaan + min_reklasifikasi_beban_hibah + min_reklasifikasi_beban_kib_a + min_reklasifikasi_beban_kib_b + min_reklasifikasi_beban_kib_c + min_reklasifikasi_beban_kib_d + min_reklasifikasi_beban_kib_e + min_reklasifikasi_beban_kdp + min_reklasifikasi_beban_aset_lain_lain + min_penghapusan + min_mutasi_antar_opd + min_tptgr'));
            $SaldoAkhir = DB::table('acc_rek_as_kdp')
                ->where('year', $request->year)
                ->where('periode_id', $request->periode)
                ->whereIn('instance_id', $instances->pluck('id'))
                ->sum('saldo_akhir');
            $SaldoAkhir = $SaldoAwal + $MutasiTambah - $MutasiKurang;
            $values[] = [
                'uraian' => 'Konstruksi Dalam Pekerjaan',
                'saldo_awal' => $SaldoAwal ?? 0,
                'mutasi_tambah' => $MutasiTambah ?? 0,
                'mutasi_kurang' => $MutasiKurang ?? 0,
                'saldo_akhir' => $SaldoAkhir ?? 0,
            ];
            // Konstruksi Dalam Pekerjaan Start

            // Akumulasi Penyusutan Start
            $SaldoAwal = DB::table('acc_rek_as_penyusutan')
                ->where('year', $request->year)
                ->where('periode_id', $request->periode)
                ->whereIn('instance_id', $instances->pluck('id'))
                ->sum('akumulasi_penyusutan_last_year');
            $MutasiTambah = DB::table('acc_rek_as_penyusutan')
                ->where('year', $request->year)
                ->where('periode_id', $request->periode)
                ->whereIn('instance_id', $instances->pluck('id'))
                ->sum('mutasi_tambah');
            $MutasiKurang = DB::table('acc_rek_as_penyusutan')
                ->where('year', $request->year)
                ->where('periode_id', $request->periode)
                ->whereIn('instance_id', $instances->pluck('id'))
                ->sum('mutasi_kurang');
            $SaldoAkhir = DB::table('acc_rek_as_penyusutan')
                ->where('year', $request->year)
                ->where('periode_id', $request->periode)
                ->whereIn('instance_id', $instances->pluck('id'))
                ->sum('akumulasi_penyusutan');
            $SaldoAkhir = $SaldoAwal + $MutasiTambah - $MutasiKurang;
            $values[] = [
                'uraian' => 'Akumulasi Penyusutan',
                'saldo_awal' => $SaldoAwal ?? 0,
                'mutasi_tambah' => $MutasiTambah ?? 0,
                'mutasi_kurang' => $MutasiKurang ?? 0,
                'saldo_akhir' => $SaldoAkhir ?? 0,
            ];
            // Akumulasi Penyusutan Start

            $grandTotal = [
                'saldo_awal' => collect($values)->sum('saldo_awal'),
                'mutasi_tambah' => collect($values)->sum('mutasi_tambah'),
                'mutasi_kurang' => collect($values)->sum('mutasi_kurang'),
                'saldo_akhir' => collect($values)->sum('saldo_akhir'),
            ];

            $datas['instances'] = $instances;
            $datas['datas'] = $values;
            $datas['grand_total'] = $grandTotal;

            return $this->successResponse($datas, 'Penyesuai Aset dan Beban berhasil diambil');
        } catch (\Exception $e) {
            return $this->errorResponse($e->getMessage() . ' ' . $e->getLine());
        }
    }
    // getRekap End

    // getPenyusutan Start
    function getPenyusutan(Request $request)
    {
        $validate = Validator::make($request->all(), [
            'instance' => 'nullable|exists:instances,id',
            'year' => 'required|integer',
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
            $instances = [];
            if (!$request->instance) {
                // $instanceIds = DB::table('acc_lra')
                //     ->select('instance_id')
                //     ->groupBy('instance_id')
                //     ->get();
                // $instances = Instance::whereIn('id', $instanceIds->pluck('instance_id'))->get();
                $instances = Instance::orderBy('name')->get();
            } elseif ($request->instance) {
                $instances = Instance::where('id', $request->instance)->get();
            }

            $auth = auth()->user();
            $now = now();

            $values = [];
            $arrUraian = [
                [
                    'name' => 'Tanah',
                    'type' => 'tanah',
                ],
                [
                    'name' => 'Peralatan dan Mesin',
                    'type' => 'peralatan_mesin',
                ],
                [
                    'name' => 'Gedung dan Bangunan',
                    'type' => 'gedung_bangunan',
                ],
                [
                    'name' => 'Jalan, Irigasi dan Jaringan',
                    'type' => 'jalan_jaringan_irigasi',
                ],
                [
                    'name' => 'Aset Tetap Lainnya',
                    'type' => 'aset_tetap_lainnya',
                ],
                [
                    'name' => 'Konstruksi Dalam Pekerjaan',
                    'type' => 'kdp',
                ],
            ];

            foreach ($arrUraian as $uraian) {
                if ($request->instance) {
                    $getData = DB::table('acc_rek_as_penyusutan')
                        ->where('instance_id', $request->instance)
                        ->where('year', $request->year)
                        ->where('periode_id', $request->periode)
                        ->where('type', $uraian['type'])
                        ->first();
                    if (!$getData) {
                        DB::table('acc_rek_as_penyusutan')->insert([
                            'periode_id' => $request->periode,
                            'year' => $request->year,
                            'instance_id' => $request->instance,
                            'type' => $uraian['type'],
                            'nama_aset' => $uraian['name'],
                            'akumulasi_penyusutan' => 0,
                            'akumulasi_penyusutan_last_year' => 0,
                            'mutasi_tambah' => 0,
                            'mutasi_kurang' => 0,
                            'created_by' => $auth->id,
                            'created_at' => $now,
                        ]);

                        $getData = DB::table('acc_rek_as_penyusutan')
                            ->where('instance_id', $request->instance)
                            ->where('year', $request->year)
                            ->where('periode_id', $request->periode)
                            ->where('type', $uraian['type'])
                            ->first();
                    }
                    $values[] = [
                        'data_id' => $getData->id,
                        'instance_id' => $request->instance,
                        'instance_name' => $instances->where('id', $request->instance)->first()->name ?? '',
                        'uraian' => $uraian['name'],
                        'type' => $uraian['type'],
                        'akumulasi_penyusutan' => $getData->akumulasi_penyusutan ?? 0,
                        'akumulasi_penyusutan_last_year' => $getData->akumulasi_penyusutan_last_year ?? 0,
                        'mutasi_tambah' => $getData->mutasi_tambah ?? 0,
                        'mutasi_kurang' => $getData->mutasi_kurang ?? 0,
                        'created_by' => User::where('id', $getData->created_by)->first()->fullname ?? '',
                        'created_at' => $getData->created_at,
                        'updated_by' => User::where('id', $getData->updated_by)->first()->fullname ?? '',
                        'updated_at' => $getData->updated_at,
                    ];
                } else {
                    $allData = DB::table('acc_rek_as_penyusutan')
                        ->where('year', $request->year)
                        ->where('periode_id', $request->periode)
                        ->where('type', $uraian['type'])
                        ->get();

                    $values[] = [
                        'data_id' => '',
                        'instance_id' => null,
                        'instance_name' => '',
                        'uraian' => $uraian['name'],
                        'type' => $uraian['type'],
                        'akumulasi_penyusutan' => $allData->sum('akumulasi_penyusutan') ?? 0,
                        'akumulasi_penyusutan_last_year' => $allData->sum('akumulasi_penyusutan_last_year') ?? 0,
                        'mutasi_tambah' => $allData->sum('mutasi_tambah') ?? 0,
                        'mutasi_kurang' => $allData->sum('mutasi_kurang') ?? 0,
                        'created_by' => '',
                        'created_at' => '',
                        'updated_by' => '',
                        'updated_at' => '',
                    ];
                }
            }

            $datas['instances'] = $instances;
            $datas['datas'] = $values;

            return $this->successResponse($datas, 'Penyesuai Aset dan Beban berhasil diambil');
        } catch (\Exception $e) {
            return $this->errorResponse($e->getMessage() . ' ' . $e->getLine());
        }
    }

    function savePenyusutan(Request $request)
    {
        $validate = Validator::make($request->all(), [
            'instance' => 'required|exists:instances,id',
            'year' => 'required|integer',
            'periode' => 'required|exists:ref_periode,id'
        ], [], [
            'instance' => 'Instance ID',
            'periode' => 'Periode'
        ]);
        if ($validate->fails()) {
            return $this->validationResponse($validate->errors());
        }

        DB::beginTransaction();
        try {
            $inputs = $request->data;
            foreach ($inputs as $input) {
                $getId = DB::table('acc_rek_as_penyusutan')
                    ->where('id', $input['data_id'])
                    ->update([
                        'akumulasi_penyusutan' => $input['akumulasi_penyusutan'],
                        'akumulasi_penyusutan_last_year' => $input['akumulasi_penyusutan_last_year'],
                        'mutasi_tambah' => $input['mutasi_tambah'],
                        'mutasi_kurang' => $input['mutasi_kurang'],
                    ]);
            }

            DB::commit();
            return $this->successResponse($inputs, 'Penyesuai Aset dan Beban berhasil disimpan');
        } catch (\Exception $e) {
            DB::rollBack();
            return $this->errorResponse($e->getMessage() . ' ' . $e->getLine());
        }
    }
    // getPenyusutan End
}
