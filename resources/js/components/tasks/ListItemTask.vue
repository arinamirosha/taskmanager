<template>
    <div>
        <a data-toggle="modal" data-target="#showTaskModal" class="stretch-a cursor-pointer p-2" @click.self="$emit('showTask', task)">
            <span :class="importanceCss(task.importance)">&bull;</span>
            {{task.name}}
            <span v-if="task.schedule" :data-title="titleSchedule(task.schedule)" class="schedule">
                <i v-if="task.schedule" class="far fa-clock pl-1 text-secondary"></i>
            </span>
            <span v-if="task.status === 6" @click.stop="archive(task.id)" class="btn btn-sm btn-outline-secondary mr-3" style="float: right">
                &#10003;
            </span>
        </a>
    </div>
</template>

<script>
import route from "../../route";
import * as c from '../../constants';
import moment from "moment";

export default {
    props: ['task'],
    methods: {
        importanceCss(importance) {
            switch (importance) {
                case c.STATUS_NORMAL: return 'text-secondary';
                case c.STATUS_MEDIUM: return 'text-primary';
                case c.STATUS_STRONG: return 'text-danger';
            }
            return '';
        },
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
</style>
