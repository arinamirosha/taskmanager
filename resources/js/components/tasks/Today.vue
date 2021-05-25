<template>
    <div class="container" v-if="isDataLoaded">

        <div class="row">
            <div class="col-md-5 font-weight-bold h3">Today tasks</div>
            <div class="col-md-6">
                <div class="row justify-content-between h5">
                    <label><input type="checkbox" :checked="hideFinished" @click="switchHideFinished"> Hide Finished</label>
                    <button class="btn btn-sm btn-outline-secondary" @click="archiveAllToday">Archive Finished</button>
                </div>
            </div>
        </div>

        <div class="row justify-content-center">
            <div class="col-md-12 p-0 m-0">
                <index-task
                    :tasks="tasks"
                    :type="type"
                    @statusUpdated="getToday"
                    @taskDeleted="getToday"
                ></index-task>
            </div>
        </div>

        <!-- Toast -->
        <toast :body="infoBody" />

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
            type: constants.TODAY,
            isDataLoaded: false,
            infoBody: '',
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
        archiveAllToday() {
            axios
                .delete(route('tasks.archive'), {
                    params: {
                        'type': this.type,
                    }
                })
                .then(response => {
                    let countArchived = response.data;

                    if (countArchived) {
                        this.infoBody = 'Archived: ' + countArchived;
                    } else {
                        this.infoBody = 'Nothing to Archive';
                    }

                    $('.toast').toast('show');
                    this.getToday();
                    this.$emit('taskArchived');
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
