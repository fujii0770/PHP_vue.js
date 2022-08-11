import config from "../app.config";
import Axios from "axios";
import store from "../store/store";

export var circularsService;
export default (circularsService = {
    postActionMultiple,
    getListSave,  
    getListSent, pullback,
    requestSendBack,
    getDetailCircularUser,
    getDetailCircularUserForCompleted,
    getListReceived,
    checkAccessCode,
    getUnreadTotal,
    getCountCircularStatus,
    getListCompleted,
    getListViewing,
    getListDocument,
    deleteDocument,
    updateDocument,
    downloadDocument,
    downloadDocumentList,
    storeCircular,
    getOriginCircularUrl,
    getOriginCircularUrlForCompleted,
    getDownloadRequest,
    deleteDownloadRequest,
    downloadDownloadRequestData,
    downloadReserve,
    downloadLongTerm,
    downloadCsvReserve,
    reRequestDownload,
    automaticUpdateTimestamp,
    getLongtermIndex,
    getLongtermIndexOption,
    setLongtermIndex,
    setApprovalLongtermIndex,
    updateCircularStatus,
    getLongTermIndexValue,
    downloadAttachement,
    getMyFolders,
    updateFolderId,
    getCircularPageData,
    saveLongTermDocument,
    longTermUpload,
    sanitizingUpdate,//PAC_5-2874
    });

function postActionMultiple(action, info) {
    return Axios.post(`${config.BASE_API_URL}/circulars/actionMultiple/${action}`, info)
        .then(response => {
            return Promise.resolve(response.data);
        })
        .catch(error => {
            error = error.response;
            const message = (error && error.data && error.data.message) || error.statusText;
            return Promise.reject(message);
        });
}

function getListSave(info) {
    return Axios.post(`${config.BASE_API_URL}/circulars/saved`, info)
        .then(response => {
            return Promise.resolve(response.data);
        })
        .catch(error => {
            error = error.response;
            const message = (error && error.data && error.data.message) || error.statusText;
            return Promise.reject(message);
        });
}

function downloadReserve(ids, fileName, finishedDate, stampHistory, frmFlg,download,download_type,upload_id){
    return Axios.post(`${config.BASE_API_URL}/circulars/reserve`, { cids: ids, fileName: fileName, finishedDate: finishedDate, stampHistory: stampHistory, frmFlg: frmFlg,download:download, download_type:download_type,upload_id:upload_id })
        .then(response => {
            return Promise.resolve(response.data);
        })
        .catch(error => {
            error = error.response;
            const message = (error && error.data && error.data.message) || error.statusText;
            return Promise.reject(message);
        });
}
function downloadLongTerm(ids, fileName, finishedDate, stampHistory, frmFlg,download,download_type,upload_id){
    return Axios.post(`${config.BASE_API_URL}/circulars/downloadLongTerm`, { cids: ids, fileName: fileName, finishedDate: finishedDate, stampHistory: stampHistory, frmFlg: frmFlg,download:download, download_type:download_type,upload_id:upload_id })
        .then(response => {
            return Promise.resolve(response.data);
        })
        .catch(error => {
            error = error.response;
            const message = (error && error.data && error.data.message) || error.statusText;
            return Promise.reject(message);
        });
}

function downloadCsvReserve($param){
    return Axios.post(`${config.BASE_API_URL}/circulars/csv-reserve`, $param)
    .then(response => {
        return Promise.resolve(response.data);
    })
    .catch(error => {
        error = error.response;
        const message = (error && error.data && error.data.message) || error.statusText;
        return Promise.reject(message);
    });
}

function reRequestDownload(id){
    return Axios.post(`${config.BASE_API_URL}/download-request/rerequest`, { rid: id })
        .then(response => {
            return Promise.resolve(response.data);
        })
        .catch(error => {
            error = error.response;
            const message = (error && error.data && error.data.message) || error.statusText;
            return Promise.reject(message);
        });
}

//PAC_5-2874 S
function sanitizingUpdate(id){
    return Axios.post(`${config.BASE_API_URL}/download-request/sanitizingUpdate`, { rid: id })
        .then(response => {
            return Promise.resolve(response.data);
        })
        .catch(error => {
            error = error.response;
            const message = (error && error.data && error.data.message) || error.statusText;
            return Promise.reject(message);
        });
}
//PAC_5-2874 E

function getListDocument(info) {
    return Axios.post(`${config.BASE_API_URL}/long-term/document`, info)
        .then(response => {
            return Promise.resolve(response.data);
        })
        .catch(error => {
            error = error.response;
            const message = (error && error.data && error.data.message) || error.statusText;
            return Promise.reject(message);
        });
}

