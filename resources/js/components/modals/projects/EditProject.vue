<template>
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Edit Project</h5>
            </div>
            <form @submit.prevent="updateProject">
                <div class="modal-body">
                    <div class="form-group">
                        <label for="name">Name</label>
                        <input class="form-control" id="name" v-model="name" :class="{'is-invalid': this.$v.name.$error}">
                    </div>
                    <div class="custom-control custom-switch">
                        <input type="checkbox" class="custom-control-input" id="favorite" v-model="favorite">
                        <label class="custom-control-label" for="favorite" :class="{'text-danger': this.$v.favorite.$error}">Add To Favorites</label>
                    </div>
                    <div class="form-group pt-2">
                        <label for="color" class="form-label" :class="{'text-danger': this.$v.color.$error}">Color</label>
                        <input type="color" class="form-control form-control-color p-0 w-25" id="color" v-model="color">
                    </div>
                </div>

                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal" @click="reset" ref="cancel">Cancel</button>
                    <button type="submit" class="btn btn-primary" :disabled="isUpdateBtnDisabled">Update</button>
                </div>
            </form>
        </div>
    </div>
</template>

<script>
import { required, maxLength } from 'vuelidate/lib/validators';
import route from "../../../route";

export default {
    props: ['project'],
    data() {
        return {
            name: '',
            favorite: false,
            color: '#bababa',
            isUpdateBtnDisabled: false
        }
    },
    watch: {
        project: function() {
            this.reset();
        }
    },
    validations: {
        name: {
            required,
            maxLength: maxLength(100),
        },
        favorite: {
            boolean: value => typeof value === 'boolean',
        },
        color: {
            color: value => /^#([0-9A-F]{3}){1,2}$/i.test(value),
        },
    },
    methods: {
        updateProject(e) {
            this.$v.$touch();
            if (!this.$v.$invalid) {
                this.isUpdateBtnDisabled = true;
                this.$refs.cancel.disabled = true;
                axios
                    .post(route('projects.update', this.project.id), {
                        'name': this.name,
                        'favorite': this.favorite,
                        'color': this.color,
                    })
                    .then(response => {
                        this.project = response.data;
                        this.$refs.cancel.disabled = false;
                        this.$refs.cancel.click();
                        this.$emit('updated', response.data.id);
                        this.isUpdateBtnDisabled = false;
                    })
                    .catch(error => {
                        console.log(error)
                    });
            }
        },
        reset() {
            this.name = this.project.name;
            this.favorite = Boolean(this.project.favorite);
            this.color = this.project.color;
            this.$v.$reset();
        },
    },
    mounted() {
        this.reset();
    }
}
</script>
