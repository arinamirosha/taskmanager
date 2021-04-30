<template>
    <div>
        <a data-toggle="modal" data-target="#showTaskModal" class="stretch-a cursor-pointer p-2" @click.self="$emit('showTask', task)">
            <span :class="importanceCss(task.importance)">&bull;</span>
            {{task.name}}
            <span v-if="task.schedule" :data-title="titleSchedule(task.schedule)" class="schedule">
                <svg v-if="task.schedule" xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-clock" viewBox="0 0 16 16">
                    <path d="M8 3.5a.5.5 0 0 0-1 0V9a.5.5 0 0 0 .252.434l3.5 2a.5.5 0 0 0 .496-.868L8 8.71V3.5z"/>
                    <path d="M8 16A8 8 0 1 0 8 0a8 8 0 0 0 0 16zm7-8A7 7 0 1 1 1 8a7 7 0 0 1 14 0z"/>
                </svg>
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
            return moment(new Date(schedule)).format('MMMM d, YYYY');
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
