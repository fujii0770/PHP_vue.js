@extends('../layouts.email')

@section('content')
    <div style="padding:16px 8px;background-color:white">
        {{$admin_name}} さん<br/><br/>
        {{$user_name}} さんから勤務表が提出されました。<br/><br/>
        勤務月: {{$working_month}}
    </div>
@endsection
