<template>
    <div class="container-xl">

        <div class="row">
            <div class="col-md-6 col-12 font-weight-bold h3">{{pageTitle}}
                <transition name="fade" appear><i v-if="!isDataLoaded || dataLoading" class="fas fa-spinner fa-spin h3"></i></transition>
            </div>
            <div class="col-md-3 col-6">
                <label v-if="type !== c.ARCHIVE" class="h5"><input type="checkbox" :checked="hideFinished" v-model="hideFinished" @click="switchHideFinished"> Hide Finished</label>
                <label v-else><input type="checkbox" v-model="notTrashed"> Not trashed projects</label>
            </div>
            <div class="col-md-3 col-6 text-right">
                <button v-if="type !== c.ARCHIVE" :disabled="isArchFinBtnDisabled" class="h5 btn btn-sm btn-outline-secondary" @click="archiveAllTasks">Archive Finished</button>
                <label v-else><input class="form-control form-control-sm" v-model="s" type="text" placeholder="Search task or project..."></label>
            </div>
        </div>

        <div class="row">
            <div class="col-md-12">
                <div class="row h5 font-weight-bold" v-if="mediumStyle">
                    <div class="col-md-1">Status</div>
                    <div :class="colTask">Task</div>
                    <div :class="colProject">Project</div>
                    <div class="col-md-2" v-if="type !== c.NOT_SCHEDULED">Schedule</div>
                    <div class="col-md-2" v-if="type === c.ARCHIVE">Archived</div>
                </div>
                <div v-else class="h5 font-weight-bold">
                    Status-Task-Project<span v-if="type !== c.NOT_SCHEDULED">-Schedule</span><span v-if="type === c.ARCHIVE">-Archived</span>
                </div>

                <div :class="halfFullScroll">
                    <div v-if="isDataLoaded && tasks.length !== 0" v-for="(task, index) in tasks"
                         :key="task.id"
                         class="row cursor-pointer task pt-1 pb-1"
                         @click="$emit('openTaskModal', c.SHOW_TASK, task, task.project)"
                         :class="{'task-finished': isNeedStyleFinished(task), 'bg-light': compactStyle && index % 2 === 0}"
                    >
                        <div class="col-md-1 col-1"><i :class="statusIconClass(task.status)"></i></div>

                        <div :class="colTask">
                            <span :class="isNeedStyleFinished(task) ? 'task-finished' : importanceCss(task.importance)">&bull;</span>
                            <span :class="{'text-custom-secondary': !(task.owner_id === currentUserId || task.user_id === currentUserId)}">{{task.name}}</span>
                        </div>

                        <div :class="colProjectFunc(task.project.deleted_at ? 'text-custom-secondary' : '')">
                            {{task.project.name}}
                        </div>

                        <div class="col-md-2 col-6" v-if="type !== c.NOT_SCHEDULED" :class="{'text-danger': isNeedStyleOverdue(task)}">
                            {{formatDate(task.schedule)}} <i v-if="isNeedStyleOverdue(task) && width > 781" class="fas fa-exclamation"></i>
                        </div>

                        <div class="col-md-2 col-6 text-right text-md-left" v-if="type === c.ARCHIVE">{{formatDate(task.deleted_at)}}</div>
                    </div>
                    <div class="m-0 pr-2 row justify-content-between pb-1" v-if="isDataLoaded && !isLastPage">
                        <span>Page {{page}} of {{lastPage}}</span>
                        <button class="btn btn-outline-secondary btn-sm" @click="loadMore" :disabled="isLoadBtnDisabled">Load More...</button>
                    </div>
                </div>
            </div>
        </div>

        <hr v-if="type === c.ARCHIVE">

        <archived-projects v-if="type === c.ARCHIVE" :currentUserId="currentUserId" @showProject="showProject"></archived-projects>

    </div>
</template>

<script>
import route from "../../route";
import moment from "moment";
import constantsMixin from "../mixins/constants.js";
import customWidthMixin from "../mixins/custom-width.js";
import paginationMixin from "../mixins/pagination";

