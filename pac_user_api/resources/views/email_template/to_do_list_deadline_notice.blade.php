@extends('../layouts.email')

@section('content')
    <div style="padding:16px 8px;background-color:white">
        <p>{{ $task_type }}タスク「{{ $title }}」は締め切り日に達しました。</p>
        <br/>
        <br/>
        <ul style="list-style-type: square">
            <li>タイトル</li>
            <div>{{ $title }}</div>
            <br/>
            <li>期限日</li>
            <div>{{ $deadline }}</div>
            <br/>
            @if($important)
            <li>優先度</li>
            <div>{{ $important }}</div>
            <br/>
            @endif
            @if($content)
            <li>詳細</li>
            <div>{{ $content }}</div>
            <br/>
            @endif
        </ul>
    </div>
@endsection