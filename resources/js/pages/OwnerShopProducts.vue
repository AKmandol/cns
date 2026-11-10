<template>
    <div><RouterLink to="/owner" class="back-to-shops"><span>&larr;</span> My shops</RouterLink>
        <div class="page-heading"><div><div class="eyebrow">SHOP INVENTORY</div><h1>{{ shop?.name || 'Shop products' }}</h1><p>Set a shop-specific price, description, and thumbnail for each catalog product.</p></div><div class="d-flex gap-2"><button class="btn btn-outline-primary" @click="openExisting">Add from catalog</button><button class="btn btn-primary" @click="openNew">Create product</button></div></div>
        <div v-if="error" class="alert alert-danger">{{ error }}</div>
        <section class="panel-card"><div class="panel-heading"><div><h2>Shop listings <span class="text-secondary fw-normal">({{ listings.length }})</span></h2><p>Removing an item unlists it here and keeps it in the shared catalog.</p></div></div>
            <div class="table-responsive"><table class="table admin-table align-middle mb-0"><thead><tr><th>PRODUCT</th><th>SHOP PRICE</th><th>STATUS</th><th>CATALOG CREATOR</th><th class="text-end">ACTIONS</th></tr></thead><tbody><tr v-for="listing in listings" :key="listing.id"><td><div class="d-flex align-items-center gap-2"><img v-if="listing.thumbnail" :src="`/storage/${listing.thumbnail}`" width="44" height="44" class="rounded object-fit-cover" alt=""><span><strong>{{ listing.product.name }}</strong><small>{{ listing.product.sku || 'No SKU' }}</small></span></div></td><td>৳{{ money(listing.price) }}</td><td><span class="status-pill" :class="listing.is_active ? 'is-active' : 'is-inactive'">{{ listing.is_active ? 'Active' : 'Inactive' }}</span></td><td>{{ listing.product.creator?.name }}</td><td class="text-end text-nowrap"><button class="btn btn-sm btn-outline-primary" @click="openEdit(listing)">Edit</button> <button class="btn btn-sm btn-outline-danger" @click="remove(listing)">Remove</button></td></tr><tr v-if="loading"><td colspan="5" class="text-center py-4">Loading shop products...</td></tr><tr v-else-if="!listings.length"><td colspan="5"><div class="empty-state"><span>PR</span><strong>No products listed</strong><p>Add from the shared catalog or create a product to get started.</p></div></td></tr></tbody></table></div>
        </section>

        <ModalDialog v-model="existingOpen" title="Add a catalog product" title-id="existing-product-title">
            <div v-if="formError" class="alert alert-danger py-2">{{ formError }}</div>
            <div v-if="!availableProducts.length" class="alert alert-info">All catalog products are already listed in this shop.</div>
            <form v-else id="existing-product-form" @submit.prevent="saveExisting"><div class="mb-3"><label class="form-label" for="existing-product">Catalog product</label><select id="existing-product" v-model="existingForm.product_id" class="form-select" required><option value="">Choose a product</option><option v-for="product in availableProducts" :key="product.id" :value="product.id">{{ product.name }}{{ product.sku ? ` · ${product.sku}` : '' }}</option></select></div><div class="mb-3"><label class="form-label" for="existing-price">Shop price</label><input id="existing-price" v-model="existingForm.price" class="form-control" type="number" min="0" step="0.01" required></div><div class="mb-3"><label class="form-label" for="existing-description">Shop description</label><textarea id="existing-description" v-model="existingForm.description" class="form-control" rows="3"></textarea></div><div class="mb-3"><label class="form-label" for="existing-thumbnail">Shop thumbnail</label><input id="existing-thumbnail" class="form-control" type="file" accept="image/*" @change="existingForm.thumbnail = $event.target.files[0]"><small class="form-text">Optional image, maximum 4 MB.</small></div></form>
            <template #footer><button class="btn btn-outline-secondary" @click="existingOpen = false">Cancel</button><button v-if="availableProducts.length" form="existing-product-form" class="btn btn-primary" :disabled="saving">{{ saving ? 'Adding...' : 'Add to shop' }}</button></template>
        </ModalDialog>

        <ModalDialog v-model="newOpen" title="Create and list a product" title-id="new-product-title" :wide="true">
            <div v-if="formError" class="alert alert-danger py-2">{{ formError }}</div><p class="text-secondary small">This creates a shared catalog product and immediately lists it in {{ shop?.name }}.</p>
            <form id="new-product-form" @submit.prevent="saveNew"><div class="row"><div class="col-md-8 mb-3"><label class="form-label" for="new-name">Product name</label><input id="new-name" v-model.trim="newForm.name" class="form-control" required></div><div class="col-md-4 mb-3"><label class="form-label" for="new-sku">SKU</label><input id="new-sku" v-model.trim="newForm.sku" class="form-control"></div></div><div class="mb-3"><label class="form-label" for="catalog-description">Shared catalog description</label><textarea id="catalog-description" v-model="newForm.catalog_description" class="form-control" rows="2"></textarea></div><hr><h3 class="h6">This shop’s listing</h3><div class="mb-3"><label class="form-label" for="new-price">Shop price</label><input id="new-price" v-model="newForm.price" class="form-control" type="number" min="0" step="0.01" required></div><div class="mb-3"><label class="form-label" for="new-description">Shop description</label><textarea id="new-description" v-model="newForm.description" class="form-control" rows="3"></textarea></div><div class="mb-3"><label class="form-label" for="new-thumbnail">Shop thumbnail</label><input id="new-thumbnail" class="form-control" type="file" accept="image/*" @change="newForm.thumbnail = $event.target.files[0]"><small class="form-text">Optional image, maximum 4 MB.</small></div></form>
            <template #footer><button class="btn btn-outline-secondary" @click="newOpen = false">Cancel</button><button form="new-product-form" class="btn btn-primary" :disabled="saving">{{ saving ? 'Creating...' : 'Create product' }}</button></template>
        </ModalDialog>

        <ModalDialog v-model="editOpen" :title="`Edit ${editing?.product?.name || 'listing'}`" title-id="edit-listing-title">
            <div v-if="formError" class="alert alert-danger py-2">{{ formError }}</div>
            <form id="edit-listing-form" @submit.prevent="saveEdit"><div class="mb-3"><label class="form-label" for="edit-price">Shop price</label><input id="edit-price" v-model="editForm.price" class="form-control" type="number" min="0" step="0.01" required></div><div class="mb-3"><label class="form-label" for="edit-description">Shop description</label><textarea id="edit-description" v-model="editForm.description" class="form-control" rows="3"></textarea></div><div v-if="editing?.thumbnail" class="mb-2"><img :src="`/storage/${editing.thumbnail}`" alt="Current thumbnail" class="edit-thumbnail"></div><div class="mb-3"><label class="form-label" for="edit-thumbnail">Replace thumbnail</label><input id="edit-thumbnail" class="form-control" type="file" accept="image/*" @change="editForm.thumbnail = $event.target.files[0]"><small class="form-text">Leave empty to keep the current image.</small></div><div class="mb-3"><label class="form-label" for="edit-active">Status</label><select id="edit-active" v-model="editForm.is_active" class="form-select"><option :value="true">Active</option><option :value="false">Inactive</option></select></div></form>
            <template #footer><button class="btn btn-outline-secondary" @click="editOpen = false">Cancel</button><button form="edit-listing-form" class="btn btn-primary" :disabled="saving">{{ saving ? 'Saving...' : 'Save changes' }}</button></template>
        </ModalDialog>
    </div>
