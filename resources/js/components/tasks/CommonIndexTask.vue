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
                <button v-if="type !== c.ARCHIVE" class="h5 btn btn-sm btn-outline-secondary" @click="archiveAllTasks">Archive Finished</button>
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
                         @click="showTask(task)"
                         :class="{'task-finished': isNeedStyleFinished(task), 'bg-light': compactStyle && index % 2 === 0}"
                    >
                        <div class="col-md-1 col-1"><i :class="statusIconClass(task.status)"></i></div>

                        <div :class="colTask">
                            <span :class="isNeedStyleFinished(task) ? 'task-finished' : importanceCss(task.importance)">&bull;</span> {{task.name}}
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
                        <button class="btn btn-outline-secondary btn-sm" @click="loadMore">Load More...</button>
                    </div>
                </div>
            </div>
        </div>

        <hr v-if="type === c.ARCHIVE">

        <archived-projects v-if="type === c.ARCHIVE" @showProject="showProject"></archived-projects>

        <!-- Toast -->
        <toast :body="infoBody" />

        <!-- Modals -->
        <button v-show="false" data-toggle="modal" data-target="#showTaskModal" ref="showTaskModalButton"></button>
        <div class="modal fade show mt-5 pb-5" id="showTaskModal" tabindex="-1" ref="showTaskModal">
            <show-task-modal
                :task="currentTask"
                @archived="taskArchived"
                @taskUpdated="taskUpdated"
                @deleteTaskModal="$refs.deleteTaskModalButton.click()"
                @editTaskModal="$refs.editTaskModalButton.click()"
                @showProject="showProject"
            ></show-task-modal>
        </div>

        <button v-show="false" data-toggle="modal" data-target="#deleteTaskModal" ref="deleteTaskModalButton"></button>
        <div class="modal fade show mt-5" id="deleteTaskModal" tabindex="-1">
            <delete-task-modal :task="currentTask" @deleted="taskDeleted" @cancel="$refs.showTaskModalButton.click()"></delete-task-modal>
        </div>

        <button v-show="false" data-toggle="modal" data-target="#editTaskModal" ref="editTaskModalButton"></button>
        <div class="modal fade show mt-5" id="editTaskModal" tabindex="-1">
            <edit-task-modal :task="currentTask" @updated="taskUpdated" @cancel="$refs.showTaskModalButton.click()"></edit-task-modal>
        </div>
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
    props: ['type'],
    data() {
        return {
            tasks: [],
            isDataLoaded: false,
            dataLoading: false,
            infoBody: '',
            hideFinished: false,
            currentTask: {},
            s: '',
            notTrashed: false,
        }
    },
    computed: {
        pageTitle: function () {
            let title = '';
            switch (this.type) {
                case this.c.INCOMING: title = 'Incoming tasks'; break;
                case this.c.TODAY: title = 'Today tasks'; break;
                case this.c.UPCOMING: title = 'Upcoming tasks'; break;
                case this.c.NOT_SCHEDULED: title = 'Not Scheduled tasks'; break;
                case this.c.ARCHIVE: title = 'Archived Tasks'; break;
            }
            return title;
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
            axios
                .delete(route('tasks.archive'), {
                    params: {
                        'type': this.type,
                    }
                })
                .then(response => {
                    let countArchived = response.data;

                    if (countArchived) {
                        this.infoBody = 'Archived: ' + countArchived;
                    } else {
                        this.infoBody = 'Nothing to Archive';
                    }

                    $('.toast').toast('show');
                    this.taskArchived();
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
        taskUpdated(task) {
            this.getTasks();
            this.$emit('taskUpdated');
            if (typeof task !== 'number') {
                this.currentTask = task;
                this.$refs.showTaskModalButton.click();
            }
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
        showTask(task) {
            this.currentTask = task;
            this.$refs.showTaskModalButton.click();
        },
        taskDeleted() {
            this.currentTask = {};
            this.getTasks();
            this.$emit('taskDeleted');
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
    }
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
.text-custom-secondary {
    color: #c8c8c8;
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
