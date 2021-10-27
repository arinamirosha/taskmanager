<template>
    <div>
        <form @submit.prevent="shareProject" class="mb-2">
            <b-form-group label="Email" label-for="email-input" :invalid-feedback="'Pattern: \'aaa@aaa.aaa\', 1-100 symbols, you have ' + email.length">
                <b-form-input id="email-input" v-model="email" :state="$v.$dirty && $v.email.$error ? false : null"></b-form-input>
            </b-form-group>
            <button type="submit" class="btn btn-primary" :disabled="isShareBtnDisabled">Share</button>
        </form>

        <div v-for="sharedUser in project.shared_users" class="justify-content-between d-flex">
            <div>{{ sharedUser.email }}</div>
            <div :class="statusSharedCss(sharedUser.pivot.accepted)">{{ statusSharedText(sharedUser.pivot.accepted) }}</div>
            <div class="mv-10px">
                <i v-if="!project.deleted_at" class="fas fa-times text-secondary text-sm" @click="unshare(sharedUser.email)" v-show="!sharedEmailsToDel.includes(sharedUser.email)"></i>
            </div>
        </div>
    </div>
</template>

<script>
import {required, maxLength, email} from 'vuelidate/lib/validators';
import route from "../../../route";
import constantsMixin from "../../mixins/constants";
import Vue from 'vue';

export default {
    mixins: [
        constantsMixin,
    ],
    props: ['project'],
    data() {
        return {
            email: '',
            isShareBtnDisabled: false,
            sharedEmailsToDel: [],
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
            if (this.$v.$invalid) {
                return
            }
            this.isShareBtnDisabled = true;
            axios
                .post(route('projects.share', this.project.id), {
                    'email': this.email,
                })
                .then(response => {
                    this.$emit('projectUpdated', response.data.id);
                    this.isShareBtnDisabled = false;
                    this.project.shared_users.unshift({'email': this.email, 'pivot': {'accepted': null}});
                    this.reset();
                })
                .catch(error => {
                    console.log(error)
                    this.isShareBtnDisabled = false;
                    if (error.response && error.response.status === 400) {
                        alert(error.response.data);
                    } else if (error.request) {
                        alert(error.request.statusText);
                    }
                });
        },
        unshare(email) {
            if (confirm('Are you sure do not want share this project with user ' + email + '? All not trashed tasks of this user will be moved to yours.')) {
                this.sharedEmailsToDel.push(email);
                axios
                    .delete(route('projects.unshare', this.project.id), {
                        params: {
                            'email': email,
                        },
                    })
                    .then(response => {
                        this.$emit('projectUpdated', response.data.id);
                        this.$nextTick(() => {
                            this.sharedEmailsToDel = this.sharedEmailsToDel.filter(em => em !== email);
                            this.project.shared_users = this.project.shared_users.filter(su => su.email !== email);
                        })
                    })
                    .catch(error => {
                        console.log(error);
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

<style scoped>
.text-sm {
    font-size: 12px;
}

.fa-times {
    cursor: pointer;
    color: #eaeaea;
}

.fa-times:hover {
    color: #d7d7d7;
}

.mv-10px {
    min-width: 10px;
}
</style>
