<?php

use App\Models\Setting;

/**
 * Created by PhpStorm.
 * User: azmy
 * Date: 09-May-20
 * Time: 4:00 AM
 */

function apiResponse($status,$message,$data = null){

    $response = [
        'status' => $status,
        'message' => $message,
        'data' => $data
    ];

    return response()->json($response);

};

function settings(){

    $settings = Setting::find(1);
    if ($settings){
        return $settings;
    }else{
        return new \App\Models\Setting;
    }

};

/**
 * @param $title
 * @param $body
 * @param $tokens
 * @param array $data
 * @param string $type
 * @param bool $is_notification
 * @return mixed
**/

function notifyByFirebase($title, $body, $tokens, $data = [], $is_notification = true)
{

    $registrationIDs = $tokens;

    $fcmMsg = array(
        'body' => $body,
        'title' => $title,
        'sound' => "default",
        'color' => "#203E78"
    );
    $fcmFields = array(
        'registration_ids' => $registrationIDs,
        'priority' => 'high',
        'data' => $data
    );
    if ($is_notification)
    {
        $fcmFields['notification'] = $fcmMsg;
    }

    $headers = array(
        'Authorization: key=' . env('API_ACCESS_KEY'),
        'Content-Type: application/json'
    );

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, 'https://fcm.googleapis.com/fcm/send');
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($fcmFields));
    $result = curl_exec($ch);
    curl_close($ch);
    return $result;
}

?>