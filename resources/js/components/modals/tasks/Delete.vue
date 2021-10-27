<template>
    <div>
        Are you sure want to delete "<strong>{{task.name}}</strong>"?
    </div>
</template>

<script>
import route from "../../../route";

export default {
    props: ['task'],
    methods: {
        handleSubmit(e) {
            this.$emit('wait')
            axios
                .delete(route('tasks.destroy-force', this.task.id))
                .then(response => {
                    this.$emit('taskDeleted');
                    this.$nextTick(() => {
                        this.$bvModal.hide('common-modal')
                    })
                })
                .catch(error => {
                    console.log(error);
                });
        },
    },
}
</script>
