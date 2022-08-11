import config from "../app.config";
import Axios from "axios";

export var specialService;
export default (specialService = {
    getListReceived,
    getListTemplate,
    });

function getListReceived(info) {
    return Axios.post(`${config.BASE_API_URL}/special/received`, info)
        .then(response => {
            return Promise.resolve(response.data);
        })
        .catch(error => {
            error = error.response;
            const message = (error && error.data && error.data.message) || error.statusText;
            return Promise.reject(message);
        });
}

function getListTemplate(info) {
    return Axios.post(`${config.BASE_API_URL}/special/template`, info)
        .then(response => {
            return Promise.resolve(response.data);
        })
        .catch(error => {
            error = error.response;
            const message = (error && error.data && error.data.message) || error.statusText;
            return Promise.reject(message);
        });
}
