@extends('../../layouts.main')

@push('scripts')
    <script>
        var appPacAdmin = initAngularApp(),
        link_ajax = "{{ route('GlobalSetting.Branding.Store') }}";
    </script>
@endpush

@section('content')
    
    <span class="clear"></span>
    <div class="GlobalSetting Settings-branding mb-3">
        <div ng-controller="SettingsBrandingController" id="SettingsBrandingController">
            <div class="message"></div>
            <div class="card mt-3">
                <div class="card-header">ブランディング設定</div>
                <div class="card-body form-horizontal">
                     
                    <div class="form-group row">
                        <label for="logo_file_data_1" class="control-label col-sm-4">ロゴ画像</label>
                        <div class="col-sm-8">
                            <label class="label mr-2" for="logo_file_data_1">
                                <input type="radio" id="logo_file_data_1" name="logoType" ng-model="logoType" value="default">
                                <label for="logo_file_data_1">標準の画像を使用する(登録情報)</label>
                            </label>
                        </div>
                    </div>
                    <div class="form-group row">
                        <label for="logo_file_data_2" class="control-label col-sm-4"></label>
                        <div class="col-sm-8">
                            <label class="label mr-2" for="logo_file_data_2">
                                <input type="radio" id="logo_file_data_2" name="logoType" ng-model="logoType" value="custome">
                                <label for="logo_file_data_2">任意の画像を使用する</label> 
                            </label>
                        </div>
                    </div>
                    <div class="form-group row" ng-class="{disable: logoType == 'default'}">
                        <label class="control-label col-sm-4"></label>
                        <div class="col-sm-8">
                            <label class="label mr-2">
                                @if(!$branding || !$branding->logo_file_data)
                                    <span ng-hide="hiddenTex">(画像ファイルを選択してください) </span>
                                    <img ng-hide="hiddenImage" ng-src="data:image/png;base64,<% branding.logo_file_data %>" style="width:70px; margin-right: 10px;">
                                @else
                                    <img ng-src="data:image/png;base64,<% branding.logo_file_data %>" style="width:70px; margin-right: 10px;">
                                @endif                          
                                
                                <button class="btn btn-default"
                                 ng-disabled="logoType == 'default'"
                                 onclick="$('#import_file').click()"><i class="far fa-folder-open"></i> 画像を変更する (登録情報)</button>
                            </label>
                        </div>
                        <input id="import_file" type="file" class="hide" onChange="angular.element(this).scope().SelectFile(event)" accept="image/*">
                    </div>
                    <div class="form-group row">
                        <label class="control-label col-sm-4"></label>
                        <div class="col-sm-3">
                            <div class="alert alert-warning">
                                画像ファイルは幅300ピクセル、高さ50ピクセルのPNG/JPG/BMP/GIF形式をご利用ください。
                                PNGとGIF形式では透過形式も利用することができます。
                                幅300ピクセル、高さ50ピクセルのどちらかを越えた場合は、縦横比を維持した縮小処理が行われます。
                              </div>
                        </div>
                        <div class="col-sm-5"></div>
                    </div>

                    <div class="form-group row">
                        <label for="branding-background_color" class="control-label col-sm-4">背景色</label>
                        <div class="col-sm-8">
                            <div class="row">
                                <div class="col-md-4 col-xl-2">                                    
                                    <input type="text" class="form-control jscolor" ng-model="branding.background_color" 
                                        id="branding-background_color" nam="background_color" 
                                        ng-style="{'background-color': '#'+branding.background_color}" maxlength="6"/>
                                </div>
                                <div class="col-md-8 col-xl-10"><span class="btn btn-default" ng-click="resetBackground()">背景色を初期値に戻す</span></div>
                            </div>
                        </div>
                    </div>
                    <div class="form-group row">
                        <label for="branding-color" class="control-label col-sm-4">文字色</label>
                        <div class="col-sm-8">
                            <div class="row">
                                <div class="col-md-4 col-xl-2">                                     
                                    <input type="text" class="form-control jscolor" ng-model="branding.color"
                                        id="branding-color" name="color"  
                                        ng-style="{'background-color': '#'+branding.color}" maxlength="6">
                                </div>
                                <div class="col-md-8 col-xl-10"><span class="btn btn-default" ng-click="resetColor()">文字色を初期値に戻す</span></div>
                            </div>
                        </div>
                    </div>
                    
                </div>
            </div>
            
            @canany([PermissionUtils::PERMISSION_BRANDING_SETTINGS_UPDATE])
            <div class="text-right mt-3">
                <button type="submit" class="btn btn-success" ng-disabled="!enable_save" ng-click="save()">
                    <i class="far fa-save"></i> 更新
                </button>
                <input type="checkbox" ng-model="enable_save" id="enable_save" class="hide" />
            </div>
            @endcanany
        </div>
    </div>

    <style>
        .row.disable{
            opacity: 0.75;
            user-select: none;
        }
        .row.disable .btn{
            cursor: not-allowed;
        }
        #file_upload{ opacity: 0; }
        #file_upload.disable{
            visibility: hidden
        }
        .text-white{ color: #fff;  }
        .text-black{ color: #000;  }
    </style>

@endsection


@push('scripts')
    <script>
        if(appPacAdmin){
            var default_bg = '107FCD', default_color = 'FFFFFF';
            appPacAdmin.controller('SettingsBrandingController', function($scope, $rootScope, $http){
                @if($branding)
                    $scope.logoType = '{{ $branding->logo_file_data==""?'default':'custome' }}';
                    $scope.branding = { background_color: '{{ $branding->background_color }}',color: '{{ $branding->color }}' ,logo_file_data: '{{ $branding->logo_file_data }}' };
                @else
                    $scope.logoType = 'default';
                    $scope.branding = { background_color: default_bg, color: default_color };
                @endif
                $scope.enable_save = false;
                $scope.colorselect = { background_color: '',color: '' };
                $scope.file_select = null;
                $scope.resetBackground = function(){ 
                    $scope.branding.background_color = default_bg;                   
                }
                $scope.resetColor = function(){ 
                    $scope.branding.color = default_color;                    
                }

                $scope.hiddenTex = false;
                $scope.hiddenImage = true;

                $scope.SelectFile = function($event){
                    $scope.hiddenTex = true;
                    $scope.hiddenImage = false;
                    if(!$scope.enable_save)
                            $("#enable_save").click();
                    $scope.file_select = $event.target.files[0];
                    readFileImageAsync($scope.file_select).then(function(file){
                        $scope.$apply( function(){
                            $scope.branding.logo_file_data = file.data_image;
                        });
                    });
                    
                }

                $scope.save = function(){
                    hideMessages();
                    $rootScope.$emit("showLoading");
                    $scope.enable_save = false;

                    var fd = new FormData();
                    if($scope.logoType == 'custome'){
                        fd.append('file_logo', $scope.file_select);
                    }
                    fd.append('background_color', $scope.branding.background_color);
                    fd.append('color', $scope.branding.color);
                    fd.append('logoType', $scope.logoType);

                    $http.post(link_ajax, fd, { headers: { 'Content-Type': undefined }, })
                        .then(function(event) {
                            $rootScope.$emit("hideLoading");
                            if(event.data.status == false){
                                $(".message").append(showMessage(event.data.message, 'warning', 10000));
                            }
                    });
                    $('#import_file').val("");
                    $scope.file_select = "";
                };

                $scope.$watch('logoType', function(newValue, oldValue) {
                    if(newValue != oldValue){ $scope.enable_save = true; }
                 });

                $scope.$watchCollection('branding', function(newValue, oldValue) {
                    if(newValue != oldValue){ $scope.enable_save = true; }
                });
            });

        }else{
            throw new Error("Something error init Angular.");
        }
    </script>
@endpush