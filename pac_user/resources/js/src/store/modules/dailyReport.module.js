import dailyReportService from "../../services/daily_report.service";

const state = { };

const actions = {
    getDailyReport ({ dispatch, commit}, info) {
        return dailyReportService.getDailyReport(info).then(
            response => {
                if (response.data) {
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
    createDailyReport ({ dispatch, commit}, info) {
        return dailyReportService.createDailyReport(info).then(
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
    updateDailyReport({ dispatch, commit }, info) {
        return dailyReportService.updateDailyReport(info).then(
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
    getUserDailyReport ({ dispatch, commit}, info) {
        return dailyReportService.getUserDailyReport(info).then(
            response => {
                if (response.data) {
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
    updateUserDailyReport({ dispatch, commit }, info) {
        return dailyReportService.updateUserDailyReport(info).then(
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
}

const mutations = {
};

export const dailyReport = {
    namespaced: true,
    state,
    actions,
    mutations
};