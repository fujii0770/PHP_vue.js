import config from "../app.config";
import Axios from "axios";
import store from '../store/store';

export var homeService;
export default (homeService = {
    uploadFile,
    getPage,
    extractPdfLine,
    getUserStamps,
    getUserCompanyStamps,
    getUserStampsByHash,
    saveFile,
    downloadFile,
    saveStampsOrder,
    deleteCircularDocument,
    renameCircularDocument,
    loadCircular,
    loadCircularForCompleted,
    loadCircularByHash,
    deleteStoredFiles,
    updateCircularUsers,
    getStampInfos,
    getStampInfosForCompleted,
    getCircularDocument,
    generateStamp,
    updateCircularStatus,
    acceptUpload,
    rejectUpload,
    approvalRequestSendBack,
    discardCircular,
    checkShowConfirmAddTimeStamp,
    attachmentUpload,
    deleteAttachment,
    downloadAttachement,
    attachmentConfidentialFlg,
    getAttachment,
    saveTemplateEditStamp,
    saveTemplateEditText,
    reservePreviewFile,
    reserveAttachment,
});

function uploadFile(data) {
    let formData = new FormData();
    if (data.name){
        formData.append('file', data.file,data.name);
    } else {
        formData.append('file', data.file);
    }
    formData.append('circular_id', data.circular_id);
    formData.append('nowait', true);
    const tokenPublic = localStorage.getItem('tokenPublic');

    let headers = {
      'Content-Type': 'multipart/form-data'
    };
    headers['Authorization'] = `Bearer ${sessionStorage.getItem('token')}`;
    if(store.state.home.usingPublicHash) {
      formData.append('usingHash', store.state.home.usingPublicHash);
      headers['Authorization'] = `Bearer ${tokenPublic}`;
    }
    //headers['Authorization'] = `Bearer ${localStorage.getItem('token')}`;
    headers['Pragma'] = `no-cache`;
    headers['Cache-Control'] = `no-cache,no-store`;

    formData.append('max_document_size', data.file.max_document_size);
    let fileSizeMB = (data.file.size /1024 /1024).toFixed(1);
    if (window.document.documentMode) {
      const fetchOptions = {
        method: 'POST',
        body: formData
      };
      return fetch(`${config.LOCAL_API_URL}${store.state.home.usingPublicHash ? '/public': ''}/upload`, fetchOptions)
        .then(response => {
            if(response.ok) {
                return response.json();
            }else {
                throw Error(response.statusText);
            }
        })
        .then(data => {
          return Promise.resolve(data);
        })
        .catch(error => {
            const errMessage = `ファイルを読み取れませんでした。
                            ・PDF、Word、Excelファイルであるかご確認ください。
                            ・ファイルがパスワード保護されていないかご確認ください。`;
            if(error.message.localeCompare('Payload Too Large') == 0 ) {
                return Promise.reject(`アップロードできる合計のファイルサイズは ${data.file.max_document_size}MB 以内です （現在ファイルは： ${fileSizeMB}MB ）`);
            }

            const message = (error && error.data && error.data.message) || errMessage;
          return Promise.reject(message);
        });
    }else {
      return Axios.post(`${config.LOCAL_API_URL}${store.state.home.usingPublicHash ? '/public': ''}/upload`, formData,{
        headers: headers
      })
        .then(response => {
          return Promise.resolve(response.data);
        })
        .catch(error => {
          error = error.response;
            if(error.status == 413) {
                return Promise.reject(`アップロードできる合計のファイルサイズは ${data.file.max_document_size}MB 以内です （現在ファイルは： ${fileSizeMB}MB ）`);
            }
          const errMessage = `ファイルを読み取れませんでした。
                            ・PDF、Word、Excelファイルであるかご確認ください。
                            ・ファイルがパスワード保護されていないかご確認ください。`;
          const message = (error && error.data && error.data.message) || errMessage;
          return Promise.reject(message);
        });
    }


}

