@extends('../layouts_plain.email')

@section('content')
{{$creator_name}} さんの回覧文書が届いています。

ファイル名：
@foreach($filenames as $filename)
        {{$filename}}
@endforeach
メッセージ：
@foreach(explode("\n", $text) as $text)
        {{$text}}
@endforeach

@if(isset($hide_circular_approval_url) && !$hide_circular_approval_url)

回覧文書をみる : {{$circular_approval_url}}

@endif

Shachihata Cloudを利用して、回覧文書を確認・捺印することができます。
回覧文書へのリンクをクリックするとShachihata Cloudの画面に移動します。


@if(!$hide_thumbnail_flg)
        @if(isset($hide_circular_approval_url) && $hide_circular_approval_url)
                {{ $message->embed($image_path) }}
        @else
                {{ $message->embed($image_path) }}
        @endif
@endif

{{\App\Http\Utils\AppUtils::getMailLoginUrlLabel($env_app_url)}} : {{$env_app_url}}

※この電子メールの内容を他の人と共有しないでください。

この電子メールに記載された文書へのリンクを用いて、
Shachihata Cloud上の文書にアクセスが可能です。
 他の人に見られることがないように、この電子メールの転送、
およびリンクの転記は控えてください。

@endsection
