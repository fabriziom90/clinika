<script setup>
import ApplicationLogo from "./ApplicationLogo.vue";
import { Link, usePage } from "@inertiajs/vue3";
import { computed } from "vue";

const page = usePage();
const user = computed(() => page.props.auth.user);

const hasRole = (role) => user.value?.roles?.includes(role);

const isRouteActive = (routes) => {
    let flag = false;
    routes.forEach((route) => {
        if (window.location.pathname.includes(route)) {
            flag = true;
        }
    });
    return flag;
};

import { router } from "@inertiajs/vue3";

const logout = () => {
    router.post(
        route("logout"),
        {},
        {
            preserveScroll: true,
            onError: (error) => {
                console.error("Errore durante il logout:", error);
            },
        }
    );
};
</script>
<template lang="">
    <div class="top-bar">
        <ul class="d-flex align-items-center m-0">
            <li class="list-item">
                <ApplicationLogo
                    :width="'75px'"
                    :color="'#c53238'"
                    :fontSize="'20px'"
                />
            </li>
            <li class="list-item"><a href="#">Agenda</a></li>
            <li class="list-item submenu-open" v-if="hasRole('superadmin')">
                <a
                    href="#"
                    :class="
                        isRouteActive(['doctors', 'nurses']) ? 'active' : ''
                    "
                    >Utenti</a
                >
                <ul class="submenu">
                    <li class="list-item">
                        <Link
                            :class="isRouteActive(['doctors']) ? 'active' : ''"
                            :href="route('admin.doctors.index')"
                            >Dottori</Link
                        >
                    </li>
                    <li class="list-item">
                        <Link
                            :class="isRouteActive(['nurses']) ? 'active' : ''"
                            :href="route('admin.nurses.index')"
                            >Infermieri</Link
                        >
                    </li>
                </ul>
            </li>
            <li
                class="list-item"
                v-if="hasRole('superadmin') || hasRole('doctor')"
            >
                <Link
                    :class="isRouteActive(['patients']) ? 'active' : ''"
                    :href="route('admin.patients.index')"
                    >Pazienti</Link
                >
            </li>
            <li class="list-item" v-if="hasRole('superadmin')">
                <a href="#">Fatture</a>
            </li>
            <li class="list-item submenu-open" v-if="hasRole('superadmin')">
                <a
                    href="#"
                    :class="
                        isRouteActive(['specialties', 'roles']) ? 'active' : ''
                    "
                    >Amministrazione</a
                >
                <ul class="submenu">
                    <li class="list-item">
                        <Link
                            :class="isRouteActive(['roles']) ? 'active' : ''"
                            :href="route('admin.roles-permissions.index')"
                            >Ruoli</Link
                        >
                    </li>
                    <li class="list-item">
                        <Link
                            :class="
                                isRouteActive(['specialties']) ? 'active' : ''
                            "
                            :href="route('admin.specialties.index')"
                            >Specializzazioni</Link
                        >
                    </li>
                </ul>
            </li>
        </ul>
        <button class="main-button" @click="logout()">Logout</button>
    </div>
</template>
<style lang="scss" scoped>
@use "../../scss/app.scss" as *;
@use "../../scss/_partials/variables" as *;

.top-bar {
    background-color: #fff;
    border-bottom: $mainRed;
    box-shadow: $mainRed 0px 0px 10px 0px;
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 0px 10px;
    height: 100px;
}

ul {
    list-style-type: none;
    padding: 0px 0px;

    li {
        padding: 0px 20px;

        // &:not(:first-child) {
        //     margin-top: -7px;
        // }

        a {
            color: $mainRed;
            font-weight: 600;
            font-size: 20px;

            &.active,
            &:hover {
                color: $mainGreen;
            }
        }

        .submenu {
            display: none;

            &::before {
                content: "";
                position: absolute;
                top: -6px; // posizione sopra il bordo
                left: 20px; // regola per centrarlo rispetto al link
                width: 10px;
                height: 10px;
                background-color: #fff;
                border-left: 1px solid $mainGrey;
                border-top: 1px solid $mainGrey;
                transform: rotate(45deg);
            }
        }

        .submenu-open {
            display: relative;
        }

        &.submenu-open:hover > .submenu {
            display: block;
            position: absolute;
            background-color: #fff;
            border: 1px solid $mainGrey;
            padding: 0px;

            li {
                padding: 20px 30px;
                &:not(:last-child) {
                    border-bottom: 1px solid $mainRed;
                }
            }
        }
    }
}

.main-button {
    margin-top: -10px2;
}
</style>