function attachmentUpload(data) {
    let formData = new FormData();
    formData.append('file', data.file);
    formData.append('circular_id', data.circular_id);
    formData.append('nowait', true);
    formData.append('max_attachment_size',data.file.file_max_attachment_size);
    const tokenPublic = localStorage.getItem('tokenPublic');
    let headers = {
        'Content-Type': 'multipart/form-data'
    };
    headers['Authorization'] = `Bearer ${sessionStorage.getItem('token')}`;
    if(store.state.home.usingPublicHash) {
        formData.append('usingHash', store.state.home.usingPublicHash);
        headers['Authorization'] = `Bearer ${tokenPublic}`;
    }

    headers['Pragma'] = `no-cache`;
    headers['Cache-Control'] = `no-cache,no-store`;
    return Axios.post(`${config.LOCAL_API_URL}${store.state.home.usingPublicHash ? '/public': ''}/attachmentUpload`,formData,{
        headers:headers
    })
        .then(response =>{
            return Promise.resolve(response.data);
        })
        .catch(error => {
            error = error.response;
            const message = (error && error.data && error.data.message) || error.statusText;
            return Promise.reject(message);
        });
}

function deleteAttachment(id) {
    let data = {circular_attachment_id:id ,usingHash:store.state.home.usingPublicHash};
    return Axios.post(`${config.LOCAL_API_URL}${store.state.home.usingPublicHash ? '/public': ''}/attachmentDelete`, data)
        .then(response => {
            return Promise.resolve(response.data);
        })
        .catch(error => {
            error = error.response;
            const message = (error && error.data && error.data.message) || error.statusText;
            return Promise.reject(message);
        });
}

function downloadAttachement(id) {
    let data = {circular_attachment_id:id ,usingHash:store.state.home.usingPublicHash};
    return Axios.post(`${config.LOCAL_API_URL}${store.state.home.usingPublicHash ? '/public': ''}/downloadAttachment`,data)
        .then(response =>{
            return Promise.resolve(response.data);
        })
        .catch(error =>{
            error = error.response;
            const message = (error && error.data && error.data.message) || error.statusText;
            return Promise.reject(message);
        });
}

function attachmentConfidentialFlg(data) {
    data.usingHash = store.state.home.usingPublicHash;
    return Axios.post(`${config.BASE_API_URL}${store.state.home.usingPublicHash ? '/public': ''}/changeAttachmentConfidentialFlg`,data)
        .then(response =>{
            return Promise.resolve(response.data);
        })
        .catch(error =>{
            error = error.response;
            const message = (error && error.data && error.data.message) || error.statusText;
            return Promise.reject(message);
        });
}

function getAttachment(circular_id) {
    return Axios.get(`${config.BASE_API_URL}${store.state.home.usingPublicHash ? '/public': ''}/getAttachment/${circular_id}`, {data: {nowait: true, usingHash:store.state.home.usingPublicHash}})
        .then(response => {
            return Promise.resolve(response.data);
        })
        .catch(error => {
            error = error.response;
            const message = (error && error.data && error.data.message) || error.statusText;
            return Promise.reject({'message': message,'statusCode': error.status});
        });
}

function acceptUpload(data) {
  data.usingHash = store.state.home.usingPublicHash;
  return Axios.post(`${config.LOCAL_API_URL}${store.state.home.usingPublicHash ? '/public': ''}/acceptUpload`, data)
    .then(response => {
      if(store.state.home.circular != null)
          store.state.home.circular.update_at = response.data.data.circular.update_at;
      return Promise.resolve(response.data);
    })
    .catch(error => {
      error = error.response ? error.response : error;
        if(error.status == 413) {
            return Promise.reject(error.data.message);
        }
        if(error.status == 422) {
            return Promise.reject('ファイルを読み取れませんでした。');
        }
        const errMessage = `ファイルを読み取れませんでした。
                            ・PDF、Word、Excelファイルであるかご確認ください。
                            ・ファイルがパスワード保護されていないかご確認ください。`;
      const message = (error && error.data && error.data.message) || errMessage;
      return Promise.reject(message);
    });
}

function rejectUpload(data) {
  data.usingHash = store.state.home.usingPublicHash;
  return Axios.post(`${config.LOCAL_API_URL}${store.state.home.usingPublicHash ? '/public': ''}/rejectUpload`, data)
    .then(response => {
      return Promise.resolve(response.data);
    })
    .catch(error => {
      error = error.response;
      const message = (error && error.data && error.data.message) || error.statusText;
      return Promise.reject(message);
    });
}

