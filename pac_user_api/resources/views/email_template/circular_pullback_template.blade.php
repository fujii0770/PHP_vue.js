<div style="font-family: Roboto,RobotoDraft,Helvetica,Arial,sans-serif">
    <div style="width:100%;border-bottom:solid 1px gray;background-color:#107fcd">
        <img src="{{ $message->embed(public_path('logo.png')) }}" style="border-width:0px" class="CToWUd">
    </div>
    <div style="padding:16px 8px;background-color:white">
        <p>{{$receiver_name}} さん：</p>
        <p>以下の文書の回覧が{{$user_name}} さんに引戻しされました。</p>

        <table border="0" cellpadding="0" cellspacing="0" style="width:100%">
            <tr>
                <td style="width: 100px">件名</td>
                <td style="width: 20px">：</td>
                <td>{{$mail_name}}</td>
            </tr>
            <tr>
                <td style="width: 100px;vertical-align: text-top">ファイル名</td>
                <td style="width: 20px;vertical-align: text-top">：</td>
                <td style="text-align: left">
                    @foreach($docs as $doc)
                        {{$doc}}<br/>
                    @endforeach
                </td>
            </tr>
            <tr>
                <td style="width: 100px;vertical-align: text-top">コメント</td>
                <td style="width: 20px;vertical-align: text-top">：</td>
                <td style="text-align: left">
                    @foreach(explode('\r\n', $pullback_remark) as $pullback_remark)
                        {{$pullback_remark}}<br/>
                    @endforeach
                </td>
            </tr>
        </table>

        <table border="0" cellpadding="0" cellspacing="0" style="width:100%">
            <tbody>
            <tr>
                <td style="width:20%"></td>
                <td class="m_-6849224559476667371link-button" style="min-width: 140px; border: 1px solid whitesmoke; border-radius: 8px; text-align: center; vertical-align: middle; background-color: #107fcd;">
                    <a style="color:#fff;text-decoration: none;display: block;margin:8px 20px;" href="{{$env_app_url}}" class="m_-6849224559476667371link-button" target="_blank" data-saferedirecturl="">{{\App\Http\Utils\AppUtils::getMailLoginUrlLabel($env_app_url)}}</a></td>
                <td style="width:20%"></td>
            </tr>
            </tbody>
        </table>
        <p>&nbsp;</p>
    </div>
    <table id="m_-6849224559476667371mail-footer" style="width: 100%" cellpadding="0" cellspacing="0" border="0">
        <tbody>
        <tr>
            <td style="background-color:#107fcd; padding: 4px;">
                <a style="color:#ffffff;padding:4px 8px; text-decoration: none;" href="http://www.shachihata.co.jp" target="_blank" data-saferedirecturl="">©2020&nbsp;Shachihata Inc.</a>&nbsp; &nbsp;
                <a style="color:#ffffff;padding:4px 8px; text-decoration: none;" href="https://www.shachihata.co.jp/policy/index.php" target="_blank" data-saferedirecturl="">プライバシーポリシー</a>&nbsp; &nbsp;
                <!--<a style="color:#ffffff;padding:4px 8px; text-decoration: none;" href="https://estamp.shachihata.co.jp/tou/" target="_blank" data-saferedirecturl="">会員規約</a>-->
            </td>
        </tr>
        </tbody>
    </table>
    <div class="yj6qo"></div>
    <div class="adL">
    </div>
</div>