</template>

<script setup>
import { inject, onMounted, reactive, ref, watch } from 'vue';
import { useRouter } from 'vue-router';
import api from '../api';
import ModalDialog from '../components/ModalDialog.vue';

const props = defineProps({ shopId: { type: String, required: true } });
const router = useRouter(); const notify = inject('notify', () => {});
const shop = ref(null); const listings = ref([]); const availableProducts = ref([]); const loading = ref(false); const saving = ref(false); const error = ref(''); const formError = ref('');
const existingOpen = ref(false); const newOpen = ref(false); const editOpen = ref(false); const editing = ref(null);
const blankExisting = () => ({ product_id: '', price: '', description: '', thumbnail: null }); const existingForm = reactive(blankExisting());
const blankNew = () => ({ name: '', sku: '', catalog_description: '', price: '', description: '', thumbnail: null }); const newForm = reactive(blankNew());
const blankEdit = () => ({ price: '', description: '', is_active: true, thumbnail: null }); const editForm = reactive(blankEdit());

async function load() {
    loading.value = true; error.value = '';
    try { const { data } = await api.get(`/owner/data/shops/${props.shopId}/products`); shop.value = data.shop; listings.value = data.listings; availableProducts.value = data.availableProducts; }
    catch (exception) { if (exception.response?.status === 403 || exception.response?.status === 404) router.replace('/owner'); else error.value = 'Could not load products for this shop.'; }
    finally { loading.value = false; }
}
function openExisting() { formError.value = ''; Object.assign(existingForm, blankExisting()); existingOpen.value = true; }
function openNew() { formError.value = ''; Object.assign(newForm, blankNew()); newOpen.value = true; }
function openEdit(listing) { editing.value = listing; formError.value = ''; Object.assign(editForm, { price: listing.price, description: listing.description || '', is_active: Boolean(listing.is_active), thumbnail: null }); editOpen.value = true; }
function makeFormData(values) { const payload = new FormData(); for (const [key, value] of Object.entries(values)) if (value !== null && value !== undefined) payload.append(key, typeof value === 'boolean' ? (value ? '1' : '0') : value); return payload; }
function validationMessage(exception) { const errors = exception.response?.data?.errors; return errors ? Object.values(errors).flat().join(' ') : exception.response?.data?.message || 'Unable to save this listing.'; }
async function saveExisting() { saving.value = true; formError.value = ''; try { await api.post(`/owner/data/shops/${props.shopId}/products`, makeFormData(existingForm)); existingOpen.value = false; notify('Product added to your shop.'); await load(); } catch (exception) { formError.value = validationMessage(exception); } finally { saving.value = false; } }
async function saveNew() { saving.value = true; formError.value = ''; try { await api.post(`/owner/data/shops/${props.shopId}/products/new`, makeFormData(newForm)); newOpen.value = false; notify('Product created and listed in your shop.'); await load(); } catch (exception) { formError.value = validationMessage(exception); } finally { saving.value = false; } }
async function saveEdit() { saving.value = true; formError.value = ''; const payload = makeFormData(editForm); payload.append('_method', 'PUT'); try { await api.post(`/owner/data/shops/${props.shopId}/products/${editing.value.id}`, payload); editOpen.value = false; notify('Shop listing updated.'); await load(); } catch (exception) { formError.value = validationMessage(exception); } finally { saving.value = false; } }
async function remove(listing) { if (!window.confirm(`Remove ${listing.product.name} from this shop?`)) return; try { await api.delete(`/owner/data/shops/${props.shopId}/products/${listing.id}`); notify('Product removed from your shop.'); await load(); } catch { error.value = 'Could not remove this product.'; } }
function money(price) { return Number(price).toLocaleString(undefined, { minimumFractionDigits: 2, maximumFractionDigits: 2 }); }
onMounted(load); watch(() => props.shopId, load);
</script>
