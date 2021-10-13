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
                <button type="button" class="btn btn-secondary" data-dismiss="modal" :disabled="isCancelBtnDisabled" @click="$emit('cancel')">Cancel</button>
                <button type="submit" class="btn btn-danger" @click="deleteTask" :disabled="isDeleteBtnDisabled">Delete</button>
                <button data-dismiss="modal" ref="dismiss" hidden></button>
            </div>
        </div>
    </div>
</template>

<script>
import route from "../../../route";
import Vue from "vue";

export default {
    props: ['task'],
    data() {
        return {
            isDeleteBtnDisabled: false,
            isCancelBtnDisabled: false,
        }
    },
    methods: {
        deleteTask(e) {
            this.isDeleteBtnDisabled = true;
            this.isCancelBtnDisabled = true;
            axios
                .delete(route('tasks.destroy-force', this.task.id))
                .then(response => {
                    this.isCancelBtnDisabled = false;
                    Vue.nextTick(() => this.$refs.dismiss.click());
                    this.$emit('deleted');
                    this.isDeleteBtnDisabled = false;
                })
                .catch(error => {
                    console.log(error);
                });
        },
    },
}
</script>
