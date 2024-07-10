//import "@/plugins/store";
import axios from "@/plugins/axios";
import router from "@/plugins/router"

export const authentication = {
    namespaced: true,
    state: {
        is_authenticated: false,
        user: null,
    },
    actions: {
        login ({ dispatch, commit }, { login, password }) {
            const formData = new FormData();
            formData.append('f', "login")
            formData.append('user[login]', login)
            formData.append('user[password]', password)
            return axios.post('/login.php', formData).then(function (res) {
                commit('loginSuccess', res);
            }, function (error) {
                commit('loginFailure', error);
                dispatch('alert/error', error);
            });
        },
        check ({ commit, dispatch }) {
            const formData = new FormData();
            formData.append('f', "check-is-authenticated");
            return axios.post('/login.php', formData).then(function (res) {
                if (res) {
                    commit('loginSuccess', res);
                } else {
                    commit('loginFailure');
                }
            }, function (error) {
                commit('loginFailure', error);
                dispatch('alert/error', error);
            });
        },
        logout ({ commit, dispatch }) {
            const formData = new FormData();
            formData.append('f', "logout");
            return axios.post('/login.php', formData).then(function () {
                commit('logout');
            }, function (error) {
                commit('loginFailure', error);
                dispatch('alert/error', error);
            });

        }
    },
    mutations: {
        loginSuccess (state, user) {
            state.is_authenticated = true;
            state.user = user;
            router.push(router.history.current.query.redirect||"/");
        },
        loginFailure (state) {
            state.is_authenticated = false;
            state.user = null;
        },
        logout (state) {
            state.is_authenticated = false;
            state.user = null;
            router.push("/");
        }
    },
    getters: {
        isAuthenticated: state => {
            if (state.is_authenticated === 'true' || state.is_authenticated === true) {
                return true
            }
            return false
        },
        isRoot: state => {
            if (state.user.is_root === 'true' || state.user.is_root === true) {
                return true
            }
            return false
        },
        isClass: state => {
            if (state.user.is_class === 'true' || state.user.is_class === true) {
                return true
            }
            return false
        }
    }

}