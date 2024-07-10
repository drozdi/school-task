import Vue from 'vue';
import VuetifyConfirm from 'vuetify-confirm'
import Vuetify from 'vuetify/lib/framework';
import ru from 'vuetify/src/locale/ru.ts'

const vuetify = new Vuetify({
    lang: {
        locales: { ru },
        current: 'ru',
    },
});

Vue.use(Vuetify);
Vue.use(VuetifyConfirm, { vuetify })
export default vuetify;
