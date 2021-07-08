<template>
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Edit Task</h5>
            </div>
            <form @submit.prevent="updateTask">
                <div class="modal-body">
                    <div class="form-group">
                        <label for="name-edit">Name</label> <span class="text-danger">*</span>
                        <input class="form-control" id="name-edit" v-model="name" :class="{'is-invalid': this.$v.name.$error}">
                    </div>
                    <div class="form-group">
                        <label for="details-edit">Details</label>
                        <textarea class="form-control" id="details-edit" v-model="details" :class="{'is-invalid': this.$v.details.$error}"></textarea>
                    </div>
                    <div class="form-group">
                        <label for="schedule-edit">Schedule</label>
                        <input type="date" :min="minSchedule" class="form-control" id="schedule-edit" v-model="schedule" :class="{'is-invalid': this.$v.schedule.$error}">
                    </div>
                    <div class="form-group">
                        <label for="importance-edit">Importance</label>
                        <select class="form-control" id="importance-edit" v-model="importance" :class="{'is-invalid': this.$v.importance.$error}">
                            <option v-for="importance in statuses"
                                    :value="importance"
                                    :class="importanceCss(importance)"
                            >{{importanceText(importance)}}</option>
                        </select>
                    </div>
                    <div class="form-group" v-if="task.owner_id === currentUserId">
                        <label for="project-id">Project</label>
                        <select class="form-control" id="project-id" v-model="projectId" @change="changePerformer">
                            <option v-for="project in projects" :value="project.id">{{project.name}}</option>
                        </select>
                    </div>
                    <div class="form-group" v-if="acceptedUsers.length">
                        <label for="performer-edit">Performer</label>
                        <select class="form-control" id="performer-edit" v-model="performerId" :disabled="isDisabled">
                            <option :value="project.user.id">{{project.user.name}}<span v-if="project.user.surname">{{project.user.surname}}</span></option>
                            <option v-for="acceptedUser in acceptedUsers" :value="acceptedUser.id">{{acceptedUser.name}}<span v-if="acceptedUser.surname"> {{acceptedUser.surname}}</span></option>
                        </select>
                    </div>
                </div>

                <div class="modal-footer" v-if="projects.length > 0">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal" ref="cancel" @click="reset">Cancel</button>
                    <button type="submit" class="btn btn-primary">Update</button>
                </div>
            </form>
        </div>
    </div>
</template>

<script>
import { required, maxLength, minValue } from 'vuelidate/lib/validators';
import route from "../../../route";
import constantsMixin from "../../mixins/constants.js";
import moment from "moment";

export default {
    mixins: [
        constantsMixin,
    ],
    props: ['task', 'project', 'currentUserId'],
    data() {
        return {
            name: '',
            details: '',
            schedule: null,
            today: moment().format("YYYY-MM-DD"),
            importance: 0,
            statuses: [],
            projectId: 0,
            performerId: 0,
            projects: [],
            isDisabled: false,
        }
    },
    watch: {
        task: function() {
            this.setOriginValues();
            this.getProjects();
        },
    },
    computed: {
        acceptedUsers() {
            return this.project ? this.project.shared_users.filter(a => a.pivot.accepted) : [];
        },
        minSchedule: function () {
            return this.task.schedule > this.today ? this.today : this.task.schedule;
        },
    },
    validations: {
        name: {
            required,
            maxLength: maxLength(50),
        },
        details: {
            maxLength: maxLength(1500),
        },
        schedule: {
            minValue (value) {
                return (value === null)
                    || (value >= this.minSchedule)
                    || (value === '')
            },
        },
        importance: {
            importance (value) { return this.statuses.includes(parseInt(value)) },
        },
    },
    methods: {
        getProjects() {
            axios
                .get(route('projects.index'))
                .then(response => {
                    this.projects = response.data.projects;
                })
                .catch(error => {
                    console.log(error);
                });
        },
        updateTask(e) {
            this.$v.$touch();
            if (!this.$v.$invalid) {
                axios
                    .post(route('tasks.update', this.task.id), {
                        'name': this.name,
                        'details': this.details,
                        'schedule': this.schedule,
                        'importance': this.importance,
                        'project_id': this.projectId,
                        'user_id': this.performerId,
                    })
                    .then(response => {
                        this.$refs.cancel.click();
                        this.$emit('updated', response.data);
                    })
                    .catch(error => {
                        console.log(error)
                    });
            }
        },
        reset() {
            this.setOriginValues();
            this.$v.$reset();
            this.$emit('cancel');
        },
        setOriginValues() {
            this.name = this.task.name;
            this.details = this.task.details;
            this.schedule = this.task.schedule;
            this.importance = this.task.importance;
            this.projectId = this.task.project_id;
            this.performerId = this.task.user_id;
            this.isDisabled = false;
        },
        changePerformer() {
            if (this.projectId !== this.task.project_id) {
                this.performerId = this.currentUserId;
                this.isDisabled = true;
            } else {
                this.performerId = this.task.user_id;
                this.isDisabled = false;
            }
        },
    },
    mounted() {
        this.importance = this.c.STATUS_NORMAL;
        this.statuses = [this.c.STATUS_NORMAL, this.c.STATUS_MEDIUM, this.c.STATUS_STRONG];
    },
}
</script>
