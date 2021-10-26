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
                    this.modalBodyComponent = 'projects-edit';
                    break;
            }
            this.$bvModal.show(modalId);
        }
    },
    components: {
        'project-create': () => import('../modals/projects/Create.vue'),
        'projects-edit': () => import('../modals/projects/Edit.vue'),
    },
}
