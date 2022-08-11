@extends('../layouts.email')

@section('content')
    <div style="padding:16px 8px;background-color:white">
        <p>
            {{$company_name}} <br/>
            {{$admin_name}} 様<br/><br/>
            いつもShachihata Cloudをご利用いただきありがとうございます。<br/>
            ご利用のShachihata Cloudに対して<br/>
            タイムスタンプの残り回数が<b>{{$timestamps_count}}</b>回以下になりました。<br/><br/>
            契約数を契約サイトにてご確認お願いいたします。<br/>
        </p>
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
                    <a style="color:#fff;text-decoration: none;display: block;margin: 8px 20px" href="{{$cloud_link}}" class="m_-6849224559476667371link-button" target="_blank">契約サイトはこちら</a>
                </td>
                <td style="width:20%"></td>
            </tr>
            </tbody>
        </table>
        <br>
    </div>
@endsection
