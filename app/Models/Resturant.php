<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Auth\User as Authenticatable;

class Resturant extends Authenticatable
{

    protected $table = 'resturants';
    public $timestamps = true;
    protected $fillable = array('resturant_name', 'email', 'delivery_time', 'district_id', 'password', 'minimum_order', 'delivery_charge', 'status', 'phone', 'whatsapp','image', 'rest_code', 'api_token');


    protected $guard = 'resturant_api';

    protected $hidden = [
        'api_token','password'
    ];


    //Mutator(SET ATTRIBUTE)
    public function SetpasswordAttribute($value)
    {
        $this->attributes['password'] = bcrypt($value);
    }

    public function categories()
    {
        return $this->belongsToMany('App\Models\Category');
    }

    public function comments()
    {
        return $this->hasMany('App\Models\Comment');
    }

    public function orders()
    {
        return $this->hasMany('App\Models\Order');
    }

    public function notifications()
    {
        return $this->morphMany('App\Models\Notification', 'notificationable');
    }

    public function contacts()
    {
        return $this->morphMany('App\Models\Contact', 'contactaxble');
    }

    public function district()
    {
        return $this->belongsTo('App\Models\District');
    }

    public function tokens()
    {
        return $this->belongsToMany('App\Models\Token');
    }

}