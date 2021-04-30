<template>
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Delete Task</h5>
            </div>
            <div class="modal-body">
                <div class="form-group">
                    Are you sure want to delete "<strong>{{task.name}}</strong>"?
                </div>
            </div>

            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal" ref="cancel" @click="$emit('cancel')">Cancel</button>
                <button type="submit" class="btn btn-danger" data-dismiss="modal"  @click="deleteTask">Delete</button>
            </div>
        </div>
    </div>
</template>

<script>
import route from "../../../route";

export default {
    props: ['task'],
    methods: {
        deleteTask(e) {
            axios
                .delete(route('tasks.destroy-force', this.task.id))
                .then(response => {
                    this.$emit('deleted');
                })
                .catch(error => {
                    console.log(error)
                });
        },
    },
}
</script>
