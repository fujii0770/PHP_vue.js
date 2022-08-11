import config from "../app.config";
import Axios from "axios";

export var notificationService;
export default (notificationService = {
    getListNotification,
    updateReadNotification,
    getUnreadNoticeTotal
});

function getListNotification(data) {
    return Axios.get(`${config.BASE_API_URL}/noticemg`, {params: data})
        .then(response => {
            return Promise.resolve(response.data);
        })
        .catch(error => {
            error = error.response;
            const message = (error && error.data && error.data.message) || error.statusText;
            return Promise.reject(message);
        });
}

function updateReadNotification(data) {
    return Axios.post(`${config.BASE_API_URL}/noticeread`, data)
        .then(response => {
            return Promise.resolve(response.data);
        })
        .catch(error => {
            error = error.response;
            const message = (error && error.data && error.data.message) || error.statusText;
            return Promise.reject(message);
        });
}

function getUnreadNoticeTotal(data) {
    return Axios.get(`${config.BASE_API_URL}/noticeunread`)
      .then(response => {
          return Promise.resolve(response.data);
      })
      .catch(error => {
          error = error.response;
          const message = (error && error.data && error.data.message) || error.statusText;
          return Promise.reject(message);
      });
}