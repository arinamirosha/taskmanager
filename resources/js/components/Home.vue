<template>
    <div>

        <div :class="{'bg-light left-menu px-2 pt-2': largeStyle}">
            <nav :class="largeStyle ? 'nav flex-column' : 'dropdown'">
                <button v-if="!largeStyle" class="btn btn-menu dropdown-toggle" type="button" id="dropdownMenuButton" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                    Menu
                </button>

                <div :class="{'dropdown-menu bg-light': !largeStyle}">
                    <a class="nav-link text-dark name-count-space"  :class="{'active': type===c.NEW_SHARED}" @click="setType(c.NEW_SHARED)">
                        New shared
                        <span class="text-custom-secondary">{{newShared.length}}</span>
                    </a>
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
                    <a class="nav-link text-dark" :class="{'active': type===c.HISTORY}" @click="setType(c.HISTORY)">History</a>

                    <h6 v-if="favorites.length" @click="isOpenFav = !isOpenFav"
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
                                <span class="text-custom-secondary counts">{{project.tasks_count}}
                                    <span v-if="project.shared_users_count">
                                        <i class="fas fa-share" :class="{'fa-flip-horizontal': project.shared}"></i>
                                    </span>
                                </span>
                            </a>
                        </div>
                    </collapse-transition>

                    <h6 @click.self="isOpenProjects = !isOpenProjects"
                        class="cursor-pointer font-weight-bold sidebar-heading name-count-space align-items-center px-3 mt-4 mb-1 text-muted">
                        <span v-if="isOpenProjects">&#8595;</span>
                        <span v-else>&#8593;</span>
                        <span class="ml-2">Projects ({{projects.length}})</span>
                        <a class="text-muted h4 pl-2 pr-2 pt-1 pb-1" @click="setModal(c.CREATE_PROJECT, 'common-modal')">+</a>
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
                                <span class="text-custom-secondary counts">{{project.tasks_count}}
                                    <span v-if="project.shared_users_count">
                                        <i class="fas fa-share" :class="{'fa-flip-horizontal': project.shared}"></i>
                                    </span>
                                </span>
                            </a>
                        </div>
                    </collapse-transition>
                </div>
            </nav>
        </div>

        <div :class="{'main-content': largeStyle}" class="pt-3">
            <component
                ref="mainComponent"
                :is="currentComponent"
                :id="this.selectedProjectId"
                :type="type"
                :newShared="newShared"
                :currentUserId="currentUserId"
                @projectUpdated="getProjects"
                @taskArchived="getProjects"
                @taskUpdated="getProjects"
                @showProject="selectProject"
                @openProjectModal="openProjectModal"
                @openTaskModal="openTaskModal"
            ></component>
        </div>

        <!-- Modal-->
        <b-modal id="common-modal" @hide="(e) => {wait && e.preventDefault()}">
            <template #modal-header>
                {{modalTitle}}
                <a
                    v-if="modalBodyComponent === 'task-show' && !taskForModal.deleted_at && isInteractWithTask"
                    class="cursor-pointer text-muted"
                    @click="openTaskModal(c.EDIT_TASK, taskForModal, projectForModal)"
                ><i class="far fa-edit"></i></a>
            </template>
            <template #default>
                <component
                    :is="modalBodyComponent"
                    :project="projectForModal"
                    :task="taskForModal"
                    :projectId="selectedProjectId"
                    :currentUserId="currentUserId"
                    ref="modalBody"
                    @wait="wait=true"
                    @waitTaskAction="waitTaskAction=true"
                    @projectStored="projectStored"
                    @projectUpdated="projectUpdated"
                    @projectDeleted="projectDeleted"
                    @taskUpdated="taskUpdated"
                    @showProject="selectProject"
                ></component>
            </template>
            <template #modal-footer="{ cancel }">
                <b-button variant="danger" v-if="modalBodyComponent === 'task-show' && taskForModal.owner_id === currentUserId" @click="openTaskModal(c.DELETE_TASK, taskForModal)">Delete</b-button>

                <div v-if="modalBodyComponent === 'task-show' && isInteractWithTask">
                    <b-button variant="primary" v-if="!taskForModal.deleted_at && taskForModal.status !== c.STATUS_FINISHED" :disabled="waitTaskAction">
                        <span v-if="taskForModal.status === c.STATUS_NEW" @click="$refs.modalBody.changeStatus(c.STATUS_PROGRESS)">Start</span>
                        <span v-else-if="taskForModal.status === c.STATUS_PROGRESS" @click="$refs.modalBody.changeStatus(c.STATUS_FINISHED)">Finish</span>
                    </b-button>

                    <b-button variant="primary" v-if="!taskForModal.deleted_at && (taskForModal.status === c.STATUS_FINISHED || (projectForModal && projectForModal.deleted_at) )" :disabled="waitTaskAction">
                        <span @click="$refs.modalBody.archive()">Archive</span>
                    </b-button>

                    <b-button variant="primary" v-if="taskForModal.deleted_at && (projectForModal && !projectForModal.deleted_at)" :disabled="waitTaskAction">
                        <span @click="$refs.modalBody.restore()">Restore</span>
                    </b-button>
                </div>

                <b-button
                    @click="modalBodyComponent === 'task-delete' || modalBodyComponent === 'task-edit' ? openTaskModal(c.SHOW_TASK, taskForModal, projectForModal) : cancel()"
                    :disabled="wait"
                >Cancel</b-button>
                <b-button variant="primary" v-if="modalButton" @click="$refs.modalBody.handleSubmit()" :disabled="wait">{{ modalButton }}</b-button>
            </template>
        </b-modal>

    </div>
