<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Product extends Model 
{

    protected $table = 'products';
    public $timestamps = true;
    protected $fillable = array('name', 'details', 'price', 'price_offer', 'image', 'category_id');

    public function getIsOfferAttribute()

    {

        if ($this->price_offer != null && $this->price_offer < $this->price) {

            return true;

        } else {

            return false;

        }

    }

    public function category()
    {
        return $this->belongsTo('App\Models\Category');
    }

    public function orders()
    {
        return $this->belongsToMany('App\Models\Order');
    }


}