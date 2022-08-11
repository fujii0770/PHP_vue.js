import config from "../app.config";
import Axios from "axios";
import store from "../store/store";

export var formIssuanceService;
export default (formIssuanceService = {
    getFormIssuances,
    showFormIssuance,
    getFormIssuancePlaceholder,
    deletes,
    uploadTemplate,
    editFormIssuance,
    convertExcelToImage,
    saveInputData,
    templateUseHistory,
    saveSettingFormIssuance,
    updateFormIssuanceStatus,
    getFile,
    uploadCSVImport,
    getCSVFormImportUploadStatus,
    getFileCSVImport,
    getLogTemplateCSV,
    loadFormIssuances,
    getFormIssuancesPage,
    getFormIssuanceStamp,
    getTemplateDepartment,
    postActionMultiple,
    getListReport,
    getListReportOther,
    getListTemplate,
    getListTemplateOther,
    exportFormIssuanceListToCSV,
    getDetailReport,
    getDetailReportOther,
    uploadExpTemplate,
    getListExpTemplate,
    showExpTemplate,
    deleteExpTemplate,
    getExpTemplate,
    getDepartmentUsers,
    getSavedCircularUsers,
    getSavedViewingUsers,
    adds,
    remove,
    clear,
    update,
    addViewing,
    removeViewing,
    getFormIssuancesIndex,
    autoCircularSave,
});

function getFormIssuances(queries) {
    return Axios.get(`${config.BASE_API_URL}/form-issuances`, {params: queries})
        .then(response => {
            return Promise.resolve(response.data);
        })
        .catch(error => {
            error = error.response;
            const message = (error && error.data && error.data.message) || error.statusText;
            return Promise.reject(message);
        });
}
function getFile(templateId) {
  return Axios.get(`${config.BASE_API_URL}/form-issuances/get/${templateId}`)
    .then(response => {
      return Promise.resolve(response.data);
    })
    .catch(error => {
      error = error.response;
      const message = (error && error.data && error.data.message) || error.statusText;
      return Promise.reject(message);
    });
}
function getFileCSVImport(data) {
  return Axios.get(`${config.BASE_API_URL}/form-issuances/${data.templateId}/getFileCSVImport/${data.csvId}`)
    .then(response => {
      return Promise.resolve(response.data);
    })
    .catch(error => {
      error = error.response;
      const message = (error && error.data && error.data.message) || error.statusText;
      return Promise.reject(message);
    });
}
function getLogTemplateCSV(data) {
  let resultParam = {
    'action': data.action
  }
  return Axios.get(`${config.BASE_API_URL}/form-issuances/${data.templateId}/getLogTemplateCSV/${data.logId}`, {params: resultParam})
    .then(response => {
      return Promise.resolve(response.data);
    })
    .catch(error => {
      error = error.response;
      const message = (error && error.data && error.data.message) || error.statusText;
      return Promise.reject(message);
    });
}
function uploadCSVImport(data) {
    let formData = new FormData();
    formData.append('file', data.file);
    formData.append('frm_template_id', data.frm_template_id);
    formData.append('frm_template_version', data.frm_template_version);

    let urUpload = `${config.BASE_API_URL}/form-issuances/${data.frm_template_id}/upload-csv-import`;
    let errMessage = `ファイルを読み取れませんでした。
                    ・CSVファイルであるかご確認ください。
                    ・ファイルがパスワード保護されていないかご確認ください。`;
    let failedMessage = 'CSVのアップロードに失敗しました。';

    return uploadFiles(urUpload, formData, errMessage, failedMessage);
}
function getCSVFormImportUploadStatus(data) {
    return Axios.get(`${config.BASE_API_URL}/form-issuances/${data.templateId}/upload-csv-import/${data.csvId}`)
        .then(response => {
            return Promise.resolve(response.data);
        })
        .catch(error => {
            error = error.response;
            const message = (error && error.data && error.data.message) || error.statusText;
            return Promise.reject(message);
        });
}

function showFormIssuance(templateId) {
    return Axios.get(`${config.BASE_API_URL}/form-issuances/show/${templateId}`)
        .then(response => {
            return Promise.resolve(response.data);
        })
        .catch(error => {
            error = error.response;
            const message = (error && error.data && error.data.message) || error.statusText;
            return Promise.reject(message);
        });
}

function getFormIssuancePlaceholder(data) {
    return Axios.get(`${config.BASE_API_URL}/form-issuances/${data.templateId}/placeholder/${data.frmType}`)
        .then(response => {
            return Promise.resolve(response.data);
        })
        .catch(error => {
            error = error.response;
            const message = (error && error.data && error.data.message) || error.statusText;
            return Promise.reject(message);
        });
}

