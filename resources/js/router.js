import { createRouter, createWebHistory } from 'vue-router';
import { homeFor, loadUser } from './auth';
import StorefrontLayout from './layouts/StorefrontLayout.vue';
import AdminLayout from './layouts/AdminLayout.vue';
import OwnerLayout from './layouts/OwnerLayout.vue';
import LoginPage from './pages/LoginPage.vue';
import StorefrontPage from './pages/StorefrontPage.vue';
import ShopPage from './pages/ShopPage.vue';
import AdminDashboard from './pages/AdminDashboard.vue';
import AdminShops from './pages/AdminShops.vue';
import AdminProducts from './pages/AdminProducts.vue';
import AdminOwners from './pages/AdminOwners.vue';
import OwnerDashboard from './pages/OwnerDashboard.vue';
import OwnerShopProducts from './pages/OwnerShopProducts.vue';

const router = createRouter({
    history: createWebHistory(),
    routes: [
        { path: '/login', name: 'login', component: LoginPage, meta: { guestOnly: true } },
        {
            path: '/', component: StorefrontLayout, children: [
                { path: '', name: 'storefront', component: StorefrontPage },
                { path: 'shops/:slug', name: 'shop', component: ShopPage, props: true },
            ],
        },
        {
            path: '/admin', component: AdminLayout, meta: { role: 'admin' }, children: [
                { path: '', name: 'admin.dashboard', component: AdminDashboard },
                { path: 'shops', name: 'admin.shops', component: AdminShops },
                { path: 'products', name: 'admin.products', component: AdminProducts },
                { path: 'owners', name: 'admin.owners', component: AdminOwners },
            ],
        },
        {
            path: '/owner', component: OwnerLayout, meta: { role: 'owner' }, children: [
                { path: '', name: 'owner.dashboard', component: OwnerDashboard },
                { path: 'shops/:shopId/products', name: 'owner.products', component: OwnerShopProducts, props: true },
            ],
        },
        { path: '/:pathMatch(.*)*', redirect: '/' },
    ],
});

router.beforeEach(async (to) => {
    let user = null;
    try {
        user = await loadUser();
    } catch {
        user = null;
    }

    if (to.meta.guestOnly) return user ? homeFor(user) : true;
    if (!to.meta.role) return true;
    if (!user) return { name: 'login', query: { redirect: to.fullPath } };
    if (user.role !== to.meta.role) return homeFor(user);

    return true;
});

export default router;
