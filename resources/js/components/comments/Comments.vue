<template>
    <div>
        <div v-if="!isArchive" class="px-1">
            <div>
                <textarea
                    class="form-control w-100 mb-2"
                    v-model="text"
                    placeholder="Comment..."
                    :class="{'is-invalid': this.$v.text.$error}"
                />
            </div>
            <div v-if="this.$v.text.$error" class="text-danger">1-1500 symbols</div>
            <div class="text-right"><button class="btn btn-primary" @click="sendComment">Send</button></div>
        </div>

        <hr v-if="!isArchive">

        <div v-if="comments.length === 0" class="h-500 text-center">No comments</div>
        <div v-else class="comments h-500 pr-1">
            <div v-for="comment in comments" class="comment">
                <div class="justify-content-between d-flex">
                    <div class="font-weight-bold">{{comment.user.email}}</div>
                    <div class="text-secondary text-sm">
                        {{formatDate(comment.created_at)}}
                        <i v-if="!isArchive" class="fas fa-times ml-1 p-1" @click="deleteComment(comment.id)"></i>
                    </div>
                </div>
                <div>{{comment.text}}</div>
                <hr>
            </div>
        </div>
    </div>
</template>

<script>
import { required, maxLength } from 'vuelidate/lib/validators';
import route from "../../route";
import moment from "moment";

export default {
    props: ['taskId', 'isArchive'],
    data() {
        return {
            comments: [],
            text: '',
        }
    },
    watch: {
        taskId: function() {
            this.getComments();
            this.reset();
        }
    },
    validations: {
        text: {
            required,
            maxLength: maxLength(1500),
        },
    },
    methods: {
        formatDate(date) {
            return moment(new Date(date)).format('DD.MM.YY HH:mm');
        },
        sendComment() {
            this.$v.$touch();
            if (!this.$v.$invalid) {
                axios
                    .post(route('comments.store'), {
                        'task_id': this.taskId,
                        'text': this.text,
                    })
                    .then(response => {
                        this.reset();
                        this.comments.unshift(response.data);
                        this.$emit('newComment');
                    })
                    .catch(error => {
                        console.log(error)
                    });
            }
        },
        getComments() {
            axios
                .get(route('comments.index'), {
                    params: {
                        'task_id': this.taskId,
                    }
                })
                .then(response => {
                    this.comments = response.data;
                })
                .catch(error => {
                    console.log(error);
                });
        },
        deleteComment(id) {
            axios
                .delete(route('comments.destroy', id))
                .then(response => {
                    this.comments = this.comments.filter(comment => comment.id !== id);
                    this.$emit('commentDeleted');
                })
                .catch(error => {
                    console.log(error);
                });
        },
        reset() {
            this.text = '';
            this.$v.$reset();
        },
    },
}
</script>

<style scoped>
.h-500 {
    height: 400px;
}
.comments {
    overflow-y: scroll;
    overflow-x: hidden;
}
.text-sm {
    font-size: 12px;
}
.fa-times {
    cursor: pointer;
    color: #eaeaea;
}
.comment:hover .fa-times {
    color: #d7d7d7;
}
</style>
