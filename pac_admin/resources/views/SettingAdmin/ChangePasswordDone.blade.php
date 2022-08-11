@extends('../layouts.main')
 
@section('content')
    
    <span class="clear"></span>
    <div class="card-group">
        <div class="card card-cascade narrower">
            <div class="card-header bg-primary">登録完了</div>
            <div class="card-body text-center">
                <h2 class="mb-5 mt-5">{{$message}}</h2>
                <div class="form-group">
                    <a href="{{ url('login') }}" class="btn btn-primary m-0">閉じる</a>
                </div>
            </div>
        </div>
    </div>

@endsection
 