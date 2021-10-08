import * as constants from "../../constants";

export default {
    computed: {
        c: function () {
            return constants;
        },
    },
    methods: {
        importanceCss(importance) {
            switch (importance) {
                case this.c.STATUS_NORMAL: return 'text-secondary';
                case this.c.STATUS_MEDIUM: return 'text-primary';
                case this.c.STATUS_STRONG: return 'text-danger';
            }
            return '';
        },
        importanceText(importance) {
            switch (importance) {
                case this.c.STATUS_NORMAL: return 'Normal';
                case this.c.STATUS_MEDIUM: return 'Medium';
                case this.c.STATUS_STRONG: return 'Strong';
            }
        },
        statusText(status) {
            switch (status) {
                case this.c.STATUS_NEW: return 'New';
                case this.c.STATUS_PROGRESS: return 'Progress';
                case this.c.STATUS_FINISHED: return 'Finished';
            }
            return '';
        },
        statusIconClass(status) {
            switch (status) {
                case this.c.STATUS_NEW: return 'fas fa-external-link-alt';
                case this.c.STATUS_PROGRESS: return 'fas fa-spinner';
                case this.c.STATUS_FINISHED: return 'fas fa-check';
            }
            return '';
        },
        statusSharedCss(status) {
            switch (status) {
                case null: return 'text-secondary';
                case 1: return 'text-success';
                case 0: return 'text-danger';
            }
            return '';
        },
        statusSharedText(status) {
            switch (status) {
                case null: return 'Wait';
                case 1: return 'Accepted';
                case 0: return 'Declined';
            }
            return '';
        },
        tasksPageTitle(type) {
            switch (type) {
                case this.c.TODAY: return 'Today tasks';
                case this.c.UPCOMING: return 'Upcoming tasks';
                case this.c.NOT_SCHEDULED: return 'Not Scheduled tasks';
                case this.c.ARCHIVE: return 'Archived Tasks';
            }
            return '';
        }
    },
}
