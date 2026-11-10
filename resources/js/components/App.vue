<template>
    <div class="container mt-5">
        <h1>Hello Laravel + Vue 3!</h1>
        <p>{{ message }}</p>
    </div>
</template>

<script setup>
import { ref } from 'vue';

const message = ref('Vue 3 successfully installed!');
</script>
