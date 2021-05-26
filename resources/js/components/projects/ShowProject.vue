<template>
    <div class="container">

        <div v-if="project">

            <div class="row mb-3">
                <div class="col-md-6">
                    <span class="font-weight-bold h4">{{project.name}}</span>
                </div>
                <div class="col-md-6">

                    <div class="row justify-content-between h5">

                        <button class="btn btn-primary btn-sm" data-toggle="modal" data-target="#createTaskModal">Add New Task</button>

                        <button class="btn btn-sm btn-outline-secondary" @click="archiveAllForProject">Archive Finished</button>

                        <span class="cursor-pointer">
                            <i class="fa fa-star fav-star-full" v-if="project.favorite" id="fav" @click="changeFav(project.id, false)"></i>
                            <i class="far fa-star text-custom-secondary" v-else id="not-fav" @click="changeFav(project.id, true)"></i>
                        </span>

                        <a class="cursor-pointer text-muted" data-toggle="modal" data-target="#editProjectModal">
                            <i class="far fa-edit"></i>
                        </a>

                        <a class="cursor-pointer" data-toggle="modal" data-target="#archiveProjectModal">
                            <i class="fas fa-archive text-secondary"></i>
                        </a>

                        <a class="cursor-pointer text-danger" data-toggle="modal" data-target="#deleteProjectModal">
                            <i class="far fa-trash-alt"></i>
                        </a>

                    </div>
                </div>
            </div>

            <div class="row font-weight-bold h6">
                <div class="col-md-4">New</div>
                <div class="col-md-4">In progress</div>
                <div class="col-md-4">Finished</div>
            </div>

            <div class="row">
                <div class="col-md-4 border-left">
                    <draggable :list="tasksNew" group="tasks" @change="update" :move="isMove">
                        <div v-for="task in tasksNew" :key="task.id">
                            <list-item-task :task="task" @showTask="showTask" @archived="taskArchived"></list-item-task>
                        </div>
                    </draggable>
                </div>
                <div class="col-md-4 border-left">
                    <draggable :list="tasksProgress" group="tasks" @change="update" :move="isMove">
                        <div v-for="task in tasksProgress" :key="task.id">
                            <list-item-task :task="task" @showTask="showTask" @archived="taskArchived"></list-item-task>
                        </div>
                    </draggable>
                </div>
                <div class="col-md-4 border-left">
                    <draggable :list="tasksFinished" group="tasks" @change="update" :move="isMove">
                        <div v-for="task in tasksFinished" :key="task.id">
                            <list-item-task :task="task" @showTask="showTask" @archived="taskArchived"></list-item-task>
                        </div>
                    </draggable>
                </div>
            </div>

            <!-- Modals-->
            <div class="modal fade show mt-5" id="editProjectModal" tabindex="-1">
                <edit-project-modal :project="project" @updated="projectUpdated"></edit-project-modal>
            </div>
            <div class="modal fade show mt-5" id="deleteProjectModal" tabindex="-1">
                <delete-project-modal :project="project" @deleted="projectDeleted"></delete-project-modal>
            </div>
            <div class="modal fade show mt-5" id="archiveProjectModal" tabindex="-1">
                <archive-project-modal :project="project" @deleted="projectDeleted"></archive-project-modal>
            </div>

            <div class="modal fade show mt-5" id="createTaskModal" tabindex="-1">
                <create-task-modal :id="project.id" @stored="taskStored"></create-task-modal>
            </div>

            <button v-show="false" data-toggle="modal" data-target="#showTaskModal" ref="showTaskModalButton"></button>
            <div class="modal fade show mt-5 pb-5" id="showTaskModal" tabindex="-1" ref="showTaskModal">
                <show-task-modal
                    :task="currentTask"
                    @deleteTaskModal="$refs.deleteTaskModalButton.click()"
                    @archived="taskArchived"
                    @statusUpdated="taskStatusUpdated"
                ></show-task-modal>
            </div>

            <button v-show="false" data-toggle="modal" data-target="#deleteTaskModal" ref="deleteTaskModalButton"></button>
            <div class="modal fade show mt-5" id="deleteTaskModal" tabindex="-1">
                <delete-task-modal :task="currentTask" @deleted="taskDeleted" @cancel="$refs.showTaskModalButton.click();"></delete-task-modal>
            </div>

            <!-- Toast -->
            <toast :body="infoBody" />

        </div>
        <div v-else class="h4">Select project</div>

    </div>
</template>

<script>
import route from "../../route";
import * as c from '../../constants';
import draggable from 'vuedraggable';

export default {
    props: ['id'],
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
            currentTask: {},
            infoBody: '',
        }
    },
    watch: {
        id: function() {
            this.editProject = false;
            this.getProject();
        }
    },
    methods: {
        getProject(projectId) {
            let id = projectId ? projectId : this.id;
            axios
                .get(route('projects.show', id))
                .then(response => {
                    this.project = response.data;
                    this.tasks = response.data.tasks;
                    this.tasksNew = this.tasks.filter(task => { return task.status === c.STATUS_NEW; }).sort((a, b) => b.importance - a.importance);
                    this.tasksProgress = this.tasks.filter(task => { return task.status === c.STATUS_PROGRESS; }).sort((a, b) => b.importance - a.importance);
                    this.tasksFinished = this.tasks.filter(task => { return task.status === c.STATUS_FINISHED; }).sort((a, b) => b.importance - a.importance);
                    this.tasksNewLength = this.tasksNew.length;
                    this.tasksProgressLength = this.tasksProgress.length;
                    this.tasksFinishedLength = this.tasksFinished.length;
                    this.projectName = this.project.name;
                })
                .catch(error => {
                    console.log(error);
                });
        },
        projectUpdated(projectId) {
            this.getProject(projectId);
            this.$emit('updated');
        },
        projectDeleted() {
            this.project = null;
            this.$emit('deleted');
        },
        isMove(e) {
            return e.from !== e.to;
        },
        update: function(e) {
            if (e.added) {
                let taskId = e.added.element.id;
                let status = 0;
                if (this.tasksNewLength < this.tasksNew.length) {
                    status = c.STATUS_NEW;
                } else if (this.tasksProgressLength < this.tasksProgress.length) {
                    status = c.STATUS_PROGRESS;
                } else if (this.tasksFinishedLength < this.tasksFinished.length) {
                    status = c.STATUS_FINISHED;
                }

                if (status) {
                    axios
                        .post(route('tasks.update', taskId), {'status': status})
                        .then(response => { this.getProject(); })
                        .catch(error => { console.log(error); });
                }
            }
        },
        showTask(task) {
            this.currentTask = task;
        },
        taskArchived(id) {
            this.tasksFinished = this.tasksFinished.filter(function (task) {
                return task.id !== id;
            });
            this.$emit('taskArchived');
        },
        taskDeleted() {
            this.getProject();
            this.currentTask = {};
            this.$emit('taskDeleted');

        },
        taskStored() {
            this.getProject();
            this.$emit('taskStored');
        },
        changeFav(projectId, favorite) {
            axios
                .post(route('projects.update', projectId), {
                    'favorite': favorite,
                })
                .then(response => {
                    this.getProject();
                    this.$emit('updated');
                })
                .catch(error => {
                    console.log(error)
                });
        },
        taskStatusUpdated(id) {
            this.getProject();
        },
        archiveAllForProject() {
            axios
                .delete(route('tasks.archive'), {
                    params: {
                        'project_id': this.id,
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
                    this.getProject();
                    this.$emit('taskArchived');
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
.fa-edit:hover, .fa-archive:hover {
    color: #212529;
}
</style>
