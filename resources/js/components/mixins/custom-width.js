export default {
    data() {
        return {
            width: 0,
            widthXLarge: 1199,
            widthMedium: 767,
        }
    },
    computed: {
        largeStyle: function () {
            return this.width > this.widthXLarge;
        },
        compactStyle:function () {
            return this.width <= this.widthMedium;
        },
        mediumStyle:function () {
            return this.width > this.widthMedium;
        },
    },
    created() {
        window.addEventListener('resize', this.updateWidth);
    },
    methods: {
        updateWidth() {
            this.width = window.innerWidth;
        },
    },
    mounted() {
        this.updateWidth();
    }
}
