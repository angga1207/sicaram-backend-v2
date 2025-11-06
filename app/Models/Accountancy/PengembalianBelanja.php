<?php

namespace App\Models\Accountancy;

use App\Models\User;
use App\Models\Instance;
use App\Models\InstanceSubUnit;
use App\Models\Ref\KodeRekening;
use App\Traits\Searchable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class PengembalianBelanja extends Model
{
    use HasFactory, SoftDeletes, Searchable;
    protected $table = 'acc_pengembalian_belanja';
    protected $fillable = [
        'periode_id',
        'year',
        'instance_id',
        'unit_id',

        'tanggal_setor',
        'kode_rekening_id',
        'uraian',
        'jenis_spm',
        'jumlah',

        'keterangan',
        'change_logs',
        'created_by',
        'updated_by',
        'deleted_by',
    ];

    protected $searchable = [
        'uraian',
        'jenis_spm',
        'tanggal_setor',
        'KodeRekening.fullcode',
        'KodeRekening.name',
    ];

    function Instance()
    {
        return $this->belongsTo(Instance::class, 'instance_id');
    }

    function KodeRekening()
    {
        return $this->belongsTo(KodeRekening::class, 'kode_rekening_id');
    }

    function Unit()
    {
        return $this->belongsTo(InstanceSubUnit::class, 'unit_id');
    }

    function CreatedBy()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    function UpdatedBy()
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    function DeletedBy()
    {
        return $this->belongsTo(User::class, 'deleted_by');
    }

    function getChangeLogsAttribute($value)
    {
        return json_decode($value, true);
    }
}
