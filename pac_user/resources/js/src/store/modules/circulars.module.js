import circularsService from "../../services/circulars.service";
import { Base64 } from 'js-base64';
import fileDownload from "js-file-download";
import {CIRCULAR} from "../../enums/circular";
import homeService from "../../services/home.service";

const state = {
  unread: 0
};

const actions = {
    postActionMultiple({ dispatch, commit }, {action, info}) {
      return circularsService.postActionMultiple(action, info).then(
          response => {
            if(action == "downloadFile"){
              if(response && response.data){
                const data = response.data;
                // PAC_5-1092 BEGIN ファイルが終了したかどうかを確認します
                for (let n = 0;n < info.select_status.length; n++){
                    // 現在の状態
                    if(info.select_status[n].status === CIRCULAR.CIRCULAR_COMPLETED_STATUS) {
                        homeService.updateCircularStatus(info.select_status[n].id, CIRCULAR.CIRCULAR_COMPLETED_SAVED_STATUS).then(
                            response => {
                                commit('updateCircularsStatus', CIRCULAR.CIRCULAR_COMPLETED_SAVED_STATUS);
                            },
                            error => {

                            }
                        )
                    }
                  }
                // PAC_5-1092 END

                if(data.fileName && data.file_data){
                  const byteString = Base64.atob(data.file_data);
                  const ab = new ArrayBuffer(byteString.length);
                  const ia = new Uint8Array(ab);
                  for (let i = 0; i < byteString.length; i++) {
                      ia[i] = byteString.charCodeAt(i);
                  }
                  const dataBlob = new Blob([ab]);
                  fileDownload(dataBlob, data.fileName);
                }
              }              
            }
            dispatch("alertSuccess", response.message, { root: true });
            return Promise.resolve(response.data);
          },
          error => {
            dispatch("alertError", error, { root: true });
            return Promise.reject(false);
          }
      );
    },
    getListSave({ dispatch, commit }, info) {        
        return circularsService.getListSave(info).then(
          response => {
            return Promise.resolve(response.data);
          },
          error => {
            dispatch("alertError", error, { root: true });
            return Promise.reject(false);
          }
      );
    },     
    getListDocument({ dispatch, commit }, info) {        
        return circularsService.getListDocument(info).then(
          response => {
              if (response.data.status === false){
                  dispatch("alertError", response.message, { root: true });
                  return Promise.resolve(response.data.data);
              }
            return Promise.resolve(response.data);
          },
          error => {
            dispatch("alertError", error, { root: true });
            return Promise.reject(false);
          }
      );
    },

    deleteDocument({ dispatch, commit }, id) {
      return circularsService.deleteDocument(id).then(
        response => {
          if(!response) return;
          dispatch("alertSuccess", response.message, { root: true });
          return Promise.resolve(response.data);
        },
        error => {
          dispatch("alertError", error, { root: true });
          return Promise.resolve(false);
        }
      );
    },
    updateDocument({ dispatch, commit }, data) {
      return circularsService.updateDocument(data).then(
        response => {
          if(!response) return;
          dispatch("alertSuccess", response.message, { root: true });
          return Promise.resolve(response.data);
        },
        error => {
          dispatch("alertError", error, { root: true });
          return Promise.resolve(false);
        }
      );
    },
    automaticUpdateTimestamp({ dispatch, commit }, data) {
      return circularsService.automaticUpdateTimestamp(data).then(
        response => {
          if(!response) return;
          dispatch("alertSuccess", response.message, { root: true });
          return Promise.resolve(response.data);
        },
        error => {
          dispatch("alertError", error, { root: true });
          return Promise.resolve(false);
        }
      );
    },

    downloadDocument({ dispatch, commit }, id) {
        return circularsService.downloadDocument(id).then(
            response => {
                if(!response) return;
                if(response && response.data){
                    const data = response.data;

                    if(data.fileName && data.file_data){
                        const byteString = Base64.atob(data.file_data);
                        const ab = new ArrayBuffer(byteString.length);
                        const ia = new Uint8Array(ab);
                        for (let i = 0; i < byteString.length; i++) {
                            ia[i] = byteString.charCodeAt(i);
                        }
                        const dataBlob = new Blob([ab]);
                        fileDownload(dataBlob, data.fileName);
                    }
                }
                dispatch("alertSuccess", response.message, { root: true });
                return Promise.resolve(response.data);
            },
            error => {
                dispatch("alertError", error, { root: true });
                return Promise.resolve(false);
            }
        );
    },

    downloadDocumentList({ dispatch, commit }, input) {
        return circularsService.downloadDocumentList(input.ids, input.filename).then(
            response => {
                if(!response) return;
                dispatch("alertSuccess", response.message, { root: true });
                return Promise.resolve(response.data);
            },
            error => {
                dispatch("alertError", error, { root: true });
                return Promise.resolve(false);
            }
        );
    },

    downloadReserve({dispatch, commit}, input){
      return circularsService.downloadReserve(input.ids, input.filename, input.finishedDate, input.stampHistory, input.frmFlg,input.download, input.download_type,input.upload_id).then(
            response => {
                if(response && response.data){
                    const data = response.data;
                    if(data.fileName && data.file_data){
                        const byteString = Base64.atob(data.file_data);

                        const ab = new ArrayBuffer(byteString.length);
                        const ia = new Uint8Array(ab);
                        for (let i = 0; i < byteString.length; i++) {
                            ia[i] = byteString.charCodeAt(i);
                        }
                        const dataBlob = new Blob([ab]);
                        fileDownload(dataBlob, data.fileName);
                    }

                }
                dispatch("alertSuccess", response.message, {root: true});
                return Promise.resolve(response.data);
            },
            error => {
                dispatch("alertError", error, {root: true});
                return Promise.reject(false);
            }
        );
    },

    downloadLongTerm({dispatch, commit}, input){
        return circularsService.downloadLongTerm(input.ids, input.filename, input.finishedDate, input.stampHistory, input.frmFlg,input.download, input.download_type,input.upload_id).then(
            response => {
                if(response && response.data){
                    const data = response.data;
                    if(data.fileName && data.file_data){
                        const byteString = Base64.atob(data.file_data);

                        const ab = new ArrayBuffer(byteString.length);
                        const ia = new Uint8Array(ab);
                        for (let i = 0; i < byteString.length; i++) {
                            ia[i] = byteString.charCodeAt(i);
                        }
                        const dataBlob = new Blob([ab]);
                        fileDownload(dataBlob, data.fileName);
                    }

                }
                dispatch("alertSuccess", response.message, {root: true});
                return Promise.resolve(response.data);
            },
            error => {
                dispatch("alertError", error, {root: true});
                return Promise.reject(false);
            }
        );
    },

    downloadCsvReserve({dispatch, commit}, param){
        return circularsService.downloadCsvReserve(param).then(
            response => {
                dispatch("alertSuccess", response.message, {root: true});
                return Promise.resolve(response.data);
            },
            error => {
                dispatch("alertError", error, {root: true});
                return Promise.reject(false);
            }
        );
    },

    downloadDownloadRequestData({dispatch, commit}, info){
      return circularsService.downloadDownloadRequestData(info).then(
        response => {
          if(!response) return;
          if(response && response.data){
              const data = response.data;
              if(data.fileName && data.file_data){
                  const byteString = Base64.atob(data.file_data);
                  const ab = new ArrayBuffer(byteString.length);
                  const ia = new Uint8Array(ab);
                  for (let i = 0; i < byteString.length; i++) {
                      ia[i] = byteString.charCodeAt(i);
                  }
                  const dataBlob = new Blob([ab]);
                  fileDownload(dataBlob, data.fileName);
              }
          }
          dispatch("alertSuccess", response.message, { root: true });
          return Promise.resolve(response.data);
        },
        error => {
            dispatch("alertError", error, { root: true });
            return Promise.resolve(false);
        }
      );
    },

    reRequestDownload({dispatch, commit}, id){
      return circularsService.reRequestDownload(id).then(
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

    //PAC_5-2874 S
    sanitizingUpdate({dispatch, commit}, id){
        return circularsService.sanitizingUpdate(id).then(
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
    //PAC_5-2874 E

    getListSent({ dispatch, commit }, info) {        
      return circularsService.getListSent(info).then(
        response => {
          return Promise.resolve(response.data);
        },
        error => {
          dispatch("alertError", error, { root: true });
          return Promise.reject(false);
        }
    );
  }, 
    getListReceived({ dispatch, commit }, info) {        
      return circularsService.getListReceived(info).then(
        response => {
          return Promise.resolve(response.data);
        },
        error => {
          dispatch("alertError", error, { root: true });
          return Promise.reject(false);
        }
    );
  }, 
 
  getListCompleted({ dispatch, commit }, info) {        
    return circularsService.getListCompleted(info).then(
      response => {
        return Promise.resolve(response.data);
      },
      error => {
        dispatch("alertError", error, { root: true });
        return Promise.reject(false);
      }
    );
  }, 
    getListViewing({ dispatch, commit }, info) {
        return circularsService.getListViewing(info).then(
            response => {
                return Promise.resolve(response.data);
            },
            error => {
                dispatch("alertError", error, { root: true });
                return Promise.reject(false);
            }
        );
    },

    getDetailCircularUser({ dispatch, commit }, id) {
        return circularsService.getDetailCircularUser(id).then(
          response => {
            return Promise.resolve(response.data);
          },
          error => {              
            dispatch("alertError", error, { root: true });
            return Promise.reject(false);
          }
      );
    },
    getDetailCircularUserForCompleted({ dispatch, commit }, {id, finishedDate,longTermFlg,lid}) {
        return circularsService.getDetailCircularUserForCompleted(id,finishedDate,longTermFlg,lid).then(
            response => {
                return Promise.resolve(response.data);
            },
            error => {
                dispatch("alertError", error, { root: true });
                return Promise.reject(false);
            }
        );
    },
    getOriginCircularUrl({ dispatch, commit }, id) {
        return circularsService.getOriginCircularUrl(id).then(
          response => {
            return Promise.resolve(response.data);
          },
          error => {              
            dispatch("alertError", error, { root: true });
            return Promise.reject(false);
          }
      );
    },
    getOriginCircularUrlForCompleted({ dispatch, commit }, {id, finishedDate}) {
        return circularsService.getOriginCircularUrlForCompleted(id, finishedDate).then(
            response => {
                return Promise.resolve(response.data);
            },
            error => {
                dispatch("alertError", error, { root: true });
                return Promise.reject(false);
            }
        );
    },

    pullback({ dispatch, commit }, data) {
        return circularsService.pullback(data.id, data).then(
          response => {
            dispatch("alertSuccess", response.message, { root: true });
            return Promise.resolve(true);
          },
          error => {              
            dispatch("alertError", error.message, { root: true });
            return Promise.resolve({'message': error.message,'statusCode': error.statusCode});
          }
      );
    },
    reqSendBack({ dispatch, commit }, data) {
      return circularsService.requestSendBack(data.id, data).then(
        response => {
          dispatch("alertSuccess", response.message, { root: true });
          return Promise.resolve(true);
        },
        error => {
          dispatch("alertError", error.message, { root: true });
          return Promise.resolve({'message': error.message,'statusCode': error.statusCode});
        }
      );
    },
    checkAccessCode({ dispatch, commit }, data) {
      return circularsService.checkAccessCode(data).then(
        response => {
          return Promise.resolve(response.data);
        },
        error => {
          dispatch("alertError", error, { root: true });
          return Promise.reject(false);
        }
      );
    },
    getUnreadTotal({ dispatch, commit }) {
      circularsService.getUnreadTotal().then(
        response => {
          commit("getUnreadTotalSuccess", response.data);
        },
        error => {
          //dispatch("alertError", error, { root: true });
        }
      );
    },

    getCountCircularStatus({ dispatch, commit }, info) {
        return circularsService.getCountCircularStatus(info).then(
            response => {
                return Promise.resolve(response.data);
            },
            error => {
                dispatch("alertError", error, { root: true });
                return Promise.reject(false);
            }
        );
    },

    storeCircular({ dispatch, commit }, data) {
      return circularsService.storeCircular(data).then(
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
    getDownloadRequest({dispatch, commit}, info){
      return circularsService.getDownloadRequest(info).then(
        response => {
          return Promise.resolve(response.data);
        },
        error => {
          dispatch("alertError", error, { root: true });
          return Promise.reject(false);
        }
      );
    },

    deleteDownloadRequest({dispatch, commit}, info){
      return circularsService.deleteDownloadRequest(info).then(
        response => {
          if(!response) return;
          dispatch("alertSuccess", response.message, { root: true });
          return Promise.resolve(response.data);
        },
        error => {
          dispatch("alertError", error, { root: true });
          return Promise.resolve(false);
        }
      );
    },
    updateCircularStatus({dispatch, commit}, id){
        return circularsService.updateCircularStatus(id).then(
            response => {
                return Promise.resolve(response.data);
            },
            error => {
                dispatch("alertError", error, { root: true });
                return Promise.reject(false);
            }
        );
    },

    getLongtermIndex({ dispatch, commit }, data) {
      return circularsService.getLongtermIndex(data).then(
        response => {
          if(!response) return;
          //dispatch("alertSuccess", response.message, { root: true });
          return Promise.resolve(response.data);
        },
        error => {
          dispatch("alertError", error, { root: true });
          return Promise.resolve(false);
        }
      );
    },

    getLongtermIndexOption({ dispatch, commit }, data) {
      return circularsService.getLongtermIndexOption(data).then(
        response => {
          if(!response) return;
          //dispatch("alertSuccess", response.message, { root: true });
          return Promise.resolve(response.data);
        },
        error => {
          dispatch("alertError", error, { root: true });
          return Promise.resolve(false);
        }
      );
    },

    setLongtermIndex({ dispatch, commit }, data) {
      return circularsService.setLongtermIndex(data).then(
        response => {
          if(!response) return;
          dispatch("alertSuccess", response.message, { root: true });
          return Promise.resolve(response.data);
        },
        error => {
          dispatch("alertError", error, { root: true });
          return Promise.resolve(false);
        }
      );
    },

    setApprovalLongtermIndex({ dispatch, commit }, data) {
        return circularsService.setApprovalLongtermIndex(data).then(
            response => {
                if(!response) return;
                dispatch("alertSuccess", response.message, { root: true });
                return Promise.resolve(response.data);
            },
            error => {
                dispatch("alertError", error, { root: true });
                return Promise.resolve(false);
            }
        );
    },
    getTermIndexValue({ dispatch, commit }, data) {
        return circularsService.getLongTermIndexValue(data).then(
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
    // PAC_5-2377
    downloadAttachement({ dispatch, commit },data) {
        return circularsService.downloadAttachement(data).then(
            response => {
                if(!response) return;
                if(response && response.data){
                    const data = response.data;

                    if(data.fileName && data.file_data){
                        const byteString = Base64.atob(data.file_data);
                        const ab = new ArrayBuffer(byteString.length);
                        const ia = new Uint8Array(ab);
                        for (let i = 0; i < byteString.length; i++) {
                            ia[i] = byteString.charCodeAt(i);
                        }
                        const dataBlob = new Blob([ab]);
                        fileDownload(dataBlob, data.fileName);
                    }
                }
                dispatch("alertSuccess", response.message, { root: true });
                return Promise.resolve(response.data);
            },
            error => {
                dispatch("alertError", error, { root: true });
                return Promise.resolve(false);
            }
        );
    },
    saveLongTermDocument({ dispatch, commit }, data){
        return circularsService.saveLongTermDocument(data).then(
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
    longTermUpload({ dispatch, commit }, data){
        return circularsService.longTermUpload(data).then(
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
    //PAC_5-2279
    getMyFolders({ dispatch, commit }) {
        return circularsService.getMyFolders().then(
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
    updateFolderId({ dispatch, commit }, data) {
        return circularsService.updateFolderId(data).then(
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
    getCircularPageData({ dispatch, commit }, data) {
        return circularsService.getCircularPageData(data).then(
            response => {
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
  getUnreadTotalSuccess(state, total) {
    if(total === -1) {
      if(state.unread > 0) state.unread = state.unread - 1;
      return;
    }
    state.unread = parseInt(total);
  }
};

export const circulars = {
    namespaced: true,
    state,
    actions,
    mutations
};
