<template>
    <div class="container" v-if="isDataLoaded">
        <div class="row">
            <div class="col-md-5 font-weight-bold h3">Not scheduled tasks</div>
            <div class="col-md-6">
                <div class="row justify-content-between h5">
                    <label><input type="checkbox" :checked="hideFinished" @click="switchHideFinished"> Hide Finished</label>
                    <button class="btn btn-sm btn-outline-secondary" v-if="countFinished">Archive all ({{countFinished}})</button>
                </div>
            </div>
        </div>
        <div class="row justify-content-center">
            <div class="col-md-12 p-0 m-0">
                <index-task
                    :tasks="tasks"
                    :type="type"
                    :hideFinished="hideFinished"
                    @statusUpdated="getNotScheduled"
                    @archived="archived"
                    @taskDeleted="getNotScheduled"
                ></index-task>
            </div>
        </div>
    </div>
    <div v-else>Loading...</div>
</template>

<script>
import route from "../../route";
import * as constants from "../../constants";

export default {
    props: ['hideFinished'],
    data() {
        return {
            tasks: [],
            type: constants.NOT_SCHEDULED,
            isDataLoaded: false,
        }
    },
    computed: {
        countFinished: function () {
            let finishedTasks = this.tasks.filter(task => {
                return task.status === constants.STATUS_FINISHED;
            });
            return finishedTasks.length;
        },
    },
    methods: {
        getNotScheduled() {
            axios
                .get(route('tasks.index'), {
                    params: {
                        'type': this.type,
                    }
                })
                .then(response => {
                    this.tasks = response.data;
                    this.isDataLoaded = true;
                })
                .catch(error => {
                    console.log(error);
                });
        },
        archived(id) {
            this.getNotScheduled();
            this.$emit('taskArchived');
        },
        switchHideFinished(e) {
            axios
                .post(route('users.update'), {
                    'hide_finished': e.target.checked,
                })
                .then(response => {
                    this.getNotScheduled();
                    this.$emit('userUpdated');
                })
                .catch(error => {
                    console.log(error);
                });
        },
    },
    mounted() {
        this.getNotScheduled();
    }
}
</script>
