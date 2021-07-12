<template>
    <div class="pb-4">
        <div class="row">
            <div class="col-md-6 col-12 font-weight-bold h3">Archived Projects
                <transition name="fade" appear><i v-if="!isDataLoaded || dataLoading" class="fas fa-spinner fa-spin h3"></i></transition>
            </div>
            <div class="col-md-3 col-6">
                <label><input type="checkbox" v-model="hasNotFinished"> Has not finished</label>
            </div>
            <div class="col-md-3 col-6 text-right">
                <label><input class="form-control form-control-sm" v-model="s" type="text" placeholder="Search project..."></label>
            </div>
        </div>

        <div class="row">
            <div class="col-md-12">
                <div v-if="mediumStyle" class="row h5 font-weight-bold">
                    <div class="col-md-8">Project</div>
                    <div class="col-md-2">Tasks To Restore</div>
                    <div class="col-md-2">Archived</div>
                </div>
                <div v-else class="h5 font-weight-bold">
                    Project-Tasks To Restore-Archived
                </div>

                <div :class="{'half': largeStyle}">
                    <div v-if="isDataLoaded && projects.length !== 0" v-for="(project, index) in projects"
                         :key="project.id"
                         class="row cursor-pointer task pt-1 pb-1"
                         @click="showProject(project.id)"
                         :class="{'bg-light': compactStyle && index % 2 === 0}"
                    >
                        <div class="col-md-8" :class="{'text-secondary': project.user_id !== currentUserId}">{{project.name}}</div>
                        <div class="col-md-2 col-6">{{project.tasks_count}}</div>
                        <div class="col-md-2 col-6 text-right text-md-left">{{formatDate(project.deleted_at)}}</div>
                    </div>
                    <div class="m-0 pr-2 row justify-content-between pb-1" v-if="isDataLoaded && !isLastPage">
                        <span>Page {{page}} of {{lastPage}}</span>
                        <button class="btn btn-outline-secondary btn-sm" @click="loadMore" ref="loadMore">Load More...</button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
import route from "../../route";
import moment from "moment";
import constantsMixin from "../mixins/constants.js";
import customWidthMixin from "../mixins/custom-width.js";
import paginationMixin from "../mixins/pagination";

export default {
    mixins: [
        constantsMixin,
        customWidthMixin,
        paginationMixin,
    ],
    props: ['currentUserId'],
    data() {
        return {
            projects: [],
            isDataLoaded: false,
            dataLoading: false,
            s: '',
            hasNotFinished: false,
        }
    },
    watch: {
        s: function () {
            this.dataLoading = true;
            this.debouncedGetUsers();
        },
        hasNotFinished: function () {
            this.dataLoading = true;
            this.getProjects();
        },
    },
    created() {
        this.debouncedGetUsers = _.debounce(this.getProjects, 500);
    },
    methods: {
        getProjects() {
            axios
                .get(route('projects.index'), {
                    params: {
                        'type': this.c.ARCHIVE,
                        's': this.s,
                        'hasNotFinished': this.hasNotFinished ? this.hasNotFinished : '',
                    }
                })
                .then(response => {
                    this.firstLoad(response.data.projects);
                    this.projects = response.data.projects.data;
                    this.isDataLoaded = true;
                    this.dataLoading = false;
                })
                .catch(error => {
                    console.log(error);
                });
        },
        loadMore() {
            this.dataLoading = true;
            this.$refs.loadMore.disabled = true;
            axios
                .get(route('projects.index'), {
                    params: {
                        'type': this.c.ARCHIVE,
                        's': this.s,
                        'hasNotFinished': this.hasNotFinished ? this.hasNotFinished : '',
                        'page': ++this.page,
                    }
                })
                .then(response => {
                    this.loadedMore(response.data.projects);
                    this.projects = this.projects.concat(response.data.projects.data);
                    this.dataLoading = false;
                    this.$refs.loadMore.disabled = false;
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
.half {
    height: calc(50vh - 120px);
    overflow-y: scroll;
    overflow-x:hidden;
}
</style>
