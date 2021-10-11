<template>
    <div class="container mt-4">
        <form @submit.prevent="updateUser">
            <div class="col-md-12">
                <div class="row justify-content-center">
                    <div class="col-md-6 form-group">
                        <label for="user-name">Name</label>
                        <input class="form-control" id="user-name" v-model="name" :class="{'is-invalid': this.$v.name.$error}">
                    </div>
                </div>
            </div>
            <div class="col-md-12">
                <div class="row justify-content-center">
                    <div class="col-md-6 form-group">
                        <label for="user-surname">Surname</label>
                        <input class="form-control" id="user-surname" v-model="surname" :class="{'is-invalid': this.$v.surname.$error}">
                    </div>
                </div>
            </div>

            <div class="col-md-12">
                <div class="row justify-content-center">
                    <div class="col-md-6 form-group">
                        <button type="submit" class="btn btn-primary" :disabled="isSaveBtnDisabled">Save</button>
                    </div>
                </div>
            </div>
        </form>
    </div>
</template>

<script>
import route from "../../route";
import {maxLength, required} from "vuelidate/lib/validators";

export default {
    props: ['user'],
    data() {
        return {
            name: '',
            surname: '',
            isSaveBtnDisabled: false,
        }
    },
    validations: {
        name: {
            required,
            maxLength: maxLength(70),
        },
        surname: {
            maxLength: maxLength(70),
        },
    },
    methods: {
        updateUser(e) {
            this.$v.$touch();
            if (!this.$v.$invalid) {
                this.isSaveBtnDisabled = true;
                axios
                    .post(route('users.update', this.user.id), {
                        'name': this.name,
                        'surname': this.surname,
                    })
                    .then(response => {
                        location.reload();
                    })
                    .catch(error => {
                        console.log(error)
                    });
            }
        },
    },
    mounted() {
        this.name = this.user.name;
        this.surname = this.user.surname;
    }
}
</script>

<style scoped>

</style>
