<template>
    <div class="admin-layout">
        <aside class="admin-sidebar">
            <RouterLink class="admin-brand" to="/admin"><span class="admin-brand-mark">S</span><span><strong>ShopSuite</strong><small>Management</small></span></RouterLink>
            <div class="sidebar-caption">WORKSPACE</div>
            <nav class="admin-nav" aria-label="Admin navigation">
                <RouterLink to="/admin" exact-active-class="active"><span class="nav-icon">OV</span>Overview</RouterLink>
                <RouterLink to="/admin/shops" active-class="active"><span class="nav-icon">SH</span>Shops</RouterLink>
                <RouterLink to="/admin/products" active-class="active"><span class="nav-icon">PR</span>Product catalog</RouterLink>
                <RouterLink to="/admin/owners" active-class="active"><span class="nav-icon">OW</span>Shop owners</RouterLink>
            </nav>
            <div class="sidebar-bottom"><RouterLink to="/"><span class="nav-icon">↗</span>View storefront</RouterLink></div>
        </aside>
        <div class="admin-main">
            <header class="admin-topbar"><div><span class="topbar-eyebrow">ADMINISTRATION</span><strong>{{ pageTitle }}</strong></div><div class="admin-user"><span class="avatar-circle">{{ initial }}</span><span class="admin-user-name">{{ auth.user?.name }}</span><button class="btn btn-sm btn-outline-secondary" @click="logout">Log out</button></div></header>
            <main class="admin-content"><div v-if="notice" class="alert alert-success alert-dismissible">{{ notice }}<button class="btn-close" aria-label="Dismiss" @click="notice = ''"></button></div><RouterView /></main>
        </div>
    </div>
</template>

<script setup>
import { computed, ref, provide } from 'vue';
import { useRoute, useRouter } from 'vue-router';
import { auth, signOut } from '../auth';

const route = useRoute();
const router = useRouter();
const notice = ref('');
const pageTitle = computed(() => ({ 'admin.dashboard': 'Overview', 'admin.shops': 'Shops', 'admin.products': 'Product catalog', 'admin.owners': 'Shop owners' }[route.name] || 'Admin panel'));
const initial = computed(() => auth.user?.name?.charAt(0)?.toUpperCase() || 'A');

function showNotice(message) { notice.value = message; }
provide('notify', showNotice);

async function logout() {
    await signOut();
    router.push('/');
}
</script>
