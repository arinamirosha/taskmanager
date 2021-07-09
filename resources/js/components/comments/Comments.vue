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
                <button class="btn btn-primary" @click="sendComment" ref="sendComment">Send</button>
            </div>
        </div>

        <hr v-if="!isArchive">

        <div v-if="!isDataLoaded" class="h-500 text-center">
            <transition name="fade" appear><i class="fas fa-spinner fa-spin h3"></i></transition>
        </div>
        <div v-else>
            <div v-if="comments.length === 0" class="h-500 text-center">No comments</div>
            <div v-else :class="{'comments h-500 pr-1': mediumStyle}">
                <div v-for="(comment, index) in comments" class="comment">
                    <div class="justify-content-between d-flex">
                        <div>
                            <span class="font-weight-bold">{{comment.user.name}} {{comment.user.surname}}</span>
                            <span v-if="comment.created_at !== comment.updated_at" class="text-secondary text-sm">Edited</span>
                        </div>
                        <div class="text-sm">
                            <span class="text-secondary">{{formatDate(comment.created_at)}}</span>
                            <i v-if="!isArchive && comment.user_id === currentUserId" class="fas fa-edit p-1" @click="editComment(index, comment.text)"></i>
                            <i v-if="!isArchive && comment.user_id === currentUserId" class="fas fa-times p-1" @click="deleteComment(comment.id)"></i>
                        </div>
                    </div>
                    <div v-if="comment.text.length <= 200" class="text-wrap" :ref="'cText'+index">{{comment.text}}</div>
                    <div v-else class="text-wrap" :ref="'cText'+index">{{comment.text.slice(0,200)}}<span :ref="'dots'+index">...</span><span :ref="'moreText'+index" class="d-none">{{comment.text.slice(200)}}</span>
                        <a @click.prevent="triggerMore(index)" class="text-secondary text-sm link" :ref="'moreTrigger'+index">Read&nbsp;more</a>
                    </div>
                    <form @submit.prevent="updateComment(index, comment.id)" :ref="'cEdit'+index" hidden>
                        <textarea class="form-control mb-2" :ref="'editedText'+index"></textarea>
                        <div class="text-right">
                            <button type="button" class="btn btn-outline-secondary btn-sm" @click="cancelEdit(index)" :ref="'commentCancel'+index">Cancel</button>
                            <button type="submit" class="btn btn-primary btn-sm" :ref="'commentUpdate'+index">Update</button>
                        </div>
                    </form>
                    <hr>
                </div>
                <div class="m-0 pr-2 row justify-content-between pb-1" v-if="!isLastPage">
                    <span>Page {{page}} of {{lastPage}}</span>
                    <transition name="fade" appear><i v-if="dataLoading" class="fas fa-spinner fa-spin h3"></i></transition>
                    <button class="btn btn-outline-secondary btn-sm" @click="loadMore">Load More...</button>
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
                this.$refs.sendComment.disabled = true;
                axios
                    .post(route('comments.store', this.taskId), {
                        'text': this.text,
                    })
                    .then(response => {
                        this.reset();
                        this.comments.unshift(response.data);
                        this.$emit('newComment');
                        this.$refs.sendComment.disabled = false;
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
        triggerMore(index) {
            let dots = this.$refs['dots'+index][0];
            let moreText = this.$refs['moreText'+index][0];
            let moreTrigger = this.$refs['moreTrigger'+index][0];

            if (dots.style.display === "none") {
                dots.style.display = "inline";
                moreTrigger.innerHTML = "Read more";
                moreText.classList.add('d-none');
            } else {
                dots.style.display = "none";
                moreTrigger.innerHTML = "Read&nbsp;less";
                moreText.classList.remove('d-none');
            }
        },
        editComment(index, text) {
            this.$refs['editedText'+index][0].value = text;
            this.$refs['cText'+index][0].hidden = true;
            this.$refs['cEdit'+index][0].hidden = false;
        },
        cancelEdit(index) {
            this.$refs['cText'+index][0].hidden = false;
            this.$refs['cEdit'+index][0].hidden = true;
        },
        updateComment(index, commentId) {
            let editedText = this.$refs['editedText'+index][0].value;

            if (editedText && editedText.length <= 1500) {
                this.$refs['commentUpdate'+index][0].disabled = true;
                this.$refs['commentCancel'+index][0].disabled = true;
                axios
                    .put(route('comments.update', commentId), {
                        text: editedText
                    })
                    .then(response => {
                        this.comments[index].text = response.data.text;
                        this.comments[index].updated_at = response.data.updated_at;
                        this.$refs['cText'+index][0].hidden = false;
                        this.$refs['cEdit'+index][0].hidden = true;
                        this.$refs['commentUpdate'+index][0].disabled = false;
                        this.$refs['commentCancel'+index][0].disabled = false;
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
