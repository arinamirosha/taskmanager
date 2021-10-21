<template>
    <div class="container-xl">
        <div class="row">
            <div class="col-12 font-weight-bold h3">
                History
                <transition name="fade" appear><i v-if="!isDataLoaded || dataLoading" class="fas fa-spinner fa-spin h3"></i></transition>
            </div>
        </div>
        <div :class="largeStyle ? 'full pr-2' : 'mb-3'">
            <div v-if="isDataLoaded" v-for="(notification, index) in notifications" class="row p-1" :class="{'bg-even': index % 2 === 0}">
                <div class="col-10" style="white-space: pre-line">{{notification.data.user + ' ' + notification.data.text}}</div>
                <div class="col-2">{{formatDate(notification.created_at)}}</div>
            </div>
            <div class="m-0 row justify-content-between pt-2" v-if="isDataLoaded && !isLastPage">
                <span>Page {{page}} of {{lastPage}}</span>
                <button class="btn btn-outline-secondary btn-sm" @click="loadMore" :disabled="isLoadBtnDisabled">Load More...</button>
            </div>
        </div>
    </div>
</template>

<script>
import route from "../../route";
import constantsMixin from "../mixins/constants";
import customWidthMixin from "../mixins/custom-width.js";
import moment from "moment";
import paginationMixin from "../mixins/pagination";

export default {
    mixins: [
        constantsMixin,
        customWidthMixin,
        paginationMixin,
    ],
    data() {
        return {
            notifications: [],
            isDataLoaded: false,
            dataLoading: false,
            isLoadBtnDisabled: false,
        }
    },
    methods: {
        formatDate(date) {
            return moment(new Date(date)).format('DD.MM.YYYY HH:mm');
        },
        getHistory() {
            axios
                .get(route('history.index'))
                .then(response => {
                    this.firstLoad(response.data);
                    this.notifications = response.data.data;
                    this.isDataLoaded = true;
                    this.dataLoading = false;
                })
                .catch(error => {
                    console.log(error);
                });
        },
        loadMore() {
            this.dataLoading = true;
            this.isLoadBtnDisabled = true;
            axios
                .get(route('history.index'), {
                    params: {
                        'page': ++this.page,
                    }
                })
                .then(response => {
                    this.loadedMore(response.data);
                    this.notifications = this.notifications.concat(response.data.data);
                    this.dataLoading = false;
                    this.isLoadBtnDisabled = false;
                })
                .catch(error => {
                    console.log(error);
                });
        },
    },
    mounted() {
        this.getHistory();
    }
}
</script>

<style scoped>
.full {
    height: calc(100vh - 160px);
    overflow-y: scroll;
    overflow-x: hidden;
    margin-right: -12px;
}
.bg-even {
    background-color: #e0eeee82;
}
</style>
