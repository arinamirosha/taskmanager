<template>
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Create New Project</h5>
            </div>
            <form @submit.prevent="storeProject">
                <div class="modal-body">
                    <div class="form-group">
                        <label for="name">Name</label>
                        <input class="form-control" id="name" v-model="name" :class="{'is-invalid': this.$v.name.$error}">
                    </div>
                    <div class="custom-control custom-switch">
                        <input type="checkbox" class="custom-control-input" id="fav" v-model="fav">
                        <label class="custom-control-label" for="fav" :class="{'text-danger': this.$v.fav.$error}">Add To Favorites</label>
                    </div>
                    <div class="form-group pt-2">
                        <label for="color" class="form-label" :class="{'text-danger': this.$v.color.$error}">Color</label>
                        <input type="color" class="form-control form-control-color p-0 w-25" id="color" v-model="color">
                    </div>
                </div>

                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal" @click="reset" ref="cancel">Cancel</button>
                    <button type="submit" class="btn btn-primary" :disabled="isAddBtnDisabled">Add</button>
                </div>
            </form>
        </div>
    </div>
</template>

<script>
import { required, maxLength } from 'vuelidate/lib/validators';
import route from "../../../route";

export default {
    data() {
        return {
            name: '',
            fav: false,
            color: '#bababa',
            isAddBtnDisabled: false,
        }
    },
    validations: {
        name: {
            required,
            maxLength: maxLength(100),
        },
        fav: {
            boolean: value => typeof value === 'boolean',
        },
        color: {
            color: value => /^#([0-9A-F]{3}){1,2}$/i.test(value),
        },
    },
    methods: {
        storeProject(e) {
            this.$v.$touch();
            if (!this.$v.$invalid) {
                this.isAddBtnDisabled = true;
                this.$refs.cancel.disabled = true;
                axios
                    .post(route('projects.store'), {
                        'name': this.name,
                        'favorite': this.fav,
                        'color': this.color,
                    })
                    .then(response => {
                        this.$refs.cancel.disabled = false;
                        this.$refs.cancel.click();
                        this.$emit('stored', response.data.id);
                        this.isAddBtnDisabled = false;
                    })
                    .catch(error => {
                        console.log(error)
                    });
            }
        },
        reset() {
            this.name = '';
            this.fav = false;
            this.color = '#bababa';
            this.$v.$reset();
        },
    },
}
</script>
