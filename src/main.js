import Vue from 'vue';
import DatetimePicker from 'vuetify-datetime-picker'
import App from './App.vue';
import CKEditor from '@ckeditor/ckeditor5-vue2';
import router from '@/plugins/router';
import vuetify from "@/plugins/vuetify";
import store from  "@/plugins/store";
import axios from "@/plugins/axios";

axios.interceptors.response.use(function (response) {
  // Any status code that lie within the range of 2xx cause this function to trigger
  // Do something with response data
  return response.data;
}, function (error) {
  if (error.response.status !== 401) {
    store.dispatch('alert/error', error.response.data);
    console.log(error);
  }
  return Promise.reject(error);
});//*/

Vue.use(DatetimePicker);
Vue.use(CKEditor);
Vue.config.productionTip = false;

new Vue({
  vuetify,
  store,
  render: h => h(App),
  el: '#app',
  router
})
