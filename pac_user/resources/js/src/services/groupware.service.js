import config from "../app.config";
import Axios from "axios";
import store from '../store/store';


export var groupwareService;
export default (groupwareService = {
    getTokenGroupware,
    getUserAppUsageStatus,
    getListNoticeGroupware,
    getUnreadNoticeGroupware,
    markAllReadNoticeGroupware,
    markReadNoticeGroupware,
    updateRefreshToken,
    getAppRole
});

function getUserAppUsageStatus(tokenGroupware) {
    let headers = {
        'Authorization': 'Bearer ' + tokenGroupware
    };
    return Axios.get(`${config.GROUPWARE_API_URL}/mst-application-users/auth`,{headers : headers})
        .then(response => {
            return Promise.resolve(response.data);
        })
        .catch(error => {
            error = error.response;
            const message = (error && error.data && error.data.message) || error.statusText;
            return Promise.reject(error);
        });
}

function getTokenGroupware(data) {
    return Axios.post(`${config.GROUPWARE_API_URL}/auth/link`, data)
        .then(response => {
            return Promise.resolve(response.data);
        })
        .catch(error => {
            error = error.response;
            return Promise.reject(error);
        });
}

function getUnreadNoticeGroupware(tokenGroupware) {
    let headers = {
        'Authorization': 'Bearer ' + tokenGroupware
    };
    return Axios.get(`${config.GROUPWARE_API_URL}/notice-management/unread-cnt`,{headers : headers})
      .then(response => {
          return Promise.resolve(response);
      })
      .catch(error => {
          error = error.response;
          return Promise.reject(error);
      });
}

function getListNoticeGroupware(data) {
    let headers = {
        'Authorization': 'Bearer ' + data.tokenGroupware
    };
    return Axios.get(`${config.GROUPWARE_API_URL}/notice-management/list?page=${data.page}`,{headers : headers})
      .then(response => {
          return Promise.resolve(response.data);
      })
      .catch(error => {
          error = error.response;
          const message = (error && error.data && error.data.message) || error.statusText;
          return Promise.reject(message);
      });
}

function markAllReadNoticeGroupware(tokenGroupware) {
    let headers = {
        'Authorization': 'Bearer ' + tokenGroupware
    };
    return Axios.put(`${config.GROUPWARE_API_URL}/notice-management/mark-as-all-read`,null,{headers : headers})
      .then(response => {
          return Promise.resolve(response);
      })
      .catch(error => {
          error = error.response;
          const message = (error && error.data && error.data.message) || error.statusText;
          return Promise.reject(message);
      });
}

function markReadNoticeGroupware(data) {
    let headers = {
        'Authorization': 'Bearer ' + data.tokenGroupware,
        'Content-Type' : 'application/json'
    };
    return Axios.put(`${config.GROUPWARE_API_URL}/notice-management/mark-as-read/${data.id}`,{isRead:1},{headers : headers})
      .then(response => {
          return Promise.resolve(response.data);
      })
      .catch(error => {
          error = error.response;
          const message = (error && error.data && error.data.message) || error.statusText;
          return Promise.reject(message);
      });
}

function updateRefreshToken(refreshToken) {
    return Axios.post(`${config.GROUPWARE_API_URL}/auth/refresh`,{refreshToken: refreshToken})
      .then(response => {
          return Promise.resolve(response.data);
      })
      .catch(error => {
          error = error.response;
          const message = (error && error.data && error.data.message) || error.statusText;
          return Promise.reject(message);
      });
}

function getAppRole() {
    return Axios.get(`${config.BASE_API_URL}/groupware/app_role`)
        .then(response => {
            return Promise.resolve(response.data);
        })
        .catch(error => {
            error = error.response;
            const message = (error && error.data && error.data.message) || error.statusText;
            return Promise.reject(message);
        });
}