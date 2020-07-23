import Vue from 'vue';

import Vuex from 'vuex';
Vue.use(Vuex);

import VuexPersist from 'vuex-persist';

import List from "./modules/List";
import User from "./modules/User";

const vuexStorage = new VuexPersist({
  // You can change this explicitly use
  // either window.localStorage  or window.sessionStorage
  // However we are going to make use of localForage
  storageFirst: false,
  session: {},
})

export default new Vuex.Store({
  plugins: [vuexStorage.plugin],
  modules: {
    List,
    User
  }
})