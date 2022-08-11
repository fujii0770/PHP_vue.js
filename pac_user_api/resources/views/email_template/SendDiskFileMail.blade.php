<div style="font-family: Roboto,RobotoDraft,Helvetica,Arial,sans-serif">
    <div style="width:100%;border-bottom:solid 1px gray;background-color:#107fcd">
        <img src="{{ $message->embed(public_path('logo.png')) }}" style="border-width:0px" class="CToWUd">
    </div>
    @if($top_advertisement)
    <div class="top-advertisement" style="text-align: center;">
        <a href="{{ $top_advertisement['url'] }}" style="text-align: center;"><img src="{{ $message->embed($top_advertisement['path']) }}" style="width: 100%;" class="CToWUd"></a>
    </div>
    @endif

    @if($middle_advertisement)
        <div style="padding: 30px 6px;background-color:white;display: flex;">
    @else
        <div>
    @endif
        <div class="email-content" style="width: 100%;margin-top: 133px;">
            <p>{{$name}} さん （{{$email}}） からファイルメール便が届いています。</p>

            <table border="0" cellpadding="0" cellspacing="0" style="width:100%">
                <tr>
                    <td style="width: 100px">件名</td>
                    <td style="width: 20px">：</td>
                    <td>{{$title}}</td>
                </tr>
                <tr>
                    <td style="width: 100px;vertical-align: text-top">メッセージ</td>
                    <td style="width: 20px;vertical-align: text-top">：</td>
                    <td style="text-align: left">
                        @foreach($mail_text as $mail_text_line)
                            {{$mail_text_line}}<br/>
                        @endforeach
                    </td>
                </tr>
                <tr>
                    <td style="width: 100px;vertical-align: text-top">ファイル名</td>
                    <td style="width: 20px;vertical-align: text-top">：</td>
                    <td style="text-align: left">
                        @foreach($file_names as $file_name)
                            {{$file_name}}<br/>
                        @endforeach
                    </td>
                </tr>
            </table>

            <table border="0" cellpadding="0" cellspacing="0" style="width:100%">
                <tbody>
                <tr>
                    <td style="width:20%"></td>
                    <td class="m_-6849224559476667371link-button" style="min-width: 140px;border: 1px solid whitesmoke;border-radius: 8px;text-align: center;vertical-align: middle;background-color: #107fcd;">
                        <a style="color:#fff;text-decoration: none;display: block;margin: 8px 20px" href="{{$download_link}}" target="_blank" class="m_-6849224559476667371link-button">ダウンロード</a></td>
                    <td style="width:20%"></td>
                </tr>
                </tbody>
            </table>
            <br>
        </div>
        @if($middle_advertisement)
        <div class="middle-advertisement"  style="align-self: center;text-align: end;">
            <a href="{{ $middle_advertisement['url'] }}" style="text-align: center;"><img src="{{ $message->embed($middle_advertisement['path']) }}" style="width: 280px;" class="CToWUd"></a>
        </div>
        @endif
    </div>
    @if($end_advertisement)
    <div class="top-advertisement" style="text-align: center;">
        <a href="{{ $end_advertisement['url'] }}" style="text-align: center;"><img src="{{  $message->embed($end_advertisement['path']) }}" style="width: 100%;" class="CToWUd"></a>
    </div>
    @endif
    <table id="m_-6849224559476667371mail-footer" style="width: 100%" cellpadding="0" cellspacing="0" border="0">
        <tbody>
        <tr>
            <td style="background-color:#107fcd;">
                <a style="color:#ffffff;padding:4px 8px" href="http://www.shachihata.co.jp" target="_blank" data-saferedirecturl="">©2020&nbsp;Shachihata Inc.</a>&nbsp; &nbsp;
                <a style="color:#ffffff;padding:4px 8px" href="https://www.shachihata.co.jp/policy/index.php" target="_blank" data-saferedirecturl="">プライバシーポリシー</a>&nbsp; &nbsp;
            </td>
        </tr>
        </tbody>
    </table>
    <div class="yj6qo"></div>
    <div class="adL">
    </div>
</div>