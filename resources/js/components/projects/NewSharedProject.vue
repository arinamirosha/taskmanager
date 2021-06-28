<template>
    <div class="container">

        <div class="row">
            <div class="col-12 font-weight-bold h3">Invitations to shared projects</div>
        </div>

        <div v-if="newShared.length === 0" class="h3 text-center pt-5">No invitations</div>
        <div v-else v-for="newS in newShared" class="row h5 pt-3">
            <div class="col-md-6 col-7">{{newS.name}}</div>
            <div class="col-md-4 col-5 text-right text-md-center">
                <button class="btn btn-success btn-sm" @click="changeAccepted(newS.id, true)">Accept</button>
                <button class="btn btn-danger btn-sm" @click="changeAccepted(newS.id, false)">Decline</button>
            </div>
        </div>

    </div>
</template>

<script>
import route from "../../route";

export default {
    name: "NewSharedProject",
    props: ['newShared'],
    methods: {
        changeAccepted(id, accepted) {
            axios
                .post(route('projects.accepted', id), {
                    'accepted': accepted,
                })
                .then(response => {
                    this.$emit('updated');
                })
                .catch(error => {
                    console.log(error);
                });
        },
    },
}
</script>

<style scoped>

</style>
