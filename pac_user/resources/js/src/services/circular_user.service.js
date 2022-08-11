import config from "../app.config";
import Axios from "axios";
import store from '../store/store';

export var circularUserService;
export default (circularUserService = {
  adds,
  remove,
  clear,
  update,
  sendMailViewed,
  sendBack,
  addChild
});

function adds(data) {
    return Axios.post(`${config.BASE_API_URL}/circulars/${store.state.home.circular.id}/users`, data)
        .then(response => {
            return Promise.resolve(response.data);
        })
        .catch(error => {
            error = error.response;
            const message = (error && error.data && error.data.message) || error.statusText;
            return Promise.reject(message);
        });
}

function addChild(data) {
  data.usingHash = store.state.home.usingPublicHash;
  return Axios.post(`${config.BASE_API_URL}${store.state.home.usingPublicHash ? '/public': ''}/circulars/${store.state.home.circular.id}/users/addChild`, data)
    .then(response => {
      return Promise.resolve(response.data);
    })
    .catch(error => {
      error = error.response;
      const message = (error && error.data && error.data.message) || error.statusText;
      return Promise.reject(message);
    });
}

function update(circular_user) {
  circular_user.usingHash = store.state.home.usingPublicHash;
  return Axios.put(`${config.BASE_API_URL}${store.state.home.usingPublicHash ? '/public': ''}/circulars/${store.state.home.circular.id}/users/${circular_user.id}`,circular_user)
    .then(response => {
      return Promise.resolve(response.data);
    })
    .catch(error => {
      error = error.response;
      const message = (error && error.data && error.data.message) || error.statusText;
      return Promise.reject(message);
    });
}

function remove(id) {
  return Axios.delete(`${config.BASE_API_URL}${store.state.home.usingPublicHash ? '/public': ''}/circulars/${store.state.home.circular.id}/users/${id}`, {data: {usingHash: store.state.home.usingPublicHash}})
    .then(response => {
      return Promise.resolve(response.data);
    })
    .catch(error => {
      error = error.response;
      const message = (error && error.data && error.data.message) || error.statusText;
      return Promise.reject(message);
    });
}

function clear(circular_id) {
  return Axios.delete(`${config.BASE_API_URL}/circulars/${circular_id}/users/clear`, {data: {nowait: true}})
    .then(response => {
      return Promise.resolve(response.data);
    })
    .catch(error => {
      error = error.response;
      const message = (error && error.data && error.data.message) || error.statusText;
      return Promise.reject(message);
    });
}

function sendMailViewed(data) {
  data.usingHash = store.state.home.usingPublicHash;
  return Axios.post(`${config.BASE_API_URL}${store.state.home.usingPublicHash ? '/public': ''}/circulars/${store.state.home.circular.id}/users/sendViewedMail`, data)
    .then(response => {
      return Promise.resolve(response.data);
    })
    .catch(error => {
      error = error.response;
      const message = (error && error.data && error.data.message) || error.statusText;
      return Promise.reject(message);
    });
}

function sendBack(data) {
  data.usingHash = store.state.home.usingPublicHash;
  return Axios.post(`${config.BASE_API_URL}${store.state.home.usingPublicHash ? '/public': ''}/circulars/${store.state.home.circular.id}/users/sendBack`, data)
    .then(response => {
      return Promise.resolve(response.data);
    })
    .catch(error => {
      error = error.response;
      const message = (error && error.data && error.data.message) || error.statusText;
      return Promise.reject(message);
    });
}