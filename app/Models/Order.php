<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Order extends Model 
{

    protected $table = 'orders';
    public $timestamps = true;
    protected $fillable = array('special_request', 'total', 'notes', 'address', 'order_status', 'reason', 'resturant_id', 'client_id', 'total_commission','cost','delivery_cost','net');

    public function resturant()
    {
        return $this->belongsTo('App\Models\Resturant');
    }

    public function client()
    {
        return $this->belongsTo('App\Models\Client');
    }

    public function products()
    {
        return $this->belongsToMany('App\Models\Product')->withPivot('price','quantity');
    }

    public function methods_payment()
    {
        return $this->belongsToMany('App\Models\Metnod_Payment');
    }

}