function getDownloadRequest(info) {
    return Axios.post(`${config.BASE_API_URL}/download-request/index`, info)
        .then(response => {
            return Promise.resolve(response.data);
        })
        .catch(error => {
            error = error.response;
            const message = (error && error.data && error.data.message) || error.statusText;
            return Promise.reject(message);
        });
}

function deleteDocument(id) {
    return Axios.post(`${config.BASE_API_URL}/long-term/delete`, id)
        .then(response => {
            return Promise.resolve(response.data);
        })
        .catch(error => {
            error = error.response;
            const message = (error && error.data && error.data.message) || error.statusText;
            return Promise.reject(message);
        });
}

function deleteDownloadRequest(info) {
    return Axios.post(`${config.BASE_API_URL}/download-request/delete`, { rid : info.id ,risCloud : info.isCloud})
        .then(response => {
            return Promise.resolve(response.data);
        })
        .catch(error => {
            error = error.response;
            const message = (error && error.data && error.data.message) || error.statusText;
            return Promise.reject(message);
        });
}

function updateDocument(data) {
    return Axios.put(`${config.BASE_API_URL}/long-term/${data.id}`, data)
        .then(response => {
            return Promise.resolve(response.data);
        })
        .catch(error => {
            error = error.response;
            const message = (error && error.data && error.data.message) || error.statusText;
            return Promise.reject(message);
        });
}
function automaticUpdateTimestamp(data) {
    return Axios.post(`${config.BASE_API_URL}/long-term/automatic-update-timestamp`, data)
        .then(response => {
            return Promise.resolve(response.data);
        })
        .catch(error => {
            error = error.response;
            const message = (error && error.data && error.data.message) || error.statusText;
            return Promise.reject(message);
        });
}

function downloadDocument(id) {
    return Axios.post(`${config.BASE_API_URL}/long-term/download`, id)
        .then(response => {
            return Promise.resolve(response.data);
        })
        .catch(error => {
            error = error.response;
            const message = (error && error.data && error.data.message) || error.statusText;
            return Promise.reject(message);
        });
}

function downloadDocumentList(ids, fileName) {
    return Axios.post(`${config.BASE_API_URL}/long-term/downloadList`, {longTermDocumentIds: ids, fileName: fileName})
        .then(response => {
            return Promise.resolve(response.data);
        })
        .catch(error => {
            error = error.response;
            const message = (error && error.data && error.data.message) || error.statusText;
            return Promise.reject(message);
        });
}

function downloadDownloadRequestData(info) {
    console.log("service:"+info.isCloud);
    return Axios.post(`${config.BASE_API_URL}/download-request/download`, { rid : info.id ,risCloud : info.isCloud})
        .then(response => {
            return Promise.resolve(response.data);
        })
        .catch(error => {
            error = error.response;
            const message = (error && error.data && error.data.message) || error.statusText;
            return Promise.reject(message);
        });
}

function getListSent(info) {
    return Axios.post(`${config.BASE_API_URL}/circulars/sent`, info)
        .then(response => {
            return Promise.resolve(response.data);
        })
        .catch(error => {
            error = error.response;
            const message = (error && error.data && error.data.message) || error.statusText;
            return Promise.reject(message);
        });
}

function getListReceived(info) {
    return Axios.post(`${config.BASE_API_URL}/circulars/received`, info)
        .then(response => {
            return Promise.resolve(response.data);
        })
        .catch(error => {
            error = error.response;
            const message = (error && error.data && error.data.message) || error.statusText;
            return Promise.reject(message);
        });
}
function getListCompleted(info) {
    return Axios.post(`${config.BASE_API_URL}/circulars/completed`, info)
        .then(response => {
            return Promise.resolve(response.data);
        })
        .catch(error => {
            error = error.response;
            const message = (error && error.data && error.data.message) || error.statusText;
            return Promise.reject(message);
        });
}
function getListViewing(info) {
    return Axios.post(`${config.BASE_API_URL}/circulars/viewing`, info)
        .then(response => {
            return Promise.resolve(response.data);
        })
        .catch(error => {
            error = error.response;
            const message = (error && error.data && error.data.message) || error.statusText;
            return Promise.reject(message);
        });
}
function getDetailCircularUser(id) {
    return Axios.get(`${config.BASE_API_URL}/circulars/${id}/detail-user`)
        .then(response => {
            return Promise.resolve(response.data);
        })
        .catch(error => {
            error = error.response;
            const message = (error && error.data && error.data.message) || error.statusText;
            return Promise.reject(message);
        });
}

