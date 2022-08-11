import config from "../app.config";
import Axios from "axios";
import store from '../store/store';

export var templateRouteService;
export default (templateRouteService = {
    getList,
    getTemplateRouteList,
});

function getList(info) {
    return Axios.get(`${config.BASE_API_URL}${store.state.home.usingPublicHash ? '/public': ''}/templates-route?templateRouteName=${encodeURIComponent(info.templateRouteName)}&template_route_flg=${info.template_route_flg}`,{data: {usingHash: store.state.home.usingPublicHash}})
        .then(response => {
            return Promise.resolve(response.data);
        })
        .catch(error => {
            error = error.response;
            const message = (error && error.data && error.data.message) || error.statusText;
            return Promise.reject(message);
        });
}

function getTemplateRouteList(info) {
    return Axios.get(`${config.BASE_API_URL}${store.state.home.usingPublicHash ? '/public': ''}/route-list?templateRouteName=${encodeURIComponent(info.templateRouteName)}&routeId=${info.routeId}&page=${info.page}&limit=${info.limit}&orderBy=${info.orderBy}&orderDir=${info.orderDir}`,{data: {usingHash: store.state.home.usingPublicHash}})
        .then(response => {
            return Promise.resolve(response.data);
        })
        .catch(error => {
            error = error.response;
            const message = (error && error.data && error.data.message) || error.statusText;
            return Promise.reject(message);
        });
}