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
            modalButton: '',
            modalBodyComponent: '',
        }
    },
    methods: {
        setModal(type, modalId) {
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
            }
            this.$bvModal.show(modalId);
        }
    },
    components: {
        'project-create': () => import('../modals/projects/Create.vue'),
        'project-edit': () => import('../modals/projects/Edit.vue'),
        'project-archive': () => import('../modals/projects/Archive.vue'),
        'project-delete': () => import('../modals/projects/Delete.vue'),
        'project-restore': () => import('../modals/projects/Restore.vue'),
    },
}