export default {
    mixins: [
        constantsMixin,
        customWidthMixin,
        paginationMixin,
    ],
    props: ['type', 'currentUserId'],
    data() {
        return {
            tasks: [],
            isDataLoaded: false,
            dataLoading: false,
            hideFinished: false,
            s: '',
            notTrashed: false,
            isLoadBtnDisabled: false,
            isArchFinBtnDisabled: false,
        }
    },
    computed: {
        pageTitle: function () {
            return this.tasksPageTitle(this.type);
        },
        colTask: function () {
            switch (this.type) {
                case this.c.TODAY:
                case this.c.UPCOMING: return 'col-md-4 col-11';
                case this.c.NOT_SCHEDULED: return 'col-md-5 col-11';
                case this.c.ARCHIVE: return 'col-md-3 col-11';
            }
            return '';
        },
        colProject: function () {
            switch (this.type) {
                case this.c.TODAY:
                case this.c.UPCOMING: return 'col-md-5';
                case this.c.NOT_SCHEDULED: return 'col-md-6';
                case this.c.ARCHIVE: return 'col-md-4';
            }
            return '';
        },
        halfFullScroll: function () {
            let result = '';
            let scroll = '';
            if (this.type === this.c.ARCHIVE) {
                result = 'mb-1';
                scroll = 'half';
            } else {
                result = 'mb-4';
                scroll = 'full';
            }
            return result + (this.largeStyle ? ' ' + scroll : '');
        }
    },
    watch: {
        type: function() {
            this.isDataLoaded = false;
            this.getTasks();
        },
        s: function () {
            this.dataLoading = true;
            this.debouncedGetUsers();
        },
        notTrashed: function () {
            this.dataLoading = true;
            this.getTasks();
        },
    },
    created() {
        this.debouncedGetUsers = _.debounce(this.getTasks, 500);
    },
    methods: {
        colProjectFunc: function (result = '') {
            return result + ' ' + this.colProject;
        },
        getTasks() {
            axios
                .get(route('tasks.index'), {
                    params: {
                        'type': this.type,
                        's': this.s,
                        'notTrashed': this.notTrashed ? this.notTrashed : '',
                    }
                })
                .then(response => {
                    this.firstLoad(response.data);
                    this.tasks = response.data.data;
                    this.isDataLoaded = true;
                    this.dataLoading = false;
                })
                .catch(error => {
                    console.log(error);
                });
        },
        loadMore() {
            this.dataLoading = true;
            this.isLoadBtnDisabled = true;
            axios
                .get(route('tasks.index'), {
                    params: {
                        'type': this.type,
                        's': this.s,
                        'notTrashed': this.notTrashed ? this.notTrashed : '',
                        'page': ++this.page,
                    }
                })
                .then(response => {
                    this.loadedMore(response.data);
                    this.tasks = this.tasks.concat(response.data.data);
                    this.dataLoading = false;
                    this.isLoadBtnDisabled = false;
                })
                .catch(error => {
                    console.log(error);
                });
        },
        switchHideFinished() {
            axios
                .post(route('users.update'), {
                    'hide_finished': !this.hideFinished,
                })
                .then(response => {
                    this.getTasks();
                })
                .catch(error => {
                    console.log(error);
                });
        },
        archiveAllTasks() {
            this.isArchFinBtnDisabled = true;
            axios
                .delete(route('tasks.archive'), {
                    params: {
                        'type': this.type,
                    }
                })
                .then(response => {
                    let countArchived = response.data;
                    let infoBody = countArchived ? 'Archived: ' + countArchived : 'Nothing to Archive';
                    this.$emit('showToast', infoBody)
                    this.taskArchived();
                    this.isArchFinBtnDisabled = false;
                })
                .catch(error => {
                    console.log(error);
                });
        },
        showProject(id) {
            this.$emit('showProject', id);
        },
        taskArchived() {
            this.getTasks();
            this.$emit('taskArchived');
        },
        getHideFinished() {
            axios
                .get(route('users.show'))
                .then(response => {
                    this.hideFinished = response.data.hide_finished;
                })
                .catch(error => {
                    console.log(error);
                });
        },
        formatDate(date) {
            return date ? moment(date).format('MMMM DD, YYYY') : '';
        },
        isOverdue(schedule) {
            return moment(schedule).isBefore(new Date, 'day');
        },
        isNeedStyleOverdue(task) {
            return this.type !== this.c.ARCHIVE
                && task.status !== this.c.STATUS_FINISHED
                && this.isOverdue(task.schedule);
        },
        isNeedStyleFinished(task) {
            return this.type !== this.c.ARCHIVE && task.status === this.c.STATUS_FINISHED;
        },
    },
    mounted() {
        if (this.type) {
            this.getTasks();
        }
        this.getHideFinished();
    },
    components: {
        'archived-projects': () => import('../projects/ArchivedProjects.vue')
    },
}
</script>

<style>
.fade-enter-active, .fade-leave-active {
    transition: opacity .5s;
}
.fade-enter, .fade-leave-to {
    opacity: 0;
}
.cursor-pointer {
    cursor: pointer;
}
.task:hover {
    background-color: #e0eeee;
    border-radius: 5px;
}
.task-finished {
    color: #dedede;
}
.half {
    height: calc(50vh - 135px);
    overflow-y: scroll;
    overflow-x: hidden;
    margin-right: -12px;
}
.full {
    height: calc(100vh - 165px);
    overflow-y: scroll;
    overflow-x: hidden;
    margin-right: -12px;
}
</style>
