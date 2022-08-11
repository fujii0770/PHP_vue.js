import config from "../app.config";
import Axios from "axios";

export var settingService;
export default (settingService = {
    getLimit,
    getPasswordPolicy,
    getProtection,
});

 

function getLimit() {
    return Axios.get(`${config.BASE_API_URL}/setting/limit`)
        .then(response => {
            return Promise.resolve(response.data);
        })
        .catch(error => {
            error = error.response;
            const message = (error && error.data && error.data.message) || error.statusText;
            return Promise.reject(message);
        });
}

function getPasswordPolicy() {
    return Axios.get(`${config.BASE_API_URL}/setting/password-policy`)
        .then(response => {
            return Promise.resolve(response.data);
        })
        .catch(error => {
            error = error.response;
            const message = (error && error.data && error.data.message) || error.statusText;
            return Promise.reject(message);
        });
}

function getProtection() {
    return Axios.get(`${config.BASE_API_URL}/setting/protection`)
        .then(response => {
            return Promise.resolve(response.data);
        })
        .catch(error => {
            error = error.response;
            const message = (error && error.data && error.data.message) || error.statusText;
            return Promise.reject(message);
        });
}
