@extends('../layouts.email')

@section('content')
    <div style="font-family: Roboto,RobotoDraft,Helvetica,Arial,sans-serif">
        <div style="margin: 20px;">
            Shachihata 様
        </div>
        <div style="margin: 20px;">
            Shachihata Cloudのバッチ（{{$batch_date}}分）を実施完了しましたので、ご連絡致します。
        </div>
        <div style="padding:20px 20px;background-color:white">
            <table border="0" cellpadding="0" cellspacing="0" style="width:100%;max-width: 980px">
                <tr>
                    <td>バッチ名/コマンド</td>
                    <td>実施日</td>
                    <td>状態</td>
                    <td>開始時刻</td>
                    <td>終了時刻</td>
                    <td>実行時間</td>
                </tr>
                @foreach($batch_histories as $history)
                    <tr>
                        <td>{{$history['batch_name']}}</td>
                        <td>{{$history['execution_date']}}</td>
                        <td>{{\App\Http\Utils\AppUtils::BATCH_HISTORY_EMAIL[$history['status']]}}</td>
                        <td>{{$history['created_at']}}</td>
                        <td>{{$history['updated_at']}}</td>
                        <td>{{$history['timediff']}}</td>
                    </tr>
                @endforeach
            </table>
        </div>
    </div>
@endsection