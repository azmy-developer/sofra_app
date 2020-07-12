<?php

namespace App\Http\Controllers\Api\Resturant;

use App\Http\Controllers\Controller;
use App\Mail\ResetPassword;
use App\Models\Resturant;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use App\models\Token;
use Image;



class AuthResturantController extends Controller
{
    /**
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function registerResturant(Request $request)
    {
//        return $request->all();
        $validator = validator()->make($request->all(), [

            'resturant_name' => 'required',
            'email' => 'required|unique:resturants,email|email',
            'delivery_time' => 'required',
            'district_id' => 'required|exists:districts,id',
            'password' => 'required|confirmed',
            'minimum_order' => 'required|numeric',
            'delivery_charge' => 'required|numeric',
            'status' => 'required',
            'phone' => 'required|unique:resturants,phone',
            'whatsapp' => 'required|unique:resturants,whatsapp',
            'image' => 'required',

        ]);

        if ($validator->fails()) {
            $data = $validator->errors();
            return apiResponse('0', $validator->errors()->first(), $data);
        }
//        $request->merge(['password' => bcrypt($request->password)]);
        $restaurant = Resturant::create($request->all());
        $restaurant->api_token = Str::random(60);

        if($request->hasFile('image')){
            $thumbnail = $request->file('image');
            $filename = time() . '.' . $thumbnail->getClientOriginalExtension();
//            dd(public_path('uploads/' . $filename ));
            Image::make($thumbnail)->resize(300, 300)->save( public_path('/uploads/authRestaurant/' . $filename ) );
            $restaurant->image = $filename;

        };

        $restaurant->save();
        return apiResponse('1', 'تم التسجيل المطعم بنجاح', [

            'api_token' => $restaurant->api_token,
            'restaurant' => $restaurant

        ]);

    }


    /**
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function loginResturant(Request $request)
    {

        $validator = validator()->make($request->all(), [
            'email' => 'required',
            'password' => 'required',
        ]);

        if ($validator->fails()) {
            $data = $validator->errors();
            return apiResponse('0', $validator->errors()->first(), $data);
        }

        $restaurant = Resturant::where('email', $request->email)->first();
        if ($restaurant) {
            if (Hash::check($request->password, $restaurant->password)) {
                return apiResponse('1', 'تم التسجيل بنجاح', [
                    'api_token' => $restaurant->api_token,
                    'restaurant' => $restaurant
                ]);
            } else {
                return apiResponse('0', 'البيانات غير صحيحه');
            }
        } else {
            return apiResponse('0', 'البيانات غير صحيحه');
        }

    }


    /**
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function restPasswordResturant(Request $request)
    {

        /** @var TYPE_NAME $validator */
        $validator = validator()->make($request->all(), [
            'email' => 'required',
        ]);

        if ($validator->fails()) {
            $data = $validator->errors();
            return apiResponse('0', $validator->errors()->first(), $data);
        }

        $restaurant = Resturant::where('email', $request->email)->first();

