@extends('../layouts.email')

@section('content')
  <div style="padding:16px 8px;background-color:white">
      <p>回覧文書{{ $title }}のアクセスコードを通知します。</p>
      <dl>
          <dt>アクセスコード：</dt>
          <dd>{{ $access_code }}</dd>
      </dl>
  </div>
@endsection
