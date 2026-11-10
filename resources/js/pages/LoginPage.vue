<template>
    <main class="login-screen"><section class="login-card"><RouterLink class="storefront-brand login-brand" to="/"><span class="storefront-logo">S</span><span>ShopSuite<small>LOCAL MARKETPLACE</small></span></RouterLink>
        <div class="login-heading"><span class="storefront-kicker">WELCOME BACK</span><h1>Sign in to your account</h1><p>Access your admin or shop owner workspace.</p></div>
        <div v-if="message" class="alert alert-danger py-2">{{ message }}</div>
        <form @submit.prevent="submit">
            <div class="mb-3"><label class="form-label" for="email">Email address</label><input id="email" v-model.trim="email" class="form-control" type="email" autocomplete="username" required autofocus></div>
            <div class="mb-3"><label class="form-label" for="password">Password</label><input id="password" v-model="password" class="form-control" type="password" autocomplete="current-password" required></div>
            <label class="form-check mb-3"><input v-model="remember" class="form-check-input" type="checkbox"><span class="form-check-label">Keep me signed in</span></label>
            <button class="btn btn-primary w-100" :disabled="busy">{{ busy ? 'Signing in...' : 'Sign in' }}</button>
        </form>
        <p class="login-demo">Demo admin: <strong>admin@example.com</strong> / <strong>password</strong></p>
        <RouterLink class="login-back" to="/">&larr; Back to the marketplace</RouterLink>
    </section></main>
</template>

<script setup>
import { ref } from 'vue';
import { useRoute, useRouter } from 'vue-router';
import { signIn, homeFor } from '../auth';

const route = useRoute();
const router = useRouter();
const email = ref('');
const password = ref('');
const remember = ref(false);
const busy = ref(false);
const message = ref('');

async function submit() {
    busy.value = true;
    message.value = '';
    try {
        const user = await signIn({ email: email.value, password: password.value, remember: remember.value });
        const destination = typeof route.query.redirect === 'string' ? route.query.redirect : homeFor(user);
        router.replace(destination);
    } catch (error) {
        message.value = error.response?.data?.message || 'We could not sign you in. Check your email and password.';
    } finally {
        busy.value = false;
    }
}
</script>
