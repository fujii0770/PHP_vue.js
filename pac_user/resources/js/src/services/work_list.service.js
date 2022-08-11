import config from "../app.config";
import Axios from "axios";

export var workListService;
export default workListService = {
    getHrWorkList
}

function getHrWorkList(data) {
    return Axios.get(`${config.BASE_API_URL}/work-list`, {params: data})
        .then(response => {
            return Promise.resolve(response.data);
        })
        .catch(error => {
            error = error.response;
            const message = (error && error.data && error.data.message) || error.statusText;
            return Promise.reject(message);
        });

}