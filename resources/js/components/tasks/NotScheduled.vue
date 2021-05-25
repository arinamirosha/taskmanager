<template>
    <div class="container" v-if="isDataLoaded">

        <div class="row">
            <div class="col-md-5 font-weight-bold h3">Not scheduled tasks</div>
            <div class="col-md-6">
                <div class="row justify-content-between h5">
                    <label><input type="checkbox" :checked="hideFinished" @click="switchHideFinished"> Hide Finished</label>
                    <button class="btn btn-sm btn-outline-secondary" @click="archiveAllNotScheduled">Archive Finished</button>
                </div>
            </div>
        </div>

        <div class="row justify-content-center">
            <div class="col-md-12 p-0 m-0">
                <index-task
                    :tasks="tasks"
                    :type="type"
                    @statusUpdated="getNotScheduled"
                    @taskDeleted="getNotScheduled"
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
            type: constants.NOT_SCHEDULED,
            isDataLoaded: false,
            infoBody: '',
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
        archiveAllNotScheduled() {
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
                    this.getNotScheduled();
                    this.$emit('taskArchived');
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
