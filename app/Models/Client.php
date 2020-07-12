<?php

namespace App\Models;

//use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Auth\User as Authenticatable;

class Client extends Authenticatable
{

    protected $table = 'clients';
    public $timestamps = true;
    protected $fillable = array('username', 'password', 'email', 'phone', 'district_id', 'image', 'reset_code', 'api_token');

    protected $guard = 'client_api';

    protected $hidden = [
        'api_token','password'
    ];


    //Mutator(SET ATTRIBUTE)
    public function SetpasswordAttribute($value)
    {
        $this->attributes['password'] = bcrypt($value);
    }

    public function city()
    {
        return $this->belongsTo('City');
    }

    public function distrct()
    {
        return $this->belongsTo('App\Models\District');
    }

    public function notifications()
    {
        return $this->morphMany('App\Models\Notification', 'notificationable');
    }

    public function orders()
    {
        return $this->hasMany('App\Models\Order');
    }

    public function comments()
    {
        return $this->hasMany('App\Models\Comment');
    }

    public function contacts()
    {
        return $this->morphMany('App\Models\Contact', 'contactable');
    }

    public function tokens()
    {
        return $this->hasMany('App\Models\Token');
    }

}