<template>
  <div>
    <v-navigation-drawer app class="hidden-md-and-up" v-model="drawer" absolute temporary>
      <nav-menu v-model="headerItems"></nav-menu>
      <v-divider></v-divider>
      <nav-menu v-model="leftItems"></nav-menu>
    </v-navigation-drawer>
    <v-toolbar color="primary" dark fixed>
      <v-app-bar-nav-icon class="hidden-md-and-up" @click.stop="drawer = !drawer"></v-app-bar-nav-icon>
      <v-toolbar-title class="hidden-sm-and-down">Школа</v-toolbar-title>
      <v-spacer></v-spacer>
      <header-menu class="hidden-sm-and-down" v-model="headerItems"></header-menu>
      <v-toolbar-items>
        <template v-if="isAuth">
          <v-btn text :to="{name: 'main-profile'}">
            ЛК
          </v-btn>
          <v-btn text @click="logout">
            <v-icon>mdi-logout</v-icon>
            Выход
          </v-btn>
        </template>
        <v-btn v-else text :to="{name: 'main-login'}">
          <v-icon>mdi-login</v-icon>
          Вход
        </v-btn>
      </v-toolbar-items>
    </v-toolbar>
  </div>
</template>

<script>
import NavMenu from "./menu/nav-menu";
import HeaderMenu from "./menu/header-menu";

export default {
  components: {
    NavMenu,
    HeaderMenu
  },
  name: "AppHeader",
  data: () => ({
    drawer: false,
    headerItems: [],
    leftItems: []
  }),
  mounted() {
    this.loadLeft();
    this.loadHeader();
  },
  computed: {
    isAuth() {
      return this.$store.getters['authentication/isAuthenticated'];
    }
  },
  methods: {
    loadLeft() {
      this.$http.get('/menu/left.php').then((function (data) {
        this.leftItems = data;
      }).bind(this));
    },
    loadHeader() {
      this.$http.get('/menu/header.php').then((function (data) {
        this.headerItems = data;
      }).bind(this));
    },
    logout() {
      const {dispatch} = this.$store;
      dispatch('authentication/logout');
    }
  },
  watch: {
    $route() {
      this.loadLeft();
      this.loadHeader();
    }
  }
}
</script>

<style scoped>

</style>