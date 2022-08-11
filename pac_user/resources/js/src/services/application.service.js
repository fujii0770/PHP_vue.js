import config from "../app.config";
import Axios from "axios";
import store from '../store/store';

export var applicationService;
export default (applicationService = {
  getDepartmentUsers,
  getDepartmentUsersWithOption,
  sendNotifyFirst,
  sendNotifyContinue,
  signatureCircular,
  updateOperationNotice,
  autoStorageBox,
  saveCircularSetting,
  getCircularSetting,
  sendAllUserFirst,
});

function getDepartmentUsers(options) {
    return Axios.get(`${config.BASE_API_URL}${store.state.home.usingPublicHash ? '/public/getDepartmentsByHash': '/users-departments'}?filter=${options.filter}`,{data: {usingHash: store.state.home.usingPublicHash}})
        .then(response => {
            return Promise.resolve(response.data);
        })
        .catch(error => {
            error = error.response;
            const message = (error && error.data && error.data.message) || error.statusText;
            return Promise.reject(message);
        });
}

function getDepartmentUsersWithOption(options) {
  // options.option='1' : 0+1+2 (回覧利用者 + グループウェア専用利用者 + 受信専用利用者)
  return Axios.get(`${config.BASE_API_URL}${store.state.home.usingPublicHash ? '/public/getDepartmentsByHash': '/users-departments'}?filter=${options.filter}&option=${options.option}`,{data: {usingHash: store.state.home.usingPublicHash}})
      .then(response => {
          return Promise.resolve(response.data);
      })
      .catch(error => {
          error = error.response;
          const message = (error && error.data && error.data.message) || error.statusText;
          return Promise.reject(message);
      });
}


function saveCircularSetting(data) {
    return Axios.post(`${config.BASE_API_URL}/circulars/${store.state.home.circular.id}/users/saveCircularSetting`, data)
        .then(response => {
            store.state.home.circular.update_at = response.data.data;
            return Promise.resolve(response.data);
        })
        .catch(error => {
            error = error.response;
            const message = (error && error.data && error.data.message) || error.statusText;
            return Promise.reject(message);
        });
}

function getCircularSetting() {
    return Axios.post(`${config.BASE_API_URL}/circulars/${store.state.home.circular.id}/users/getCircularSetting`)
        .then(response => {
            return Promise.resolve(response.data);
        })
        .catch(error => {
            error = error.response;
            const message = (error && error.data && error.data.message) || error.statusText;
            return Promise.reject(message);
        });
}

function sendNotifyFirst(data) {
  return Axios.post(`${config.BASE_API_URL}/circulars/${store.state.home.circular.id}/users/sendNotifyFirst`, data)
    .then(response => {
      return Promise.resolve(response.data);
    })
    .catch(error => {
      error = error.response;
      const message = (error && error.data && error.data.message) || error.statusText;
      return Promise.reject(message);
    });
}
// PAC_5-2353
function sendAllUserFirst(data){
    return Axios.post(`${config.BASE_API_URL}/circulars/${store.state.home.circular.id}/users/sendAllUserFirst`, data)
        .then(response => {
            return Promise.resolve(response.data);
        })
        .catch(error => {
            error = error.response;
            const message = (error && error.data && error.data.message) || error.statusText;
            return Promise.reject(message);
        });
}

function signatureCircular() {
  return Axios.post(`${config.LOCAL_API_URL}/circular/${store.state.home.circular.id}/signatureCircular`,{update_at:store.state.home.circular.update_at})
    .then(response => {
      return Promise.resolve(response.data);
    })
    .catch(error => {
      error = error.response;
      const message = (error && error.data && error.data.message) || error.statusText;
      return Promise.reject(message);
    });
}


function sendNotifyContinue(data) {
  data.usingHash = store.state.home.usingPublicHash;
  return Axios.post(`${config.BASE_API_URL}${store.state.home.usingPublicHash ? '/public': ''}/circulars/${store.state.home.circular.id}/users/sendNotifyContinue`, data)
    .then(response => {
      return Promise.resolve(response.data);
    })
    .catch(error => {
      error = error.response;
      const message = (error && error.data && error.data.message) || error.statusText;
      return Promise.reject(message);
    });
}

function updateOperationNotice(data) {
  return Axios.post(`${config.BASE_API_URL}/user/updateOperationNotice`, data)
    .then(response => {
      return Promise.resolve(response.data);
    })
    .catch(error => {
      error = error.response;
      const message = (error && error.data && error.data.message) || error.statusText;
      return Promise.reject(message);
    });
}

function autoStorageBox(data) {
    return Axios.post(`${config.BASE_API_URL}${store.state.home.usingPublicHash ? '/public': ''}/autoStorageBox`, data, {data:{nowait: true,usingHash: store.state.home.usingPublicHash}})
        .then(response => {
            return Promise.resolve(response.data);
        })
        .catch(error => {
            error = error.response;
            const message = (error && error.data && error.data.message) || error.statusText;
            return Promise.reject(message);
        });
}