<template>
    <div>
        <a data-toggle="modal" data-target="#showTaskModal" class="stretch-a cursor-pointer p-2" @click.self="$emit('showTask', task)">
            <span :class="importanceCss(task.importance)">&bull;</span>
            {{task.name}}
            <span v-if="task.schedule" :data-title="titleSchedule(task.schedule)" class="schedule">
                <i v-if="task.schedule" class="far fa-clock pl-1 text-secondary"></i>
            </span>
            <span v-if="task.status === c.STATUS_FINISHED" @click.stop="archive(task.id)" class="mr-2 float-right"><i class="fas fa-archive text-secondary"></i></span>
        </a>
    </div>
</template>

<script>
import route from "../../route";
import constantsMixin from "../mixins/constants.js";
import moment from "moment";

export default {
    mixins: [
        constantsMixin,
    ],
    props: ['task'],
    methods: {
        titleSchedule(schedule) {
            return moment(new Date(schedule)).format('MMMM DD, YYYY');
        },
        archive(id) {
            axios
                .delete(route('tasks.destroy', id))
                .then(response => {
                    this.$emit('archived', id);
                })
                .catch(error => {
                    console.log(error);
                });
        },
    },
}
</script>

<style scoped>
.cursor-pointer{
    cursor: pointer;
}
.schedule:hover::after {
    position: absolute;
    content: attr(data-title);
    margin-left: 5px;
    z-index: 1;
    background: #f8f9fa;
    text-align: center;
    padding: 5px 10px;
    border: 1px solid #212529;
    border-radius: 5px;
}
.stretch-a {
    width: 100%;
    display: block;
    text-decoration: none;
    color: #212529;
}
.cursor-pointer{
    cursor: pointer;
}
.cursor-pointer:hover{
    background-color: #e0eeee;
    border-radius: 5px;
}
.fa-archive:hover {
    color: #212529;
}
</style>
