import config from "../app.config";
import Axios from "axios";
import store from '../store/store';

export var contactsService;
export default (contactsService = {
    getListContact, getContact,
    updateContact, addNewContact, deleteContact
});
 
function getListContact(info) {
    return Axios.get(`${config.BASE_API_URL}${store.state.home.usingPublicHash ? '/public/getContactsByHash': '/contacts'}`,{params: info, data: {usingHash: store.state.home.usingPublicHash}})
        .then(response => {
            return Promise.resolve(response.data);
        })
        .catch(error => {
            error = error.response;
            const message = (error && error.data && error.data.message) || error.statusText;
            return Promise.reject(message);
        });
}

function getContact(id) {
    return Axios.get(`${config.BASE_API_URL}/contacts/${id}`,{data: {}})
        .then(response => {
            return Promise.resolve(response.data);
        })
        .catch(error => {
            error = error.response;
            const message = (error && error.data && error.data.message) || error.statusText;
            return Promise.reject(message);
        });
}

function updateContact(info) {
    return Axios.put(`${config.BASE_API_URL}/contacts/${info.id}`,info)
        .then(response => {
            return Promise.resolve(response.data);
        })
        .catch(error => {
            error = error.response;
            const message = (error && error.data && error.data.message) || error.statusText;
            return Promise.reject(message);
        });
}

function addNewContact(info) {
    return Axios.post(`${config.BASE_API_URL}/contacts`,info)
        .then(response => {
            return Promise.resolve(response.data);
        })
        .catch(error => {
            error = error.response;
            const message = (error && error.data && error.data.message) || error.statusText;
            return Promise.reject(message);
        });
}

function deleteContact(id) {
    return Axios.delete(`${config.BASE_API_URL}/contacts/${id}`,)
        .then(response => {
            return Promise.resolve(response.data);
        })
        .catch(error => {
            error = error.response;
            const message = (error && error.data && error.data.message) || error.statusText;
            return Promise.reject(message);
        });
}
