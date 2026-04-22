import './bootstrap';

const dashboard = document.querySelector('[data-admin-dashboard]');

if (dashboard) {
    const navButtons = Array.from(dashboard.querySelectorAll('[data-nav]'));
    const sections = Array.from(dashboard.querySelectorAll('[data-section]'));
    const modalShell = dashboard.querySelector('[data-modal-shell]');
    const modalTitle = dashboard.querySelector('[data-modal-title]');
    const modalMessage = dashboard.querySelector('[data-modal-message]');
    const modalConfirm = dashboard.querySelector('[data-modal-confirm]');
    const modalCancel = dashboard.querySelector('[data-modal-cancel]');
    const inventoryTitle = dashboard.querySelector('[data-inventory-title]');
    const inventorySubtitle = dashboard.querySelector('[data-inventory-subtitle]');
    const inventoryDrawer = dashboard.querySelector('[data-inventory-drawer]');
    const inventoryDrawerClose = dashboard.querySelector('[data-inventory-close]');
    const inventoryForm = dashboard.querySelector('[data-inventory-form]');
    const inventoryFeedback = dashboard.querySelector('[data-inventory-feedback]');
    const inventoryName = dashboard.querySelector('[data-inventory-name]');
    const inventoryDescription = dashboard.querySelector('[data-inventory-description]');
    const inventoryType = dashboard.querySelector('[data-inventory-type]');
    const inventoryId = dashboard.querySelector('[data-inventory-id]');
    const customerPanel = dashboard.querySelector('[data-customer-panel]');
    const customerPanelTitle = dashboard.querySelector('[data-customer-panel-title]');
    const customerPanelMeta = dashboard.querySelector('[data-customer-panel-meta]');
    const notificationCount = dashboard.querySelector('[data-notification-count]');
    const personTabs = Array.from(dashboard.querySelectorAll('[data-person-tab]'));
    const personPanels = Array.from(dashboard.querySelectorAll('[data-person-panel]'));

    let modalAction = null;

    const showSection = (name) => {
        sections.forEach((section) => {
            section.hidden = section.dataset.section !== name;
        });

        navButtons.forEach((button) => {
            button.dataset.active = button.dataset.nav === name ? 'true' : 'false';
        });
    };

    const openModal = (action) => {
        modalAction = action;
        modalTitle.textContent = action.title;
        modalMessage.textContent = action.message;
        modalConfirm.textContent = action.confirmLabel;
        modalShell.hidden = false;
    };

    const openInventoryDrawer = (mode, product = null) => {
        setInventoryMode(mode, product);
        inventoryDrawer.hidden = false;
    };

    const closeInventoryDrawer = () => {
        inventoryDrawer.hidden = true;
    };

    const closeModal = () => {
        modalAction = null;
        modalShell.hidden = true;
    };

    const setInventoryMode = (mode, product = null) => {
        inventoryForm.dataset.mode = mode;
        inventoryTitle.textContent = mode === 'edit' ? 'Edit Product' : 'Add Product';
        inventorySubtitle.textContent = mode === 'edit'
            ? 'Update the selected product information before saving.'
            : 'Create a new active product for the bakery catalog.';
        inventoryFeedback.textContent = '';

        if (product) {
            inventoryId.value = product.id;
            inventoryName.value = product.name;
            inventoryDescription.value = product.description;
            inventoryType.value = product.type;
        } else {
            inventoryForm.reset();
            inventoryId.value = '';
        }
    };

    dashboard.addEventListener('click', (event) => {
        const nav = event.target.closest('[data-nav]');
        if (nav) {
            showSection(nav.dataset.nav);
            return;
        }

        const addProduct = event.target.closest('[data-open-add-product]');
        if (addProduct) {
            openInventoryDrawer('add');
            return;
        }

        const editProduct = event.target.closest('[data-edit-product]');
        if (editProduct) {
            const row = editProduct.closest('[data-product-row]');
            openInventoryDrawer('edit', {
                id: row.dataset.productId,
                name: row.dataset.productName,
                description: row.dataset.productDescription,
                type: row.dataset.productType,
            });
            return;
        }

        const personTab = event.target.closest('[data-person-tab]');
        if (personTab) {
            const key = personTab.dataset.personTab;
            personTabs.forEach((tab) => {
                tab.dataset.active = tab.dataset.personTab === key ? 'true' : 'false';
            });
            personPanels.forEach((panel) => {
                panel.hidden = panel.dataset.personPanel !== key;
            });
            customerPanel.hidden = true;
            return;
        }

        const removeProduct = event.target.closest('[data-remove-product]');
        if (removeProduct) {
            const row = removeProduct.closest('[data-product-row]');
            openModal({
                title: 'Remove Product',
                message: 'Are you sure you want to remove this product?',
                confirmLabel: 'Remove',
                productId: row.dataset.productId,
            });
            return;
        }

        const orderAction = event.target.closest('[data-order-action]');
        if (orderAction) {
            const orderCard = orderAction.closest('[data-order-card]');
            const action = orderAction.dataset.orderAction;

            if (action === 'ready') {
                openModal({
                    title: 'Mark as Complete',
                    message: 'Mark this order as complete?',
                    confirmLabel: 'Complete',
                    orderId: orderCard.dataset.orderId,
                });
                return;
            }

            if (action === 'mark-paid') {
                orderCard.dataset.paymentStatus = 'paid';
                orderCard.querySelector('[data-payment-status]').textContent = 'Paid';
                orderAction.hidden = true;
                return;
            }

            const nextStatus = action === 'accept' ? 'preparing' : action === 'preparing' ? 'ready' : null;
            if (nextStatus) {
                orderCard.dataset.orderStatus = nextStatus;
                orderAction.hidden = true;
                const nextButton = orderCard.querySelector(`[data-order-action="${nextStatus}"]`);
                if (nextButton) nextButton.hidden = false;
            }
            return;
        }

        const viewCustomer = event.target.closest('[data-view-person]');
        if (viewCustomer) {
            const row = viewCustomer.closest('[data-person-card]');
            customerPanel.hidden = false;
            customerPanelTitle.textContent = row.dataset.personName;
            customerPanelMeta.textContent = `${row.dataset.personRole} · ${row.dataset.personEmail}`;
            customerPanel.querySelector('[data-customer-panel-body]').innerHTML = row.querySelector('[data-person-details]').innerHTML;
            return;
        }

        const togglePerson = event.target.closest('[data-toggle-person]');
        if (togglePerson) {
            const row = togglePerson.closest('[data-person-card]');
            openModal({
                title: row.dataset.personStatus === 'active' ? 'Suspend Account' : 'Unsuspend Account',
                message: row.dataset.personStatus === 'active'
                    ? 'Are you sure you want to suspend this account?'
                    : 'Are you sure you want to unsuspend this account?',
                confirmLabel: row.dataset.personStatus === 'active' ? 'Suspend' : 'Unsuspend',
                personId: row.dataset.personId,
            });
            return;
        }

        const removeNotification = event.target.closest('[data-remove-notification]');
        if (removeNotification) {
            removeNotification.closest('[data-notification-item]').hidden = true;
            notificationCount.textContent = String(dashboard.querySelectorAll('[data-notification-item]:not([hidden])').length);
            return;
        }

        const clearNotifications = event.target.closest('[data-clear-notifications]');
        if (clearNotifications) {
            dashboard.querySelectorAll('[data-notification-item]').forEach((item) => item.hidden = true);
            notificationCount.textContent = '0';
        }
    });

    inventoryForm?.addEventListener('submit', (event) => {
        event.preventDefault();
        inventoryFeedback.textContent = inventoryForm.dataset.mode === 'edit' ? 'Product updated' : 'Product added';
        closeInventoryDrawer();
    });

    modalConfirm?.addEventListener('click', () => {
        if (!modalAction) return;

        if (modalAction.productId) {
            const productRow = dashboard.querySelector(`[data-product-row][data-product-id="${modalAction.productId}"]`);
            if (productRow) productRow.hidden = true;
        }

        if (modalAction.orderId) {
            const orderCard = dashboard.querySelector(`[data-order-card][data-order-id="${modalAction.orderId}"]`);
            if (orderCard) orderCard.hidden = true;
        }

        if (modalAction.personId) {
            const personCard = dashboard.querySelector(`[data-person-card][data-person-id="${modalAction.personId}"]`);
            if (personCard) {
                const current = personCard.dataset.personStatus;
                personCard.dataset.personStatus = current === 'active' ? 'inactive' : 'active';
                personCard.querySelector('[data-person-status]').textContent = current === 'active' ? 'Suspended' : 'Active';
            }
        }

        closeModal();
    });

    modalCancel?.addEventListener('click', closeModal);
    modalShell?.addEventListener('click', (event) => {
        if (event.target === modalShell) closeModal();
    });

    inventoryDrawerClose?.addEventListener('click', closeInventoryDrawer);

    showSection(dashboard.dataset.defaultSection || 'dashboard');
}
