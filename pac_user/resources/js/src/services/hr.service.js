import config from "../app.config";
import Axios from "axios";

export var hrService;
export default (hrService = {
    getHrTimeCardDetail,
    getMstHrInfo,
    registerNewTimeCardDetail,
    updateLeaveWork,
    getWorkDetailByTimecard,
    createWorkDetailByTimecard,
    updateWorkDetailByTimecard,
    getWorkDetailByWorkingMonth,
    getHrTimeCardDetailByWorkingMonth,
    exportToPDF,
    getHrUserWorkList,
    updateHrUserWorkList,
    getUserWorkDetail,
    updateUserWorkDetail,
    getUser,
    exportUserWorkDetailToCSV,
    createUserWorkDetailByTimecard,
    updateUserWorkDetailByTimecard,
    updateUserSubmissionState,
    exportUserWorkListToCSV,
    updateSubmissionState,
    getHrUserWorkStatusList,
    getHrDailyReportList,
    getUserWorkDetailByTimecard,
    getUserHrInfo,
    getWorkDetailHrInfo,
    getHrMailSetting,
    updateHrMailSetting,
    hrMailSend,
    updateBreakWork
});

function getHrTimeCardDetail() {
    return Axios.get(`${config.BASE_API_URL}/timecard-detail`)
        .then(response => {
            return Promise.resolve(response.data);
        })
        .catch(error => {
            error = error.response;
            const message = (error && error.data && error.data.message) || error.statusText;
            return Promise.reject(message);
        });
}

function getWorkDetailByTimecard(timecard_detail_id) {
    return Axios.get(`${config.BASE_API_URL}/detail-work-by-timecard/${timecard_detail_id}`)
        .then(response => {
            return Promise.resolve(response.data);
        })
        .catch(error => {
            error = error.response;
            const message = (error && error.data && error.data.message) || error.statusText;
            return Promise.reject(message);
        });
}

function createWorkDetailByTimecard(data) {
    return Axios.post(`${config.BASE_API_URL}/timecard-detail`, data)
        .then(response => {
            return Promise.resolve(response.data);
        })
        .catch(error => {
            error = error.response;
            const message = (error && error.data && error.data.message) || error.statusText;
            return Promise.reject(message);
        });
}

function updateWorkDetailByTimecard(data) {
    return Axios.put(`${config.BASE_API_URL}/timecard-detail/${data.id}`,data)
        .then(response => {
            return Promise.resolve(response.data);
        })
        .catch(error => {
            error = error.response;
            const message = (error && error.data && error.data.message) || error.statusText;
            return Promise.reject(message);
        });
}

function getMstHrInfo() {
    return Axios.get(`${config.BASE_API_URL}/hr-info`)
        .then(response => {
            return Promise.resolve(response.data);
        })
        .catch(error => {
            error = error.response;
            const message = (error && error.data && error.data.message) || error.statusText;
            return Promise.reject(message);
        });
}

function registerNewTimeCardDetail(info) {
    return Axios.get(`${config.BASE_API_URL}/register-new-time-card`, {params: info})
        .then(response => {
            return Promise.resolve(response.data);
        })
        .catch(error => {
            error = error.response;
            const message = (error && error.data && error.data.message) || error.statusText;
            return Promise.reject(message);
        });
}

function updateLeaveWork(info) {
    return Axios.put(`${config.BASE_API_URL}/leave-work/${info.id}`,info)
        .then(response => {
            return Promise.resolve(response.data);
        })
        .catch(error => {
            error = error.response;
            const message = (error && error.data && error.data.message) || error.statusText;
            return Promise.reject(message);
        });
}

function getWorkDetailByWorkingMonth(working_month) {
    return Axios.get(`${config.BASE_API_URL}/work/getWorkDetail/${working_month}` )
        .then(response => {
            return Promise.resolve(response.data);
        })
        .catch(error => {
            error = error.response;
            const message = (error && error.data && error.data.message) || error.statusText;
            return Promise.reject(message);
        });
}

