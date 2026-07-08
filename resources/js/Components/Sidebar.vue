<script setup>
import { computed } from "vue";
import { Link, usePage } from "@inertiajs/vue3";
import { sidebarMenus } from "@/data/sidebarMenu.js";
import { useConfigStore } from "@/stores/main";

// props
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

const { user, hasRole } = useConfigStore();

const page = usePage();
const currentRouteName = computed(() => page.props.currentRouteName);

// menu logic
const currentMenu = computed(() => {
    const section = sidebarMenus[props.currentSection];

    if (!section) return [];

    // superadmin sees all
    if (props.userRole === "superadmin") {
        return section;
    }

    const menu = section
        .filter((s) => !s.roles || s.roles.includes(props.userRole))
        .map((s) => ({
            ...s,
            links: s.links.filter(
                (l) => !l.roles || l.roles.includes(props.userRole)
            ),
        }));

    // other roles sees only some route
    return section
        .filter((s) => !s.roles || s.roles.includes(props.userRole))
        .map((s) => ({
            ...s,
            links: s.links.filter(
                (l) => !l.roles || l.roles.includes(props.userRole)
            ),
        }));
});

const isRouteActive = (routeName) => currentRouteName.value === routeName;
</script>

<template>
    <div id="sidebar">
        <div id="top-sidebar">

            <div v-for="section in currentMenu" :key="section.id" class="menu-section">
                <h3>
                    <i :class="`${section.icon} me-2`"></i>{{ section.title }}
                </h3>
                <hr />
                <ul>
                    <li :class="isRouteActive(link.route) ? 'active' : ''" v-for="link in section.links"
                        :key="link.route">
                        <Link :href="route(link.route)">
                            <i :class="`${link.icon} me-2`"></i>{{ link.name }}
                        </Link>
                    </li>
                </ul>
            </div>
        </div>
        <div id="bottom-sidebar">
            <Link :href="route('profile.show')" class="button-profile">
                <i class="fas fa-user fa-2xl me-3"></i>Benvenuto<br />
                {{ page.props.auth.user.name }}<br />
                {{ page.props.auth.user.surname }}
            </Link>
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

    .button-profile {
        display: flex;
        align-items: center;
        justify-content: center;
        box-shadow: inset 0px 0px 0px 5px #fff;
        border: 5px solid $mainRed;

        color: #fff;
        transition: 0.3s;
        padding: 5px 0px;

        &:hover {
            background-color: #fff;
            color: $mainRed;
        }
    }

    #top-sidebar {
        height: calc(100% - 130px);
    }

    h3 {
        margin: 20px 10px;
    }

    hr {
        margin: 0px;
        color: white;
        height: 1px;
    }

    ul {
        list-style-type: none;
        padding: 0;
        margin-top: 20px;

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
