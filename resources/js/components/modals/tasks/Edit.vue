<template>
    <form>
        <b-form-group label="Name" label-for="name-input" :invalid-feedback="'1-50 symbols, you have ' + name.length">
            <b-form-input id="name-input" v-model="name" :state="$v.$dirty && $v.name.$error ? false : null" required></b-form-input>
        </b-form-group>

        <b-form-group label="Details" label-for="details-input" :invalid-feedback="'0-1500 symbols, you have ' + details.length">
            <b-form-textarea id="details-input" v-model="details" :state="$v.$dirty && $v.details.$error ? false : null" rows="2" max-rows="6"></b-form-textarea>
        </b-form-group>

        <b-form-group label="Schedule" label-for="schedule-input">
            <b-form-datepicker id="schedule-input" v-model="schedule" :min="minSchedule" locale="en" :state="$v.$dirty && $v.schedule.$error ? false : null"></b-form-datepicker>
        </b-form-group>

        <b-form-group label="Importance" label-for="importance-input">
            <b-form-select id="importance-input" v-model="importance" :options="statuses"></b-form-select>
        </b-form-group>

        <b-form-group label="Performer" label-for="performer-input" v-if="acceptedUsers.length">
            <b-form-select id="performer-input" v-model="performerId" :options="performers" :disabled="isDisabled"></b-form-select>
        </b-form-group>

        <b-form-group label="Project" label-for="project-input" v-if="task.owner_id === currentUserId">
            <b-form-select id="project-input" v-model="projectId" :options="projects" @change="changePerformer"></b-form-select>
        </b-form-group>
    </form>
</template>

<script>
import { required, maxLength, minValue } from 'vuelidate/lib/validators';
import route from "../../../route";
import constantsMixin from "../../mixins/constants.js";
import moment from "moment";
import Vue from "vue";

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
            performers: [],
            isDisabled: false,
        }
    },
    computed: {
        acceptedUsers() {
            return this.project ? this.project.shared_users.filter(a => a.pivot.accepted) : [];
        },
        minSchedule: function () {
            return (!this.task.schedule) || (this.task.schedule > this.today) ? this.today : this.task.schedule;
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
    },
    methods: {
        getProjects() {
            axios
                .get(route('projects.index'))
                .then(response => {
                    let projects = response.data.projects;
                    this.projects = projects.map((p) => {
                        return {value: p.id, text: p.name}
                    });
                })
                .catch(error => {
                    console.log(error);
                });
        },
        handleSubmit(e) {
            this.$v.$touch();
            if (this.$v.$invalid) {
                return
            }
            this.$emit('wait');
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
                    this.$emit('taskUpdated');
                    this.$nextTick(() => {
                        this.$bvModal.hide('common-modal')
                    })
                })
                .catch(error => {
                    console.log(error)
                });
        },
        setOriginValues() {
            this.name = this.task.name;
            this.details = this.task.details ?? '';
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
        this.setOriginValues();
        this.getProjects();

        let statuses = [this.c.STATUS_NORMAL, this.c.STATUS_MEDIUM, this.c.STATUS_STRONG];
        this.statuses = statuses.map((s) => {
            return {value: s, text: this.importanceText(s)}
        });

        let projectUsers = [this.project.user, ...this.acceptedUsers];
        this.performers = projectUsers.map((u) => {
            return {value: u.id, text: u.name + (u.surname ? ' ' + u.surname : '')}
        });
    },
}
</script>
