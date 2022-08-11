import {cloudService} from "../../services/cloud.service";
import {CIRCULAR} from "../../enums/circular";
import homeService from "../../services/home.service";
import fileDownload from "js-file-download";

const state = {
  drive: null
};

const actions = {
    getItems({ dispatch, commit, state }, folder_id) {
      return cloudService.getItems(state.drive,encodeURIComponent(folder_id)).then(
        response => {
          return Promise.resolve(response);
        },
        error => {
          //dispatch("alertError", error, { root: true });
          return Promise.reject(error);
        }
      );
    },
    downloadItem({ dispatch, commit, state }, file_data) {
        let { file_id, filename, file_max_document_size} = file_data;
        return cloudService.downloadItem(state.drive,file_id, filename, file_max_document_size).then(
            response => {
                dispatch("alertSuccess", response.message, { root: true });
                return Promise.resolve(response);
            },
            error => {
                dispatch("alertError", error, { root: true });
                return Promise.resolve(false);
            }
        );
    },
    downloadCloudAttachment({ dispatch, commit, state}, file_data){
        let { file_id, filename, file_max_attachment_size,circular_id} = file_data;
        return cloudService.downloadCloudAttachment(state.drive,file_id, filename, file_max_attachment_size,circular_id).then(
            response => {
                dispatch("alertSuccess", response.message, { root: true });
                return Promise.resolve(response);
            },
            error => {
                dispatch("alertError", error, { root: true });
                return Promise.resolve(false);
            }
        );
    },
    downloadCloudMailFile({ dispatch, commit, state}, file_data){
        let { file_id, filename, disk_mail_id,file_mail_size_single} = file_data;
        return cloudService.downloadCloudMailFile(state.drive,file_id, filename, disk_mail_id,file_mail_size_single).then(
            response => {
                dispatch("alertSuccess", response.message, { root: true });
                return Promise.resolve(response);
            },
            error => {
                dispatch("alertError", error, { root: true });
                return Promise.resolve(false);
            }
        );
    },
};

const mutations = {
  setDrive(state, drive) {
    state.drive = drive;
  }
};

export const cloud = {
    namespaced: true,
    state,
    actions,
    mutations
};
