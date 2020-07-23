<template>
  <div id="app">
    <b-navbar toggleable="lg" type="dark" variant="dark" sticky>
      <b-navbar-brand class="nav-link" to="/">
        <b-icon icon="building"></b-icon>
        Home
      </b-navbar-brand>
      <b-navbar-toggle target="nav-collapse"></b-navbar-toggle>
      <b-collapse id="nav-collapse" is-nav>
        <b-navbar-nav>
          <b-nav-item class="nav-link" to="/">
            <b-icon icon="list-ul"></b-icon>
            List
          </b-nav-item>
        </b-navbar-nav>
        <b-navbar-nav class="ml-auto">
          <b-nav-item-dropdown right>
            <template v-slot:button-content>
              <em>
                <b-icon icon="emoji-sunglasses"></b-icon>
                User ({{ $store.getters.user ? $store.getters.user : 'Anonymous'}})
              </em>
            </template>
            <b-dropdown-item class="nav-link" to="/login">
              <b-icon icon="shield-lock"></b-icon>
              Log in
            </b-dropdown-item>
            <b-dropdown-item @click="logout" v-if="$store.getters.user">
              <b-icon icon="power"></b-icon>
              Log out
            </b-dropdown-item>
          </b-nav-item-dropdown>
        </b-navbar-nav>
      </b-collapse>
    </b-navbar>
    <b-alert :show="dismissCountDown"
             dismissible
             variant="danger"
             @dismissed="dismissCountDown=0"
             @dismiss-count-down="countDownChanged"
    >
      {{ error }}
    </b-alert>
    <router-view/>
  </div>
</template>

<script>

import List from "./views/List";
import Item from "./views/Item";

export default {
  components: [List, Item],
  methods: {
    logout() {
      this.$store.dispatch('changeUser', '')
      this.$store.dispatch('changeToken', '')
      this.$store.dispatch('changeUntil', '')
      this.$root.$emit('bv::refresh::table', 'list')
      this.$router.push({ name: 'list'}).catch(()=>{})
    }
  }
}
</script>
