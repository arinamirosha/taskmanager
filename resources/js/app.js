require('./bootstrap');

window.Vue = require('vue').default;

import Vue from 'vue';
import Vuelidate from 'vuelidate';
Vue.use(Vuelidate);

import { BootstrapVue, IconsPlugin } from 'bootstrap-vue'
// import 'bootstrap/dist/css/bootstrap.css'
import 'bootstrap-vue/dist/bootstrap-vue.css'
Vue.use(BootstrapVue)
Vue.use(IconsPlugin)

const app = new Vue({
    el: '#app',
    components: {
        'home': () => import('./components/Home.vue'),
        'welcome': () => import('./components/Welcome.vue'),
        'user-profile': () => import('./components/users/UserProfile.vue'),
    },
});
