<template>
    <div class="container">
        <div class="row font-weight-bold h3">
            Not scheduled tasks
        </div>
        <div class="row justify-content-center">
            <div class="col-md-12 p-0 m-0">
                <index-task :tasks="tasks" :type="type" :isDataLoaded="isDataLoaded" @taskDeleted="getNotScheduled()"></index-task>
            </div>
        </div>
    </div>
</template>

<script>
import route from "../../route";
import * as c from "../../constants";

export default {
    data() {
        return {
            tasks: {},
            type: c.NOT_SCHEDULED,
            isDataLoaded: false,
        }
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
        }
    },
    mounted() {
        this.getNotScheduled();
    }
}
</script>
