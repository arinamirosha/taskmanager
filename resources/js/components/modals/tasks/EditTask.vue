<template>
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Edit Task</h5>
            </div>
            <form @submit.prevent="updateTask">
                <div class="modal-body">
                    <div class="form-group">
                        <label for="name1">Name</label> <span class="text-danger">*</span>
                        <input class="form-control" id="name1" v-model="name" :class="{'is-invalid': this.$v.name.$error}">
                    </div>
                    <div class="form-group">
                        <label for="details">Details</label>
                        <textarea class="form-control" id="details" v-model="details" :class="{'is-invalid': this.$v.details.$error}"></textarea>
                    </div>
                    <div class="form-group">
                        <label for="schedule">Schedule</label>
                        <input type="date" :min="task.schedule" class="form-control" id="schedule" v-model="schedule" :class="{'is-invalid': this.$v.schedule.$error}">
                    </div>
                    <div class="form-group">
                        <label for="importance">Importance</label>
                        <select class="form-control" id="importance" v-model="importance" :class="{'is-invalid': this.$v.importance.$error}">
                            <option v-for="importance in statuses"
                                    :value="importance"
                                    :class="importanceCss(importance)"
                            >{{importanceText(importance)}}</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="project-id">Project</label>
                        <select class="form-control" id="project-id" v-model="projectId">
                            <option v-for="project in projects" :value="project.id">{{project.name}}</option>
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
import * as c from '../../../constants';
import * as constants from "../../../constants";

export default {
    props: ['task'],
    data() {
        return {
            name: '',
            details: '',
            schedule: null,
            importance: c.STATUS_NORMAL,
            statuses: [c.STATUS_NORMAL, c.STATUS_MEDIUM, c.STATUS_STRONG],
            projectId: 0,
            projects: [],
        }
    },
    watch: {
        task: function() {
            this.setOriginValues();
            this.getProjects();
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
            minValue (value) { return (value === null) || (value >= this.task.schedule) || (value === '') },
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
        },
    },
}
</script>
