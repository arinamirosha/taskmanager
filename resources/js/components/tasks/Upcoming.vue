<template>
    <div class="container">
        <div class="row font-weight-bold h3 justify-content-between">
            <span>Upcoming tasks</span>
            <span class="h5"><label><input type="checkbox" :checked="hideFinished" @click="switchHideFinished"> Hide Finished</label></span>
        </div>
        <div class="row justify-content-center">
            <div class="col-md-12 p-0 m-0">
                <index-task
                    :tasks="tasks"
                    :type="type"
                    :isDataLoaded="isDataLoaded"
                    @statusUpdated="getUpcoming"
                    @archived="archived"
                    @taskDeleted="getUpcoming"
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
            type: c.UPCOMING,
            isDataLoaded: false,
        }
    },
    methods: {
        getUpcoming() {
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
            this.getUpcoming();
            this.$emit('taskArchived');
        },
        switchHideFinished(e) {
            axios
                .post(route('users.update'), {
                    'hide_finished': e.target.checked,
                })
                .then(response => {
                    this.getUpcoming();
                    this.$emit('userUpdated');
                })
                .catch(error => {
                    console.log(error);
                });
        },
    },
    mounted() {
        this.getUpcoming();
    }
}
</script>
