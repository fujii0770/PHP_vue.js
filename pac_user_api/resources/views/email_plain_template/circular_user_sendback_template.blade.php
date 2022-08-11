{{$receiver_name}} さん：
{{$return_user}} さんから以下の回覧文書が差戻しされました。

件名：
{{$mail_name}}

メッセージ：
@foreach(explode("\n", $text) as $text)
    {{$text}}
@endforeach

ファイル名：
@foreach($filenames as $filename)
    {{$filename}}
@endforeach

作成者：
@if($author_email)
{{$author_email}}
@endif



最終更新者：
@if($last_updated_email)
{{$last_updated_email}}
@endif

@if(isset($hide_circular_approval_url) && !$hide_circular_approval_url)
回覧文書をみる : {{$circular_approval_url}}
@endif

Shachihata Cloudを利用して、回覧文書を確認・捺印することができます。
リンクをクリックするとShachihata Cloudの画面に移動します。
@if(!$hide_thumbnail_flg)
{{ $message->embed($image_path) }}
@endif


{{\App\Http\Utils\AppUtils::getMailLoginUrlLabel($env_app_url)}} : {{$env_app_url}}

この電子メールの内容をほかの人と共有しないでください
メールに記載された文書へのリンクを用いて、
Shachihata Cloudの文書にアクセスが可能です。
 他の人に見られることがないように、メールの転送、
および文書へのリンクの転記は控えてください。

©2020 Shachihata Inc. : http://www.shachihata.co.jp
プライバシーポリシー : https://www.shachihata.co.jp/policy/index.php