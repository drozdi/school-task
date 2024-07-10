import Vue from 'vue';
import Vuex from 'vuex'
import {alert} from "@/store/alert";
import {snackbar} from "@/store/snackbar";
import {authentication} from "@/store/authentication";

Vue.use(Vuex);

const store = new Vuex.Store({
    modules: {
        alert,
        snackbar,
        authentication
    }
});

export default store;