<template>
    <div>
        <ul class="nav nav-tabs pl-2" id="myTab" role="tablist">
            <li class="nav-item" role="presentation">
                <button class="nav-link active" id="home-tab" data-toggle="tab" data-target="#info" type="button" role="tab" aria-controls="home" aria-selected="true">Information</button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link" id="profile-tab" data-toggle="tab" data-target="#comments" type="button" role="tab" aria-controls="profile" aria-selected="false">Comments ({{ task.comments_count }})</button>
            </li>
            <li class="p-2">{{ tDate }}</li>
        </ul>

        <div class="modal-body">
            <div class="tab-content" id="myTabContent">
                <div class="tab-pane fade show active" id="info" role="tabpanel" aria-labelledby="home-tab">
                    <div v-if="task.owner" class="mb-2">
                        <div class="font-weight-bold">Owner</div>
                        <div>{{ task.owner.name }} {{ task.owner.surname }}</div>
                    </div>
                    <div v-if="task.user" class="mb-2">
                        <div class="font-weight-bold">Performer</div>
                        <div>{{ task.user.name }} {{ task.user.surname }}</div>
                    </div>
                    <div v-if="task.details" class="mb-2">
                        <div class="font-weight-bold">Details</div>
                        <div>{{ task.details }}</div>
                    </div>
                    <div class="mb-2">
                        <div class="font-weight-bold">Project</div>
                        <div class="cursor-pointer project-name" @click="showProject(project.id)">
                            {{ project.name }}
                            <span v-if="project.deleted_at" class="text-info">ARCHIVED</span>
                        </div>
                    </div>
                    <div v-if="task.schedule" class="mb-2">
                        <div class="font-weight-bold">Schedule</div>
                        <div :class="{'text-danger': isOverdue()}">
                            {{ task.schedule }} <i v-if="isOverdue()" class="fas fa-exclamation"></i>
                        </div>
                    </div>
                    <div class="mb-2">
                        <div class="font-weight-bold">Importance</div>
                        <div><span :class="importanceCss(task.importance)">&bull;</span>
                            {{ importanceText(task.importance) }}
                        </div>
                    </div>
                    <div class="mb-2">
                        <div class="font-weight-bold">Status</div>
                        <div>{{ statusText(task.status) }}</div>
                    </div>
                </div>

                <div class="tab-pane fade" id="comments" role="tabpanel" aria-labelledby="profile-tab">
                    <comments
                        :taskId="task.id"
                        :isArchive="task.deleted_at !== null"
                        :currentUserId="currentUserId"
                        @newComment="++task.comments_count"
                        @commentDeleted="--task.comments_count"
                    ></comments>
                </div>

            </div>
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
    props: ['task', 'project', 'currentUserId'],
    computed: {
        tDate: function () {
            return moment(new Date(this.task.created_at)).format('DD.MM.YY HH:mm');
        },
    },
    methods: {
        changeStatus(newStatus) {
            this.task.status = newStatus;
            this.$emit('waitTaskAction');
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
            this.$emit('waitTaskAction');
            axios
                .delete(route('tasks.destroy', this.task.id))
                .then(response => {
                    this.$emit('taskUpdated', this.task.id);
                    this.$nextTick(() => {
                        this.$bvModal.hide('common-modal')
                    })
                })
                .catch(error => {
                    console.log(error);
                });
        },
        restore() {
            this.$emit('waitTaskAction');
            axios
                .post(route('tasks.restore', this.task.id))
                .then(response => {
                    this.$emit('taskUpdated', this.task.id);
                    this.$nextTick(() => {
                        this.$bvModal.hide('common-modal')
                    })
                })
                .catch(error => {
                    console.log(error);
                });
        },
        showProject(id) {
            this.$nextTick(() => {
                this.$bvModal.hide('common-modal')
            })
            this.$emit('showProject', id)
        },
        isOverdue() {
            if (this.task.deleted_at || this.task.status === this.c.STATUS_FINISHED) {
                return false;
            }
            return moment(this.task.schedule).isBefore(new Date, 'day');
        },
    },
    components: {
        'comments': () => import('../../comments/Comments.vue')
    }
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
