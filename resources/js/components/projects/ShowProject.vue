<template>
    <div class="container-xl">

        <div v-if="project">
            <div v-if="isProjectLoaded">

                <div class="row">
                    <div class="col-md-6">
                        <span class="font-weight-bold h4">
                            {{project.name}}
                            <span v-if="project.deleted_at" class="text-info">ARCHIVED</span>
                        </span>
                    </div>
                    <div class="col-md-6">

                        <div class="row h5 justify-content-between px-2 pt-2 pt-md-0">

                            <button v-if="!project.deleted_at" class="btn btn-primary btn-sm" @click="$emit('openProjectModal', c.CREATE_TASK, project)">Add New Task</button>

                            <button v-if="!project.deleted_at" :disabled="isArchFinBtnDisabled" class="btn btn-sm btn-outline-secondary" @click="archiveAllForProject">Archive Finished</button>

                            <span v-if="!project.deleted_at" class="cursor-pointer">
                                <i class="fa fa-star fav-star-full" v-if="project.favorite" id="fav" @click="changeFav(project.id, false)"></i>
                                <i class="far fa-star text-custom-secondary" v-else id="not-fav" @click="changeFav(project.id, true)"></i>
                            </span>

                            <a v-if="!project.deleted_at && !project.shared" class="text-muted cursor-pointer" @click="$emit('openProjectModal', c.EDIT_PROJECT, project)">
                                <i class="far fa-edit"></i>
                            </a>

                            <a v-if="!project.deleted_at && !project.shared" class="cursor-pointer text-muted" @click="$emit('openProjectModal', c.SHARE_PROJECT, project)">
                                <i class="far fa-share-square"></i>
                            </a>

                            <span v-if="!project.shared">
                                <a v-if="!project.deleted_at" class="cursor-pointer" @click="$emit('openProjectModal', c.ARCHIVE_PROJECT)">
                                    <i class="fas fa-archive text-secondary"></i>
                                </a>
                                <a v-else class="cursor-pointer" @click="$emit('openProjectModal', c.RESTORE_PROJECT)">
                                    <i class="fas fa-trash-restore"></i>
                                </a>
                            </span>

                            <a v-if="!project.shared" class="cursor-pointer text-danger" @click="$emit('openProjectModal', c.DELETE_PROJECT)">
                                <i class="far fa-trash-alt"></i>
                            </a>

                        </div>
                    </div>
                </div>

                <div class="row my-1">
                    <div class="col-8">
                        {{project.user.name}}<span v-if="project.user.surname">
                        {{project.user.surname}}</span><span v-if="project.shared_users.length > 0">{{sharedNames}}</span>
                    </div>
                    <div class="col-4 text-right">{{pDate}}</div>
                </div>

                <div v-if="mediumStyle" class="row font-weight-bold h6">
                    <div class="col-md-4">New</div>
                    <div class="col-md-4">In progress</div>
                    <div class="col-md-4">Finished</div>
                </div>

                <div class="row" :class="{'full': largeStyle}">
                    <div class="col-md-4 border-left">
                        <div v-if="compactStyle" class="font-weight-bold h6">New</div>
                        <draggable :handle="compactStyle" :list="tasksNew" group="tasks" @change="update" :move="isMove">
                            <div v-for="task in tasksNew" :key="task.id">
                                <list-item-task :currentUserId="currentUserId" :task="task" @showTask="showTask" @archived="taskArchived"></list-item-task>
                            </div>
                        </draggable>
                    </div>
                    <div class="col-md-4 border-left">
                        <div v-if="compactStyle" class="font-weight-bold h6">In progress</div>
                        <draggable :handle="compactStyle" :list="tasksProgress" group="tasks" @change="update" :move="isMove">
                            <div v-for="task in tasksProgress" :key="task.id">
                                <list-item-task :currentUserId="currentUserId" :task="task" @showTask="showTask" @archived="taskArchived"></list-item-task>
                            </div>
                        </draggable>
                    </div>
                    <div class="col-md-4 border-left">
                        <div v-if="compactStyle" class="font-weight-bold h6">Finished</div>
                        <draggable :handle="compactStyle" :list="tasksFinished" group="tasks" @change="update" :move="isMove">
                            <div v-for="task in tasksFinished" :key="task.id">
                                <list-item-task :currentUserId="currentUserId" :task="task" @showTask="showTask" @archived="taskArchived"></list-item-task>
                            </div>
                        </draggable>
                    </div>
                </div>

            </div>
        </div>
        <div v-else class="h4">Select project</div>

    </div>
</template>

<script>
import route from "../../route";
import draggable from 'vuedraggable';
import constantsMixin from "../mixins/constants.js";
import customWidthMixin from "../mixins/custom-width.js";
import moment from "moment";

