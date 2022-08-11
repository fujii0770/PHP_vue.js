import config from "../app.config";
import Axios from "axios";
import store from "../store/store";

export var expenseService;
export default (expenseService = {
    getListReceived,
});

function getListReceived(info) {
    return Axios.post(`${config.BASE_API_URL}/expense/received`, info)
        .then(response => {
            return Promise.resolve(response.data);
        })
        .catch(error => {
            error = error.response;
            const message = (error && error.data && error.data.message) || error.statusText;
            return Promise.reject(message);
        });
}