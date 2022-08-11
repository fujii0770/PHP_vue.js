{{$receiver_name}} さん：
以下の文書の回覧が{{$user_name}} さんに引戻しされました。

件名: {{$mail_name}}
ファイル名:   
    @foreach($docs as $doc)
    ・ {{$doc}}
    @endforeach

コメント:
@foreach(explode('\r\n', $pullback_remark) as $pullback_remark)
    {{$pullback_remark}}
@endforeach

--------------------------

{{\App\Http\Utils\AppUtils::getMailLoginUrlLabel($env_app_url)}}: {{$env_app_url}}
    
©2020 Shachihata Inc.: http://www.shachihata.co.jp
プライバシーポリシー: https://www.shachihata.co.jp/policy/index.php            

