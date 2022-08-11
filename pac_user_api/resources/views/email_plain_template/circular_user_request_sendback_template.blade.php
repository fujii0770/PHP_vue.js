{{$receiver_name}} さん：
{{$return_requester}} さんから以下の回覧文書の差戻し依頼が届いています。

件名：
{{$mail_name}}

メッセージ：
{{$text}}

ファイル名：
@foreach($filenames as $filename)
{{$filename}}
@endforeach

@if(isset($hide_circular_approval_url) && !$hide_circular_approval_url)
回覧文書をみる: {{$circular_approval_url}}
@endif

Shachihata Cloudを利用して、回覧文書を確認・捺印することができます。
リンクをクリックするとShachihata Cloudの画面に移動します。
@if(!$hide_thumbnail_flg)
{{ $message->embed($image_path) }}
@endif

{{\App\Http\Utils\AppUtils::getMailLoginUrlLabel($env_app_url)}}: {{$env_app_url}}

この電子メールの内容をほかの人と共有しないでください
メールに記載された文書へのリンクを用いて、
Shachihata Cloudの文書にアクセスが可能です。
他の人に見られることがないように、メールの転送、
および文書へのリンクの転記は控えてください。

©2020 Shachihata Inc. : http://www.shachihata.co.jp
プライバシーポリシー : https://www.shachihata.co.jp/policy/index.php