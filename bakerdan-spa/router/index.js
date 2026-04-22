import { createRouter, createWebHistory } from 'vue-router'
import CustomerLayout from '../components/layout/CustomerLayout.vue'
import HomePage from '../components/pages/HomePage.vue'
import CartPage from '../components/pages/CartPage.vue'
import CustomizePage from '../components/pages/CustomizePage.vue'
import SettingsPage from '../components/pages/SettingsPage.vue'

const routes = [
  {
    path: '/customer',
    component: CustomerLayout,
    children: [
      {
        path: '',
        name: 'home',
        component: HomePage
      },
      {
        path: 'cart',
        name: 'cart',
        component: CartPage
      },
      {
        path: 'customize',
        name: 'customize',
        component: CustomizePage
      },
      {
        path: 'settings',
        name: 'settings',
        component: SettingsPage,
        props: route => ({ tab: route.query.tab || 'personal' })
      }
    ]
  },
  {
    path: '/:pathMatch(.*)*',
    redirect: '/customer'
  }
]

const router = createRouter({
  history: createWebHistory(),
  routes,
  scrollBehavior(to, from, savedPosition) {
    if (savedPosition) {
      return savedPosition
    }
    return { top: 0 }
  }
})

export default router
