import formIssuanceService from "../../services/formIssuance.service";
import {Base64} from "js-base64";
import fileDownload from "js-file-download";
import homeService from "../../services/home.service";

const state = {
  formIssuanceList: null,
  files: [],
  fileSelected: null,
  optionSetting: [],
    companyUsers: [],
    frmTemplate: null,
    selectUserView: [],
    checkOperationNotice: false,
    circularChangeListUserView:{},
    loadDepartmentUsersSuccess: false,
    selectUserChange: false,
    selectTemplateUserChange: false,
    commentTitle: '',
    commentContent: '',
    oldCircularUsers: [],
    departmentUsers: [],
};

const actions = {
  getFormIssuances({dispatch, commit}, queries) {
    return formIssuanceService.getFormIssuances(queries).then(
      response => {
        return Promise.resolve(response.data);
      },
      error => {
        dispatch("alertError", error, {root: true});
        return Promise.reject(false);
      }
    );
  },  
  loadFormIssuances({dispatch, commit}, data) {
    return formIssuanceService.loadFormIssuances(data).then(
        response => {
            return Promise.resolve(response.data);
        },
        error => {
            dispatch("alertError", error, {root: true});
            return Promise.reject(false);
        }
    );
  },
  getFormIssuancesPage({ dispatch, commit, state }, options) {
    return formIssuanceService.getFormIssuancesPage(options).then(
        response => {
            return {
                imageUrl: response.data.image,
            };
        },
        error => {
            //commit('getPageFailure');
            dispatch("alertError", error, { root: true });
            return Promise.reject({ fileChanged: false });
        }
    );
  },
  getFile({dispatch, commit}, data) {
    return formIssuanceService.getFile(data).then(
      response => {
        dispatch("alertSuccess", response.message, { root: true });
        return Promise.resolve(response.data);
      },
      error => {
        dispatch("alertError", error, {root: true});
        return Promise.reject(false);
      }
    );
  },
    uploadCSVImport({ dispatch, commit, state }, data) {
        return formIssuanceService.uploadCSVImport(data).then(
            response => {
                dispatch("alertSuccess", response.message, { root: true });
                return Promise.resolve(response.data);
            },
            error => {
                dispatch("alertError", error, { root: true });
                return Promise.resolve(false);
            }
        );
    },
    getCSVFormImportUploadStatus({dispatch, commit}, data) {
        return formIssuanceService.getCSVFormImportUploadStatus(data).then(
            response => {
                return Promise.resolve(response.data);
            },
            error => {
                dispatch("alertError", error, {root: true});
                return Promise.reject(false);
            }
        );
    },
    getFileCSVImport({dispatch, commit}, data) {
      return formIssuanceService.getFileCSVImport(data).then(
          response => {
            dispatch("alertSuccess", response.message, { root: true });
              return Promise.resolve(response.data);
          },
          error => {
              dispatch("alertError", error, {root: true});
              return Promise.reject(false);
          }
      );
  },
  getLogTemplateCSV({dispatch, commit}, data) {
    return formIssuanceService.getLogTemplateCSV(data).then(
      response => {
        dispatch("alertSuccess", response.message, { root: true });
        return Promise.resolve(response.data);
      },
      error => {
        dispatch("alertError", error, {root: true});
        return Promise.reject(false);
      }
    );
  },
    showFormIssuance({dispatch, commit}, data) {
        return formIssuanceService.showFormIssuance(data).then(
            response => {
                return Promise.resolve(response.data);
            },
            error => {
                dispatch("alertError", error, {root: true});
                return Promise.reject(false);
            }
        );
    },    
  getFormIssuancePlaceholder({dispatch, commit}, data) {
    return formIssuanceService.getFormIssuancePlaceholder(data).then(
        response => {
            return Promise.resolve(response.data);
        },
        error => {
            dispatch("alertError", error, {root: true});
            return Promise.reject(false);
        }
    );
  },
    getFormIssuancesIndex({dispatch, commit}) {
        return formIssuanceService.getFormIssuancesIndex().then(
            response => {
                return Promise.resolve(response.data);
            },
            error => {
                dispatch("alertError", error, {root: true});
                return Promise.reject(false);
            }
        );
    },
    templateUseHistory({dispatch, commit}, data) {
        return formIssuanceService.templateUseHistory(data).then(
            response => {
                return Promise.resolve(response.data);
            },
            error => {
                dispatch("alertError", error, {root: true});
                return Promise.reject(false);
            }
        );
    },
  deletes({ dispatch, commit }, data) {
    return formIssuanceService.deletes(data).then(
      response => {
        dispatch("alertSuccess", response.message, { root: true });
        return Promise.resolve(response.data);
      },
      error => {
        dispatch("alertError", error, { root: true });
        return Promise.reject(false);
      }
    );
  },
  uploadTemplate({ dispatch, commit, state }, data) {
    return formIssuanceService.uploadTemplate(data).then(
      response => {
        //ダイアログにエラーメッセージを出力するための措置。エラーの場合はidが空
        if(response.data.id){
          dispatch("alertSuccess", response.message, { root: true });
        }
        return Promise.resolve(response.data);
      },
      error => {
        dispatch("alertError", error, { root: true });
        return Promise.resolve(false);
      }
    );
  },  
  updateFormIssuanceStatus({ dispatch, commit, state }, data) {
    return formIssuanceService.updateFormIssuanceStatus(data).then(
        response => {
            dispatch("alertSuccess", response.message, { root: true });
            return Promise.resolve(response.data);
        },
        error => {
            dispatch("alertError", error, { root: true });
            return Promise.resolve(false);
        }
    );
  },
  getListExpTemplate({dispatch, commit}, queries) {
    return formIssuanceService.getListExpTemplate(queries).then(
      response => {
        return Promise.resolve(response.data);
      },
      error => {
        dispatch("alertError", error, {root: true});
        return Promise.reject(false);
      }
    );
  },
  showExpTemplate({dispatch, commit}, data) {
    return formIssuanceService.showExpTemplate(data).then(
        response => {
            return Promise.resolve(response.data);
        },
        error => {
            dispatch("alertError", error, {root: true});
            return Promise.reject(false);
        }
    );
  },
  deleteExpTemplate({ dispatch, commit }, data) {
    return formIssuanceService.deleteExpTemplate(data).then(
      response => {
        dispatch("alertSuccess", response.message, { root: true });
        return Promise.resolve(response.data);
      },
      error => {
        dispatch("alertError", error, { root: true });
        return Promise.reject(false);
      }
    );
  },
  changePositionFile({ dispatch, commit }, data) {
    commit('changePositionFile',data);
  },
  selectFile({commit,state}, file) {
    commit('selectFile',file);
  },
  setFiles({ dispatch, commit }, files) {
    commit('setFilesMutation',files);
  },
  editFormIssuance({ dispatch, commit }, data) {
    let circular = null;
    let circularDocument = null;
    return formIssuanceService.editFormIssuance(data).then(
      response => {
        const resData = response;
        const byteString = Base64.atob(resData.file_data);

        const ab = new ArrayBuffer(byteString.length);
        const ia = new Uint8Array(ab);
        for (let i = 0; i < byteString.length; i++) {
            ia[i] = byteString.charCodeAt(i);
        }
        const splitName = resData.file_name.split('.');
        const extension = splitName[splitName.length-1];
        let dataBlob = '';
        if(extension === 'xlsx'){
          dataBlob = new File([ab], `${data.frm_name}.${extension}`, {lastModified: new Date(), type: "application/vnd.openxmlformats-officedocument.spreadsheetml.sheet"});
        } else {
          dataBlob = new File([ab], `${data.frm_name}.${extension}`, {lastModified: new Date(), type: "application/vnd.openxmlformats-officedocument.wordprocessingml.document"});
        }
        //dataBlob.lastModifiedDate = new Date();
        //dataBlob.name = resData.file_name;
        //const file = new File([ab], resData.file_name, {lastModified: new Date(), type: "overide/mimetype"});
        let file = Object.defineProperty(dataBlob, 'max_document_size', {
          value: '10',
          writable: true
        });
        //file.max_document_size = 10;
        let uploadData = {
          file: file,
          circular_id: state.circular ? state.circular.id: null,
        }
        //uploadData.file.max_document_size = 10;
        return homeService.uploadFile(uploadData);
        //fileDownload(dataBlob, resData.file_name);
        //homeService.acceptUpload()
        //return Promise.resolve(response.data);
      },
      error => {
        dispatch("alertError", error, {root: true});
        return Promise.reject(false);
      }
    )
    .then(
      response => {
        commit('createCircular', response.data);
        dispatch("alertSuccess", response.message, { root: true });
        const fileAfterUploads = [];
        fileAfterUploads.push(response.data);
        const data = {files: fileAfterUploads,circular_id: state.circular ? state.circular.id: null};
        return homeService.acceptUpload(data);
      },
      error => {
        //commit('uploadFileFailure');
        if (error !== false){
          dispatch("alertError", error, { root: true });
        }
        return Promise.reject(false);
      }
    )
    .then(
        response => {
            circular = response.data.circular;
            circularDocument = response.data.fileInfo;
            const getStampData = {templateId: data.templateId};
            return formIssuanceService.getFormIssuanceStamp(getStampData);
        },
        error => {
            //commit('uploadFileFailure');
            if (error !== false){
                dispatch("alertError", error, { root: true });
            }
            return Promise.reject(false);
        }
    )
    .then(
      response => {
          if (response.data.stamps && response.data.stamps.length > 0){
              let data = {
                  signature: 0,
                  circular_id: circular.id,
                  active_id: circularDocument.circular_document_id,
                  downloadable: false,
                  files: [{
                      file_name: circularDocument.name,
                      server_file_name: circularDocument.server_file_name,
                      circular_document_id: circularDocument.circular_document_id,
                      confidential_flg: circularDocument.confidential_flg,
                      stamps: response.data.stamps,
                      texts: [],
                      deleteInfo: [],
                      comments: circularDocument.comments,
                      parent_send_order: circularDocument.parent_send_order,
                      update_at: circularDocument.update_at,
                  }]
              };
              return homeService.saveFile(data);
          }else{
              return Promise.resolve(response.data);
          }
      },
      error => {
        //commit('uploadFileFailure');
          if (error !== false){
              dispatch("alertError", error, { root: true });
          }
        return Promise.reject(false);
      }
    )
    .then(
        response => {
            //commit('createCircular', response.data);
            //commit('uploadFileSuccess', response.data);
            // commit('application/updateCommentTitle', '' , {root : true});
            // commit('application/updateCommentContent', '' , {root : true});
            // commit('application/updateListUserView', [] , {root : true});
            return Promise.resolve(circular);
        },
        error => {
            //commit('uploadFileFailure');
            if (error !== false){
                dispatch("alertError", error, { root: true });
            }
            return Promise.resolve(false);
        }
    );
  },
  convertExcelToImage({dispatch, commit}, data){
    return formIssuanceService.convertExcelToImage(data).then(
      response => {
        if(!response) return;
        return Promise.resolve(response.data);
      },
      error => {
        dispatch("alertError", error, { root: true });
        return Promise.resolve(false);
      }
    );
  },
  saveInputData({ dispatch, commit }, data){
    return formIssuanceService.saveInputData(data).then(
      response => {
        return Promise.resolve(response.data);
      },
      error => {
        dispatch("alertError", error, { root: true });
        return Promise.resolve(false);
      }
    )
        // 自動回覧を実施
        .then(
            response => {
                const saveData = {circularId: data.circularId, templateId: data.templateId};
                return formIssuanceService.autoCircularSave(saveData);
            },
            error => {
                //commit('uploadFileFailure');
                if (error !== false){
                    dispatch("alertError", error, { root: true });
                }
                return Promise.resolve(false);
            }
        );
  },
  getTemplateDepartment({dispatch, commit}, data) {
    return formIssuanceService.getTemplateDepartment(data).then(
        response => {
            return Promise.resolve(response.data);
        },
        error => {
            dispatch("alertError", error, {root: true});
            return Promise.reject(false);
        }
    );
  },
  saveSettingFormIssuance({ dispatch, commit }, data){
    return formIssuanceService.saveSettingFormIssuance(data).then(
      response => {
        commit('setFilesMutation',[response.data]);
        dispatch("alertSuccess", response.message, { root: true });
        return Promise.resolve(response.data);
      },
      error => {
        dispatch("alertError", error, { root: true });
        return Promise.resolve(false);
      }
    );
  },
  getListReport({ dispatch, commit }, info) {        
    return formIssuanceService.getListReport(info).then(
      response => {
        return Promise.resolve(response.data);
      },
      error => {
        dispatch("alertError", error, { root: true });
        return Promise.reject(false);
      }
    );
  }, 
  getListReportOther({ dispatch, commit }, info) {        
    return formIssuanceService.getListReportOther(info).then(
      response => {
        return Promise.resolve(response.data);
      },
      error => {
        dispatch("alertError", error, { root: true });
        return Promise.reject(false);
      }
    );
  }, 
  getListTemplate({ dispatch, commit }, info) {        
    return formIssuanceService.getListTemplate(info).then(
      response => {
        return Promise.resolve(response.data);
      },
      error => {
        dispatch("alertError", error, { root: true });
        return Promise.reject(false);
      }
    );
  }, 
  getListTemplateOther({ dispatch, commit }, info) {        
    return formIssuanceService.getListTemplateOther(info).then(
      response => {
        return Promise.resolve(response.data);
      },
      error => {
        dispatch("alertError", error, { root: true });
        return Promise.reject(false);
      }
    );
  }, 
  exportFormIssuanceListToCSV ({ dispatch, commit}, info) {
    return formIssuanceService.exportFormIssuanceListToCSV(info).then(
        response => {
            return Promise.resolve(response.data);
        },
        error => {
            dispatch("alertError", error, { root: true });
            return Promise.reject(false);
        }

    );
  },
  getDetailReport({ dispatch, commit }, {id, finishedDate}) {       
    return formIssuanceService.getDetailReport(id, finishedDate).then(
      response => {
        return Promise.resolve(response.data);
      },
      error => {              
        dispatch("alertError", error, { root: true });
        return Promise.reject(false);
      }
    );
  },  
  getDetailReportOther({ dispatch, commit }, {id, finishedDate}) {
    return formIssuanceService.getDetailReportOther(id, finishedDate).then(
      response => {
        return Promise.resolve(response.data);
      },
      error => {              
        dispatch("alertError", error, { root: true });
        return Promise.reject(false);
      }
    );
  },  
  postActionMultiple({ dispatch, commit }, {action, info}) {
    return formIssuanceService.postActionMultiple(action, info).then(
        response => {
          dispatch("alertSuccess", response.message, { root: true });
          return Promise.resolve(response.data);
        },
        error => {
          dispatch("alertError", error, { root: true });
          return Promise.reject(false);
        }
    );
  },
  uploadExpTemplate({ dispatch, commit, state }, data) {
    return formIssuanceService.uploadExpTemplate(data).then(
      response => {
        dispatch("alertSuccess", response.message, { root: true });
        return Promise.resolve(response.data);
      },
      error => {
        dispatch("alertError", error, { root: true });
        return Promise.resolve(false);
      }
    );
  },
  getExpTemplate({dispatch, commit}, data) {
    return formIssuanceService.getExpTemplate(data).then(
      response => {
        dispatch("alertSuccess", response.message, { root: true });
        return Promise.resolve(response.data);
      },
      error => {
        dispatch("alertError", error, {root: true});
        return Promise.reject(false);
      }
    );
  },
    getDepartmentUsers({ dispatch, commit }, options) {
        formIssuanceService.getDepartmentUsers(options).then(
            response => {
                if(!response) return;
                commit('getDepartmentUsersSuccess', response.data);
            },
            error => {
                dispatch("alertError", error, { root: true });
            }
        );
    },
    getSavedCircularUsers({ dispatch, commit }, data) {
        return formIssuanceService.getSavedCircularUsers(data).then(
            response => {
                if(response.data.savedCircular){
                    commit("formIssuance/addSavedCircularUser", response.data.savedCircularUsers, { root: true });
                }
                return Promise.resolve(response.data);
            },
            error => {
                dispatch("alertError", error, { root: true });
            }
        );
    },
    getSavedViewingUsers({ dispatch, commit }, data) {
        return formIssuanceService.getSavedViewingUsers(data).then(
            response => {
                if(response.data.savedViewing){
                    commit("formIssuance/updateListUserView", response.data.savedViewingUsers, { root: true });
                }
                return Promise.resolve(response.data);
            },
            error => {
                dispatch("alertError", error, { root: true });
            }
        );
    },
    addCircularUsers({ dispatch, commit }, data) {
        return formIssuanceService.adds(data).then(
            response => {
                if(!response) return;
                commit("formIssuance/addCircularUserSuccess", response.data, { root: true });
                commit("setSelectUserChange");
                commit("setSelectTemplateUserChange");
                return Promise.resolve(response.data);
            },
            error => {
                dispatch("alertError", error, { root: true });
                return Promise.resolve(false);
            }
        );
    },
    addViewingUser({ dispatch, commit }, data) {
        return formIssuanceService.addViewing(data).then(
            response => {
                if(!response) return;
                return Promise.resolve(response.data);
            },
            error => {
                dispatch("alertError", error, { root: true });
                return Promise.resolve(false);
            }
        );
    },
    removeCircularUser({ dispatch, commit }, circular_user_id) {
        return formIssuanceService.remove(circular_user_id).then(
            response => {
                commit("formIssuance/removeCircularUserSuccess", response.data, { root: true });
                commit("setSelectUserChange");
                return Promise.resolve(true);
            },
            error => {
                commit("formIssuance/removeCircularUserSuccess", circular_user_id, { root: true });
                commit("setSelectUserChange");
                dispatch("alertError", error, { root: true });
                return Promise.resolve(false);
            }
        );
    },
    removeViewingUser({ dispatch, commit }, data) {
        return formIssuanceService.removeViewing(data).then(
            response => {
                return Promise.resolve(true);
            },
            error => {
                dispatch("alertError", error, { root: true });
                return Promise.resolve(false);
            }
        );
    },
    clearCircularUsers({ dispatch, commit }, frm_template_id) {
        return formIssuanceService.clear(frm_template_id).then(
            response => {
                if(!response) return;
                commit("formIssuance/clearCircularUsersSuccess", null, { root: true });
                commit("setSelectUserChange");
                commit("setSelectTemplateUserChange");
                return Promise.resolve(true);
            },
            error => {
                dispatch("alertError", error, { root: true });
                return Promise.resolve(false);
            }
        );
    },
    updateCircularUser({ dispatch, commit }, circular_user) {
        formIssuanceService.update(circular_user).then(
            response => {
                commit("updateCircularUserSuccess", response.data, { root: true });
                commit("setSelectUserChange");
            },
            error => {
                dispatch("alertError", error, { root: true });
            }
        );
    },
    // loadFrmTemplate(frm_template_id) {
    //     commit('createFrmTemplateCircular', frm_template_id);
    // },
    updateCircularUsers({ dispatch,commit,state }, users) {
        if(!users || users.length <= 0) return;
        let data = users.slice();
        //    const creator_company_id = data[0] ? data[0].mst_company_id: null;
        //  let index = 0;
        let old = data[0];

        for(let index = 1; index< data.length; index++){
            old = data[index -1];
            let user = data[index];
            if (Array.isArray(user)){
                if (user.length > 0){
                    user = user[0]
                    data[index] = user;
                }else{
                    continue;
                }
            }
            let parent_send_order = parseInt(old.parent_send_order);
            let child_send_order = parseInt(old.child_send_order) + 1;

            const old_company_id = old ? old.mst_company_id : null;
            const user_company_id = user ? user.mst_company_id : null;

            // 20200402 - Hoapx: fix No65
            /*if(old_company_id !== null && creator_company_id !== old_company_id && old_company_id === user_company_id) {
               dispatch("alertError", '社内／社外ともに設定可能。ただし、社外は窓口の1人のみ設定可能。', { root: true });
               return false;
            }

            if(old_company_id !== creator_company_id && creator_company_id === user_company_id) {
               dispatch("alertError", '社外宛先の後に社内宛先は設定不可。', { root: true });
               return false;
            }*/

            if(old_company_id !== user_company_id || user_company_id === null) {
                parent_send_order += 1;
                child_send_order = 1;
            }
            if (user.parent_send_order != parent_send_order || user.child_send_order != child_send_order){
                commit('homeUpdateCircularUserOrder', {id: user.id,parent_send_order:parent_send_order, child_send_order: child_send_order});
            }
        }

        homeService.updateCircularUsers(data).then(
            response => {

            },
            error => {
                commit('rollBackCircularUsers');
                dispatch("alertError", error, { root: true });
            }
        );
        commit('homeUpdateCircularUsers',data);
    },
    clearState({ dispatch, commit, state }) {
        commit('homeClearState');
        commit('updateListUserView', []);
    },
};

