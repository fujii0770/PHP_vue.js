<div class="modal" id="modalWithBtnAndTitle">
    <div class="modal-dialog" style="max-width: 670px;">
        <div class="modal-content">
            <!-- Modal Header -->
            <div class="modal-header">
                <h5 class="modal-title title"></h5>
                {{--<button type="button" class="close" data-dismiss="modal">&times;</button>--}}
            </div>

            <!-- Modal body -->
            <div class="modal-body form-horizontal">
                <div class="message"></div>
                <div class="message-index" style="text-align: right;"></div>
            </div>

            <div class="modal-footer" style="border-top: none;">
                <button type="button" class="btn btn-primary" data-dismiss="modal"
                        onclick="javascript:window.open('{{config('app.url_contract')}}')">
                    本契約はこちら
                </button>
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