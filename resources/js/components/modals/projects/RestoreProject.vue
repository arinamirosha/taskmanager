<template>
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Restore Project</h5>
            </div>
            <form @submit.prevent="restoreProject">
                <div class="modal-body">
                    <div class="form-group">
                        <p>
                            Are you sure want to restore "<strong>{{project.name}}</strong>"?
                            Tasks with statuses "NEW" and "IN PROCESS" will be restored too.
                            If you desire, tasks with status "FINISHED" you could restore manually.
                        </p>
                    </div>
                </div>

                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal" ref="cancel">Cancel</button>
                    <button type="submit" class="btn btn-primary" ref="restoreProject">Restore</button>
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
        restoreProject() {
            this.$refs.restoreProject.disabled = true;
            this.$refs.cancel.disabled = true;
            axios
                .post(route('projects.restore', this.project.id))
                .then(response => {
                    this.$refs.cancel.disabled = false;
                    this.$refs.cancel.click();
                    this.$emit('updated');
                    this.$refs.restoreProject.disabled = false;
                })
                .catch(error => {
                    console.log(error);
                });
        },
    },
}
</script>
