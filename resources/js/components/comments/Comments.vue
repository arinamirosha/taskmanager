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

        <div v-if="!isDataLoaded" class="h-500 text-center">
            <transition name="fade" appear><i class="fas fa-spinner fa-spin h3"></i></transition>
        </div>
        <div v-else>
            <div v-if="comments.length === 0" class="h-500 text-center">No comments</div>
            <div v-else class="comments h-500 pr-1">
                <div v-for="(comment, index) in comments" class="comment">
                    <div class="justify-content-between d-flex">
                        <div class="font-weight-bold">{{comment.user.name}} {{comment.user.surname}}</div>
                        <div class="text-secondary text-sm">
                            {{formatDate(comment.created_at)}}
                            <i v-if="!isArchive" class="fas fa-times ml-1 p-1" @click="deleteComment(comment.id)"></i>
                        </div>
                    </div>
                    <div v-if="comment.text.length <= 200">{{comment.text}}</div>
                    <div v-else>
                        {{comment.text.slice(0,200)}}<span :ref="'dots'+index">...</span><span :ref="'moreText'+index" class="d-none">{{comment.text.slice(200)}}</span>
                        <a @click.prevent="triggerMore(index)" class="text-secondary text-sm link" :ref="'moreTrigger'+index">Read more</a>
                    </div>
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

export default {
    mixins: [
        paginationMixin,
    ],
    props: ['taskId', 'isArchive'],
    data() {
        return {
            comments: [],
            text: '',
            isDataLoaded: false,
            dataLoading: false,
        }
    },
    watch: {
        taskId: function() {
            this.reset();
            this.getComments();
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
                    this.firstLoad(response.data);
                    this.comments = response.data.data;
                    this.isDataLoaded = true;
                })
                .catch(error => {
                    console.log(error);
                });
        },
        loadMore() {
            this.dataLoading = true;
            axios
                .get(route('comments.index'), {
                    params: {
                        'task_id': this.taskId,
                        'page': ++this.page,
                    }
                })
                .then(response => {
                    this.loadedMore(response.data);
                    this.comments = this.comments.concat(response.data.data);
                    this.dataLoading = false;
                })
                .catch(error => {
                    console.log(error);
                });
        },
        deleteComment(id) {
            if (confirm('Are you shure want to delete this comment?')) {
                axios
                    .delete(route('comments.destroy', id))
                    .then(response => {
                        this.comments = this.comments.filter(comment => comment.id !== id);
                        this.$emit('commentDeleted');
                    })
                    .catch(error => {
                        console.log(error);
                    });
            }
        },
        reset() {
            this.text = '';
            this.isDataLoaded = false;
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
                moreTrigger.innerHTML = "Read less";
                moreText.classList.remove('d-none');
            }
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
.link:hover {
    cursor: pointer;
}
</style>
