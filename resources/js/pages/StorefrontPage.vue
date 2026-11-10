<template>
    <div>
        <section class="storefront-hero"><div class="storefront-hero-copy"><span class="storefront-kicker">A MARKETPLACE FOR EVERYDAY FINDS</span><h1>Find a shop<br>you’ll love.</h1><p>Explore independent shops and see what’s available in each one.</p><a href="#shops" class="hero-link">Explore shops <span>&darr;</span></a></div>
            <div class="hero-art" aria-hidden="true"><span class="hero-orbit orbit-one"></span><span class="hero-orbit orbit-two"></span><span class="hero-bag"><i></i><b></b></span><span class="hero-spark spark-one">✦</span><span class="hero-spark spark-two">✦</span></div><div class="hero-count"><strong>{{ shops.length }}</strong><span>active<br>{{ shops.length === 1 ? 'shop' : 'shops' }}</span></div>
        </section>
        <section id="shops" class="shop-directory"><div class="storefront-section-heading"><div><span class="storefront-kicker">THE MARKETPLACE</span><h2>Explore our shops</h2><p>Pick a shop to browse its current collection.</p></div><span class="shop-count-tag">{{ shops.length }} {{ shops.length === 1 ? 'shop' : 'shops' }}</span></div>
            <div v-if="loading" class="storefront-empty"><span class="spinner-border spinner-border-sm text-primary"></span><p>Loading shops...</p></div>
            <div v-else-if="error" class="alert alert-danger">{{ error }} <button class="btn btn-sm btn-outline-danger ms-2" @click="loadShops">Try again</button></div>
            <div v-else-if="shops.length" class="row g-3 g-xl-4"><div v-for="(shop, index) in shops" :key="shop.id" class="col-sm-6 col-xl-4"><article class="shop-directory-card h-100"><div class="shop-card-top"><span class="shop-mark" :class="markStyles[index % markStyles.length]">{{ shop.name.slice(0, 1).toUpperCase() }}</span><span class="shop-open-label"><i></i> Open</span></div><div class="shop-card-content"><h3>{{ shop.name }}</h3><p>{{ shop.description || 'A local shop with a collection picked just for you.' }}</p></div><div class="shop-card-footer"><span>{{ shop.active_listings_count }} {{ shop.active_listings_count === 1 ? 'item' : 'items' }} available</span><RouterLink :to="`/shops/${shop.slug}`">Visit shop <span>&rarr;</span></RouterLink></div></article></div></div>
            <div v-else class="storefront-empty"><span class="empty-shop-icon">S</span><h3>Shops are getting ready</h3><p>There are no active shops to browse right now. Please check back soon.</p></div>
        </section>
    </div>
</template>

<script setup>
import { onMounted, ref } from 'vue';
import api from '../api';

const shops = ref([]);
const loading = ref(true);
const error = ref('');
const markStyles = ['shop-mark-blue', 'shop-mark-lilac', 'shop-mark-peach', 'shop-mark-mint'];

async function loadShops() {
    loading.value = true;
    error.value = '';
    try { shops.value = (await api.get('/data/shops')).data.shops; }
    catch { error.value = 'We could not load shops right now.'; }
    finally { loading.value = false; }
}

onMounted(loadShops);
</script>
