import config from "../app.config";
import Axios from "axios";

export var bizcardService;
export default (bizcardService = {
    getListBizcard,
    getBizcardById,
    getBizcardByIdPublic,
    registerBizcard,
    deleteBizcard,
    updateBizcard,
    getByLinkPageURL,
    getMyBizcard,
    getURL,
    uploadZip,
    deleteZip,
    uploadCsv,
    multipleRegister,
});

function getListBizcard(info) {
    return Axios.get(`${config.BASE_API_URL}/bizcard?filter=${info.filter}&offset=${info.offset}&limit=${info.limit}`, info)
    .then(response => {
        return Promise.resolve(response.data);
    })
    .catch(error => {
        error = error.response;
        const message = (error && error.data && error.data.message) || error.statusText;
        return Promise.reject(message);
    });
}

function getBizcardById(info) {
    return Axios.get(`${config.BASE_API_URL}/bizcard/${info.bizcard_id}?env_flg=${info.env_flg}&server_flg=${info.server_flg}&edition_flg=${info.edition_flg}`)
    .then(response => {
        return Promise.resolve(response.data);
    })
    .catch(error => {
        error = error.response;
        const message = (error && error.data && error.data.message) || error.statusText;
        return Promise.reject(message);
    });
}

function getBizcardByIdPublic(info) {
    return Axios.get(`${config.BASE_API_URL}/bizcard/showPublic/${info.bizcard_id}?env_flg=${info.env_flg}&server_flg=${info.server_flg}&edition_flg=${info.edition_flg}`)
    .then(response => {
        return Promise.resolve(response.data);
    })
    .catch(error => {
        error = error.response;
        const message = (error && error.data && error.data.message) || error.statusText;
        return Promise.reject(message);
    });
}

function registerBizcard(info) {
    return Axios.post(`${config.BASE_API_URL}/bizcard`, info)
    .then(response => {
        return Promise.resolve(response.data);
    })
    .catch(error => {
        error = error.response;
        const message = (error && error.data && error.data.message) || error.statusText;
        return Promise.reject(message);
    })
}

function deleteBizcard(info) {
    return Promise.all(
        info.bizcardIds.map(bizcardId => {
            return Axios.delete(`${config.BASE_API_URL}/bizcard/${bizcardId}?my_bizcard=${info.myBizcard}`);
        })
    ).then(responses => {
        return Promise.resolve(info.bizcardIds.length);
    }).catch(error => {
        error = error.response;
        const message = (error && error.data && error.data.message) || error.statusText;
        return Promise.reject(message);
    })
}

function updateBizcard(info) {
    return Axios.put(`${config.BASE_API_URL}/bizcard/${info.bizcard_id}`, info.param)
    .then(response => {
        return Promise.resolve(response.data);
    })
    .catch(error => {
        error = error.response;
        const message = (error && error.data && error.data.message) || error.statusText;
        return Promise.reject(message);
    })
}

function getByLinkPageURL(info) {
    return Axios.get(`${config.BASE_API_URL}/bizcard/showByLinkPageURL?link_page_url=${info.link_page_url}`)
    .then(response => {
        return Promise.resolve(response.data);
    })
    .catch(error => {
        error = error.response;
        const message = (error && error.data && error.data.message) || error.statusText;
        return Promise.reject(message);
    });
}

function getMyBizcard() {
    return Axios.get(`${config.BASE_API_URL}/bizcard/getMyBizcard`)
    .then(response => {
        return Promise.resolve(response.data);
    })
    .catch(error => {
        error = error.response;
        const message = (error && error.data && error.data.message) || error.statusText;
        return Promise.reject(message);
    });
}

function getURL(id) {
    return Axios.get(`${config.BASE_API_URL}/bizcard/getLinkPageURL/${id}`)
    .then(response => {
        return Promise.resolve(response.data);
    })
    .catch(error => {
        error = error.response;
        const message = (error && error.data && error.data.message) || error.statusText;
        return Promise.reject(message);
    })
}
function uploadZip(file) {
    const params = new FormData();
    params.append('file', file);
    return Axios.post(`${config.BASE_API_URL}/multipleBizcard/acceptZip`, params, {headers: {'content-type': 'multipart/form-data'}})
    .then(response => {
        return Promise.resolve(response.data);
    })
    .catch(error => {
        error = error.response;
        const message = (error && error.data && error.data.message) || error.statusText;
        return Promise.reject(message);
    })
}
function deleteZip(zipUploadTime) {
    return Axios.post(`${config.BASE_API_URL}/multipleBizcard/deleteZipContents/${zipUploadTime}`)
    .then(response => {
        return Promise.resolve(response.data);
    })
    .catch(error => {
        error = error.response;
        const message = (error && error.data && error.data.message) || error.statusText;
        return Promise.reject(message);
    })
}
function uploadCsv(file) {
    const params = new FormData();
    params.append('file', file);
    return Axios.post(`${config.BASE_API_URL}/multipleBizcard/acceptCsv`, params, {headers: {'content-type': 'multipart/form-data'}})
    .then(response => {
        return Promise.resolve(response.data);
    })
    .catch(error => {
        error = error.response;
        const message = (error && error.data && error.data.message) || error.statusText;
        return Promise.reject(message);
    })
}

function multipleRegister(info) {
    return Axios.post(`${config.BASE_API_URL}/multipleBizcard/register`, info)
    .then(response => {
        return Promise.resolve(response.data);
    })
    .catch(error => {
        error = error.response;
        const message = (error && error.data && error.data.message) || error.statusText;
        return Promise.reject(message);
    })
}