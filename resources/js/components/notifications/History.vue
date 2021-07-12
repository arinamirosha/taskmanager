<template>
    <div class="container-xl">
        <div class="row">
            <div class="col-12 font-weight-bold h3">History</div>
        </div>
        <div v-if="isDataLoaded" v-for="notification in notifications" class="row">
            <div class="col-8">{{getNotificationText(notification)}}</div>
            <div class="col-4">{{notification.created_at}}</div>
        </div>
    </div>
</template>

<script>
import route from "../../route";

export default {
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
                case 'App\\Notifications\\ProjectStored':
                    return data.user + ' created project "' + data.project + '"';
                case 'App\\Notifications\\ProjectUpdated':
                    return data.user + ' updated ' + data.field + ' project from "' + data.old + '" to "' + data.new + '"';
                case 'App\\Notifications\\ProjectArchived':
                    return data.user + ' archived project "' + data.project + '"';
                case 'App\\Notifications\\ProjectRestored':
                    return data.user + ' restored project "' + data.project + '"';
                case 'App\\Notifications\\ProjectDeleted':
                    return data.user + ' deleted project "' + data.project + '"';
                case 'App\\Notifications\\ProjectShared':
                    return data.user + ' sent share request to ' + data.userShared + ' for project "' + data.project + '"';
                case 'App\\Notifications\\ProjectUnshared':
                    return data.user + ' unshared with ' + data.userUnshared + ' for project "' + data.project + '"';
                case 'App\\Notifications\\ProjectShareDecision':
                    return data.user + ' ' + data.decision + ' shared project "' + data.project + '"';
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
