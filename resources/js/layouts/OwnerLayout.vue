<template>
    <div class="owner-app">
        <header class="owner-topbar"><RouterLink class="storefront-brand" to="/"><span class="storefront-logo">S</span><span>ShopSuite<small>OWNER WORKSPACE</small></span></RouterLink><div class="d-flex align-items-center gap-3"><RouterLink class="storefront-nav-link" to="/owner">My shops</RouterLink><span class="storefront-user d-none d-md-inline">{{ auth.user?.name }}</span><button class="btn btn-sm btn-outline-secondary" @click="logout">Log out</button></div></header>
        <main class="container owner-content"><div v-if="notice" class="alert alert-success">{{ notice }}</div><RouterView /></main>
    </div>
</template>

<script setup>
import { provide, ref } from 'vue';
import { useRouter } from 'vue-router';
import { auth, signOut } from '../auth';

const router = useRouter();
const notice = ref('');
function showNotice(message) { notice.value = message; }
provide('notify', showNotice);
async function logout() { await signOut(); router.push('/'); }
</script>
