@extends('../../layouts.main')

@push('scripts')
    <script>
        var appPacAdmin = initAngularApp();
        var link_ajax = "{{ action('GlobalSetting\SignatureController@index') }}";       
        var link_detail = "{{ route('signature.show') }}";
        var link_update = "{{ route('signature.update') }}";
        var link_delete = "{{ route('signature.delete') }}";
        var hasChange = false, allow_create = {{ $allow_create?1:0 }}, allow_update = {{ $allow_update?1:0 }};
    </script>
@endpush

@section('content')
    
    <span class="clear"></span>
    <div>
        @if($disabled)
            <p>この機能は現在無効化されています。</p>
        @else
            @include('GlobalSetting.Signature.list')

            @include('GlobalSetting.Signature.detail')
        @endif
    </div>

@endsection