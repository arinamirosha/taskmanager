<template>
    <div class="container">

        <div class="row">
            <div class="col-12 font-weight-bold h3">Invitations to shared projects</div>
        </div>

        <div v-if="newShared.length === 0" class="h3 text-center pt-5">No invitations</div>
        <div v-else v-for="(newS, index) in newShared" class="row h5 pt-3">
            <div class="col-md-6 col-7">{{newS.name}}</div>
            <div class="col-md-4 col-5 text-right text-md-center">
                <button class="btn btn-success btn-sm" @click="changeAccepted(newS.id, true, index)" ref="accept">Accept</button>
                <button class="btn btn-danger btn-sm" @click="changeAccepted(newS.id, false, index)" ref="decline">Decline</button>
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
        changeAccepted(id, accepted, index) {
            this.$refs.accept[index].disabled = true;
            this.$refs.decline[index].disabled = true;
            axios
                .post(route('projects.accepted', id), {
                    'accepted': accepted,
                })
                .then(response => {
                    this.$emit('updated');
                    this.$refs.accept[index].disabled = false;
                    this.$refs.decline[index].disabled = false;
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
