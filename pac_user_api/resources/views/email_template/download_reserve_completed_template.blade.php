@extends('../layouts.email')

@section('content')
    <div style="padding:16px 8px;background-color:white">
        <p>ダウンロードファイルの準備が完了しました。</p>
        <p>ダウンロード期限内にファイルのダウンロードをお願い致します。</p>
        <br/>
        <p>ファイル名：{{ $file_name }}</p>
        <p>ダウンロード期限：{{ $dl_period }}</p>
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
    </div>
@endsection
