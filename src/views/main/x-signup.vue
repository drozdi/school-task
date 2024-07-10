<template>
  <v-container fluid fill-height>
    <v-layout align-center justify-center>
      <v-flex xs12 sm8 md4>
        <v-card class="elevation-12">
          <v-toolbar dark color="primary">
            <v-toolbar-title>Вход</v-toolbar-title>
          </v-toolbar>
          <v-card-text>
            <v-form>
              <v-text-field prepend-icon="mdi-account" v-model="login" name="login" label="Логин" type="text"></v-text-field>
              <v-text-field prepend-icon="mdi-lock" v-model="password" name="password" label="Пароль" type="password"></v-text-field>
            </v-form>
          </v-card-text>
          <v-card-actions>
            <v-spacer></v-spacer>
            <v-btn color="primary" @click="send">Войти</v-btn>
          </v-card-actions>
        </v-card>
      </v-flex>
    </v-layout>
  </v-container>
</template>

<script>
export default {
  data: () => ({
    login: '',
    password: ''
  }),
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