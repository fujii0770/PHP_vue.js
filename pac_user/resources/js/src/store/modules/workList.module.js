import workListService from "../../services/work_list.service"

const state = { };

const actions = {
    getHrWorkList ({ dispatch, commit}, info) {
        return workListService.getHrWorkList(info).then(
            response => {
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

export const workList = {
    namespaced: true,
    state,
    actions,
    mutations
};
