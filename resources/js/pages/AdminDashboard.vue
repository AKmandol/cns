<template>
    <div>
        <div class="page-heading"><div><div class="eyebrow">OVERVIEW</div><h1>Good {{ greeting }}, {{ auth.user?.name }}</h1><p>Here’s what’s happening across your marketplace today.</p></div><RouterLink class="btn btn-primary" to="/admin/shops">＋ <span class="ms-1">Add a shop</span></RouterLink></div>
        <div v-if="error" class="alert alert-danger">{{ error }} <button class="btn btn-sm btn-outline-danger ms-2" @click="load">Try again</button></div>
        <div class="row g-3 dashboard-stats"><div v-for="stat in stats" :key="stat.label" class="col-6 col-xl-3"><RouterLink v-if="stat.to" class="stat-card" :to="stat.to"><span class="stat-icon" :class="stat.color">{{ stat.code }}</span><span class="stat-label">{{ stat.label }}</span><strong>{{ stat.value }}</strong><small>{{ stat.hint }} <span>→</span></small></RouterLink><div v-else class="stat-card"><span class="stat-icon" :class="stat.color">{{ stat.code }}</span><span class="stat-label">{{ stat.label }}</span><strong>{{ stat.value }}</strong><small>{{ stat.hint }}</small></div></div></div>
        <div class="row g-3 mt-1"><div class="col-lg-7"><section class="panel-card h-100"><div class="panel-heading"><div><h2>Recently added shops</h2><p>Your latest shop accounts</p></div><RouterLink to="/admin/shops">See all shops <span>→</span></RouterLink></div><div v-if="!loading && !data.recentShops?.length" class="empty-state"><span>SH</span><strong>No shops yet</strong><p>Create a shop and assign it to an owner to get started.</p><RouterLink class="btn btn-sm btn-primary" to="/admin/shops">Manage shops</RouterLink></div><div v-else class="table-responsive"><table class="table admin-table mb-0"><thead><tr><th>SHOP</th><th>OWNER</th><th>STATUS</th></tr></thead><tbody><tr v-for="shop in data.recentShops" :key="shop.id"><td><strong>{{ shop.name }}</strong><small>/{{ shop.slug }}</small></td><td>{{ shop.owner?.name }}</td><td><span class="status-pill" :class="shop.is_active ? 'is-active' : 'is-inactive'">{{ shop.is_active ? 'Active' : 'Inactive' }}</span></td></tr><tr v-if="loading"><td colspan="3" class="text-center py-4">Loading shops...</td></tr></tbody></table></div></section></div>
            <div class="col-lg-5"><section class="panel-card h-100"><div class="panel-heading"><div><h2>Quick actions</h2><p>Common admin tasks</p></div></div><div class="quick-actions"><RouterLink to="/admin/shops"><span class="quick-icon stat-blue">＋</span><span><strong>Create a shop</strong><small>Assign a shop to an owner</small></span><b>→</b></RouterLink><RouterLink to="/admin/products"><span class="quick-icon stat-violet">＋</span><span><strong>Add a catalog product</strong><small>Make it available to shop owners</small></span><b>→</b></RouterLink><RouterLink to="/admin/owners"><span class="quick-icon stat-green">＋</span><span><strong>Create an owner account</strong><small>Give a shop owner access</small></span><b>→</b></RouterLink></div></section></div>
        </div>
        <section class="panel-card mt-3"><div class="panel-heading"><div><h2>Recently added products</h2><p>Catalog products created by admins and shop owners</p></div><RouterLink to="/admin/products">Open catalog <span>→</span></RouterLink></div><div v-if="!loading && !data.recentProducts?.length" class="empty-state"><span>PR</span><strong>Catalog is empty</strong><p>Add the first shared product to the catalog.</p></div><div v-else class="table-responsive"><table class="table admin-table mb-0"><thead><tr><th>PRODUCT</th><th>SKU</th><th>CREATED BY</th><th>CREATED</th></tr></thead><tbody><tr v-for="product in data.recentProducts" :key="product.id"><td><strong>{{ product.name }}</strong><small>{{ product.description || 'No description' }}</small></td><td>{{ product.sku || '-' }}</td><td>{{ product.creator?.name }}</td><td>{{ date(product.created_at) }}</td></tr><tr v-if="loading"><td colspan="4" class="text-center py-4">Loading products...</td></tr></tbody></table></div></section>
    </div>
</template>

<script setup>
import { computed, inject, onMounted, ref } from 'vue';
import { auth } from '../auth';
import api from '../api';

const data = ref({ recentShops: [], recentProducts: [] });
const loading = ref(true);
const error = ref('');
const greeting = computed(() => new Date().getHours() < 12 ? 'morning' : new Date().getHours() < 18 ? 'afternoon' : 'evening');
const stats = computed(() => [
    { code: 'SH', color: 'stat-blue', label: 'Total shops', value: data.value.shopCount ?? '—', hint: 'Manage store locations', to: '/admin/shops' },
    { code: 'PR', color: 'stat-violet', label: 'Catalog products', value: data.value.productCount ?? '—', hint: 'Shared product catalog', to: '/admin/products' },
    { code: 'OW', color: 'stat-green', label: 'Shop owners', value: data.value.ownerCount ?? '—', hint: 'Owner accounts', to: '/admin/owners' },
    { code: 'LS', color: 'stat-amber', label: 'Shop listings', value: data.value.listingCount ?? '—', hint: 'Products assigned to shops' },
]);
const notify = inject('notify', () => {});

async function load() {
    loading.value = true;
    error.value = '';
    try { data.value = (await api.get('/admin/data/dashboard')).data; }
    catch { error.value = 'Could not load dashboard data.'; notify(error.value); }
    finally { loading.value = false; }
}
function date(value) { return value ? new Date(value).toLocaleDateString() : '-'; }
onMounted(load);
</script>
