<template>
    <div class="pb-4">
        <div class="row">
            <div class="col-md-6 font-weight-bold h3">Archived Projects
                <transition name="fade" appear><i v-if="!isDataLoaded || dataLoading" class="fas fa-spinner fa-spin h3"></i></transition>
            </div>
            <div class="col-md-6">
                <div class="row justify-content-end pr-2">
                    <label>
                        <input type="checkbox" v-model="hasNotFinished"> Has not finished
                    </label>
                    <label>
                        <input class="form-control form-control-sm ml-2" v-model="s" type="text" placeholder="Search project...">
                    </label>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-md-12">
                <div class="row h5 font-weight-bold">
                    <div class="col-md-3">Project</div>
                    <div class="col-md-3">Tasks To Restore</div>
                    <div class="col-md-2">Archived</div>
                </div>

                <div class="half">
                    <div v-if="isDataLoaded && projects.length !== 0" v-for="project in projects"
                         :key="project.id"
                         class="row cursor-pointer task pt-1 pb-1"
                         @click="showProject(project.id)"
                    >
                        <div class="col-md-3">{{project.name}}</div>
                        <div class="col-md-3">{{project.tasks_count}}</div>
                        <div class="col-md-2">{{formatDate(project.deleted_at)}}</div>
                    </div>
                    <div class="m-0 pr-2 row justify-content-between pb-1" v-if="!isLastPage">
                        <span>Page {{page}} of {{lastPage}}</span>
                        <button class="btn btn-outline-secondary btn-sm" @click="loadMore">Load More...</button>
                    </div>
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
            dataLoading: false,
            page: 0,
            lastPage: 0,
            isLastPage: false,
            s: '',
            hasNotFinished: false,
        }
    },
    computed: {
        c: function () {
            return constants;
        },
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
                        'type': constants.ARCHIVE,
                        's': this.s,
                        'hasNotFinished': this.hasNotFinished ? this.hasNotFinished : '',
                    }
                })
                .then(response => {
                    this.isLastPage = response.data.projects.current_page === response.data.projects.last_page;
                    this.page = 1;
                    this.lastPage = response.data.projects.last_page;
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
            axios
                .get(route('projects.index'), {
                    params: {
                        'type': constants.ARCHIVE,
                        's': this.s,
                        'hasNotFinished': this.hasNotFinished ? this.hasNotFinished : '',
                        'page': ++this.page,
                    }
                })
                .then(response => {
                    this.isLastPage = response.data.projects.current_page === response.data.projects.last_page;
                    this.projects = this.projects.concat(response.data.projects.data);
                    this.dataLoading = false;
                })
                .catch(error => {
                    console.log(error);
                    this.dataLoading = false;
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
::-webkit-scrollbar {
    width: 12px;
}
::-webkit-scrollbar-track {
    -webkit-box-shadow: inset 0 0 6px #e0eeee;
}
::-webkit-scrollbar-thumb {
    border-radius: 10px;
    -webkit-box-shadow: inset 0 0 6px #e1e1e1;
}
</style>
