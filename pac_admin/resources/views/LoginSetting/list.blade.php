    <form class="imagechange"method="post" action="{{ url('/login-layout-setting/imageChange')}}" enctype="multipart/form-data">
    @csrf
        <div class="card mt-3">
            <div class="card-header">画像差し替え設定</div>
                <div class="card-body">
                    <div class="upload-area" id="upload-area">
                        <p id="a">画像をドラック＆ドロップ</p>
                        <input type="file" name="upload_file" id="file_input" accept=".png" required=".png">
                        <div id="output"></div>
                    </div>
                    <br/>
                    <button class="btn btn-success" name="imagechange"><i class="far fa-save"></i> 登録</button> 
                    </div>
                </div>
                <div class="col-12 col-md-3"></div>
        </div>
    </form>

    <form class="changelogintext" method="post" action="{{ url('/login-layout-setting/write')}}">
    @csrf
        <div class="card mt-3">
            <div class="card-header">ログイン画面文章設定</div>
                <div class="card-body">
                    <div class="form-group">
                    <p>ログイン画面に設定する文言を入力して下さい</p>
                    <textarea name="textBox_contents" id="textBox_contents" cols="120" rows="10" required></textarea>
                    <br/>
                    <button class="btn btn-success" name="write"><i class="far fa-save"></i> 登録</button> 
                    </div>
                </div>
                <div class="col-12 col-md-3"></div>
        </div>
    </form>

    <form class="changeloginurl" method="post" action="{{ url('/login-layout-setting/writeurl')}}">
    @csrf
        <div class="card mt-3">
            <div class="card-header">URL編集設定</div>
                <div class="card-body">
                    <div class="form-group">
                    <p>指定したいURL(タグ込みで)を入力して下さい</p>
                    <textarea name="textBox_contentsurl" id="textBox_contentsurl" cols="120" rows="10" required></textarea>
                    <br/>
                    <button class="btn btn-success" name="writeurl"><i class="far fa-save"></i> 登録</button> 
                    </div>
                </div>
                <div class="col-12 col-md-3"></div>
        </div>
    </form>

    <style>

    .imagechange {
        margin: 15px auto;
        width: 1000px;
        height: 550px;
        padding: 30px;
        text-align: center;
    }

    .changelogintext {
        margin: 15px auto;
        width: 1400px;
        height: 450px;
        padding: 30px;
        text-align: center;
    }

    .changeloginurl {
        margin: 15px auto;
        width: 1400px;
        height: 450px;
        padding: 30px;
        text-align: center;
    }
 
    .upload-area {
        margin: auto;
        width: 70%;
        height: 300px;
        position: relative;
        border: 1px dotted rgba(0, 0, 0, .4);
    }

    .upload-area i {
        position: absolute;
        font-size: 120px;
        opacity: .1;
        width: 100%;
        left: 0;
        top: 80px;
    }
    .upload-area p {
        width: 100%;
        position: absolute;
        top: 200px;
        opacity: .8;
        font-size: 30px;
    }
 
    #file_input {
        top: 0;
        left: 0;
        opacity: 0;
        position: absolute;
        width: 100%;
        height: 100%;
    }

    #output {
        width: 100%;
        position: absolute;
        top: 40px;
        opacity: .8;
        font-size: 30px;
    }
    
    </style>

    <script>

    const notclick = document.getElementById('file_input')

    $(function(){

        notclick.addEventListener("click", function (e) {
        e.preventDefault();
        });               

      // ドラック＆ドロップ時の画面レイアウト処理
        $(document).on('dragover', '#upload-area, #upload-area_stl', function (event) {
              event.preventDefault();
              $(this).css("background-color", "lightblue");
        });
        $(document).on('dragleave', '#upload-area, #upload-area_stl', function (event) {
            event.preventDefault();
            $(this).css("background-color", "transparent");
        });
    
        //ドロップ後の処理  
        $(document).on('drop', '#upload-area', function (event) {

            let org_e = event;
            if (event.originalEvent) {
              org_e = event.originalEvent;
            }

            org_e.preventDefault();

            const str = org_e.dataTransfer.files;

            file_input.files = org_e.dataTransfer.files;

            if(!document.getElementById('file_input').value.match(/\.(png)$/i)){
                var filedata = document.getElementById('file_input')
                output.innerHTML= 'このファイルは無効です';
                $('input[type="file"]').val(null);
                $(this).css("background-color", "transparent");
                return false;
            }

            $(this).css("background-color", "transparent");

            var files = file_input.files;

            for (var i = 0; i < files.length; i++) {
                output.innerHTML= files[i].name +'<br/>';
            }
        });
                                                                                                                                                                      
    });
    </script>