function getFormIssuancesIndex() {
    return Axios.get(`${config.BASE_API_URL}/form-issuances/getFrmIndex`)
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
    return Axios.post(`${config.BASE_API_URL}/form-issuances/${data.templateId}/delete`, data)
      .then(response => {
          return Promise.resolve(response.data);
      })
      .catch(error => {
          error = error.response;
          const message = (error && error.data && error.data.message) || error.statusText;
          return Promise.reject(message);
      });
}

function deleteExpTemplate(data) {
    return Axios.post(`${config.BASE_API_URL}/form-issuances/${data.templateId}/deleteExpTemplate`, data)
      .then(response => {
          return Promise.resolve(response.data);
      })
      .catch(error => {
          error = error.response;
          const message = (error && error.data && error.data.message) || error.statusText;
          return Promise.reject(message);
      });
}

function uploadFiles(urUpload, formData, errMessage, failedMessage) {
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
        return fetch(urUpload, fetchOptions)
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
              const message = (error && error.data && error.data.message) || errMessage;
              return Promise.reject(message);
          });
    }else {
        return Axios.post(urUpload, formData,{
            headers: headers
        })
          .then(response => {
              return Promise.resolve(response.data);
          })
          .catch(error => {
              error = error.response;
              if(error.status == 422 || error.status == 413) {
                  var messValid = Object.values(error.data.errors);
                  return Promise.reject(failedMessage + messValid[0].toString());
              }
              const message = (error && error.data && error.data.message) || errMessage;
              return Promise.reject(message);
          });
    }
}

function uploadTemplate(data) {
    let formData = new FormData();
    formData.append('file', data.file);
    formData.append('frm_template_code', data.frm_template_code);
    formData.append('frm_type_flg', data.frm_type_flg);
    formData.append('frm_template_access_flg', data.frm_template_access_flg);
    formData.append('frm_template_edit_flg', data.frm_template_edit_flg);
    formData.append('remarks', data.remarks);
    let urUpload = `${config.BASE_API_URL}/form-issuances/upload`;
    let errMessage = `ファイルを読み取れませんでした。
                    ・PDF、Word、Excelファイルであるかご確認ください。
                    ・ファイルがパスワード保護されていないかご確認ください。`;
    let failedMessage = '明細テンプレートの登録は失敗しました。';

    return uploadFiles(urUpload, formData, errMessage, failedMessage);
}

function editFormIssuance(data){
    return Axios.post(`${config.BASE_API_URL}/form-issuances/edit/${data.templateId}` , data)
        .then(response => {
            return Promise.resolve(response.data);
        })
        .catch(error => {
            error = error.response;
            const message = (error && error.data && error.data.message) || error.statusText;
            return Promise.reject(message);
        });
}

function updateFormIssuanceStatus(data){
    return Axios.post(`${config.BASE_API_URL}/form-issuances/status/${data.templateId}` , data)
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
    return Axios.get(`${config.LOCAL_API_URL}/form-issuances/convertExcelToImage/${data.templateId}?storage_file_name=${data.storageFileName}&page=${data.page}`)
    .then(response => {
      return Promise.resolve(response.data);
    })
    .catch(error => {
      error = error.response;
      const message = (error && error.data && error.data.message) || error.statusText;
      return Promise.reject(message);
    });
}

