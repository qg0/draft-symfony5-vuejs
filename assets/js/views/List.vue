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
        <b-pagination
            v-model="currentPage"
            :total-rows="totalRows"
            :per-page="perPage"
            align="fill"
            size="sm"
            class="my-0"
        ></b-pagination>
      </b-col>
    </b-row>
    <b-row>
      <b-col>
        <b-table striped hover responsive
                 id="list"
                 ref="list"
                 :items="listProvider"
                 :fields="fields"
                 :current-page="currentPage"
                 :per-page="perPage"
        >
          <template v-slot:cell(id)="data">
            <router-link :to="{ name: 'item', params: { id: data.item.id }}">{{ data.item.id }}</router-link>
          </template>
          <template v-slot:cell(status)="data">
            <span v-if="data.item.status === 'published'"><b-icon icon="check-circle-fill"
                                                                  variant="success"></b-icon></span>
            <span v-if="data.item.status === 'draft'"><b-icon icon="dash-circle-fill" variant="danger"></b-icon></span>
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
    <b-row>
      <b-col>
        <b-pagination
            v-model="currentPage"
            :total-rows="totalRows"
            :per-page="perPage"
            align="fill"
            size="sm"
            class="my-0"
        ></b-pagination>
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
      perPage: 20,
      currentPage: this.$store.getters.currentPage,
      totalRows: this.$store.getters.totalRows,
    }
  },
  watch: {
    '$store.getters.user': function() {
      this.currentPage = 1
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
    listProvider(ctx) {
      const params = '?page=' + ctx.currentPage + '&perPage=' + ctx.perPage
      const url = '/api/v1/document' + params

      let config = {}

      if (this.$store.getters.token) {
        config = {headers: {"Authorization": "Bearer " + this.$store.getters.token}}
      }

      const promise = axios.get(url, config)

      return promise.then((data) => {
        this.perPage = data.data.pagination.perPage
        this.totalRows = data.data.pagination.total * data.data.pagination.perPage

        this.$store.dispatch('changePage', data.data.pagination.page)
        this.$store.dispatch('changeTotalRows', this.totalRows)

        const items = data.data.document

        return (items)
      }).catch(error => {
        if(error.response.status === 401) {
          this.$store.dispatch('changeUser', '')
          this.$store.dispatch('changeToken', '')
          this.$store.dispatch('changeUntil', '')
          this.$root.$emit('bv::refresh::table', 'list')
          this.$store.dispatch('changePage', 1)
        }
        this.showAlert(error.response.data.message)
      })
    },
  },
}
</script>
