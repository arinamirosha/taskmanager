<template>
    <div class="container-xl mb-3">
        <div class="row">
            <div class="col-12 font-weight-bold h3">History</div>
        </div>
        <div v-if="isDataLoaded" v-for="(notification, index) in notifications" class="row p-1" :class="{'bg-light': index % 2 === 0}">
            <div class="col-8" style="white-space: pre-line">{{getNotificationText(notification)}}</div>
            <div class="col-4">{{notification.created_at}}</div>
        </div>
    </div>
</template>

<script>
import route from "../../route";
import constantsMixin from "../mixins/constants";

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
                        // todo show only difference
                        result += "\n" + 'details - "' + data.old.details + '" -> "' + data.new.details + '"';
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
                    // project_id

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