function saveInputData(data){
    return Axios.post(`${config.BASE_API_URL}/form-issuances/${data.templateId}/save/inputData`, data)
        .then(response => {
            return Promise.resolve(response.data);
        })
        .catch(error => {
            error = error.response;
            const message = (error && error.data && error.data.message) || error.statusText;
            return Promise.reject(message);
        });
}
function templateUseHistory(templateId) {
    return Axios.get(`${config.BASE_API_URL}/form-issuances/templateUseHistory/${templateId}`)
        .then(response => {
            return Promise.resolve(response.data);
        })
        .catch(error => {
            error = error.response;
            const message = (error && error.data && error.data.message) || error.statusText;
            return Promise.reject(message);
        });
}
function saveSettingFormIssuance(data) {
    return Axios.post(`${config.BASE_API_URL}/form-issuances/${data.templateId}/setting`, data)
        .then(response => {
            return Promise.resolve(response.data);
        })
        .catch(error => {
            error = error.response;
            if (error.status == 422) {
                var messValid = Object.values(error.data.errors);
                return Promise.reject('明細テンプレートの登録は失敗しました。' + messValid[0].toString());
            }
            const message = (error && error.data && error.data.message) || error.statusText;
            return Promise.reject(message);
        });
}
function loadFormIssuances(data) {
    return Axios.get(`${config.LOCAL_API_URL}/form-issuances/${data.templateId}`, {params: data})
        .then(response => {
            return Promise.resolve(response.data);
        })
        .catch(error => {
            error = error.response;
            const message = (error && error.data && error.data.message) || error.statusText;
            return Promise.reject(message);
        });
}
function getFormIssuancesPage(options) {
    return Axios.get(`${config.LOCAL_API_URL}/form-issuances/page?page=${options.page}&filename=${options.filename}&is_thumbnail=${Number(options.isThumbnail)}`, {data: {nowait: true}})
        .then(response => {
            return Promise.resolve(response.data);
        })
        .catch(error => {
            error = error.response;
            const message = (error && error.data && error.data.message) || error.statusText;
            return Promise.reject(message);
        });
}
function getTemplateDepartment(templateId) {
    return Axios.get(`${config.BASE_API_URL}/form-issuances/getTemplateDepartment/${templateId}`)
        .then(response => {
            return Promise.resolve(response.data);
        })
        .catch(error => {
            error = error.response;
            const message = (error && error.data && error.data.message) || error.statusText;
            return Promise.reject(message);
        });
}
function getFormIssuanceStamp(data){
    return Axios.get(`${config.BASE_API_URL}/form-issuances/${data.templateId}/stamp` , data)
        .then(response => {
            return Promise.resolve(response.data);
        })
        .catch(error => {
            error = error.response;
            const message = (error && error.data && error.data.message) || error.statusText;
            return Promise.reject(message);
        });
}
function postActionMultiple(action, info) {
    return Axios.post(`${config.BASE_API_URL}/form-issuances/list/actionMultipleIssuance/${action}`, info)
        .then(response => {
            return Promise.resolve(response.data);
        })
        .catch(error => {
            error = error.response;
            const message = (error && error.data && error.data.message) || error.statusText;
            return Promise.reject(message);
        });
}
function getListReport(info) {
    return Axios.post(`${config.BASE_API_URL}/form-issuances/list/getListReport`, info)
        .then(response => {
            return Promise.resolve(response.data);
        })
        .catch(error => {
            error = error.response;
            const message = (error && error.data && error.data.message) || error.statusText;
            return Promise.reject(message);
        });
}
function getListReportOther(info) {
    return Axios.post(`${config.BASE_API_URL}/form-issuances/list/getListReportOther`, info)
        .then(response => {
            return Promise.resolve(response.data);
        })
        .catch(error => {
            error = error.response;
            const message = (error && error.data && error.data.message) || error.statusText;
            return Promise.reject(message);
        });
}
function getListTemplate(info) {
    return Axios.post(`${config.BASE_API_URL}/form-issuances/list/getListTemplate`, info)
        .then(response => {
            return Promise.resolve(response.data);
        })
        .catch(error => {
            error = error.response;
            const message = (error && error.data && error.data.message) || error.statusText;
            return Promise.reject(message);
        });
}
function getListTemplateOther(info) {
    return Axios.post(`${config.BASE_API_URL}/form-issuances/list/getListTemplateOther`, info)
        .then(response => {
            return Promise.resolve(response.data);
        })
        .catch(error => {
            error = error.response;
            const message = (error && error.data && error.data.message) || error.statusText;
            return Promise.reject(message);
        });
}
function exportFormIssuanceListToCSV(data) {
    return Axios.post(`${config.BASE_API_URL}/form-issuances/list/export-list`, data)
        .then(response => {
            return Promise.resolve(response.data);
        })
        .catch(error => {
            error = error.response;
            const message = (error && error.data && error.data.message) || error.statusText;
            return Promise.reject(message);
        });
}
function getDetailReport(id, finishedDate) {
    return Axios.get(`${config.BASE_API_URL}/form-issuances/list/${id}/detail?finishedDate=${finishedDate}`)
        .then(response => {
            return Promise.resolve(response.data);
        })
        .catch(error => {
            error = error.response;
            const message = (error && error.data && error.data.message) || error.statusText;
            return Promise.reject(message);
        });
}
function getDetailReportOther(id, finishedDate) {
    return Axios.get(`${config.BASE_API_URL}/form-issuances/list/${id}/detailOther?finishedDate=${finishedDate}`)
        .then(response => {
            return Promise.resolve(response.data);
        })
        .catch(error => {
            error = error.response;
            const message = (error && error.data && error.data.message) || error.statusText;
            return Promise.reject(message);
        });
}

function uploadExpTemplate(data) {
    let formData = new FormData();
    formData.append('file', data.file);
    formData.append('display_order', data.display_order);
    formData.append('remarks', data.remarks);
    let urUpload = `${config.BASE_API_URL}/form-issuances/uploadExpTemplate`;
    let errMessage = `明細Expテンプレートの登録に失敗しました。xlsxファイルであるかご確認ください。`;
    let failedMessage = '明細Expテンプレートの登録は失敗しました。';

    return uploadFiles(urUpload, formData, errMessage, failedMessage);
}

