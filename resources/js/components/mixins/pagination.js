export default {
    data() {
        return {
            page: 0,
            lastPage: 0,
            isLastPage: false,
        }
    },
    methods: {
        firstLoad(data) {
            this.isLastPage = data.current_page === data.last_page;
            this.page = 1;
            this.lastPage = data.last_page;
        },
        loadedMore(data) {
            this.isLastPage = data.current_page === data.last_page;
        },
    },
}
