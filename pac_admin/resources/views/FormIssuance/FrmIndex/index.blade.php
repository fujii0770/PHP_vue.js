@extends('../layouts.main')

@push('scripts')
    <script>
        var appPacAdmin = initAngularApp(),
            link_ajax = "{{ url('form-issuance/frm-index') }}"
    </script>
@endpush

@section('content')

    <span class="clear"></span>
    <div>
        @include('FormIssuance.FrmIndex.list')
        @include('FormIssuance.FrmIndex.detail')
    </div>

@endsection