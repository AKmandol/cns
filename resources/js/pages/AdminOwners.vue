<template>
    <div>
        <div class="page-heading"><div><div class="eyebrow">TEAM</div><h1>Shop owners</h1><p>Create accounts and see which owners are managing shops.</p></div><button class="btn btn-primary" @click="openCreate">＋ <span class="ms-1">Add owner</span></button></div>
        <div v-if="error" class="alert alert-danger">{{ error }}</div>
        <section class="panel-card"><div class="panel-heading"><div><h2>Owner accounts <span class="text-secondary fw-normal">({{ pagination.total || 0 }})</span></h2><p>Shop owners can only manage shops assigned to them.</p></div><form class="admin-search-form" @submit.prevent="load(1)"><input v-model="search" class="form-control form-control-sm admin-search" type="search" placeholder="Search name or email..." aria-label="Search shop owners"><button class="btn btn-sm btn-outline-primary">Search</button><button v-if="search" type="button" class="clear-search" @click="search = ''; load(1)">Clear</button></form></div>
            <div class="table-responsive"><table class="table admin-table mb-0"><thead><tr><th>OWNER</th><th>EMAIL</th><th>SHOPS</th><th>JOINED</th></tr></thead><tbody><tr v-for="owner in owners" :key="owner.id"><td><div class="owner-name"><span class="avatar-circle">{{ owner.name.charAt(0).toUpperCase() }}</span><strong>{{ owner.name }}</strong></div></td><td>{{ owner.email }}</td><td><span class="listing-count">{{ owner.shops_count }}</span></td><td>{{ date(owner.created_at) }}</td></tr><tr v-if="loading"><td colspan="4" class="text-center py-4">Loading owners...</td></tr><tr v-else-if="!owners.length"><td colspan="4"><div class="empty-state"><span>OW</span><strong>{{ search ? 'No matching owners' : 'No shop owners yet' }}</strong><p>{{ search ? 'Try another name or email address.' : 'Create an owner account before adding their shop.' }}</p></div></td></tr></tbody></table></div>
            <PaginationBar :page="pagination.current_page" :last-page="pagination.last_page" :from="pagination.from" :to="pagination.to" :total="pagination.total" @change="load" />
        </section>
        <ModalDialog v-model="modalOpen" title="Create owner account" title-id="owner-modal-title">
            <div v-if="formError" class="alert alert-danger py-2">{{ formError }}</div>
            <form id="owner-form" @submit.prevent="save"><div class="mb-3"><label class="form-label" for="owner-name">Name</label><input id="owner-name" v-model.trim="form.name" class="form-control" required maxlength="255"></div><div class="mb-3"><label class="form-label" for="owner-email">Email</label><input id="owner-email" v-model.trim="form.email" class="form-control" type="email" required maxlength="255"></div><div class="mb-3"><label class="form-label" for="owner-password">Temporary password</label><input id="owner-password" v-model="form.password" class="form-control" type="password" minlength="8" required></div><div class="mb-3"><label class="form-label" for="owner-password-confirmation">Confirm password</label><input id="owner-password-confirmation" v-model="form.password_confirmation" class="form-control" type="password" required></div></form>
            <template #footer><button class="btn btn-outline-secondary" @click="modalOpen = false">Cancel</button><button form="owner-form" class="btn btn-primary" :disabled="saving">{{ saving ? 'Creating...' : 'Create account' }}</button></template>
        </ModalDialog>
    </div>
</template>

<script setup>
import { inject, onMounted, reactive, ref } from 'vue';
import api from '../api';
import ModalDialog from '../components/ModalDialog.vue';
import PaginationBar from '../components/PaginationBar.vue';

const notify = inject('notify', () => {});
const owners = ref([]); const search = ref(''); const loading = ref(false); const saving = ref(false); const error = ref(''); const formError = ref(''); const modalOpen = ref(false);
const pagination = reactive({ current_page: 1, last_page: 1, from: null, to: null, total: 0 });
const blankForm = () => ({ name: '', email: '', password: '', password_confirmation: '' }); const form = reactive(blankForm());

async function load(page = 1) { loading.value = true; error.value = ''; try { const { data } = await api.get('/admin/data/owners', { params: { search: search.value, page } }); owners.value = data.owners.data; Object.assign(pagination, data.owners); } catch { error.value = 'Could not load shop owners.'; } finally { loading.value = false; } }
function openCreate() { formError.value = ''; Object.assign(form, blankForm()); modalOpen.value = true; }
function date(value) { return value ? new Date(value).toLocaleDateString() : '-'; }
async function save() {
    saving.value = true; formError.value = '';
    try { await api.post('/admin/data/owners', form); modalOpen.value = false; notify('Shop owner account created.'); await load(1); }
    catch (exception) { const errors = exception.response?.data?.errors; formError.value = errors ? Object.values(errors).flat().join(' ') : exception.response?.data?.message || 'Unable to create this account.'; }
    finally { saving.value = false; }
}
onMounted(() => load());
</script>
