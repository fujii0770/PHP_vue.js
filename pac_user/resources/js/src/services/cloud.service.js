import config from "../app.config";
import Axios from "axios";
import store from "../store/store";

export var cloudService;
export default (cloudService = {
    getItems,
    upload,
    downloadItem,
    downloadCloudAttachment,
    downloadCloudMailFile
});

function downloadCloudAttachment(drive, file_id, filename, file_max_attachment_size,circular_id) {
    // PAC_5-1488 クラウドストレージを追加する
    return Axios.get(`${config.LOCAL_API_URL}${store.state.home.usingPublicHash ? '/public': ''}/downloadCloudAttachment?drive=${drive}&file_id=${file_id}&filename=${filename}&file_max_attachment_size=${file_max_attachment_size}&circular_id=${circular_id}&usingHash=${store.state.home.usingPublicHash}`, {data: {usingHash: store.state.home.usingPublicHash}})
        .then(response => {
            return Promise.resolve({data: response.data.data, statusCode: 200, message: response.data.message});
        })
        .catch(error => {
            error = error.response;
            if(error.status == 413) {
                return Promise.reject(`アップロードできる合計のファイルサイズは ${file_max_attachment_size ?? 1}GB 以内です`);
            }
            return Promise.reject(error.data.message);
        });
}
function downloadCloudMailFile(drive, file_id, filename, disk_mail_id,file_mail_size_single) {
    return Axios.get(`${config.LOCAL_API_URL}/downloadCloudMailFile?drive=${drive}&file_id=${file_id}&filename=${filename}&file_mail_size_single=${file_mail_size_single}&disk_mail_id=${disk_mail_id}&usingHash=${store.state.home.usingPublicHash}`, {data: {usingHash: store.state.home.usingPublicHash}})
        .then(response => {
            return Promise.resolve({data: response.data.data, statusCode: 200, message: response.data.message});
        })
        .catch(error => {
            error = error.response;
            if(error.status == 413) {
                return Promise.reject(`アップロードできる合計のファイルサイズは ${file_max_attachment_size ?? 1}GB 以内です`);
            }
            return Promise.reject(error.data.message);
        });
}

function getItems(drive,folder_id) {
    // PAC_5-1488 クラウドストレージを追加する
    return Axios.get(`${config.LOCAL_API_URL}${store.state.home.usingPublicHash ? '/public': ''}/getCloudItems?drive=${drive}&folder_id=${folder_id}&usingHash=${store.state.home.usingPublicHash}`, {data: {usingHash: store.state.home.usingPublicHash}})
        .then(response => {
            return Promise.resolve({data: response.data.data, statusCode: 200});
        })
        .catch(error => {
            const message = (error.response && error.response.data && error.response.data.message) || error.response.statusText;
            return Promise.resolve({data: error.response.data, statusCode: error.response.status, message: message});
        });
}

function downloadItem(drive, file_id, filename, file_max_document_size) {
    // PAC_5-1488 クラウドストレージを追加する
    return Axios.get(`${config.LOCAL_API_URL}${store.state.home.usingPublicHash ? '/public': ''}/downloadCloudItem?drive=${drive}&file_id=${file_id}&filename=${filename}&file_max_document_size=${file_max_document_size}&usingHash=${store.state.home.usingPublicHash}`, {data: {usingHash: store.state.home.usingPublicHash}})
        .then(response => {
            return Promise.resolve({data: response.data.data, statusCode: 200, message: response.data.message});
        })
        .catch(error => {
            error = error.response;
            if(error.status == 413) {
                return Promise.reject(error.data.message);
            }
            if(error.status == 422) {
                return Promise.reject(`ファイルを読み取れませんでした。
                            ・PDF、Word、Excelファイルであるかご確認ください。
                            ・ファイルがパスワード保護されていないかご確認ください。`);
            }
            const errMessage = `ファイルを読み取れませんでした。
                            ・PDF、Word、Excelファイルであるかご確認ください。
                            ・ファイルがパスワード保護されていないかご確認ください。`;
            const message = (error && error.data && error.data.message) || errMessage;
            return Promise.reject(message);
        });
}

function upload(drive,folder_id,filename,file,file_id) {
    let formData = new FormData();
    formData.append('drive', drive);
    formData.append('folder_id', folder_id);
    formData.append('filename', filename);
    formData.append('file', file);
    // PAC_5_1216 Start
    formData.append('file_id',file_id);
    // PAC_5_1216 END
    formData.append('nowait', true);
    let headers = {
        'Content-Type': 'multipart/form-data'
    };
    // PAC_5-1488 クラウドストレージを追加する Start
    const tokenPublic = localStorage.getItem('tokenPublic');
    headers['Authorization'] = `Bearer ${sessionStorage.getItem('token')}`;
    if(store.state.home.usingPublicHash) {
      formData.append('usingHash', store.state.home.usingPublicHash);
      headers['Authorization'] = `Bearer ${tokenPublic}`;
    }
    // PAC_5-1488 End
    return Axios.post(`${config.LOCAL_API_URL}${store.state.home.usingPublicHash ? '/public': ''}/uploadToCloud`, formData,{
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
