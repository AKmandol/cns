<template>
    <div>
        <div class="page-heading"><div><div class="eyebrow">MARKETPLACE</div><h1>Shops</h1><p>Manage shop profiles, owners, and customer visibility.</p></div><button class="btn btn-primary" @click="openCreate">＋ <span class="ms-1">Add shop</span></button></div>
        <div v-if="error" class="alert alert-danger">{{ error }}</div>
        <section class="panel-card"><div class="panel-heading"><div><h2>All shops <span class="text-secondary fw-normal">({{ pagination.total || 0 }})</span></h2><p>Shop pages are visible to customers while active.</p></div><form class="admin-search-form" @submit.prevent="load(1)"><input v-model="search" class="form-control form-control-sm admin-search" type="search" placeholder="Search shops or owners..." aria-label="Search shops or owners"><button class="btn btn-sm btn-outline-primary">Search</button><button v-if="search" type="button" class="clear-search" @click="search = ''; load(1)">Clear</button></form></div>
            <div class="table-responsive"><table class="table admin-table align-middle mb-0"><thead><tr><th>SHOP</th><th>OWNER</th><th>STATUS</th><th>CUSTOMER PAGE</th><th class="text-end">ACTIONS</th></tr></thead><tbody>
                <tr v-for="shop in shops" :key="shop.id"><td><strong>{{ shop.name }}</strong><small>shop/{{ shop.slug }}</small></td><td>{{ shop.owner?.name }}<small>{{ shop.owner?.email }}</small></td><td><span class="status-pill" :class="shop.is_active ? 'is-active' : 'is-inactive'">{{ shop.is_active ? 'Active' : 'Inactive' }}</span></td><td><RouterLink class="table-link" :to="`/shops/${shop.slug}`" target="_blank">Preview shop ↗</RouterLink></td><td class="text-end text-nowrap"><button class="btn btn-sm btn-outline-primary" @click="openEdit(shop)">Edit</button> <button class="btn btn-sm btn-outline-danger" @click="remove(shop)">Delete</button></td></tr>
                <tr v-if="loading"><td colspan="5" class="text-center py-4">Loading shops...</td></tr><tr v-else-if="!shops.length"><td colspan="5"><div class="empty-state"><span>SH</span><strong>{{ search ? 'No matching shops' : 'No shops yet' }}</strong><p>{{ search ? 'Try another shop name, slug, or owner email.' : 'Create a shop and assign it to an owner to get started.' }}</p></div></td></tr>
            </tbody></table></div>
            <PaginationBar :page="pagination.current_page" :last-page="pagination.last_page" :from="pagination.from" :to="pagination.to" :total="pagination.total" @change="load" />
        </section>

        <ModalDialog v-model="modalOpen" :title="editing ? 'Edit shop' : 'Add shop'" :wide="true" :title-id="'shop-modal-title'">
            <div v-if="formError" class="alert alert-danger py-2">{{ formError }}</div>
            <form id="shop-form" @submit.prevent="save">
                <div class="mb-3"><label class="form-label" for="shop-owner">Shop owner</label><select id="shop-owner" v-model="form.owner_id" class="form-select" required><option value="">Choose an owner</option><option v-for="owner in owners" :key="owner.id" :value="owner.id">{{ owner.name }} ({{ owner.email }})</option></select><small v-if="!owners.length" class="form-text">Create an owner account before adding a shop.</small></div>
                <div class="row"><div class="col-md-6 mb-3"><label class="form-label" for="shop-name">Shop name</label><input id="shop-name" v-model.trim="form.name" class="form-control" required maxlength="255"></div><div class="col-md-6 mb-3"><label class="form-label" for="shop-slug">URL slug</label><input id="shop-slug" v-model.trim="form.slug" class="form-control" required maxlength="255" placeholder="my-shop"></div></div>
                <div class="mb-3"><label class="form-label" for="shop-description">Description</label><textarea id="shop-description" v-model="form.description" class="form-control" rows="3"></textarea></div>
                <div class="mb-3"><label class="form-label" for="shop-address">Address</label><input id="shop-address" v-model="form.address" class="form-control" maxlength="255"></div>
                <div class="mb-3"><label class="form-label" for="shop-active">Status</label><select id="shop-active" v-model="form.is_active" class="form-select"><option :value="true">Active</option><option :value="false">Inactive</option></select></div>
            </form>
            <template #footer><button type="button" class="btn btn-outline-secondary" @click="modalOpen = false">Cancel</button><button form="shop-form" class="btn btn-primary" :disabled="saving">{{ saving ? 'Saving...' : 'Save shop' }}</button></template>
        </ModalDialog>
    </div>
</template>

<script setup>
import { inject, onMounted, reactive, ref } from 'vue';
import api from '../api';
import ModalDialog from '../components/ModalDialog.vue';
import PaginationBar from '../components/PaginationBar.vue';

const notify = inject('notify', () => {});
const shops = ref([]);
const owners = ref([]);
const pagination = reactive({ current_page: 1, last_page: 1, from: null, to: null, total: 0 });
const search = ref('');
const loading = ref(false);
const saving = ref(false);
const error = ref('');
const formError = ref('');
const modalOpen = ref(false);
const editing = ref(null);
const blankForm = () => ({ owner_id: '', name: '', slug: '', description: '', address: '', is_active: true });
const form = reactive(blankForm());

async function load(page = 1) {
    loading.value = true;
    error.value = '';
    try {
        const { data } = await api.get('/admin/data/shops', { params: { search: search.value, page } });
        shops.value = data.shops.data;
        owners.value = data.owners;
        Object.assign(pagination, data.shops);
    } catch { error.value = 'Could not load shops. Please try again.'; }
    finally { loading.value = false; }
}

function openCreate() {
    editing.value = null; formError.value = ''; Object.assign(form, blankForm()); modalOpen.value = true;
}
function openEdit(shop) {
    editing.value = shop; formError.value = '';
    Object.assign(form, { owner_id: shop.owner_id, name: shop.name, slug: shop.slug, description: shop.description || '', address: shop.address || '', is_active: Boolean(shop.is_active) });
    modalOpen.value = true;
}
function validationMessage(exception) {
    const errors = exception.response?.data?.errors;
    return errors ? Object.values(errors).flat().join(' ') : exception.response?.data?.message || 'Unable to save this shop.';
}
async function save() {
    saving.value = true; formError.value = '';
    try {
        if (editing.value) await api.put(`/admin/data/shops/${editing.value.id}`, form);
        else await api.post('/admin/data/shops', form);
        modalOpen.value = false; notify(editing.value ? 'Shop updated.' : 'Shop created.'); await load(pagination.current_page);
    } catch (exception) { formError.value = validationMessage(exception); }
    finally { saving.value = false; }
}
async function remove(shop) {
    if (!window.confirm(`Delete ${shop.name} and its shop listings?`)) return;
    try { await api.delete(`/admin/data/shops/${shop.id}`); notify('Shop deleted.'); await load(pagination.current_page); }
    catch { error.value = 'Could not delete this shop.'; }
}
onMounted(() => load());
</script>
