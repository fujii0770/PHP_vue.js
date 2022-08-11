@extends('../layouts.email')

@section('content')
    <div style="padding:16px 8px;background-color:white">
        <p>{{$receiver_name}} さん：</p>
        <p>{{$creator_name}} さんから以下の回覧文書が届いています。</p>

        <table border="0" cellpadding="0" cellspacing="0" style="width:100%">
            <tr>
                <td style="width: 100px">件名</td>
                <td style="width: 20px">：</td>
                <td>{{$mail_name}}</td>
            </tr>
            <tr>
                <td style="width: 100px;vertical-align: text-top">メッセージ</td>
                <td style="width: 20px;vertical-align: text-top">：</td>
                <td>
                    @foreach(explode("\n", $text) as $text)
                        {{$text}}<br/>
                    @endforeach
                </td>
            </tr>
            <tr>
                <td style="width: 100px;vertical-align: text-top">ファイル名</td>
                <td style="width: 20px;vertical-align: text-top">：</td>
                <td style="text-align: left">
                    @foreach($filenames as $filename)
                        {{$filename}}<br/>
                    @endforeach
                </td>
            </tr>
        </table>

        @if(isset($hide_circular_approval_url) && !$hide_circular_approval_url)
            <table border="0" cellpadding="0" cellspacing="0" style="width:100%">
                <tbody>
                <tr>
                    <td style="width:20%"></td>
                    <td class="m_-6849224559476667371link-button" style="min-width: 140px;
        border: 1px solid whitesmoke;
        border-radius: 8px;
        text-align: center;
        vertical-align: middle;
        background-color: #107fcd;">
                        <a style="color:#fff;text-decoration: none;display: block;margin: 8px 20px" href="{{$circular_approval_url}}" target="_blank" class="m_-6849224559476667371link-button">回覧文書をみる</a></td>
                    <td style="width:20%"></td>
                </tr>
                </tbody>
            </table>
            <br>
        @endif
        <p>Shachihata Cloudを利用して、回覧文書を確認・
            <wbr>捺印することができます。
            <br>回覧文書へのリンクをクリックするとShachihata Cloudの画面に移動します
            <wbr>。
            <br>
            <br>
            @if(!$hide_thumbnail_flg)
                <img style="width: 100%;border: solid 1px whitesmoke;" alt="Preview" src="{{ $message->embed($image_path) }}">
            @endif
        </p>
        <p>※この電子メールの内容を他の人と共有しないでください。</p>
        <br/>
        <section style="background-color: whitesmoke;
    padding: 1.0em;
    margin-left: 1em;
    margin-right: 1em;">
            <strong>この電子メールに記載された文書へのリンクを用いて、</strong>
            <p>Shachihata Cloud上の文書にアクセスが可能です。
                <br> 他の人に見られることがないように、この電子メールの転送、
                <br>およびリンクの転記は控えてください。
            </p>
        </section>
    </div>
@endsection