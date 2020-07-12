<?php

namespace App\Http\Controllers\Api\Resturant;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Client;
use App\Models\Comment;
use App\Models\Offer;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Image;

class MainResturantController extends Controller
{
    public function addCategory(Request $request){

        $validator = validator()->make($request->all(),[

            'name' => 'required',
            'image' => 'required',

        ]);
        if ($validator->fails()) {
            $data = $validator->errors();
            return apiResponse('0', $validator->errors(), $data);
        }

        $category = new Category();
        $category->name = $request->name;
        if($request->hasFile('image')){
            $thumbnail = $request->file('image');
            $filename = time() . '.' . $thumbnail->getClientOriginalExtension();
//            dd(public_path('uploads/' . $filename ));
            Image::make($thumbnail)->resize(300, 300)->save( public_path('/uploads/category/' . $filename ) );
            $category->image = $filename;

        };
        $category->save();

        return apiResponse('1', 'تم الارسال بنجاح',[
            'category' => $category
        ]);


    }


    public function editCategory(Request $request){

        $validator = validator()->make($request->all(),[

            'name' => 'required',
            'image' => 'required',

        ]);
        if ($validator->fails()) {
            $data = $validator->errors();
            return apiResponse('0', $validator->errors(), $data);
        }

        $category = Category::findOrFail($request->id);
        $category->name = $request->name;

        if($request->hasFile('image')){
            Storage::disk('local')->delete('category/' . $category->image);
//            Storage::delete($category->image);

            $thumbnail = $request->file('image');
            $filename = time() . '.' . $thumbnail->getClientOriginalExtension();
            Image::make($thumbnail)->resize(100, 100)->save( public_path('/uploads/category/' . $filename ) );
            $category['image'] = $filename;

        }
        $category->save();

        return apiResponse('1', 'تم الارسال بنجاح',[
            'category' => $category
        ]);


    }


    public function deleteCategory(Request $request)
    {
        $record = Category::findorfail($request->id);
        if (!$record) {
            return apiResponse('0','تعذر الحصول على البيانات');
        }

        if ($record->products()->count())
            return apiResponse('0','لا يمكن الحذف, يوجد منتجات مرتبطة بالاقسام');

        $record->delete();
        Storage::disk('local')->delete('category/' . $record->image);
        return apiResponse('1', 'تم الحذف بنجاح');


    }


    public function addProduct(Request $request){

        $validator = validator()->make($request->all(),[

            'image' => 'required',
            'name' => 'required',
            'details' => 'required',
            'price' => 'required',
            'price_offer' => 'required',
            'category_id' => 'required|exists:categories,id',

        ]);
        if ($validator->fails()) {
            $data = $validator->errors();
            return apiResponse('0', $validator->errors(), $data);
        }

        $product = new Product();
        $product->name = $request->name;
        if($request->hasFile('image')){
            $thumbnail = $request->file('image');
            $filename = time() . '.' . $thumbnail->getClientOriginalExtension();
//            dd(public_path('uploads/' . $filename ));
            Image::make($thumbnail)->resize(300, 300)->save( public_path('uploads/products/' . $filename ) );
            $product->image = $filename;

        };
        $product->details = $request->details;
        $product->price = $request->price;
        $product->price_offer = $request->price_offer;
        $product->category_id = $request->category_id;


        $product->save();

        return apiResponse('1', 'تم الارسال بنجاح',[
            'product' => $product
        ]);


    }


    public function editProduct(Request $request){

        $validator = validator()->make($request->all(),[

            'image' => 'required',
            'name' => 'required',
            'details' => 'required',
            'price' => 'required',
            'price_offer' => 'required',
            'category_id' => 'required|exists:categories,id',

        ]);
        if ($validator->fails()) {
            $data = $validator->errors();
            return apiResponse('0', $validator->errors(), $data);
        }

        $product = Product::findOrFail($request->id);
        $product->name = $request->name;

        if($request->hasFile('image')){
            Storage::disk('local')->delete('products/' . $product->image);
//            Storage::delete($category->image);

            $thumbnail = $request->file('image');
            $filename = time() . '.' . $thumbnail->getClientOriginalExtension();
            Image::make($thumbnail)->resize(100, 100)->save( public_path('/uploads/products/' . $filename ) );
            $product['image'] = $filename;

        }
        $product->details = $request->details;
        $product->price = $request->price;
        $product->price_offer = $request->price_offer;
        $product->category_id = $request->category_id;
        $product->save();

        return apiResponse('1', 'تم الارسال بنجاح',[
            'product' => $product
        ]);

    }


