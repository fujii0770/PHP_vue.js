@extends('../../layouts.main')

@push('scripts')
    <script>
        var appPacAdmin = initAngularApp();
        var link_ajax_update_hradminflg = "hr-admin-reg-updateflg";
        var link_search_users = "{{ route('HrAdminRegistration.getUsers') }}";
        var link_ajax_update_users = "hr-admin-reg-updatehrusers";
    </script>
@endpush

@section('content')
    
    <span class="clear"></span>
    <div>
        @include('HrAdminRegistration.list')
        @include('HrAdminRegistration.detail')
    </div>

@endsection
