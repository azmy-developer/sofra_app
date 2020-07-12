<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\City;
use App\Models\District;
use App\Models\Resturant;
use App\Models\Setting;
use App\Models\Token;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class GeneralController extends Controller
{


    public function cities(){

        $Cities = City::paginate(25);
        if (count($Cities)== 0){
            return apiResponse('0', 'لا يوجد بيانات',$Cities);

        }else{
            return apiResponse('1', 'تم نجاح العمليه',$Cities);
        }
    }


    public function districts(Request $request){

        $District = District::where(function ($query) use ($request){
            if ($request->has('city_id')){
                $query->where('city_id',$request->city_id);
            }
        })->get();

        if (count($District)== 0){
            return apiResponse('0', 'لا يوجد بيانات',$District);

        }else{
            return apiResponse('1', 'تم نجاح العمليه',$District);
        }
    }


    public function settings(){

        $Settings = Setting::all();
        if (count($Settings)== 0){
            return apiResponse('0', 'لا يوجد بيانات',$Settings);

        }else{
            return apiResponse('1', 'تم نجاح العمليه',$Settings);
        }
    }




}