    public function deleteProduct(Request $request)
    {
        $record = Product::findorfail($request->id);
        if (!$record) {
            return apiResponse('0','تعذر الحصول على البيانات');
        }


        $record->delete();
        Storage::disk('local')->delete('products/' . $record->image);
        return apiResponse('1', 'تم الحذف بنجاح');


    }



    public function addOffers(Request $request){

        $validator = validator()->make($request->all(),[

            'name' => 'required',
            'details' => 'required',
            'from' => 'required',
            'to' => 'required',
            'image' => 'required',
        ]);
        if ($validator->fails()) {
            $data = $validator->errors();
            return apiResponse('0', $validator->errors(), $data);
        }

        $offer = new Offer();
        $offer->name = $request->name;

        if($request->hasFile('image')){
            $thumbnail = $request->file('image');
            $filename = time() . '.' . $thumbnail->getClientOriginalExtension();
//            dd(public_path('uploads/' . $filename ));
            Image::make($thumbnail)->resize(300, 300)->save( public_path('uploads/offer/' . $filename ) );
            $offer->image = $filename;

        };
        $offer->details = $request->details;
        $offer->from = $request->from;
        $offer->to = $request->to;

        $offer->save();

        return apiResponse('1', 'تم الارسال بنجاح',[
            'offers' => $offer
        ]);


    }


    public function editOffers(Request $request){

        $validator = validator()->make($request->all(),[

            'image' => 'required',
            'name' => 'required',
            'details' => 'required',
            'from' => 'required|date',
            'to' => 'required|date',

        ]);
        if ($validator->fails()) {
            $data = $validator->errors();
            return apiResponse('0', $validator->errors(), $data);
        }

        $offer = Offer::findOrFail($request->id);
        $offer->name = $request->name;

        if($request->hasFile('image')){
            Storage::disk('local')->delete('offer/' . $offer->image);
//            Storage::delete($category->image);

            $thumbnail = $request->file('image');
            $filename = time() . '.' . $thumbnail->getClientOriginalExtension();
            Image::make($thumbnail)->resize(100, 100)->save( public_path('/uploads/offer/' . $filename ) );
            $offer['image'] = $filename;

        }
        $offer->details = $request->details;
        $offer->from = $request->from;
        $offer->to = $request->to;

        $offer->save();

        return apiResponse('1', 'تم الارسال بنجاح',[
            'offer' => $offer
        ]);

    }


    public function deleteOffers(Request $request)
    {
        $record = Offer::findorfail($request->id);
        if (!$record) {
            return apiResponse('0','تعذر الحصول على البيانات');
        }


        $record->delete();
        Storage::disk('local')->delete('offer/' . $record->image);
        return apiResponse('1', 'تم الحذف بنجاح');


    }

    public function newOrder(Request $request)

    {

        $orders = $request->user()->orders()->where('order_status', 'pending')->with('client')->get();


        if (count($orders)) {

            return responseJson(1, 'success', $orders);

        } else {

            return responseJson(0, 'لا يوجد طلبات');

        }

    }



    public function currentOrder(Request $request)

    {

        $orders = $request->user()->orders()->where('order_status', 'accepted')->with('resturant')->get();



        if (count($orders)) {

            return responseJson(1, 'success', $orders);

        } else {

            return responseJson(0, 'لا يوجد طلبات');

        }

    }



    public function lastOrder(Request $request)

    {

        $orders = $request->user()->orders()->where('order_status', 'delivered')

            ->orWhere('order_status', 'rejected')

            ->orWhere('order_status', 'declined')

            ->with('resturant')->get();



        if (count($orders)) {

            return responseJson(1, 'success', $orders);

        } else {

            return responseJson(0, 'لا يوجد طلبات');

        }

    }



    public function acceptOrder(Request $request)

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

            $client = Client::find($order->client_id);

//                dd($client);



            $order->update([

                'order_status' => 'accepted',

                'reason_of_rejection'=>''

            ]);



            $notification = $client->notifications()->create([

                'title' => 'تمت الموافقه علي طلبك',

                'content' => $order->resturant->name . ' تمت الموافقه علي طلبك من المطعم ',

                'order_id' => $order->id

            ]);



//            dd($notification);



            $token = $client->tokens()->where('token', '!=', '')->pluck('token')->toArray();



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





    public function rejectOrder(Request $request)

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



        if ($order) {

            $client = Client::find($order->client_id);

//                dd($client);

            $order->update([

                'state' => 'rejected',

                'reason_of_rejection'=> ' المطعم ليس لديه طلبك حاليا'

            ]);



            $notification = $client->notifications()->create([

                'title' => 'تم رفض طلبك',

                'content' => $order->resturant->name . ' تمت رفض طلبك من المطعم ',

                'order_id' => $order->id

            ]);



//            dd($notification);



            $token = $client->tokens()->where('token', '!=', '')->pluck('token')->toArray();



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
