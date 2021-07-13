<template>
    <div class="container-xl mb-3">
        <div class="row">
            <div class="col-12 font-weight-bold h3">History</div>
        </div>
        <div v-if="isDataLoaded" v-for="(notification, index) in notifications" class="row p-1" :class="{'bg-light': index % 2 === 0}">
            <div class="col-10" style="white-space: pre-line">{{getNotificationText(notification)}}</div>
            <div class="col-2">{{formatDate(notification.created_at)}}</div>
        </div>
    </div>
</template>

<script>
import route from "../../route";
import constantsMixin from "../mixins/constants";
import moment from "moment";

export default {
    mixins: [
        constantsMixin,
    ],
    data() {
        return {
            notifications: [],
            isDataLoaded: false,
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
                    this.notifications = response.data;
                    this.isDataLoaded = true;
                })
                .catch(error => {
                    console.log(error);
                });
        },
        // getDifference(a, b) {
        //     let i = 0,
        //         j = 0,
        //         result = "";
        //
        //     while (j < b.length)
        //     {
        //         if (a[i] !== b[j] || i === a.length)
        //             result += b[j];
        //         else
        //             i++;
        //         j++;
        //     }
        //     return result;
        // },
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
                            result += '"' + oldD.slice(i, i + 25) + '" -> "' + newD.slice(i, i + 25) + '"';
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

</style>
