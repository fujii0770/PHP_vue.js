<div style="font-family: Roboto,RobotoDraft,Helvetica,Arial,sans-serif">
    <div style="width:100%;border-bottom:solid 1px gray;background-color:#107fcd">
        <img src="{{ $message->embed(public_path('logo.png')) }}" style="border-width:0px" class="CToWUd">
    </div>
    <div style="padding:16px 8px;background-color:white">
        <p>{{$receiver_name}} さん：</p>
        <p>以下の文書をスキップされました。</p>

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
        </table>
    </div>

    <table style="width: 100%; text-align: center">
        <tbody>
        <tr>
            <td style="width:20%"></td>
            <td style="width:60%;margin: 0 auto;  background-color: #107fcd; min-width: 140px; padding: 8px 20px; border-radius: 8px; text-align: center; text-decoration: none;">
                <a style="color: white; " href="{{ config('app.new_app_url') }}">ログイン画面に移動</a>
            </td>
            <td style="width:20%"></td>
        </tr>
        </tbody>
    </table>
    <p></p>
    <p></p>
    <table id="m_-6849224559476667371mail-footer" style="width: 100%" cellpadding="0" cellspacing="0" border="0">
        <tbody>
        <tr>
            <td style="background-color:#107fcd; padding: 4px;">
                <a style="color:#ffffff;padding:4px 8px; text-decoration: none;" href="http://www.shachihata.co.jp" target="_blank" data-saferedirecturl="">©2020&nbsp;Shachihata Inc.</a>&nbsp; &nbsp;
                <a style="color:#ffffff;padding:4px 8px; text-decoration: none;" href="https://www.shachihata.co.jp/policy/index.php" target="_blank" data-saferedirecturl="">プライバシーポリシー</a>&nbsp; &nbsp;
            </td>
        </tr>
        </tbody>
    </table>
    <div class="yj6qo"></div>
    <div class="adL">
    </div>
</div>