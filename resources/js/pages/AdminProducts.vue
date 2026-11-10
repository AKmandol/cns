<template>
    <div>
        <div class="page-heading"><div><div class="eyebrow">CATALOG</div><h1>Product catalog</h1><p>Shared products created by you and shop owners. Customers see products once a shop lists them.</p></div><button class="btn btn-primary" @click="openCreate">＋ <span class="ms-1">Add product</span></button></div>
        <div v-if="error" class="alert alert-danger">{{ error }}</div>
        <section class="panel-card"><div class="panel-heading"><div><h2>All products <span class="text-secondary fw-normal">({{ pagination.total || 0 }})</span></h2><p>Catalog details are shared; price and thumbnail are set per shop.</p></div><form class="admin-search-form" @submit.prevent="load(1)"><input v-model="search" class="form-control form-control-sm admin-search" type="search" placeholder="Search name, SKU, creator..." aria-label="Search catalog"><button class="btn btn-sm btn-outline-primary">Search</button><button v-if="search" type="button" class="clear-search" @click="search = ''; load(1)">Clear</button></form></div>
            <div class="table-responsive"><table class="table admin-table align-middle mb-0"><thead><tr><th>PRODUCT</th><th>SKU</th><th>CREATED BY</th><th>SHOP LISTINGS</th><th class="text-end">ACTIONS</th></tr></thead><tbody><tr v-for="product in products" :key="product.id"><td><strong>{{ product.name }}</strong><small>{{ product.description || 'No description' }}</small></td><td>{{ product.sku || '-' }}</td><td>{{ product.creator?.name }}</td><td><span class="listing-count">{{ product.listings_count }}</span></td><td class="text-end text-nowrap"><button class="btn btn-sm btn-outline-primary" @click="openEdit(product)">Edit</button> <button class="btn btn-sm btn-outline-danger" @click="remove(product)">Delete</button></td></tr><tr v-if="loading"><td colspan="5" class="text-center py-4">Loading products...</td></tr><tr v-else-if="!products.length"><td colspan="5"><div class="empty-state"><span>PR</span><strong>{{ search ? 'No matching products' : 'Catalog is empty' }}</strong><p>{{ search ? 'Try another name, SKU, or creator.' : 'Add a shared product for shop owners to list.' }}</p></div></td></tr></tbody></table></div>
            <PaginationBar :page="pagination.current_page" :last-page="pagination.last_page" :from="pagination.from" :to="pagination.to" :total="pagination.total" @change="load" />
        </section>
        <ModalDialog v-model="modalOpen" :title="editing ? 'Edit catalog product' : 'Add catalog product'" :title-id="'product-modal-title'">
            <div v-if="formError" class="alert alert-danger py-2">{{ formError }}</div>
            <form id="product-form" @submit.prevent="save"><div class="mb-3"><label class="form-label" for="product-name">Product name</label><input id="product-name" v-model.trim="form.name" class="form-control" required maxlength="255"></div><div class="mb-3"><label class="form-label" for="product-sku">SKU (optional)</label><input id="product-sku" v-model.trim="form.sku" class="form-control" maxlength="255"></div><div class="mb-3"><label class="form-label" for="product-description">Catalog description</label><textarea id="product-description" v-model="form.description" class="form-control" rows="4"></textarea></div></form>
            <template #footer><button class="btn btn-outline-secondary" @click="modalOpen = false">Cancel</button><button form="product-form" class="btn btn-primary" :disabled="saving">{{ saving ? 'Saving...' : 'Save product' }}</button></template>
        </ModalDialog>
    </div>
</template>

<script setup>
import { inject, onMounted, reactive, ref } from 'vue';
import api from '../api';
import ModalDialog from '../components/ModalDialog.vue';
import PaginationBar from '../components/PaginationBar.vue';

const notify = inject('notify', () => {});
const products = ref([]); const search = ref(''); const loading = ref(false); const saving = ref(false); const error = ref(''); const formError = ref(''); const modalOpen = ref(false); const editing = ref(null);
const pagination = reactive({ current_page: 1, last_page: 1, from: null, to: null, total: 0 });
const blankForm = () => ({ name: '', sku: '', description: '' }); const form = reactive(blankForm());

async function load(page = 1) {
    loading.value = true; error.value = '';
    try { const { data } = await api.get('/admin/data/products', { params: { search: search.value, page } }); products.value = data.products.data; Object.assign(pagination, data.products); }
    catch { error.value = 'Could not load products. Please try again.'; }
    finally { loading.value = false; }
}
function openCreate() { editing.value = null; formError.value = ''; Object.assign(form, blankForm()); modalOpen.value = true; }
function openEdit(product) { editing.value = product; formError.value = ''; Object.assign(form, { name: product.name, sku: product.sku || '', description: product.description || '' }); modalOpen.value = true; }
function validationMessage(exception) { const errors = exception.response?.data?.errors; return errors ? Object.values(errors).flat().join(' ') : exception.response?.data?.message || 'Unable to save this product.'; }
async function save() {
    saving.value = true; formError.value = '';
    try { if (editing.value) await api.put(`/admin/data/products/${editing.value.id}`, form); else await api.post('/admin/data/products', form); modalOpen.value = false; notify(editing.value ? 'Product updated.' : 'Product created.'); await load(pagination.current_page); }
    catch (exception) { formError.value = validationMessage(exception); }
    finally { saving.value = false; }
}
async function remove(product) {
    if (!window.confirm(`Delete ${product.name} and all its shop listings?`)) return;
    try { await api.delete(`/admin/data/products/${product.id}`); notify('Product deleted.'); await load(pagination.current_page); }
    catch { error.value = 'Could not delete this product.'; }
}
onMounted(() => load());
</script>