function getPage(options) {
    return Axios.get(`${config.LOCAL_API_URL}${store.state.home.usingPublicHash ? '/public': ''}/file/page?page=${options.page}&filename=${options.filename}&is_thumbnail=${Number(options.isThumbnail)}`, {data: {nowait: true,usingHash: store.state.home.usingPublicHash}})
        .then(response => {
            return Promise.resolve(response.data);
        })
        .catch(error => {
            error = error.response;
            const message = (error && error.data && error.data.message) || error.statusText;
            return Promise.reject(message);
        });
}

function extractPdfLine(data) {
    return Axios.post(`${config.LOCAL_API_URL}/extractPdfLine`, data, {nowait: true})
        .then(response => {
            return Promise.resolve(response.data);
        })
        .catch(error => {
            error = error.response;
            const message = (error && error.data && error.data.message) || error.statusText;
            return Promise.reject(message);
        });
}

function deleteCircularDocument(data) {
  data.usingHash = store.state.home.usingPublicHash;
  return Axios.post(`${config.LOCAL_API_URL}${store.state.home.usingPublicHash ? '/public': ''}/deleteCircularDocument`, data)
    .then(response => {
      return Promise.resolve(response.data);
    })
    .catch(error => {
      error = error.response;
      const message = (error && error.data && error.data.message) || error.statusText;
      return Promise.reject(message);
    });
}

// アップロードしたPDFファイル名変更
function renameCircularDocument(data) {
    data.usingHash = store.state.home.usingPublicHash;
    return Axios.post(`${config.LOCAL_API_URL}${store.state.home.usingPublicHash ? '/public': ''}/renameCircularDocument`, data)
        .then(response => {
            return Promise.resolve(response.data);
        })
        .catch(error => {
            error = error.response;
            const message = (error && error.data && error.data.message) || error.statusText;
            return Promise.reject(message);
        });
}

function deleteStoredFiles(data) {
  return Axios.post(`${config.LOCAL_API_URL}${store.state.home.usingPublicHash ? '/public': ''}/deleteStoredFiles`, data)
    .then(response => {
      return Promise.resolve(response.data);
    })
    .catch(error => {
      error = error.response;
      const message = (error && error.data && error.data.message) || error.statusText;
      return Promise.reject(message);
    });
}

function getUserStamps(options) {
    return Axios.get(`${config.BASE_API_URL}/myStamps?date=${options.date}`, {data: {nowait: true}})
      .then(response => {
          return Promise.resolve(response.data);
      })
      .catch(error => {
          error = error.response;
          const message = (error && error.data && error.data.message) || error.statusText;
          return Promise.reject(message);
      });
}

function checkShowConfirmAddTimeStamp(finishedDate) {
  return Axios.get(`${config.BASE_API_URL}${store.state.home.usingPublicHash ? '/public': ''}/circulars/${store.state.home.circular.id}/${(store.state.home.addStampHistory || store.state.home.addTextHistory) ? 'checkUsingTasDownload': 'checkUsingTasDownloadNoAddHistory'}/${store.state.home.fileSelected.circular_document_id}?finishedDate=${finishedDate}`, {data: {nowait: true, usingHash:store.state.home.usingPublicHash}})
    .then(response => {
        return Promise.resolve(response.data);
    })
    .catch(error => {
        error = error.response;
        const message = (error && error.data && error.data.message) || error.statusText;
        return Promise.reject(message);
    });
}

function getUserStampsByHash(options) {
    return Axios.get(`${config.BASE_API_URL}/public/getStampsByHash?date=${options.date}`, {data: {nowait: true,usingHash: true}})
        .then(response => {
            return Promise.resolve(response.data);
        })
        .catch(error => {
            error = error.response;
            const message = (error && error.data && error.data.message) || error.statusText;
            return Promise.reject(message);
        });
}

function saveFile(file) {
  file.usingHash = store.state.home.usingPublicHash;
  if (store.state.home.circular){
      file.update_at = store.state.home.circular.update_at;
  }
  return Axios.post(`${config.LOCAL_API_URL}${store.state.home.usingPublicHash ? '/public': ''}/saveFile`, file)
    .then(response => {
        if (store.state.home.circular){
            store.state.home.circular.update_at = response.data.data.update_at;
        }
        return Promise.resolve(response.data);
    })
    .catch(error => {
      error = error.response;
      const message = (error && error.data && error.data.message) || error.statusText;
      return Promise.reject({'message': message,'statusCode': error.status});
    });
}

