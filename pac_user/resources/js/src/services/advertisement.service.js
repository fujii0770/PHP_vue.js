import config from "../app.config";
import Axios from "axios";

export var advertisementService;
export default (advertisementService = {
    getListAdvertisement
});

function getListAdvertisement(data) {
    return Axios.get(`${config.BASE_API_URL}/advertisementmg`, {params: data})
        .then(response => {
            return Promise.resolve(response.data);
        })
        .catch(error => {
            error = error.response;
            const message = (error && error.data && error.data.message) || error.statusText;
            return Promise.reject(message);
        });
}