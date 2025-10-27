<script lang="ts" setup>
import ApplicationLogo from "./ApplicationLogo.vue";
import { Link } from "@inertiajs/vue3";
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
            <li class="list-item submenu-open">
                <a href="#">Utenti</a>
                <ul class="submenu">
                    <li class="list-item"><a href="#">Dottori</a></li>
                    <li class="list-item"><a href="#">Infermieri</a></li>
                </ul>
            </li>
            <li class="list-item">
                <Link :href="route('patients.index')">Pazienti</Link>
            </li>
            <li class="list-item"><a href="#">Fatture</a></li>
            <li class="list-item submenu-open">
                <a href="#">Amministrazione</a>
                <ul class="submenu">
                    <li class="list-item"><a href="#">Ruoli</a></li>
                    <li class="list-item">
                        <Link :href="route('specialties.index')"
                            >Specializzazioni</Link
                        >
                    </li>
                </ul>
            </li>
        </ul>
        <button class="main-button">Logout</button>
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
