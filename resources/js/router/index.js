import { computed, defineAsyncComponent, markRaw, shallowRef } from 'vue';
import HomePage from '../components/pages/HomePage.vue';

const CartPage = defineAsyncComponent(() => import('../components/pages/CartPage.vue'));
const CheckoutPage = defineAsyncComponent(() => import('../components/pages/CheckoutPage.vue'));
const CustomizePage = defineAsyncComponent(() => import('../components/pages/CustomizePage.vue'));
const OrdersPage = defineAsyncComponent(() => import('../components/pages/OrdersPage.vue'));
const MessagesPage = defineAsyncComponent(() => import('../components/pages/MessagesPage.vue'));
const NotificationsPage = defineAsyncComponent(() => import('../components/pages/NotificationsPage.vue'));
const PurchaseHistoryPage = defineAsyncComponent(() => import('../components/pages/PurchaseHistoryPage.vue'));
const SettingsPage = defineAsyncComponent(() => import('../components/pages/SettingsPage.vue'));

const baseLayoutProps = {
  hideSidebar: false,
  mainClass: 'bg-[#F6F4F1]',
  requiresAuth: false,
};

const buildRoute = (component, name, path, props = {}, layoutProps = {}) => ({
  component: markRaw(component),
  name,
  path,
  props,
  layoutProps: {
    ...baseLayoutProps,
    ...layoutProps,
  },
});

const normalizePath = (path) => {
  if (!path) {
    return '/customer';
  }

  if (path !== '/' && path.endsWith('/')) {
    return path.slice(0, -1);
  }

  return path;
};

const resolveRoute = () => {
  const path = normalizePath(window.location.pathname);
  const params = new URLSearchParams(window.location.search);

  switch (path) {
    case '/customer':
      return buildRoute(HomePage, 'customer.home', path);
    case '/customer/cart':
      return buildRoute(CartPage, 'customer.cart', path, {
        added: params.get('added') || null,
        ordered: params.get('ordered') || null,
        paymentReturn: params.get('payment_return') || null,
        option: params.get('option') || null,
        preview: params.get('preview') || null,
      });
    case '/customer/checkout':
      return buildRoute(
        CheckoutPage,
        'customer.checkout',
        path,
        {
          items: params.get('items') || null,
        },
        {
          hideSidebar: true,
          mainClass: 'bg-[#FBF7F2]',
          requiresAuth: true,
        },
      );
    case '/customer/customize':
      return buildRoute(CustomizePage, 'customer.customize', path, {
        guide: params.get('guide') || 'cakes',
        section: params.get('section') || null,
      });
    case '/customer/orders':
      return buildRoute(
        OrdersPage,
        'customer.orders',
        path,
        {
          highlight: params.get('highlight') || params.get('order') || null,
          paymentReturn: params.get('payment_return') || null,
        },
        {
          hideSidebar: true,
          mainClass: 'bg-[#FBF7F2]',
          requiresAuth: true,
        },
      );
    case '/customer/messages':
      return buildRoute(
        MessagesPage,
        'customer.messages',
        path,
        {},
        {
          hideSidebar: true,
          mainClass: 'bg-[#F6EFE8]',
          requiresAuth: true,
        },
      );
    case '/customer/notifications':
      return buildRoute(
        NotificationsPage,
        'customer.notifications',
        path,
        {},
        {
          hideSidebar: true,
          mainClass: 'bg-[#FBF6F0]',
          requiresAuth: true,
        },
      );
    case '/customer/purchases':
      return buildRoute(
        PurchaseHistoryPage,
        'customer.purchases',
        path,
        {},
        {
          hideSidebar: true,
          mainClass: 'bg-[#FBF7F2]',
          requiresAuth: true,
        },
      );
    case '/customer/settings':
      return buildRoute(SettingsPage, 'customer.settings', path, {
          tab: params.get('tab') || 'personal',
      }, {
        requiresAuth: true,
      });
    default:
      return {
        ...buildRoute(HomePage, 'customer.home', '/customer'),
        redirect: '/customer',
      };
  }
};

const currentRoute = shallowRef(resolveRoute());

const syncRoute = () => {
  currentRoute.value = resolveRoute();

  if (currentRoute.value.redirect && currentRoute.value.redirect !== window.location.pathname) {
    window.history.replaceState({}, '', currentRoute.value.redirect);
    currentRoute.value = resolveRoute();
  }
};

if (!window.__bakerdanSpaRouterBound) {
  window.addEventListener('popstate', syncRoute);
  window.__bakerdanSpaRouterBound = true;
}

export const navigate = (to, { replace = false } = {}) => {
  const destination = typeof to === 'string' ? to : '/customer';
  const currentLocation = `${window.location.pathname}${window.location.search}`;

  if (destination === currentLocation) {
    return;
  }

  if (replace) {
    window.history.replaceState({}, '', destination);
  } else {
    window.history.pushState({}, '', destination);
  }

  syncRoute();
  window.scrollTo({ top: 0, behavior: 'smooth' });
};

export const useSpaRoute = () => computed(() => currentRoute.value);

export const useSpaRouter = () => ({
  currentRoute: computed(() => currentRoute.value),
  push: (to) => navigate(to),
  replace: (to) => navigate(to, { replace: true }),
});

export default {
  currentRoute,
  navigate,
  syncRoute,
};
