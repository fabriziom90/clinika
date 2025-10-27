<script setup lang="ts">
import { computed } from "vue";
import logoUrl from "../../assets/logo_clinika.png";

const props = defineProps({
    width: String,
    color: String,
    fontSize: String,
});

const logoStyle = computed(() => {
    return {
        width: props.width || "150px",
    };
});

const titleStyle = computed(() => {
    return {
        color: props.color || "black",
        "font-size": props.fontSize || "35px",
    };
});
</script>

<template>
    <div class="d-flex align-items-center">
        <img :src="logoUrl" :style="logoStyle" alt="Clinka - Elpis Medica" />
        <h2 :style="titleStyle">Clinika</h2>
    </div>
</template>

<style lang="scss" scoped>
h2 {
    font-weight: 600;
    margin-bottom: 0px;
}
</style>
