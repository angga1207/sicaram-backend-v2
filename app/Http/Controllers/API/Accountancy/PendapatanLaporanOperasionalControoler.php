<?php

namespace App\Http\Controllers\API\Accountancy;

use App\Traits\JsonReturner;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Validator;

class PendapatanLaporanOperasionalControoler extends Controller
{
    use JsonReturner;

    function getRekap(Request $request)
    {
        $validate = Validator::make($request->all(), [
            'periode' => 'required|exists:ref_periode,id',
            'year' => 'required|numeric',
        ], [], [
            'periode' => 'Periode',
        ]);
        if ($validate->fails()) {
            return $this->validationResponse($validate->errors());
        }
        $datas = [];
        $arrUraian = [
            'Pajak Daerah',
            'Hasil Retribusi Daerah',
            'Hasil Pengelolaan Kekayaan Daerah',
            'Lain-lain PAD yang Sah',
            'Transfer Pemerintah Pusat',
            'DBH Pajak Daerah',
            // 'Bantuan Keuangan Provinsi',
        ];

        foreach ($arrUraian as $key => $uraian) {
            // pendapatan_pajak_daerah
            $saldoAwal = DB::table('acc_plo_piutang')
                ->where('periode_id', $request->periode)
                ->where('year', $request->year)
                ->when($request->instance, function ($query) use ($request) {
                    return $query->where('instance_id', $request->instance);
                })
                ->where(function ($query) use ($key) {
                    if ($key == 0) {
                        return $query->where('type', 'pendapatan_pajak_daerah');
                    } else if ($key == 1) {
                        return $query->where('type', 'hasil_retribusi_daerah');
                    } else if ($key == 2) {
                        return $query->where('type', 'hasil_pengelolaan_kekayaan_daerah_yang_dipisahkan');
                    } else if ($key == 3) {
                        return $query->where('type', 'lain_lain_pad_yang_sah');
                    } else if ($key == 4) {
                        return $query->where('type', 'transfer_pemerintah_pusat');
                    } else if ($key == 5) {
                        return $query->where('type', 'transfer_antar_daerah');
                    }
                })
                ->sum('saldo_awal');
            $saldoAkhir = DB::table('acc_plo_piutang')
                ->where('periode_id', $request->periode)
                ->where('year', $request->year)
                ->when($request->instance, function ($query) use ($request) {
                    return $query->where('instance_id', $request->instance);
                })
                ->where(function ($query) use ($key) {
                    if ($key == 0) {
                        return $query->where('type', 'pendapatan_pajak_daerah');
                    } else if ($key == 1) {
                        return $query->where('type', 'hasil_retribusi_daerah');
                    } else if ($key == 2) {
                        return $query->where('type', 'hasil_pengelolaan_kekayaan_daerah_yang_dipisahkan');
                    } else if ($key == 3) {
                        return $query->where('type', 'lain_lain_pad_yang_sah');
                    } else if ($key == 4) {
                        return $query->where('type', 'transfer_pemerintah_pusat');
                    } else if ($key == 5) {
                        return $query->where('type', 'transfer_antar_daerah');
                    }
                })
                ->sum('saldo_akhir');
            $piutangBruto = DB::table('acc_plo_piutang')
                ->where('periode_id', $request->periode)
                ->where('year', $request->year)
                ->when($request->instance, function ($query) use ($request) {
                    return $query->where('instance_id', $request->instance);
                })
                ->where(function ($query) use ($key) {
                    if ($key == 0) {
                        return $query->where('type', 'pendapatan_pajak_daerah');
                    } else if ($key == 1) {
                        return $query->where('type', 'hasil_retribusi_daerah');
                    } else if ($key == 2) {
                        return $query->where('type', 'hasil_pengelolaan_kekayaan_daerah_yang_dipisahkan');
                    } else if ($key == 3) {
                        return $query->where('type', 'lain_lain_pad_yang_sah');
                    } else if ($key == 4) {
                        return $query->where('type', 'transfer_pemerintah_pusat');
                    } else if ($key == 5) {
                        return $query->where('type', 'transfer_antar_daerah');
                    }
                })
                ->sum('piutang_bruto');
            $penyisihanPiutang = DB::table('acc_plo_penyisihan')
                ->where('periode_id', $request->periode)
                ->where('year', $request->year)
                ->when($request->instance, function ($query) use ($request) {
                    return $query->where('instance_id', $request->instance);
                })
                ->where(function ($query) use ($key) {
                    if ($key == 0) {
                        return $query->where('type', 'pendapatan_pajak_daerah');
                    } else if ($key == 1) {
                        return $query->where('type', 'hasil_retribusi_daerah');
                    } else if ($key == 2) {
                        return $query->where('type', 'hasil_pengelolaan_kekayaan_daerah_yang_dipisahkan');
                    } else if ($key == 3) {
                        return $query->where('type', 'lain_lain_pad_yang_sah');
                    } else if ($key == 4) {
                        return $query->where('type', 'transfer_pemerintah_pusat');
                    } else if ($key == 5) {
                        return $query->where('type', 'transfer_antar_daerah');
                    }
                })
                ->sum('jumlah');
            $bebanPenyisihan = DB::table('acc_plo_beban')
                ->where('periode_id', $request->periode)
                ->where('year', $request->year)
                ->when($request->instance, function ($query) use ($request) {
                    return $query->where('instance_id', $request->instance);
                })
                ->where(function ($query) use ($key) {
                    if ($key == 0) {
                        return $query->where('type', 'pendapatan_pajak_daerah');
                    } else if ($key == 1) {
                        return $query->where('type', 'hasil_retribusi_daerah');
                    } else if ($key == 2) {
                        return $query->where('type', 'hasil_pengelolaan_kekayaan_daerah_yang_dipisahkan');
                    } else if ($key == 3) {
                        return $query->where('type', 'lain_lain_pad_yang_sah');
                    } else if ($key == 4) {
                        return $query->where('type', 'transfer_pemerintah_pusat');
                    } else if ($key == 5) {
                        return $query->where('type', 'transfer_antar_daerah');
                    }
                })
                ->sum('beban_penyisihan');


            $datas[] = [
                'uraian' => $uraian,
                'saldo_awal' => $saldoAwal ?? 0,
                'saldo_akhir' => $saldoAkhir ?? 0,
                'piutang_bruto' => $piutangBruto ?? 0,
                'penyisihan_piutang' => $penyisihanPiutang ?? 0,
                'beban_penyisihan' => $bebanPenyisihan ?? 0,
            ];
        }

        return $this->successResponse($datas);
    }

