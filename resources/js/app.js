require('./bootstrap');

window.Vue = require('vue').default;

import Vuelidate from 'vuelidate';
Vue.use(Vuelidate);

// import route from "../route";
// console.log(route('home'));

Vue.component('welcome', require('./components/Welcome.vue').default);
Vue.component('home', require('./components/Home.vue').default);
Vue.component('toast', require('./components/Toast.vue').default);


// PROJECTS
Vue.component('show-project', require('./components/projects/ShowProject.vue').default);

// modals
Vue.component('create-project-modal', require('./components/modals/projects/CreateProject.vue').default);
Vue.component('edit-project-modal', require('./components/modals/projects/EditProject.vue').default);
Vue.component('delete-project-modal', require('./components/modals/projects/DeleteProject.vue').default);
Vue.component('archive-project-modal', require('./components/modals/projects/ArchiveProject.vue').default);


// TASKS
Vue.component('list-item-task', require('./components/tasks/ListItemTask.vue').default);
Vue.component('common-index-task', require('./components/tasks/CommonIndexTask.vue').default);

// modals
Vue.component('create-task-modal', require('./components/modals/tasks/CreateTask.vue').default);
Vue.component('show-task-modal', require('./components/modals/tasks/ShowTask.vue').default);
Vue.component('delete-task-modal', require('./components/modals/tasks/DeleteTask.vue').default);


const app = new Vue({
    el: '#app',
});
