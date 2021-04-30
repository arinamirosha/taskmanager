<template>
    <div class="container">
        <div class="row justify-content-center">
            <transition name="welcome" appear>
                <div class="col-md-12 p-5 m-5 h1 text-center">
                    Welcome to TaskMaker!
                </div>
            </transition>
        </div>
    </div>
</template>

<script>
export default {
    mounted() {
        // console.log('Component mounted.');
    }
}
</script>
