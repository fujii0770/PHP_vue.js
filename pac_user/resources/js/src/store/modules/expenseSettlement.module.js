import expenseSettlementService from "../../services/expenseSettlement.service"
import {Base64} from "js-base64";
import homeService from "../../services/home.service";


const state = {
    formName: null,
    formCode: null,
    purposeName: null,
    formType: null,
    tAppStatus: null,
    createForm: false,
    viewForm: false,
    editForm: false,
    isBackupData: false,
    tAppDuplicateData: null,
    storage_file_name: '',
    isCreateFormnAdvanceComplete: false
};

function downloadFileFromUrl(url) {
    if (!url) return
    const link = document.createElement('a');
    link.href = url;
    document.body.appendChild(link);
    link.click();
}

const actions = {
    getActuarialData({ dispatch, commit }, info) {
        return expenseSettlementService.getActuarialData(info).then(
            response => {
                return Promise.resolve(response.data);
            },
            error => {
                dispatch("alertError", error, { root: true });
                return Promise.reject(false);
            }
        );
    },

    getEpsMPurposeInfo({ dispatch, commit }, info) {
        return expenseSettlementService.getEpsMPurposeInfo(info).then(
            response => {
                return Promise.resolve(response.data);
            },
            error => {
                dispatch("alertError", error, { root: true });
                return Promise.reject(false);
            }
        );
    },
    getEpsTAppItemDetail({ dispatch, commit }, info) {
        return expenseSettlementService.getEpsTAppItemDetail(info).then(
            response => {
                return Promise.resolve(response.data);
            },
            error => {
                dispatch("alertError", error, { root: true });
                return Promise.reject(false);
            }
        );
    },

    getMFormPurposeDataSelect({ dispatch, commit }, info) {
        return expenseSettlementService.getMFormPurposeDataSelect(info).then(
            response => {
                return Promise.resolve(response.data);
            },
            error => {
                dispatch("alertError", error, { root: true });
                return Promise.reject(false);
            }
        );
    },
    getCurrentUserDepartmentInfo({ dispatch, commit }, info) {
        return expenseSettlementService.getCurrentUserDepartmentInfo(info).then(
            response => {
                return Promise.resolve(response.data);
            },
            error => {
                dispatch("alertError", error, { root: true });
                return Promise.reject(false);
            }
        );
    },
    getListTAppItems({ dispatch, commit }, info) {
        return expenseSettlementService.getListTAppItems(info).then(
            response => {
                return Promise.resolve(response.data);
            },
            error => {
                dispatch("alertError", error, { root: true });
                return Promise.reject(false);
            }
        );
    },
    getMPurposeDataSelect({ dispatch, commit }, info) {
        return expenseSettlementService.getMPurposeDataSelect(info).then(
            response => {
                return Promise.resolve(response.data);
            },
            error => {
                dispatch("alertError", error, { root: true });
                return Promise.reject(false);
            }
        );
    },

    saveExpense({ dispatch, commit }, info) {
        return expenseSettlementService.saveExpense(info).then(
            response => {
                if (response.hasOwnProperty('message')) {
                    dispatch("alertSuccess", response.message, { root: true });
                }
                return Promise.resolve(response.data);
            },
            error => {
                dispatch("alertError", error, { root: true });
                return Promise.reject(false);
            }
        );
    },
    updateExpense({ dispatch, commit }, info) {
        return expenseSettlementService.updateExpense(info).then(
            response => {
                if (response.hasOwnProperty('message')) {
                    dispatch("alertSuccess", response.message, { root: true });
                }
                return Promise.resolve(response);
            },
            error => {
                dispatch("alertError", error, { root: true });
                return Promise.reject(false);
            }
        );
    },
    updateEpsTAppItem({ dispatch, commit }, info) {
        return expenseSettlementService.updateEpsTAppItem(info).then(
            response => {
                if (response.hasOwnProperty('message')) {
                    dispatch("alertSuccess", response.message, { root: true });
                }
                return Promise.resolve(response.data);
            },
            error => {
                dispatch("alertError", error, { root: true });
                return Promise.reject(false);
            }
        );
    },

    duplicateEpsTAppItem({ dispatch, commit }, info) {
        return expenseSettlementService.duplicateEpsTAppItem(info).then(
            response => {
                if (response.hasOwnProperty('message')) {
                    dispatch("alertSuccess", response.message, { root: true });
                }
                return Promise.resolve(response.data);
            },
            error => {
                dispatch("alertError", error, { root: true });
                return Promise.reject(false);
            }
        );
    },

    duplicateEpsTAppAndEpsTAppItems({ dispatch, commit }, info) {
        return expenseSettlementService.duplicateEpsTAppAndEpsTAppItems(info).then(
            response => {
                if (response.hasOwnProperty('message')) {
                    dispatch("alertSuccess", response.message, { root: true });
                }
                return Promise.resolve(response.data);
            },
            error => {
                dispatch("alertError", error, { root: true });
                return Promise.reject(false);
            }
        );
    },

    createFormSettlementFromAdvanceForm({ dispatch, commit }, info) {
        return expenseSettlementService.createFormSettlementFromAdvanceForm(info).then(
            response => {
                if (response.hasOwnProperty('message')) {
                    dispatch("alertSuccess", response.message, { root: true });
                }
                return Promise.resolve(response.data);
            },
            error => {
                dispatch("alertError", error, { root: true });
                return Promise.reject(false);
            }
        );
    },

    duplicateEpsTAppAndEpsTAppItem({ dispatch, commit }, info) {
        return expenseSettlementService.duplicateEpsTAppAndEpsTAppItem(info).then(
            response => {
                if (response.hasOwnProperty('message')) {
                    dispatch("alertSuccess", response.message, { root: true });
                }
                return Promise.resolve(response.data);
            },
            error => {
                dispatch("alertError", error, { root: true });
                return Promise.reject(false);
            }
        );
    },

    createEpsTAppItem({ dispatch, commit }, info) {
        return expenseSettlementService.createEpsTAppItem(info).then(
            response => {
                if (response.hasOwnProperty('message')) {
                    dispatch("alertSuccess", response.message, { root: true });
                }
                return Promise.resolve(response.data);
            },
            error => {
                dispatch("alertError", error, { root: true });
                return Promise.reject(false);
            }
        );
    },

    getEpsMWtsmName({ dispatch, commit }, info) {
        return expenseSettlementService.getEpsMWtsmName(info).then(
            response => {
                return Promise.resolve(response.data);
            },
            error => {
                dispatch("alertError", error, { root: true });
                return Promise.reject(false);
            }
        );
    },
    getEpsMFormRelation({ dispatch, commit }, info) {
        return expenseSettlementService.getEpsMFormRelation(info).then(
            response => {
                return Promise.resolve(response.data);
            },
            error => {
                dispatch("alertError", error, { root: true });
                return Promise.reject(false);
            }
        );
    },

    deleteEpsTAppAndItems({ dispatch, commit }, info) {
        return expenseSettlementService.deleteEpsTAppAndItems(info).then(
            response => {
                if (response.hasOwnProperty('message')) {
                    dispatch("alertSuccess", response.message, { root: true });
                }
                return Promise.resolve(response.data);
            },
            error => {
                dispatch("alertError", error, { root: true });
                return Promise.reject(false);
            }
        );
    },

    displayAndValidatePrice({ dispatch, commit }, info) {
        return expenseSettlementService.displayAndValidatePrice(info).then(
            response => {
                if (response.hasOwnProperty('message')) {
                    dispatch("alertSuccess", response.message, { root: true });
                }
                return Promise.resolve(response.data);
            },
            error => {
                dispatch("alertError", error, { root: true });
                return Promise.reject(false);
            }
        );
    },
    downloadFile({ dispatch, commit }, fileId) {
        return expenseSettlementService.downloadFile(fileId).then(
            response => {
                if (response.data && response.data && response.data.url)
                    downloadFileFromUrl(response.data.url)
            },
            error => {
                dispatch("alertError", error, { root: true });
            }
        );
    },
    deleteFile({ dispatch, commit }, fileId) {
        return expenseSettlementService.deleteFile(fileId).then(
            response => {
                return Promise.resolve(true);
            },
            error => {
                dispatch("alertError", error, { root: true });
                return Promise.resolve(false);
            }
        );
    },

    updateExpenseFormInput({dispatch, commit, state}, data) {
        let dataPayload = data;
        let expenseInfo = dataPayload.update_expense
        return expenseSettlementService.updateExpenseFormInput(expenseInfo).then(
            response => {
                const data = response.data
                const byteString = Base64.atob(data.file_data);
                const ab = new ArrayBuffer(byteString.length);
                const ia = new Uint8Array(ab);
                for (let i = 0; i < byteString.length; i++) {
                    ia[i] = byteString.charCodeAt(i);
                }
                const splitName = data.file_name.split('.');
                const extension = splitName[splitName.length-1];
                let dataBlob = '';
                if(extension === 'xlsx'){
                    dataBlob = new Blob([ab], {type: "application/vnd.openxmlformats-officedocument.spreadsheetml.sheet"});
                } else {
                    dataBlob = new Blob([ab], { type: "application/vnd.openxmlformats-officedocument.wordprocessingml.document"});
                }
                dataBlob.lastModifiedDate = new Date();
                dataBlob.name = data.file_name;
                // Todo: max_document_size
                dataBlob.max_document_size = 10;
                let uploadData = {
                    file: dataBlob,
                    circular_id: null,
                    name: data.file_name,
                };
                let result = homeService.uploadFile(uploadData);
                commit('setStorageFileName', data.storage_file_name);
                return result;
            },
            error => {
                dispatch("alertError", error, { root: true });
                return Promise.resolve(false);
            }
        ).then(
            response => {
                dispatch("alertSuccess", response.message, { root: true });
                const fileAfterUploads = [];
                fileAfterUploads.push(response.data);

                let special_sit_flg = false
                const data = {
                    files: fileAfterUploads,
                    circular_id: null,
                    special_sit_flg: special_sit_flg,
                };
                const result = homeService.acceptUpload(data);
                return result;
            },
            error => {
                dispatch("alertError", error, { root: true });
                return Promise.resolve(false);
            }
        ).then(
            response => {
                let circularId = null
                if (response.data && response.data.circular && response.data.circular.id) {
                    circularId = response.data.circular.id
                }
                if (circularId && expenseInfo.hasOwnProperty('form_code')) {
                    let params = {
                        circular_id: circularId,
                        t_app_id: expenseInfo.t_app_id,
                        form_code: expenseInfo.form_code
                    }
                    let resultSaveExpenseInput = expenseSettlementService.saveExpenseInputData(params)

                }
                return Promise.resolve(response.data);

            },
            error => {
                dispatch("alertError", error, { root: true });
                return Promise.resolve(false);
            }
        );
    },
    updateExpenseCircularInfo({ dispatch, commit }, info) {
        return expenseSettlementService.updateExpenseCircularInfo(info).then(
            response => {
                return Promise.resolve(response.data);
            },
            error => {
                dispatch("alertError", error, { root: true });
                return Promise.resolve(false);
            }
        );
    },

    getCircularSentById({ dispatch, commit }, info) {
        return expenseSettlementService.getCircularSentById(info).then(
            response => {
                return Promise.resolve(response.data);
            },
            error => {
                dispatch("alertError", error, { root: true });
                return Promise.resolve(false);
            }
        );
    },

}

const mutations = {
    updateFormName(state, data) {
        state.formName = data
    },
    updateFormCode(state, data) {
        state.formCode = data
    },
    updatePurposeName(state, data) {
        state.purposeName = data
    },
    updateFormType(state, data) {
        state.formType = data
    },
    updateTAppStatus(state, data) {
        state.tAppStatus = data
    },
    updateCreateForm(state, data) {
        state.createForm = data
    },
    updateViewForm(state, data) {
        state.viewForm = data
    },
    updateEditForm(state, data) {
        state.editForm = data
    },
    updateTAppDuplicateData(state, data) {
        state.tAppDuplicateData = data
    },
    updateBackupData(state, data) {
        state.isBackupData = data
    },
    setStorageFileName(state, fileName) {
        state.storage_file_name = fileName;
    },
    updateCreateFormnAdvanceComplete(state, data) {
        state.isCreateFormnAdvanceComplete = data;
    },

};

export const expenseSettlement = {
    namespaced: true,
    state,
    actions,
    mutations
};
