<template>
  <v-app>
    <app-header></app-header>
    <v-main fill-height>
      <v-container fluid>
        <v-alert tag="pre" v-if="alert.message" dense :outlined="alert.outlined" :type="alert.type">
          {{ alert.message }}
        </v-alert>
        <router-view></router-view>
      </v-container>
      <v-snackbar v-model="snackbar.show" :timeout="snackbar.timeout" :outlined="snackbar.outlined">
        {{ snackbar.message }}
        <v-btn color="blue" text @click="snackbar.show = false">
          Close
        </v-btn>
      </v-snackbar>
    </v-main>
  </v-app>
</template>

<script>
import AppHeader from "@/components/AppHeader";

export default {
  name: 'App',
  data: () => ({}),
  computed: {
    alert () {
      return this.$store.state.alert
    },
    snackbar () {
      return this.$store.state.snackbar
    }
  },
  watch:{
    $route () {
      this.$store.dispatch('alert/clear');
      this.$store.dispatch('snackbar/clear');
    }
  },
  components: {
    AppHeader
  },
  mounted() {
    const { dispatch } = this.$store;
    dispatch('authentication/check');
  }
}
</script>


<style lang="scss">
  .ck-content,
  .ck-source-editing-area textarea {
    height: 300px;
  }
  .current-time {
    height: 2px;
    background-color: red;
    position: absolute;
    left: -1px;
    right: 0;
    pointer-events: none;

    &.first::before {
      content: '';
      position: absolute;
      background-color: red;
      width: 12px;
      height: 12px;
      border-radius: 50%;
      margin-top: -5px;
      margin-left: -6.5px;
    }
  }
</style>