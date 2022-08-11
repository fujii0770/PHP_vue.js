import config from "../app.config";
import Axios from "axios";
import store from "../store/store";

export var userService;
export default (userService = {
    getMyInfo,
    getWorkTime,
    getHoursTime,
    verifyMyInfo,
    updateMyInfo,
    updateComment,
    updateDisplaySetting,
    updatePassword,
    getInfoByHash,
    getDepartment,
    checkOutsideAccessCodeByHash,
    getAvatarUser,
    deleteImageProfile,
    updateUserImageProfileGroupWare,
});

function getMyInfo() {
    return Axios.get(`${config.BASE_API_URL}/myinfo`)
        .then(response => {
            return Promise.resolve(response.data);
        })
        .catch(error => {
            error = error.response;
            const message = (error && error.data && error.data.message) || error.statusText;
            return Promise.reject(message);
        });
}
function getWorkTime() {
    return Axios.get(`${config.BASE_API_URL}/worktime`)
        .then(response => {
            return Promise.resolve(response.data);
        })
        .catch(error => {
            error = error.response;
            const message = (error && error.data && error.data.message) || error.statusText;
            return Promise.reject(message);
        });
}
function getHoursTime(id) {
    return Axios.get(`${config.BASE_API_URL}/hours_work_time/`+id)
        .then(response => {
            return Promise.resolve(response.data);
        })
        .catch(error => {
            error = error.response;
            const message = (error && error.data && error.data.message) || error.statusText;
            return Promise.reject(message);
        });
}
function verifyMyInfo(info) {
    return Axios.post(`${config.LOCAL_API_URL}/public/verifyMyInfo`, {data: info})
        .then(response => {
            if (response.status == 206) {
                var linkRouter = response.data.message;
                window.location.href = linkRouter;
            } else {
                return Promise.resolve(response.data);
            }
        })
        .catch(error => {
            error = error.response;
            const message = (error && error.data && error.data.message) || (error && error.statusText) || false;
            return Promise.reject(message);
        });
}

function getInfoByHash() {
    return Axios.get(`${config.LOCAL_API_URL}/public/userByHashing`, {data: {nowait: true, usingHash: true}})
      .then(response => {
          if (response.status == 206) {
              var linkRouter = response.data.message;
              window.location.href = linkRouter;
          }else{
              return Promise.resolve(response.data);
          }
      })
      .catch(error => {
          error = error.response;
          const message = (error && error.data && error.data.message) || (error && error.statusText) || false;
          return Promise.reject(message);
      });
}

// パラメーターから社外アクセスコード認証
function checkOutsideAccessCodeByHash(data) {
    var Axios_data = {accessCodeHash: data.accessCodeHash, usingHash: true};
    return Axios.post(`${config.BASE_API_URL}${store.state.home.usingPublicHash ? '/public': ''}/checkOutsideAccessCodeByHash`, Axios_data)
        .then(response => {
            return Promise.resolve(response.data);
        })
        .catch(error => {
            error = error.response;
            const message = (error && error.data && error.data.message) || error.statusText;
            return Promise.reject(message);
        });
}

function updateMyInfo(info) {
    return Axios.post(`${config.BASE_API_URL}/myinfo`, {info})
    .then(response => {
        return Promise.resolve(response.data);
    })
    .catch(error => {
        error = error.response;
        const message = (error && error.data && error.data.message) || error.statusText;
        return Promise.reject(message);
    });
}

function updatePassword(info) {
    return Axios.post(`${config.BASE_API_URL}/user/update-password`, info)
    .then(response => {
        return Promise.resolve(response.data);
    })
    .catch(error => {
        error = error.response;
        const message = (error && error.data && error.data.message) || error.statusText;
        return Promise.reject(message);
    });
}

function updateComment(info) {
    return Axios.post(`${config.BASE_API_URL}/user/update-comment`, {info})
        .then(response => {
            return Promise.resolve(response.data);
        })
        .catch(error => {
            error = error.response;
            const message = (error && error.data && error.data.message) || error.statusText;
            return Promise.reject(message);
        });
}
function updateDisplaySetting(info) {
    return Axios.post(`${config.BASE_API_URL}/user/update-display-setting`, {info})
        .then(response => {
            return Promise.resolve(response.data);
        })
        .catch(error => {
            error = error.response;
            const message = (error && error.data && error.data.message) || error.statusText;
            return Promise.reject(message);
        });
}

function getDepartment(info) {
    return Axios.get(`${config.BASE_API_URL}/getCompanyDepartment`, {data: {nowait: true}})
        .then(response => {
            return Promise.resolve(response.data);
        })
        .catch(error => {
            error = error.response;
            const message = (error && error.data && error.data.message) || error.statusText;
            return Promise.reject(message);
        });
}

function getAvatarUser() {
    return Axios.get(`${config.BASE_API_URL}/userinfo`)
        .then(response => {
            return Promise.resolve(response.data);
        })
        .catch(error => {
            error = error.response;
            const message = (error && error.data && error.data.message) || error.statusText;
            return Promise.reject(message);
        });
}

function deleteImageProfile(data) {
    return Axios.post(`${config.BASE_API_URL}/userimage`, data)
        .then(response => {
            return Promise.resolve(response.data);
        })
        .catch(error => {
            error = error.response;
            const message = (error && error.data && error.data.message) || error.statusText;
            return Promise.reject(message);
        });
}
function updateUserImageProfileGroupWare(data) {
    let headers = {
        'Authorization': 'Bearer ' + data.tokenGroupware
    };   
    return Axios.put(`${config.GROUPWARE_API_URL}/mst-user/user-profile-data`,{userProfileData : data.userProfileData},{headers : headers})
      .then(response => {
          return Promise.resolve(response.data);
      })
      .catch(error => {
          error = error.response;
          const message = (error && error.data && error.data.message) || error.statusText;
          return Promise.reject(message);
      });
}