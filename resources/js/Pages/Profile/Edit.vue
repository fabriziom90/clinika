<script setup>
import { Head, useForm } from "@inertiajs/vue3";
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";

const props = defineProps({
    user: {
        type: Object,
        required: true,
    },
    isAdmin: {
        type: Boolean,
        required: true,
    },
    clinic: {
        type: Object,
        default: null,
    },
});

const profileForm = useForm({
    name: props.user.name,
    surname: props.user.surname,
    email: props.user.email,
    clinic_name: props.clinic?.name ?? "",
    clinic_email: props.clinic?.email ?? "",
    clinic_phone: props.clinic?.phone ?? "",
    clinic_address: props.clinic?.address ?? "",
    clinic_city: props.clinic?.city ?? "",
    clinic_province: props.clinic?.province ?? "",
    clinic_zip_code: props.clinic?.zip_code ?? "",
    clinic_vat_number: props.clinic?.vat_number ?? "",
    clinic_tax_code: props.clinic?.tax_code ?? "",
    clinic_logo: null,
});

const passwordForm = useForm({
    current_password: "",
    password: "",
    password_confirmation: "",
});

const updateProfile = () => {
    profileForm
        .transform((data) => ({
            ...data,
            _method: "patch",
        }))
        .post(route("profile.update"), {
            forceFormData: true,
            preserveScroll: true,
        });
};

const updatePassword = () => {
    passwordForm.patch(route("profile.password.update"), {
        preserveScroll: true,
        onSuccess: () => {
            passwordForm.reset();
        },
    });
};

const handleLogoChange = (event) => {
    profileForm.clinic_logo = event.target.files[0];
};
</script>

