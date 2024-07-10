export const snackbar = {
    namespaced: true,
    state: {
        timeout: 5000,
        message: null,
        outlined: false,
        show: false
    },
    actions: {
        message ({ commit }, message) {
            commit('message', message);
        },
        outlined ({ commit }, val) {
            commit('outlined', val)
        },
        clear ({ commit }) {
            commit('clear');
        }
    },
    mutations: {
        message (state, message) {
            state.message = message;
            state.show = true;
        },
        outlined (state, val) {
            state.outlined = val;
        },
        clear (state) {
            state.message = null;
            state.outlined = false;
            state.show = false;
        }
    }
}