import favoriteService from "../../services/favorite.service";

const state = { };

const actions = {
    getList({ dispatch, commit }, info) {        
        return favoriteService.getList(info).then(
          response => {
            return Promise.resolve(response.data);
          },
          error => {
            dispatch("alertError", error, { root: true });
            return Promise.reject(false);
          }
      );
    },
    add({ dispatch, commit }, info) {      
        return favoriteService.add(info).then(
          response => {
          //  dispatch("alertSuccess", response.message, { root: true });
            return Promise.resolve(true);
          },
          error => {              
            dispatch("alertError", error, { root: true });
            return Promise.reject(false);
          }
      );
    },
    remove({ dispatch, commit }, favorite_no) {      
        return favoriteService.remove(favorite_no).then(
          response => {
          //  dispatch("alertSuccess", response.message, { root: true });
            return Promise.resolve(true);
          },
          error => {              
            dispatch("alertError", error, { root: true });
            return Promise.reject(false);
          }
      );
    },
    /* PAC_5-1982 S*/
    removeView({ dispatch, commit }, data) {
        return favoriteService.remove(data.favorite_no,data.favorite_flg).then(
            response => {
                //  dispatch("alertSuccess", response.message, { root: true });
                return Promise.resolve(true);
            },
            error => {
                dispatch("alertError", error, { root: true });
                return Promise.reject(false);
            }
        );
    },
    /* PAC_5-1982 E*/
    updateSort({ dispatch, commit }, info) {      
        return favoriteService.updateSort(info).then(
          response => {
           // dispatch("alertSuccess", response.message, { root: true });
            return Promise.resolve(true);
          },
          error => {              
            dispatch("alertError", error, { root: true });
            return Promise.reject(false);
          }
      );
    },
    updateFavorite({dispatch}, info){
        return favoriteService.updateFavorite(info).then(
            response => {
                return Promise.resolve(response.data);
            },
            error => {
                dispatch("alertError", error, { root: true });
                return Promise.reject(false);
            });
    },
    sortFavoriteItem({dispatch}, info){
        return favoriteService.sortFavoriteItem(info).then(
            // eslint-disable-next-line no-unused-vars
            response => {
                return Promise.resolve(response.data);
            },
            error => {
                dispatch("alertError", error, {root: true});
                return Promise.reject(false);
            });
    },
    deleteFavoriteItem({dispatch}, favorite_route_id){
        return favoriteService.deleteFavoriteItem(favorite_route_id).then(
            // eslint-disable-next-line no-unused-vars
            response => {
                return Promise.resolve(response.data);
            },
            error => {
                dispatch("alertError", error, {root: true});
                return Promise.reject(false);
            });
    },
};

const mutations = {
    
};

export const favorite = {
    namespaced: true,
    state,
    actions,
    mutations
};
