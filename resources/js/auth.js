import { reactive } from 'vue';
import api from './api';

export const auth = reactive({ user: null, loaded: false });

export async function loadUser(force = false) {
    if (auth.loaded && !force) return auth.user;

    const { data } = await api.get('/app/user');
    auth.user = data.user;
    auth.loaded = true;

    return auth.user;
}

export async function signIn(credentials) {
    const { data } = await api.post('/login', credentials);
    auth.user = data.user;
    auth.loaded = true;

    return auth.user;
}

export async function signOut() {
    await api.post('/logout');
    auth.user = null;
    auth.loaded = true;
}

export function homeFor(user) {
    if (user?.role === 'admin') return '/admin';
    if (user?.role === 'owner') return '/owner';
    return '/';
}
