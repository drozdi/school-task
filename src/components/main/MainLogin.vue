<template>
  <v-form ref="form">
    <v-card class="mx-auto" max-width="500">
      <v-card-title>
        Вход
      </v-card-title>
      <v-card-text>
        <v-text-field
            v-model="login"
            name="user[login]"
            label="Login"
            placeholder="Login"
            required
        ></v-text-field>
        <v-text-field
            v-model="password"
            name="user[password]"
            label="Password"
            placeholder="Password"
            :type="'password'"
            required
        ></v-text-field>
      </v-card-text>
      <v-card-actions>
        <v-btn @click.prevent.stop="send">
          Вход
        </v-btn>
      </v-card-actions>
    </v-card>
  </v-form>
</template>

<script>
export default {
  name: "MainLogin",
  data: () => ({
    login: '',
    password: ''
  }),
  computed: {
    loggedIn () {
      return this.$store.state.authentication.loggedIn;
    }
  },
  methods: {
    send () {
      const { login, password } = this;
      const { dispatch } = this.$store;
      if (login && password) {
        dispatch('authentication/login', { login, password });
      }
    }
  }
}
</script>

<style scoped>

</style>