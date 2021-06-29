<template>
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Archive Project</h5>
            </div>
            <form @submit.prevent="archiveProject">
                <div class="modal-body">
                    <div class="form-group">
                        Are you sure want to archive "<strong>{{project.name}}</strong>"?
                        All tasks will be archived too.
                        If you decide to restore project later, tasks only with status "NEW" and "IN PROGRESS" will be restored.
                        Tasks with status "FINISHED" you will be able to restore manually.
                        Invitations to join project will be deleted.
                    </div>
                </div>

                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal" ref="cancel">Cancel</button>
                    <button type="submit" class="btn btn-primary">Archive</button>
                </div>
            </form>
        </div>
    </div>
</template>

<script>
import route from "../../../route";

export default {
    props: ['project'],
    methods: {
        archiveProject(e) {
            axios
                .delete(route('projects.archive', this.project.id))
                .then(response => {
                    this.$refs.cancel.click();
                    this.$emit('deleted');
                })
                .catch(error => {
                    console.log(error)
                });
        },
    },
}
</script>
