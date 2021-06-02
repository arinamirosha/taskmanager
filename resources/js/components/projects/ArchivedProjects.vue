<template>
    <div>
        <div class="row mt-5">
            <div class="col-md-6 font-weight-bold h3">Archived Projects
                <transition name="fade" appear><i v-if="!isDataLoaded" class="fas fa-spinner fa-spin h3"></i></transition>
            </div>
        </div>

        <div class="row">
            <div class="col-md-12">
                <div class="row h5 font-weight-bold">
                    <div class="col-md-3">Project</div>
                    <div class="col-md-3">Count Active Tasks</div>
                    <div class="col-md-2">Archived</div>
                </div>

                <div v-if="isDataLoaded && projects.length !== 0" v-for="project in projects"
                     :key="project.id"
                     class="row cursor-pointer task pt-1 pb-1"
                     @click="showProject(project.id)"
                >
                    <div class="col-md-3">{{project.name}}</div>
                    <div class="col-md-3">{{project.tasks_count}}</div>
                    <div class="col-md-2">{{formatDate(project.deleted_at)}}</div>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
import route from "../../route";
import * as constants from "../../constants";
import moment from "moment";

export default {
    data() {
        return {
            projects: [],
            isDataLoaded: false,
        }
    },
    computed: {
        c: function () {
            return constants;
        },
    },
    methods: {
        getProjects() {
            axios
                .get(route('projects.index'), {
                    params: {
                        'type': constants.ARCHIVE,
                    }
                })
                .then(response => {
                    this.projects = response.data.projects;
                    this.isDataLoaded = true;
                })
                .catch(error => {
                    console.log(error);
                });
        },
        formatDate(date) {
            return date ? moment(date).format('MMMM DD, YYYY') : '';
        },
        showProject(id) {
            this.$emit('showProject', id);
        },
    },
    mounted() {
        this.getProjects();
    }
}
</script>

<style>
.fade-enter-active, .fade-leave-active {
    transition: opacity .5s;
}
.fade-enter, .fade-leave-to {
    opacity: 0;
}
.cursor-pointer {
    cursor: pointer;
}
.task:hover {
    background-color: #e0eeee;
    border-radius: 5px;
}
.task-finished {
    color: #dedede;
}
</style>
