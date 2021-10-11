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
                <button type="submit" class="btn btn-danger" @click="deleteTask" :disabled="isDeleteBtnDisabled">Delete</button>
                <button data-dismiss="modal" ref="dismiss" hidden></button>
            </div>
        </div>
    </div>
</template>

<script>
import route from "../../../route";

export default {
    props: ['task'],
    data() {
        return {
            isDeleteBtnDisabled: false,
        }
    },
    methods: {
        deleteTask(e) {
            this.isDeleteBtnDisabled = true;
            this.$refs.cancel.disabled = true;
            axios
                .delete(route('tasks.destroy-force', this.task.id))
                .then(response => {
                    this.$refs.cancel.disabled = false;
                    this.$emit('deleted');
                    this.isDeleteBtnDisabled = false;
                    this.$refs.dismiss.click();
                })
                .catch(error => {
                    console.log(error);
                    this.$refs.dismiss.click();
                });
        },
    },
}
</script>
