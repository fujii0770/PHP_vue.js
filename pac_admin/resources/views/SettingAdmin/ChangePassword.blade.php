@extends('../layouts.main')
 
@section('content')
    
    <span class="clear"></span>
    <form method="post" id="form-changepass">
        {{csrf_field()}}
        @if (Session::get('message')!='')
        <div class="form-group">
            <div class="row">
                <div class="col-12 col-md-2"></div>
                <div class="col-12 col-md-8">
                    <div class="row">
                        <div class="col-12">
                            <p style="border: 1px dashed #c2cfd6;padding: 10px" class="text-left text-danger">{!! Session::get('message') !!}</p>
                        </div>
                    </div>
                </div>
                <div class="col-12 col-md-2"></div>
            </div>
        </div>            
        @endif
        <div class="form-horizontal">
            <div class="form-group">
                <div class="row">
                    <div class="col-12 col-md-2"></div>
                    <div class="col-12 col-md-8"><div class="text-center">新しいパスワードを入力して　［パスワードを変更する］　ボタンをクリックします。</div></div>
                    <div class="col-12 col-md-2"></div>
                </div>
            </div>
            <div class="form-group">
                <div class="row">
                    <div class="col-12 col-md-3"></div>
                    <div class="col-12 col-md-6">
                        <div style="background: #ffba0026; padding: 10px;">
                            {{$passwordPolicy->min_length}}～32文字の半角英数字、記号が設定可能です。<br />
                            必ず英字と数字を含めてください。<br />
                            ※英字の大文字と小文字は区別されます。<br />
                            （設定例）@shachihata1234, #1234shachihata など
                        </div>
                    </div>
                    <div class="col-12 col-md-3"></div>
                </div>
            </div>        
            <div class="form-group">
                <div class="row">
                    <div class="col-12 col-md-3"></div>
                    <div class="col-12 col-md-6">                        
                        {!! \App\Http\Utils\CommonUtils::showFormField('password','新しいパスワード','','password', true, 
                            [ 'id'=>'password','placeholder'=>'パスワード', 'minlength'=>$passwordPolicy->min_length,  'maxlength' => 32 ]) !!}
                    </div>
                    <div class="col-12 col-md-3"></div>
                </div>
            </div>

            <div class="row">
                <div class="col-12 col-md-3"></div>
                <div class="col-12 col-md-6">
                    {!! \App\Http\Utils\CommonUtils::showFormField('password_confirmation','新しいパスワードを再入力','','password', true, 
                        [ 'id'=>'password_confirmation', 'placeholder'=>"新しいパスワード", 'minlength'=>$passwordPolicy->min_length,  'maxlength' => 32 ]) !!}
                   
                </div>
                <div class="col-12 col-md-3"></div>
            </div>
        
            <div class="row mt-1 form-group">
                <div class="col-12 col-md-3"></div>
                <div class="col-12 col-md-6">
                    <div class="row">
                        <label for="password" class="col-md-4 control-label"></label>
                        <div class="form-check col-md-8">
                            <input type="checkbox" id="checkShowPass" onclick="clickShowPass(this)">
                            <label class="form-check-label" for="checkShowPass">パスワードを表示</label>
                        </div>
                    </div>
                </div>
                <div class="col-12 col-md-3"></div>
            </div>

            <div class="row">
                <div class="col-12 col-md-3"></div>
                <div class="col-12 col-md-6">
                    <div class="row">
                        <label for="password" class="col-md-4 control-label"></label>
                        <div class="col-md-8">
                            <button class="btn btn-success"><i class="far fa-save"></i> 登録</button>
                        </div>
                    </div>
                </div>
                <div class="col-12 col-md-3"></div>
            </div>
            
        </div>
    </form>

@endsection

@push('scripts')
    <script>
        function clickShowPass(el){
            if(el.checked){
                $("#password_confirmation").attr('type','text');
                $("#password").attr('type','text');
            }else{
                $("#password_confirmation").attr('type','password');
                $("#password").attr('type','password');
            }
        }

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
            $('#form-changepass button.btn-success').on('click', function (e) {
                let pwd  = $("#password").val();
                if(!validPass(pwd) || (character_type_limit == 1 && !validPassCharacterType(pwd))){
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
                $('#form-changepass').submit();

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
            /*PAC_5-2848 S*/
            if(/^(?=.*?[A-Z])(?=.*?[a-z])(?=.*?[0-9])|^(?=.*?[a-z])(?=.*?[0-9])(?=.*?[`~!@#$%^&*()_\-+=<>?:"{}|,.\/;'\\[\]])|^(?=.*?[A-Z])(?=.*?[0-9])(?=.*?[`~!@#$%^&*()_\-+=<>?:"{}|,.\/;'\\[\]])|^(?=.*?[A-Z])(?=.*?[a-z])(?=.*?[`~!@#$%^&*()_\-+=<>?:"{}|,.\/;'\\[\]])/.test(value)){
            /*PAC_5-2848 E*/
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