@extends('../layouts.main')

@push('scripts')
    <script>
        var appPacAdmin = initAngularApp(),
        link_ajax = "{{ action('Admin\AttendanceController@users') }}",
        link_ajax_csv = "{{ route('CsvUserSetting') }}";
        link_book = "{{ action('Admin\AttendanceController@book') }}";
    </script>
@endpush

@section('content')
    <span class="clear"></span>
    <div class="SettingUser">
        @include('Attendance.Book.list')
        @include('Attendance.Book.detail')
    </div>
@endsection
