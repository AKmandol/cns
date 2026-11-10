<template>
    <div v-if="lastPage > 1" class="pagination-bar"><span>Showing {{ from || 0 }}–{{ to || 0 }} of {{ total }}</span><div class="pagination-controls"><button class="btn btn-sm btn-outline-secondary" :disabled="page <= 1" @click="$emit('change', page - 1)">Previous</button><span>Page {{ page }} of {{ lastPage }}</span><button class="btn btn-sm btn-outline-secondary" :disabled="page >= lastPage" @click="$emit('change', page + 1)">Next</button></div></div>
</template>

<script setup>
defineProps({ page: { type: Number, default: 1 }, lastPage: { type: Number, default: 1 }, from: Number, to: Number, total: { type: Number, default: 0 } });
defineEmits(['change']);
</script>
