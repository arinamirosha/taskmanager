<template>
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">{{task.name}}</h5>
            </div>
            <form @submit.prevent="updateTask">
                <div class="modal-body">
                    <div v-if="task.details" class="mb-2">
                        <div class="font-weight-bold">Details</div>
                        <div>{{task.details}}</div>
                    </div>
                    <div v-if="task.schedule" class="mb-2">
                        <div class="font-weight-bold">Schedule</div>
                        <div>{{task.schedule}}</div>
                    </div>
                    <div class="mb-2">
                        <div class="font-weight-bold">Importance</div>
                        <div><span :class="importanceCss(task.importance)">&bull;</span> {{importanceText(task.importance)}}</div>
                    </div>
                </div>

                <div class="modal-footer justify-content-between">
                    <button type="button" class="btn btn-outline-danger" @click="deleteTaskModal">Delete</button>
                    <button type="button" class="btn btn-secondary" data-dismiss="modal" ref="closeShowTask">Close</button>
                </div>
            </form>
        </div>
    </div>
</template>

<script>
import * as c from '../../../constants';

export default {
    props: ['task'],
    methods: {
        importanceText(importance) {
            switch (importance) {
                case c.STATUS_NORMAL: return 'Normal';
                case c.STATUS_MEDIUM: return 'Medium';
                case c.STATUS_STRONG: return 'Strong';
            }
        },
        importanceCss(importance) {
            switch (importance) {
                case c.STATUS_NORMAL: return 'text-secondary';
                case c.STATUS_MEDIUM: return 'text-primary';
                case c.STATUS_STRONG: return 'text-danger';
            }
            return '';
        },
        deleteTaskModal() {
            this.$refs.closeShowTask.click();
            this.$emit('deleteTaskModal');
        },
    },
}
</script>

<style scoped>
.cursor-pointer{
    cursor: pointer;
}
</style>
