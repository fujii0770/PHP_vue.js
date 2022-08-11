import "babel-polyfill"
import Vue from 'vue'

import VueKonva from 'vue-konva'
import App from './App.vue'
import Axios from "axios";
import VueScrollTo  from 'vue-scrollto';
import VTooltip from 'v-tooltip'
import "isomorphic-fetch";

// Vue Router
import router from './router';
import VueGtag from "vue-gtag";

if(localStorage.getItem('GA_ID')) {
    Vue.use(VueGtag, {
      config: { id: localStorage.getItem('GA_ID') }
    }, router);
}

// Vue Lazyload
import VueLazyload from 'vue-lazyload';

Vue.use(VueLazyload, {
  preLoad: 1.3,
  error: 'dist/error.png',
  loading: 'dist/loading.gif',
  attempt: 1,
  listenEvents: [ 'scroll' ],
  lazyComponent: true
})

// Vue Clipboad
import VueClipboard from 'vue-clipboard2'

Vue.use(VueClipboard)


import VeeValidate from "vee-validate";

import VModal from 'vue-js-modal'

import Storage from 'vue-ls';
if (window.document.documentMode) {
  require('es6-promise').polyfill();
  require("jspolyfill-array.prototype.find");
  require("intersection-observer");
}

import VueForceNextTick from './library/vue-force-next-tick/index'
Vue.use(VueForceNextTick)

const options = {
  namespace: 'vuejs__', // key prefix
  name: 'ls', // name variable Vue.[ls] or this.[$ls],
  storage: 'local', // storage name session, local, memory
};

Vue.use(Storage, options);

// Vuesax Component Framework
import Vuesax from 'vuesax'
Vue.use(Vuesax)
Vue.use(VeeValidate)
Vue.use(VModal)

window.$ = require('jquery');
window.JQuery = require('jquery');

window.$.fn.textWidth = function(text, font) {
  if (!$.fn.textWidth.fakeEl) $.fn.textWidth.fakeEl = $('<span>').hide().appendTo(document.body);
  let html = text || this.val() || this.text() || this.attr('placeholder');
  html = html.replace(/[\r\n]/g, "<br />");
  $.fn.textWidth.fakeEl.html(html).css('font', font || this.css('font'));
  const width = $.fn.textWidth.fakeEl.width();
  //$.fn.textWidth.fakeEl.remove();
  return width;
};
const moment = require('moment')
require('moment/locale/ja')
Vue.use(require('vue-moment'), {
    moment
});

// Theme Configurations
import '../themeConfig.js'


// Globally Registered Components
import './globalComponents.js'


// Vuex Store
import store from './store/store'


// Vuejs - Vue wrapper for hammerjs
import { VueHammer } from 'vue2-hammer'
Vue.use(VueHammer)


// PrismJS
import 'prismjs'
import 'prismjs/themes/prism-tomorrow.css'

// Font Awesome
import '@fortawesome/fontawesome-free/css/all.css'
import '@fortawesome/fontawesome-free/js/all.js'




// Vue select css
// Note: In latest version you have to add it separately
// import 'vue-select/dist/vue-select.css';
import VueIziToast from "vue-izitoast";
import "izitoast/dist/css/iziToast.css";
Vue.use(VueIziToast);

import auth from './auth.js';
window.auth = auth;

Vue.config.productionTip = false;


Vue.use(VueKonva);
Vue.use(VueScrollTo);
Vue.use(VTooltip);
Vue.use(require('vue-cookie'));

Axios.interceptors.request.use(function(config) {
        if(!config.noToken){
          const token = config.tokenName ? localStorage.getItem(config.tokenName) : sessionStorage.getItem('token');
            const tokenPublic = localStorage.getItem('tokenPublic');
            if(token && !config.headers.Authorization) {
                config.headers.Authorization = `Bearer ${token}`;
            }
            let usingHash = config.data ? config.data.usingHash : false;

            if(!usingHash && config.url.includes('upload')) {
                try{
                    usingHash = config.data?config.data.get('usingHash'):false;
                }catch(e){

                }
            }

            if(tokenPublic && usingHash) {
                config.headers.Authorization = `Bearer ${tokenPublic}`;
            }
        }

        config.headers['X-Requested-With'] = 'X' +
          'MLHttpRequest';
        if(config.data && (!config.url.includes('upload') && !config.url.includes('uploadToCloud')
                            && !config.data.nowait && !config.nowait)) {
          store.dispatch('updateLoading', true);
        }
        return config;
    },
    function(err) {
      if(store.state.loading) {
        store.dispatch('updateLoading', false);
      }
      return Promise.reject(err);
    });

Axios.interceptors.response.use((response) => {
    if(store.state.loading) {
      store.dispatch('updateLoading', false);
    }
    return response
}, function(error) {
    if(store.state.loading) {
      store.dispatch('updateLoading', false);
    }
    return Promise.reject(error)
});


new Vue({
    router,
    store,
    render: h => h(App)
}).$mount('#app')
