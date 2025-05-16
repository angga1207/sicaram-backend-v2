<?php

namespace App\Models\Accountancy;

use App\Models\Instance;
use App\Models\Ref\KodeRekening;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class AccReport extends Model
{
    use HasFactory;
    protected $table = 'acc_report';
    protected $guarded = ['id'];

    public function Instance()
    {
        return $this->belongsTo(Instance::class, 'instance_id');
    }

    public function KodeRekening()
    {
        return $this->belongsTo(KodeRekening::class, 'kode_rekening_id');
    }
}
