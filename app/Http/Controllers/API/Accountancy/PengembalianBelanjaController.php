<?php

namespace App\Http\Controllers\API\Accountancy;

use App\Traits\JsonReturner;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Validator;
use App\Models\Accountancy\PengembalianBelanja;

class PengembalianBelanjaController extends Controller
{
    use JsonReturner;

    function getIndex(Request $request)
    {
        $validate = Validator::make($request->all(), [
            'year' => 'required|numeric|digits:4',
            'instance' => 'nullable|numeric|exists:instances,id',
        ]);
        if ($validate->fails()) {
            return $this->validationResponse($validate->errors()->first());
        }

        $returnData = [];
        $datas = PengembalianBelanja::search($request->search)
            ->where('year', $request->year)
            ->when($request->has('instance'), function ($query) use ($request) {
                $query->where('instance_id', $request->instance);
            });

        $count = $datas->count();
        $totalJumlah = $datas->sum('jumlah');
        $arrDatas = $datas->orderBy('tanggal_setor', 'asc')
            ->get()
            ->map(function ($item) {
                return [
                    'id' => $item->id,
                    'year' => $item->year,
                    'periode_id' => $item->periode_id,
                    'instance_id' => $item->instance_id,
                    'instance_name' => $item->instance ? $item->instance->name : null,
                    'tanggal_setor' => $item->tanggal_setor,
                    'kode_rekening_id' => $item->kode_rekening_id,
                    'kode_rekening_name' => $item->kodeRekening ? $item->kodeRekening->name : null,
                    'kode_rekening_fullcode' => $item->kodeRekening ? $item->kodeRekening->fullcode : null,
                    'uraian' => $item->uraian,
                    'jenis_spm' => $item->jenis_spm,
                    'jumlah' => $item->jumlah,
                ];
            });

        $returnData = [
            'total_data' => $count,
            'total_jumlah' => $totalJumlah,
            'datas' => $arrDatas,
        ];

        return $this->successResponse($returnData);
    }

    function storeData(Request $request)
    {
        $validate = Validator::make($request->all(), [
            'year' => 'required|numeric|digits:4',
            'periode' => 'required|numeric|exists:ref_periode,id',
            'data' => 'required|array',
            // 'instance_id' => 'nullable|numeric|exists:instances,id',
        ]);
        if ($validate->fails()) {
            return $this->validationResponse($validate->errors()->first());
        }

        DB::beginTransaction();
        try {
            foreach ($request->data as $item) {
                $item['year'] = $request->year;
                $item['periode'] = $request->periode;
                if ($item['id']) {
                    $data = PengembalianBelanja::find($item['id']);
                    $data->update($item);
                } else {
                    if (!$item['instance_id']) {
                        continue;
                    }
                    PengembalianBelanja::create($item);
                }
            }
            DB::commit();
            return $this->successResponse('Data berhasil disimpan');
        } catch (\Exception $e) {
            DB::rollBack();
            return $this->errorResponse($e->getMessage());
        }
    }

    function deleteData($id)
    {
        $data = PengembalianBelanja::find($id);
        if (!$data) {
            return $this->errorResponse('Data tidak ditemukan');
        }

        try {
            $data->delete();
            return $this->successResponse('Data berhasil dihapus');
        } catch (\Exception $e) {
            return $this->errorResponse($e->getMessage());
        }
    }
}
