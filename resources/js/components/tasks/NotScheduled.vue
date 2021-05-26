<template>
    <div class="container">

        <div class="row">
            <div class="col-md-6 font-weight-bold h3">Not scheduled tasks
                <transition name="fade" appear><i v-if="!isDataLoaded" class="fas fa-spinner fa-spin h3"></i></transition>
            </div>
            <div class="col-md-6">
                <div class="row justify-content-between h5">
                    <label><input type="checkbox" :checked="hideFinished" @click="switchHideFinished"> Hide Finished</label>
                    <button class="btn btn-sm btn-outline-secondary" @click="archiveAllNotScheduled">Archive Finished</button>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-md-12">
                <index-task
                    :tasks="tasks"
                    :type="type"
                    :isDataLoaded="isDataLoaded"
                    @archived="taskArchived"
                    @statusUpdated="getNotScheduled"
                    @taskDeleted="getNotScheduled"
                    @showProject="showProject"
                ></index-task>
            </div>
        </div>

        <!-- Toast -->
        <toast :body="infoBody" />

    </div>
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
        showProject(id) {
            this.$emit('showProject', id);
        },
        taskArchived() {
            this.getNotScheduled();
            this.$emit('taskArchived');
        }
    },
    mounted() {
        this.getNotScheduled();
    }
}
</script>
