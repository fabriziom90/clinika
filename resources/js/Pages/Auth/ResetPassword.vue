<script setup>
import GuestLayout from "@/Layouts/GuestLayout.vue";
import { Head, useForm } from "@inertiajs/vue3";
import ApplicationLogo from "@/Components/ApplicationLogo.vue";

const props = defineProps({
    token: {
        type: String,
        required: true,
    },
});

const form = useForm({
    token: props.token,
    password: "",
    password_confirmation: "",
});

const submit = () => {
    form.post(route("password.store"), {
        onError: (errors) => {
            console.log("Reset password errors:", errors);
        },
        onSuccess: () => {
            console.log("Password modificata");
        },
        onFinish: () => {
            form.reset("password", "password_confirmation");
        },
    });
};
</script>

<template>
    <GuestLayout>

        <Head title="Reset Password" />
        <div class="vh-100 vw-100 d-flex justify-content-center align-items-center">
            <div class="container">
                <div class="row">
                    <div class="col-12">
                        <div class="d-flex justify-content-center align-items-center">
                            <form @submit.prevent="submit" class="form-login">
                                <a href="/">
                                    <ApplicationLogo />
                                </a>

                                <div class="form-group mt-3">
                                    <label for="password" class="form-label">Password</label>

                                    <input id="password" type="password" class="form-control" v-model="form.password"
                                        required autocomplete="new-password" />
                                    <div v-if="form.errors.password" class="text-danger">
                                        {{ form.errors.password }}
                                    </div>
                                </div>


                                <div class="form-group mt-3">
                                    <label for="password_confirmation" class="form-label">Conferma password</label>

                                    <input id="password_confirmation" type="password" class="form-control"
                                        v-model="form.password_confirmation" required autocomplete="new-password" />
                                    <div v-if="form.errors.password_confirmation" class="text-danger small mt-1">
                                        {{ form.errors.password_confirmation }}
                                    </div>
                                </div>

                                <div v-if="form.errors.token" class="text-danger">
                                    {{ form.errors.token }}
                                </div>
                                <div class="flex items-center justify-end mt-4">
                                    <button class="main-button" :disabled="form.processing">
                                        Reset Password
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </GuestLayout>
</template>
<style lang="scss" scoped>
@use "../../../scss/app.scss";
@use "../../../scss/_partials/variables" as *;

.form-login {
    max-width: 450px;
    border: 1px solid $mainGrey;
    padding: 25px;
    border-radius: 10px;
    box-shadow: rgb(174, 174, 174) 0px 0px 25px 10px;
}
</style>
