<template>
  <b-container fluid class="p-4">
    <b-row>
      <b-col>
        <b-alert :show="dismissCountDown"
                 dismissible
                 variant="danger"
                 @dismissed="dismissCountDown=0"
                 @dismiss-count-down="countDownChanged"
        >
          {{ error }}
        </b-alert>
      </b-col>
    </b-row>
    <b-row>
      <b-col>
        <router-link :to="{ path: $store.state.route.from.fullPath }">
          <b-button variant="outline-primary" class="mb-4">
            <b-icon icon="arrow-left"></b-icon>
            Back
          </b-button>
        </router-link>
      </b-col>
    </b-row>
    <b-row>
      <b-col>
        <b-table stacked :items="listProvider" :fields="fields" ref="item">
          <template v-slot:cell(id)="data">
            {{ data.item.id }}
          </template>
          <template v-slot:cell(status)="data">
                        <span v-if="data.item.status === 'published'"><b-icon icon="check-circle-fill"
                                                                              variant="success"></b-icon></span>
            <span v-if="data.item.status === 'draft'"><b-icon icon="dash-circle-fill"
                                                              variant="danger"></b-icon></span>
            {{ data.item.status }}
          </template>
          <template v-slot:cell(createAt)="data">
            {{ toLocaleString(data.item.createAt) }}
          </template>
          <template v-slot:cell(modifyAt)="data">
            {{ toLocaleString(data.item.modifyAt) }}
          </template>
          <template v-slot:cell(payload)="data">
            <json-view :data="data.item.payload"/>
          </template>
        </b-table>
      </b-col>
    </b-row>
  </b-container>
</template>
<script>
export default {
  data() {
    return {
      items: [],
      fields: [
        {key: 'id', label: 'Id'},
        {key: 'status', label: 'Status'},
        {key: 'createAt', label: 'Created At'},
        {key: 'modifyAt', label: 'Modified At'},
        {key: 'payload', label: 'Payload'},
      ],
      dismissSecs: 5,
      dismissCountDown: 0,
      showDismissibleAlert: false,
      error: '',
    }
  },
  methods: {
    countDownChanged(dismissCountDown) {
      this.dismissCountDown = dismissCountDown
    },
    showAlert(error) {
      this.dismissCountDown = this.dismissSecs
      this.error = error
    },
    toLocaleString: function (time) {
      return new Date(time).toLocaleString()
    },
    delay: function(ms) {
      return new Promise(res => setTimeout(res, ms))
    },
    listProvider() {
      const url = '/api/v1/document/' + this.$route.params.id

      let config = {}

      if (this.$store.getters.token) {
        config = { headers: {"Authorization" : "Bearer " + this.$store.getters.token }}
      }

      const promise = axios.get(url, config)
      return promise.then((data) => {
        const items = [data.data.document]
        return (items)
      }).catch(error => {
        if(error.response.status === 401) {
          this.$store.dispatch('changeUser', '')
          this.$store.dispatch('changeToken', '')
          this.$store.dispatch('changeUntil', '')
          this.$router.push({ name: 'list'}).catch(()=>{})
          this.$root.$emit('bv::refresh::table', 'list')
          this.$store.dispatch('changePage', 1)
        }
        if(error.response.status === 403) {
          this.$router.push({ name: 'list'}).catch(()=>{})
        }
        this.showAlert(error.response.data.message)
      })
    }
  }
}
</script>
