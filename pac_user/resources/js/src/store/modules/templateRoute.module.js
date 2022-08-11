import templateRouteService from "../../services/templateRoute.service";

const state = { };

const actions = {
    getList({ dispatch, commit }, info) {
        return templateRouteService.getList(info).then(
          response => {
            return Promise.resolve(response.data);
          },
          error => {
            dispatch("alertError", error, { root: true });
            return Promise.reject(false);
          }
      );
    },
    getTemplateRouteList({ dispatch, commit }, info) {
        return templateRouteService.getTemplateRouteList(info).then(
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
    
};

export const templateRoute = {
    namespaced: true,
    state,
    actions,
    mutations
};
