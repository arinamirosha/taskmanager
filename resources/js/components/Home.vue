<template>
    <div class="row">

        <div class="col-md-2 pl-md-5 pt-4">
            <nav class="nav flex-column">
                <a class="nav-link text-dark" :class="{'active': currentComponent==='incoming'}" @click="setComponent('incoming')">Incoming</a>
                <a class="nav-link text-dark" :class="{'active': currentComponent==='today'}" @click="setComponent('today')">Today</a>
                <a class="nav-link text-dark" :class="{'active': currentComponent==='upcoming'}" @click="setComponent('upcoming')">Upcoming</a>
                <a class="nav-link text-dark" :class="{'active': currentComponent==='not-scheduled'}" @click="setComponent('not-scheduled')">Not Scheduled</a>
                <a class="nav-link text-dark" :class="{'active': currentComponent==='archive'}" @click="setComponent('archive')">Archive</a>

                <h6 v-if="favorites.length > 0" @click="isOpenFav = !isOpenFav"
                    class="cursor-pointer font-weight-bold sidebar-heading d-flex justify-content-between align-items-center px-3 mt-4 mb-1 text-muted">
                    <span v-if="isOpenFav">&#8595;</span>
                    <span v-else>&#8593;</span>
                    <span class="ml-2">Favorites ({{favorites.length}})</span>
                    <span class="text-muted h4 pr-2 pt-2">&#9734;</span>
                </h6>
                <collapse-transition>
                    <div v-show="isOpenFav">
                        <a class="nav-link"
                           v-for="project in favorites"
                           :key="project.id"
                           @click="selectProject(project.id)"
                           :class="{'active': currentComponent==='show-project' && selectedProjectId === project.id }"
                           :style="{color: project.color}"
                        >{{project.name}}</a>
                    </div>
                </collapse-transition>

                <h6 @click.self="isOpenProjects = !isOpenProjects"
                    class="cursor-pointer font-weight-bold sidebar-heading d-flex justify-content-between align-items-center px-3 mt-4 mb-1 text-muted">
                    <span v-if="isOpenProjects">&#8595;</span>
                    <span v-else>&#8593;</span>
                    <span class="ml-2">Projects ({{projects.length}})</span>
                    <a class="text-muted h4 pl-2 pr-2 pt-1 pb-1" data-toggle="modal" data-target="#createProjectModal">+</a>
                </h6>
                <collapse-transition>
                    <div v-show="isOpenProjects">
                        <a class="nav-link"
                           v-for="project in projects"
                           :key="project.id"
                           @click="selectProject(project.id)"
                           :class="{'active': currentComponent==='show-project' && selectedProjectId === project.id }"
                           :style="{color: project.color}"
                        >{{project.name}}</a>
                    </div>
                </collapse-transition>

            </nav>
        </div>

        <div class="col-md-10 pt-4">
            <component v-bind:is="currentComponent" :id="this.selectedProjectId" @updated="getProjects" @deleted="getProjects"></component>
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
</style>

<script>
import { CollapseTransition } from "@ivanv/vue-collapse-transition";
import route from "../route";

export default {
    data() {
        return {
            currentComponent: 'today',
            projects: [],
            selectedProjectId: 0,
            isOpenFav: true,
            isOpenProjects: true,
        }
    },
    computed: {
        favorites: function () {
            return this.projects.filter(project => {
                return project.favorite;
            });
        },
    },
    methods: {
        setComponent(component) {
            this.currentComponent = component;
        },
        getProjects() {
            axios
                .get(route('projects.index'))
                .then(response => {
                    this.projects = response.data;
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
