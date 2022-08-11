import movieService from "../../services/movie.service";

const state = { };

const actions = {
    getListMovie({ dispatch, commit}, info) {
        return movieService.getListMovie(info).then(
            response => {
                return Promise.resolve(response.data);
            },
            error => {
                dispatch("alertError", error, { root: true });
                return Promise.reject(false);
            }
        );
    },
    getMovieTheme({ dispatch, commit}, info) {
        return movieService.getMovieTheme(info).then(
            response => {
                return Promise.resolve(response.data);
            },
            error => {
                dispatch("alertError", error, { root: true });
                return Promise.reject(false);
            }
        );
    },
    getListMovieTop({ dispatch, commit}, info) {
        return movieService.getListMovieTop(info).then(
            response => {
                return Promise.resolve(response.data);
            },
            error => {
                dispatch("alertError", error, { root: true });
                return Promise.reject(false);
            }
        );
    },
    addPlayCount({ dispatch, commit}, info) {
        return movieService.addPlayCount(info).then(
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

export const movie = {
    namespaced: true,
    state,
    actions,
    mutations
};