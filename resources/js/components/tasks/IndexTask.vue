<template>
    <div>
        <div class="row h5 font-weight-bold">
            <div class="col-md-1">#</div>
            <div class="col-md-3">Task</div>
            <div class="col-md-3">Project</div>
            <div class="col-md-2" v-if="type !== c.NOT_SCHEDULED">Schedule</div>
            <div class="col-md-2" v-if="type === c.ARCHIVE">Archived</div>
        </div>

        <transition-group name="fade" appear>
            <div v-if="isDataLoaded && tasks.length !== 0" v-for="(task, index) in tasks"
                 :key="task.id"
                 class="row cursor-pointer pt-1 pb-1"
                 @click="showTask(task)"
                 :class="{'task-finished': isNeedStyleFinished(task)}"
            >
                <div class="col-md-1">{{++index}} <i v-if="type !== c.ARCHIVE" :class="statusIconClass(task.status)"></i></div>

                <div class="col-md-3">
                    <span :class="isNeedStyleFinished(task) ? 'task-finished' : importanceCss(task.importance)">&bull;</span> {{task.name}}
                </div>

                <div class="col-md-3">{{task.project.name}}</div>

                <div class="col-md-2" v-if="type !== c.NOT_SCHEDULED" :class="{'text-danger': isNeedStyleOverdue(task)}">
                    {{formatDate(task.schedule)}} <i v-if="isNeedStyleOverdue(task)" class="fas fa-exclamation"></i>
                </div>

                <div class="col-md-2" v-if="type === c.ARCHIVE">{{formatDate(task.deleted_at)}}</div>
            </div>
        </transition-group>

        <!-- Modals-->
        <button v-show="false" data-toggle="modal" data-target="#showTaskModal" ref="showTaskModalButton"></button>
        <div class="modal fade show mt-5 pb-5" id="showTaskModal" tabindex="-1" ref="showTaskModal">
            <show-task-modal
                :task="currentTask"
                :deletable="type !== c.ARCHIVE"
                @archived="$emit('archived')"
                @statusUpdated="$emit('statusUpdated')"
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
import * as constants from '../../constants';
import moment from "moment";

export default {
    props: ['tasks', 'type', 'isDataLoaded'],
    data() {
        return {
            currentTask: {},
        }
    },
    computed: {
        c: function () {
            return constants;
        },
    },
    methods: {
        formatDate(date) {
            return date ? moment(date).format('MMMM DD, YYYY') : '';
        },
        showTask(task) {
            this.currentTask = task;
            this.$refs.showTaskModalButton.click();
        },
        taskDeleted() {
            this.currentTask = 0;
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
        showProject(id) {
            this.$emit('showProject', id);
        }
    },
}
</script>

<style scoped>
.cursor-pointer{
    cursor: pointer;
}
.cursor-pointer:hover{
    background-color: #e0eeee;
    border-radius: 5px;
}
.task-finished {
    color: #dedede;
}
.fade-enter-active, .fade-leave-active {
    transition: opacity .5s;
}
.fade-enter, .fade-leave-to /* .fade-leave-active до версии 2.1.8 */ {
    opacity: 0;
}
</style>