function getHrTimeCardDetailByWorkingMonth(working_month) {
    return Axios.get(`${config.BASE_API_URL}/work/getWorkList/${working_month}` )
        .then(response => {
            return Promise.resolve(response.data);
        })
        .catch(error => {
            error = error.response;
            const message = (error && error.data && error.data.message) || error.statusText;
            return Promise.reject(message);
        });
}

function exportToPDF(info) {
    return Axios.get(`${config.BASE_API_URL}/timecard-detail/export-to-new-edition`,{params: info})
        .then(response => {
            return Promise.resolve(response.data);
        })
        .catch(error => {
            error = error.response;
            const message = (error && error.data && error.data.message) || error.statusText;
            return Promise.reject(message);
        });
}

function getHrUserWorkList(data) {
    return Axios.get(`${config.BASE_API_URL}/user-work-list`, {params: data})
        .then(response => {
            return Promise.resolve(response.data);
        })
        .catch(error => {
            error = error.response;
            const message = (error && error.data && error.data.message) || error.statusText;
            return Promise.reject(message);
        });
}

function updateHrUserWorkList(data) {
    return Axios.post(`${config.BASE_API_URL}/user-work-list/updateApprovalState`, data)
        .then(response => {
            return Promise.resolve(response.data);
        })
        .catch(error => {
            error = error.response;
            const message = (error && error.data && error.data.message) || error.statusText;
            return Promise.reject(message);
        });
}

function getUserWorkDetail(id, working_month) {
    return Axios.get(`${config.BASE_API_URL}/user-work-detail/${id}/${working_month}` )
        .then(response => {
            return Promise.resolve(response.data);
        })
        .catch(error => {
            error = error.response;
            const message = (error && error.data && error.data.message) || error.statusText;
            return Promise.reject(message);
        });
}

function updateUserWorkDetail(data) {
    return Axios.post(`${config.BASE_API_URL}/user-work-detail/updateApprovalState`, data)
        .then(response => {
            return Promise.resolve(response.data);
        })
        .catch(error => {
            error = error.response;
            const message = (error && error.data && error.data.message) || error.statusText;
            return Promise.reject(message);
        });
}

function getUser(id) {
    return Axios.get(`${config.BASE_API_URL}/user-work-detail/user/${id}` )
        .then(response => {
            return Promise.resolve(response.data);
        })
        .catch(error => {
            error = error.response;
            const message = (error && error.data && error.data.message) || error.statusText;
            return Promise.reject(message);
        });
}

function exportUserWorkDetailToCSV(data) {
    return Axios.post(`${config.BASE_API_URL}/user-work-detail/export-to-csv`,data)
        .then(response => {
            return Promise.resolve(response.data);
        })
        .catch(error => {
            error = error.response;
            const message = (error && error.data && error.data.message) || error.statusText;
            return Promise.reject(message);
        });
}

function createUserWorkDetailByTimecard(data) {
    return Axios.post(`${config.BASE_API_URL}/user-work-detail`, data)
        .then(response => {
            return Promise.resolve(response.data);
        })
        .catch(error => {
            error = error.response;
            const message = (error && error.data && error.data.message) || error.statusText;
            return Promise.reject(message);
        });
}

function exportUserWorkListToCSV(data) {
    return Axios.post(`${config.BASE_API_URL}/user-work-list/export-to-csv`,data)
        .then(response => {
            return Promise.resolve(response.data);
        })
        .catch(error => {
            error = error.response;
            const message = (error && error.data && error.data.message) || error.statusText;
            return Promise.reject(message);
        });
}

function updateUserWorkDetailByTimecard(data) {
    return Axios.put(`${config.BASE_API_URL}/user-work-detail/${data.id}`,data)
        .then(response => {
            return Promise.resolve(response.data);
        })
        .catch(error => {
            error = error.response;
            const message = (error && error.data && error.data.message) || error.statusText;
            return Promise.reject(message);
        });
}

