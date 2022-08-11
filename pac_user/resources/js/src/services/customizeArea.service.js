import config from "../app.config";
import Axios from "axios";

export var customizeService;
export default (customizeService = {
    getCustomizeAreaList,
});

function getCustomizeAreaList(data) {
    return Axios.get(`${config.BASE_API_URL}/customizemg`, {params: data})
        .then(response => {
            return Promise.resolve(response.data);
        })
        .catch(error => {
            error = error.response;
            const message = (error && error.data && error.data.message) || error.statusText;
            return Promise.reject(message);
        });
}