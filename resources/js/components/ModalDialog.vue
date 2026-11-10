<template>
    <Teleport to="body">
        <div v-if="modelValue" class="vue-modal-backdrop" @click.self="$emit('update:modelValue', false)" @keydown.esc="$emit('update:modelValue', false)">
            <section class="vue-modal" :class="{ 'vue-modal-wide': wide }" role="dialog" aria-modal="true" :aria-labelledby="titleId">
                <header class="vue-modal-header"><h2 :id="titleId">{{ title }}</h2><button type="button" class="vue-modal-close" aria-label="Close" @click="$emit('update:modelValue', false)">&times;</button></header>
                <div class="vue-modal-body"><slot /></div>
                <footer v-if="$slots.footer" class="vue-modal-footer"><slot name="footer" /></footer>
            </section>
        </div>
    </Teleport>
</template>

<script setup>
defineProps({ modelValue: Boolean, title: { type: String, required: true }, titleId: { type: String, default: 'modal-title' }, wide: Boolean });
defineEmits(['update:modelValue']);
</script>
