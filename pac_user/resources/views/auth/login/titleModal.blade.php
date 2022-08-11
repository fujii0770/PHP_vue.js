<div class="modal" id="modalWithTitle">
    <div class="modal-dialog" style="max-width: 670px;">
        <div class="modal-content">
            <!-- Modal Header -->
            <div class="modal-header">
                <h5 class="modal-title title"></h5>
            </div>

            <!-- Modal body -->
            <div class="modal-body form-horizontal">
                <div class="message"></div>
                <div class="message-index" style="text-align: right;"></div>
            </div>

            <div class="modal-footer" style="border-top: none;">
                <button type="button" class="btn btn-default" data-dismiss="modal">
                    <i class="fas fa-times-circle"></i> 閉じる
                </button>
            </div>
        </div>
        <div class="survey-content" style="padding: 0 20px;">
            <div class="survey-header">
                <h5 class="survey-title"></h5>
            </div>
            <div class="survey-body form-horizontal">
                <div class="surveyMessage"></div>
            </div>
            <div class="survey-footer" style="border-top: none;text-align: right;padding: 1rem;">
                <button type="button" class="btn btn-survey"
                        onclick="javascript:window.open('{{config('app.survey_url')}}')">
                    アンケートにご<br/>協力ください
                </button>
            </div>
        </div>
    </div>
</div>
<div class="modal" id="InvalidURL">
    <div class="modal-dialog" style="max-width: 670px;">
        <div class="modal-content">
            <!-- Modal Header -->
            <div class="modal-header">
                <h5 class="modal-title title"></h5>
            </div>
        </div>
        <div class="survey-content" style="padding: 0 20px;">
            <div class="survey-footer" style="border-top: none;text-align: right;padding: 1rem;">
                <button type="button" class="btn btn-blue"
                        onclick="javascript:window.location.assign('{{config('app.url')}}')">
                    ログイン画面に遷移
                </button>
            </div>
        </div>
    </div>
</div>