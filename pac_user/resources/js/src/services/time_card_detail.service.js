import config from "../app.config";
import Axios from "axios";

export var timeCardDetailService;
export default timeCardDetailService = {
    exportWorkListToCSV,
}

function exportWorkListToCSV(data) {
    return Axios.post(`${config.BASE_API_URL}/timecard-detail/export-work-list`, data)
        .then(response => {
            return Promise.resolve(response.data);
        })
        .catch(error => {
            error = error.response;
            const message = (error && error.data && error.data.message) || error.statusText;
            return Promise.reject(message);
        });
}
