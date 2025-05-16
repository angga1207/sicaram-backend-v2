<?php

namespace App\Models\Accountancy\RekonAset;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class KibA extends Model
{
    use HasFactory, SoftDeletes;
    protected $table = 'acc_rek_as_kib_a';
    protected $guarded = [];
}
