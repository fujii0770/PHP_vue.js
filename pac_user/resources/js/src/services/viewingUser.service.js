import config from "../app.config";
import Axios from "axios";
import store from '../store/store';

export var viewingUserService;
export default (viewingUserService = {
    updateViewingUser
});

function updateViewingUser(info) {
    info.usingHash = store.state.home.usingPublicHash;
    return Axios.post(`${config.BASE_API_URL}${store.state.home.usingPublicHash ? '/public': ''}/circulars/${store.state.home.circular.id}/memo`, info)
        .then(response => {
            return Promise.resolve(response.data);
        })
        .catch(error => {
            error = error.response;
            const message = (error && error.data && error.data.message) || error.statusText;
            return Promise.reject(message);
        });
}
