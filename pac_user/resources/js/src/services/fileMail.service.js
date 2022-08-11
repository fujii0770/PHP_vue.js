import config from "../app.config";
import Axios from "axios";
import store from '../store/store';

export var fileMailService;
export default (fileMailService = {
    mailFilesUpload,
    mailFilesDelete,
    getMailFileList,
    mailFilesSend,
    deleteMailItem,
    getDiskMailItem,
    downloadDiskMailItem,
    getDiskMailInfo,
    updateDiskMailInfo,
    mailFilesSendAgain,
});


// ファイルメール便ファイルアップロード
function mailFilesUpload(data) {
    let formData = new FormData();
    formData.append('file', data.file);
    formData.append('file_mail_size_single', data.file_mail_size_single);
    formData.append('disk_mail_id', data.disk_mail_id);
    const tokenPublic = localStorage.getItem('tokenPublic');
    let headers = {
        'Content-Type': 'multipart/form-data'
    };
    headers['Authorization'] = `Bearer ${sessionStorage.getItem('token')}`;
    headers['Pragma'] = `no-cache`;
    headers['Cache-Control'] = `no-cache,no-store`;
    return Axios.post(`${config.LOCAL_API_URL}/mailFileUpload`, formData, {
        headers: headers
    })
        .then(response => {
            return Promise.resolve(response.data);
        })
        .catch(error => {
            error = error.response;
            const message = (error && error.data && error.data.message) || error.statusText;
            return Promise.reject(message);
        });
}
//ファイルメール便ファイル削除
function mailFilesDelete(id) {
    return Axios.post(`${config.BASE_API_URL}/deleteMailFile`, {file_id: id})
        .then(response => {
            return Promise.resolve(response.data);
        })
        .catch(error => {
            error = error.response;
            const message = (error && error.data && error.data.message) || error.statusText;
            return Promise.reject(message);
        });
}
//ファイルメール便ファイル一覧取得
function getMailFileList(data) {
    return Axios.post(`${config.BASE_API_URL}/getMailFileList`, data)
        .then(response => {
            return Promise.resolve(response.data);
        })
        .catch(error => {
            error = error.response;
            const message = (error && error.data && error.data.message) || error.statusText;
            return Promise.reject(message);
        });
}
//送信
function mailFilesSend(data) {
    return Axios.post(`${config.BASE_API_URL}/sendMailFile`, data)
        .then(response => {
            return Promise.resolve(response.data);
        })
        .catch(error => {
            error = error.response;
            const message = (error && error.data && error.data.message) || error.statusText;
            return Promise.reject(message);
        });
}
//ファイルメール便ファイル削除
function deleteMailItem(id) {
    return Axios.post(`${config.BASE_API_URL}/deleteMailItem`, {mail_id: id})
        .then(response => {
            return Promise.resolve(response.data);
        })
        .catch(error => {
            error = error.response;
            const message = (error && error.data && error.data.message) || error.statusText;
            return Promise.reject(message);
        });
}
//ファイルメール便送信内容詳細
function getDiskMailItem(id) {
    return Axios.get(`${config.BASE_API_URL}/getDiskMailItem/${id}`)
        .then(response => {
            return Promise.resolve(response.data);
        })
        .catch(error => {
            error = error.response;
            const message = (error && error.data && error.data.message) || error.statusText;
            return Promise.reject(message);
        });
}
//ファイルメール便送信内容詳細 ファイルダウンロード
function downloadDiskMailItem(id) {
    return Axios.post(`${config.BASE_API_URL}/downloadDiskMailItem`,{disk_mail_id: id})
        .then(response => {
            return Promise.resolve(response.data);
        })
        .catch(error => {
            error = error.response;
            const message = (error && error.data && error.data.message) || error.statusText;
            return Promise.reject(message);
        });
}
//テンプレート 内容
function getDiskMailInfo(data) {
    return Axios.post(`${config.BASE_API_URL}/getDiskMailInfo`, data)
        .then(response => {
            return Promise.resolve(response.data);
        })
        .catch(error => {
            error = error.response;
            const message = (error && error.data && error.data.message) || error.statusText;
            return Promise.reject(message);
        });
}
//テンプレート 更新
function updateDiskMailInfo(info) {
    return Axios.post(`${config.BASE_API_URL}/updateDiskMailInfo`, {info})
        .then(response => {
            return Promise.resolve(response.data);
        })
        .catch(error => {
            error = error.response;
            const message = (error && error.data && error.data.message) || error.statusText;
            return Promise.reject(message);
        });
}
//再送信
function mailFilesSendAgain(date) {
    return Axios.post(`${config.BASE_API_URL}/sendMailFileAgain`, date)
        .then(response => {
            return Promise.resolve(response.data);
        })
        .catch(error => {
            error = error.response;
            const message = (error && error.data && error.data.message) || error.statusText;
            return Promise.reject(message);
        });
}