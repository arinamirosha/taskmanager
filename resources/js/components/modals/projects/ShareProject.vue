<template>
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Share Project</h5>
            </div>

            <div class="modal-body">
                <form @submit.prevent="shareProject">
                    <div class="form-group">
                        <label for="email">Email</label>
                        <input class="form-control" id="email" v-model="email" :class="{'is-invalid': this.$v.email.$error}">
                    </div>
                    <button type="submit" class="btn btn-primary">Share</button>
                </form>

                <div v-for="sharedUser in this.project.shared_users">
                    <hr>
                    {{sharedUser.email}}
                </div>

            </div>

            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal" @click="reset" ref="cancel">Close</button>
            </div>
        </div>
    </div>
</template>

<script>
import { required, maxLength, email } from 'vuelidate/lib/validators';
import route from "../../../route";

export default {
    props: ['project'],
    data() {
        return {
            email: '',
        }
    },
    watch: {
        project: function() {
            this.reset();
        }
    },
    validations: {
        email: {
            required,
            email,
            maxLength: maxLength(100),
        },
    },
    methods: {
        shareProject(e) {
            this.$v.$touch();
            if (!this.$v.$invalid) {
                axios
                    .post(route('projects.share', this.project.id), {
                        'email': this.email,
                    })
                    .then(response => {
                        this.$emit('updated', response.data.id);
                    })
                    .catch(error => {
                        console.log(error)
                        if (error.response && error.response.status === 400) {
                            alert(error.response.data);
                        } else if (error.request) {
                            alert(error.request.statusText);
                        }
                    });
            }
        },
        reset() {
            this.email = '';
            this.$v.$reset();
        },
    },
}
</script>
