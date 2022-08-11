@extends('../layouts.email')

@section('content')
    <div style="padding:16px 8px;background-color:white">
        {{$user_name}} さん<br/><br/>
        {{$admin_name}} さんから勤務表が差戻しされました。<br/><br/>
        勤務月: {{$working_month}}
    </div>
@endsection
