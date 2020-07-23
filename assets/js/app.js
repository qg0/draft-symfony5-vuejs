require('../css/app.scss');
var $ = require('jquery');
require('bootstrap');


import Vue from 'vue'
import App from './App.vue'
import store from './store'
import router from './router'

import { BootstrapVue, IconsPlugin, AlertPlugin, FormInputPlugin } from 'bootstrap-vue'
Vue.use(BootstrapVue)
Vue.use(IconsPlugin)
Vue.use(AlertPlugin)
Vue.use(FormInputPlugin)

import JSONView from 'vue-json-component'
Vue.use(JSONView)

import VuexRouterSync from 'vuex-router-sync';
VuexRouterSync.sync(store, router);

import axios from 'axios'
window.axios = axios

Vue.config.productionTip = false

new Vue({
  router,
  store,
  render: function (h) { return h(App) }
}).$mount('#app')
