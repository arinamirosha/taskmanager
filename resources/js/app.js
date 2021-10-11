require('./bootstrap');

window.Vue = require('vue').default;

import Vue from 'vue';
import Vuelidate from 'vuelidate';

Vue.use(Vuelidate);

const app = new Vue({
    el: '#app',
    components: {
        'home': () => import('./components/Home.vue'),
        'welcome': () => import('./components/Welcome.vue'),
        'user-profile': () => import('./components/users/UserProfile.vue'),
    },
});
