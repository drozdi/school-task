export const alert = {
    namespaced: true,
    state: {
        type: null,
        message: null,
        outlined: false,
    },
    actions: {
        success ({ commit }, message) {
            commit('success', message);
        },
        info ({ commit }, message) {
            commit('info', message);
        },
        warning ({ commit }, message) {
            commit('warning', message);
        },
        error ({ commit }, message) {
            commit('error', message);
        },
        outlined ({ commit }, val) {
            commit('outlined', val)
        },
        clear ({ commit }) {
            commit('clear');
        }
    },
    mutations: {
        success (state, message) {
            state.type = 'success';
            state.message = message;
        },
        info (state, message) {
            state.type = 'danger';
            state.message = message;
        },
        warning (state, message) {
            state.type = 'danger';
            state.message = message;
        },
        error (state, message) {
            state.type = 'error';
            state.message = message;
        },
        outlined (state, val) {
            state.outlined = val;
        },
        clear (state) {
            state.type = null;
            state.message = null;
            state.outlined = false;
        }
    }
}