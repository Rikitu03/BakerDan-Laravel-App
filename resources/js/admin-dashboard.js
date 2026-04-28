import './bootstrap';

const dashboard = document.querySelector('[data-admin-dashboard]');

if (dashboard) {
    const sidebarCompactButton = dashboard.querySelector('[data-sidebar-compact]');
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
    const inventoryPrice = dashboard.querySelector('[data-inventory-price]');
    const inventoryType = dashboard.querySelector('[data-inventory-type]');
    const inventoryIsActive = dashboard.querySelector('[data-inventory-is-active]');
    const inventoryMethod = dashboard.querySelector('[data-inventory-method]');
    const inventoryId = dashboard.querySelector('[data-inventory-id]');
    const customerPanel = dashboard.querySelector('[data-customer-panel]');
    const customerPanelTitle = dashboard.querySelector('[data-customer-panel-title]');
    const customerPanelMeta = dashboard.querySelector('[data-customer-panel-meta]');
    const notificationCount = dashboard.querySelector('[data-notification-count]');
    const inventoryEmptyState = dashboard.querySelector('[data-inventory-empty]');
    const lineChart = dashboard.querySelector('[data-line-chart]');
    const linePath = dashboard.querySelector('[data-line-path]');
    const linePoints = dashboard.querySelector('[data-line-points]');
    const lineLabels = dashboard.querySelector('[data-line-labels]');
    const typeBars = dashboard.querySelector('[data-type-bars]');
    const exportTarget = dashboard.querySelector('[data-export-target]');
    const exportFeedback = dashboard.querySelector('[data-export-feedback]');
    const currentSectionLabel = dashboard.querySelector('[data-current-section-label]');
    const personTabs = Array.from(dashboard.querySelectorAll('[data-person-tab]'));
    const personPanels = Array.from(dashboard.querySelectorAll('[data-person-panel]'));
    const paginationControlGroups = Array.from(dashboard.querySelectorAll('[data-pagination-controls]'));

    const reportDataElement = document.getElementById('admin-report-data');
    const reportData = reportDataElement ? JSON.parse(reportDataElement.textContent) : null;
    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';

    const paginationKeys = ['inventory', 'orders', 'customers', 'admins', 'notifications'];
    const paginations = {
        inventory: { page: 1, size: 10 },
        orders: { page: 1, size: 10 },
        customers: { page: 1, size: 10 },
        admins: { page: 1, size: 10 },
        notifications: { page: 1, size: 10 },
    };

    let modalAction = null;

    const sectionLabels = navButtons.reduce((acc, button) => {
        const key = button.dataset.nav;
        const textNode = button.querySelector('[data-sidebar-text]');
        acc[key] = textNode ? textNode.textContent.trim() : key;
        return acc;
    }, {});

    const toCsv = (rows) => {
        if (!Array.isArray(rows) || !rows.length) {
            return '';
        }

        const headers = Object.keys(rows[0]);
        const lines = [headers.join(',')];

        rows.forEach((row) => {
            const values = headers.map((header) => {
                const value = row[header] ?? '';
                const escaped = String(value).replace(/"/g, '""');
                return `"${escaped}"`;
            });
            lines.push(values.join(','));
        });

        return lines.join('\n');
    };

    const downloadContent = (filename, content, mimeType) => {
        const blob = new Blob([content], { type: mimeType });
        const url = URL.createObjectURL(blob);
        const link = document.createElement('a');
        link.href = url;
        link.download = filename;
        document.body.appendChild(link);
        link.click();
        link.remove();
        URL.revokeObjectURL(url);
    };

    const getReportRows = (target) => {
        if (!reportData) {
            return [];
        }

        if (target === 'summary') {
            return [{
                generated_at: new Date().toISOString(),
                total_products: reportData.products.length,
                total_orders: reportData.orders.length,
                total_customers: reportData.customers.length,
                total_admins: reportData.admins.length,
                total_notifications: reportData.notifications.length,
            }];
        }

        return reportData[target] || [];
    };

    const renderLineChart = () => {
        if (!lineChart || !linePath || !linePoints || !lineLabels || !reportData?.weeklyCompletions?.length) {
            return;
        }

        const data = reportData.weeklyCompletions;
        const width = 680;
        const height = 240;
        const paddingX = 54;
        const chartBottom = 190;
        const chartTop = 45;
        const maxValue = Math.max(...data.map((item) => item.value), 1);
        const stepX = (width - paddingX * 2) / Math.max(data.length - 1, 1);

        const points = data.map((item, index) => {
            const x = paddingX + index * stepX;
            const y = chartBottom - ((item.value / maxValue) * (chartBottom - chartTop));
            return { x, y, label: item.label, value: item.value };
        });

        linePath.setAttribute('d', points.map((point, index) => `${index === 0 ? 'M' : 'L'} ${point.x.toFixed(2)} ${point.y.toFixed(2)}`).join(' '));
        linePoints.innerHTML = points.map((point) => `<circle class="chart-point" cx="${point.x.toFixed(2)}" cy="${point.y.toFixed(2)}" r="4"></circle>`).join('');
        lineLabels.innerHTML = points.map((point) => `<span>${point.label}</span>`).join('');
    };

    const renderTypeBars = () => {
        if (!typeBars || !reportData?.productTypeBreakdown?.length) {
            return;
        }

        const data = reportData.productTypeBreakdown;
        const maxValue = Math.max(...data.map((item) => item.value), 1);

        typeBars.innerHTML = data.map((item) => {
            const width = (item.value / maxValue) * 100;
            return `
                <div>
                    <div class="mb-1 flex items-center justify-between text-sm font-semibold text-slate-600">
                        <span>${item.label}</span>
                        <span>${item.value}</span>
                    </div>
                    <div class="h-3 rounded-full bg-slate-100">
                        <div class="h-3 rounded-full bg-gradient-to-r from-[var(--brand-deep)] to-[var(--brand)]" style="width: ${width.toFixed(2)}%"></div>
                    </div>
                </div>
            `;
        }).join('');
    };

    const getPageItems = (key) => {
        return Array.from(dashboard.querySelectorAll(`[data-page-item="${key}"]`)).filter((item) => item.dataset.removed !== '1');
    };

    const getTotalPages = (key) => {
        const total = getPageItems(key).length;
        return Math.max(1, Math.ceil(total / paginations[key].size));
    };

    const getActivePeopleKey = () => {
        const activeTab = personTabs.find((tab) => tab.dataset.active === 'true');
        return activeTab?.dataset.personTab === 'admins' ? 'admins' : 'customers';
    };

    const syncPeoplePaginationControls = (activeKey) => {
        paginationControlGroups.forEach((group) => {
            const groupKey = group.dataset.paginationControls;
            if (groupKey === 'customers' || groupKey === 'admins') {
                group.hidden = groupKey !== activeKey;
            }
        });
    };

    const updateNotificationCount = () => {
        if (notificationCount) {
            notificationCount.textContent = String(getPageItems('notifications').length);
        }
    };

    const updateNavCounts = () => {
        const counts = {
            dashboard: '•',
            inventory: getPageItems('inventory').length,
            orders: getPageItems('orders').length,
            customers: getPageItems('customers').length,
            notifications: getPageItems('notifications').length,
        };

        Object.entries(counts).forEach(([key, value]) => {
            const target = dashboard.querySelector(`[data-nav-count="${key}"]`);
            if (target) {
                target.textContent = String(value);
            }
        });
    };

    const renderPagination = (key) => {
        const state = paginations[key];
        const items = getPageItems(key);
        const total = items.length;
        const pages = Math.max(1, Math.ceil(total / state.size));

        if (state.page > pages) {
            state.page = pages;
        }

        const start = (state.page - 1) * state.size;
        const end = start + state.size;

        items.forEach((item, index) => {
            item.hidden = index < start || index >= end;
        });

        const info = dashboard.querySelector(`[data-page-info="${key}"]`);
        if (info) {
            const from = total === 0 ? 0 : start + 1;
            const to = Math.min(end, total);
            info.textContent = `Showing ${from}-${to} of ${total}`;
        }

        const prevButton = dashboard.querySelector(`[data-page-prev="${key}"]`);
        const nextButton = dashboard.querySelector(`[data-page-next="${key}"]`);

        if (prevButton) {
            prevButton.disabled = state.page <= 1;
        }
        if (nextButton) {
            nextButton.disabled = state.page >= pages;
        }

        if (key === 'inventory' && inventoryEmptyState) {
            inventoryEmptyState.hidden = total > 0;
        }

        if (key === 'notifications') {
            updateNotificationCount();
        }

        updateNavCounts();
    };

    const showSection = (name) => {
        sections.forEach((section) => {
            section.hidden = section.dataset.section !== name;
        });

        navButtons.forEach((button) => {
            button.dataset.active = button.dataset.nav === name ? 'true' : 'false';
        });

        if (currentSectionLabel) {
            currentSectionLabel.textContent = sectionLabels[name] || 'Dashboard';
        }

        if (name === 'customers') {
            const activePeople = getActivePeopleKey();
            syncPeoplePaginationControls(activePeople);
            renderPagination(activePeople);
        }

        if (name === 'inventory' || name === 'orders' || name === 'notifications') {
            renderPagination(name);
        }
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
            inventoryPrice.value = product.price;
            inventoryType.value = product.category || 'Bread';
            if (inventoryIsActive) {
                inventoryIsActive.value = product.is_active ? '1' : '0';
            }
            if (inventoryMethod) {
                inventoryMethod.disabled = false;
                inventoryMethod.value = 'PUT';
            }
        } else {
            inventoryForm.reset();
            inventoryId.value = '';
            if (inventoryType) {
                inventoryType.value = 'Bread';
            }
            if (inventoryIsActive) {
                inventoryIsActive.value = '1';
            }
            if (inventoryMethod) {
                inventoryMethod.disabled = true;
                inventoryMethod.value = '';
            }
        }
    };

    dashboard.querySelectorAll('[data-page-size]').forEach((select) => {
        select.addEventListener('change', (event) => {
            const key = event.target.dataset.pageSize;
            paginations[key].size = Number(event.target.value);
            paginations[key].page = 1;
            renderPagination(key);
        });
    });

    dashboard.addEventListener('click', (event) => {
        const exportButton = event.target.closest('[data-export-format]');
        if (exportButton) {
            const format = exportButton.dataset.exportFormat;
            const target = exportTarget?.value || 'summary';
            const rows = getReportRows(target);
            const stamp = new Date().toISOString().slice(0, 10);

            if (format === 'csv') {
                const csv = toCsv(rows);
                if (!csv) {
                    if (exportFeedback) exportFeedback.textContent = 'No data available for CSV export.';
                    return;
                }
                downloadContent(`report-${target}-${stamp}.csv`, csv, 'text/csv;charset=utf-8;');
            }

            if (format === 'json') {
                downloadContent(`report-${target}-${stamp}.json`, JSON.stringify(rows, null, 2), 'application/json;charset=utf-8;');
            }

            if (exportFeedback) {
                exportFeedback.textContent = `Report exported: ${target.toUpperCase()} (${format.toUpperCase()})`;
            }
            return;
        }

        const nav = event.target.closest('[data-nav]');
        if (nav) {
            showSection(nav.dataset.nav);
            return;
        }

        const prevPage = event.target.closest('[data-page-prev]');
        if (prevPage) {
            const key = prevPage.dataset.pagePrev;
            paginations[key].page = Math.max(1, paginations[key].page - 1);
            renderPagination(key);
            return;
        }

        const nextPage = event.target.closest('[data-page-next]');
        if (nextPage) {
            const key = nextPage.dataset.pageNext;
            paginations[key].page = Math.min(getTotalPages(key), paginations[key].page + 1);
            renderPagination(key);
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
                price: row.dataset.productPrice,
                category: row.dataset.productCategory,
                is_active: row.dataset.productIsActive === '1',
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
            syncPeoplePaginationControls(key);
            renderPagination(key);
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
            removeNotification.closest('[data-notification-item]').dataset.removed = '1';
            renderPagination('notifications');
            return;
        }

        const clearNotifications = event.target.closest('[data-clear-notifications]');
        if (clearNotifications) {
            dashboard.querySelectorAll('[data-notification-item]').forEach((item) => {
                item.dataset.removed = '1';
            });
            paginations.notifications.page = 1;
            renderPagination('notifications');
        }
    });

    inventoryForm?.addEventListener('submit', (event) => {
        event.preventDefault();

        const mode = inventoryForm.dataset.mode || 'add';
        const productId = inventoryId.value;
        const baseUrl = inventoryForm.dataset.updateUrlBase;

        if (mode === 'edit' && productId) {
            inventoryForm.action = `${baseUrl}/${productId}`;
            if (inventoryMethod) {
                inventoryMethod.disabled = false;
                inventoryMethod.value = 'PUT';
            }
        } else {
            inventoryForm.action = inventoryForm.dataset.storeUrl || inventoryForm.action;
            if (inventoryMethod) {
                inventoryMethod.disabled = true;
                inventoryMethod.value = '';
            }
        }

        inventoryForm.submit();
    });

    modalConfirm?.addEventListener('click', () => {
        if (!modalAction) return;

        if (modalAction.productId) {
            fetch(`/admin/inventory/${modalAction.productId}`, {
                method: 'DELETE',
                headers: {
                    'X-CSRF-TOKEN': csrfToken,
                    'Accept': 'application/json',
                },
            }).then(() => {
                window.location.reload();
            });
            closeModal();
            return;
        }

        if (modalAction.orderId) {
            const orderCard = dashboard.querySelector(`[data-order-card][data-order-id="${modalAction.orderId}"]`);
            if (orderCard) {
                orderCard.dataset.removed = '1';
                renderPagination('orders');
            }
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

    sidebarCompactButton?.addEventListener('click', () => {
        dashboard.classList.toggle('is-sidebar-compact');
    });

    inventoryDrawerClose?.addEventListener('click', closeInventoryDrawer);

    renderLineChart();
    renderTypeBars();

    paginationKeys.forEach((key) => renderPagination(key));
    updateNavCounts();
    syncPeoplePaginationControls(getActivePeopleKey());

    showSection(dashboard.dataset.defaultSection || 'dashboard');
}
