@extends('../../layouts.main')

@push('scripts')
    <script>
        var appPacAdmin = initAngularApp();
        var link_ajax = "{{ action('MailSendResumeController@index') }}";

    </script>
@endpush

@section('content')
    
    <span class="clear"></span>
    <div>
        @include('MailSendResume.list')
    </div>

@endsection