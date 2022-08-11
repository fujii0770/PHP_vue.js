import templateService from "../../services/template.service";
import config from "../../app.config";
import Axios from "axios/index";
import circularsService from "../../services/circulars.service";
import {Base64} from "js-base64";
import fileDownload from "js-file-download";
import homeService from "../../services/home.service";
import {CIRCULAR} from "../../enums/circular"

const state = {
  templateList: null,
  files: [],
  homeFiles:[],
  fileSelected: null,
  storage_file_name:'',
  templateId: null,
  templateEditFlg: false,
  homeFileSelected: null,
  stampUsed: null,
  circular: null,
  title: '',
  company_logos:null,
  currentViewingUser:{},
  circular_id:null,
  stamps: [],
  parent_send_order: '',
  send_circular_template_edit_flg: false,
  display_temp_edit: 0,
  no_placeHolder: 0
};

const actions = {
  getTemplates({dispatch, commit}, queries) {
    return templateService.getTemplates(queries).then(
      response => {
        return Promise.resolve(response.data);
      },
      error => {
        dispatch("alertError", error, {root: true});
        return Promise.reject(false);
      }
    );
  },
  getTemplatesEdit({dispatch, commit}, queries) {
    return templateService.getTemplatesEdit(queries).then(
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
    return templateService.deletes(data).then(
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
  sendTemplateEditFlg({ dispatch, commit }, data) {
    return templateService.sendTemplateEditFlg(data).then(
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
  uploadFiles({ dispatch, commit, state }, data) {
    return templateService.uploadFiles(data).then(
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
  changePositionFile({ dispatch, commit }, data) {
    commit('changePositionFile',data);
  },
  selectFile({commit,state}, file) {
    commit('selectFile',file);
  },
  setFiles({ dispatch, commit }, files) {
    commit('setFilesMutation',files);
  },
  setHomeFiles({ dispatch, commit }, files) {
    commit('setHomeFilesMutation',files);
  },
  editTemplate({ dispatch, commit }, data) {
      const special_sit_flg = data.special_sit_flg;
      const circular_id = data.circular_id;
      const templateId = data.templateId;
      data.circular_temp_edit = state.send_circular_template_edit_flg;
    return templateService.editTemplate(data).then(
      response => {
        const data = response;
        commit('setNoPlaceholder',data.no_placeHolder);
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
        dataBlob.max_document_size = 10;
        //const file = new File([ab], data.file_name, {lastModified: new Date(), type: "overide/mimetype"});
        /*let file = Object.defineProperty(dataBlob, 'max_document_size', {
          value: '10',
          writable: true
        });*/
        //file.max_document_size = 10;
        let uploadData = {
          file: dataBlob,
          circular_id: state.circular ? state.circular.id: null,
          name:data.file_name,
        };
        let result = homeService.uploadFile(uploadData);
        commit('setStorageFileName',data.storage_file_name);
        return result;
      },
      error => {
        dispatch("alertError", error, {root: true});
        return Promise.reject(false);
      }
    )
    .then(
      response => {
        //commit('createCircular', response.data);
        dispatch("alertSuccess", response.message, { root: true });
        const fileAfterUploads = [];
        fileAfterUploads.push(response.data);

        if(state.templateEditFlg){
          const data = response.data;
          commit('setServerNameAndServerPath',response.data);
          return state.homeFiles;
        }else{
        const data = {files: fileAfterUploads,circular_id: state.circular ? state.circular.id: null,
            special_sit_flg:special_sit_flg,templateId:templateId ? templateId:null};
        const result = homeService.acceptUpload(data); 
          return result;
        }
      },
      error => {
        //commit('uploadFileFailure');
        dispatch("alertError", error, { root: true });
        return Promise.resolve(false);
      }
    )
    .then(
      response => {
        if(state.templateEditFlg){
          commit('setCircularId',circular_id);
        }else{
          commit('setCircularId',response.data.circular.id);
        }
        if(state.templateEditFlg || state.send_circular_template_edit_flg){
          let fileData = {circularInfo: state.circular_id,storage_file_name: state.storage_file_name,templateId: state.templateId};
          let templateEditResult = templateService.saveInputEditTemplate(fileData);
          //commit('setCirularTemplateEditFlg', false);
        }

        return Promise.resolve(response.data);
      },
      error => {
        //commit('uploadFileFailure');
        dispatch("alertError", error, { root: true });
        return Promise.resolve(false);
      }
    );
  },
  loadCircular({ dispatch,commit }, id) {
    return homeService.loadCircular(id).then(
      response => {
          //commit('disableAccessCodeFlg');
          delete response.data.circular.first_page_data; // 暫定対処として 詳細:PAC_5-1278

          const data = response.data;
          const user = JSON.parse(getLS('user'));

          data.files.forEach(file => {
            file.overlay_hidden_flg=false;
            file.enableDelete = (!data.circular || (data.circular.circular_status === CIRCULAR.SAVING_STATUS) || (user && data.circular.circular_status === CIRCULAR.SEND_BACK_STATUS && file.create_user_id === user.id)|| (user && data.circular.circular_status === CIRCULAR.CIRCULATING_STATUS && file.create_user_id === user.id && file.origin_env_flg === config.APP_SERVER_ENV && file.origin_edition_flg === config.APP_EDITION_FLV && file.origin_server_flg === config.APP_SERVER_FLG));
          });

          commit('loadCircularSuccess', data);

          commit('pushFiles', data.files);
          setTimeout(()=> {
            commit('initFileSelected');
          },500);
          return Promise.resolve(true);
      },
      error => {
          dispatch("alertError", error, { root: true });
          return Promise.resolve(false);
      }
    );
  },
  getTemplateEditStamp({dispatch, commit},id) {
    return templateService.getTemplateEditStamp({circularId: id}).then(
      response => {
        return Promise.resolve(response.data);
      },
      error => {
        dispatch("alertError", error, {root: true});
        return Promise.reject(false);
      }
    );
  },
  getTemplateEditText({dispatch, commit},id) {
    return templateService.getTemplateEditText({circularId: id}).then(
      response => {
        return Promise.resolve(response.data);
      },
      error => {
        dispatch("alertError", error, {root: true});
        return Promise.reject(false);
      }
    );
  },
  releaseTemplateEditFlg({dispatch, commit},id) {
    return templateService.releaseTemplateEditFlg({circularId: id}).then(
      response => {
        return Promise.resolve(response.data);
      },
      error => {
        dispatch("alertError", error, {root: true});
        return Promise.reject(false);
      }
    );
  },
  getTemplateNextUserCompletedFlg({ dispatch,commit }, id) {
    return templateService.getTemplateNextUserCompletedFlg({circularId: id}).then(
      response => {
        return Promise.resolve(response.data);
      },
      error => {
          dispatch("alertError", error, { root: true });
          return Promise.resolve(false);
      }
    );
  },
    saveFileAndSignature({dispatch, state},editData) {
      //PAC_5-1527 stamp_info 被りバグ修正
      templateService.templateStampInfoDelete({circular_id:state.circular_id}).then(
        response => {
          dispatch("alertSuccess", response.message, { root: true });
          return Promise.resolve(response.data);
        },
        error => {
          dispatch("alertError", error, { root: true });
          return Promise.resolve(false);
        }
      )
        if(state.homeFiles.length <= 0) return;
        let data = {
            signature: state.checkSentCircular == true ? 1 : 0,
            circular_id: state.circular_id ? state.circular_id : null,
            active_id: state.homeFileSelected ? state.homeFileSelected.circular_document_id: null,
            downloadable: false,
            files: []
        };

        if(state.homeFiles.length <= 0) return;
              data.files = state.homeFiles.map(file=> {
                const ret = {
                    file_name: file.name,
                    server_file_name: file.server_file_name,
                    circular_document_id: file.circular_document_id,
                    confidential_flg: file.confidential_flg,
                    stamps: editData[0] !=undefined ? editData[0] : [],
                    texts: editData[1] !=undefined ? editData[1] : [],
                    deleteInfo: [],
                    comments: file.tempComments,
                    parent_send_order: state.parent_send_order,
                    update_at: file.update_at,
                };
                return ret;
              });

        return homeService.saveFile(data).then(
          response => {
              if(!response) return;
              return Promise.resolve(true);
          },
          error => {
              dispatch("alertError", error.message, { root: true });
              return Promise.resolve(error);
          }
        );
    },
    getCircularTempEdit({dispatch, commit}, queries) {
      return templateService.getCircularTempEdit(queries).then(
        response => {
          commit('setDisplayTempEdit', response.data);
          return Promise.resolve(response.data);
        },
        error => {
          dispatch("alertError", error, {root: true});
          return Promise.reject(false);
        }
      );
    },
    tempEditStampInfoFix({dispatch, commit}, id) {
      return templateService.tempEditStampInfoFix({circularId: id}).then(
        response => {
          //commit('setDisplayTempEdit', response.data);
          return Promise.resolve(response.data);
        },
        error => {
          dispatch("alertError", error, {root: true});
          return Promise.reject(false);
        }
      );
    },
    templateEditS3delete({dispatch, commit}, id) {
      return templateService.templateEditS3delete({circularId: id}).then(
        response => {
          return Promise.resolve(response.data);
        },
        error => {
          dispatch("alertError", error, {root: true});
          return Promise.reject(false);
        }
      );
    },
  convertExcelToImage({commit,state}, data){
    return templateService.convertExcelToImage(data).then(
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
    return templateService.saveInputData(data).then(
      response => {
        return Promise.resolve(response.data);
      },
      error => {
        dispatch("alertError", error, { root: true });
        return Promise.resolve(false);
      }
    );
  },
  csvDownloadreserve({ dispatch, commit }, input){
    return templateService.csvDownloadreserve(input).then(
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
  getListCompleted({ dispatch, commit }, info) {        
    return templateService.getListCompleted(info).then(
      response => {
        return Promise.resolve(response.data);
      },
      error => {
        dispatch("alertError", error, { root: true });
        return Promise.reject(false);
      }
    );
  },
  CsvDownloadUserForm({ dispatch, commit }, data){
    return templateService.CsvDownloadUserForm(data).then(
      response => {
        return Promise.resolve(response.data);
      },
      error => {
        dispatch("alertError", error, { root: true });
        return Promise.resolve(false);
      }
    );
  },
  getCsvFlg({ dispatch, commit }, data){
    return templateService.getCsvFlg(data).then(
      response => {
        return Promise.resolve(response.data);
      },
      error => {
        dispatch("alertError", error, { root: true });
        return Promise.resolve(false);
      }
    );
  },
  templateCsvCheckEmail({ dispatch, commit }, data){
    return templateService.templateCsvCheckEmail(data).then(
      response => {
        return Promise.resolve(response.data);
      },
      error => {
        dispatch("alertError", error, { root: true });
        return Promise.resolve(false);
      }
    );
  },
  getTemplateInputComplete({ dispatch, commit }, data){
    return templateService.getTemplateInputComplete(data).then(
      response => {
        return Promise.resolve(response.data);
      },
      error => {
        dispatch("alertError", error, { root: true });
        return Promise.resolve(false);
      }
    );
  },
  getTemplateInfo({ dispatch, commit }, data){
    return templateService.getTemplateInfo(data).then(
      response => {
        return Promise.resolve(response.data);
      },
      error => {
        dispatch("alertError", error, { root: true });
        return Promise.resolve(false);
      }
    );
  },
  updateTemplateRoute({dispatch, commit}, info) {
    return templateService.updateTemplateRoute(info).then(
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
    getTemplateRoute({ dispatch, commit },info) {
        return templateService.getTemplateRouteInfo(info).then(
            response => {
                return Promise.resolve(response.data);
            },
            error => {
                dispatch("alertError", error, { root: true });
                return Promise.reject(false);
            }
        );
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
    state.files = [];
    state.files = files;
  },
  setHomeFilesMutation(state, files) {
    state.homeFiles = files;
  },
  setStorageFileName(state, fileName) {
    state.storage_file_name = fileName;
  },
  setTemplateId(state, id) {
    state.templateId = id;
  },
  setStampUsed(state, value) {
    state.stampUsed = value;
  },
  setCircularId(state, id) {
    state.circular_id = id;
  },
  setCircular(state, data) {
    state.circular = data;
  },
  setHomeFileSelected(state, data) {
    state.homeFileSelected = data;
  },
  setTemplateEditFlg(state, boolean) {
    state.templateEditFlg = boolean;
  },
  setCirularTemplateEditFlg(state, boolean) {
    state.send_circular_template_edit_flg = boolean;
  },
  setDisplayTempEdit(state, data) {
    state.display_temp_edit = data;
  },
  onAddConfirmMutation(state, index) {
    state.fileSelected.placeholderData[index].confirm_flg = 1;
  },
  onReleaseMutation(state, index) {
    state.fileSelected.placeholderData[index].confirm_flg = 0;
  },
  pushFiles(state, files) {
    for (const file of files) {
        file.pagesInfo = file.pages; // 衝突回避
        const pageCount = file.pagesInfo.length;
        file.zoom = 100;
        file.actions = [];
        file.tempComments = [];
        file.maxpages = pageCount;

        const pages = new Array(pageCount);
        for (let i = 0; i < pages.length; i++) {
            pages[i] = {
                no: i + 1,
                stamps: [],
                texts: [],
                deleteInfo: [],
            };
    }

        file.pages = pages;
    }
    state.homeFiles = [];
    state.homeFiles = state.homeFiles.concat(files);
  },
  initFileSelected(state) {
    let loginUser = JSON.parse(getLS('user'));
    if(state.usingPublicHash) loginUser = {};

    state.homeFileSelected = state.homeFiles[state.homeFiles.findIndex(item => !item.confidential_flg || (item.confidential_flg && loginUser.mst_company_id === item.mst_company_id))];
  },
  setServerNameAndServerPath(state, data) {
    state.homeFiles[0].server_file_name = data.server_file_name;
    state.homeFiles[0].server_file_path = data.server_file_path;
  },
  loadCircularSuccess(state, data) {
    if(!data) return;
    state.circular = data.circular;
    state.title = data.title;
    if(state.title == '' && state.circular && state.circular.hasOwnProperty('users') && state.circular.users.length > 0){
            state.title = state.circular.users[0].title
  }
  state.company_logos = data.company_logos;
    state.currentViewingUser = data.current_viewing_user ?? null;
  },
  templateCurrentParentSendOrder(state, value) {
    state.parent_send_order = value;
  },
  setNoPlaceholder(state, boolean) {
    state.no_placeHolder = boolean;
  },
};

export const template = {
  namespaced: true,
  state,
  actions,
  mutations
};