function getHrUserWorkStatusList(data) {
    return Axios.get(`${config.BASE_API_URL}/user-work-status-list`, {params: data})
        .then(response => {
            return Promise.resolve(response.data);
        })
        .catch(error => {
            error = error.response;
            const message = (error && error.data && error.data.message) || error.statusText;
            return Promise.reject(message);
        });
}

function updateUserSubmissionState(data) {
    return Axios.post(`${config.BASE_API_URL}/user-work-detail/updateUserSubmissionState`, data)
        .then(response => {
            return Promise.resolve(response.data);
        })
        .catch(error => {
            error = error.response;
            const message = (error && error.data && error.data.message) || error.statusText;
            return Promise.reject(message);
        });
}

function updateSubmissionState(data) {
    return Axios.post(`${config.BASE_API_URL}/user-work-list/updateSubmissionState`, data)
        .then(response => {
            return Promise.resolve(response.data);
        })
        .catch(error => {
            error = error.response;
            const message = (error && error.data && error.data.message) || error.statusText;
            return Promise.reject(message);
        });
}

function getHrDailyReportList(data) {
    return Axios.get(`${config.BASE_API_URL}/user-daily-report`, {params: data})
        .then(response => {
            return Promise.resolve(response.data);
        })
        .catch(error => {
            error = error.response;
            const message = (error && error.data && error.data.message) || error.statusText;
            return Promise.reject(message);
        });
}

function getUserWorkDetailByTimecard(timecard_detail_id) {
    return Axios.get(`${config.BASE_API_URL}/user-work-detail/${timecard_detail_id}`)
        .then(response => {
            return Promise.resolve(response.data);
        })
        .catch(error => {
            error = error.response;
            const message = (error && error.data && error.data.message) || error.statusText;
            return Promise.reject(message);
        });
}

function getUserHrInfo(id) {
    return Axios.get(`${config.BASE_API_URL}/user-work-detail―get-hr-info/${id}` )
        .then(response => {
            return Promise.resolve(response.data);
        })
        .catch(error => {
            error = error.response;
            const message = (error && error.data && error.data.message) || error.statusText;
            return Promise.reject(message);
        });
}

function getWorkDetailHrInfo(id) {
    return Axios.get(`${config.BASE_API_URL}/user-hr-info/${id}` )
        .then(response => {
            return Promise.resolve(response.data);
        })
        .catch(error => {
            error = error.response;
            const message = (error && error.data && error.data.message) || error.statusText;
            return Promise.reject(message);
        });
}

function getHrMailSetting() {
    return Axios.get(`${config.BASE_API_URL}/hr-mail-setting`)
        .then(response => {
            return Promise.resolve(response.data);
        })
        .catch(error => {
            error = error.response;
            const message = (error && error.data && error.data.message) || error.statusText;
            return Promise.reject(message);
        });
}

function updateHrMailSetting(info) {
    return Axios.post(`${config.BASE_API_URL}/hr-mail-setting/update`, info)
        .then(response => {
            return Promise.resolve(response.data);
        })
        .catch(error => {
            error = error.response;
            const message = (error && error.data && error.data.message) || error.statusText;
            return Promise.reject(message);
        });
}

function hrMailSend(info) {
    return Axios.post(`${config.BASE_API_URL}/hr-mail-send`, info)
        .then(response => {
            return Promise.resolve(response.data);
        })
        .catch(error => {
            error = error.response;
            const message = (error && error.data && error.data.message) || error.statusText;
            return Promise.reject(message);
        });
}
function updateBreakWork (data) {
    return Axios.put(`${config.BASE_API_URL}/break-work/${data.id}`,data)
        .then(response => {
            return Promise.resolve(response.data);
        })
        .catch(error => {
            error = error.response;
            const message = (error && error.data && error.data.message) || error.statusText;
            return Promise.reject(message);
        });
}
