<template>
    <div><div class="page-heading"><div><div class="eyebrow">OWNER WORKSPACE</div><h1>My shops</h1><p>Choose a shop to manage its products and shop-specific details.</p></div></div>
        <div v-if="error" class="alert alert-danger">{{ error }} <button class="btn btn-sm btn-outline-danger ms-2" @click="load">Try again</button></div>
        <div v-if="loading" class="owner-shop-grid"><div v-for="item in 2" :key="item" class="owner-shop-card">Loading shop...</div></div>
        <div v-else-if="shops.length" class="owner-shop-grid"><article v-for="shop in shops" :key="shop.id" class="owner-shop-card"><div class="owner-shop-mark">{{ shop.name.slice(0, 1).toUpperCase() }}</div><div class="owner-shop-info"><span class="status-pill" :class="shop.is_active ? 'is-active' : 'is-inactive'">{{ shop.is_active ? 'Active' : 'Inactive' }}</span><h2>{{ shop.name }}</h2><p>{{ shop.description || 'Manage products assigned to this shop.' }}</p><small>{{ shop.listings_count }} product {{ shop.listings_count === 1 ? 'listing' : 'listings' }}</small></div><div class="owner-shop-actions"><RouterLink class="btn btn-primary" :to="`/owner/shops/${shop.id}/products`">Manage products</RouterLink><RouterLink class="btn btn-outline-secondary" :to="`/shops/${shop.slug}`">View storefront</RouterLink></div></article></div>
        <div v-else class="storefront-empty"><span class="empty-shop-icon">S</span><h3>No shop assigned yet</h3><p>An administrator needs to assign a shop to your account before you can manage products.</p></div>
    </div>
</template>

<script setup>
import { onMounted, ref } from 'vue';
import api from '../api';

const shops = ref([]); const loading = ref(true); const error = ref('');
async function load() { loading.value = true; error.value = ''; try { shops.value = (await api.get('/owner/data')).data.shops; } catch { error.value = 'Could not load your shops.'; } finally { loading.value = false; } }
onMounted(load);
</script>
