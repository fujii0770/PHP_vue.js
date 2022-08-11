@extends('../layouts_plain.email')

@section('content')
[送信者]: {{$name}}
[送信者メールアドレス]: {{$email}}

{{$text}}

{{$signature}}
@endsection
