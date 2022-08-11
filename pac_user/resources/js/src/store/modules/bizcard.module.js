import bizcardService from "../../services/bizcard.service";

const actions = {
    getListBizcard({ dispatch, commit }, info) {
        return bizcardService.getListBizcard(info).then(
          response => {
            return Promise.resolve(response.data);
          },
          error => {
            dispatch("alertError", error.result_message, { root: true });
            return Promise.reject(false);
          }
      );
    },
    getBizcardById({ dispatch, commit}, info) {
        return bizcardService.getBizcardById(info).then(
            response => {
                return Promise.resolve(response.data);
            },
            error => {
                dispatch("alertError", error.result_message, {root: true});
                return Promise.resolve(null);
            }
        )
    },
    getBizcardByIdPublic({ dispatch, commit}, info) {
        return bizcardService.getBizcardByIdPublic(info).then(
            response => {
                return Promise.resolve(response.data);
            },
            error => {
                dispatch("alertError", error.result_message, {root: true});
                return Promise.resolve(null);
            }
        )
    },
    registerBizcard({ dispatch, commit }, info) {
        return bizcardService.registerBizcard(info).then(
            response => {
                dispatch("alertSuccess", response.message, { root: true });
                return Promise.resolve(response.data);
            },
            error => {
                dispatch("alertError", error.result_message, { root: true });
                return Promise.reject(false);
            }
        );
    },
    deleteBizcard({ dispatch, commit }, info) {
        return bizcardService.deleteBizcard(info).then(
            response => {
                dispatch("alertSuccess", `${response}件の名刺を削除しました。`, { root: true });
                return Promise.resolve(response);
            },
            error => {
                dispatch("alertError", "名刺削除に失敗しました。", { root: true });
                return Promise.reject(false);
            }
        );
    },
    updateBizcard({ dispatch, commit }, info) {
        return bizcardService.updateBizcard(info).then(
            response => {
                dispatch("alertSuccess", response.message, { root: true });
                return Promise.resolve(response.data);
            },
            error => {
                dispatch("alertError", error.result_message, { root: true });
                return Promise.reject(false);
            }
        );
    },
    getByLinkPageURL({ dispatch, commit }, info) {
        return bizcardService.getByLinkPageURL(info).then(
          response => {
            return Promise.resolve(response.data);
          },
          error => {
            dispatch("alertError", error.result_message, { root: true });
            return Promise.reject(false);
          }
      );
    },
    getMyBizcard({ dispatch, commit}) {
        return bizcardService.getMyBizcard().then(
            response => {
                return Promise.resolve(response.data);
            },
            error => {
                dispatch("alertError", error.result_message, {root: true});
                return Promise.resolve(null);
            }
        )
    },
    getURL({ dispatch, commit }, id) {
        return bizcardService.getURL(id).then(
            response => {
                return Promise.resolve(response.data);
            },
            error => {
                dispatch("alertError", error.result_message, { root: true });
                return Promise.reject(false);
            }
        )
    },
    uploadZip({ dispatch, commit }, file) {
        return bizcardService.uploadZip(file).then(
            response => {
                dispatch("alertSuccess", response.message, { root: true });
                return Promise.resolve(response.data);
            },
            error => {
                dispatch("alertError", error.result_message, { root: true });
                return Promise.reject(false);
            }
        )
    },
    deleteZip({ dispatch, commit }, zipUploadTime) {
        return bizcardService.deleteZip(zipUploadTime).then(
            response => {
                response.data.message = response.message;
                return Promise.resolve(response.data);
            },
            error => {
                return Promise.reject(error);
            }
        )
    },
    uploadCsv({ dispatch, commit }, file) {
        return bizcardService.uploadCsv(file).then(
            response => {
                dispatch("alertSuccess", response.message, { root: true });
                return Promise.resolve(response.data);
            },
            error => {
                dispatch("alertError", error.result_message, { root: true });
                return Promise.reject(false);
            }
        )
    },
    multipleRegister({ dispatch, commit }, info) {
        return bizcardService.multipleRegister(info).then(
            response => {
                dispatch("alertSuccess", response.message, { root: true });
                return Promise.resolve(response.data);
            },
            error => {
                dispatch("alertError", error.result_message, { root: true });
                return Promise.reject(false);
            }
        )
    },
};

export const bizcard = {
    namespaced: true,
    actions,
};
