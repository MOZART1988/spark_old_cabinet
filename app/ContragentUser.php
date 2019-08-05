<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class ContragentUser extends Model
{
    protected $table = 'cabinet_users';
    public $timestamps = false;
    protected $primaryKey = 'unique_id';
    public $incrementing = false;
}
