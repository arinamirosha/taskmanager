<template>
    <div>
        Are you sure want to delete this project?
        All tasks of this project will be deleted including archived ones.
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
                .delete(route('projects.destroy-force', this.projectId))
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
