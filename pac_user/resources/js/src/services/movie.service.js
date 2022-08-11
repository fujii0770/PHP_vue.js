import config from "../app.config";
import Axios from "axios";

export var movieService;
export default (movieService = {
    getListMovie,
    getMovieTheme,
    getListMovieTop,
    addPlayCount,
});

function getListMovie(data) {
    return Axios.get(`${config.BASE_API_URL}/moviemg`, {params: data})
        .then(response => {
            return Promise.resolve(response.data);
        })
        .catch(error => {
            error = error.response;
            const message = (error && error.data && error.data.message) || error.statusText;
            return Promise.reject(message);
        });
}

function getMovieTheme(data) {
    return Axios.get(`${config.BASE_API_URL}/movietheme`, {params: data})
    .then(response => {
        return Promise.resolve(response.data);
    })
    .catch(error => {
        error = error.response;
        const message = (error && error.data && error.data.message) || error.statusText;
        return Promise.reject(message);
    });
}

function getListMovieTop(data) {
    return Axios.get(`${config.BASE_API_URL}/moviemgtop`, {params: data})
    .then(response => {
        return Promise.resolve(response.data);
    })
    .catch(error => {
        error = error.response;
        const message = (error && error.data && error.data.message) || error.statusText;
        return Promise.reject(message);
    });
}

function addPlayCount(data) {
    return Axios.post(`${config.BASE_API_URL}/movieaddplaycount`, data)
    .then(response => {
        return Promise.resolve(response.data);
    })
    .catch(error => {
        error = error.response;
        const message = (error && error.data && error.data.message) || error.statusText;
        return Promise.reject(message);
    });
}