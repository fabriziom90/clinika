import 'bootstrap/dist/css/bootstrap.min.css'
import '../scss/app.scss';
import '@fortawesome/fontawesome-free/css/all.css'
import 'vue-toast-notification/dist/theme-bootstrap.css';

import { createApp, h } from 'vue';
import { createInertiaApp } from '@inertiajs/vue3';
import { resolvePageComponent } from 'laravel-vite-plugin/inertia-helpers';

import { ZiggyVue } from '../../vendor/tightenco/ziggy';
import { createPinia } from 'pinia';

import loading from './directives/loading';

const appName = import.meta.env.VITE_APP_NAME || 'Laravel';

createInertiaApp({
    title: (title) => `${title} - ${appName}`,
    resolve: (name) => resolvePageComponent(`./Pages/${name}.vue`, import.meta.glob('./Pages/**/*.vue')),
    setup({ el, App, props, plugin }) {
        const pinia = createPinia();
        return createApp({ render: () => h(App, props) })
            .use(pinia)
            .use(plugin)
            .use(ZiggyVue)
            .directive('loading', loading)
            .mount(el);

            
    },
    progress: {
        color: '#c53238',
    },
});
