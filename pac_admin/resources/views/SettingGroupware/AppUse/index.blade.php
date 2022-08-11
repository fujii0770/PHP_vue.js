@extends('../../layouts.main')

@push('scripts')
    <script>
        var appPacAdmin = initAngularApp();
        var link_ajax = "{{ route('AppUse.Index') }}";
        var link_ajax_update ="{{ route('AppUse.update') }}";
    </script>
@endpush

@section('content')

    <span class="clear"></span>
    <div>
        @include('SettingGroupware.AppUse.list')
    </div>

@endsection
