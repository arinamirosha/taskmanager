<template>
    <div>
        <div v-if="!isArchive" class="mx-3">
            <div class="row">
                <textarea
                    class="form-control mb-2"
                    v-model="text"
                    placeholder="Comment..."
                    :class="{'is-invalid': this.$v.text.$error}"
                />
            </div>
            <div v-if="this.$v.text.$error" class="row text-danger">1-1500 symbols</div>
            <div class="row justify-content-end">
                <button class="btn btn-primary" @click="sendComment" :disabled="isSendBtnDisabled">Send</button>
            </div>
        </div>

        <hr v-if="!isArchive">

        <div v-if="!isDataLoaded" class="h-500 text-center">
            <transition name="fade" appear><i class="fas fa-spinner fa-spin h3"></i></transition>
        </div>
        <div v-else>
            <div v-if="comments.length === 0" class="h-500 text-center">No comments</div>
            <div v-else :class="{'comments h-500 pr-1': mediumStyle}">
                <div v-for="comment in comments" class="comment">
                    <div class="justify-content-between d-flex">
                        <div>
                            <span class="font-weight-bold">{{comment.user.name}} {{comment.user.surname}}</span>
                            <span v-if="comment.created_at !== comment.updated_at" class="text-secondary text-sm">Edited</span>
                        </div>
                        <div class="text-sm">
                            <span class="text-secondary">{{formatDate(comment.created_at)}}</span>
                            <i v-if="!isArchive && comment.user_id === currentUserId" class="fas fa-edit p-1" @click="editComment(comment.text, comment.id)"></i>
                            <i v-if="!isArchive && comment.user_id === currentUserId" class="fas fa-times p-1" @click="deleteComment(comment.id)"></i>
                        </div>
                    </div>
                    <form v-if="editCommentId === comment.id" @submit.prevent="updateComment(comment.id)">
                        <textarea class="form-control mb-2" v-model="textToEdit"></textarea>
                        <div class="text-right">
                            <button type="button" class="btn btn-outline-secondary btn-sm" @click="cancelEdit()" :disabled="isCancelBtnDisabled">Cancel</button>
                            <button type="submit" class="btn btn-primary btn-sm" :disabled="isUpdateBtnDisabled">Update</button>
                        </div>
                    </form>
                    <div v-else>
                        <div v-if="comment.text.length <= 200" class="text-wrap">{{comment.text}}</div>
                        <div v-else-if="showMoreCommentIds.includes(comment.id)" class="text-wrap">
                            {{comment.text}} <a @click.prevent="triggerMore(comment.id)" class="text-secondary text-sm link">Read less</a>
                        </div>
                        <div v-else class="text-wrap">
                            {{comment.text.slice(0,200)}}... <a @click.prevent="triggerMore(comment.id)" class="text-secondary text-sm link">Read more</a>
                        </div>
                    </div>
                    <hr>
                </div>
                <div class="m-0 pr-2 row justify-content-between pb-1" v-if="!isLastPage">
                    <span>Page {{page}} of {{lastPage}}</span>
                    <transition name="fade" appear><i v-if="dataLoading" class="fas fa-spinner fa-spin h3 m-0"></i></transition>
                    <button class="btn btn-outline-secondary btn-sm" @click="loadMore" :disabled="isLoadBtnDisabled">Load More...</button>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
import { required, maxLength } from 'vuelidate/lib/validators';
import route from "../../route";
import moment from "moment";
import paginationMixin from "../mixins/pagination";
import customWidthMixin from "../mixins/custom-width";

export default {
    mixins: [
        customWidthMixin,
        paginationMixin,
    ],
    props: ['taskId', 'isArchive'],
    data() {
        return {
            comments: [],
            text: '',
            isDataLoaded: false,
            dataLoading: false,
            currentUserId: 0,
            isSendBtnDisabled: false,
            isLoadBtnDisabled: false,
            editCommentId: null,
            textToEdit: '',
            isCancelBtnDisabled: false,
            isUpdateBtnDisabled: false,
            showMoreCommentIds: [],
        }
    },
    watch: {
        taskId: function() {
            this.reset();
            if (this.taskId) {
                this.getComments();
            }
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
                this.isSendBtnDisabled = true;
                axios
                    .post(route('comments.store', this.taskId), {
                        'text': this.text,
                    })
                    .then(response => {
                        this.reset();
                        this.comments.unshift(response.data);
                        this.$emit('newComment');
                        this.isSendBtnDisabled = false;
                    })
                    .catch(error => {
                        console.log(error)
                    });
            }
        },
        getComments() {
            axios
                .get(route('comments.index', this.taskId))
                .then(response => {
                    this.firstLoad(response.data.comments);
                    this.comments = response.data.comments.data;
                    this.currentUserId = response.data.currentUserId;
                    this.isDataLoaded = true;
                })
                .catch(error => {
                    console.log(error);
                });
        },
        loadMore() {
            this.dataLoading = true;
            this.isLoadBtnDisabled = true;
            axios
                .get(route('comments.index', this.taskId), {
                    params: {
                        'page': ++this.page,
                    }
                })
                .then(response => {
                    this.loadedMore(response.data.comments);
                    this.comments = this.comments.concat(response.data.comments.data);
                    this.dataLoading = false;
                    this.isLoadBtnDisabled = false;
                })
                .catch(error => {
                    console.log(error);
                });
        },
        deleteComment(id) {
            if (confirm('Are you sure want to delete this comment?')) {
                this.comments = this.comments.filter(comment => comment.id !== id);
                axios
                    .delete(route('comments.destroy', id))
                    .then(response => {
                        this.$emit('commentDeleted');
                    })
                    .catch(error => {
                        console.log(error);
                    });
            }
        },
        reset() {
            this.text = '';
            this.isDataLoaded = true;
            this.dataLoading = false;
            this.$v.$reset();
        },
        triggerMore(commentId) {
            if (this.showMoreCommentIds.includes(commentId)) {
                this.showMoreCommentIds = this.showMoreCommentIds.filter(cId => cId !== commentId);
            } else {
                this.showMoreCommentIds.push(commentId);
            }
        },
        editComment(text, commentId) {
            this.textToEdit = text;
            this.editCommentId = commentId;
        },
        cancelEdit() {
            this.textToEdit = '';
            this.editCommentId = null;
        },
        updateComment(commentId) {
            let editedText = this.textToEdit;

            if (editedText && editedText.length <= 1500) {
                this.isUpdateBtnDisabled = true;
                this.isCancelBtnDisabled = true;
                axios
                    .put(route('comments.update', commentId), {
                        text: editedText
                    })
                    .then(response => {
                        let foundIndex = this.comments.findIndex(c => c.id === commentId);
                        this.comments[foundIndex].text = response.data.text;
                        this.comments[foundIndex].updated_at = response.data.updated_at;
                        this.isUpdateBtnDisabled = false;
                        this.isCancelBtnDisabled = false;
                        this.textToEdit = '';
                        this.editCommentId = null;
                    })
                    .catch(error => {
                        console.log(error);
                    });
            } else {
                alert('1-1500 symbols');
            }
        }
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
.fa-times, .fa-edit {
    cursor: pointer;
    color: #eaeaea;
}
.fa-times:hover, .fa-edit:hover {
    color: #d7d7d7;
}
.link:hover {
    cursor: pointer;
}
.text-wrap {
    word-wrap: break-word;
}
</style>
