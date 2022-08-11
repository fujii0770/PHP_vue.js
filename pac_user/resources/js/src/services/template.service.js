import config from "../app.config";
import Axios from "axios";
import store from "../store/store";

export var templateService;
export default (templateService = {
    getTemplates,
    getTemplatesEdit,
    deletes,
    uploadFiles,
    editTemplate,
    convertExcelToImage,
    saveInputData,
    csvDownloadreserve,
    getListCompleted,
    CsvDownloadUserForm,
    getCsvFlg,
    templateCsvCheckEmail,
    getTemplateInfo,
    saveInputEditTemplate,
    getTemplateEditStamp,
    getTemplateEditText,
    sendTemplateEditFlg,
    releaseTemplateEditFlg,
    getCircularTempEdit,
    templateStampInfoDelete,
    getTemplateInputComplete,
    getTemplateNextUserCompletedFlg,
    tempEditStampInfoFix,
    templateEditS3delete,
    updateTemplateRoute,
    getTemplateRouteInfo,
});

function getTemplates(queries) {
    return Axios.get(`${config.BASE_API_URL}/templates?file_name=${queries.file_name}&page=${queries.page}&limit=${queries.limit}&orderBy=${queries.orderBy}&orderDir=${queries.orderDir}&circularId=${queries.circularId}`)
        .then(response => {
            return Promise.resolve(response.data);
        })
        .catch(error => {
            error = error.response;
            const message = (error && error.data && error.data.message) || error.statusText;
            return Promise.reject(message);
        });
}

function getTemplatesEdit(queries) {
    return Axios.get(`${config.BASE_API_URL}/templates/indexEdit?file_name=${queries.file_name}&page=${queries.page}&limit=${queries.limit}&orderBy=${queries.orderBy}&orderDir=${queries.orderDir}&circularId=${queries.circularId}`)
        .then(response => {
            return Promise.resolve(response.data);
        })
        .catch(error => {
            error = error.response;
            const message = (error && error.data && error.data.message) || error.statusText;
            return Promise.reject(message);
        });
}

function deletes(data) {
    return Axios.post(`${config.BASE_API_URL}/templates/delete`, data)
      .then(response => {
          return Promise.resolve(response.data);
      })
      .catch(error => {
          error = error.response;
          const message = (error && error.data && error.data.message) || error.statusText;
          return Promise.reject(message);
      });
}