function getDetailCircularUserForCompleted(id, finishedDate,longTermFlg=0,lid='') {
    return Axios.get(`${config.BASE_API_URL}/circulars/${id}/detail-user?finishedDate=${finishedDate}&lid=${lid}&longTermFlg=${longTermFlg}`)
        .then(response => {
            return Promise.resolve(response.data);
        })
        .catch(error => {
            error = error.response;
            const message = (error && error.data && error.data.message) || error.statusText;
            return Promise.reject(message);
        });
}

function getOriginCircularUrl(id) {
    return Axios.get(`${config.BASE_API_URL}/circulars/${id}/origin-circular-url`)
        .then(response => {
            return Promise.resolve(response.data);
        })
        .catch(error => {
            error = error.response;
            const message = (error && error.data && error.data.message) || error.statusText;
            return Promise.reject(message);
        });
}

function getOriginCircularUrlForCompleted(id, finishedDate) {
    return Axios.get(`${config.BASE_API_URL}/circulars/${id}/origin-circular-url?finishedDate=${finishedDate}`)
        .then(response => {
            return Promise.resolve(response.data);
        })
        .catch(error => {
            error = error.response;
            const message = (error && error.data && error.data.message) || error.statusText;
            return Promise.reject(message);
        });
}

function pullback(id, data) {
    data.pullback_remark = data.pullback_remark.replace(/\n/g,"\\r\\n");
    return Axios.get(`${config.BASE_API_URL}/circulars/${id}/pullback?parent_send_order=${data.parent_send_order}&child_send_order=${data.child_send_order}&update_at=${data.update_at}&pullback_remark=${data.pullback_remark}`)
        .then(response => {
            return Promise.resolve(response.data);
        })
        .catch(error => {
            error = error.response;
            const message = (error && error.data && error.data.message) || error.statusText;
            return Promise.reject({'message': message,'statusCode': error.status});
        });
}
function requestSendBack(id, data) {
    return Axios.post(`${config.BASE_API_URL}/circulars/${id}/requestSendBack`, {parent_send_order: data.parent_send_order, child_send_order: data.child_send_order,update_at: data.update_at})
        .then(response => {
            return Promise.resolve(response.data);
        })
        .catch(error => {
            error = error.response;
            const message = (error && error.data && error.data.message) || error.statusText;
            return Promise.reject({'message': message,'statusCode': error.status});
        });
}

function checkAccessCode(data) {
    return Axios.post(`${config.BASE_API_URL}${store.state.home.usingPublicHash ? '/public': ''}/circulars/${data.id}/checkAccessCode`, {access_code: data.access_code,current_user_identity:data.current_user_identity,usingHash:store.state.home.usingPublicHash, finishedDate: data.finishedDate})
      .then(response => {
          return Promise.resolve(response.data);
      })
      .catch(error => {
          error = error.response;
          const message = (error && error.data && error.data.message) || error.statusText;
          return Promise.reject(message);
      });
}

function getUnreadTotal(data) {
    return Axios.get(`${config.BASE_API_URL}/circulars/received/unread`)
      .then(response => {
          return Promise.resolve(response.data);
      })
      .catch(error => {
          error = error.response;
          const message = (error && error.data && error.data.message) || error.statusText;
          return Promise.reject(message);
      });
}

function getCountCircularStatus(data) {
    return Axios.get(`${config.BASE_API_URL}/myCirculars/count-status`)
        .then(response => {
            return Promise.resolve(response.data);
        })
        .catch(error => {
            error = error.response;
            const message = (error && error.data && error.data.message) || error.statusText;
            return Promise.reject(message);
        });
}

function storeCircular(data) {
    return Axios.post(`${config.BASE_API_URL}${store.state.home.usingPublicHash ? '/public': ''}/circulars/${data.id}/store`, {keyword: data.keyword, finishedDate: data.finishedDate,usingHash:store.state.home.usingPublicHash, keyword_flg: data.keyword_flg, folderId: data.folderId})
        .then(response => {
            return Promise.resolve(response.data);
        })
        .catch(error => {
            error = error.response;
            const message = (error && error.data && error.data.message) || error.statusText;
            return Promise.reject(message);
        });
}

function getLongtermIndex(data) {
    return Axios.get(`${config.BASE_API_URL}/long-term/index/list`)
        .then(response => {
            return Promise.resolve(response.data);
        })
        .catch(error => {
            error = error.response;
            const message = (error && error.data && error.data.message) || error.statusText;
            return Promise.reject(message);
        });
}

function getLongtermIndexOption(id) {
    return Axios.get(`${config.BASE_API_URL}/long-term/index/list/Option?id=${id}`)
        .then(response => {
            return Promise.resolve(response.data);
        })
        .catch(error => {
            error = error.response;
            const message = (error && error.data && error.data.message) || error.statusText;
            return Promise.reject(message);
        });
}