</template>

<script>
import { CollapseTransition } from "@ivanv/vue-collapse-transition";
import route from "../route";
import constantsMixin from "./mixins/constants.js";
import customWidthMixin from "./mixins/custom-width.js";
import modalsMixin from "./mixins/modals.js";

export default {
    mixins: [
        constantsMixin,
        customWidthMixin,
        modalsMixin,
    ],
    data() {
        return {
            currentComponent: 'common-index-task',
            projects: [],
            newShared: [],
            cToday: [],
            cUpcoming: [],
            cNotScheduled: [],
            selectedProjectId: 0,
            isOpenFav: true,
            isOpenProjects: true,
            type: '',
            wait: false,
            waitTaskAction: false,
            currentUserId: 0,
        }
    },
    computed: {
        favorites: function () {
            return this.projects.filter(project => {
                return project.favorite;
            });
        },
        isInteractWithTask() {
            return this.taskForModal
                ? this.taskForModal.owner_id === this.currentUserId || this.taskForModal.user_id === this.currentUserId
                : false;
        },
    },
    watch: {
        selectedProjectId: function () {
            this.getProjects();
        },
        type: function () {
            this.getProjects();
        },
    },
    methods: {
        setComponent(component) {
            this.currentComponent = component;
        },
        setType(type) {
            this.type = type;
            if (type === this.c.NEW_SHARED) {
                this.setComponent('new-shared-projects');
            } else if (type === this.c.HISTORY) {
                this.setComponent('history');
            } else {
                this.setComponent('common-index-task');
            }
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
                    this.newShared = response.data.newShared;
                    let counts = response.data.counts;
                    this.cToday = counts[this.c.TODAY];
                    this.cUpcoming = counts[this.c.UPCOMING];
                    this.cNotScheduled = counts[this.c.NOT_SCHEDULED];
                })
                .catch(error => {
                    console.log(error);
                });
        },
        projectUpdated(projectId) {
            this.getProjects();
            this.$refs.mainComponent.getProject(projectId);
            this.wait = false;
        },
        projectStored(projectId) {
            this.getProjects();
            this.selectProject(projectId);
            this.wait = false;
        },
        projectDeleted() {
            this.getProjects();
            this.selectedProjectId = 0;
            this.wait = false;
        },
        taskUpdated(task) {
            this.getProjects();
            if (this.currentComponent === 'show-project') {
                this.$refs.mainComponent.getProject();
            } else {
                this.$refs.mainComponent.getTasks();
            }
            if (task && this.modalBodyComponent === 'task-edit') {
                this.taskForModal = task;
                this.openTaskModal(this.c.SHOW_TASK, this.taskForModal, this.projectForModal);
            }
            this.wait = false;
            this.waitTaskAction = false;
        },
        selectProject(id) {
            this.selectedProjectId = id;
            this.type = '';
            this.setComponent('show-project');
        },
        loadCurrentUser() {
            axios
                .get(route('users.show'))
                .then(response => {
                    this.currentUserId = response.data.id;
                })
                .catch(error => {
                    console.log(error);
                });
        },
    },
    mounted() {
        this.type = this.c.TODAY;
        this.loadCurrentUser();
    },
    components: {
        CollapseTransition,
        'history': () => import('./notifications/History.vue'),
        'common-index-task': () => import('./tasks/CommonIndexTask.vue'),
        'show-project': () => import('./projects/ShowProject.vue'),
        'new-shared-projects': () => import('./projects/NewSharedProject.vue'),
    },
}
</script>

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
.btn-menu {
    background-color: #e0eeee;
    border-radius: 5px 5px 0 0;
}
.dropdown {
    height: 0;
}
#dropdownMenuButton {
    margin-top: -62px;
    width: 80px;
    margin-left: calc(50% - 40px);
}
.dropdown-menu {
    width: 100vw;
    margin-left: 5px;
    top: 49px !important;
    border: none;
    border-radius: 0;
    border-bottom: 1px solid #e0eeee;
}
.fa-share {
    font-size: 10px;
}
.counts {
    min-width: 40px;
    text-align: right;
}
.nav-link {
    padding: 3px 5px;
}
.fa-edit:hover {
    color: #212529;
}
</style>
