<template>
    <div>
        Are you sure want to archive this project?
        All tasks will be archived too.
        If you decide to restore project later, tasks only with status "NEW" and "IN PROGRESS" will be restored.
        Tasks with status "FINISHED" you will be able to restore manually.
        Invitations to join project will be deleted.
    </div>
</template>

<script>
import route from "../../../route";

export default {
    props: ['projectId'],
    methods: {
        handleSubmit() {
            this.$emit('wait');
            axios
                .delete(route('projects.archive', this.projectId))
                .then(response => {
                    this.$emit('projectDeleted', response.data.id);
                    this.$nextTick(() => {
                        this.$bvModal.hide('common-modal')
                    })
                })
                .catch(error => {
                    console.log(error)
                });
        },
    },
}
</script>