function setLongtermIndex(data) {
    return Axios.post(`${config.BASE_API_URL}/long-term/index/set`, data)
        .then(response => {
            return Promise.resolve(response.data);
        })
        .catch(error => {
            error = error.response;
            const message = (error && error.data && error.data.message) || error.statusText;
            return Promise.reject(message);
        });
}

function setApprovalLongtermIndex(data) {
    return Axios.post(`${config.BASE_API_URL}${store.state.home.usingPublicHash ? '/public': ''}/longTermIndex/setApproval`, data)
        .then(response => {
            return Promise.resolve(response.data);
        })
        .catch(error => {
            error = error.response;
            const message = (error && error.data && error.data.message) || error.statusText;
            return Promise.reject(message);
        });
}

function updateCircularStatus(id) {
    return Axios.post(`${config.BASE_API_URL}/download-request/updateCircularStatus`, {id: id})
        .then(response => {
            return Promise.resolve(response.data);
        })
        .catch(error => {
            error = error.response;
            const message = (error && error.data && error.data.message) || error.statusText;
            return Promise.reject(message);
        });
}
function getLongTermIndexValue(cid){
    return Axios.get(`${config.BASE_API_URL}/long-term/index/list/getLongTermIndex?cid=${cid}`)
        .then(response => {
            return Promise.resolve(response.data);
        })
        .catch(error => {
            error = error.response;
            const message = (error && error.data && error.data.message) || error.statusText;
            return Promise.reject(message);
        });
}

// PAC_5-2377
function downloadAttachement(data) {
    return Axios.post(`${config.BASE_API_URL}/long-term/downloadattachment`, data)
        .then(response => {
            return Promise.resolve(response.data);
        })
        .catch(error => {
            error = error.response;
            const message = (error && error.data && error.data.message) || error.statusText;
            return Promise.reject(message);
        });
}

function saveLongTermDocument(data) {
    return Axios.post(`${config.BASE_API_URL}/long-term/saveLongTermDocument`, data)
        .then(response => {
            return Promise.resolve(response.data);
        })
        .catch(error => {
            error = error.response;
            const message = (error && error.data && error.data.message) || error.statusText;
            return Promise.reject(message);
        });
}
function longTermUpload(file)
{

    let formData = new FormData();

    formData.append('file', file);

    let headers = {
        'Content-Type': 'multipart/form-data'
    };
    headers['Authorization'] = `Bearer ${sessionStorage.getItem('token')}`;
    headers['Pragma'] = `no-cache`;
    headers['Cache-Control'] = `no-cache,no-store`;

    if (window.document.documentMode) {
        const fetchOptions = {
            method: 'POST',
            headers,
            body: formData
        };
        return fetch(`${config.BASE_API_URL}/long-term/longTermUpload`, fetchOptions)
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
                    return Promise.reject('アップロードできるファイルサイズは 10MB 以内です');
                }

                const message = (error && error.data && error.data.message) || errMessage;
                return Promise.reject(message);
            });
    }else {
        return Axios.post(`${config.BASE_API_URL}/long-term/longTermUpload`, formData,{
            headers: headers
        })
            .then(response => {
                return Promise.resolve(response.data);
            })
            .catch(error => {
                error = error.response;
                if(error.status == 413) {
                    return Promise.reject('アップロードできるファイルサイズは 10MB 以内です');
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
}// PAC_5-2279
function getMyFolders() {
    return Axios.post(`${config.BASE_API_URL}${store.state.home.usingPublicHash ? '/public': ''}/long-term/getMyFolders`,{usingHash:store.state.home.usingPublicHash})
        .then(response => {
            return Promise.resolve(response.data);
        })
        .catch(error => {
            error = error.response;
            const message = (error && error.data && error.data.message) || error.statusText;
            return Promise.reject(message);
        });
}

function updateFolderId(data) {
    return Axios.post(`${config.BASE_API_URL}/long-term/updateFolderId`, data)
        .then(response => {
            return Promise.resolve(response.data);
        })
        .catch(error => {
            error = error.response;
            const message = (error && error.data && error.data.message) || error.statusText;
            return Promise.reject(message);
        });
}

function getCircularPageData(data) {
    return Axios.post(`${config.BASE_API_URL}/long-term/getCircularPageData`, data)
        .then(response => {
            return Promise.resolve(response.data);
        })
        .catch(error => {
            error = error.response;
            const message = (error && error.data && error.data.message) || error.statusText;
            return Promise.reject(message);
        });
}
