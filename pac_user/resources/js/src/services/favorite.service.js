import config from "../app.config";
import Axios from "axios";
import store from '../store/store';

export var favoriteService;
export default (favoriteService = {
    getList,  
    add,
    remove,
    updateSort,
    updateFavorite,
    sortFavoriteItem,
    deleteFavoriteItem,
});

 

function getList(info) {
    let favorite_name = info != undefined && info.favorite_name != undefined ? info.favorite_name :  '';
    /* PAC_5-1982 S */
    let favorite_flg = (info && info.favorite_flg) || 0;
    /* PAC_5-1982 E */
    return Axios.get(`${config.BASE_API_URL}${store.state.home.usingPublicHash ? '/public': ''}/favorites?favorite_name=${encodeURIComponent(favorite_name)}&favorite_flg=${favorite_flg} `,{info,data: {usingHash: store.state.home.usingPublicHash}})
        .then(response => {
            return Promise.resolve(response.data);
        })
        .catch(error => {
            error = error.response;
            const message = (error && error.data && error.data.message) || error.statusText;
            return Promise.reject(message);
        });
}
 
function add(info) {
    return Axios.post(`${config.BASE_API_URL}/favorites`, info)
        .then(response => {
            return Promise.resolve(response.data);
        })
        .catch(error => {
            error = error.response;
            const message = (error && error.data && error.data.message) || error.statusText;
            return Promise.reject(message);
        });
}

function remove(favorite_no,favorite_flg=0) {
    return Axios.delete(`${config.BASE_API_URL}${store.state.home.usingPublicHash ? '/public': ''}/favorites/${favorite_no}`, {data: {usingHash: store.state.home.usingPublicHash,favorite_flg:favorite_flg}})
        .then(response => {
            return Promise.resolve(response.data);
        })
        .catch(error => {
            error = error.response;
            const message = (error && error.data && error.data.message) || error.statusText;
            return Promise.reject(message);
        });
}

function updateSort(info) {
    return Axios.post(`${config.BASE_API_URL}/favorites/sort`, info)
        .then(response => {
            return Promise.resolve(response.data);
        })
        .catch(error => {
            error = error.response;
            const message = (error && error.data && error.data.message) || error.statusText;
            return Promise.reject(message);
        });
}

function updateFavorite(info) {
    return Axios.put(`${config.BASE_API_URL}${store.state.home.usingPublicHash ? '/public': ''}/favorites/${info.favorite_no}`,  {usingHash: store.state.home.usingPublicHash, users: info.users})
        .then(response => {
            return Promise.resolve(response.data);
        })
        .catch(error => {
            error = error.response;
            const message = (error && error.data && error.data.message) || error.statusText;
            return Promise.reject(message);
        });
}
function sortFavoriteItem(info){
    return Axios.post(`${config.BASE_API_URL}${store.state.home.usingPublicHash ? '/public': ''}/favorites/sortFavoriteItem`,  {usingHash: store.state.home.usingPublicHash,from_favorite: info.from_favorite,to_favorite: info.to_favorite})
        .then(response => {
            return Promise.resolve(response.data);
        })
        .catch(error => {
            error = error.response;
            const message = (error && error.data && error.data.message) || error.statusText;
            return Promise.reject(message);
        });
}
function deleteFavoriteItem(favorite_route_id){
    return Axios.delete(`${config.BASE_API_URL}${store.state.home.usingPublicHash ? '/public': ''}/favorites/deleteFavoriteItem/${favorite_route_id}`,{data: {usingHash: store.state.home.usingPublicHash}})
        .then(response => {
            return Promise.resolve(response.data);
        })
        .catch(error => {
            error = error.response;
            const message = (error && error.data && error.data.message) || error.statusText;
            return Promise.reject(message);
        });
}