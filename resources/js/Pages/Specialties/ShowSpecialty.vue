<script setup>
    import { Head } from '@inertiajs/vue3';
    import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
    
    const props = defineProps({
        specialty: Object
    })

</script>
<template>
    <Head title="Dettaglio specializzazione" />
    <AuthenticatedLayout section="specialties">
        <h2>{{ specialty.name }}</h2>
        <hr>
        <h3>Prestazioni mediche</h3>
        <div class="row">
            <div class="col-12">
                <table class="custom-table">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Codice</th>
                            <th>Nome</th>
                            <th>Durata (minuti)</th>
                            <th>Prezzo</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-for="service in specialty.services" :key="service.code">
                            <td>{{ service.id }}</td>
                            <td>{{ service.code }}</td>
                            <td>{{ service.name }}</td>
                            <td>{{ service.default_duration }}</td>
                            <td>{{ service.default_price }}€</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
<style lang="scss" scoped>
    @use '../../../scss/app.scss';
    @use '../../../scss/_partials/variables' as *;
    
</style>