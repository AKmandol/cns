<template>
    <div><RouterLink to="/" class="back-to-shops"><span>&larr;</span> All shops</RouterLink>
        <section v-if="shop" class="shop-hero"><div class="shop-hero-mark">{{ shop.name.slice(0, 1).toUpperCase() }}</div><div class="shop-hero-copy"><span class="storefront-kicker">SHOP COLLECTION</span><h1>{{ shop.name }}</h1><p>{{ shop.description || 'Take a look through the products available from this shop.' }}</p><div class="shop-meta"><span><i class="shop-meta-dot"></i>{{ products.length }} {{ products.length === 1 ? 'product' : 'products' }}</span><span v-if="shop.address"><i class="shop-location-pin">⌖</i>{{ shop.address }}</span></div></div></section>
        <section class="product-collection"><div class="storefront-section-heading"><div><span class="storefront-kicker">CURATED FOR YOU</span><h2>Available products</h2></div></div>
            <div v-if="loading" class="storefront-empty"><span class="spinner-border spinner-border-sm text-primary"></span><p>Loading products...</p></div>
            <div v-else-if="error" class="alert alert-danger">{{ error }} <RouterLink to="/">Browse other shops</RouterLink></div>
            <div v-else-if="products.length" class="row g-3 g-xl-4"><div v-for="listing in products" :key="listing.id" class="col-6 col-lg-4 col-xl-3"><article class="product-card h-100"><div class="product-image-wrap"><img v-if="listing.thumbnail" :src="`/storage/${listing.thumbnail}`" class="product-image" :alt="listing.product.name"><div v-else class="product-image-placeholder"><span>{{ listing.product.name.slice(0, 1).toUpperCase() }}</span></div></div><div class="product-card-body"><span class="product-available"><i></i> Available</span><h3>{{ listing.product.name }}</h3><p>{{ listing.description || listing.product.description || 'A product selected by this shop.' }}</p><div class="product-price">৳{{ Number(listing.price).toLocaleString(undefined, { minimumFractionDigits: 2, maximumFractionDigits: 2 }) }}</div></div></article></div></div>
            <div v-else class="storefront-empty"><span class="empty-shop-icon">{{ shop?.name?.slice(0, 1).toUpperCase() || 'S' }}</span><h3>Nothing on the shelves yet</h3><p>This shop hasn’t listed any products. Please check back later.</p></div>
        </section>
    </div>
</template>

<script setup>
import { onMounted, ref, watch } from 'vue';
import api from '../api';

const props = defineProps({ slug: { type: String, required: true } });
const shop = ref(null);
const products = ref([]);
const loading = ref(true);
const error = ref('');

async function loadShop() {
    loading.value = true;
    error.value = '';
    try {
        const { data } = await api.get(`/data/shops/${encodeURIComponent(props.slug)}`);
        shop.value = data.shop;
        products.value = data.products;
    } catch (exception) {
        error.value = exception.response?.status === 404 ? 'This shop is unavailable.' : 'We could not load this shop right now.';
    } finally { loading.value = false; }
}

onMounted(loadShop);
watch(() => props.slug, loadShop);
</script>
