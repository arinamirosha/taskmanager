<template>
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Create New Task</h5>
            </div>
            <form @submit.prevent="storeTask">
                <div class="modal-body">
                    <div class="form-group">
                        <label for="name">Name</label> <span class="text-danger">*</span>
                        <input class="form-control" id="name" v-model="name" :class="{'is-invalid': this.$v.name.$error}">
                    </div>
                    <div class="form-group">
                        <label for="details">Details</label>
                        <textarea class="form-control" id="details" v-model="details" :class="{'is-invalid': this.$v.details.$error}"></textarea>
                    </div>
                    <div class="form-group">
                        <label for="schedule">Schedule</label>
                        <input type="date" :min="today" class="form-control" id="schedule" v-model="schedule" :class="{'is-invalid': this.$v.schedule.$error}">
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
                </div>

                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal" @click="reset" ref="cancel">Cancel</button>
                    <button type="submit" class="btn btn-primary">Add</button>
                </div>
            </form>
        </div>
    </div>
</template>

<script>
import { required, maxLength, minValue } from 'vuelidate/lib/validators';
import route from "../../../route";
import moment from "moment";
import constantsMixin from "../../mixins/constants";

export default {
    mixins: [
        constantsMixin,
    ],
    props: ['id'],
    data() {
        return {
            name: '',
            details: '',
            schedule: null,
            today: moment().format("YYYY-MM-DD"),
            importance: 0,
            statuses: [],
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
            minValue (value) { return (value === null) || (value >= this.today) || (value === '') },
        },
        importance: {
            importance (value) { return this.statuses.includes(parseInt(value)) },
        },
    },
    methods: {
        storeTask(e) {
            this.$v.$touch();
            if (!this.$v.$invalid) {
                axios
                    .post(route('tasks.store'), {
                        'project_id': this.id,
                        'name': this.name,
                        'details': this.details,
                        'schedule': this.schedule,
                        'importance': this.importance,
                    })
                    .then(response => {
                        this.$refs.cancel.click();
                        this.$emit('stored');
                    })
                    .catch(error => {
                        console.log(error)
                    });
            }
        },
        reset() {
            this.name = '';
            this.details = '';
            this.schedule = null;
            this.importance = this.c.STATUS_NORMAL;
            this.$v.$reset();
        },
    },
    mounted() {
        this.importance = this.c.STATUS_NORMAL;
        this.statuses = [this.c.STATUS_NORMAL, this.c.STATUS_MEDIUM, this.c.STATUS_STRONG];
    },
}
</script>
