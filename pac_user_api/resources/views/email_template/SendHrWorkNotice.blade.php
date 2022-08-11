@extends('../layouts.email')

@section('content')
    <div style="padding:16px 8px;background-color:white">
        [送信者]: {{$name}}<br/>
        [送信者メールアドレス]: {{$email}}<br/>
            <br/>
        {!! $text !!}<br/>
            <br/>
        {!! $signature !!}
    </div>
@endsection
