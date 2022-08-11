import notificationService from "../../services/notification.service";

const state = {
    unread: 0
};

const actions = {
    getListNotification({ dispatch, commit }, info) {
        return notificationService.getListNotification(info).then(
            response => {
                return Promise.resolve(response.data);
            },
            error => {
                dispatch("alertError", error, { root: true });
                return Promise.reject(false);
            }
        );
    },
    updateReadNotification({ dispatch, commit }, data) {
        return notificationService.updateReadNotification(data).then(
            response => {
                if(!response) return;
                // dispatch("alertSuccess", response.message, { root: true });
                return Promise.resolve(response.data);
            },
            error => {
                dispatch("alertError", error, { root: true });
                return Promise.resolve(false);
            }
        );
    },
    getUnreadNoticeTotal({ dispatch, commit }) {
        notificationService.getUnreadNoticeTotal().then(
            response => {
                commit("getUnreadNoticeTotalSuccess", response.data);
            },
            error => {
            //dispatch("alertError", error, { root: true });
            }
        );
    },
};

const mutations = {
    getUnreadNoticeTotalSuccess(state, total) {
        if(total === -1) {
            if(state.unread > 0) state.unread = state.unread - 1;
            return;
        }
        state.unread = parseInt(total);
    }
};

export const notice = {
    namespaced: true,
    state,
    actions,
    mutations
};
