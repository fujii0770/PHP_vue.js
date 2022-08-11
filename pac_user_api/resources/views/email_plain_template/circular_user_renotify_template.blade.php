@extends('../layouts_plain.email')

@section('content')

{{$receiver_name}} さん：
{{$creator_name}} さんから以下の回覧文書が届いています。

件名：
{{$mail_name}}

メッセージ：
@foreach(explode("\n", $text) as $text)
  {{$text}}
@endforeach

ファイル名：
@foreach($filenames as $filename)
・{{$filename}}
@endforeach

@if(isset($hide_circular_approval_url) && !$hide_circular_approval_url)
回覧文書をみる: {{$circular_approval_url}}
@endif

Shachihata Cloudを利用して、回覧文書を確認・捺印することができます。
回覧文書へのリンクをクリックするとShachihata Cloudの画面に移動します。
@if(!$hide_thumbnail_flg)
{{ $message->embed($image_path) }}
@endif

※この電子メールの内容を他の人と共有しないでください。

この電子メールに記載された文書へのリンクを用いて、
Shachihata Cloud上の文書にアクセスが可能です。
他の人に見られることがないように、この電子メールの転送、
およびリンクの転記は控えてください。

@endsection