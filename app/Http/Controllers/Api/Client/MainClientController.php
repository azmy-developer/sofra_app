<?php

namespace App\Http\Controllers\Api\Client;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\City;
use App\Models\District;
use App\Models\Product;
use App\Models\Resturant;
use App\Models\Setting;
use App\Models\Token;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class MainClientController extends Controller
{


    public function restaurants(Request $request){
        DB::enableQueryLog();
        $Restaurant = Resturant::where(function ($query) use ($request){
            if ($request->has('status')){
            $query->where('status',$request->status);
            }else{
                $query->where('status','open');
            }
            if ($request->has('city_id')){
                $query->whereHas('districts', function($q)
                {
                    $q->where('districts.id', '=', 'resturant.district_id');
                });
            }

            if ($request->has('resturant_name')){
                $query->where('resturant_name','LIKE', '%' . $request->resturant_name . '%');
            }
        })->get();
        dd(DB::getQueryLog());
        if (count($Restaurant)== 0){
            return apiResponse('0', 'لا يوجد بيانات',$Restaurant);

        }else{
            return apiResponse('1', 'تم نجاح العمليه',$Restaurant);
        }
    }


    public function categories(){

        $Categories = Category::paginate(25);
        if (count($Categories)== 0){
            return apiResponse('0', 'لا يوجد بيانات',$Categories);

        }else{
            return apiResponse('1', 'تم نجاح العمليه',$Categories);
        }
    }

    public function productsBycategory(Request $request){

        $products = Product::where('category_id',$request->category_id);
        if (count($products)== 0){
            return apiResponse('0', 'لا يوجد بيانات',$products);

        }else{
            return apiResponse('1', 'تم نجاح العمليه',$products);
        }
    }

    public function addComment(Request $request)

    {

        $validator = validator()->make($request->all(),

            [

                'comment' => 'required',

                'status' => 'required|in:1,2,3,4,5',

                'resturant_id' => 'required|exists:resturants,id',

                'client_id' => 'required|exists:clients,id',

            ]);



        if ($validator->fails()) {

            $data = $validator->errors();

            return responseJson(0, $validator->errors(), $data);

        }



        $review = $request->user()->reviews()->create($request->all());





        if ($review) {

            return responseJson(1, 'تم اضافة التعليق بنجاح', $review);

        } else {

            return responseJson(0, 'حدث خطأ ، برجاء المحاوله مره أخري');

        }

    }



    public function clientOrders(Request $request){

        $validator = validator()->make($request->all(), [

            'resturant_id' => 'required|exists:resturants,id',
            'products.*.product_id' => 'required|exists:products,id',
            'products.*.quantity' => 'required',
            'address' => 'required',
            'payment_method_id' => 'required|exists:methods_payment,id',

        ]);

        if ($validator->fails()) {
            $data = $validator->errors();
            return apiResponse('0', $validator->errors()->first(), $data);
        }

        $restaurant = Resturant::find($request->resturant_id);

        if ($restaurant->status == 'closed'){
            return apiResponse(0 ,'عذرا هذا المطعم مغلق');
        }


        $order = $request->user()->orders()->create([

            'resturant_id' => $request->resturant_id,
            'notes' => $request->notes,
            'order_status' => $request->order_status,
            'address' => $request->address,
            'payment_method_id' => $request->payment_method_id,
        ]);

        $cost = 0 ;
        $delivery_cost = $restaurant->delivery_charge;

        foreach ($request->products as $i) {

            $product = Product::Find($i['product_id']);

            $readyProduct = [
                $i['product_id'] => [
                    'quantity' => $i['quantity'],
                    'price' => $product['price'],
                ]
            ];

            $order->products()->attach($readyProduct);
            $cost += ($product->price*$i['quantity']);
        }
        //minimum charge
        if ($cost >= $restaurant->minimum_order){

            $total = $cost + $delivery_cost;
            $commission = settings()->commission *$cost / 100;
            $net = $total - settings()->commission ;

            $update = $order->update([
                'cost' => $cost,
                'delivery_cost' => $delivery_cost,
                'total_commission' => $commission,
                'total' => $total,
                'net' => $net,
            ]);


            $notif = $restaurant->notifications()->create([
                'title' => 'لديك طلب جديد',
                'title_en' => 'you have new order',
                'content' => 'لديك طلب جديد من العميل' .$request->user()->name,
                'content_en' => 'you have new order' .$request->user()->name,
                'order_id' => $order->id
            ]);

            $tokens = $restaurant->tokens()->where('tokens','!=','')->pluck('token')->toArray();

            if (count($tokens)){

                $title   = $notif->title;
                $content = $notif->content;
                $data    = [
                    'order_id' => $order->id
                ];

                $send = notifyByFirebase($title,$content,$tokens,$data);
                info("firebase result : " . $send);

            }

            return apiResponse("1" , "تم الطلب بنجاح" , $send);

        }else{

            $order->products()->delete();
            $order->delete();
            return apiResponse("0" , 'لابد ان يكون الطلب اقل من'.$restaurant->delivery_charge);

        }

    }

    public function orderDetails(Request $request)

    {



        $validator = validator()->make($request->all(),

            [

                'order_id' => 'required|exists:orders,id'

            ]);



        if ($validator->fails()) {

            $data = $validator->errors();

            return responseJson(0, $validator->errors(), $data);

        }



        $order = $request->user()->orders()->with('resturant')->where('id', $request->order_id)->get();



        return responseJson(1, 'success', $order);

    }



    public function newOrder(Request $request)

    {

        $orders = $request->user()->orders()->where('order_status', 'pending')->with('resturant')->get();



        if (count($orders)) {

            return responseJson(1, 'success', $orders);

        } else {

            return responseJson(0, 'لا يوجد طلبات');

        }

    }



    public function currentOrder(Request $request)

    {

        $orders = $request->user()->orders()->where('order_status' , 'accepted')->with('resturant')->get();



        if (count($orders)) {

            return responseJson(1, 'success', $orders);

        } else {

            return responseJson(0, 'لا يوجد طلبات');

        }

    }



    public function lastOrder(Request $request)

    {

        $orders = $request->user()->orders()->where('order_status', 'delivered')

            ->orWhere('order_status' , 'rejected')

            ->orWhere('order_status' , 'declined')

            ->with('resturant')->get();



        if (count($orders)) {

            return responseJson(1, 'success', $orders);

        } else {

            return responseJson(0, 'لا يوجد طلبات');

        }

    }



    public function deliverOrder(Request $request)

    {

        $validator = validator()->make($request->all(),

            [

                'order_id' => 'required|exists:orders,id'

            ]);



        if ($validator->fails()) {

            $data = $validator->errors();

            return responseJson(0, $validator->errors(), $data);

        }



        $order = $request->user()->orders()->where('id', $request->order_id)->where('order_status', 'pending')->first();

//        dd($order);



        if ($order) {

            $resturant = Resturant::find($order->client_id);

//                dd($resturant);



            $order->update([

                'state' => 'delivered',

                'reason_of_rejection'=>''

            ]);



            $notification = $resturant->notifications()->create([

                'title' => 'تم توصيل الطلب الي العميل بنجاح',

                'content' => $order->client->name.'تم توصيل الطلب بنجاح الي العميل ',

                'order_id' => $order->id

            ]);



//            dd($notification);



            $token = $resturant->tokens()->where('token', '!=', '')->pluck('token')->toArray();



            if (count($token)) {

                $title = $notification->title;

                $body = $notification->content;

                $data = [

                    'order_id' => $order->id

                ];



                $send = notifyByFirebase($title, $body, $token, $data);

                info("firebase result :" . $send);

//                    dd($send);

            }



            return responseJson(1, 'تم التعديل بنجاح', $order);



        } else {

            return responseJson(0, 'عفوا حالة الطلب ليست معلقه');

        }

    }



    public function declineOrder(Request $request)

    {

        $validator = validator()->make($request->all(),

            [

                'order_id' => 'required|exists:orders,id'

            ]);



        if ($validator->fails()) {

            $data = $validator->errors();

            return responseJson(0, $validator->errors(), $data);

        }



        $order = $request->user()->orders()->where('id', $request->order_id)->where('order_status', 'pending')->first();

//        dd($order);



        if ($order) {

            $resturant = Resturant::find($order->client_id);

//                dd($resturant);



            $order->update([

                'state' => 'declined',

                'reason_of_rejection'=>''

            ]);



            $notification = $resturant->notifications()->create([

                'title' => 'تم توصيل الطلب الي العميل بنجاح',

                'content' => $order->client->name.'تم توصيل الطلب بنجاح الي العميل ',

                'order_id' => $order->id

            ]);



//            dd($notification);



            $token = $resturant->tokens()->where('token', '!=', '')->pluck('token')->toArray();



            if (count($token)) {

                $title = $notification->title;

                $body = $notification->content;

                $data = [

                    'order_id' => $order->id

                ];



                $send = notifyByFirebase($title, $body, $token, $data);

                info("firebase result :" . $send);

//                    dd($send);

            }



            return responseJson(1, 'تم التعديل بنجاح', $order);



        } else {

            return responseJson(0, 'عفوا حالة الطلب ليست معلقه');

        }

    }



    public function notificationList(Request $request)

    {

        $notifications = $request->user()->notifications()->get();

        if (count($notifications))

        {

            return responseJson(1 , 'success' , $notifications);

        }else{

            return responseJson(0 ,' لا يوجد اي اشعارات خاصه بك حاليا');

        }

    }



    public function notificationUpdate(Request $request)

    {

        $validator = validator()->make($request->all(),

            [

                'notification_id' => 'required|exists:notifications,id'

            ]);



        if ($validator->fails()) {

            $data = $validator->errors();

            return responseJson(0, $validator->errors(), $data);

        }



        $notification = $request->user()->notifications()->find($request->notification_id);



        if (!isset($notification))

        {

            return responseJson(0,'حدث خطأ ما');

        }



        $notification->is_read = 1;

        $notification->save();



        return responseJson(1,'تم التحديث ');

    }

}
