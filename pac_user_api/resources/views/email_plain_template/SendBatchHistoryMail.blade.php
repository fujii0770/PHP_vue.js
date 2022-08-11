@extends('../layouts_plain.email')

@section('content')
	Shachihata 様
	Shachihata Cloudのバッチ（{{$batch_date}}分）を実施完了しましたので、ご連絡致します。
@foreach($batch_histories as $history)
---------------
バッチ名/コマンド： {{$history['batch_name']}}
実施日			： {{$history['execution_date']}}
状態			： {{\App\Http\Utils\AppUtils::BATCH_HISTORY_EMAIL[$history['status']]}}
開始時刻		： {{$history['created_at']}}
終了時刻		： {{$history['updated_at']}}
実行時間        ： {{$history['timediff']}}
@endforeach
@endsection