const mutations = {
  changePositionFile(state, data) {
    if(!state.files[data.from]) return;
    state.files.splice(data.to, 0, state.files.splice(data.from, 1)[0]);
  },
  selectFile(state, file) {
    state.fileSelected = file;
  },
  setFilesMutation(state, files) {
    state.files = files;
  },
  setOptionSetting(state, data) {
    state.optionSetting = data;
  },

    getDepartmentUsersSuccess(state, data) {
        state.departmentUsers = [];
        state.departmentUsers.push(...data);
        state.loadDepartmentUsersSuccess = !state.loadDepartmentUsersSuccess;
    },
    createFrmTemplateCircular(state, frm_template) {
        if(!state.frmTemplate) {
            state.frmTemplate = frm_template;
            state.frmTemplate.users = [];
        }
    },
    addCircularUserSuccess(state, datas) {
        if(!datas) return;
        if(!state.frmTemplate) {
            state.frmTemplate = {
                id : datas[0].frm_template_id,
                    users : [],
            }
        };
        datas = datas.map(item => {item.isAddNew = true; return item});
        state.frmTemplate.users.push(...datas);
    },
    addSavedCircularUser(state, datas) {
        if(!datas) return;
        if(!state.frmTemplate) {
            state.frmTemplate = {
                id : datas[0].frm_template_id,
                users : [],
            }
        }
        if(state.frmTemplate.users.length <= 0){
            datas = datas.map(item => {item.isAddNew = true; return item});
            state.frmTemplate.users.push(...datas);
        }
    },
    setSelectUserChange(state) {
        state.selectUserChange = !state.selectUserChange;
    },
    setSelectTemplateUserChange(state) {
        state.selectTemplateUserChange = !state.selectTemplateUserChange;
    },
    removeCircularUserSuccess(state, newUsers) {
        if(!state.frmTemplate) return;
        if (Array.isArray(newUsers)){
            state.frmTemplate.users.length = 0;
            state.frmTemplate.users.push.apply(state.frmTemplate.users, newUsers);
        }
    },
    clearCircularUsersSuccess(state) {
        if(!state.frmTemplate) return;
        if(!state.frmTemplate.users) return;
        if(state.frmTemplate.users.length <= 1) return;
        state.frmTemplate.users.splice(1, state.frmTemplate.users.length);
    },
    updateCircularUserSuccess(state, circular_user) {
        if(!state.frmTemplate) return;
        if(!state.frmTemplate.users) return;
        state.frmTemplate.users.map(item => {
            if(item.id === circular_user.id) {
                item.circular_status = circular_user.circular_status;
            }
        })
    },
    updateCircularChangeListUserView(state, value){
        state.circularChangeListUserView[value.id] = value.data;
    },
    addUserView(state, value) {
        state.selectUserView.push(value);
    },
    updateListUserView(state, value){
        state.selectUserView = value;
    },
    updateCommentTitle(state, value) {
        state.commentTitle = value;
    },
    updateCommentContent(state, value) {
        state.commentContent = value;
    },
    homeUpdateCircularUserOrder(state, user) {
        if(!user) return;
        if(!state.frmTemplate) return;
        if(!state.frmTemplate.users) return;
        state.frmTemplate.users.map(item => {
            if(item.id === user.id) {
                item.parent_send_order = user.parent_send_order;
                item.child_send_order = user.child_send_order;
            }
        })
    },
    rollBackCircularUsers(state) {
        state.frmTemplate.users = state.oldCircularUsers.slice();
        state.oldCircularUsers = [];
    },
    homeUpdateCircularUsers(state, data) {
        if(!state.frmTemplate) return;
        state.oldCircularUsers = state.frmTemplate.users.slice();
        state.frmTemplate.users = data;
        state.selectUserChange = !state.selectUserChange;
    },
    homeClearState(state) {
        state.files = [];
        state.frmTemplate = null;
    },

};

export const formIssuance = {
  namespaced: true,
  state,
  actions,
  mutations
};
