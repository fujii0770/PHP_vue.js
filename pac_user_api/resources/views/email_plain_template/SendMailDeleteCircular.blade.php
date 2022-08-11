@extends('../layouts_plain.email')

@section('content')
管理者により以下の回覧が削除されました。
※申請日時 - 件名(ファイル名)
・{{  $deleteTime }} - {{$title}} ({{ $fileName }})

ログイン画面に移動 : {{ config('app.new_app_url') }}
@if(isset($url_domain_id) && $url_domain_id)
SAML機能でログイン : {{ config('app.new_app_url').'/'.config('app.saml_url_prefix').'/'.$url_domain_id }}
@else
ログイン画面に移動 : {{ config('app.new_app_url') }}@endif
@endsection
 
