@extends('../basic')

@section('content') 
<div class="row justify-content-center">
    <div class="col-md-10 col-lg-8 col-xl-6">
        <div class="card-group">
            <div class="card card-cascade narrower">
                <div class="card-header bg-primary">ログインパスワードの変更</div>
                <div class="card-body">
                    <form method="post" id="form-setpass">
                        {{csrf_field()}}
                        @if (Session::get('message')!='')
                            <div class="row">
                                <div class="col-12">
                                    <p style="border: 1px dashed #c2cfd6;padding: 10px" class="text-left text-danger">{!! Session::get('message') !!}</p>
                                </div>
                            </div>
                        @endif
                        <div class="row">
                            <div class="col-12">
                                <p><strong>新しいパスワードを入力して「更新」ボタンをクリックしてください。</strong></p>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-12">
                            <p class="warning-text" style="background: #fff9b4; padding: 5px; border-radius: 3px;">{{ $passwordPolicy->min_length }}～32文字の半角英数字、記号が設定可能です。<br/> 必ず英字と数字を含めてください。<br/> ※英字の大文字と小文字は区別されます。 <br/>（設定例）@shachihata1234, #1234shachihata など</p>
                            </div>
                        </div>
                        <input type="hidden" name="email" value="{{$email}}">
                        <input type="hidden" name="acctype" value="{{Request::get('acctype')}}">
                        <div class="form-group">
                            <div class="row">
                               <label for="password" class="col-md-4 control-label">新しいパスワード<span style="color: red">*</span></label>
                               <div class="col-md-8">
                                     <input type="password" required="" name="password" value="" class="form-control"
                               id="password" minlength="{{ $passwordPolicy->min_length }}" maxlength="32">
                                     <span class="error password-error"></span>
                               </div>
                            </div>
                         </div>

                         <div class="form-group">
                            <div class="row">
                               <label for="password_confirmation" class="col-md-4 control-label">再入力<span style="color: red">*</span></label>
                               <div class="col-md-8">
                                     <input type="password" required="" name="password_confirmation" value="" class="form-control" id="password_confirmation"  minlength="{{ $passwordPolicy->min_length }}" maxlength="32">
                                     <span class="error password_confirmation-error"></span>
                               </div>
                            </div>
                         </div>
 
                        <div class="form-group text-right">
                            <button class="btn btn-success m-0">更新</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div> 
@endsection
<script>   document.oncontextmenu = function () {return false;} </script>
@push('scripts')
    <script>

        $(document).ready(function() {
            var character_type_limit = {{$passwordPolicy->character_type_limit}};
            $('#password').keyup(function () {
                $('.password-error').addClass('hide');
                let message = 'パスワードは、文字と数字を含める必要があります。';
                if(!validPass($(this).val())){
                    $('.password-error').removeClass('hide');
                    $('.password-error').html(message);
                }
                if(character_type_limit ==1){
                    let message = 'パスワードポリシーに反しています。英大文字、英小文字、数字、記号の内、3種類以上入れてください。';
                    if(!validPassCharacterType($(this).val())){
                        $('.password-error').removeClass('hide');
                        $('.password-error').html(message);
                    }
                }
            });

            $('form').on('submit', function (e) {
                let pwd  = $("#password").val();
                if(!validPass(pwd) || (character_type_limit ==1 && !validPassCharacterType(pwd))){
                    $('#password').focus();
                    e.preventDefault();return false;
                }

                var value = $('#password_confirmation').val();
                $('.password_confirmation-error').addClass('hide');

                if(value != pwd) {
                    $('.password_confirmation-error').removeClass('hide');
                    $('.password_confirmation-error').html('パスワードと確認パスワードが一致しません');
                    e.preventDefault();return false;
                }
            
            });
        });

        function validPass(value){
            if(/^(?=.*[0-9])(?=.*[a-zA-Z])/.test(value)){
                for(let i in value){
                    if(value[i].charCodeAt() > 126){
                        return false;
                    }
                }
            }else{
                return false;
            }
            return true;
        }

        function validPassCharacterType(value){
            if(/^(?=.*?[A-Z])(?=.*?[a-z])(?=.*?[0-9])|^(?=.*?[a-z])(?=.*?[0-9])(?=.*?[`~!@#$%^&*()_\-+=<>?:"{}|,.\/;'\\[\]])|^(?=.*?[A-Z])(?=.*?[0-9])(?=.*?[`~!@#$%^&*()_\-+=<>?:"{}|,.\/;'\\[\]])|^(?=.*?[A-Z])(?=.*?[a-z])(?=.*?[`~!@#$%^&*()_\-+=<>?:"{}|,.\/;'\\[\]])/.test(value)){
                for(let i in value){
                    if(value[i].charCodeAt() > 126){
                        return false;
                    }
                }
            }else{
                return false;
            }
            return true;
        }
    </script>
@endpush