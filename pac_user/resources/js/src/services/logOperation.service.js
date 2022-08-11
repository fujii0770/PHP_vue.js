import config from "../app.config";
import Axios from "axios";
import store from '../store/store';

export var logOperationService;
export default (logOperationService = {
    addLog,
    getLastLogin,
});

function addLog(info) {
    var Axios_Config = { nowait: true, noToken: false, data:{usingHash: store.state.home.usingPublicHash}};

    return Axios.post(`${config.BASE_API_URL}${store.state.home.usingPublicHash ? '/public': ''}/store-log`,info, Axios_Config)
        .then(response => {
            return Promise.resolve(response.data);
        })
        .catch(error => {
            error = error.response;
            const message = (error && error.data && error.data.message) || error.statusText;
            return Promise.reject(message);
        });
}

function getLastLogin(data) {
    return Axios.get(`${config.BASE_API_URL}/loginat`)
        .then(response => {
            return Promise.resolve(response.data);
        })
        .catch(error => {
            error = error.response;
            const message = (error && error.data && error.data.message) || error.statusText;
            return Promise.reject(message);
        });
}