    function getPiutang(Request $request)
    {
        $datas = [];
        $arrData = DB::table('acc_plo_piutang')
            ->where('periode_id', $request->periode)
            ->where('year', $request->year)
            ->when($request->instance, function ($query) use ($request) {
                return $query->where('instance_id', $request->instance);
            })
            ->whereNull('deleted_at')
            ->get();
        foreach ($arrData as $data) {
            $instance = DB::table('instances')
                ->where('id', $data->instance_id)
                ->first();
            $kodeRekening = DB::table('ref_kode_rekening_complete')
                ->where('id', $data->kode_rekening_id)
                ->first();
            $datas[] = [
                'id' => $data->id,
                'periode_id' => $data->periode_id,
                'year' => $data->year,
                'instance_id' => $data->instance_id,
                'instance_name' => $instance->name ?? null,
                'type' => $data->type,
                'kode_rekening_id' => $data->kode_rekening_id,
                'kode_rekening_fullcode' => $kodeRekening->fullcode ?? null,
                'kode_rekening_name' => $kodeRekening->name ?? null,
                'saldo_awal' => $data->saldo_awal,
                'saldo_akhir' => $data->saldo_akhir,
                'koreksi_saldo_awal' => $data->koreksi_saldo_awal,
                'mutasi_debet' => $data->mutasi_debet,
                'mutasi_kredit' => $data->mutasi_kredit,
                'penghapusan_piutang' => $data->penghapusan_piutang,
                'piutang_bruto' => $data->piutang_bruto,
                'umur_piutang_1' => $data->umur_piutang_1,
                'umur_piutang_2' => $data->umur_piutang_2,
                'umur_piutang_3' => $data->umur_piutang_3,
                'umur_piutang_4' => $data->umur_piutang_4,
            ];
        }

        return $this->successResponse($datas);
    }

