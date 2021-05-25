<template>
    <div class="container">
        <div class="row justify-content-between">
            <span class="font-weight-bold h3">Today tasks</span>
            <span class="h5"><label><input type="checkbox" :checked="hideFinished" @click="switchHideFinished"> Hide Finished</label></span>
        </div>
        <div class="row justify-content-center">
            <div class="col-md-12 p-0 m-0">
                <index-task
                    :tasks="tasks"
                    :type="type"
                    :isDataLoaded="isDataLoaded"
                    @statusUpdated="getToday"
                    @archived="archived"
                    @taskDeleted="getToday"
                ></index-task>
            </div>
        </div>
    </div>
</template>

<script>
import route from "../../route";
import * as c from "../../constants";

export default {
    props: ['hideFinished'],
    data() {
        return {
            tasks: {},
            type: c.TODAY,
            isDataLoaded: false,
        }
    },
    methods: {
        getToday() {
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
            this.getToday();
            this.$emit('taskArchived');
        },
        switchHideFinished(e) {
            axios
                .post(route('users.update'), {
                    'hide_finished': e.target.checked,
                })
                .then(response => {
                    this.getToday();
                    this.$emit('userUpdated');
                })
                .catch(error => {
                    console.log(error);
                });
        },
    },
    mounted() {
        this.getToday();
    }
}
</script>
