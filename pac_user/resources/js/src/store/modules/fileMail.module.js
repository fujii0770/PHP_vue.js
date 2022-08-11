import fileMailService from "../../services/fileMail.service";
import {Base64} from "js-base64";
import fileDownload from "js-file-download";

const state = {
    commentTitle: '',
    commentContent: '',
    mailId: 0, //ファイルメール便ID
    selectMailUsers: [], //ファイルメール便宛先
    mailFiles: [], //ファイルメール便ファイル
    title: '', //件名
    message: '', //メッセージ
    accessCode: '', //セキュリティコード
    count: 10, //ダウンロード最大回数
    expire_day: 2, //ダウンロード有効期限 日
    expire: 0, //ダウンロード有効期限 時
    addToContactsFlg: 0, //アドレス帳に追加
};

const actions = {
    // ファイルメールアップロード
    mailFilesUpload({dispatch}, file) {
        return fileMailService.mailFilesUpload(file).then(
            response => {
                dispatch("alertSuccess", response.message, {root: true});
                return Promise.resolve(response.data);
            },
            error => {
                dispatch("alertError", error, {root: true});
                return Promise.resolve(false);
            }
        );
    },
    // ファイルメール削除
    mailFilesDelete({dispatch}, id) {
        return fileMailService.mailFilesDelete(id).then(
            response => {
                dispatch("alertSuccess", response.message, {root: true});
                return Promise.resolve(response.data);
            },
            error => {
                dispatch("alertError", error, {root: true});
                return Promise.resolve(false);
            }
        );
    },
    // メール削除
    deleteMailItem({dispatch}, id) {
        return fileMailService.deleteMailItem(id).then(
            response => {
                dispatch("alertSuccess", response.message, {root: true});
                return Promise.resolve(response.data);
            },
            error => {
                dispatch("alertError", error, {root: true});
                return Promise.resolve(false);
            }
        );
    },
    //一覧取得
    getMailFileList({dispatch}, info) {
        return fileMailService.getMailFileList(info).then(
            response => {
                // dispatch("alertSuccess", response.message, {root: true});
                return Promise.resolve(response.data);
            },
            error => {
                dispatch("alertError", error, {root: true});
                return Promise.resolve(false);
            }
        );
    },
    //送信
    mailFilesSend({dispatch}, info) {
        return fileMailService.mailFilesSend(info).then(
            response => {
                dispatch("alertSuccess", response.message, {root: true});
                return Promise.resolve(response.data);
            },
            error => {
                dispatch("alertError", error, {root: true});
                return Promise.resolve(false);
            }
        );
    },
    //送信内容詳細
    getDiskMailItem({dispatch}, mail_id) {
        return fileMailService.getDiskMailItem(mail_id).then(
            response => {
                return Promise.resolve(response.data);
            },
            error => {
                dispatch("alertError", error, {root: true});
                return Promise.resolve(false);
            }
        );
    },
    //送信内容詳細　ファイルダウンロード
    downloadDiskMailItem({dispatch}, mail_id) {
        return fileMailService.downloadDiskMailItem(mail_id).then(
            response => {
                if(!response || !response.data) return Promise.reject(false);
                let byteString = Base64.atob(response.data.data);
                const ab = new ArrayBuffer(byteString.length);
                const ia = new Uint8Array(ab);
                for (let i = 0; i < byteString.length; i++) {
                    ia[i] = byteString.charCodeAt(i);
                }
                const dataBlob = new Blob([ab]);
                fileDownload(dataBlob, response.data.file_name);
                return Promise.resolve(response.data);
            },
            error => {
                dispatch("alertError", error, {root: true});
                return Promise.resolve(false);
            }
        );
    },
    //テンプレート 内容
    getMyDiskMailInfo({dispatch}, info) {
        return fileMailService.getDiskMailInfo(info).then(
            response => {
                // dispatch("alertSuccess", response.message, {root: true});
                return Promise.resolve(response.data);
            },
            error => {
                dispatch("alertError", error, {root: true});
                return Promise.resolve(false);
            }
        );
    },
    //テンプレート 更新
    updateDiskMailInfo({ dispatch}, info) {
        return fileMailService.updateDiskMailInfo(info).then(
            response => {
                dispatch("alertSuccess", response.message, { root: true});
                return Promise.resolve(true);
            },
            error => {
                dispatch("alertError", error, { root: true });
                return Promise.reject(false);
            }
        )
    },
    //再送信
    mailFilesSendAgain({dispatch}, date) {
        return fileMailService.mailFilesSendAgain(date).then(
            response => {
                dispatch("alertSuccess", response.message, {root: true});
                return Promise.resolve(response.data);
            },
            error => {
                dispatch("alertError", error, {root: true});
                return Promise.resolve(false);
            }
        );
    },

};

const mutations = {
    // ファイルメール便宛先追加
    addMailUsers(state, value) {
        state.selectMailUsers.push(value);
    },
    //ファイルメール便宛先削除
    deleteMailUsers(state, value) {
        state.selectMailUsers = value;
    },
    // ファイルメール便宛先追加
    addMailFiles(state, value) {
        state.mailFiles.push(value);
    },
    //ファイルメール便宛先削除
    updateMailFiles(state, value) {
        state.mailFiles = value;
    },
    //ファイルメール便id更新
    updateMailId(state, value) {
        state.mailId = value;
    },
    updateMailTitle(state, value) {
        state.title = value;
    },
    updateMailComment(state, value) {
        state.message = value;
    },
    updateMailAccessCode(state, value) {
        state.accessCode = value;
    },
    updateMailCount(state, value) {
        state.count = value;
    },
    updateMailExpire(state, value) {
        state.expire = value;
    },
    updateMailExpireDay(state, value) {
        state.expire_day = value;
    },
    updateMailContactsFlg(state, value) {
        state.addToContactsFlg = value;
    },
};

export const fileMail = {
    namespaced: true,
    state,
    actions,
    mutations
};