    function storePiutang(Request $request)
    {
        $validate = Validator::make($request->all(), [
            'periode' => 'required|exists:ref_periode,id',
            'year' => 'nullable|numeric',
            'data.*.instance_id' => 'required|exists:instances,id',
            'data.*.kode_rekening_id' => 'nullable|exists:ref_kode_rekening_complete,id',
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
            $datas = $request->data;
            foreach ($datas as $input) {
                if ($input['id']) {
                    $input = collect($input)->except([
                        'instance_name',
                        'kode_rekening_fullcode',
                        'kode_rekening_name',
                    ])->toArray();
                    DB::table('acc_plo_piutang')->where('id', $input['id'])->update($input);
                } elseif (!$input['id']) {
                    if ($input['kode_rekening_id'] == null) {
                        continue;
                    }
                    $input = collect($input)
                        ->merge([
                            'periode_id' => $request->periode,
                            'year' => $request->year,
                        ]);
                    $input = collect($input)->except('id')->toArray();
                    DB::table('acc_plo_piutang')
                        ->insert($input);
                }
            }
            DB::commit();
            return $this->successResponse(null, 'Data berhasil disimpan');
        } catch (\Exception $e) {
            DB::rollBack();
            return $this->errorResponse($e->getMessage());
        }
    }

    function deletePiutang(Request $request)
    {
        $validate = Validator::make($request->all(), [
            'id' => 'required|exists:acc_plo_piutang,id',
        ], [], [
            'id' => 'ID',
        ]);
        if ($validate->fails()) {
            return $this->validationResponse($validate->errors());
        }

        DB::beginTransaction();
        try {
            DB::table('acc_plo_piutang')->where('id', $request->id)->delete();
            DB::commit();
            return $this->successResponse(null, 'Data berhasil dihapus');
        } catch (\Exception $e) {
            DB::rollBack();
            return $this->errorResponse($e->getMessage());
        }
    }

    function getPenyisihan(Request $request)
    {
        $datas = [];
        $arrPiutangData = DB::table('acc_plo_piutang')
            ->where('periode_id', $request->periode)
            ->where('year', $request->year)
            ->when($request->instance, function ($query) use ($request) {
                return $query->where('instance_id', $request->instance);
            })
            ->whereNull('deleted_at')
            ->get();

        foreach ($arrPiutangData as $data) {
            $penyisihanData = DB::table('acc_plo_penyisihan')
                ->where('periode_id', $data->periode_id)
                ->where('year', $data->year)
                ->where('instance_id', $data->instance_id)
                ->where('kode_rekening_id', $data->kode_rekening_id)
                ->whereNull('deleted_at')
                ->first();
            $penyisihan1 = 0;
            $penyisihan1 = $data->umur_piutang_1 * (0.5 / 100);
            $penyisihan2 = 0;
            $penyisihan2 = $data->umur_piutang_2 * (10 / 100);
            $penyisihan3 = 0;
            $penyisihan3 = $data->umur_piutang_3 * (50 / 100);
            $penyisihan4 = 0;
            $penyisihan4 = $data->umur_piutang_4 * (100 / 100);
            $jumlah = $penyisihan1 + $penyisihan2 + $penyisihan3 + $penyisihan4;

            if (!$penyisihanData) {
                if ($data->kode_rekening_id != null) {
                    $penyisihanData = DB::table('acc_plo_penyisihan')
                        ->insert([
                            'periode_id' => $data->periode_id,
                            'year' => $data->year,
                            'type' => $data->type,
                            'instance_id' => $data->instance_id,
                            'kode_rekening_id' => $data->kode_rekening_id,
                            'piutang_bruto' => $data->piutang_bruto,
                            'penyisihan_piutang_1' => $penyisihan1 ?? 0,
                            'penyisihan_piutang_2' => $penyisihan2 ?? 0,
                            'penyisihan_piutang_3' => $penyisihan3 ?? 0,
                            'penyisihan_piutang_4' => $penyisihan4 ?? 0,
                            'jumlah' => $jumlah ?? 0,
                        ]);
                }
            } else {
                if ($data->kode_rekening_id != null) {
                    $penyisihanData = DB::table('acc_plo_penyisihan')
                        ->where('periode_id', $data->periode_id)
                        ->where('year', $data->year)
                        ->where('instance_id', $data->instance_id)
                        ->where('kode_rekening_id', $data->kode_rekening_id)
                        ->update([
                            'piutang_bruto' => $data->piutang_bruto,
                            'penyisihan_piutang_1' => $penyisihan1 ?? 0,
                            'penyisihan_piutang_2' => $penyisihan2 ?? 0,
                            'penyisihan_piutang_3' => $penyisihan3 ?? 0,
                            'penyisihan_piutang_4' => $penyisihan4 ?? 0,
                            'jumlah' => $jumlah ?? 0,
                        ]);
                }
            }
        }

        $arrData = DB::table('acc_plo_penyisihan')
            ->where('periode_id', $request->periode)
            ->where('year', $request->year)
            ->when($request->instance, function ($query) use ($request) {
                return $query->where('instance_id', $request->instance);
            })
            ->whereNull('deleted_at')
            ->get();

        foreach ($arrData as $data) {
            $instance = DB::table('instances')
                ->where('id', $data->instance_id)
                ->first();
            $kodeRekening = DB::table('ref_kode_rekening_complete')
                ->where('id', $data->kode_rekening_id)
                ->first();
            $datas[] = [
                'id' => $data->id,
                'periode_id' => $data->periode_id,
                'year' => $data->year,
                'instance_id' => $data->instance_id,
                'instance_name' => $instance->name ?? null,
                'type' => $data->type,
                'kode_rekening_id' => $data->kode_rekening_id,
                'kode_rekening_fullcode' => $kodeRekening->fullcode ?? null,
                'kode_rekening_name' => $kodeRekening->name ?? null,
                'piutang_bruto' => $data->piutang_bruto,
                'penyisihan_piutang_1' => $data->penyisihan_piutang_1,
                'penyisihan_piutang_2' => $data->penyisihan_piutang_2,
                'penyisihan_piutang_3' => $data->penyisihan_piutang_3,
                'penyisihan_piutang_4' => $data->penyisihan_piutang_4,
                'jumlah' => $data->jumlah,
            ];
        }
        return $this->successResponse($datas);
    }

    function storePenyisihan(Request $request)
    {
        $validate = Validator::make($request->all(), [
            'periode' => 'required|exists:ref_periode,id',
            'year' => 'nullable|numeric',
            'data.*.instance_id' => 'required|exists:instances,id',
            'data.*.kode_rekening_id' => 'nullable|exists:ref_kode_rekening_complete,id',
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
            $datas = $request->data;
            foreach ($datas as $input) {
                if ($input['id']) {
                    $input = collect($input)->except([
                        'instance_name',
                        'kode_rekening_fullcode',
                        'kode_rekening_name',
                    ])->toArray();
                    DB::table('acc_plo_penyisihan')->where('id', $input['id'])->update($input);
                } elseif (!$input['id']) {
                    if ($input['kode_rekening_id'] == null) {
                        continue;
                    }
                    $input = collect($input)
                        ->merge([
                            'periode_id' => $request->periode,
                            'year' => $request->year,
                        ]);
                    $input = collect($input)->except('id')->toArray();
                    DB::table('acc_plo_penyisihan')
                        ->insert($input);
                }
            }
            DB::commit();
            return $this->successResponse(null, 'Data berhasil disimpan');
        } catch (\Exception $e) {
            DB::rollBack();
            return $this->errorResponse($e->getMessage());
        }
    }

    function deletePenyisihan(Request $request)
    {
        $validate = Validator::make($request->all(), [
            'id' => 'required|exists:acc_plo_penyisihan,id',
        ], [], [
            'id' => 'ID',
        ]);
        if ($validate->fails()) {
            return $this->validationResponse($validate->errors());
        }

        DB::beginTransaction();
        try {
            DB::table('acc_plo_penyisihan')->where('id', $request->id)->delete();
            DB::commit();
            return $this->successResponse(null, 'Data berhasil dihapus');
        } catch (\Exception $e) {
            DB::rollBack();
            return $this->errorResponse($e->getMessage());
        }
    }

    function getBeban(Request $request)
    {
        $datas = [];
        $arrPenyisihanData = DB::table('acc_plo_penyisihan')
            ->where('periode_id', $request->periode)
            ->where('year', $request->year)
            ->when($request->instance, function ($query) use ($request) {
                return $query->where('instance_id', $request->instance);
            })
            ->whereNull('deleted_at')
            ->get();

        foreach ($arrPenyisihanData as $data) {
            $bebanData = DB::table('acc_plo_beban')
                ->where('periode_id', $data->periode_id)
                ->where('year', $data->year)
                ->where('instance_id', $data->instance_id)
                ->where('kode_rekening_id', $data->kode_rekening_id)
                ->whereNull('deleted_at')
                ->first();
            if (!$bebanData) {
                if ($data->kode_rekening_id != null) {
                    $bebanData = DB::table('acc_plo_beban')
                        ->insert([
                            'periode_id' => $data->periode_id,
                            'year' => $data->year,
                            'type' => $data->type,
                            'instance_id' => $data->instance_id,
                            'kode_rekening_id' => $data->kode_rekening_id,
                            'jumlah_penyisihan' => $data->jumlah,
                            'jumlah_penyisihan_last_year' => 0,
                            'koreksi_penyisihan' => 0,
                            'beban_penyisihan' => $data->jumlah,
                        ]);
                }
            } else {
                if ($data->kode_rekening_id != null) {
                    $bebanPenyisihan = $data->jumlah - $bebanData->jumlah_penyisihan_last_year + $bebanData->koreksi_penyisihan;
                    $bebanData = DB::table('acc_plo_beban')
                        ->where('periode_id', $data->periode_id)
                        ->where('year', $data->year)
                        ->where('instance_id', $data->instance_id)
                        ->where('kode_rekening_id', $data->kode_rekening_id)
                        ->update([
                            'jumlah_penyisihan' => $data->jumlah,
                            'beban_penyisihan' => $bebanPenyisihan,
                        ]);
                }
            }
        }

        $arrData = DB::table('acc_plo_beban')
            ->where('periode_id', $request->periode)
            ->where('year', $request->year)
            ->when($request->instance, function ($query) use ($request) {
                return $query->where('instance_id', $request->instance);
            })
            ->whereNull('deleted_at')
            ->get();

        foreach ($arrData as $data) {
            $instance = DB::table('instances')
                ->where('id', $data->instance_id)
                ->first();
            $kodeRekening = DB::table('ref_kode_rekening_complete')
                ->where('id', $data->kode_rekening_id)
                ->first();
            $datas[] = [
                'id' => $data->id,
                'periode_id' => $data->periode_id,
                'year' => $data->year,
                'instance_id' => $data->instance_id,
                'instance_name' => $instance->name ?? null,
                'type' => $data->type,
                'kode_rekening_id' => $data->kode_rekening_id,
                'kode_rekening_fullcode' => $kodeRekening->fullcode ?? null,
                'kode_rekening_name' => $kodeRekening->name ?? null,
                'jumlah_penyisihan' => $data->jumlah_penyisihan,
                'jumlah_penyisihan_last_year' => $data->jumlah_penyisihan_last_year,
                'koreksi_penyisihan' => $data->koreksi_penyisihan,
                'beban_penyisihan' => $data->beban_penyisihan,
            ];
        }
        return $this->successResponse($datas);
    }

    function storeBeban(Request $request)
    {
        $validate = Validator::make($request->all(), [
            'periode' => 'required|exists:ref_periode,id',
            'year' => 'nullable|numeric',
            'data.*.instance_id' => 'required|exists:instances,id',
            'data.*.kode_rekening_id' => 'nullable|exists:ref_kode_rekening_complete,id',
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
            $datas = $request->data;
            foreach ($datas as $input) {
                if ($input['id']) {
                    $input = collect($input)->except([
                        'instance_name',
                        'kode_rekening_fullcode',
                        'kode_rekening_name',
                    ])->toArray();
                    DB::table('acc_plo_beban')->where('id', $input['id'])->update($input);
                } elseif (!$input['id']) {
                    if ($input['kode_rekening_id'] == null) {
                        continue;
                    }
                    $input = collect($input)
                        ->merge([
                            'periode_id' => $request->periode,
                            'year' => $request->year,
                        ]);
                    $input = collect($input)->except('id')->toArray();
                    DB::table('acc_plo_beban')
                        ->insert($input);
                }
            }
            DB::commit();
            return $this->successResponse(null, 'Data berhasil disimpan');
        } catch (\Exception $e) {
            DB::rollBack();
            return $this->errorResponse($e->getMessage());
        }
    }

    function deleteBeban(Request $request)
    {
        $validate = Validator::make($request->all(), [
            'id' => 'required|exists:acc_plo_beban,id',
        ], [], [
            'id' => 'ID',
        ]);
        if ($validate->fails()) {
            return $this->validationResponse($validate->errors());
        }

        DB::beginTransaction();
        try {
            DB::table('acc_plo_beban')->where('id', $request->id)->delete();
            DB::commit();
            return $this->successResponse(null, 'Data berhasil dihapus');
        } catch (\Exception $e) {
            DB::rollBack();
            return $this->errorResponse($e->getMessage());
        }
    }

    function getPdd(Request $request)
    {
        $datas = [];
        $arrData = DB::table('acc_plo_pdd')
            ->where('periode_id', $request->periode)
            ->where('year', $request->year)
            ->when($request->instance, function ($query) use ($request) {
                return $query->where('instance_id', $request->instance);
            })
            ->whereNull('deleted_at')
            ->get();
        foreach ($arrData as $data) {
            $instance = DB::table('instances')
                ->where('id', $data->instance_id)
                ->first();
            $kodeRekening = DB::table('ref_kode_rekening_complete')
                ->where('id', $data->kode_rekening_id)
                ->first();
            $datas[] = [
                'id' => $data->id,
                'periode_id' => $data->periode_id,
                'year' => $data->year,
                'instance_id' => $data->instance_id,
                'instance_name' => $instance->name ?? null,
                'type' => $data->type,
                'kode_rekening_id' => $data->kode_rekening_id,
                'kode_rekening_fullcode' => $kodeRekening->fullcode ?? null,
                'kode_rekening_name' => $kodeRekening->name ?? null,
                'pendapatan_diterima_dimuka_awal' => $data->pendapatan_diterima_dimuka_awal,
                'mutasi_berkurang' => $data->mutasi_berkurang,
                'mutasi_bertambah' => $data->mutasi_bertambah,
                'pendapatan_diterima_dimuka_akhir' => $data->pendapatan_diterima_dimuka_akhir,
            ];
        }
        return $this->successResponse($datas);
    }

    function storePdd(Request $request)
    {
        $validate = Validator::make($request->all(), [
            'periode' => 'required|exists:ref_periode,id',
            'year' => 'nullable|numeric',
            'data.*.instance_id' => 'required|exists:instances,id',
            'data.*.kode_rekening_id' => 'nullable|exists:ref_kode_rekening_complete,id',
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
            $datas = $request->data;
            foreach ($datas as $input) {
                if ($input['id']) {
                    $input = collect($input)->except([
                        'instance_name',
                        'kode_rekening_fullcode',
                        'kode_rekening_name',
                    ])->toArray();
                    DB::table('acc_plo_pdd')->where('id', $input['id'])->update($input);
                } elseif (!$input['id']) {
                    if ($input['kode_rekening_id'] == null) {
                        continue;
                    }
                    $input = collect($input)
                        ->merge([
                            'periode_id' => $request->periode,
                            'year' => $request->year,
                        ]);
                    $input = collect($input)->except('id')->toArray();
                    DB::table('acc_plo_pdd')
                        ->insert($input);
                }
            }
            DB::commit();
            return $this->successResponse(null, 'Data berhasil disimpan');
        } catch (\Exception $e) {
            DB::rollBack();
            return $this->errorResponse($e->getMessage());
        }
    }

    function deletePdd(Request $request)
    {
        $validate = Validator::make($request->all(), [
            'id' => 'required|exists:acc_plo_pdd,id',
        ], [], [
            'id' => 'ID',
        ]);
        if ($validate->fails()) {
            return $this->validationResponse($validate->errors());
        }

        DB::beginTransaction();
        try {
            DB::table('acc_plo_pdd')->where('id', $request->id)->delete();
            DB::commit();
            return $this->successResponse(null, 'Data berhasil dihapus');
        } catch (\Exception $e) {
            DB::rollBack();
            return $this->errorResponse($e->getMessage());
        }
    }

    function getLoTa(Request $request)
    {
        $datas = [];
        $arrLra = DB::table('acc_lra')
            ->where('periode_id', $request->periode)
            ->where('year', $request->year ?? date('Y'))
            // ->where('instance_id', $request->instance)
            ->when($request->instance, function ($query) use ($request) {
                return $query->where('instance_id', $request->instance);
            })
            ->where('kode_rekening', 'ILIKE', '4.%')
            // ->whereRaw('LENGTH(kode_rekening) >= ?', [17])
            ->orderBy('kode_rekening')
            ->get();
        foreach ($arrLra as $lra) {
            $kodeRekening = DB::table('ref_kode_rekening_complete')
                ->where('fullcode', $lra->kode_rekening)
                ->whereNotNull('code_6')
                ->first();
            if ($kodeRekening) {
                $data = DB::table('acc_plo_lo_ta')
                    ->where('periode_id', $request->periode)
                    ->where('year', $request->year ?? date('Y'))
                    ->where('instance_id', $lra->instance_id)
                    ->where('kode_rekening_id', $kodeRekening->id)
                    ->first();
                $dataPiutang = DB::table('acc_plo_piutang')
                    ->where('periode_id', $request->periode)
                    ->where('year', $request->year)
                    ->where('instance_id', $lra->instance_id)
                    ->where('kode_rekening_id', $kodeRekening->id)
                    ->whereNull('deleted_at')
                    ->first();
                $dataPdd = DB::table('acc_plo_pdd')
                    ->where('periode_id', $request->periode)
                    ->where('year', $request->year)
                    ->where('instance_id', $lra->instance_id)
                    ->where('kode_rekening_id', $kodeRekening->id)
                    ->whereNull('deleted_at')
                    ->first();
                $laporanOperasional = 0;
                // if ($dataPiutang || $dataPdd) {
                $laporanOperasional = (($lra->realisasi ?? 0) - ($dataPiutang->saldo_awal ?? 0) + ($dataPiutang->saldo_akhir ?? 0) - ($dataPdd->pendapatan_diterima_dimuka_akhir ?? 0) + ($dataPdd->pendapatan_diterima_dimuka_awal ?? 0)) + ($data->penambahan_pengurangan_lo ?? 0) + ($data->reklas_koreksi_lo ?? 0);
                // }
                $laporanOperasionalPercent = 0;
                if ($lra->realisasi) {
                    $laporanOperasionalPercent = $laporanOperasional / $lra->realisasi * 100;
                }
                $lraPercent = 0;
                if ($lra->realisasi) {
                    $lraPercent = $lra->realisasi / $lra->anggaran * 100;
                }
                if (!$data) {
                    DB::table('acc_plo_lo_ta')
                        ->insert([
                            'periode_id' => $request->periode,
                            'year' => $request->year ?? date('Y'),
                            'instance_id' => $request->instance ?? $lra->instance_id,
                            'kode_rekening_id' => $kodeRekening->id,
                            'anggaran_perubahan' => $lra->anggaran,
                            'lra' => $lra->realisasi,
                            'lra_percent' => $lraPercent ?? 0,
                            'piutang_awal' => $dataPiutang->saldo_awal ?? 0,
                            'piutang_akhir' => $dataPiutang->saldo_akhir ?? 0,
                            'pdd_awal' => $dataPdd->pendapatan_diterima_dimuka_awal ?? 0,
                            'pdd_akhir' => $dataPdd->pendapatan_diterima_dimuka_akhir ?? 0,
                            'laporan_operasional' => $laporanOperasional ?? 0,
                            'laporan_operasional_percent' => $laporanOperasionalPercent ?? 0,
                            'penambahan_pengurangan_lo' => 0,
                            'reklas_koreksi_lo' => 0,
                            'perbedaan_lo_lra' => 0,
                        ]);
                } elseif ($data) {
                    DB::table('acc_plo_lo_ta')
                        ->where('id', $data->id)
                        ->update([
                            'anggaran_perubahan' => $lra->anggaran,
                            'instance_id' => $lra->instance_id,
                            'lra' => $lra->realisasi,
                            'lra_percent' => $lraPercent ?? 0,
                            'piutang_awal' => $dataPiutang->saldo_awal ?? 0,
                            'piutang_akhir' => $dataPiutang->saldo_akhir ?? 0,
                            'pdd_awal' => $dataPdd->pendapatan_diterima_dimuka_awal ?? 0,
                            'pdd_akhir' => $dataPdd->pendapatan_diterima_dimuka_akhir ?? 0,
                            'laporan_operasional' => $laporanOperasional ? ($laporanOperasional + ($data->penambahan_pengurangan_lo ?? 0) + ($data->reklas_koreksi_lo ?? 0)) : 0,
                            'laporan_operasional_percent' => $laporanOperasionalPercent ?? 0,
                        ]);

                    $data = DB::table('acc_plo_lo_ta')
                        ->where('id', $data->id)
                        ->first();
                    $instance = DB::table('instances')
                        ->where('id', $data->instance_id)
                        ->first();
                    $datas[] = [
                        'id' => $data->id,
                        'periode_id' => $data->periode_id,
                        'year' => $data->year,
                        'instance_id' => $data->instance_id,
                        'instance_name' => $instance->name ?? null,
                        'type' => $data->type,
                        'kode_rekening_id' => $data->kode_rekening_id,
                        'kode_rekening_fullcode' => $kodeRekening->fullcode ?? null,
                        'kode_rekening_name' => $kodeRekening->name ?? null,
                        'anggaran_perubahan' => $data->anggaran_perubahan,
                        'lra' => $data->lra,
                        'lra_percent' => $data->lra_percent,
                        'piutang_awal' => $data->piutang_awal,
                        'piutang_akhir' => $data->piutang_akhir,
                        'pdd_awal' => $data->pdd_awal,
                        'pdd_akhir' => $data->pdd_akhir,
                        'laporan_operasional' => $data->laporan_operasional,
                        'laporan_operasional_percent' => $data->laporan_operasional_percent,
                        'penambahan_pengurangan_lo' => $data->penambahan_pengurangan_lo,
                        'reklas_koreksi_lo' => $data->reklas_koreksi_lo,
                        'perbedaan_lo_lra' => $data->perbedaan_lo_lra,
                    ];
                }
            }
        }
        $unListed = DB::table('acc_plo_lo_ta')
            ->where('periode_id', $request->periode)
            ->where('year', $request->year ?? date('Y'))
            ->where('instance_id', $request->instance)
            ->whereNotIn('id', collect($datas)->pluck('id')->toArray())
            ->get();
        foreach ($unListed as $data) {
            $instance = DB::table('instances')
                ->where('id', $data->instance_id)
                ->first();
            $kodeRekening = DB::table('ref_kode_rekening_complete')
                ->where('id', $data->kode_rekening_id)
                ->first();
            $datas[] = [
                'id' => $data->id,
                'periode_id' => $data->periode_id,
                'year' => $data->year,
                'instance_id' => $data->instance_id,
                'instance_name' => $instance->name ?? null,
                'type' => $data->type,
                'kode_rekening_id' => $data->kode_rekening_id,
                'kode_rekening_fullcode' => $kodeRekening->fullcode ?? null,
                'kode_rekening_name' => $kodeRekening->name ?? null,
                'anggaran_perubahan' => $data->anggaran_perubahan,
                'lra' => $data->lra,
                'lra_percent' => $data->lra_percent,
                'piutang_awal' => $data->piutang_awal,
                'piutang_akhir' => $data->piutang_akhir,
                'pdd_awal' => $data->pdd_awal,
                'pdd_akhir' => $data->pdd_akhir,
                'laporan_operasional' => $data->laporan_operasional,
                'laporan_operasional_percent' => $data->laporan_operasional_percent,
                'penambahan_pengurangan_lo' => $data->penambahan_pengurangan_lo,
                'reklas_koreksi_lo' => $data->reklas_koreksi_lo,
                'perbedaan_lo_lra' => $data->perbedaan_lo_lra,
            ];
        }
        return $this->successResponse($datas);
    }

    function storeLoTa(Request $request)
    {
        $validate = Validator::make($request->all(), [
            'periode' => 'required|exists:ref_periode,id',
            'year' => 'nullable|numeric',
            'data.*.instance_id' => 'required|exists:instances,id',
            'data.*.kode_rekening_id' => 'nullable|exists:ref_kode_rekening_complete,id',
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
            $datas = $request->data;
            foreach ($datas as $input) {
                if ($input['id']) {
                    DB::table('acc_plo_lo_ta')->where('id', $input['id'])->update($input);
                } elseif (!$input['id']) {
                    if ($input['kode_rekening_id'] == null) {
                        continue;
                    }
                    $input = collect($input)
                        ->merge([
                            'periode_id' => $request->periode,
                            'year' => $request->year,
                        ]);
                    $input = collect($input)->except('id')->toArray();
                    DB::table('acc_plo_lo_ta')
                        ->insert($input);
                }
            }
            DB::commit();
            return $this->successResponse(null, 'Data berhasil disimpan');
        } catch (\Exception $e) {
            DB::rollBack();
            return $this->errorResponse($e->getMessage());
        }
    }

    function deleteLoTa(Request $request)
    {
        $validate = Validator::make($request->all(), [
            'id' => 'required|exists:acc_plo_lo_ta,id',
        ], [], [
            'id' => 'ID',
        ]);
        if ($validate->fails()) {
            return $this->validationResponse($validate->errors());
        }

        DB::beginTransaction();
        try {
            DB::table('acc_plo_lo_ta')->where('id', $request->id)->delete();
            DB::commit();
            return $this->successResponse(null, 'Data berhasil dihapus');
        } catch (\Exception $e) {
            DB::rollBack();
            return $this->errorResponse($e->getMessage());
        }
    }
}
