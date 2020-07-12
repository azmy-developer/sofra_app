<?php

namespace App\Http\Controllers\Api\Client;

use App\Http\Controllers\Controller;
use App\Mail\ResetPassword;
use App\Models\Client;
use App\Models\Contact;
use App\Models\Offer;
use App\Models\Order;
use App\Models\Product;
use App\Models\Resturant;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use App\models\Token;
use Image;
Use \Carbon\Carbon;




class AuthClientController extends Controller
{
    /**
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function registerClient(Request $request)
    {
//        return $request->all();
        $validator = validator()->make($request->all(), [

            'username' => 'required',
            'image' => 'required',
            'email' => 'required|unique:resturants,email|email',
            'phone' => 'required|unique:resturants,phone',
            'district_id' => 'required|exists:districts,id',
            'password' => 'required|confirmed',
        ]);

        if ($validator->fails()) {
            $data = $validator->errors();
            return apiResponse('0', $validator->errors()->first(), $data);
        }
//        $request->merge(['password' => bcrypt($request->password)]);
        $client = Client::create($request->all());
        $client->api_token = Str::random(60);

        if($request->hasFile('image')){
            $thumbnail = $request->file('image');
            $filename = time() . '.' . $thumbnail->getClientOriginalExtension();
//            dd(public_path('uploads/' . $filename ));
            Image::make($thumbnail)->resize(300, 300)->save( public_path('/uploads/authClient/' . $filename ) );
            $client->image = $filename;

        };

        $client->save();
        return apiResponse('1', 'تم التسجيل العميل بنجاح', [

            'api_token' => $client->api_token,
            'restaurant' => $client

        ]);

    }


    /**
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function loginClient(Request $request)
    {

        $validator = validator()->make($request->all(), [
            'email' => 'required',
            'password' => 'required',
        ]);

        if ($validator->fails()) {
            $data = $validator->errors();
            return apiResponse('0', $validator->errors()->first(), $data);
        }

        $client = Client::where('email', $request->email)->first();
        if ($client) {
            if (Hash::check($request->password, $client->password)) {
                return apiResponse('1', 'تم التسجيل بنجاح', [
                    'api_token' => $client->api_token,
                    'client' => $client
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
    public function restPasswordClient(Request $request)
    {

        /** @var TYPE_NAME $validator */
        $validator = validator()->make($request->all(), [
            'email' => 'required',
        ]);

        if ($validator->fails()) {
            $data = $validator->errors();
            return apiResponse('0', $validator->errors()->first(), $data);
        }

        $client = Client::where('email', $request->email)->first();

        if ($client) {
            $code = rand(1111, 9999);

            $update = $client->update(['reset_code' => $code]);

            if ($update) {

                Mail::to($client->email)
                    ->bcc("azmy.abuzeid@hotmail.com")
                    ->send(new ResetPassword($client));

                return apiResponse('1', 'برجاء فحص هاتفك', [
                    'code' => $code,
                    'mail_fails' => Mail::failures(),
                    'email' => $client->email,
                ]);
            } else {
                return apiResponse('0', 'حدث خطأ: برجاء المحاوله مره اخري');
            }
        } else {
            return apiResponse('0', 'البيانات غير صحيحه');
        }

    }


    public function newPasswordClient(Request $request)
    {

        /** @var TYPE_NAME $validator */
        $validator = validator()->make($request->all(), [
            'reset_code' => 'required',
            'password' => 'required|confirmed',
            'email' => 'required',
        ]);

        if ($validator->fails()) {
            $data = $validator->errors();
            return apiResponse('0', $validator->errors()->first(), $data);
        }

        $client = Client::where('reset_code', $request->reset_code)->where('reset_code', '!=', 0)->where('email', $request->email)->first();
        if ($client) {
            $client->password = bcrypt($request->password);
            $client->reset_code = null;
            if ($client->save()) {

                return apiResponse('1', 'تم تغيير كلمه المرور بنجاح', [
                    'api_token' => $client->api_token,
                    'client' => $client
                ]);
            } else {
                return apiResponse('0', 'حدث خطأ: برجاء المحاوله مره اخري', []);
            }
        } else {
            return apiResponse('0', 'هذا الكود غير صالح', []);
        }

    }


    public function profileClient(Request $request)
    {

        $validator = validator()->make($request->all(), [
            'phone' => Rule::unique('clients')->ignore($request->user()->id),
            'email' => Rule::unique('clients')->ignore($request->user()->id),
            'password' => 'confirmed'
        ]);

        if ($validator->fails()) {
            $data = $validator->errors();
            return apiResponse('0', $validator->errors(), $data);
        }

        $userLogin = $request->user('client_api');

        $userLogin->username = $request->username;
        $userLogin->email = $request->email;
        $userLogin->district_id = $request->district_id;
        $userLogin->password = $request->password;
        $userLogin->phone = $request->phone;
        if($request->hasFile('image')){
            Storage::disk('local')->delete('authClient/' . $userLogin->image);
//            Storage::delete($category->image);

            $thumbnail = $request->file('image');
            $filename = time() . '.' . $thumbnail->getClientOriginalExtension();
            Image::make($thumbnail)->resize(100, 100)->save( public_path('/uploads/authClient/' . $filename ) );
            $userLogin['image'] = $filename;
        }
        $userLogin->update();

        return apiResponse('1', 'تمت التعديل بنجاح', [
            'client' => $request->user()->fresh()->load('distrct')
        ]);
//        if ($request->has('password')){
//            $userLogin->password = bcrypt($request->password);
//        }

    }


    public function offers(){
        $date = Carbon::now()->toDateString('y-m-d');
//        DB::enableQueryLog();
        $offers = Offer::where('to','>=',$date)->orderBy('from', 'DESC')->get();
//                dd(DB::getQueryLog());


        if (count($offers)== 0){
            return apiResponse('0', 'لا يوجد بيانات',$offers);

        }else{
            return apiResponse('1', 'تم نجاح العمليه',$offers);
        }
    }


    public function contactClient(Request $request){

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

        $contact = $request->user('client_api')->contacts()->create($request->all());

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
        $token = $request->user('client_api')->tokens()->create($request->all());
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