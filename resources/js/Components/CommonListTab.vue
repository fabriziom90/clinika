<script setup>
const props = defineProps({
    title: String
})
</script>
<template>
    <div class="card">
        <div class="card-header bg-main-red-light">
            <h2 class="text-white">{{ title }}</h2>
        </div>
        <div class="card-body max-height-500">
            <slot />
        </div>
    </div>
</template>


<style></style>