<template>
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header justify-content-between">
                <h5 class="modal-title">{{task.name}}</h5>
                <a v-if="!task.deleted_at" class="cursor-pointer text-muted" @click="editTaskModal">
                    <i class="far fa-edit"></i>
                </a>
            </div>
            <form @submit.prevent="updateTask">
                <div class="modal-body">
                    <div v-if="task.details" class="mb-2">
                        <div class="font-weight-bold">Details</div>
                        <div>{{task.details}}</div>
                    </div>
                    <div class="mb-2">
                        <div class="font-weight-bold">Project</div>
                        <div v-if="task.project" class="cursor-pointer project-name" @click="showProject(task.project.id)">
                            {{task.project.name}}
                            <span v-if="task.project.deleted_at" class="text-info">ARCHIVED</span>
                        </div>
                        <div v-else-if="project" class="cursor-pointer project-name" @click="showProject(project.id)">
                            {{project.name}}
                        </div>
                    </div>
                    <div v-if="task.schedule" class="mb-2">
                        <div class="font-weight-bold">Schedule</div>
                        <div :class="{'text-danger': isOverdue()}">
                            {{task.schedule}} <i v-if="isOverdue()" class="fas fa-exclamation"></i>
                        </div>
                    </div>
                    <div class="mb-2">
                        <div class="font-weight-bold">Importance</div>
                        <div><span :class="importanceCss(task.importance)">&bull;</span> {{importanceText(task.importance)}}</div>
                    </div>
                    <div class="mb-2">
                        <div class="font-weight-bold">Status</div>
                        <div>{{statusText(task.status)}}</div>
                    </div>
                </div>

                <div class="modal-footer justify-content-between">
                    <button type="button" class="btn btn-outline-danger" @click="deleteTaskModal">Delete</button>

                    <button type="button" class="btn btn-primary" v-if="!task.deleted_at && task.status !== c.STATUS_FINISHED">
                        <span v-if="task.status === c.STATUS_NEW" @click="changeStatus(c.STATUS_PROGRESS)">Start</span>
                        <span v-else-if="task.status === c.STATUS_PROGRESS" @click="changeStatus(c.STATUS_FINISHED)">Finish</span>
                    </button>

                    <button type="button" class="btn btn-primary"
                            v-if="!task.deleted_at && (task.status === c.STATUS_FINISHED || (task.project && task.project.deleted_at) )"
                    >
                        <span @click="archive" data-dismiss="modal">Archive</span>
                    </button>

                    <button type="button" class="btn btn-primary" v-if="task.deleted_at && !task.project.deleted_at">
                        <span @click="restore" data-dismiss="modal">Restore</span>
                    </button>

                    <button type="button" class="btn btn-secondary" data-dismiss="modal" ref="closeShowTask">Close</button>
                </div>
            </form>
        </div>
    </div>
</template>

<script>
import constantsMixin from "../../mixins/constants.js";
import route from "../../../route";
import moment from "moment";

export default {
    mixins: [
        constantsMixin,
    ],
    props: ['task', 'project'],
    methods: {
        deleteTaskModal() {
            this.$refs.closeShowTask.click();
            this.$emit('deleteTaskModal');
        },
        editTaskModal() {
            this.$refs.closeShowTask.click();
            this.$emit('editTaskModal');
        },
        changeStatus(newStatus) {
            this.task.status = newStatus;
            axios
                .post(route('tasks.update', this.task.id), {'status': newStatus})
                .then(response => {
                    this.$emit('taskUpdated', this.task.id);
                })
                .catch(error => {
                    console.log(error);
                });
        },
        archive() {
            axios
                .delete(route('tasks.destroy', this.task.id))
                .then(response => {
                    this.$emit('archived', this.task.id);
                })
                .catch(error => {
                    console.log(error);
                });
        },
        restore() {
            axios
                .post(route('tasks.restore', this.task.id))
                .then(response => {
                    this.$emit('taskUpdated', this.task.id);
                })
                .catch(error => {
                    console.log(error);
                });
        },
        showProject(id) {
            this.$refs.closeShowTask.click();
            this.$emit('showProject', id)
        },
        isOverdue() {
            if (this.task.deleted_at || this.task.status === this.c.STATUS_FINISHED) {
                return false;
            }
            return moment(this.task.schedule).isBefore(new Date, 'day');
        },
    },
}
</script>

<style scoped>
.cursor-pointer {
    cursor: pointer;
}
.project-name:hover {
    color: #bcbcbc;
}
.fa-edit:hover {
    color: #212529;
}
</style>
