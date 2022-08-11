@extends('../../layouts.main')

@push('scripts')
    <script>
        var appPacAdmin = initAngularApp();
        var link_ajax = "{{ action('HrUserRegistrationController@index') }}";
        var link_hr_store = "{{ action('HrUserRegistrationController@store') }}";
        var link_ajax_store ="hr-user-reg-updateflg";
    </script>
@endpush

@section('content')
    
    <span class="clear"></span>
    <div>
        @include('HrUserRegistration.list')

        @include('HrUserRegistration.detail')
    </div>

@endsection