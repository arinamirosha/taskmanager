<template>
    <div>
        <div v-if="isDataLoaded">
            <div v-if="tasks.length !== 0">
                <div class="row h5 font-weight-bold">
                    <div class="col-md-1">#</div>
                    <div class="col-md-3">Task</div>
                    <div class="col-md-3">Project</div>
                    <div class="col-md-2" v-if="type !== 'notScheduled'">Schedule</div>
                    <div class="col-md-2" v-if="type === 'archive'">Archived</div>
                </div>

                <div v-for="(task, index) in tasks" :key="task.id" class="row cursor-pointer p-1" @click="showTask(task)"  :class="{'text-danger': isOverdue(task.schedule)}">
                    <div class="col-md-1">{{++index}}</div>
                    <div class="col-md-3">
                        <span :class="{
                            'text-secondary': task.importance === c.STATUS_NORMAL,
                            'text-primary': task.importance === c.STATUS_MEDIUM,
                            'text-danger': task.importance === c.STATUS_STRONG
                        }">&bull;</span>
                        {{task.name}}
                    </div>
                    <div class="col-md-3">{{task.project.name}}</div>
                    <div class="col-md-2" v-if="type !== 'notScheduled'">{{formatDate(task.schedule)}}</div>
                    <div class="col-md-2" v-if="type === 'archive'">{{formatDate(task.deleted_at)}}</div>
                </div>
            </div>
            <div v-else>No Tasks</div>
        </div>
        <div v-else>Loading...</div>

        <!-- Modals-->
        <button v-show="false" data-toggle="modal" data-target="#showTaskModal" ref="showTaskModalButton"></button>
        <div class="modal fade show mt-5 pb-5" id="showTaskModal" tabindex="-1" ref="showTaskModal">
            <show-task-modal :task="currentTask" :deletable="type !== 'archive'" @deleteTaskModal="$refs.deleteTaskModalButton.click()"></show-task-modal>
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
            return moment(date).format('MMMM DD, YYYY');
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
</style>
