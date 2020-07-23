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
    <b-form-row class="justify-content-md-center">
      <b-col sm="4">
        <b-form @submit="onSubmit" v-if="show">
          <b-form-group
              id="input-group-1"
              label="Login:"
              label-for="input-login"
              description="'admin' or 'user'"
          >
            <b-form-input
                id="input-login"
                v-model="form.login"
                required
                placeholder="user"
            ></b-form-input>
          </b-form-group>
          <div class="text-right">
            <b-button type="submit" variant="primary">Submit</b-button>
          </div>
        </b-form>
      </b-col>
    </b-form-row>
  </b-container>
</template>

<script>
export default {
  data() {
    return {
      form: {
        login: '',
      },
      show: true,
      dismissSecs: 5,
      dismissCountDown: 0,
      showDismissibleAlert: false,
      error: '',
      user: '',
      token: '',
      until: '',
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
    onSubmit(evt) {
      evt.preventDefault()
      this.dismissCountDown = 0

      const promise = axios.post('/api/v1/login', this.form)

      return promise.then((data) => {
        this.form.login = ''

        this.user  = data.data.user
        this.token = data.data.token
        this.until = data.data.until

        this.$store.dispatch('changeUser', this.user)
        this.$store.dispatch('changeToken', this.token)
        this.$store.dispatch('changeUntil', this.until)

        this.$router.push({ name: 'list'}).catch(()=>{})
        this.$store.dispatch('changePage', 1)
      }).catch(error => {
        return this.showAlert(error.response.data.message);
      })
    },
  }
}
</script>
