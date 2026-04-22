import { computed, ref } from 'vue';
import HomePage from '../components/pages/HomePage.vue';
import CartPage from '../components/pages/CartPage.vue';
import CustomizePage from '../components/pages/CustomizePage.vue';
import NotificationsPage from '../components/pages/NotificationsPage.vue';
import SettingsPage from '../components/pages/SettingsPage.vue';

const baseLayoutProps = {
  hideSidebar: false,
  mainClass: 'bg-[#F6F4F1]',
};

const buildRoute = (component, name, path, props = {}, layoutProps = {}) => ({
  component,
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
      });
    case '/customer/customize':
      return buildRoute(CustomizePage, 'customer.customize', path);
    case '/customer/notifications':
      return buildRoute(
        NotificationsPage,
        'customer.notifications',
        path,
        {},
        {
          hideSidebar: true,
          mainClass: 'bg-[#FBF6F0]',
        },
      );
    case '/customer/settings':
      return buildRoute(SettingsPage, 'customer.settings', path, {
          tab: params.get('tab') || 'personal',
      });
    default:
      return {
        ...buildRoute(HomePage, 'customer.home', '/customer'),
        redirect: '/customer',
      };
  }
};

const currentRoute = ref(resolveRoute());

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
