<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Lead extends Model
{
    protected $table = 'leads_new';
    public $timestamps = false;
    protected $primaryKey = 'lead_id';
    public $incrementing = false;
}