<template>

    <Head title="Profilo" />

    <AuthenticatedLayout section="profile">
        <div class="container-fluid">
            <div class="row gy-5">
                <div class="col-12">
                    <h2>Profilo</h2>
                </div>

                <!-- DATI UTENTE -->
                <div class="col-12 col-lg-6">
                    <div class="profile-box">
                        <h3>Informazioni personali</h3>

                        <form @submit.prevent="updateProfile">
                            <div class="row gy-3">
                                <div class="col-12 col-md-6">
                                    <label class="form-label">
                                        Nome
                                    </label>

                                    <input v-model="profileForm.name" type="text" class="form-control" />

                                    <div v-if="profileForm.errors.name" class="text-danger mt-1">
                                        {{ profileForm.errors.name }}
                                    </div>
                                </div>

                                <div class="col-12 col-md-6">
                                    <label class="form-label">
                                        Cognome
                                    </label>

                                    <input v-model="profileForm.surname" type="text" class="form-control" />

                                    <div v-if="profileForm.errors.surname" class="text-danger mt-1">
                                        {{ profileForm.errors.surname }}
                                    </div>
                                </div>

                                <div class="col-12">
                                    <label class="form-label">
                                        Email
                                    </label>

                                    <input v-model="profileForm.email" type="email" class="form-control" />

                                    <div v-if="profileForm.errors.email" class="text-danger mt-1">
                                        {{ profileForm.errors.email }}
                                    </div>
                                </div>

                                <div class="col-12">
                                    <button type="submit" class="main-button" :disabled="profileForm.processing">
                                        {{
                                            profileForm.processing
                                                ? "Salvataggio..."
                                                : "Salva modifiche"
                                        }}
                                    </button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>

                <!-- DATI CLINICA -->
                <div v-if="isAdmin && clinic" class="col-12">
                    <div class="profile-box">
                        <h3>Dati della clinica</h3>

                        <form @submit.prevent="updateProfile" enctype="multipart/form-data">
                            <div class="row gy-3">
                                <div class="col-12 col-md-6">
                                    <label class="form-label">
                                        Nome clinica
                                    </label>

                                    <input v-model="profileForm.clinic_name" type="text" class="form-control" />

                                    <div v-if="profileForm.errors.clinic_name" class="text-danger mt-1">
                                        {{ profileForm.errors.clinic_name }}
                                    </div>
                                </div>

                                <div class="col-12 col-md-6">
                                    <label class="form-label">
                                        Email
                                    </label>

                                    <input v-model="profileForm.clinic_email" type="email" class="form-control" />
                                </div>

                                <div class="col-12 col-md-6">
                                    <label class="form-label">
                                        Telefono
                                    </label>

                                    <input v-model="profileForm.clinic_phone" type="text" class="form-control" />
                                </div>

                                <div class="col-12 col-md-6">
                                    <label class="form-label">
                                        Indirizzo
                                    </label>

                                    <input v-model="profileForm.clinic_address" type="text" class="form-control" />
                                </div>

                                <div class="col-12 col-md-5">
                                    <label class="form-label">
                                        Città
                                    </label>

                                    <input v-model="profileForm.clinic_city" type="text" class="form-control" />
                                </div>

                                <div class="col-12 col-md-2">
                                    <label class="form-label">
                                        Provincia
                                    </label>

                                    <input v-model="profileForm.clinic_province" type="text" class="form-control" />
                                </div>

                                <div class="col-12 col-md-5">
                                    <label class="form-label">
                                        CAP
                                    </label>

                                    <input v-model="profileForm.clinic_zip_code" type="text" class="form-control" />
                                </div>

                                <div class="col-12 col-md-6">
                                    <label class="form-label">
                                        Partita IVA
                                    </label>

                                    <input v-model="profileForm.clinic_vat_number" type="text" class="form-control" />
                                </div>

                                <div class="col-12 col-md-6">
                                    <label class="form-label">
                                        Codice fiscale
                                    </label>

                                    <input v-model="profileForm.clinic_tax_code" type="text" class="form-control" />
                                </div>

                                <div class="col-12">
                                    <label class="form-label">
                                        Logo
                                    </label>

                                    <input type="file" class="form-control" accept="image/*"
                                        @change="handleLogoChange" />
                                </div>

                                <div v-if="clinic.logo_path" class="col-12">
                                    <label class="form-label">
                                        Logo attuale
                                    </label>

                                    <div>
                                        <img :src="`/storage/${clinic.logo_path}`" alt="Logo clinica"
                                            class="clinic-logo" />
                                    </div>
                                </div>

                                <div class="col-12">
                                    <button type="submit" class="main-button" :disabled="profileForm.processing">
                                        {{
                                            profileForm.processing
                                                ? "Salvataggio..."
                                                : "Salva dati clinica"
                                        }}
                                    </button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>

                <!-- PASSWORD -->
                <div class="col-12 col-lg-6">
                    <div class="profile-box">
                        <h3>Modifica password</h3>

                        <form @submit.prevent="updatePassword">
                            <div class="row gy-3">
                                <div class="col-12">
                                    <label class="form-label">
                                        Password attuale
                                    </label>

                                    <input v-model="passwordForm.current_password" type="password" class="form-control"
                                        autocomplete="current-password" />

                                    <div v-if="passwordForm.errors.current_password" class="text-danger mt-1">
                                        {{
                                            passwordForm.errors
                                                .current_password
                                        }}
                                    </div>
                                </div>

                                <div class="col-12">
                                    <label class="form-label">
                                        Nuova password
                                    </label>

                                    <input v-model="passwordForm.password" type="password" class="form-control"
                                        autocomplete="new-password" />

                                    <div v-if="passwordForm.errors.password" class="text-danger mt-1">
                                        {{ passwordForm.errors.password }}
                                    </div>
                                </div>

                                <div class="col-12">
                                    <label class="form-label">
                                        Conferma nuova password
                                    </label>

                                    <input v-model="passwordForm.password_confirmation
                                        " type="password" class="form-control" autocomplete="new-password" />

                                    <div v-if="
                                        passwordForm.errors
                                            .password_confirmation
                                    " class="text-danger mt-1">
                                        {{
                                            passwordForm.errors
                                                .password_confirmation
                                        }}
                                    </div>
                                </div>

                                <div class="col-12">
                                    <button type="submit" class="main-button" :disabled="passwordForm.processing">
                                        {{
                                            passwordForm.processing
                                                ? "Salvataggio..."
                                                : "Modifica password"
                                        }}
                                    </button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>

<style lang="scss" scoped>
@use "../../../scss/app.scss";
@use "../../../scss/_partials/variables" as *;

.profile-box {
    padding: 25px;
    background: #fff;
    border-radius: 8px;
    box-shadow: 0 2px 10px rgba(0, 0, 0, 0.08);

    h3 {
        color: $mainRed;
        margin-bottom: 25px;
    }

    label {
        color: $mainRed;
        font-weight: bold;
    }
}

.clinic-logo {
    max-width: 200px;
    max-height: 100px;
    object-fit: contain;
}
</style>