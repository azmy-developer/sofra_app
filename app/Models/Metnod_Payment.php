<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Metnod_Payment extends Model 
{

    protected $table = 'methods_payment';
    public $timestamps = true;
    protected $fillable = array('name');

    public function orders()
    {
        return $this->belongsToMany('Order');
    }

}