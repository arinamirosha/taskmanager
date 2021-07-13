<template>
    <div class="container-xl">
        <div class="row">
            <div class="col-12 font-weight-bold h3">
                History
                <transition name="fade" appear><i v-if="!isDataLoaded || dataLoading" class="fas fa-spinner fa-spin h3"></i></transition>
            </div>
        </div>
        <div :class="largeStyle ? 'full pr-2' : 'mb-3'">
            <div v-if="isDataLoaded" v-for="(notification, index) in notifications" class="row p-1" :class="{'bg-light': index % 2 === 0}">
                <div class="col-10" style="white-space: pre-line">{{getNotificationText(notification)}}</div>
                <div class="col-2">{{formatDate(notification.created_at)}}</div>
            </div>
            <div class="m-0 row justify-content-between pt-2" v-if="isDataLoaded && !isLastPage">
                <span>Page {{page}} of {{lastPage}}</span>
                <button class="btn btn-outline-secondary btn-sm" @click="loadMore" ref="loadMore">Load More...</button>
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
            this.$refs.loadMore.disabled = true;
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
                    this.$refs.loadMore.disabled = false;
                })
                .catch(error => {
                    console.log(error);
                });
        },
        getNotificationText(n) {
            let data = n.data;
            switch (n.type) {
                case 'App\\Notifications\\ProjectAction':
                    return data.user + ' ' + data.action + ' project "' + data.project + '"';
                case 'App\\Notifications\\ProjectUpdated':
                    return data.user + ' updated ' + data.field + ' project from "' + data.old + '" to "' + data.new + '"';
                case 'App\\Notifications\\ProjectShared':
                    return data.user + ' sent share request to ' + data.userShared + ' for project "' + data.project + '"';
                case 'App\\Notifications\\ProjectUnshared':
                    return data.user + ' unshared with ' + data.userUnshared + ' for project "' + data.project + '"';
                case 'App\\Notifications\\ProjectShareDecision':
                    return data.user + ' ' + data.decision + ' shared project "' + data.project + '"';
                case 'App\\Notifications\\CommentStored':
                    return data.user + ' left a comment "' + data.comment + '" in project "' + data.project + '"';
                case 'App\\Notifications\\TaskAction':
                    return data.user + ' ' + data.action + ' task "' + data.task + '" in project "' + data.project + '"';
                case 'App\\Notifications\\TaskUpdated':
                    let result = data.user + ' updated task "' + data.old.name + '": ';
                    if (data.old.name !== data.new.name) {
                        result += "\n" + 'name - "' + data.old.name + '" -> "' + data.new.name + '"';
                    }
                    if (data.old.details !== data.new.details) {
                        let oldD = data.old.details,
                            newD = data.new.details;
                        result += "\n" + 'details - ';
                        if (!oldD || !newD) {
                            result += '"' + oldD + '" -> "' + newD + '"';
                        } else {
                            let i = 0;
                            while (oldD[i] === newD[i]) {
                                i++;
                            }
                            result += 'near "' + oldD.slice(i, i + 25) + '" -> "' + newD.slice(i, i + 25) + '"';
                        }
                    }
                    if (data.old.schedule !== data.new.schedule) {
                        result += "\n" + 'schedule - "' + data.old.schedule + '" -> "' + data.new.schedule + '"';
                    }
                    if (data.old.importance !== data.new.importance) {
                        result += "\n" + 'importance - "' +
                            this.importanceText(data.old.importance) + '" -> "' +
                            this.importanceText(data.new.importance) + '"';
                    }
                    if (data.old.status !== data.new.status) {
                        result += "\n" + 'status - "' +
                            this.statusText(data.old.status) + '" -> "' +
                            this.statusText(data.new.status) + '"';
                    }
                    if (data.old.user_id !== data.new.user_id) {
                        result += "\n" + 'performer - "' +
                            data.old.user.name + (data.old.user.surname ? ' ' + data.old.user.surname : '') + '" -> "' +
                            data.new.user.name + (data.new.user.surname ? ' ' + data.new.user.surname : '') + '"';
                    }
                    if (data.old.project_id !== data.new.project_id) {
                        result += "\n" + 'project - "' +
                            data.old.project.name + '" -> "' +
                            data.new.project.name + '"';
                    }

                    // todo small length text

                    return result;
            }
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
</style>
