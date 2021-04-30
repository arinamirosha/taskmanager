<template>
    <div class="container">
        <div class="row font-weight-bold h3">
            Archive
        </div>
        <div class="row justify-content-center">
            <div class="col-md-12 p-0 m-0">
                <index-task :tasks="tasks"></index-task>
            </div>
        </div>
    </div>
</template>

<script>
import route from "../../route";

export default {
    data() {
        return {
            tasks: {},
        }
    },
    methods: {
        getArchive() {
            axios
                .get(route('tasks.index'), {
                    params: {
                        'trashed': true,
                    }
                })
                .then(response => {
                    this.tasks = response.data;
                })
                .catch(error => {
                    console.log(error);
                });
        }
    },
    mounted() {
        this.getArchive();
    }
}
</script>
