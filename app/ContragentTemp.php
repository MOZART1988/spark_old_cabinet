<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class ContragentTemp extends Model
{
    protected $table = 'contragents_temp';
    public $timestamps = false;
    protected $primaryKey = 'amo_id';
    public $incrementing = false;
}
