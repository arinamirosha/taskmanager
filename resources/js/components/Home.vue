<template>
    <div>

        <div class="left-menu bg-light px-2 pt-2">
            <nav class="nav flex-column">
                <a class="nav-link text-dark" :class="{'active': type===c.INCOMING}" @click="setType(c.INCOMING)">Incoming</a>
                <a class="nav-link text-dark name-count-space" :class="{'active': type===c.TODAY}" @click="setType(c.TODAY)">
                    Today
                    <span class="text-custom-secondary">
                        {{cToday[c.TOTAL]}}
                        <span v-if="cToday[c.TOTAL]">
                            ({{cToday[c.STATUS_NEW_TEXT]}}/{{cToday[c.STATUS_PROGRESS_TEXT]}}/{{cToday[c.STATUS_FINISHED_TEXT]}})
                        </span>
                        <span v-else>
                            (0/0/0)
                        </span>
                    </span>
                </a>
                <a class="nav-link text-dark name-count-space" :class="{'active': type===c.UPCOMING}" @click="setType(c.UPCOMING)">
                    Upcoming
                    <span class="text-custom-secondary">
                        {{cUpcoming[c.TOTAL]}}
                        <span v-if="cUpcoming[c.TOTAL]">
                            ({{cUpcoming[c.STATUS_NEW_TEXT]}}/{{cUpcoming[c.STATUS_PROGRESS_TEXT]}}/{{cUpcoming[c.STATUS_FINISHED_TEXT]}})
                        </span>
                        <span v-else>
                            (0/0/0)
                        </span>
                    </span>
                </a>
                <a class="nav-link text-dark name-count-space" :class="{'active': type===c.NOT_SCHEDULED}" @click="setType(c.NOT_SCHEDULED)">
                    Not Scheduled
                    <span class="text-custom-secondary">
                        {{cNotScheduled[c.TOTAL]}}
                        <span v-if="cNotScheduled[c.TOTAL]">
                            ({{cNotScheduled[c.STATUS_NEW_TEXT]}}/{{cNotScheduled[c.STATUS_PROGRESS_TEXT]}}/{{cNotScheduled[c.STATUS_FINISHED_TEXT]}})
                        </span>
                        <span v-else>
                            (0/0/0)
                        </span>
                    </span>
                </a>
                <a class="nav-link text-dark name-count-space" :class="{'active': type===c.ARCHIVE}" @click="setType(c.ARCHIVE)">
                    Archive
                </a>

                <h6 v-if="favorites.length > 0" @click="isOpenFav = !isOpenFav"
                    class="cursor-pointer font-weight-bold sidebar-heading name-count-space align-items-center px-3 mt-4 mb-1 text-muted">
                    <span v-if="isOpenFav">&#8595;</span>
                    <span v-else>&#8593;</span>
                    <span class="ml-2">Favorites ({{favorites.length}})</span>
                    <i class="far fa-star pr-2 text-secondary"></i>
                </h6>
                <collapse-transition>
                    <div v-show="isOpenFav">
                        <a class="nav-link name-count-space"
                           v-for="project in favorites"
                           :key="project.id"
                           @click="selectProject(project.id)"
                           :class="{'active': currentComponent==='show-project' && selectedProjectId === project.id }"
                        >
                            <span :style="{color: project.color}">{{project.name}}</span>
                            <span class="text-custom-secondary">{{project.tasks_count}}</span>
                        </a>
                    </div>
                </collapse-transition>

                <h6 @click.self="isOpenProjects = !isOpenProjects"
                    class="cursor-pointer font-weight-bold sidebar-heading name-count-space align-items-center px-3 mt-4 mb-1 text-muted">
                    <span v-if="isOpenProjects">&#8595;</span>
                    <span v-else>&#8593;</span>
                    <span class="ml-2">Projects ({{projects.length}})</span>
                    <a class="text-muted h4 pl-2 pr-2 pt-1 pb-1" data-toggle="modal" data-target="#createProjectModal">+</a>
                </h6>
                <collapse-transition>
                    <div v-show="isOpenProjects">
                        <a class="nav-link name-count-space"
                           v-for="project in projects"
                           :key="project.id"
                           @click="selectProject(project.id)"
                           :class="{'active': currentComponent==='show-project' && selectedProjectId === project.id }"
                        >
                            <span :style="{color: project.color}">{{project.name}}</span>
                            <span class="text-custom-secondary">{{project.tasks_count}}</span>
                        </a>
                    </div>
                </collapse-transition>

            </nav>
        </div>

        <div class="main-content pt-3">
            <component
                v-bind:is="currentComponent"
                :id="this.selectedProjectId"
                :type="type"
                @updated="getProjects"
                @deleted="getProjects"
                @taskArchived="getProjects"
                @taskUpdated="getProjects"
                @taskStored="getProjects"
                @taskDeleted="getProjects"
                @showProject="selectProject"
            ></component>
        </div>

        <!-- Modal-->
        <div class="modal fade show mt-5" id="createProjectModal" tabindex="-1">
            <create-project-modal @stored="storedProject"></create-project-modal>
        </div>

    </div>
</template>

<style scoped>
nav a:hover,
.active {
    background-color: #e0eeee;
    border-radius: 5px;
    cursor: pointer;
}
nav a:hover {
    text-decoration: none;
}
.cursor-pointer{
    cursor: pointer;
}
.text-custom-secondary {
    color: #c8c8c8;
}
.left-menu {
    width: 300px;
    height: calc(100vh - 55px);
    position: fixed;
    left: 0;
    bottom: 0;
    overflow-y: scroll;
}
.main-content {
    margin-left: 300px;
}
.name-count-space {
    display: flex;
    justify-content: space-between;
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

<script>
import { CollapseTransition } from "@ivanv/vue-collapse-transition";
import route from "../route";
import * as constants from "../constants";

export default {
    data() {
        return {
            currentComponent: 'common-index-task',
            projects: [],
            cToday: [],
            cUpcoming: [],
            cNotScheduled: [],
            selectedProjectId: 0,
            isOpenFav: true,
            isOpenProjects: true,
            type: 'archive', //today
        }
    },
    computed: {
        favorites: function () {
            return this.projects.filter(project => {
                return project.favorite;
            });
        },
        c: function () {
            return constants;
        },
    },
    methods: {
        setComponent(component) {
            this.currentComponent = component;
        },
        setType(type) {
            this.type = type;
            this.setComponent('common-index-task');
        },
        getProjects() {
            axios
                .get(route('projects.index'), {
                    params: {
                        'get_counts': true,
                    }
                })
                .then(response => {
                    this.projects = response.data.projects;
                    let counts = response.data.counts;
                    this.cToday = counts[constants.TODAY];
                    this.cUpcoming = counts[constants.UPCOMING];
                    this.cNotScheduled = counts[constants.NOT_SCHEDULED];
                })
                .catch(error => {
                    console.log(error);
                });
        },
        storedProject(projectId) {
            this.getProjects();
            this.selectProject(projectId);
        },
        selectProject(id) {
            this.selectedProjectId = id;
            this.type = '';
            this.setComponent('show-project');
        },
    },
    mounted() {
        this.getProjects();
    },
    components: {
        CollapseTransition,
    },
}
</script>
