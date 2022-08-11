@extends('../basic')

@section('content') 
    <div class="row justify-content-center">
        <div class="col-md-10">
            <div class="card-group">
                <div class="card card-cascade narrower">
                    <div class="card-header bg-primary">登録完了</div>
                    <div class="card-body text-center">
                        <h2 class="mb-5 mt-5">
                            @if(is_object($message))
                                {{$message->message}}
                            @else
                                {{$message}}
                            @endif
                        </h2>
                        <div class="form-group">
                            <a href="{{ config('app.unauthenticated_redirect_url') }}" class="btn btn-primary m-0">閉じる</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@endsection