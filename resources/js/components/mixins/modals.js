import * as constants from "../../constants";

export default {
    computed: {
        c: function () {
            return constants;
        },
    },
    data() {
        return {
            modalTitle: '',
            modalButton: null,
            modalBodyComponent: '',
            projectForModal: {},
            taskForModal: {},
        }
    },
    methods: {
        setModal(type, modalId, task = null) {
            switch (type) {
                case this.c.CREATE_PROJECT:
                    this.modalTitle = this.c.CREATE_PROJECT;
                    this.modalButton = 'Add';
                    this.modalBodyComponent = 'project-create';
                    break;
                case this.c.EDIT_PROJECT:
                    this.modalTitle = this.c.EDIT_PROJECT;
                    this.modalButton = 'Update';
                    this.modalBodyComponent = 'project-edit';
                    break;
                case this.c.ARCHIVE_PROJECT:
                    this.modalTitle = this.c.ARCHIVE_PROJECT;
                    this.modalButton = 'Archive';
                    this.modalBodyComponent = 'project-archive';
                    break;
                case this.c.DELETE_PROJECT:
                    this.modalTitle = this.c.DELETE_PROJECT;
                    this.modalButton = 'Delete';
                    this.modalBodyComponent = 'project-delete';
                    break;
                case this.c.RESTORE_PROJECT:
                    this.modalTitle = this.c.RESTORE_PROJECT;
                    this.modalButton = 'Restore';
                    this.modalBodyComponent = 'project-restore';
                    break;
                case this.c.SHARE_PROJECT:
                    this.modalTitle = this.c.SHARE_PROJECT;
                    this.modalButton = null;
                    this.modalBodyComponent = 'project-share';
                    break;
                case this.c.CREATE_TASK:
                    this.modalTitle = this.c.CREATE_TASK;
                    this.modalButton = 'Add';
                    this.modalBodyComponent = 'task-create';
                    break;
                case this.c.DELETE_TASK:
                    this.modalTitle = this.c.DELETE_TASK;
                    this.modalButton = 'Delete';
                    this.modalBodyComponent = 'task-delete';
                    break;
                case this.c.SHOW_TASK:
                    this.modalTitle = task.name;
                    this.modalButton = null;
                    this.modalBodyComponent = 'task-show';
                    break;
                case this.c.EDIT_TASK:
                    this.modalTitle = this.c.EDIT_TASK;
                    this.modalButton = 'Update';
                    this.modalBodyComponent = 'task-edit';
                    break;
            }
            this.$bvModal.show(modalId);
        },
        openProjectModal(type, project = null){
            if (project) {
                this.projectForModal = project;
            }
            this.setModal(type, 'common-modal');
        },
        openTaskModal(type, task = null, project = null) {
            if (task) {
                this.taskForModal = task;
            }
            if (project) {
                this.projectForModal = project;
            }
            this.setModal(type, 'common-modal', task);
        },
    },
    components: {
        'project-create': () => import('../modals/projects/Create.vue'),
        'project-edit': () => import('../modals/projects/Edit.vue'),
        'project-archive': () => import('../modals/projects/Archive.vue'),
        'project-delete': () => import('../modals/projects/Delete.vue'),
        'project-restore': () => import('../modals/projects/Restore.vue'),
        'project-share': () => import('../modals/projects/Share.vue'),
        'task-create': () => import('../modals/tasks/Create.vue'),
        'task-delete': () => import('../modals/tasks/Delete.vue'),
        'task-show': () => import('../modals/tasks/Show.vue'),
        'task-edit': () => import('../modals/tasks/Edit.vue'),
    },
}