        if ($restaurant) {
            $code = rand(1111, 9999);

            $update = $restaurant->update(['rest_code' => $code]);

            if ($update) {

                Mail::to($restaurant->email)
                    ->bcc("azmy.abuzeid@hotmail.com")
                    ->send(new ResetPassword($restaurant));

                return apiResponse('1', 'برجاء فحص هاتفك', [
                    'code' => $code,
                    'mail_fails' => Mail::failures(),
                    'email' => $restaurant->email,
                ]);
            } else {
                return apiResponse('0', 'حدث خطأ: برجاء المحاوله مره اخري');
            }
        } else {
            return apiResponse('0', 'البيانات غير صحيحه');
        }

    }


    public function newPasswordResturant(Request $request)
    {

        /** @var TYPE_NAME $validator */
        $validator = validator()->make($request->all(), [
            'rest_code' => 'required',
            'password' => 'required|confirmed',
            'email' => 'required',
        ]);

        if ($validator->fails()) {
            $data = $validator->errors();
            return apiResponse('0', $validator->errors()->first(), $data);
        }

        $restaurant = Resturant::where('rest_code', $request->rest_code)->where('rest_code', '!=', 0)->where('email', $request->email)->first();
        if ($restaurant) {
            $restaurant->password = bcrypt($request->password);
            $restaurant->rest_code = null;
            if ($restaurant->save()) {

                return apiResponse('1', 'تم تغيير كلمه المرور بنجاح', [
                    'api_token' => $restaurant->api_token,
                    'client' => $restaurant
                ]);
            } else {
                return apiResponse('0', 'حدث خطأ: برجاء المحاوله مره اخري', []);
            }
        } else {
            return apiResponse('0', 'هذا الكود غير صالح', []);
        }

    }


    public function profileResturant(Request $request)
    {

        $validator = validator()->make($request->all(), [
            'phone' => Rule::unique('resturants')->ignore($request->user()->id),
            'email' => Rule::unique('resturants')->ignore($request->user()->id),
            'password' => 'confirmed'
        ]);

        if ($validator->fails()) {
            $data = $validator->errors();
            return apiResponse('0', $validator->errors(), $data);
        }

        $userLogin = $request->user('resturant_api');

        $userLogin->resturant_name = $request->resturant_name;
        $userLogin->email = $request->email;
        $userLogin->delivery_time = $request->delivery_time;
        $userLogin->district_id = $request->district_id;
        $userLogin->password = $request->password;
        $userLogin->minimum_order = $request->minimum_order;
        $userLogin->delivery_charge = $request->delivery_charge;
        $userLogin->status = $request->status;
        $userLogin->phone = $request->phone;
        $userLogin->whatsapp = $request->whatsapp;
        if($request->hasFile('image')){
            Storage::disk('local')->delete('authRestaurant/' . $userLogin->image);
//            Storage::delete($category->image);

            $thumbnail = $request->file('image');
            $filename = time() . '.' . $thumbnail->getClientOriginalExtension();
            Image::make($thumbnail)->resize(100, 100)->save( public_path('/uploads/authRestaurant/' . $filename ) );
            $userLogin['image'] = $filename;
        }
        $userLogin->update();

        return apiResponse('1', 'تمت التعديل بنجاح', [
            'client' => $request->user('resturant_api')->fresh()->load('district')
        ]);
//        if ($request->has('password')){
//            $userLogin->password = bcrypt($request->password);
//        }


    }



    public function commentResturant(Request $request)
    {
        $Comments = $request->user('resturant_api')->comments()->get();
        if (count($Comments)== 0){
            return apiResponse('0', 'لا يوجد بيانات',$Comments);

        }else{
            return apiResponse('1', 'تم نجاح العمليه',$Comments);
        }
    }



    public function contactResturant(Request $request){

        $validator = validator()->make($request->all(), [

            'name' => 'required',
            'email' => 'required|email',
            'phone' => 'required',
            'content' => 'required',
            'type' => 'required',
        ]);

        if ($validator->fails()) {
            $data = $validator->errors();
            return apiResponse('0', $validator->errors()->first(), $data);
        }

        $contact = $request->user('resturant_api')->contacts()->create($request->all());

        return apiResponse('1', 'تمت الاضافه بنجاح',[
            'contact' => $contact
        ]);
    }

    public function registerToken(Request $request){

        $validator = validator()->make($request->all(), [
            'token' => 'required',
            'platform' =>'required|in:android,ios'

        ]);

        if ($validator->fails()) {
            $data = $validator->errors();
            return apiResponse('0', $validator->errors(), $data);
        }


        Token::where('token',$request->token)->delete();
        $token = $request->user('resturant_api')->tokens()->create($request->all());
        return apiResponse('1','تم اضافة التوكين بنجاح',$token);

    }


    public function removeToken(Request $request){

        $validator = validator()->make($request->all(), [
            'token' => 'required',
        ]);

        if ($validator->fails()) {
            $data = $validator->errors();
            return apiResponse('0', $validator->errors(), $data);
        }


        Token::where('token',$request->token)->delete();

        return apiResponse('1','تم الحذف بنجاح');

    }




}