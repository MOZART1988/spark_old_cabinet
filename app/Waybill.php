<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Waybill extends Model
{
    protected $table = 'orders_statistics';
    public $timestamps = false;
    public $primaryKey = 'waybill';
    public $incrementing = false;
    public $keyType = 'string';
    protected $fillable = ['waybill','lead_id','order_creation_time','accepted_by_dispatcher','transferred_to_driver','taken_by_driver','delivered_to_warehouse','ready_to_send','on_the_way','in_region','shipping','shipped'];
}
