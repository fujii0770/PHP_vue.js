import config from "../app.config";
import Axios from "axios";

export var dailyReportService;
export default dailyReportService = {
    getDailyReport,
    createDailyReport,
    updateDailyReport,
    getUserDailyReport,
    updateUserDailyReport,
}

function getDailyReport(data) {
    return Axios.get(`${config.BASE_API_URL}/daily-report`, {params: data})
        .then(response => {
            return Promise.resolve(response.data);
        })
        .catch(error => {
            error = error.response;
            const message = (error && error.data && error.data.message) || error.statusText;
            return Promise.reject(message);
        });
}

function createDailyReport(data) {
    return Axios.post(`${config.BASE_API_URL}/daily-report`, data)
        .then(response => {
            return Promise.resolve(response.data);
        })
        .catch(error => {
            error = error.response;
            const message = (error && error.data && error.data.message) || error.statusText;
            return Promise.reject(message);
        });
}

function updateDailyReport(data) {
    return Axios.put(`${config.BASE_API_URL}/daily-report/${data.id}`, data)
        .then(response => {
            return Promise.resolve(response.data);
        })
        .catch(error => {
            error = error.response;
            const message = (error && error.data && error.data.message) || error.statusText;
            return Promise.reject(message);
        });
}

function getUserDailyReport(data) {
    return Axios.get(`${config.BASE_API_URL}/user-daily-report/${data.id}`)
        .then(response => {
            return Promise.resolve(response.data);
        })
        .catch(error => {
            error = error.response;
            const message = (error && error.data && error.data.message) || error.statusText;
            return Promise.reject(message);
        });
}

function updateUserDailyReport(data) {
    return Axios.put(`${config.BASE_API_URL}/user-daily-report/${data.id}`, data)
        .then(response => {
            return Promise.resolve(response.data);
        })
        .catch(error => {
            error = error.response;
            const message = (error && error.data && error.data.message) || error.statusText;
            return Promise.reject(message);
        });
}