function uploadFiles(data) {
    let formData = new FormData();
    formData.append('file', data.file);
    formData.append('document_access_flg', data.document_access_flg);

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
        return fetch(`${config.BASE_API_URL}/templates/upload`, fetchOptions)
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
        return Axios.post(`${config.BASE_API_URL}/templates/upload`, formData,{
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


}

function editTemplate(data){
    let formData = new FormData();
    data.usingHash = store.state.home.usingPublicHash;
    return Axios.post(`${config.BASE_API_URL}${store.state.home.usingPublicHash ? '/public': ''}/templates/edit/${data.templateId}`, data)
        .then(response => {
            return Promise.resolve(response.data);
        })
        .catch(error => {
            error = error.response;
            const message = (error && error.data && error.data.message) || error.statusText;
            return Promise.reject(message);
        });
}

function getTemplateInputComplete(data){
    data.usingHash = store.state.home.usingPublicHash;
    return Axios.post(`${config.BASE_API_URL}${store.state.home.usingPublicHash ? '/public': ''}/templates/getTemplateInputComplete`, data)
        .then(response => {
            return Promise.resolve(response.data);
        })
        .catch(error => {
            error = error.response;
            const message = (error && error.data && error.data.message) || error.statusText;
            return Promise.reject(message);
        });
}

function templateEditS3delete(data){
    data.usingHash = store.state.home.usingPublicHash;
    return Axios.post(`${config.BASE_API_URL}${store.state.home.usingPublicHash ? '/public': ''}/templates/templateEditS3delete`, data)
        .then(response => {
            return Promise.resolve(response.data);
        })
        .catch(error => {
            error = error.response;
            const message = (error && error.data && error.data.message) || error.statusText;
            return Promise.reject(message);
        });
}

function getTemplateNextUserCompletedFlg(data){
    data.usingHash = store.state.home.usingPublicHash;
    return Axios.post(`${config.BASE_API_URL}${store.state.home.usingPublicHash ? '/public': ''}/templates/getTemplateNextUserCompletedFlg`, data)
        .then(response => {
            return Promise.resolve(response.data);
        })
        .catch(error => {
            error = error.response;
            const message = (error && error.data && error.data.message) || error.statusText;
            return Promise.reject(message);
        });
}

function getTemplateInfo(data){
    return Axios.post(`${config.BASE_API_URL}/templates/getTemplateInfo` , data)
        .then(response => {
            return Promise.resolve(response.data);
        })
        .catch(error => {
            error = error.response;
            const message = (error && error.data && error.data.message) || error.statusText;
            return Promise.reject(message);
        });
}

function templateStampInfoDelete(data){
    data.usingHash = store.state.home.usingPublicHash;
    return Axios.post(`${config.BASE_API_URL}${store.state.home.usingPublicHash ? '/public': ''}/templates/templateStampInfoDelete`, data)
        .then(response => {
            return Promise.resolve(response.data);
        })
        .catch(error => {
            error = error.response;
            const message = (error && error.data && error.data.message) || error.statusText;
            return Promise.reject(message);
        });
}

function tempEditStampInfoFix(data){
    data.usingHash = store.state.home.usingPublicHash;
    return Axios.post(`${config.BASE_API_URL}${store.state.home.usingPublicHash ? '/public': ''}/templates/tempEditStampInfoFix`, data)
        .then(response => {
            return Promise.resolve(response.data);
        })
        .catch(error => {
            error = error.response;
            const message = (error && error.data && error.data.message) || error.statusText;
            return Promise.reject(message);
        });
}

function convertExcelToImage(data){
    if(data.hasOwnProperty('special_sit_flg') && data.special_sit_flg){
        return Axios.get(`${config.LOCAL_API_URL}/templates/convertExcelToImage/${data.templateId}?storage_file_name=${data.storageFileName}&page=${data.page}&special_sit_flg=${data.special_sit_flg}`)
            .then(response => {
                return Promise.resolve(response.data);
            })
            .catch(error => {
                error = error.response;
                const message = (error && error.data && error.data.message) || error.statusText;
                return Promise.reject(message);
            });
    }else {
        return Axios.get(`${config.LOCAL_API_URL}/templates/convertExcelToImage/${data.templateId}?storage_file_name=${data.storageFileName}&page=${data.page}&circularId=${data.circular_id}`)
            .then(response => {
                return Promise.resolve(response.data);
            })
            .catch(error => {
                error = error.response;
                const message = (error && error.data && error.data.message) || error.statusText;
                return Promise.reject(message);
            });
    }
}

function saveInputData(data){
    return Axios.post(`${config.BASE_API_URL}/templates/save/inputData`, data)
        .then(response => {
            return Promise.resolve(response.data);
        })
        .catch(error => {
            error = error.response;
            const message = (error && error.data && error.data.message) || error.statusText;
            return Promise.reject(message);
        });
}

function saveInputEditTemplate(data){
    return Axios.post(`${config.BASE_API_URL}/templates/save/saveInputEditTemplate`, data)
        .then(response => {
            return Promise.resolve(response.data);
        })
        .catch(error => {
            error = error.response;
            const message = (error && error.data && error.data.message) || error.statusText;
            return Promise.reject(message);
        });
}

function getTemplateEditStamp(data){
    let formData = new FormData();
    data.usingHash = store.state.home.usingPublicHash;
    return Axios.post(`${config.BASE_API_URL}${store.state.home.usingPublicHash ? '/public': ''}/templates/getTemplateEditStamp`, data)
        .then(response => {
            return Promise.resolve(response.data);
        })
        .catch(error => {
            error = error.response;
            const message = (error && error.data && error.data.message) || error.statusText;
            return Promise.reject(message);
        });
}

function getTemplateEditText(data){
    let formData = new FormData();
    data.usingHash = store.state.home.usingPublicHash;
    return Axios.post(`${config.BASE_API_URL}${store.state.home.usingPublicHash ? '/public': ''}/templates/getTemplateEditText`, data)
        .then(response => {
            return Promise.resolve(response.data);
        })
        .catch(error => {
            error = error.response;
            const message = (error && error.data && error.data.message) || error.statusText;
            return Promise.reject(message);
        });
}

function sendTemplateEditFlg(data){
    let formData = new FormData();
    return Axios.post(`${config.BASE_API_URL}/templates/sendTemplateEditFlg` , data)
        .then(response => {
            return Promise.resolve(response.data);
        })
        .catch(error => {
            error = error.response;
            const message = (error && error.data && error.data.message) || error.statusText;
            return Promise.reject(message);
        });
}

function releaseTemplateEditFlg(data){
    let formData = new FormData();
    data.usingHash = store.state.home.usingPublicHash;
    return Axios.post(`${config.BASE_API_URL}${store.state.home.usingPublicHash ? '/public': ''}/templates/releaseTemplateEditFlg`, data)
        .then(response => {
            return Promise.resolve(response.data);
        })
        .catch(error => {
            error = error.response;
            const message = (error && error.data && error.data.message) || error.statusText;
            return Promise.reject(message);
        });
}

function getCircularTempEdit(data){
    return Axios.post(`${config.BASE_API_URL}/templates/getCircularTempEdit` , data)
        .then(response => {
            return Promise.resolve(response.data);
        })
        .catch(error => {
            error = error.response;
            const message = (error && error.data && error.data.message) || error.statusText;
            return Promise.reject(message);
        });
}

function csvDownloadreserve(data){
    return Axios.post(`${config.BASE_API_URL}/templatecsv/csvDownloadReserve`, data)
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
    return Axios.post(`${config.BASE_API_URL}/templatecsv`, info)
        .then(response => {
            return Promise.resolve(response.data);
        })
        .catch(error => {
            error = error.response;
            const message = (error && error.data && error.data.message) || error.statusText;
            return Promise.reject(message);
        });
}

function CsvDownloadUserForm(data){
    return Axios.post(`${config.BASE_API_URL}/templates/CsvDownloadUserForm`, data)
        .then(response => {
            return Promise.resolve(response.data);
        })
        .catch(error => {
            error = error.response;
            const message = (error && error.data && error.data.message) || error.statusText;
            return Promise.reject(message);
        });
}

function getCsvFlg(data){
    return Axios.post(`${config.BASE_API_URL}/templates/getCsvFlg`, data)
        .then(response => {
            return Promise.resolve(response.data);
        })
        .catch(error => {
            error = error.response;
            const message = (error && error.data && error.data.message) || error.statusText;
            return Promise.reject(message);
        });
}

function templateCsvCheckEmail(data){
    return Axios.post(`${config.BASE_API_URL}/templates/templateCsvCheckEmail`, data)
        .then(response => {
            return Promise.resolve(response.data);
        })
        .catch(error => {
            error = error.response;
            const message = (error && error.data && error.data.message) || error.statusText;
            return Promise.reject(message);
        });
}

function updateTemplateRoute(data) {
    data.usingHash = store.state.home.usingPublicHash;
    return Axios.post(`${config.BASE_API_URL}${store.state.home.usingPublicHash ? '/public': ''}/templates/updateTemplateRoute/${data.templateId}`, data)
        .then(response => {
            return Promise.resolve(response.data);
        })
        .catch(error => {
            error = error.response;
            const message = (error && error.data && error.data.message) || error.statusText;
            return Promise.reject(message);
        });
}

function getTemplateRouteInfo(data){
    data.usingHash = store.state.home.usingPublicHash;
    return Axios.post(`${config.BASE_API_URL}${store.state.home.usingPublicHash ? '/public': ''}/templates/getTemplateRouteInfo/${data.templateId}`, data)
        .then(response => {
            return Promise.resolve(response.data);
        })
        .catch(error => {
            error = error.response;
            const message = (error && error.data && error.data.message) || error.statusText;
            return Promise.reject(message);
        });
}