function downloadFile(circular_document) {
  circular_document.usingHash = store.state.home.usingPublicHash;
  return Axios.post(`${config.LOCAL_API_URL}${store.state.home.usingPublicHash ? '/public': ''}/downloadFile`, circular_document)
    .then(response => {
      return Promise.resolve(response.data);
    })
    .catch(error => {
      error = error.response;
      const message = (error && error.data && error.data.message) || error.statusText;
      return Promise.reject(message);
    });
}

function updateCircularStatus(circular_id, status, finishedDate) {
  let data = {};
  data.usingHash = store.state.home.usingPublicHash;
  data.status = status;
  data.finishedDate = finishedDate;
  return Axios.patch(`${config.BASE_API_URL}${store.state.home.usingPublicHash ? '/public': ''}/circulars/${circular_id}/updateStatus`, data)
    .then(response => {
        // PAC_5-1092 ページの更新 ← PAC_5-1205 詳細画面でもページ更新がされたため取り消し
        // location.reload();
      return Promise.resolve(response.data);
    })
    .catch(error => {
      error = error.response;
      const message = (error && error.data && error.data.message) || error.statusText;
      return Promise.reject(message);
    });
}

function saveStampsOrder(stamps) {
  return Axios.post(`${config.BASE_API_URL}${store.state.home.usingPublicHash ? '/public': ''}/users/updateStampsOrder`, {stamps: stamps, usingHash: store.state.home.usingPublicHash})
    .then(response => {
      return Promise.resolve(response.data);
    })
    .catch(error => {
      error = error.response;
      const message = (error && error.data && error.data.message) || error.statusText;
      return Promise.reject(message);
    });
}

function loadCircular(circular_id) {
  return Axios.get(`${config.LOCAL_API_URL}/loadCircular?id=${circular_id}`)
    .then(response => {
      return Promise.resolve(response.data);
    })
    .catch(error => {
      error = error.response;
      const message = (error && error.data && error.data.message) || error.statusText;
      return Promise.reject(message);
    });
}
function loadCircularForCompleted(circular_id, finishedDate) {
    return Axios.get(`${config.LOCAL_API_URL}/loadCircular?id=${circular_id}&finishedDate=${finishedDate}`)
        .then(response => {
            return Promise.resolve(response.data);
        })
        .catch(error => {
            error = error.response;
            const message = (error && error.data && error.data.message) || error.statusText;
            return Promise.reject(message);
        });
}


function loadCircularByHash() {
  return Axios.get(`${config.LOCAL_API_URL}/public/loadCircularByHash`, {data: {usingHash: true}})
    .then(response => {
      return Promise.resolve(response.data);
    })
    .catch(error => {
      error = error.response;
      const message = (error && error.data && error.data.message) || error.statusText;
      return Promise.reject(message);
    });
}

function saveTemplateEditStamp(data){
  data.usingHash = store.state.home.usingPublicHash;
  return Axios.post(`${config.BASE_API_URL}${store.state.home.usingPublicHash ? '/public': ''}/templates/saveTemplateEditStamp`,data)
      .then(response => {
          return Promise.resolve(response.data);
      })
      .catch(error => {
          error = error.response;
          const message = (error && error.data && error.data.message) || error.statusText;
          return Promise.reject(message);
      });
}

function saveTemplateEditText(data){
  data.usingHash = store.state.home.usingPublicHash;
  return Axios.post(`${config.BASE_API_URL}${store.state.home.usingPublicHash ? '/public': ''}/templates/saveTemplateEditText`,data)
      .then(response => {
          return Promise.resolve(response.data);
      })
      .catch(error => {
          error = error.response;
          const message = (error && error.data && error.data.message) || error.statusText;
          return Promise.reject(message);
      });
}

