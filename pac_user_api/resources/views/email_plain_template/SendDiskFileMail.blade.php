@extends('../layouts_plain.email')

@section('content')
{{$name}} さん （{{$email}}） からファイルメール便が届いています。

件名：{{$title}}
メッセージ：
@foreach($mail_text as $mail_text_line)
    {{$mail_text_line}}
@endforeach
ファイル名：
@foreach($file_names as $file_name)
    {{$file_name}}
@endforeach

ダウンロードリンク : {{$download_link}}

@endsection