@extends('../basic')

@section('content')
    <div class="row justify-content-center">
        <div class="col-md-10 col-lg-8 col-xl-6">
            <div>
                <div>
                    <div>
                        <form name='iframeForm' target="iframeForm">
                            {{csrf_field()}}
                            <div id="download">
                                <div class="form-group">
                                    <div class="row" style="margin-top: 10%">
                                        <label for="code" class="col-md-3 control-label" style="font-size: 20px">セキュリティコード</label>
                                        <div class="col-md-7">
                                            <input type="text" required="" name="code" value="" class="form-control"
                                                   id="code">
                                            <span class="error access-code"></span>
                                        </div>

                                    </div>
                                    <div class="row" style="margin-top: 20%;text-align:center">
                                        <div class="col-md-12">
                                            <button class="square btn btn-download m-0 download" id="download" onclick="ShowDiv()">ダウンロード</button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div id="error" style="display: none" class="card-body text-center">
                                <h2 class="mb-5 mt-5 error-message">
                                </h2>
                                <div class="form-group">
                                    <a href="{{ config('app.unauthenticated_redirect_url') }}" class="btn btn-primary m-0">閉じる</a>
                                </div>
                            </div>
                        </form>
                        <iframe id="iframeForm" name="iframeForm" style="display:none;"></iframe>
                    </div>
                </div>
            </div>
        </div>
        <div class="black" id="black"></div>
        <div class="dialog" id="dialogBox">
            <div class="modal-header">
                <div class="modal-title ng-binding ng-scope" ng-if="title" ng-bind-html="title">送信者が信頼できる場合、OKボタンをクリックしてファイルをダウンロードします。</div>
            </div>
            <div class="modal-footer">
                <div>
                    <button class="square btn btn-success m-0" onclick="downloadFile()">OK</button>
                </div>
                <div>
                    <button class="square btn btn-default m-0" onclick="CloseDiv()">キャンセル</button>
                </div>
            </div>
        </div>
    </div>
@endsection
<script>   document.oncontextmenu = function () {
        return false;
    } </script>
@push('scripts')
    <script>

        $(document).ready(function () {
        });

        function ShowDiv() {
            var code = $("[name='code']").val();
            if (code){
                $('.access-code').html('');
                document.getElementById('black').style.display = 'block';
                document.getElementById('dialogBox').style.display = 'block';
            }
        };

        function CloseDiv() {
            document.getElementById('black').style.display = 'none';
            document.getElementById('dialogBox').style.display = 'none';
        };

        function downloadFile() {
            this.CloseDiv();
            var code = $("[name='code']").val();
            $('.access-code').html('');
            $('.download').attr("disabled",true);
            $.ajax({
                method: 'post',
                url: '{{url('/mailFileDownload')}}',
                dataType: "json",
                data: {code: code},
                headers: {'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')},
                success: function (data, textStatus, jqXHR) {
                    $('.download').attr("disabled",false);
                    let message = data.message;
                    if (jqXHR.status === 200) {
                        if (data.status_code == 200) {
                            let bytes = window.atob(data.data.data);
                            let ab = new ArrayBuffer(bytes.length);
                            let ia = new Uint8Array(ab);
                            for (let i = 0; i < bytes.length; i++) {
                                ia[i] = bytes.charCodeAt(i);
                            }
                            let blob = new Blob([ab]);
                            let downloadElement = document.createElement("a");
                            let href = window.URL.createObjectURL(blob);
                            downloadElement.href = href;
                            downloadElement.download = data.data.file_name;
                            document.body.appendChild(downloadElement);
                            downloadElement.click();
                            document.body.removeChild(downloadElement);
                            window.URL.revokeObjectURL(href);
                            setTimeout(function () {
                                window.location.reload();
                            }, 30);
                        } else if (data.status_code == 403) {
                            $("#download").css("display", "none");
                            $("#error").css("display", "block");
                            $('.error-message').html(message);
                        } else {
                            $('.access-code').html(message);
                        }
                    }
                },
                error: function (jqXHR, textStatus, errorThrown) {
                    let resJson = jqXHR.responseJSON || null;
                    let message = resJson.message ? resJson.message : '予期せぬエラーが発生しました。 時間をおいてお試しください。';
                    $('.access-code').html(message);
                }
            });
            return false;
        }

    </script>
    <style>
        .black {
            display: none;
            position: fixed;
            width: 100%;
            height: 100%;
            z-index: 2;
            background: rgba(128, 128, 128, 0.6);
            top: 0;
            left: 0;
        }

        .dialog {
            display: none;
            position: fixed;
            z-index: 3;
            width: 500px;
            top: 50%;
            left: 50%;
            margin: -150px 0 0 -250px;
            background: #fff;
            padding: 15px;
            border-radius: 5px;
        }
    </style>
@endpush