function getStampInfos(circular_document_id) {
  return Axios.get(`${config.BASE_API_URL}${store.state.home.usingPublicHash ? '/public': ''}/circulars/${store.state.home.circular.id}/stamp_infos/findStampAndTextByCircularDocumentId?circular_document_id=${circular_document_id}`, {data: {nowait: true,usingHash: store.state.home.usingPublicHash}})
    .then(response => {
      return Promise.resolve(response.data);
    })
    .catch(error => {
      error = error.response;
      const message = (error && error.data && error.data.message) || error.statusText;
      return Promise.reject(message);
    });
}
function getStampInfosForCompleted(circular_document_id, finishedDate) {
    return Axios.get(`${config.BASE_API_URL}${store.state.home.usingPublicHash ? '/public': ''}/circulars/${store.state.home.circular.id}/stamp_infos/findStampAndTextByCircularDocumentId?circular_document_id=${circular_document_id}&finishedDate=${finishedDate}`, {data: {nowait: true,usingHash: store.state.home.usingPublicHash}})
        .then(response => {
            return Promise.resolve(response.data);
        })
        .catch(error => {
            error = error.response;
            const message = (error && error.data && error.data.message) || error.statusText;
            return Promise.reject(message);
        });
}


function discardCircular() {
  return Axios.delete(`${config.BASE_API_URL}${store.state.home.usingPublicHash ? '/public': ''}/circulars/${store.state.home.circular.id}`,{data: {usingHash: store.state.home.usingPublicHash}})
    .then(response => {
      return Promise.resolve(response.data);
    })
    .catch(error => {
      error = error.response;
      const message = (error && error.data && error.data.message) || error.statusText;
      return Promise.reject(message);
    });
}function getCircularDocument(circular_document_id) {
  return Axios.get(`${config.BASE_API_URL}/circulars/${store.state.home.circular.id}/documents/${circular_document_id}`)
    .then(response => {
      return Promise.resolve(response.data);
    })
    .catch(error => {
      error = error.response;
      const message = (error && error.data && error.data.message) || error.statusText;
      return Promise.reject(message);
    });
}

function updateCircularUsers(data) {
  let params = JSON.parse(JSON.stringify(data)).map(item => {
      delete item.circular_id;
      return item;
  });

  params.usingHash = store.state.home.usingPublicHash;
  return Axios.put(`${config.BASE_API_URL}${store.state.home.usingPublicHash ? '/public': ''}/circulars/${store.state.home.circular.id}/users/updates`, {circular_users: params, usingHash: store.state.home.usingPublicHash})
    .then(response => {
      return Promise.resolve(response.data);
    })
    .catch(error => {
      error = error.response;
      const message = (error && error.data && error.data.message) || error.statusText;
      return Promise.reject(message);
    });
}

function generateStamp(data) {
  return Axios.get(`${config.BASE_API_URL}/public/generateStamp?name=${encodeURIComponent(data.name)}&date=${data.date}`, {data: {usingHash: true}})
    .then(response => {
      return Promise.resolve(response.data);
    })
    .catch(error => {
      error = error.response;
      const message = (error && error.data && error.data.message) || error.statusText;
      return Promise.reject(message);
    });
}

function approvalRequestSendBack() {
  return Axios.post(`${config.BASE_API_URL}${store.state.home.usingPublicHash ? '/public': ''}/circulars/${store.state.home.circular.id}/users/approvalRequestSendBack`, {usingHash: store.state.home.usingPublicHash})
    .then(response => {
      return Promise.resolve(response.data);
    })
    .catch(error => {
      error = error.response;
      const message = (error && error.data && error.data.message) || error.statusText;
      return Promise.reject(message);
    });
}

function getUserCompanyStamps(options) {
    return Axios.get(`${config.BASE_API_URL}/myCompanyStamps?date=${options.date}`, {data: {nowait: true}})
        .then(response => {
            return Promise.resolve(response.data);
        })
        .catch(error => {
            error = error.response;
            const message = (error && error.data && error.data.message) || error.statusText;
            return Promise.reject(message);
        });
}

function reservePreviewFile(info) {
    info.usingHash = store.state.home.usingPublicHash;
    return Axios.post(`${config.BASE_API_URL}/previewFile/reserve`, info)
        .then(response => {
            return Promise.resolve(response.data);
        })
        .catch(error => {
            error = error.response;
            const message = (error && error.data && error.data.message) || error.statusText;
            return Promise.reject(message);
        });
}

function reserveAttachment(info) {
    info.usingHash = store.state.home.usingPublicHash;
    return Axios.post(`${config.BASE_API_URL}/attachment/reserve`, info)
        .then(response => {
            return Promise.resolve(response.data);
        })
        .catch(error => {
            error = error.response;
            const message = (error && error.data && error.data.message) || error.statusText;
            return Promise.reject(message);
        });
}