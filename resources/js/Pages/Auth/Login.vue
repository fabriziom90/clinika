<script setup lang="ts">
import ApplicationLogo from "@/Components/ApplicationLogo.vue";

import { Head, useForm } from "@inertiajs/vue3";
import { ref } from "vue";

defineProps({
    canResetPassword: Boolean,
    status: String,
});

const form = useForm({
    email: "",
    password: "",
});

const loading = ref(false);
const errorMessage = ref("");

const submit = () => {
    loading.value = true;


    form.post("/login", {
        onFinish: () => {
            loading.value = false;
            form.reset("password");
        },
        onError: (errors) => {
            console.log(errors);
            errorMessage.value = errors.email || "Credenziali non valide.";
        },
        onSuccess: () => {
            console.log("Login avvenuto");
        },
    });
};
</script>

<template>

    <Head title="Log in" />
    <div class="vh-100 vw-100 d-flex justify-content-center align-items-center">
        <div class="container">
            <div class="row">
                <div class="col-12">
                    <div class="d-flex justify-content-center align-items-center">
                        <form @submit.prevent="submit" class="form-login">
                            <a href="/">
                                <ApplicationLogo />
                            </a>
                            <div v-if="status" class="mb-4 font-medium text-sm text-green-600">
                                {{ status }}
                            </div>
                            <div class="form-group mt-3">
                                <label class="form-label" for="email">Email</label>
                                <input class="form-control" v-model="form.email" type="email" id="email"
                                    placeholder="Inserisci email" required />
                            </div>

                            <div class="form-group mt-3">
                                <label class="form-label" for="password">Password</label>
                                <input class="form-control" v-model="form.password" type="password" id="password"
                                    placeholder="Inserisci password" required />
                            </div>

                            <div class="form-group mt-3">
                                <button class="main-button" :disabled="loading">
                                    {{ loading ? "Accesso..." : "Accedi" }}
                                </button>
                            </div>

                            <p v-if="errorMessage != ''" class="error">{{ errorMessage }}</p>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<style lang="scss" scoped>
@use "../../../scss/_partials/variables" as *;

.form-login {
    max-width: 450px;
    border: 1px solid $mainGrey;
    padding: 25px;
    border-radius: 10px;
    box-shadow: rgb(174, 174, 174) 0px 0px 25px 10px;
}
</style>
