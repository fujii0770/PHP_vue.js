import homeService from "../../services/home.service";
import fileDownload from "js-file-download";
import {CIRCULAR} from "../../enums/circular"
import {cloudService} from "../../services/cloud.service";
import {Base64} from 'js-base64';
import config from "../../app.config"
import {CIRCULAR_USER} from "../../enums/circular_user";
import templateService from "../../services/template.service";

const ACTIONS = {
    ADD_STAMP: 'ADD_STAMP',
    UPDATE_STAMP: 'UPDATE_STAMP',
    DELETE_STAMP: 'DELETE_STAMP',

    ADD_TEXT: 'ADD_TEXT',
    UPDATE_TEXT: 'UPDATE_TEXT',
    DELETE_TEXT: 'DELETE_TEXT',

    ZOOM: 'ZOOM'

};

const state = {
    files: [],
    title: '',
    circularChangeListUserView:{},// PAC_5-1702 別回覧で登録した閲覧者が、データ上は登録されていないものの画面上にのみ表示される
    fileSelected: null,
    stamps: [],
    stampDisplays: [],
    stampSelected: null,
    textSelected: false,
    stickyPosition: null,
    StickySelected: false,
    noteHomeSelected: false,
    noteOneSelected: '',
    isUpdateStampOrder: false,
    first_page_image: null,
    circular: null,
    company_logos: null,
    oldCircularUsers: [],
    usingPublicHash: false,
    accessCodePopupActive: false,
    tmpData: null,
    hasAction: false,
    addStampHistory: false,
    checkSentCircular: false,
    selectUserChange: false,
    usingTas: false,
    currentUserIdentity: false, // 社内社外回覧フラグ 社内:false 社外:true
    addTextHistory: false, // 自社のみの捺印履歴フラグ
    disabledProceed: true,// 決定ボタンチェック
    tempComments: [], //社内社外宛先一時入力コメント
    parent_send_order: '',
    currentViewingUser: {},
    deviceType: {},
    closeCheck: true,   // 確認コーション必要
    template_flg: false,
    templateEditFlg: 0,
    templateNextUserCompletedFlg: false,
    cloudBoxFlg: false,//PAC_5-3116 Boxを表示する時Boxを移動できるスライドバーだけを表示する
};

