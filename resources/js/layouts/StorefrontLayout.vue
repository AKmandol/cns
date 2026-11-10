<template>
    <div class="storefront-app">
        <nav class="navbar navbar-expand-lg storefront-navbar"><div class="container">
            <RouterLink class="navbar-brand storefront-brand" to="/"><span class="storefront-logo">S</span><span>ShopSuite<small>LOCAL MARKETPLACE</small></span></RouterLink>
            <div class="d-flex align-items-center gap-2 gap-md-3 storefront-nav-actions"><RouterLink class="storefront-nav-link" to="/">Browse shops</RouterLink>
                <RouterLink v-if="auth.user?.role === 'admin'" class="storefront-nav-link" to="/admin">Admin panel</RouterLink><RouterLink v-else-if="auth.user?.role === 'owner'" class="storefront-nav-link" to="/owner">My shops</RouterLink>
                <span v-if="auth.user" class="storefront-user d-none d-md-inline">{{ auth.user.name }}</span>
                <button v-if="auth.user" class="btn btn-sm btn-outline-secondary" @click="logout">Log out</button><RouterLink v-else class="btn btn-sm btn-outline-primary" to="/login">Sign in</RouterLink>
            </div>
        </div></nav>
        <main class="container storefront-main py-4"><RouterView /></main>
    </div>
</template>

<script setup>
import { useRouter } from 'vue-router';
import { auth, signOut } from '../auth';

const router = useRouter();
async function logout() { await signOut(); router.push('/'); }
</script>
