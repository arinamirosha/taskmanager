<template>
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Archive Project</h5>
            </div>
            <form @submit.prevent="archiveProject">
                <div class="modal-body">
                    <div class="form-group">
                        <p>
                            Are you sure want to archive "<strong>{{project.name}}</strong>"?
                            If you check "Archive tasks" all task of this project will be archived including not finished.
                            If you don't check it no one task will be archived.
                        </p>
                        <label><input type="checkbox" v-model="archiveTasks"> Archive tasks</label>
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
    data() {
        return {
            archiveTasks: true,
        }
    },
    methods: {
        archiveProject(e) {
            axios
                .delete(route('projects.archive', this.project.id), {
                    data: {
                        'archive_tasks': this.archiveTasks,
                    }
                })
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