const actions = {
    uploadFile({ dispatch, commit, state }, file) {
        const data = {file: file,circular_id: state.circular ? state.circular.id: null};
        commit('beginUploadFile');
        return homeService.uploadFile(data).then(
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
    attachmentUpload({ dispatch, commit, state }, file){
        const data = {file: file,circular_id: state.circular ? state.circular.id: null};
        return homeService.attachmentUpload(data).then(
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
    deleteAttachment({ dispatch, commit }, id) {
        return homeService.deleteAttachment(id).then(
            response => {
                if(!response) return;
                return Promise.resolve(true);
            },
            error => {
                dispatch("alertError", error, { root: true });
                return Promise.reject(false);
            }
        );
    },
    downloadAttachment({dispatch,commit},id){
        return homeService.downloadAttachement(id).then(
            response => {
                if(!response || !response.data) return Promise.reject(false);
                let byteString = Base64.atob(response.data.file_data);
                const ab = new ArrayBuffer(byteString.length);
                const ia = new Uint8Array(ab);
                for (let i = 0; i < byteString.length; i++) {
                    ia[i] = byteString.charCodeAt(i);
                }
                const dataBlob = new Blob([ab]);
                fileDownload(dataBlob, response.data.file_name);
                return Promise.resolve(true);
            },
            error => {
                dispatch("alertError", error, { root: true });
                return Promise.reject(false);
            }
        );
    },
    attachmentConfidentialFlg({dispatch,commit},data){
        return homeService.attachmentConfidentialFlg(data).then(
            response => {
                if(!response) return;
                return Promise.resolve(true);
            },
            error => {
                dispatch("alertError", error, { root: true });
                return Promise.reject(false);
            }
        );
    },
    getAttachment({dispatch,commit},circular_id){
        return homeService.getAttachment(circular_id).then(
            response => {
                if(!response) return;
                return Promise.resolve(response.data);
            },
            error => {
                dispatch("alertError", error, { root: true });
                return Promise.reject(false);
            }
        );
    },
    acceptUpload({ dispatch, commit, state }, files) {
      const data = {files: files,circular_id: state.circular ? state.circular.id: null};
      return homeService.acceptUpload(data).then(
        response => {
          commit('createCircular', response.data);

          const fileInfo = response.data.fileInfo;
          fileInfo.enableDelete = true;
          // 「プレビュー・捺印へ」でpdf変換後も改ページ調整出来るように
          // アップロードしたファイル情報を保持させる
          if (
            files.length === 1 &&
            files[0].server_file_name_for_office_soft &&
            (files[0].server_file_name_for_office_soft.endsWith(".xls") ||
              files[0].server_file_name_for_office_soft.endsWith(".xlsx"))
          ) {
            // アップロードファイルが1つだけで、excelファイルの時のみ
            dispatch("pageBreaks/setCircularIdAndRegisteredDocInfoList", {
              circularId: response.data.circular.id,
              registeredDocInfo: {
                circular_document_id: fileInfo.circular_document_id,
                document_data_update_at: fileInfo.document_data_update_at,
                fileAfterUploads: files
              }
            }, {root : true})
          }
          commit('pushFiles', [fileInfo]);
          commit('homeSelectFile', fileInfo);

          commit('application/updateCommentTitle', '' , {root : true});
          commit('application/updateCommentContent', '' , {root : true});
          commit('application/updateListUserView', [] , {root : true});
          commit('application/updateRequirePrint', null , {root : true}); // PAC_5-2245
          return Promise.resolve(true);
        },
        error => {
          //commit('uploadFileFailure');
          dispatch("alertError", error, { root: true });
          return Promise.resolve(false);
        }
      );
    },
    rejectUpload({ dispatch, commit }, files) {
      const data = {files: files};
      return homeService.rejectUpload(data).then(
        response => {
          return Promise.resolve(true);
        },
        error => {
          //commit('uploadFileFailure');
          dispatch("alertError", error, { root: true });
          return Promise.resolve(false);
        }
      );
    },
    getPage({ dispatch, commit, state }, options) {
        const fileSelected = state.fileSelected;
        if(!fileSelected) throw new Error("fileSelected is not set");

        return homeService.getPage(options).then(
            response => {
                const isOtherFileSelected = state.fileSelected != fileSelected;
                if (isOtherFileSelected) {
                    // 処理中断
                    return Promise.reject({ fileChanged: true });
                }

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
    extractPdfLine({ dispatch }, data) {
        return homeService.extractPdfLine(data).then(
          response => response.data,
          error => {
              dispatch("alertError", error, { root: true });
              return Promise.reject();
          }
        );
    },
    deleteCircularDocument({ dispatch, commit }, data) {
        return homeService.deleteCircularDocument(data).then(
          response => {
            return Promise.resolve(true);
          },
          error => {
              dispatch("alertError", error, { root: true });
              return Promise.reject(false);
          }
        );
    },
    renameCircularDocument({ dispatch, commit }, data) {
        return homeService.renameCircularDocument(data).then(
            response => {
                return Promise.resolve(response.data);
            },
            error => {
                dispatch("alertError", error, { root: true });
                return Promise.reject(false);
            }
        );
    },

    getDepartment({ dispatch, commit }) {
        return homeService.getDepartment().then(
          response => {
            return Promise.resolve(true);
          },
          error => {
              dispatch("alertError", error, { root: true });
              return Promise.reject(false);
          }
        );
    },

    clearState({ dispatch, commit, state }, data) {
        const filepaths = state.files.map(item => {return item.server_file_path});
        commit('homeClearState',data);
        // 非同期処理はhomeClearState後に行う
        // （並行処理のため）
        return homeService.deleteStoredFiles({filepaths: filepaths}).then(
          response => {
              return Promise.resolve(true);
          },
          error => {
              dispatch("alertError", error, { root: true });
              return Promise.resolve(false);
          }
        );
    },
    clearFileState({ dispatch, commit, state }, data) {
        const filepaths = state.files.map(item => {return item.server_file_path});
        commit('homeClearFileState',data);
        return homeService.deleteStoredFiles({filepaths: filepaths}).then(
          response => {
              return Promise.resolve(true);
          },
          error => {
              dispatch("alertError", error, { root: true });
              return Promise.resolve(false);
          }
        );
    },
    changePositionFile({ dispatch, commit }, data) {
        commit('homeChangePositionFile',data);
    },
    selectFile({commit,state}, file) {
        commit('homeSelectFile',file);
    },
    addEmptyFile({commit, state}) {
        commit('homeAddEmptyFile');
    },
    closeFile({commit}, file) {
        commit('homeCloseFile',file);
    },
    updateCurrentFileZoom({commit}, zoom) {
        commit('homeUpdateCurrentFileZoom',zoom);
    },
    selectStampFormIssuance({commit}, stampId) {
        commit('formIssuanceSelectStamp', stampId);
          },
    selectStamp({commit}, stampId) {
        commit('homeSelectStamp', stampId);
    },
    selectText({commit}) {
        commit('homeSelectText');
    },
    selectSticky({commit}) {
        commit('homeSelectSticky');
    },
    setStickyPosition({commit},data) {
        commit('homeSetStickyPosition',data);
    },
    updateStickyPosition({commit},data) {
        commit('homeUpdateStickyPosition',data);
    },
    addSticky({commit},data) {
        commit('homeAddSticky',data);
    },
    editSticky ({ commit }, data) {
      commit('homeEditSticky', data)
    },
    deleteSticky({commit},data) {
        commit('homeDeleteSticky',data);
    },
    selectNote({commit}) {
        commit('HomeSelectNote');
    },
    selectNoteOneItem({commit}, noteId) {
        commit('homeSelectNoteOne', noteId);
    },
    addFileStamp({commit}, data) {
        commit('homeAddFileStamp', data);
    },
    updateFileStamp({commit}, data) {
        commit('homeUpdateFileStamp', data);
    },
    deleteFileStamp({commit}, data) {
        commit('homeDeleteFileStamp', data);
    },
    addFileText({commit}, data) {
        commit('homeAddFileText', data);
    },
    updateFileText({commit}, data) {
        commit('homeUpdateFileText', data);
    },
    deleteFileText({dispatch,commit, state}, data) {
        commit('homeDeleteFileText', data);
    },
    updateFileComment({commit}, data) {
        commit('homeUpdateFileComment', data);
    },
    deleteFileComment({dispatch,commit, state}, data) {
        commit('homeDeleteFileComment', data);
    },
    addAction({commit}, action) {
        commit('homeAddAction', action);
    },
    undoAction({commit}) {
        commit('homeUndoAction');
    },
    updateStampDisplays({commit}, data) {
        commit('homeUpdateStampDisplays', data);
    },
    saveFile({dispatch, state}) {
        if(state.files.length <= 0) return;

        let data = {
            signature: 0,
            circular_id: state.circular ? state.circular.id : null,
            active_id: state.fileSelected ? state.fileSelected.circular_document_id: null,
            downloadable: false,
            files: []
        };

        data.files = state.files.map(file=> {
            const ret = {
                file_name: file.name,
                server_file_name: file.server_file_name,
                circular_document_id: file.circular_document_id,
                confidential_flg: file.confidential_flg,
                stamps: [],
                texts: [],
                deleteInfo: [],
                comments: file.tempComments,
                parent_send_order: state.parent_send_order,
                update_at: file.update_at,
                sticky_notes: file.sticky_notes,
            };
            file.pages.forEach(page=> {
                const _stamp = page.stamps.filter(item => !item.selected).map(stamp => {
                    const stamp_info = state.stamps.find(item => item.id === stamp.id);
                    const height = stamp.height / 3.7795275591;
                    return {
                        repeated: stamp.repeated ? stamp.repeated : false,
                        page: page.no,
                        stamp_data: stamp_info ? stamp_info.url : '',
                        x_axis: stamp.x  / 3.7795275591,
                        y_axis: (stamp.y / 3.7795275591) + height,
                        width: stamp.width / 3.7795275591,
                        height: height,
                        stamp_url: '',
                        id: stamp_info?stamp_info.db_id : null,
                        stamp_flg: stamp_info?stamp_info.stamp_flg : null,
                        time_stamp_permission: stamp_info?stamp_info.time_stamp_permission : 0,
                        serial: stamp_info?stamp_info.serial : null,
                        rotateAngle: stamp.rotateAngle,
                        opacity: stamp.opacity,
                    }
                });
                const _text = page.texts.map(text => {
                    return {
                        page: page.no,
                        text: text.text,
                        x_axis: text.x / 3.7795275591,
                        y_axis: text.y / 3.7795275591,
                        fontSize: text.fontSize / 3.7795275591,
                        fontFamily: text.fontFamily,
                        fontColor: text.hasOwnProperty('fontColor') ? text.fontColor : '#000000',
                    }
                });

                ret.stamps.push(..._stamp);
                ret.texts.push(..._text);
            })
            return ret;
        });
        return homeService.saveFile(data).then(
          response => {
              if(!response) return;
              //dispatch("clearFileState", null);
              dispatch("alertSuccess", response.message, { root: true });
              return Promise.resolve(true);
          },
          error => {
              dispatch("alertError", error.message, { root: true });
              return Promise.resolve(false);
          }
        );
    },
    editFileAndSignature({dispatch, state},options) {
        if(state.files.length <= 0) return;

        state.stamps = [];
        state.files.forEach(file=> {
                file.pages.forEach(page=> {
                    page.stamps.forEach(stamp=> {
                        const stamp_info = options.stampDisplays.find(item => item.id === stamp.id);
                        state.stamps.push(stamp_info);
                        if(state.stamps.findIndex(item => item.id === stamp.id) == -1){
                            state.stamps.push(stamp_info);
                        }
                    });
                });
        });
    },
    saveFileAndSignature({dispatch, state}, isSendBack = false) {
        if(state.files.length <= 0) return;
        let data = {
            signature: state.checkSentCircular == true ? 1 : 0,
            isSendBack: isSendBack,
            circular_id: state.circular ? state.circular.id : null,
            active_id: state.fileSelected ? state.fileSelected.circular_document_id: null,
            downloadable: false,
            files: []
        };
        data.files = state.files.map(file=> {
            const ret = {
                file_name: file.name,
                server_file_name: file.server_file_name,
                circular_document_id: file.circular_document_id,
                confidential_flg: file.confidential_flg,
                stamps: [],
                texts: [],
                deleteInfo: [],
                sticky_notes: file.sticky_notes,
                comments: file.tempComments,
                parent_send_order: state.parent_send_order,
                update_at: file.update_at,
            };
            file.pages.forEach(page=> {
                //const _stamp = page.stamps.filter(item => !item.selected).map(stamp => {
                const _stamp = page.stamps.map(stamp => {
                    const stamp_info = state.stamps.find(item => item.id === stamp.id);
                    const height = stamp.height / 3.7795275591;
                    return {
                        repeated: stamp.repeated ? stamp.repeated : false,
                        page: page.no,
                        stamp_data: stamp_info ? stamp_info.url : '',
                        x_axis: stamp.x  / 3.7795275591,
                        y_axis: (stamp.y / 3.7795275591) + height,
                        width: stamp.width / 3.7795275591,
                        height: height,
                        stamp_url: '',
                        id: stamp_info?stamp_info.db_id : null,
                        sid: stamp_info?stamp_info.sid : null,
                        stamp_flg: stamp_info?stamp_info.stamp_flg : null,
                        time_stamp_permission: stamp_info?stamp_info.time_stamp_permission : 0,
                        serial: stamp_info?stamp_info.serial : null,
                        rotateAngle: stamp.rotateAngle,
                        opacity: stamp.opacity,
                    }
                });
                const _text = page.texts.map(text => {
                    return {
                        page: page.no,
                        text: text.text,
                        x_axis: text.x / 3.7795275591,
                        y_axis: text.y / 3.7795275591,
                        fontSize: text.fontSize / 3.7795275591,
                        fontFamily: text.fontFamily,
                        fontColor: text.hasOwnProperty('fontColor') ? text.fontColor : '#000000',
                    }
                });

                ret.stamps.push(..._stamp);
                ret.texts.push(..._text);
            })

            return ret;
        });
        //PAC_5-1527 テンプレート編集　スタンプテキスト保存
        if(state.template_flg && state.templateEditFlg == 1) {
            homeService.saveTemplateEditStamp(data).then(
                response => {
                    return Promise.resolve(true);
                },
                error => {
                    dispatch("alertError", error.message, { root: true });
                    return Promise.resolve(error);
                });

            homeService.saveTemplateEditText(data).then(
                response => {
                    return Promise.resolve(true);
                },
                error => {
                    dispatch("alertError", error.message, { root: true });
                    return Promise.resolve(error);
                });
        }

            let result = homeService.saveFile(data).then(
            response => {
                if(!response) return;
                return Promise.resolve(true);
            },
            error => {
                dispatch("alertError", error.message, { root: true });
                return Promise.resolve(error);
            }
            );
        return result;
    },

    //PAC_1527 テンプレート編集ルート完了対応
    editTemplate({ dispatch, commit }, data) {
        //const special_sit_flg = data.special_sit_flg;
        const circular_id = data.circular_id;
      return templateService.editTemplate(data).then(
        response => {
          const data = response;
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
          let uploadData = {
            file: dataBlob,
            circular_id: state.circular ? state.circular.id: null,
            name:data.file_name,
          };
          let result = homeService.uploadFile(uploadData);
          //commit('setTemplateId',data.templateId);
          //commit('setStorageFileName',data.storage_file_name);
          return result;
        },
        error => {
          dispatch("alertError", error, {root: true});
          return Promise.reject(false);
        }
      )
      .then(
        response => {
          dispatch("alertSuccess", response.message, { root: true });
            commit('setServerNameAndServerPath',response.data);
            return state.files;
        },
        error => {
          dispatch("alertError", error, { root: true });
          return Promise.resolve(false);
        }
      )
      .then(
        response => {
          return Promise.resolve(response.data);
        },
        error => {
          dispatch("alertError", error, { root: true });
          return Promise.resolve(false);
        }
      );
    },

    templateSaveFileAndSignature({dispatch, state},editData) {
        //PAC_5-1527 stamp_info 被りバグ修正
        templateService.templateStampInfoDelete({circular_id:state.circular.id}).then(
          response => {
            dispatch("alertSuccess", response.message, { root: true });
            return Promise.resolve(response.data);
          },
          error => {
            dispatch("alertError", error, { root: true });
            return Promise.resolve(false);
          }
        )
          if(state.files.length <= 0) return;
          let data = {
              signature: state.checkSentCircular == true ? 1 : 0,
              circular_id: state.circular ? state.circular.id : null,
              active_id: state.fileSelected ? state.fileSelected.circular_document_id: null,
              downloadable: false,
              files: []
          };

          if(state.files.length <= 0) return;
                data.files = state.files.map(file=> {
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

    downloadFile({dispatch, state}) {
        if(!state.fileSelected) return;

        let data = {
            signature: 1,
            no_timestamp: 1,
            circular_id: state.circular ? state.circular.id : null,
            active_id: state.fileSelected ? state.fileSelected.circular_document_id: null,
            downloadable: true,
            files: [],
            check_add_stamp_history: state.addStampHistory,
            check_add_text_history: state.addTextHistory,
        };

        const file = {
            file_name: state.fileSelected.name,
            server_file_name: state.fileSelected.server_file_name,
            circular_document_id: state.fileSelected.circular_document_id,
            confidential_flg: state.fileSelected.confidential_flg,
            stamps: [],
            texts: [],
            deleteInfo: [],
            comments: state.fileSelected.tempComments,
            parent_send_order: state.parent_send_order,
            update_at: state.fileSelected.update_at,
        };
        state.fileSelected.pages.forEach(page=> {
            const _stamp = page.stamps.filter(item => !item.selected).map(stamp => {
                const stamp_info = state.stamps.find(item => item.id === stamp.id);
                const height = stamp.height / 3.7795275591;
                return {
                    repeated: stamp.repeated ? stamp.repeated : false,
                    page: page.no,
                    stamp_data: stamp_info ? stamp_info.url : '',
                    x_axis: stamp.x  / 3.7795275591,
                    y_axis: (stamp.y / 3.7795275591) + height,
                    width: stamp.width / 3.7795275591,
                    height: height,
                    stamp_url: '',
                    id: stamp_info?stamp_info.db_id : null,
                    stamp_flg: stamp_info?stamp_info.stamp_flg : null,
                    time_stamp_permission: stamp_info?stamp_info.time_stamp_permission : 0,
                    serial: stamp_info?stamp_info.serial : null,
                    rotateAngle: stamp.rotateAngle,
                }
            });
            const _text = page.texts.map(text => {
                return {
                    page: page.no,
                    text: text.text,
                    x_axis: text.x / 3.7795275591,
                    y_axis: text.y / 3.7795275591,
                    fontSize: text.fontSize / 3.7795275591,
                    fontFamily: text.fontFamily,
                    fontColor: text.hasOwnProperty('fontColor') ? text.fontColor : '#000000',
                }
            });

            file.stamps.push(..._stamp);
            file.texts.push(..._text);
        });

        data.files.push(file);

        if(state.circular.circular_status === CIRCULAR.CIRCULAR_COMPLETED_STATUS) {
            homeService.updateCircularStatus(state.circular.id, CIRCULAR.CIRCULAR_COMPLETED_SAVED_STATUS).then(
              response => {
                  commit('updateCircularStatus', CIRCULAR.CIRCULAR_COMPLETED_SAVED_STATUS);
              },
              error => {

              }
            )
        }

        return homeService.saveFile(data).then(
            response => {
                if(!response || !response.data) return Promise.reject(false);
                const data = response.data.pop();
                if(!data || data.circular_document_id !== state.fileSelected.circular_document_id) return;
                let byteString = Base64.atob(data.pdf_data);
                state.circular.update_at = data.update_at;
                const ab = new ArrayBuffer(byteString.length);
                const ia = new Uint8Array(ab);
                for (let i = 0; i < byteString.length; i++) {
                    ia[i] = byteString.charCodeAt(i);
                }
                const dataBlob = new Blob([ab]);
                fileDownload(dataBlob, state.fileSelected.name);
                state.fileSelected.actions.length = 0; //PAC_5-1036 ダウンロード時元に戻すボタン無効化
                state.disabledProceed = true; //PAC_5-1036 ダウンロード時やり直すボタン無効化
                return Promise.resolve(true);
            },
            error => {
                dispatch("alertError", error.message, { root: true });
                return Promise.reject(false);
            }
        );
    },
    uploadToCloud({dispatch, state}, options) {
      if(!state.fileSelected) return;

      /*if(state.circular.circular_status === CIRCULAR.CIRCULAR_COMPLETED_STATUS) {
        homeService.updateCircularStatus(state.circular.id, CIRCULAR.CIRCULAR_COMPLETED_SAVED_STATUS, options.finishedDate).then(
          response => {
            commit('updateCircularStatus', CIRCULAR.CIRCULAR_COMPLETED_SAVED_STATUS);
          },
          error => {

          }
        )
      }*/

      if(!options.saveFile) {
        return homeService.downloadFile({active_id:state.fileSelected.circular_document_id,
                                                        circular_id: state.circular.id,
                                                        check_add_stamp_history: state.addStampHistory,
                                                        check_add_text_history: state.addTextHistory,
                                                        usingTas: state.usingTas, finishedDate: options.finishedDate}).then(
          response => {
            if(!response || !response.data) return;
            const data = response.data.pop();
            if(!data || data.circular_document_id !== state.fileSelected.circular_document_id) return;
            const byteString = Base64.atob(data.pdf_data);

            const ab = new ArrayBuffer(byteString.length);
            const ia = new Uint8Array(ab);
            for (let i = 0; i < byteString.length; i++) {
              ia[i] = byteString.charCodeAt(i);
            }
            const dataBlob = new Blob([ab]);
            //PAC_5-1216 Box上書き実装
            return cloudService.upload(options.drive,options.folder_id, options.filename, dataBlob,options.file_id).then(
            //PAC_5-1216
                resp => {
                dispatch("alertSuccess", resp.message, { root: true });
                state.fileSelected.actions.length = 0; //PAC_5-1036 ダウンロード時元に戻すボタン無効化
                state.disabledProceed = true; //PAC_5-1036 ダウンロード時やり直すボタン無効化
                return Promise.resolve(true);
              },
              error=> {
                dispatch("alertError", error, { root: true });
                return Promise.resolve(false);
              }
            )
          },
          error => {
            dispatch("alertError", error, { root: true });
            return Promise.resolve(false);
          }
        );
      }

      let data = {
        signature: 1,
        circular_id: state.circular ? state.circular.id : null,
        active_id: state.fileSelected ? state.fileSelected.circular_document_id: null,
        downloadable: true,
        files: [],
        check_add_stamp_history: state.addStampHistory,
        check_add_text_history: state.addTextHistory,
        usingTas: state.usingTas
      };

      const file = {
        file_name: state.fileSelected.name,
        server_file_name: state.fileSelected.server_file_name,
        circular_document_id: state.fileSelected.circular_document_id,
        confidential_flg: state.fileSelected.confidential_flg,
        stamps: [],
        texts: [],
        deleteInfo: [],
        comments: state.fileSelected.tempComments,
        parent_send_order: state.parent_send_order,
      };
      state.fileSelected.pages.forEach(page=> {
        const _stamp = page.stamps.filter(item => !item.selected).map(stamp => {
          const stamp_info = options.stampDisplays.find(item => item.id === stamp.id);
          const height = stamp.height / 3.7795275591;
          return {
            repeated: stamp.repeated ? stamp.repeated : false,
            page: page.no,
            stamp_data: stamp_info ? stamp_info.url : '',
            x_axis: stamp.x  / 3.7795275591,
            y_axis: (stamp.y / 3.7795275591) + height,
            width: stamp.width / 3.7795275591,
            height: height,
            stamp_url: '',
          }
        });
        const _text = page.texts.map(text => {
          return {
            page: page.no,
            text: text.text,
            x_axis: text.x / 3.7795275591,
            y_axis: text.y / 3.7795275591,
            fontSize: text.fontSize / 3.7795275591,
            fontFamily: text.fontFamily,
            fontColor: text.hasOwnProperty('fontColor') ? text.fontColor : '#000000',
          }
        });

        file.stamps.push(..._stamp);
        file.texts.push(..._text);
      });

      data.files.push(file);
      data.nowait = true;
      return homeService.saveFile(data).then(
        response => {
          if(!response || !response.data) return;
          const data = response.data.pop();
          if(!data || data.circular_document_id !== state.fileSelected.circular_document_id) return;
          const byteString = Base64.atob(data.pdf_data);

          const ab = new ArrayBuffer(byteString.length);
          const ia = new Uint8Array(ab);
          for (let i = 0; i < byteString.length; i++) {
            ia[i] = byteString.charCodeAt(i);
          }
          const dataBlob = new Blob([ab]);
          //PAC_5-1216
          return cloudService.upload(options.drive,options.folder_id, options.filename, dataBlob,options.file_id).then(
          //PAC_5-1216 END
            resp => {
              state.fileSelected.actions.length = 0; //PAC_5-1036 ダウンロード時元に戻すボタン無効化
              state.disabledProceed = true; //PAC_5-1036 ダウンロード時やり直すボタン無効化
              return Promise.resolve(true);
            },
            error=> {
              dispatch("alertError", error, { root: true });
              return Promise.resolve(false);
            }
          )
        },
        error => {
          dispatch("alertError", error.message, { root: true });

          return Promise.resolve(false);
        }
      );
    },
    checkShowConfirmAddTimeStamp({ dispatch, commit }, finishedDate) {
        return homeService.checkShowConfirmAddTimeStamp(finishedDate).then(
          response => {
            return Promise.resolve(response.data);
          },
          error => {
              dispatch("alertError", error, { root: true });
              return Promise.reject(false);
          }
        );
    },
    async downloadSendFile({ dispatch,commit,state }, finishedDate) {
        let result = false;
        if(!state.fileSelected) return result;

        if(state.circular.circular_status === CIRCULAR.CIRCULAR_COMPLETED_STATUS) {
            await homeService.updateCircularStatus(state.circular.id, CIRCULAR.CIRCULAR_COMPLETED_SAVED_STATUS, finishedDate).then(
              response => {
                commit('updateCircularStatus', CIRCULAR.CIRCULAR_COMPLETED_SAVED_STATUS);
              },
              error => {

              }
            )
        }

        await homeService.downloadFile({active_id:state.fileSelected.circular_document_id, circular_id: state.circular.id, check_add_stamp_history: state.addStampHistory, check_add_text_history: state.addTextHistory, usingTas: state.usingTas, finishedDate: finishedDate}).then(
          response => {
              if(!response || !response.data) return result;
              const data = response.data.pop();
              if(!data || data.circular_document_id !== state.fileSelected.circular_document_id) return result;

              const byteString = Base64.atob(data.pdf_data);

              const ab = new ArrayBuffer(byteString.length);
              const ia = new Uint8Array(ab);
              for (let i = 0; i < byteString.length; i++) {
                  ia[i] = byteString.charCodeAt(i);
              }
              const dataBlob = new Blob([ab]);
              fileDownload(dataBlob, state.fileSelected.name);
              result = true;
          },
          error => {
              dispatch("alertError", error, { root: true });
          }
        );
        return result;

    },
    saveStampsOrder({ dispatch,commit, state },options) {
        if (!state.isUpdateStampOrder) return;
        return homeService.saveStampsOrder(options.stampDisplays).then(
          response => {
              dispatch("alertSuccess", response.message, { root: true });
              commit('saveStampsOrderSuccess');
              return Promise.resolve(true);
          },
          error => {
              dispatch("alertError", error, { root: true });
              return Promise.reject(false);
          }
        );
    },
    setFirstPageImage({ dispatch,commit }, image) {
        commit('homeSetFirstPageImage', image);
    },
    loadCircular({ dispatch,commit }, options) {
        return homeService.loadCircular(options.id).then(
          response => {
              commit('disableAccessCodeFlg');
              delete response.data.circular.first_page_data; // 暫定対処として 詳細:PAC_5-1278

              const data = response.data;
              const user = JSON.parse(getLS('user'));

              data.files.forEach(file => {
                file.overlay_hidden_flg=false;
                file.enableDelete = (!data.circular || (data.circular.circular_status === CIRCULAR.SAVING_STATUS) || (data.circular.circular_status === CIRCULAR.RETRACTION_STATUS) || (user && data.circular.circular_status === CIRCULAR.SEND_BACK_STATUS && file.create_user_id === user.id)|| (user && data.circular.circular_status === CIRCULAR.CIRCULATING_STATUS && file.create_user_id === user.id && file.origin_env_flg === config.APP_SERVER_ENV && file.origin_edition_flg === config.APP_EDITION_FLV && file.origin_server_flg === config.APP_SERVER_FLG));
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
    loadCircularForCompleted({ dispatch,commit }, options) {
        return homeService.loadCircularForCompleted(options.id, options.finishedDate).then(
            response => {
                commit('disableAccessCodeFlg');
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

    loadCircularByHash({ dispatch,commit }, options) {
        const { hashUserPromise, accessCodeHashFlgPromise} = options;
        return homeService.loadCircularByHash().then(
           async response => {
              const circular = response.data.circular;
              delete response.data.circular.first_page_data; // 暫定対処として 詳細:PAC_5-1278

              const hashUser = await hashUserPromise;
              const accessCodeHashFlg = await accessCodeHashFlgPromise;

              const data = response.data;
              const user = hashUser;

              data.files.forEach(file => {
                file.overlay_hidden_flg=false
                if (user.is_external) {
                  file.enableDelete = false;
                } else {
                  file.enableDelete = (!data.circular || (data.circular.circular_status === CIRCULAR.SAVING_STATUS) || (user && data.circular.circular_status === CIRCULAR.SEND_BACK_STATUS && file.create_user_id === user.id)|| (user && data.circular.circular_status === CIRCULAR.CIRCULATING_STATUS && file.create_user_id === user.id && file.origin_env_flg === config.APP_SERVER_ENV && file.origin_edition_flg === config.APP_EDITION_FLV && file.origin_server_flg === config.APP_SERVER_FLG));
                }
              });

               // PAC_5-445 社内社外回覧者アクセスコード認証追加
               // PAC_5-445 完了メールから文書を見る場合もアクセスコード求められる
              if(!accessCodeHashFlg && ((!circular.current_user_identity && circular.access_code_flg) || (circular.current_user_identity && circular.outside_access_code_flg))) {
                  commit('setAccessCodeFlg', data);
              }else {
                  commit('disableAccessCodeFlg');
                  commit('loadCircularSuccess', data);
                  commit('pushFiles', data.files);
                setTimeout(()=> {
                  commit('initFileSelectedByHash', hashUser);
                },500);
              }
              return Promise.resolve(true);
          },
          error => {
              dispatch("alertError", error, { root: true });
              return Promise.resolve(false);
          }
        );
    },
    afterCheckAccessCode({ dispatch,commit, state }) {
        commit('disableAccessCodeFlg');
        const data = state.tmpData;
        commit('clearTmpData');
        commit('loadCircularSuccess', data);
        commit('pushFiles', data.files);
        setTimeout(()=> {
          commit('initFileSelected');
        },500);
    },
    afterCheckAccessCodeByHash({ dispatch,commit, state }, hashUser) {
      commit('disableAccessCodeFlg');
      const data = state.tmpData;
      commit('clearTmpData');
      commit('loadCircularSuccess', data);
      commit('pushFiles', data.files);
      setTimeout(()=> {
        commit('initFileSelectedByHash', hashUser);
      },500);
    },
    getStampInfos({ dispatch,commit }, circular_document_id) {
        return homeService.getStampInfos(circular_document_id).then(
          response => {
              return Promise.resolve(response.data);
          },
          error => {
              dispatch("alertError", error, { root: true });
              return Promise.resolve([]);
          }
        );
    },
    getStampInfosForCompleted({ dispatch,commit }, {circular_document_id, finishedDate}) {
        return homeService.getStampInfosForCompleted(circular_document_id, finishedDate).then(
            response => {
                return Promise.resolve(response.data);
            },
            error => {
                dispatch("alertError", error, { root: true });
                return Promise.resolve([]);
            }
        );
    },


    updateCircularUsers({ dispatch,commit,state }, users) {
        if(!users || users.length <= 0) return;
        let data = users.slice();
        /*PAC_5-1698*/
        let tempIds=[]
        data.forEach((_,i)=>{
            if (tempIds.includes(_.id)){
                data.splice(i,1)
            }else{
                tempIds.push(_.id)
            }
        })
        let tmpPlan = []
        let planuser=[]
        let datatmp=data.map(v=>{
            if (v.plan_id>0){
                if(tmpPlan[v.plan_id]){
                    planuser.push(v)
                    return null
                }else{
                    tmpPlan[v.plan_id] =1
                    return v
                }

            }else{
                return v
            }
        }).filter(v=>{
            return v!=null
        })
        datatmp.push(...planuser)
        data=datatmp
    //    const creator_company_id = data[0] ? data[0].mst_company_id: null;
      //  let index = 0;
        let old = data[0];
        let plan=[];
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
            /*PAC_5-1698*/
            if (user.plan_id>0){
                if (plan[user.plan_id]){
                    child_send_order=plan[user.plan_id].child_send_order
                    parent_send_order=plan[user.plan_id].parent_send_order
                }else {
                    plan[user.plan_id]={child_send_order:child_send_order,parent_send_order:parent_send_order}
                }
            }
            user.parent_send_order=parent_send_order
            user.child_send_order=child_send_order
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
    updateFormatCircularUsers({ dispatch,commit,state }, users) {
      if(!users || users.length <= 0) return Promise.resolve(false);
      let formatUsers = users.slice();
      let data = [];
      const flatUser = () => {
        if(!formatUsers.length) return;
        const formatUser = formatUsers.shift();
        data.push(formatUser.user);
        if(formatUser.children && formatUser.children.length) {
          data.push(...formatUser.children);
        }
        flatUser();
      };
      flatUser();
      const creator_company_id = data[0] ? data[0].mst_company_id: null;
      let index = 0;
      let old = data[0];

      const iterable = () => {
        index++;
        old = data[index -1];
        let user = data[index];

        let parent_send_order = parseInt(old.parent_send_order);
        let child_send_order = parseInt(old.child_send_order) + 1;

        const old_company_id = old ? old.mst_company_id : null;
        const user_company_id = user ? user.mst_company_id : null;

        if(old_company_id !== null && creator_company_id !== old_company_id && old.child_send_order === 1 && user.child_send_order === 1 && old_company_id === user_company_id) {
          dispatch("alertError", '社内／社外ともに設定可能。ただし、社外は窓口の1人のみ設定可能。', { root: true });
          return false;
        }

        if(old_company_id !== creator_company_id && creator_company_id === user_company_id) {
          dispatch("alertError", '社外宛先の後に社内宛先は設定不可。', { root: true });
          return false;
        }

        if(old_company_id !== user_company_id) {
          parent_send_order += 1;
          child_send_order = 1;
        }

        commit('homeUpdateCircularUserOrder', {id: user.id,parent_send_order:parent_send_order, child_send_order: child_send_order});

        if(index < data.length - 1) {
          return iterable();
        }
        return true;


      };
      const ret = iterable();
      if(ret) {
        homeService.updateCircularUsers({circular_users: data, nowait: true}).then(
          response => {
            return Promise.resolve(true);
          },
          error => {
            commit('rollBackCircularUsers');
            dispatch("alertError", error, { root: true });
            return Promise.resolve(false);
          }
        );
        commit('homeUpdateCircularUsers',data);
      }else {
        return Promise.resolve(false);
      }
    },
    approvalRequestSendBack({ dispatch, commit, state }, data) {
      return homeService.approvalRequestSendBack().then(
        response => {
          commit('approvalRequestSendBackSuccess', data.approvalUser);
          if(response.data && response.data.isLastApproval) {
            let toCircularUser = state.circular.users.find(item => item.circular_status === CIRCULAR_USER.SUBMIT_REQUEST_SEND_BACK);
            let send_to_id = null;
            if(toCircularUser) send_to_id = toCircularUser.id;
            if(response.data.lastId && send_to_id) {
              const sendBackData = {
                isRequestSendBack: true,
                send_from_id: response.data.lastId,
                send_to_id: send_to_id,
                text: ''
              };
              dispatch('application/sendBack', sendBackData, { root: true });
            }else{
                return Promise.resolve(response.data);
            }
          }else{
              return Promise.resolve(response.data);
          }
        },
        error => {
          //commit('uploadFileFailure');
          dispatch("alertError", error, { root: true });
          return Promise.resolve(false);
        }
      );
    },
    discardCircular({ dispatch,commit }) {
      return homeService.discardCircular().then(
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
    checkDeviceType({ dispatch, commit, state }){
        let ua = navigator.userAgent,
            isWindowsPhone = /(?:Windows Phone)/.test(ua),
            isSymbian = /(?:SymbianOS)/.test(ua) || isWindowsPhone,
            isAndroid = /(?:Android)/.test(ua),
            isFireFox = /(?:Firefox)/.test(ua),
            isChrome = /(?:Chrome|CriOS)/.test(ua),
            isSafari = /(?:Safari)/.test(ua),
            isTablet = /(?:iPad|PlayBook)/.test(ua) || (isAndroid && !/(?:Mobile)/.test(ua)) || (isFireFox && /(?:Tablet)/.test(ua)) || (isSafari && /(?:Macintosh)/.test(ua)) || (isSafari && /(?:iPhone)/.test(ua)),
            isPhone = /(?:iPhone)/.test(ua) && !isTablet,
            isPc = !isPhone && !isAndroid && !isSymbian;
        let os = {
            isTablet: isTablet,
            isPhone: isPhone,
            isAndroid: isAndroid,
            isPc: isPc
        }

        commit("getDeviceType",os);
    },
    getCompanyStamps({ dispatch, commit }, options) {
        return homeService.getUserCompanyStamps(options).then(
            response => {
                if(!response) return;
                commit('getStampsSuccess', response.data);
                return Promise.resolve(response.data);
            },
            error => {
                dispatch("alertError", error, { root: true });
                return Promise.reject(false);
    }
        );
    },
    //一覧画面のダウンロード予約
    reservePreviewFile({dispatch, commit}, info){
        return homeService.reservePreviewFile(info).then(
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
    //新規作成ページのダウンロード予約
    downloadCreatedPreviewFile({dispatch, state}, info){
        if(!state.fileSelected) return;

        let data = {
            signature: 1,
            no_timestamp: 1,
            circular_id: state.circular ? state.circular.id : null,
            active_id: state.fileSelected ? state.fileSelected.circular_document_id: null,
            downloadable: false,
            files: [],
            check_add_stamp_history: state.addStampHistory,
            check_add_text_history: state.addTextHistory,
            isDownloadReserve: true,
            reserveFileName: info.reserveFileName,
        };

        const file = {
            file_name: state.fileSelected.name,
            server_file_name: state.fileSelected.server_file_name,
            circular_document_id: state.fileSelected.circular_document_id,
            confidential_flg: state.fileSelected.confidential_flg,
            stamps: [],
            texts: [],
            deleteInfo: [],
            comments: state.fileSelected.tempComments,
            parent_send_order: state.parent_send_order,
            update_at: state.fileSelected.update_at,
        };

        state.fileSelected.pages.forEach(page=> {
            const _stamp = page.stamps.filter(item => !item.selected).map(stamp => {
                const stamp_info = state.stamps.find(item => item.id === stamp.id);
                const height = stamp.height / 3.7795275591;
                return {
                    repeated: stamp.repeated ? stamp.repeated : false,
                    page: page.no,
                    stamp_data: stamp_info ? stamp_info.url : '',
                    x_axis: stamp.x  / 3.7795275591,
                    y_axis: (stamp.y / 3.7795275591) + height,
                    width: stamp.width / 3.7795275591,
                    height: height,
                    stamp_url: '',
                    id: stamp_info?stamp_info.db_id : null,
                    stamp_flg: stamp_info?stamp_info.stamp_flg : null,
                    time_stamp_permission: stamp_info?stamp_info.time_stamp_permission : 0,
                    serial: stamp_info?stamp_info.serial : null,
                    rotateAngle: stamp.rotateAngle,
                }
            });
            const _text = page.texts.map(text => {
                return {
                    page: page.no,
                    text: text.text,
                    x_axis: text.x / 3.7795275591,
                    y_axis: text.y / 3.7795275591,
                    fontSize: text.fontSize / 3.7795275591,
                    fontFamily: text.fontFamily,
                    fontColor: text.hasOwnProperty('fontColor') ? text.fontColor : '#000000',
                }
            });

            file.stamps.push(..._stamp);
            file.texts.push(..._text);
        });

        data.files.push(file);

        if(state.circular.circular_status === CIRCULAR.CIRCULAR_COMPLETED_STATUS) {
            homeService.updateCircularStatus(state.circular.id, CIRCULAR.CIRCULAR_COMPLETED_SAVED_STATUS).then(
                response => {
                    commit('updateCircularStatus', CIRCULAR.CIRCULAR_COMPLETED_SAVED_STATUS);
                },
                error => {

                }
            )
        }

        return homeService.saveFile(data).then(
            response => {
                if(!response) return Promise.reject(false);
                state.fileSelected.actions.length = 0; //PAC_5-1036 ダウンロード時元に戻すボタン無効化
                state.disabledProceed = true; //PAC_5-1036 ダウンロード時やり直すボタン無効化
                dispatch("alertSuccess", response.message, { root: true });
                return Promise.resolve(true);
            },
            error => {
                dispatch("alertError", error.message, { root: true });
                return Promise.reject(false);
            }
        );
    },

    reserveAttachment({dispatch,commit},info){
        return homeService.reserveAttachment(info).then(
            response => {
                if(!response) return Promise.reject(false);
                dispatch("alertSuccess", response.message, { root: true });
                return Promise.resolve(true);
            },
            error => {
                dispatch("alertError", error, { root: true });
                return Promise.reject(false);
            }
        );
    },
};

const mutations = {
    getDeviceType(state,value){
        state.deviceType = value;
    },
  checkAddStampHistory(state, value) {
      state.addStampHistory = value;
    },
    checkAddTextHistory(state, value) {
        state.addTextHistory = value;
    },
    beginUploadFile(state) {
        state.fileSelected = null;
    },
    checkCircularUserNextSend(state, value) {
      state.checkSentCircular = value;
    },
    setTemplateNextUserCompletedFlg(state, value) {
        state.templateNextUserCompletedFlg = value;
    },
    setTemplateFlg(state, value) {
        state.template_flg = value;
    },
    setServerNameAndServerPath(state, data) {
        state.files[0].server_file_name = data.server_file_name;
        state.files[0].server_file_path = data.server_file_path;
    },
    pushFiles(state, files) {
        for (const file of files) {
            file.pagesInfo = file.pages; // 衝突回避
            if (!file.pagesInfo || file.pagesInfo == 'undefined') {
                //特設サイト文書データ受信中の場合
                break;
            }
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
        state.files = state.files.concat(files);
    },
    createCircular(state, data) {
        if(!state.circular) {
            state.circular = data.circular;
            state.circular.users = [];
            state.circular.palns = [];
        }
    },
    homeClearState(state,data) {
        state.stamps = [];
        state.stampDisplays = [];
        state.files = [];
        state.fileSelected = null;
        // state.stampSelected = null;
        state.textSelected = false;
        state.StickySelected = false;
        state.stickyNextNo = 0;
        state.noteHomeSelected = false;
        state.noteOneSelected = '';
        state.isUpdateStampOrder = false;
        state.circular = null;
        state.tempComments = [];
        //state.currentPageNo = 1;
    },
    homeClearFileState(state,data) {
        state.files = [];
        state.fileSelected = null;
        state.circular = null;
    },
    homeSelectFile(state, file) {
        state.fileSelected = file;
    },
    homeAddEmptyFile(state) {
        state.fileSelected = null;
    },
    homeCloseFile(state, file) {
        const fileIndex = state.files.findIndex(item => item.server_file_name === file.server_file_name);
        if(fileIndex > -1) {
            if (state.fileSelected && file.server_file_name === state.fileSelected.server_file_name) {
                if(fileIndex < state.files.length - 1) {
                    state.fileSelected = state.files[fileIndex + 1];
                }else {
                    state.fileSelected = fileIndex  > 0 ? state.files[fileIndex - 1]: null;
                }
            }
            state.files.splice(fileIndex, 1);
        }
        if(state.files.length <= 0) {
            state.circular = null;
            state.fileSelected = null;
        }
    },
    homeUpdateCurrentFileZoom(state,zoom) {
        if(state.fileSelected) {
            state.fileSelected.zoom = zoom;
        }
    },
    getStampsSuccess(state, stamps) {
        let id = state.stamps.length + 1;
        state.stampDisplays = [];
        stamps.forEach((item, index) => {
           const stamp = {
               id: id + index,
               db_id: item.id,
               sid: item.sid,
               //mst_user_id: item.mst_user_id,
               url: item.stamp_image,
               stamp_division: item.stamp_division,
               width: item.width * 0.001 * 3.7795275591,
               height: item.height * 0.001 * 3.7795275591,
               date_width: item.date_width * 3.7795275591,
               date_height: item.date_height * 3.7795275591,
               date_x: item.date_x * 3.7795275591,
               date_y: item.date_y * 3.7795275591,
               display_no: item.display_no,
               stamp_flg: item.stamp_flg,//0：通常印 1：共通印 2：日付印
               time_stamp_permission: item.time_stamp_permission,
               serial: item.serial,
               stamp_name: item.stamp_name, //印面の名称
               //create_at: item.create_at,
               //create_user: item.create_user,
           };
           state.stamps.push(stamp);
           state.stampDisplays.push(stamp);
        });

        // if(state.stampDisplays.length > 0) {
        //     state.stampSelected = state.stampDisplays[0];
        // }

    },
    formIssuanceSelectStamp(state, stampId){
        let stamp = state.stamps.find(item => item.id === stampId);
        if(!stamp) return;
        state.stampSelected = stamp;
        state.textSelected = false;
        state.StickySelected = false;
        state.noteHomeSelected = false;
        state.noteOneSelected = '';
    },
    homeSelectStamp(state, stamp){
        // let stamp = state.stamps.find(item => item.id === stampId);
        if(!stamp) return;
        state.stampSelected = stamp;
        state.textSelected = false;
        state.StickySelected = false;
    },
    homeSelectText(state){
        state.stampSelected = null;
        state.StickySelected = false;
        if(state.textSelected == true){
            state.textSelected = false;
        }else {
            state.textSelected = true;
            state.noteHomeSelected = false;
            state.noteOneSelected = '';
        }

    },
    homeUnSelectText(state){
        state.textSelected = false;
    },
    homeSelectSticky(state){
        state.stampSelected = null;
        state.textSelected = false;
        if(state.StickySelected == true){
            state.StickySelected = false;
        }else {
            state.StickySelected = true;
            state.noteHomeSelected = false;
            state.noteOneSelected = '';
        }
    },
    homeUnSelectSticky(state){
        state.StickySelected = false;
    },
    homeSetStickyPosition(state,data){
        state.stickyPosition = data;
    },
    homeUpdateStickyPosition(state,data){
        let stickys = state.fileSelected.sticky_notes;
        const sticky = stickys[data.edit_id];
        sticky.left = data.left;
        sticky.top = data.top;
    },
    homeAddSticky(state,data){
        data.removed_flg = 0;
        state.fileSelected.sticky_notes.push(data);
    },
    homeDeleteSticky (state, note_index) {
      let stickys = state.fileSelected.sticky_notes;
      if (stickys[note_index].id > 0) {
        let temp = stickys[note_index]
        temp.deleted_flg = 1
        stickys.splice(note_index, 1, temp)
      } else {
        stickys.splice(note_index, 1)
      }
    },
    homeShowHideSticky (state, data) {
      let stickys = state.fileSelected.sticky_notes;
      let temp = stickys[data]
      temp.removed_flg = !temp.removed_flg
      stickys.splice(data, 1, temp)
    },
    homeEditSticky (state, data) {
      let stickys = state.fileSelected.sticky_notes;
      let temp = stickys[data.note_index]
      temp.note_format = data.note_format
      temp.note_text = data.note_text
      temp.removed_flg = 0
      stickys.splice(data.note_index, 1, temp)
    },
    HomeSelectNote(state){
        state.stampSelected = null;
        state.textSelected = false;
        state.StickySelected = false;
        state.noteHomeSelected = true;
    },
    homeSelectNoteOne(state ,noteId){
        state.noteHomeSelected = true;
        state.noteOneSelected = noteId;
    },

    homeUnSelectNote(state){
        state.noteHomeSelected = false;
        state.noteOneSelected = '';
    },
    homeUpdateStampDisplays(state, data) {
        if(!data) return;
        state.isUpdateStampOrder = true;
    },
    homeAddFileStamp(state,data) {
        if(state.fileSelected) {
            const pageIndex = state.fileSelected.pages.findIndex(item => item.no === data.pageno);
            const dataStamp = {...data.stamp,rotateAngle: Math.abs(data.stamp.rotation)}
            if(pageIndex > -1) {
                state.fileSelected.pages[pageIndex].stamps.push(dataStamp);
                state.fileSelected.actions.push({name: ACTIONS.ADD_STAMP, pageno: data.pageno, oldData: null});
                state.hasAction = !state.hasAction;
            }
        }
    },
    homeUpdateFileStamp(state, data) {
        if(state.fileSelected) {
            const pageIndex = state.fileSelected.pages.findIndex(item => item.no === data.pageno);
            if(pageIndex > -1) {
                state.fileSelected.pages[pageIndex].stamps.map(item=>{
                    if(item.index === data.stamp.index) {
                        if(data.stamp.x) {
                            item.x = data.stamp.x;
                        }
                        if(data.stamp.y) {
                            item.y = data.stamp.y;
                        }
                        if(data.stamp.opacity) {
                            item.opacity = data.stamp.opacity;
                        }
                        item.selected = data.stamp.selected;
                    }
                });
            }
        }
    },
    homeAddFileText(state,data) {
        if(state.fileSelected) {
            const pageIndex = state.fileSelected.pages.findIndex(item => item.no === data.pageno);
            if(pageIndex > -1) {
                state.fileSelected.pages[pageIndex].texts.push(data.text);
                if(data.text.text) {
                  state.fileSelected.actions.push({name: ACTIONS.ADD_TEXT, pageno: data.pageno, oldData: null});
                  state.hasAction = !state.hasAction;
                }
            }
        }
    },
    homeDeleteFileText(state, data) {
        if(!data) return;
        if(state.fileSelected) {
            const pageIndex = state.fileSelected.pages.findIndex(item => item.no === data.pageno);
            if(pageIndex > -1) {
              if(!data.text) return;
                const textIndex = state.fileSelected.pages[pageIndex].texts.findIndex(item => {
                    if(item.index === data.text.index) {
                        const oldData = $.extend({}, item);
                        if(oldData.text) {
                          state.fileSelected.actions.push({
                            name: ACTIONS.DELETE_TEXT,
                            pageno: data.pageno,
                            oldData: oldData
                          });
                          state.hasAction = !state.hasAction;
                        }
                    }
                    return item.index === data.text.index;
                });
                state.fileSelected.pages[pageIndex].texts.splice(state.fileSelected.pages[pageIndex].texts.findIndex(item => item.index === data.text.index), 1);
            }
        }
    },
    homeUpdateFileText(state, data) {
        if(state.fileSelected) {
            const pageIndex = state.fileSelected.pages.findIndex(item => item.no === data.pageno);
            if(pageIndex > -1) {
                state.fileSelected.pages[pageIndex].texts.map(item=>{
                    if(item.index === data.text.index) {

                      const oldData = $.extend({}, item);

                      item.x = data.text.x;
                      item.y = data.text.y;
                      item.text = data.text.text;
                      item.width = data.text.width;
                      item.fontSize = data.text.fontSize;
                      item.fontFamily = data.text.fontFamily;
                      item.scaleX = data.text.scaleX;
                      item.scaleY = data.text.scaleY;
                      item.rotation = data.text.rotation;
                      item.fontColor = data.text.fontColor;

                      if(oldData.x !== item.x || item.y !== oldData.y || item.text !== oldData.text || oldData.fontSize !== item.fontSize || oldData.fontFamily !== item.fontFamily) {
                        state.fileSelected.actions.push({
                          name: oldData.text ? ACTIONS.UPDATE_TEXT: ACTIONS.ADD_TEXT,
                          pageno: data.pageno,
                          oldData: oldData
                        });

                        state.hasAction = !state.hasAction;
                      }
                    }
                });
            }
        }
    },
    // 一時社内社外宛先変更
    homeUpdateFileComment(state, data) {
        if (state.fileSelected) {
            const commentIndex = state.fileSelected.tempComments.findIndex(item => item.private_flg == data.private_flg);
            if (commentIndex > -1){
                state.fileSelected.tempComments.splice(commentIndex, 1, {private_flg: data.private_flg, text: data.text, parent_send_order: data.parent_send_order});
            }else{
                state.fileSelected.tempComments.push({private_flg: data.private_flg, text: data.text, parent_send_order: data.parent_send_order});
            }
        }
    },
    // 一時社内社外宛先削除
    homeDeleteFileComment(state, data) {
        if(!data) return;
        if(state.fileSelected) {
            const commentIndex = state.fileSelected.tempComments.findIndex(item => item.private_flg == data.private_flg);
            if (commentIndex > -1){
                state.fileSelected.tempComments.splice(commentIndex, 1);
            }
        }
    },
    homeAddAction(state, data) {
        if(!state.fileSelected) {
            return Promise.resolve();
        }
        let oldData = null;

        /*if(data.name === ACTIONS.ZOOM) {
            oldData = data.zoom;
        }*/
        state.fileSelected.actions.push({name:data.name, pageno: data.pageno, oldData: oldData});

        //return Promise.resolve();
    },
    homeUndoAction(state){
        state.hasAction = !state.hasAction;
        if (!state.fileSelected){
            return;
        }
        const action = state.fileSelected.actions.pop();
        if(!action) {
            return;
        }
        if(action.name === ACTIONS.ADD_STAMP) {
            if(action.pageno) {
                var deleteStamps = state.fileSelected.pages[action.pageno - 1].stamps.pop();
                if(!deleteStamps.selected){
                    state.fileSelected.pages[action.pageno - 1].deleteInfo.push(deleteStamps);
                    state.disabledProceed = false;
                }
            }
        }
        if(action.name === ACTIONS.UPDATE_STAMP) {
            if(action.pageno) {
                state.fileSelected.pages[action.pageno - 1].stamps.map(item=>{
                    if(item.index === action.oldData.index) {
                        item.x = action.oldData.x;
                        item.y = action.oldData.y;
                        item.width = action.oldData.width;
                        item.hight = action.oldData.hight;
                        item.scaleX = action.oldData.scaleX;
                        item.scaleY = action.oldData.scaleY;
                        item.rotation = action.oldData.rotation;
                    }
                });
            }
        }
        if(action.name === ACTIONS.DELETE_STAMP) {
            if (action.pageno) {
                state.fileSelected.pages[action.pageno - 1].stamps.push(action.oldData);
            }
        }

        if(action.name === ACTIONS.ADD_TEXT) {
            if(action.pageno) {
                var deleteTexts = state.fileSelected.pages[action.pageno - 1].texts.pop();
                state.fileSelected.pages[action.pageno - 1].deleteInfo.push(deleteTexts);
                state.disabledProceed = false;
            }
        }
        if(action.name === ACTIONS.UPDATE_TEXT) {
            if(action.pageno) {
                state.fileSelected.pages[action.pageno -1].texts.map(item=>{
                    if(item.index === action.oldData.index) {
                        item.x = action.oldData.x;
                        item.y = action.oldData.y;
                        item.text = action.oldData.text;
                        item.width = action.oldData.width;
                        item.fontSize = action.oldData.fontSize;
                        item.fontFamily = action.oldData.fontFamily;
                        item.scaleX = action.oldData.scaleX;
                        item.scaleY = action.oldData.scaleY;
                        item.rotation = action.oldData.rotation;
                    }
                });
            }
        }
        if(action.name === ACTIONS.DELETE_TEXT) {
            if (action.pageno) {
                state.fileSelected.pages[action.pageno - 1].texts.push(action.oldData);
            }
        }
        /*if(action.name === ACTIONS.ZOOM) {
            if(state.fileSelected) state.fileSelected.zoom = action.oldData;
        }*/
    },
    homeSaveFile(state) {

    },
    saveStampsOrderSuccess(state) {
        state.isUpdateStampOrder = false;
    },
    homeSetFirstPageImage(state, image) {
        state.first_page_image = image;
    },
    homeChangePositionFile(state, data) {
        if(!state.files[data.from]) return;
        state.files.splice(data.to, 0, state.files.splice(data.from, 1)[0]);
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
    initFileSelected(state) {
      let loginUser = JSON.parse(getLS('user'));
      if(state.usingPublicHash) loginUser = {};

      state.fileSelected = state.files[state.files.findIndex(item => !item.confidential_flg || (item.confidential_flg && loginUser.mst_company_id === item.mst_company_id))];
    },
    initFileSelectedByHash(state, user) {
      if(!user) return;
      state.fileSelected = state.files[state.files.findIndex(item => !item.confidential_flg || (item.confidential_flg && user.mst_company_id === item.mst_company_id))];
    },
    addCircularUserSuccess(state, datas) {
        if(!datas) return;
        if(!state.circular) return;
        datas = datas.map(item => {item.isAddNew = true; return item});
        state.circular.users.push(...datas);
    },
    addChildCircularUserSuccess(state, data) {
        if(!data) return;
        if(!state.circular) return;
        data.isAddNew = true;
        const index = state.circular.users.findIndex(item => item.parent_send_order === data.parent_send_order && item.child_send_order === (data.child_send_order -1));
        //const length = state.circular.users.filter(item => item.id === data.parent_id);
        if(index > -1) state.circular.users.splice(index + 1,0,data);
    },
    removeCircularUserSuccess(state, newUsers) {
        if(!state.circular) return;
        if (Array.isArray(newUsers)){
            state.circular.users.length = 0;
            state.circular.users.push.apply(state.circular.users, newUsers);
        }
       /* const user = state.circular.users.find(item => item.id === id);
        if(!user) return;*/
       /* if(user.parent_send_order > 0 && user.child_send_order === 1) {
            var index = 0;
            while (index < state.circular.users.length) {
                if (state.circular.users[index].parent_send_order == user.parent_send_order) {
                    state.circular.users.splice(index, 1);
                } else {
                    ++index;
                }
            }
        }else {
          state.circular.users.splice(state.circular.users.findIndex(item => item.id === id), 1);
        }*/
        /*state.circular.users.splice(state.circular.users.findIndex(item => item.id === id), 1);
        for(var i = 0; i < state.circular.users.length; i++){
            var item = state.circular.users[i];
            if(item.parent_send_order === user.parent_send_order) {
                if(item.child_send_order > user.child_send_order) item.child_send_order = item.child_send_order - 1;
            }
            if(user.parent_send_order > 0 && user.child_send_order === 1 && item.parent_send_order > user.parent_send_order){


                item.parent_send_order = item.parent_send_order - 1;
            }
        }*/
    },
    clearCircularUsersSuccess(state) {
        if(!state.circular) return;
        if(!state.circular.users) return;
        if(state.circular.users.length <= 1) return;
        state.circular.users.splice(1, state.circular.users.length);
    },
    homeUpdateCircularUsers(state, data) {
        if(!state.circular) return;
        state.oldCircularUsers = state.circular.users.slice();
        state.circular.users = data;
        state.selectUserChange = !state.selectUserChange;
    },
    rollBackCircularUsers(state) {
        state.circular.users = state.oldCircularUsers.slice();
        state.oldCircularUsers = [];
    },
    homeUpdateCircularUserOrder(state, user) {
        if(!user) return;
        if(!state.circular) return;
        if(!state.circular.users) return;
        state.circular.users.map(item => {
            if(item.id === user.id) {
                item.parent_send_order = user.parent_send_order;
                item.child_send_order = user.child_send_order;
            }
        })
    },
    setUsingPublicHash(state, value) {
        state.usingPublicHash = value;
    },
    checkAddUsingTas(state, value) {
        state.usingTas = value;
    },
    updateCircularUserSuccess(state, circular_user) {
        if(!state.circular) return;
        if(!state.circular.users) return;
        state.circular.users.map(item => {
            if(item.id === circular_user.id) {
                item.circular_status = circular_user.circular_status;
            }
        })
    },
    setAccessCodeFlg(state, data) {
        state.accessCodePopupActive = true;
        state.tmpData = data;
        state.currentUserIdentity = data.circular.current_user_identity ? true : false; // 社内社外回覧フラグ 社内:false 社外:true
    },
    clearTmpData (state) {
        state.tmpData = null;
    },
    disableAccessCodeFlg(state) {
        state.accessCodePopupActive = false;
    },
    updateCircularStatus(state, status) {
        state.circular.circular_status = status;
    },
    setTemplateEditFlg(state, status) {
        state.templateEditFlg = status;
    },
    updateConfidentialFlg(state, value) {
      if(!state.fileSelected) return;
      state.fileSelected.confidential_flg = value;
    },
    updateCurrentParentSendOrder(state, value) {
        state.parent_send_order = value;
    },
    updateOverlayHiddenFlg(state, value) {
      if(!state.fileSelected) return;
      state.fileSelected.overlay_hidden_flg = value;
    },
    approvalRequestSendBackSuccess(state, approval_user) {
      if(!state.circular || !state.circular.users) return;
      state.circular.users.map(item => {
        if(item.parent_send_order === approval_user.parent_send_order) {
          if(![CIRCULAR_USER.PULL_BACK_TO_USER_STATUS, CIRCULAR_USER.NOTIFIED_UNREAD_STATUS, CIRCULAR_USER.READ_STATUS].includes(item.circular_status)) {
            item.circular_status = CIRCULAR_USER.NOT_NOTIFY_STATUS;
          }
          if([CIRCULAR_USER.PULL_BACK_TO_USER_STATUS, CIRCULAR_USER.NOTIFIED_UNREAD_STATUS, CIRCULAR_USER.READ_STATUS].includes(item.circular_status)) {
            item.circular_status = CIRCULAR_USER.END_OF_REQUEST_SEND_BACK;
          }
        }
      });
    },
    setCloseCheck(state, value) {
        state.closeCheck = value;
    },
    // PAC_5-1702 別回覧で登録した閲覧者が、データ上は登録されていないものの画面上にのみ表示される
    updateCircularChangeListUserView(state, value){
        state.circularChangeListUserView[value.id] = value.data;
    },
    //PAC_5-3116 Boxを表示する時Boxを移動できるスライドバーだけを表示する
    updateCloudBoxFlg(state, value){
        state.cloudBoxFlg = value;
    },
};

export const home = {
    namespaced: true,
    state,
    actions,
    mutations
};
