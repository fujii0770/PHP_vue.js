import config from "../app.config";
import Axios from "axios";
import store from "../store/store";

export var pageBreaksService;

export default pageBreaksService = {
    uploadFilesForPageBreak,
    rejectPageBreaks,
    decidePageBreaksBeforeAcceptUpload,
    decidePageBreaksAfterAcceptUpload,
    odsPreview,
    odtPreview,
    odtUpdate,
    odtReset
};

/**
 * 改ページ調整APIへのファイルアップロード
 */
function uploadFilesForPageBreak(targetFiles) {
    return Axios.post(`${config.LOCAL_API_URL}/${store.state.home.usingPublicHash ? 'public/' : ''}uploadFilesForPageBreak`, {
        convertedFiles: targetFiles,
        usingHash: store.state.home.usingPublicHash
    })
        .then(response => {
            // タイプ判定、プレビュー表示
            const responseType = response.data.data[0].type;

            const editorMap = {
                text: "odt",
                spreadsheet: "ods"
            };

            const editorType = editorMap[responseType];
            if (!editorType) {
                throw new Error("unexpected type");
            }

            return Promise.resolve(editorType);
        })
        .catch(error => {
            error = error.response;
            const message =
                (error && error.data && error.data.message) || error.statusText;
            return Promise.reject(message);
        });
}

/**
 * アップロード中止によるサーバー上のファイル削除
 */
function rejectPageBreaks() {
    return Axios.post(`${config.LOCAL_API_URL}/${store.state.home.usingPublicHash ? 'public/' : ''}rejectPageBreaks`, {
        filename: store.state.pageBreaks.fileInfoList[0].serverFilename,
        files: store.state.pageBreaks.uploadFileInfoList,
        usingHash: store.state.home.usingPublicHash
    })
        .then(response => {
            return Promise.resolve(response.data);
        })
        .catch(error => {
            error = error.response;
            const message =
                (error && error.data && error.data.message) || error.statusText;
            return Promise.reject(message);
        });
}

/**
 * DB登録前の改ページ調整
 */
function decidePageBreaksBeforeAcceptUpload(breaks) {
    // homeのacceptUploadを参考にする
    return Axios.post(
        `${config.LOCAL_API_URL}/${store.state.home.usingPublicHash ? 'public/' : ''}decidePageBreaksBeforeAcceptUpload`,
        {
            filename: store.state.pageBreaks.fileInfoList[0].serverFilename,
            pdfFilename: store.state.pageBreaks.fileInfoList[0].pdfFilename,
            breaks: breaks,
            // acceptUploadで渡すデータと合わせる
            files: store.state.pageBreaks.uploadFileInfoList,
            circular_id: store.state.home.circular
                ? store.state.home.circular.id
                : null,
            usingHash: store.state.home.usingPublicHash
        }
    )
        .then(response => {
            return Promise.resolve(response.data);
        })
        .catch(error => {
            error = error.response ? error.response : error;
            if (error.status == 413) {
                return Promise.reject(
                    error.data.message
                );
            }
            if (error.status == 422) {
                return Promise.reject("ファイルを読み取れませんでした。");
            }
            const errMessage = `ファイルを読み取れませんでした。
                                ・PDF、Word、Excelファイルであるかご確認ください。
                                ・ファイルがパスワード保護されていないかご確認ください。`;
            const message =
                (error && error.data && error.data.message) || errMessage;
            return Promise.reject(message);
        });
}

/**
 * DB登録後の改ページ調整
 */
function decidePageBreaksAfterAcceptUpload(breaks) {
    const circular_document_id = store.state.pageBreaks.circularDocIdBeforeMod;
    return Axios.post(
        `${config.LOCAL_API_URL}/${store.state.home.usingPublicHash ? 'public/' : ''}decidePageBreaksAfterAcceptUpload`,
        {
            filename: store.state.pageBreaks.fileInfoList[0].serverFilename,
            pdfFilename: store.state.pageBreaks.fileInfoList[0].pdfFilename,
            breaks: breaks,
            // 置き換えるためのDB情報
            circular_id: store.state.pageBreaks.circularIdForRegisteredDocs,
            circular_document_id: circular_document_id,
            document_data_update_at: store.state.pageBreaks.registeredDocInfoList.find(
                registeredDocInfo =>
                    registeredDocInfo.circular_document_id ===
                    circular_document_id
            ).document_data_update_at,
            usingHash: store.state.home.usingPublicHash
        }
    )
        .then(response => {
            return Promise.resolve(response.data);
        })
        .catch(error => {
            error = error.response;
            // isHttpStatus412は楽観ロックエラーとしている
            const isHttpStatus412 = error.status == 412;
            let message = error.data?.message || error.statusText;
            if (isHttpStatus412) {
                message += `
                ファイルが更新されている可能性があります。
                前画面に戻って、ファイルのアップロードからやり直してください。`;
            }
            return Promise.reject({
                isHttpStatus412: isHttpStatus412,
                message: message
            });
        });
}

/**
 * odsプレビュー取得
 */
function odsPreview(filename) {
    return Axios.post(`${config.LOCAL_API_URL}/${store.state.home.usingPublicHash ? 'public/' : ''}odsPreview`, {
        filename: filename
    })
        .then(response => {
            return Promise.resolve(response.data);
        })
        .catch(error => {
            error = error.response;
            const message =
                (error && error.data && error.data.message) || error.statusText;
            return Promise.reject(message);
        });
}

/**
 * odtプレビュー取得
 */
function odtPreview(filename) {
    return Axios.post(`${config.LOCAL_API_URL}/${store.state.home.usingPublicHash ? 'public/' : ''}odtPreview`, {
        filename: filename
    })
        .then(response => {
            return Promise.resolve(response.data);
        })
        .catch(error => {
            error = error.response;
            const message =
                (error && error.data && error.data.message) || error.statusText;
            return Promise.reject(message);
        });
}

/**
 * odtの改ページ位置更新
 */
function odtUpdate(filename, operation) {
    return Axios.post(`${config.LOCAL_API_URL}/${store.state.home.usingPublicHash ? 'public/' : ''}odtUpdate`, {
        filename: filename,
        operation: operation
    })
        .then(response => {
            return Promise.resolve(response.data);
        })
        .catch(error => {
            error = error.response;
            const message =
                (error && error.data && error.data.message) || error.statusText;
            return Promise.reject(message);
        });
}

/**
 * odtの改ページ位置リセット
 */
function odtReset(filename) {
    return Axios.post(`${config.LOCAL_API_URL}/${store.state.home.usingPublicHash ? 'public/' : ''}odtReset`, {
        filename: filename,
    })
        .then(response => {
            return Promise.resolve(response.data);
        })
        .catch(error => {
            error = error.response;
            const message =
                (error && error.data && error.data.message) || error.statusText;
            return Promise.reject(message);
        });
}