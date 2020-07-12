@component('mail::message')
    # Introduction

    <p> Sofra App Reset Password</p>

    Hello {{$restaurant->name}}

    {{--@component('mail::button', ['url' => '','color'=>'success'])--}}
    {{--Reset--}}
    {{--@endcomponent--}}

    <p>Your Reset Code Is : {{$restaurant->rest_code}}</p>

    Thanks,<br>
    {{ config('app.name') }}
@endcomponent
