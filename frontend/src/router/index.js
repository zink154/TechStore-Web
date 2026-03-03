import { createRouter, createWebHistory } from 'vue-router'
import { useAuthStore } from '@/stores/auth'

const routes = [
  // Public
  { path: '/', component: () => import('@/views/public/HomePage.vue') },
  { path: '/products', component: () => import('@/views/public/ProductListPage.vue') },
  { path: '/products/:id', component: () => import('@/views/public/ProductDetailPage.vue') },
  { path: '/cart', component: () => import('@/views/public/CartPage.vue') },

  // Auth
  { path: '/login', component: () => import('@/views/auth/LoginPage.vue') },
  { path: '/register', component: () => import('@/views/auth/RegisterPage.vue') },

  // Customer (requires auth)
  {
    path: '/checkout',
    component: () => import('@/views/customer/CheckoutPage.vue'),
    meta: { requiresAuth: true },
  },
  {
    path: '/orders',
    component: () => import('@/views/customer/OrderHistoryPage.vue'),
    meta: { requiresAuth: true },
  },
  {
    path: '/profile',
    component: () => import('@/views/customer/ProfilePage.vue'),
    meta: { requiresAuth: true },
  },

  // Admin (requires admin role)
  {
    path: '/admin',
    component: () => import('@/views/admin/AdminLayout.vue'),
    meta: { requiresAuth: true, requiresAdmin: true },
    children: [
      { path: '', redirect: '/admin/dashboard' },
      { path: 'dashboard', component: () => import('@/views/admin/DashboardPage.vue') },
      { path: 'products', component: () => import('@/views/admin/ProductsPage.vue') },
      { path: 'orders', component: () => import('@/views/admin/OrdersPage.vue') },
      { path: 'categories', component: () => import('@/views/admin/CategoriesPage.vue') },
    ],
  },

  // 404
  { path: '/:pathMatch(.*)*', component: () => import('@/views/public/NotFoundPage.vue') },
]

const router = createRouter({
  history: createWebHistory(),
  routes,
  scrollBehavior: () => ({ top: 0 }),
})

router.beforeEach(async (to) => {
  const auth = useAuthStore()

  if (auth.token && !auth.user) {
    await auth.fetchUser().catch(() => {})
  }

  if (to.meta.requiresAuth && !auth.isAuthenticated) {
    return { path: '/login', query: { redirect: to.fullPath } }
  }

  if (to.meta.requiresAdmin && !auth.isAdmin) {
    return { path: '/' }
  }
})

export default router
