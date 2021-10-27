<template>
    <div>
        Are you sure want to restore this project?
        Tasks with statuses "NEW" and "IN PROCESS" will be restored too.
        If you desire, tasks with status "FINISHED" you could restore manually.
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
                .post(route('projects.restore', this.projectId))
                .then(response => {
                    this.$emit('projectUpdated', response.data.id);
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
