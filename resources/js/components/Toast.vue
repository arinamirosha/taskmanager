<template>
    <div class="toast d-none" role="alert" data-delay="2000">
        <div class="toast-header">
            <strong class="mr-auto">Task Manager Info</strong>
        </div>
        <div class="toast-body">
            {{body}}
        </div>
    </div>
</template>

<style>
.toast {
    position: absolute;
    top: 0;
    right: 30px;
    min-width: 300px;
}
</style>

<script>
    export default {
        props: ['body'],
        mounted() {
            let toastEl = $('.toast');

            toastEl.on('hidden.bs.toast', function () {
                $(this).addClass('d-none');
            });

            toastEl.on('show.bs.toast', function () {
                $(this).removeClass('d-none');
            });
        }
    }
</script>