function getListExpTemplate(queries) {
    return Axios.get(`${config.BASE_API_URL}/form-issuances/exp-template-list`, {params: queries})
        .then(response => {
            return Promise.resolve(response.data);
        })
        .catch(error => {
            error = error.response;
            const message = (error && error.data && error.data.message) || error.statusText;
            return Promise.reject(message);
        });
}

function showExpTemplate(templateId) {
    return Axios.get(`${config.BASE_API_URL}/form-issuances/showExpTemplate/${templateId}`)
        .then(response => {
            return Promise.resolve(response.data);
        })
        .catch(error => {
            error = error.response;
            const message = (error && error.data && error.data.message) || error.statusText;
            return Promise.reject(message);
        });
}

function getExpTemplate(templateId) {
    return Axios.get(`${config.BASE_API_URL}/form-issuances/getExpTemplate/${templateId}`)
      .then(response => {
        return Promise.resolve(response.data);
      })
      .catch(error => {
        error = error.response;
        const message = (error && error.data && error.data.message) || error.statusText;
        return Promise.reject(message);
      });
}

function getDepartmentUsers(options) {
    return Axios.get(`${config.BASE_API_URL}/users-departments?filter=${options.filter}`)
        .then(response => {
            return Promise.resolve(response.data);
        })
        .catch(error => {
            error = error.response;
            const message = (error && error.data && error.data.message) || error.statusText;
            return Promise.reject(message);
        });
}

function getSavedCircularUsers(data) {
    return Axios.get(`${config.BASE_API_URL}/form-issuances/template/${data.templateId}/getSavedCircularUsers`, data)
        .then(response => {
            return Promise.resolve(response.data);
        })
        .catch(error => {
            error = error.response;
            const message = (error && error.data && error.data.message) || error.statusText;
            return Promise.reject(message);
        });
}

function getSavedViewingUsers(data) {
    return Axios.get(`${config.BASE_API_URL}/form-issuances/template/${data.templateId}/getSavedViewingUsers`, data)
        .then(response => {
            return Promise.resolve(response.data);
        })
        .catch(error => {
            error = error.response;
            const message = (error && error.data && error.data.message) || error.statusText;
            return Promise.reject(message);
        });
}

function adds(data) {
    return Axios.post(`${config.BASE_API_URL}/form-issuances/template/${data.users[0].frm_template_id}/users`, data)
        .then(response => {
            return Promise.resolve(response.data);
        })
        .catch(error => {
            error = error.response;
            const message = (error && error.data && error.data.message) || error.statusText;
            return Promise.reject(message);
        });
}

function addViewing(data) {
    return Axios.post(`${config.BASE_API_URL}/form-issuances/template/${data.frm_template_id}/viewing/add`, data)
        .then(response => {
            return Promise.resolve(response.data);
        })
        .catch(error => {
            error = error.response;
            const message = (error && error.data && error.data.message) || error.statusText;
            return Promise.reject(message);
        });
}

function update(circular_user) {
    return Axios.put(`${config.BASE_API_URL}/form-issuances/template/${store.state.formIssuance.frmTemplate.id}/users/${circular_user.id}`,circular_user)
        .then(response => {
            return Promise.resolve(response.data);
        })
        .catch(error => {
            error = error.response;
            const message = (error && error.data && error.data.message) || error.statusText;
            return Promise.reject(message);
        });
}

function remove(id) {
    return Axios.delete(`${config.BASE_API_URL}/form-issuances/template/${store.state.formIssuance.frmTemplate.id}/users/${id}`)
        .then(response => {
            return Promise.resolve(response.data);
        })
        .catch(error => {
            error = error.response;
            const message = (error && error.data && error.data.message) || error.statusText;
            return Promise.reject(message);
        });
}

function removeViewing(data) {
    return Axios.post(`${config.BASE_API_URL}/form-issuances/template/${data.frm_template_id}/viewing/remove`, data)
        .then(response => {
            return Promise.resolve(response.data);
        })
        .catch(error => {
            error = error.response;
            const message = (error && error.data && error.data.message) || error.statusText;
            return Promise.reject(message);
        });
}

function clear(frm_template_id) {
    return Axios.delete(`${config.BASE_API_URL}/form-issuances/template/${frm_template_id}/users/clear`, {data: {nowait: true}})
        .then(response => {
            return Promise.resolve(response.data);
        })
        .catch(error => {
            error = error.response;
            const message = (error && error.data && error.data.message) || error.statusText;
            return Promise.reject(message);
        });
}

function autoCircularSave(data) {
    return Axios.post(`${config.BASE_API_URL}/form-issuances/template/${data.templateId}/autoSave/${data.circularId}`, data)
        .then(response => {
            return Promise.resolve(response.data);
        })
        .catch(error => {
            error = error.response;
            const message = (error && error.data && error.data.message) || error.statusText;
            return Promise.reject(message);
        });
}