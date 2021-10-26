<template>
    <form ref="form">
        <b-form-group
            label="Name"
            label-for="name-input"
            :state="$v.$dirty && $v.name.$error ? false : null"
            :invalid-feedback="'1-100 symbols, you have ' + name.length"
        >
            <b-form-input
                id="name-input"
                v-model="name"
                :state="$v.$dirty && $v.name.$error ? false : null"
                required
            ></b-form-input>
        </b-form-group>

        <b-form-checkbox v-model="fav" switch>Add To Favorites</b-form-checkbox>

        <b-form-group label="Color" label-for="color-input" class="w-25 pt-2">
            <b-form-input id="color-input" type="color" v-model="color"></b-form-input>
        </b-form-group>
    </form>
</template>

<script>
import {required, maxLength} from 'vuelidate/lib/validators';
import route from "../../../route";

export default {
    data() {
        return {
            name: '',
            fav: false,
            color: '#bababa',
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
        handleSubmit() {
            this.$v.$touch();
            if (this.$v.$invalid) {
                return
            }
            this.$emit('wait');
            axios
                .post(route('projects.store'), {
                    'name': this.name,
                    'favorite': this.fav,
                    'color': this.color,
                })
                .then(response => {
                    this.$emit('projectStored', response.data.id);
                    this.$nextTick(() => {
                        this.$bvModal.hide('common-modal')
                    })
                })
                .catch(error => {
                    console.log(error)
                });
        },
    },
}
</script>
