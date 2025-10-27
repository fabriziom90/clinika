<script setup>
import { computed } from "vue";
import { Link } from "@inertiajs/vue3";
import { sidebarMenus } from "@/data/sidebarMenu.js"; // ← attenzione: ora è .js
// props senza tipi TypeScript
const props = defineProps({
    currentSection: {
        type: String,
        required: true,
    },
    userRole: {
        type: String,
        required: true,
    },
});

// logica menu
const currentMenu = computed(() => {
    const section = sidebarMenus[props.currentSection];
    if (!section) return [];

    // superadmin vede tutto nella sezione corrente
    if (props.userRole === "superadmin") {
        return section;
    }

    // altri ruoli vedono solo quello consentito
    return section
        .filter((s) => !s.roles || s.roles.includes(props.userRole))
        .map((s) => ({
            ...s,
            links: s.links.filter(
                (l) => !l.roles || l.roles.includes(props.userRole)
            ),
        }));
});

const isRouteActive = (route) => {
    console.log(window.location.pathname);
    console.log(route);
    console.log(window.location.pathname.includes(route));
    return window.location.pathname.includes(route);
};
</script>

<template>
    <div id="sidebar">
        <div
            v-for="section in currentMenu"
            :key="section.id"
            class="menu-section"
        >
            <h3><i :class="`${section.icon} me-2`"></i>{{ section.title }}</h3>
            <hr class="text-white" />
            <ul>
                <li
                    :class="isRouteActive(link.path) ? 'active' : ''"
                    v-for="link in section.links"
                    :key="link.route"
                >
                    <Link :href="route(link.route)"
                        ><i :class="`${link.icon} me-2`"></i
                        >{{ link.name }}</Link
                    >
                </li>
            </ul>
        </div>
    </div>
</template>

<style lang="scss" scoped>
@use "../../scss/app.scss";
@use "../../scss/_partials/variables" as *;

#sidebar {
    height: 100%;
    width: 250px;
    background-color: $mainRed;
    color: #fff;

    h3 {
        padding: 10px;
    }

    ul {
        list-style-type: none;
        padding: 0;

        li {
            padding: 10px;

            &.active {
                a {
                    color: $mainRed;
                }
                background-color: #fff;
            }

            a {
                color: #fff;
            }
        }
    }
}
</style>
