import hrService from "../../services/hr.service";

const state = {
};

const actions = {
    getHrTimeCardDetail({ dispatch, commit }, ) {
        return hrService.getHrTimeCardDetail().then(
            response => {
                return Promise.resolve(response.data);
            },
            error => {
                dispatch("alertError", error, { root: true });
                return Promise.reject(false);
            }
        );
    },
    getWorkDetailByTimecard({ dispatch, commit }, timecard_detail_id) {
        return hrService.getWorkDetailByTimecard(timecard_detail_id).then(
            response => {
                return Promise.resolve(response.data);
            },
            error => {
                dispatch("alertError", error, { root: true });
                return Promise.reject(false);
            }
        );
    },
    updateWorkDetailByTimecard({ dispatch, commit }, data) {
        return hrService.updateWorkDetailByTimecard(data).then(
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
    createWorkDetailByTimecard({ dispatch, commit }, data) {
        return hrService.createWorkDetailByTimecard(data).then(
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
    getMstHrInfo({ dispatch, commit }, info) {
        return hrService.getMstHrInfo(info).then(
            response => {
                return Promise.resolve(response.data);
            },
            error => {
                dispatch("alertError", error, { root: true });
                return Promise.reject(false);
            }
        );
    },
    registerNewTimeCardDetail({ dispatch, commit }, info) {
        return hrService.registerNewTimeCardDetail(info).then(
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
    updateLeaveWork({ dispatch, commit }, info) {
        return hrService.updateLeaveWork(info).then(
            response => {
                if(!response) return;
                dispatch("alertSuccess", response.message, { root: true });
                return Promise.resolve(response.data);
            },
            error => {
                dispatch("alertError", error, { root: true });
                return Promise.reject(false);
            }
        );
    },
    getWorkDetailByWorkingMonth({ dispatch, commit }, working_month) {
        return hrService.getWorkDetailByWorkingMonth(working_month).then(
            response => {
                return Promise.resolve(response.data);
            },
            error => {
                dispatch("alertError", error, { root: true });
                return Promise.reject(false);
            }
        );
    },
    getHrTimeCardDetailByWorkingMonth({ dispatch, commit }, working_month) {
        return hrService.getHrTimeCardDetailByWorkingMonth(working_month).then(
            response => {
                return Promise.resolve(response.data);
            },
            error => {
                dispatch("alertError", error, { root: true });
                return Promise.reject(false);
            }
        );
    },
    exportToPDF({ dispatch, commit}, info) {
        return hrService.exportToPDF(info).then(
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
    exportJoinWkListToPDF({ dispatch, commit}, info) {
        return hrService.exportJoinWkListToPDF(info).then(
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
    getHrUserWorkList ({ dispatch, commit}, info) {
        return hrService.getHrUserWorkList(info).then(
            response => {
                return Promise.resolve(response.data);
            },
            error => {
                dispatch("alertError", error, { root: true });
                return Promise.reject(false);
            }

        );
    },
    updateHrUserWorkList ({ dispatch, commit}, info) {
        return hrService.updateHrUserWorkList(info).then(
            response => {
                return Promise.resolve(response.data);
            },
            error => {
                dispatch("alertError", error, { root: true });
                return Promise.reject(false);
            }

        );
    },
    getUserWorkDetail({ dispatch, commit }, { id, working_month }) {
        return hrService.getUserWorkDetail(id, working_month).then(
            response => {
                return Promise.resolve(response.data);
            },
            error => {
                dispatch("alertError", error, { root: true });
                return Promise.reject(false);
            }
        );
    },
    updateUserWorkDetail({ dispatch, commit}, info) {
        return hrService.updateUserWorkDetail(info).then(
            response => {
                return Promise.resolve(response.data);
            },
            error => {
                dispatch("alertError", error, { root: true });
                return Promise.reject(false);
            }

        );
    },
    getUser({ dispatch, commit }, id) {
        return hrService.getUser(id).then(
            response => {
                return Promise.resolve(response.data);
            },
            error => {
                dispatch("alertError", error, { root: true });
                return Promise.reject(false);
            }
        );
    },
    exportUserWorkDetailToCSV({ dispatch, commit }, data) {
        return hrService.exportUserWorkDetailToCSV(data).then(
            response => {
                return Promise.resolve(response.data);
            },
            error => {
                dispatch("alertError", error, { root: true });
                return Promise.reject(false);
            }
        );
    },
    updateUserWorkDetailByTimecard({ dispatch, commit }, data) {
        return hrService.updateUserWorkDetailByTimecard(data).then(
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
    exportUserWorkListToCSV({ dispatch, commit }, data) {
        return hrService.exportUserWorkListToCSV(data).then(
            response => {
                return Promise.resolve(response.data);
            },
            error => {
                dispatch("alertError", error, { root: true });
                return Promise.reject(false);
            }
        );
    },
    createUserWorkDetailByTimecard({ dispatch, commit }, data) {
        return hrService.createUserWorkDetailByTimecard(data).then(
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
    updateSubmissionState({ dispatch, commit }, data) {
        return hrService.updateSubmissionState(data).then(
            response => {
                return Promise.resolve(response.data);
            },
            error => {
                dispatch("alertError", error, { root: true });
                return Promise.reject(false);
            }
        );
    },

    updateUserSubmissionState({ dispatch, commit }, data) {
        return hrService.updateUserSubmissionState(data).then(
            response => {
                return Promise.resolve(response.data);
            },
            error => {
                dispatch("alertError", error, { root: true });
                return Promise.reject(false);
            }
        );
    },

    getHrUserWorkStatusList ({ dispatch, commit}, info) {
        return hrService.getHrUserWorkStatusList(info).then(
            response => {
                return Promise.resolve(response.data);
            },
            error => {
                dispatch("alertError", error, { root: true });
                return Promise.reject(false);
            }
        );
    },
    getHrDailyReportList ({ dispatch, commit}, info) {
        return hrService.getHrDailyReportList(info).then(
            response => {
                return Promise.resolve(response.data);
            },
            error => {
                dispatch("alertError", error, { root: true });
                return Promise.reject(false);
            }
        );
    },
    getUserWorkDetailByTimecard({ dispatch, commit }, timecard_detail_id) {
        return hrService.getUserWorkDetailByTimecard(timecard_detail_id).then(
            response => {
                return Promise.resolve(response.data);
            },
            error => {
                dispatch("alertError", error, { root: true });
                return Promise.reject(false);
            }
        );
    },
    getUserHrInfo({ dispatch, commit }, { id }) {
        return hrService.getUserHrInfo(id).then(
            response => {
                return Promise.resolve(response.data);
            },
            error => {
                dispatch("alertError", error, { root: true });
                return Promise.reject(false);
            }
        );
    },
    getWorkDetailHrInfo({ dispatch, commit }, { id }) {
        return hrService.getWorkDetailHrInfo(id).then(
            response => {
                return Promise.resolve(response.data);
            },
            error => {
                dispatch("alertError", error, { root: true });
                return Promise.reject(false);
            }
        );
    },
    getHrMailSetting({ dispatch, commit }, ) {
        return hrService.getHrMailSetting().then(
            response => {
                return Promise.resolve(response.data);
            },
            error => {
                dispatch("alertError", error, { root: true });
                return Promise.reject(false);
            }
        );
    },
    updateBreakWork({ dispatch, commit }, info) {
        return hrService.updateBreakWork(info).then(
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
    updateHrMailSetting({dispatch, commit}, info){
        return hrService.updateHrMailSetting(info).then(
            response => {
                if(!response) return;
                dispatch("alertSuccess", response.message, { root: true });
                return Promise.resolve(response.success);
            },
            error => {
                dispatch("alertError", error, { root: true });
                return Promise.resolve(false);
            }
        );
    },
    //送信
    hrMailSend({dispatch, commit}, info) {
        return hrService.hrMailSend(info).then(
            response => {
                if(!response) return;
                dispatch("alertSuccess", response.message, {root: true});
                return Promise.resolve(response.success);
            },
            error => {
                dispatch("alertError", error, {root: true});
                return Promise.resolve(false);
            }
        );
    },
};

const mutations = {
};

export const hr = {
    namespaced: true,
    state,
    actions,
    mutations
};
