<template>
    <div class="container">

        <div class="row">
            <div class="col-md-6 font-weight-bold h3">{{pageTitle}}
                <transition name="fade" appear><i v-if="!isDataLoaded || dataLoading" class="fas fa-spinner fa-spin h3"></i></transition>
            </div>
            <div class="col-md-6">
                <div class="row justify-content-between h5" v-if="type !== c.ARCHIVE">
                    <label><input type="checkbox" :checked="hideFinished" v-model="hideFinished" @click="switchHideFinished"> Hide Finished</label>
                    <button class="btn btn-sm btn-outline-secondary" @click="archiveAllTasks">Archive Finished</button>
                </div>
                <div v-else class="row justify-content-end pr-2">
                    <label>
                        <input class="form-control form-control-sm" v-model="s" type="text" placeholder="Search task or project...">
                    </label>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-md-12">
                <div class="row h5 font-weight-bold">
                    <div class="col-md-1">Status</div>
                    <div class="col-md-3">Task</div>
                    <div class="col-md-3">Project</div>
                    <div class="col-md-2" v-if="type !== c.NOT_SCHEDULED">Schedule</div>
                    <div class="col-md-2" v-if="type === c.ARCHIVE">Archived</div>
                </div>

                <div :class="type === c.ARCHIVE ? 'half mb-1' : 'full mb-4'">
                    <div v-if="isDataLoaded && tasks.length !== 0" v-for="task in tasks"
                         :key="task.id"
                         class="row cursor-pointer task pt-1 pb-1"
                         @click="showTask(task)"
                         :class="{'task-finished': isNeedStyleFinished(task)}"
                    >
                        <div class="col-md-1"><i :class="statusIconClass(task.status)"></i></div>

                        <div class="col-md-3">
                            <span :class="isNeedStyleFinished(task) ? 'task-finished' : importanceCss(task.importance)">&bull;</span> {{task.name}}
                        </div>

                        <div class="col-md-3" :class="{'text-custom-secondary': task.project.deleted_at}">
                            {{task.project.name}}
                        </div>

                        <div class="col-md-2" v-if="type !== c.NOT_SCHEDULED" :class="{'text-danger': isNeedStyleOverdue(task)}">
                            {{formatDate(task.schedule)}} <i v-if="isNeedStyleOverdue(task)" class="fas fa-exclamation"></i>
                        </div>

                        <div class="col-md-2" v-if="type === c.ARCHIVE">{{formatDate(task.deleted_at)}}</div>
                    </div>
                    <div class="m-0 pr-2 row justify-content-between pb-1" v-if="!isLastPage">
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
                @showProject="showProject"
            ></show-task-modal>
        </div>

        <button v-show="false" data-toggle="modal" data-target="#deleteTaskModal" ref="deleteTaskModalButton"></button>
        <div class="modal fade show mt-5" id="deleteTaskModal" tabindex="-1">
            <delete-task-modal :task="currentTask" @deleted="taskDeleted" @cancel="$refs.showTaskModalButton.click();"></delete-task-modal>
        </div>

    </div>
</template>

<script>
import route from "../../route";
import * as constants from "../../constants";
import moment from "moment";

export default {
    props: ['type'],
    data() {
        return {
            tasks: [],
            isDataLoaded: false,
            dataLoading: false,
            infoBody: '',
            hideFinished: false,
            currentTask: {},
            page: 0,
            isLastPage: false,
            lastPage: 0,
            s: '',
        }
    },
    computed: {
        c: function () {
            return constants;
        },
        pageTitle: function () {
            let title = '';
            switch (this.type) {
                case constants.INCOMING: title = 'Incoming tasks'; break;
                case constants.TODAY: title = 'Today tasks'; break;
                case constants.UPCOMING: title = 'Upcoming tasks'; break;
                case constants.NOT_SCHEDULED: title = 'Not Scheduled tasks'; break;
                case constants.ARCHIVE: title = 'Archived Tasks'; break;
            }
            return title;
        },
    },
    watch: {
        type: function() {
            this.isDataLoaded = false;
            this.getTasks();
        },
        s: function () {
            this.dataLoading = true;
            this.debouncedGetUsers();
        }
    },
    created() {
        this.debouncedGetUsers = _.debounce(this.getTasks, 500);
    },
    methods: {
        getTasks() {
            axios
                .get(route('tasks.index'), {
                    params: {
                        'type': this.type,
                        's': this.s,
                    }
                })
                .then(response => {
                    this.isLastPage = response.data.current_page === response.data.last_page;
                    this.page = 1;
                    this.lastPage = response.data.last_page;
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
                        'page': ++this.page,
                    }
                })
                .then(response => {
                    this.isLastPage = response.data.current_page === response.data.last_page;
                    this.tasks = this.tasks.concat(response.data.data);
                    this.dataLoading = false;
                })
                .catch(error => {
                    console.log(error);
                    this.dataLoading = false;
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
        taskUpdated() {
            this.getTasks();
            this.$emit('taskUpdated');
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
            return this.type !== constants.ARCHIVE
                && task.status !== constants.STATUS_FINISHED
                && this.isOverdue(task.schedule);
        },
        isNeedStyleFinished(task) {
            return this.type !== constants.ARCHIVE && task.status === constants.STATUS_FINISHED;
        },
        statusIconClass(status) {
            switch (status) {
                case constants.STATUS_NEW: return 'fas fa-external-link-alt';
                case constants.STATUS_PROGRESS: return 'fas fa-spinner';
                case constants.STATUS_FINISHED: return 'fas fa-check';
            }
            return '';
        },
        importanceCss(importance) {
            switch (importance) {
                case constants.STATUS_NORMAL: return 'text-secondary';
                case constants.STATUS_MEDIUM: return 'text-primary';
                case constants.STATUS_STRONG: return 'text-danger';
            }
            return '';
        },
    },
    mounted() {
        this.getTasks();
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
}
.full {
    height: calc(100vh - 165px);
    overflow-y: scroll;
    overflow-x: hidden;
}
::-webkit-scrollbar {
    width: 12px;
}
::-webkit-scrollbar-track {
    -webkit-box-shadow: inset 0 0 6px #edf3f3;
}
::-webkit-scrollbar-thumb {
    border-radius: 10px;
    -webkit-box-shadow: inset 0 0 6px #e9e9e9;
}
</style>
