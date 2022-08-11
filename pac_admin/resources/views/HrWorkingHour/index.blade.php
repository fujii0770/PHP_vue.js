@extends('../../layouts.main')

@push('scripts')
    <script>
        var appPacAdmin = initAngularApp();
        var link_ajax="hr-working-hours-show";
        var link_hr_store="hr-working-hours-update";
        var link_hr_insert="hr-working-hours-insert";
        var hasChange=false;
        var work_a=true;
        var work_b=false;
        var work_c=false;

    </script>
@endpush

@section('content')

    <span class="clear"></span>
    <div>
         @include('HrWorkingHour.list')
         @include('HrWorkingHour.detail')
    </div>

@endsection
