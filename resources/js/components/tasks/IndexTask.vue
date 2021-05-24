<template>
    <div>
        <div v-if="tasks.length !== 0">
            <div class="row h5 font-weight-bold">
                <div class="col-md-3">Project</div>
                <div class="col-md-3">Task</div>
                <div class="col-md-3" v-if="type !== 'notScheduled'">Schedule</div>
                <div class="col-md-3" v-if="type === 'archive'">Archived</div>
            </div>

            <div v-for="task in tasks" :key="task.id" class="row cursor-pointer p-1">
                <div class="col-md-3">{{task.project.name}}</div>
                <div class="col-md-3">
                <span :class="{
                    'text-secondary': task.importance === c.STATUS_NORMAL,
                    'text-primary': task.importance === c.STATUS_MEDIUM,
                    'text-danger': task.importance === c.STATUS_STRONG
                }">&bull;</span>
                    {{task.name}}
                </div>
                <div class="col-md-3" v-if="type !== 'notScheduled'">{{formatDate(task.schedule)}}</div>
                <div class="col-md-3" v-if="type === 'archive'">{{formatDate(task.deleted_at)}}</div>
            </div>
        </div>
    </div>
</template>

<script>
import route from "../../route";
import * as constants from '../../constants';
import moment from "moment";

export default {
    props: ['tasks', 'type'],
    computed: {
        c: function () {
            return constants;
        },
    },
    methods: {
        formatDate(date) {
            if (!date) {
                return '';
            }
            return moment(date).format('MMMM d, YYYY');
        },
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
