import { computed, ref } from 'vue';
import api from './api';

const notifications = ref([]);
const unreadCount = ref(0);
const isLoading = ref(false);
const hasLoaded = ref(false);

let loadPromise = null;

const syncNotifications = (payload = {}) => {
    notifications.value = Array.isArray(payload.notifications) ? payload.notifications : [];
    unreadCount.value = Number(
        payload.unread_count
        ?? notifications.value.filter((notification) => notification.unread).length
        ?? 0,
    );
};

const loadNotifications = async ({ force = false } = {}) => {
    if (hasLoaded.value && !force) {
        return notifications.value;
    }

    if (loadPromise && !force) {
        return loadPromise;
    }

    isLoading.value = true;
    loadPromise = api.getNotifications()
        .then((response) => {
            syncNotifications(response.data?.data || {});
            hasLoaded.value = true;
            return notifications.value;
        })
        .finally(() => {
            isLoading.value = false;
            loadPromise = null;
        });

    return loadPromise;
};

const markAllAsRead = async () => {
    await api.markAllNotificationsRead();
    notifications.value = notifications.value.map((notification) => ({
        ...notification,
        unread: false,
    }));
    unreadCount.value = 0;
};

const markAsRead = async (notificationId) => {
    const existing = notifications.value.find((notification) => Number(notification.id) === Number(notificationId));

    if (!existing || !existing.unread) {
        return;
    }

    await api.markNotificationRead(notificationId);
    notifications.value = notifications.value.map((notification) => (
        Number(notification.id) === Number(notificationId)
            ? { ...notification, unread: false }
            : notification
    ));
    unreadCount.value = Math.max(0, unreadCount.value - 1);
};

export const useNotificationStore = () => ({
    notifications,
    unreadCount: computed(() => unreadCount.value),
    isLoading,
    hasLoaded,
    loadNotifications,
    markAsRead,
    markAllAsRead,
});
