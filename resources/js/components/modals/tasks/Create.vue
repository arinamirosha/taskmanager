<template>
    <form>
        <b-form-group label="Name" label-for="name-input" :invalid-feedback="'1-50 symbols, you have ' + name.length">
            <b-form-input id="name-input" v-model="name" :state="$v.$dirty && $v.name.$error ? false : null" required></b-form-input>
        </b-form-group>

        <b-form-group label="Details" label-for="details-input" :invalid-feedback="'0-1500 symbols, you have ' + details.length">
            <b-form-textarea id="details-input" v-model="details" :state="$v.$dirty && $v.details.$error ? false : null" rows="2" max-rows="6"></b-form-textarea>
        </b-form-group>

        <b-form-group label="Schedule" label-for="schedule-input">
            <b-form-datepicker id="schedule-input" v-model="schedule" :min="today" locale="en" :state="$v.$dirty && $v.schedule.$error ? false : null"></b-form-datepicker>
        </b-form-group>

        <b-form-group label="Importance" label-for="importance-input">
            <b-form-select id="importance-input" v-model="importance" :options="statuses"></b-form-select>
        </b-form-group>

        <b-form-group label="Performer" label-for="performer-input" v-if="acceptedUsers.length">
            <b-form-select id="performer-input" v-model="performerId" :options="performers"></b-form-select>
        </b-form-group>
    </form>
</template>

<script>
import { required, maxLength, minValue, numeric } from 'vuelidate/lib/validators';
import route from "../../../route";
import moment from "moment";
import constantsMixin from "../../mixins/constants";
import Vue from 'vue';

export default {
    mixins: [
        constantsMixin,
    ],
    props: ['project', 'currentUserId'],
    computed: {
        acceptedUsers() {
            return this.project.shared_users.filter(a => a.pivot.accepted);
        },
    },
    data() {
        return {
            name: '',
            details: '',
            schedule: null,
            today: moment().format("YYYY-MM-DD"),
            importance: 0,
            statuses: [],
            performerId: this.currentUserId,
            performers: [],
        }
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
            minValue(value) {
                return (value === null) || (value >= this.today) || (value === '')
            },
        },
        importance: {
            importance(value) {
                return this.statuses.some(s => s.value === parseInt(value))
            },
        },
        performerId: {
            performerId(value) {
                return this.performers.some(p => p.value === parseInt(value))
            },
        },
    },
    methods: {
        handleSubmit(e) {
            this.$v.$touch();
            if (this.$v.$invalid) {
                return;
            }
            this.$emit('wait');
            axios
                .post(route('tasks.store'), {
                    'project_id': this.project.id,
                    'user_id': this.performerId,
                    'name': this.name,
                    'details': this.details,
                    'schedule': this.schedule,
                    'importance': this.importance,
                })
                .then(response => {
                    this.$emit('projectUpdated', this.project.id);
                    this.$nextTick(() => {
                        this.$bvModal.hide('common-modal')
                    })
                })
                .catch(error => {
                    console.log(error)
                });
        },
    },
    mounted() {
        this.importance = this.c.STATUS_NORMAL;

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