export default {
    mixins: [
        constantsMixin,
        customWidthMixin,
    ],
    props: ['id', 'currentUserId'],
    data() {
        return {
            project: {},
            tasks: [],
            tasksNew: [],
            tasksProgress: [],
            tasksFinished: [],
            tasksNewLength: 0,
            tasksProgressLength: 0,
            tasksFinishedLength: 0,
            isProjectLoaded: false,
            isArchFinBtnDisabled: false,
        }
    },
    watch: {
        id: function() {
            this.editProject = false;
            this.getProject();
        }
    },
    computed: {
        sharedNames: function () {
            let acceptedUsers = this.project.shared_users.filter(a => a.pivot.accepted);
            let names = acceptedUsers.map(a => a.name + (a.surname ? ' ' + a.surname : ''));
            return names.length ? ', ' + names.join(', ') : '';
        },
        pDate: function () {
            return moment(new Date(this.project.created_at)).format('DD.MM.YY HH:mm');
        },
    },
    methods: {
        getProject(projectId) {
            let id = projectId ? projectId : this.id;
            if (!id) {
                this.project = null;
                return;
            }
            axios
                .get(route('projects.show', id))
                .then(response => {
                    this.project = response.data;
                    this.tasks = this.project.tasks;

                    this.tasksNew = this.tasks.filter(task => { return task.status === this.c.STATUS_NEW; }).sort((a, b) => b.importance - a.importance);
                    this.tasksProgress = this.tasks.filter(task => { return task.status === this.c.STATUS_PROGRESS; }).sort((a, b) => b.importance - a.importance);
                    this.tasksFinished = this.tasks.filter(task => { return task.status === this.c.STATUS_FINISHED; }).sort((a, b) => b.importance - a.importance);

                    this.tasksNewLength = this.tasksNew.length;
                    this.tasksProgressLength = this.tasksProgress.length;
                    this.tasksFinishedLength = this.tasksFinished.length;

                    this.isProjectLoaded = true;
                })
                .catch(error => {
                    console.log(error);
                    this.project = null;
                });
        },
        isMove(e) {
            let task =  e.draggedContext.element;
            return (e.from !== e.to) && (task.owner_id === this.currentUserId || task.user_id === this.currentUserId);
        },
        update(e) {
            if (e.added) {
                let taskId = e.added.element.id;
                let status = 0;
                if (this.tasksNewLength < this.tasksNew.length) {
                    status = this.c.STATUS_NEW;
                } else if (this.tasksProgressLength < this.tasksProgress.length) {
                    status = this.c.STATUS_PROGRESS;
                } else if (this.tasksFinishedLength < this.tasksFinished.length) {
                    status = this.c.STATUS_FINISHED;
                }

                if (status) {
                    axios
                        .post(route('tasks.update', taskId), {'status': status})
                        .then(response => {
                            this.getProject();
                            this.$emit('taskUpdated');
                        })
                        .catch(error => { console.log(error); });
                }
            }
        },
        showTask(task) {
            this.$emit('openTaskModal', this.c.SHOW_TASK, task, this.project);
        },
        taskArchived(id) {
            this.tasksFinished = this.tasksFinished.filter(function (task) {
                return task.id !== id;
            });
            this.$emit('taskArchived');
        },
        changeFav(projectId, favorite) {
            axios
                .post(route('projects.favorite', projectId), {
                    'favorite': favorite,
                })
                .then(response => {
                    this.getProject();
                    this.$emit('projectUpdated');
                })
                .catch(error => {
                    console.log(error)
                });
        },
        archiveAllForProject() {
            this.isArchFinBtnDisabled = true;
            axios
                .delete(route('tasks.archive'), {
                    params: {
                        'project_id': this.id,
                    }
                })
                .then(response => {
                    let countArchived = response.data;
                    let infoBody = countArchived ? 'Archived: ' + countArchived : 'Nothing to Archive';
                    this.$emit('showToast', infoBody)
                    this.getProject();
                    this.$emit('taskArchived');
                    this.isArchFinBtnDisabled = false;
                })
                .catch(error => {
                    console.log(error);
                });
        },
    },
    mounted() {
        this.getProject();
    },
    components: {
        draggable,
        'list-item-task': () => import('../tasks/ListItemTask.vue'),
    },
}
</script>

<style scoped>
.cursor-pointer{
    cursor: pointer;
}
.fav-star-full {
    color: #f7c948;
}
.text-custom-secondary {
    color: #c8c8c8;
}
#fav:hover {
    color: #c8c8c8;
}
#not-fav:hover {
    color: #f7c948;
}
.fa-edit:hover, .fa-archive:hover, .fa-share-square:hover {
    color: #212529;
}
.full {
    height: calc(100vh - 190px);
    overflow-y: scroll;
    overflow-x: hidden;
}
</style>
