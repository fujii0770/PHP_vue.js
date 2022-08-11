@extends('../layouts_plain.email')

@section('content')

{{ $task_type }}タスク「{{ $title }}」は締め切り日に達しました。

タイトル
{{ $title }}

期限日
{{ $deadline }}

@if($important)
優先度
{{ $important }}
@endif

@if($content)
詳細
{{ $content }}
@endif


@endsection