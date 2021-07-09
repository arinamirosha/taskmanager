<template>
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Delete Project</h5>
            </div>
            <form @submit.prevent="deleteProject">
                <div class="modal-body">
                    <div class="form-group">
                        Are you sure want to delete "<strong>{{project.name}}</strong>"?
                        All tasks of this project will be deleted including archived ones.
                    </div>
                </div>

                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal" ref="cancel">Cancel</button>
                    <button type="submit" class="btn btn-danger" ref="deleteProject">Delete</button>
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
        deleteProject(e) {
            this.$refs.deleteProject.disabled = true;
            this.$refs.cancel.disabled = true;
            axios
                .delete(route('projects.destroy-force', this.project.id))
                .then(response => {
                    this.$refs.cancel.disabled = false;
                    this.$refs.cancel.click();
                    this.$emit('deleted');
                    this.$refs.deleteProject.disabled = false;
                })
                .catch(error => {
                    console.log(error)
                });
        },
    },
}
</script>
