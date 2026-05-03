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
    const adminMessageSearch = dashboard.querySelector('[data-admin-message-search]');
    const adminMessageList = dashboard.querySelector('[data-admin-message-list]');
    const adminMessageUnread = dashboard.querySelector('[data-admin-message-unread]');
    const adminMessageAvatar = dashboard.querySelector('[data-admin-message-avatar]');
    const adminMessageName = dashboard.querySelector('[data-admin-message-name]');
    const adminMessageSubtitle = dashboard.querySelector('[data-admin-message-subtitle]');
    const adminMessageLabel = dashboard.querySelector('[data-admin-message-label]');
    const adminMessageFeed = dashboard.querySelector('[data-admin-message-feed]');
    const adminMessageDraft = dashboard.querySelector('[data-admin-message-draft]');
    const adminMessageSend = dashboard.querySelector('[data-admin-message-send]');
    const adminMessageMarkRead = dashboard.querySelector('[data-admin-message-mark-read]');
    const walkinOrderModal = document.getElementById('walkin-order-modal');
    const openWalkinOrderBtn = dashboard.querySelector('[data-open-walkin-order]');
    const closeWalkinOrderButtons = Array.from(walkinOrderModal?.querySelectorAll('[data-close-walkin-order]') || []);
    const bulkUploadModal = document.getElementById('bulk-upload-modal');
    const openBulkUploadBtn = dashboard.querySelector('[data-open-bulk-upload]');
    const closeBulkUploadButtons = Array.from(bulkUploadModal?.querySelectorAll('[data-close-bulk-upload]') || []);
    const walkinItemsContainer = walkinOrderModal?.querySelector('[data-walkin-items]');
    const addWalkinItemButton = walkinOrderModal?.querySelector('[data-add-walkin-item]');
    const walkinItemTemplate = dashboard.querySelector('[data-walkin-item-template]');
    const downloadBulkTemplateButton = bulkUploadModal?.querySelector('[data-download-bulk-template]');

    const reportDataElement = document.getElementById('admin-report-data');
    const reportData = reportDataElement ? JSON.parse(reportDataElement.textContent) : null;
    const productOptionsElement = document.getElementById('admin-product-options');
    const productOptions = productOptionsElement ? JSON.parse(productOptionsElement.textContent) : [];
    const adminMessagesElement = document.getElementById('admin-messages-data');
    const adminMessages = adminMessagesElement ? JSON.parse(adminMessagesElement.textContent) : [];
    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';

    const paginationKeys = ['inventory', 'orders', 'customers', 'admins', 'notifications'];
    const paginations = {
        inventory: { page: 1, size: 10 },
        orders: { page: 1, size: 10 },
        customers: { page: 1, size: 10 },
        admins: { page: 1, size: 10 },
        notifications: { page: 1, size: 10 },
    };
    const filters = {
        inventory: { query: '', value: 'all' },
        orders: { query: '', value: 'all' },
        notifications: { query: '', value: 'all' },
    };

    let modalAction = null;
    let activeAdminMessageId = adminMessages[0]?.id || null;

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

    const openDialog = (dialog) => {
        dialog?.removeAttribute('hidden');
    };

    const closeDialog = (dialog) => {
        dialog?.setAttribute('hidden', '');
    };

    const syncWalkinRows = () => {
        const rows = Array.from(walkinItemsContainer?.querySelectorAll('[data-walkin-item]') || []);
        rows.forEach((row, index) => {
            const productSelect = row.querySelector('select');
            const quantityInput = row.querySelector('input[type="number"]');
            if (productSelect) {
                productSelect.name = `items[${index}][product_id]`;
            }
            if (quantityInput) {
                quantityInput.name = `items[${index}][quantity]`;
            }
            const removeButton = row.querySelector('[data-remove-walkin-item]');
            if (removeButton) {
                removeButton.disabled = rows.length === 1;
                removeButton.classList.toggle('opacity-50', rows.length === 1);
            }
        });
    };

    const buildProductOptions = (select, selectedValue = '') => {
        select.innerHTML = '';
        const placeholder = document.createElement('option');
        placeholder.value = '';
        placeholder.textContent = 'Select a product';
        select.appendChild(placeholder);

        productOptions.forEach((product) => {
            const option = document.createElement('option');
            option.value = product.id;
            option.textContent = `${product.name} - ${product.price}`;
            option.selected = String(selectedValue) === String(product.id);
            select.appendChild(option);
        });
    };

    const addWalkinItemRow = ({ productId = '', quantity = 1 } = {}) => {
        if (!walkinItemsContainer || !walkinItemTemplate) {
            return;
        }

        const fragment = walkinItemTemplate.content.cloneNode(true);
        const row = fragment.querySelector('[data-walkin-item]');
        const select = row.querySelector('[data-walkin-product]');
        const quantityInput = row.querySelector('[data-walkin-quantity]');

        buildProductOptions(select, productId);
        quantityInput.value = quantity;

        walkinItemsContainer.appendChild(fragment);
        syncWalkinRows();
    };

    const downloadBulkTemplate = () => {
        const template = [
            'name,description,price,category,image_url,is_active',
            '"Classic Pandesal","Soft bread rolls",45,Bread,,1',
            '"Cheese Roll","Butter bread with cheese",65,Pastries,https://example.com/cheese-roll.jpg,1',
        ].join('\n');

        downloadContent('inventory-bulk-template.csv', template, 'text/csv;charset=utf-8;');
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

    const getFilteredAdminMessages = () => {
        const query = (adminMessageSearch?.value || '').trim().toLowerCase();

        if (!query) {
            return adminMessages;
        }

        return adminMessages.filter((conversation) => [
            conversation.name,
            conversation.label,
            conversation.preview,
        ].join(' ').toLowerCase().includes(query));
    };

    const getActiveAdminMessage = () => adminMessages.find((conversation) => conversation.id === activeAdminMessageId) || null;

    const renderAdminMessageSummary = () => {
        if (!adminMessageUnread) {
            return;
        }

        const unread = adminMessages.filter((conversation) => conversation.unread).length;
        adminMessageUnread.textContent = `${unread} unread`;

        const navCount = dashboard.querySelector('[data-nav-count="messages"]');
        if (navCount) {
            navCount.textContent = String(unread);
        }
    };

    const renderAdminMessageList = () => {
        if (!adminMessageList) {
            return;
        }

        const filtered = getFilteredAdminMessages();

        if (!filtered.length) {
            adminMessageList.innerHTML = `
                <div class="rounded-[24px] border border-dashed border-[#E4D5C8] bg-white px-5 py-8 text-center text-sm text-[#7F746D]">
                    No conversations match your search.
                </div>
            `;
            return;
        }

        adminMessageList.innerHTML = filtered.map((conversation) => `
            <button
                type="button"
                data-admin-message-thread="${conversation.id}"
                class="message-thread-button mb-2 flex w-full items-start gap-3 rounded-[22px] border border-transparent px-3 py-3 text-left transition-all hover:bg-white/80"
                data-active="${conversation.id === activeAdminMessageId ? 'true' : 'false'}"
            >
                <div class="flex h-12 w-12 shrink-0 items-center justify-center rounded-full bg-gradient-to-br from-[#C9876C] to-[#B8765B] text-sm font-bold text-white shadow-sm">
                    ${conversation.avatar}
                </div>
                <div class="min-w-0 flex-1">
                    <div class="flex items-center justify-between gap-3">
                        <p class="truncate text-sm font-semibold text-[#453A35]">${conversation.name}</p>
                        <span class="shrink-0 text-[11px] font-semibold uppercase tracking-[0.16em] text-[#B79F8F]">${conversation.time}</span>
                    </div>
                    <p class="mt-1 text-xs font-medium uppercase tracking-[0.16em] text-[#C68D64]">${conversation.label}</p>
                    <p class="mt-1 truncate text-sm text-[#796F68]">${conversation.preview}</p>
                </div>
                ${conversation.unread ? '<span class="mt-1 h-2.5 w-2.5 shrink-0 rounded-full bg-[#D96D45]"></span>' : ''}
            </button>
        `).join('');
    };

    const renderAdminMessageFeed = () => {
        const conversation = getActiveAdminMessage();

        if (!adminMessageName || !adminMessageSubtitle || !adminMessageAvatar || !adminMessageLabel || !adminMessageFeed) {
            return;
        }

        if (!conversation) {
            adminMessageAvatar.textContent = '--';
            adminMessageName.textContent = 'Select a thread';
            adminMessageSubtitle.textContent = 'Choose a conversation from the inbox.';
            adminMessageLabel.textContent = 'Inbox';
            adminMessageFeed.innerHTML = `
                <div class="flex h-full min-h-[20rem] items-center justify-center">
                    <div class="text-center">
                        <h3 class="text-2xl font-bold text-[#50382A]" style="font-family: 'Sora', sans-serif;">No conversation selected</h3>
                        <p class="mt-3 text-sm text-[#7F746C]">Pick a message thread from the left to continue chatting.</p>
                    </div>
                </div>
            `;
            return;
        }

        adminMessageAvatar.textContent = conversation.avatar;
        adminMessageName.textContent = conversation.name;
        adminMessageSubtitle.textContent = conversation.subtitle;
        adminMessageLabel.textContent = conversation.label;
        adminMessageFeed.innerHTML = `
            <div class="mx-auto flex min-h-full max-w-4xl flex-col justify-end gap-4">
                <div class="self-center rounded-full bg-white/85 px-4 py-1.5 text-[11px] font-semibold uppercase tracking-[0.18em] text-[#B7A08F] shadow-sm">
                    Today
                </div>
                ${conversation.messages.map((message) => `
                    <div class="flex ${message.sender === 'me' ? 'justify-end' : 'justify-start'}">
                        <div class="max-w-[85%] rounded-[24px] px-4 py-3 shadow-[0_18px_36px_-32px_rgba(95,59,35,0.5)] md:max-w-[70%] ${message.sender === 'me'
                            ? 'rounded-br-[8px] bg-[#C9876C] text-white'
                            : 'rounded-bl-[8px] bg-white text-[#5D534D] ring-1 ring-[#E9DDD3]'}">
                            <p class="text-sm leading-6">${message.text}</p>
                            <p class="mt-2 text-[11px] font-semibold uppercase tracking-[0.14em] ${message.sender === 'me' ? 'text-white/75' : 'text-[#B49A89]'}">${message.time}</p>
                        </div>
                    </div>
                `).join('')}
            </div>
        `;

        adminMessageFeed.scrollTop = adminMessageFeed.scrollHeight;
    };

    const renderAdminMessages = () => {
        renderAdminMessageSummary();
        renderAdminMessageList();
        renderAdminMessageFeed();
    };

    const getPageItems = (key) => {
        return Array.from(dashboard.querySelectorAll(`[data-page-item="${key}"]`)).filter((item) => item.dataset.removed !== '1');
    };

    const matchesInventoryFilter = (item) => {
        const query = filters.inventory.query.trim().toLowerCase();
        const category = filters.inventory.value;
        const haystack = [
            item.dataset.productName,
            item.dataset.productDescription,
            item.dataset.productCategory,
        ].join(' ').toLowerCase();

        if (category !== 'all' && item.dataset.productCategory !== category) {
            return false;
        }

        return !query || haystack.includes(query);
    };

    const matchesOrdersFilter = (item) => {
        const query = filters.orders.query.trim().toLowerCase();
        const selected = filters.orders.value;
        const haystack = [
            item.dataset.orderId,
            item.dataset.orderCustomer,
            item.dataset.orderStatus,
            item.dataset.paymentStatus,
            item.textContent,
        ].join(' ').toLowerCase();

        if (selected !== 'all') {
            const matchesSelected = item.dataset.orderStatus === selected
                || item.dataset.paymentStatus === selected
                || (selected === 'custom' && item.dataset.orderCustom === '1');

            if (!matchesSelected) {
                return false;
            }
        }

        return !query || haystack.includes(query);
    };

    const matchesNotificationsFilter = (item) => {
        const query = filters.notifications.query.trim().toLowerCase();
        const selected = filters.notifications.value;
        const haystack = [
            item.dataset.notificationTitle,
            item.dataset.notificationMessage,
            item.dataset.notificationCustomer,
            item.dataset.notificationOrder,
        ].join(' ').toLowerCase();

        if (selected !== 'all' && item.dataset.notificationCategory !== selected) {
            return false;
        }

        return !query || haystack.includes(query);
    };

    const getFilteredItems = (key) => {
        const items = getPageItems(key);

        if (key === 'inventory') {
            return items.filter(matchesInventoryFilter);
        }

        if (key === 'orders') {
            return items.filter(matchesOrdersFilter);
        }

        if (key === 'notifications') {
            return items.filter(matchesNotificationsFilter);
        }

        return items;
    };

    const getTotalPages = (key) => {
        const total = getFilteredItems(key).length;
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
            notificationCount.textContent = String(getFilteredItems('notifications').length);
        }
    };

    const updateNavCounts = () => {
        const counts = {
            dashboard: '•',
            inventory: getPageItems('inventory').length,
            orders: getPageItems('orders').length,
            customers: getPageItems('customers').length,
            notifications: getPageItems('notifications').length,
            messages: adminMessages.filter((conversation) => conversation.unread).length,
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
        const allItems = getPageItems(key);
        const items = getFilteredItems(key);
        const total = items.length;
        const pages = Math.max(1, Math.ceil(total / state.size));

        if (state.page > pages) {
            state.page = pages;
        }

        const start = (state.page - 1) * state.size;
        const end = start + state.size;

        allItems.forEach((item) => {
            item.hidden = true;
        });

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

        if (name === 'messages') {
            renderAdminMessages();
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
        if (inventoryDrawer) {
            inventoryDrawer.hidden = false;
        }
    };

    const closeInventoryDrawer = () => {
        inventoryDrawer.hidden = true;
    };

    const closeModal = () => {
        modalAction = null;
        modalShell.hidden = true;
    };

    const setInventoryMode = (mode, product = null) => {
        if (inventoryForm) {
            inventoryForm.dataset.mode = mode;
        }
        if (inventoryTitle) {
            inventoryTitle.textContent = mode === 'edit' ? 'Edit Product' : 'Add Product';
        }
        if (inventorySubtitle) {
            inventorySubtitle.textContent = mode === 'edit'
                ? 'Update the selected product information before saving.'
                : 'Create a new active product for the bakery catalog.';
        }
        if (inventoryFeedback) {
            inventoryFeedback.textContent = '';
        }

        if (product) {
            if (inventoryId) inventoryId.value = product.id;
            if (inventoryName) inventoryName.value = product.name;
            if (inventoryDescription) inventoryDescription.value = product.description;
            if (inventoryPrice) inventoryPrice.value = product.price;
            if (inventoryType) inventoryType.value = product.category || 'Bread';
            if (inventoryIsActive) {
                inventoryIsActive.value = product.is_active ? '1' : '0';
            }
            if (inventoryMethod) {
                inventoryMethod.disabled = false;
                inventoryMethod.value = 'PUT';
            }
        } else {
            if (inventoryForm) inventoryForm.reset();
            if (inventoryId) inventoryId.value = '';
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

    dashboard.querySelectorAll('[data-search-input]').forEach((input) => {
        input.addEventListener('input', (event) => {
            const key = event.target.dataset.searchInput;
            if (!filters[key]) {
                return;
            }

            filters[key].query = event.target.value || '';
            paginations[key].page = 1;
            renderPagination(key);
        });
    });

    dashboard.querySelectorAll('[data-filter-button]').forEach((button) => {
        button.addEventListener('click', () => {
            const key = button.dataset.filterButton;
            if (!filters[key]) {
                return;
            }

            filters[key].value = button.dataset.filterValue || 'all';
            paginations[key].page = 1;

            dashboard.querySelectorAll(`[data-filter-button="${key}"]`).forEach((candidate) => {
                candidate.dataset.active = candidate === button ? 'true' : 'false';
            });

            renderPagination(key);
        });
    });

    adminMessageSearch?.addEventListener('input', () => {
        renderAdminMessageList();
    });

    adminMessageSend?.addEventListener('click', () => {
        const draft = adminMessageDraft?.value.trim();
        const conversation = getActiveAdminMessage();

        if (!draft || !conversation) {
            return;
        }

        conversation.messages.push({
            id: Date.now(),
            sender: 'me',
            text: draft,
            time: 'Just now',
        });
        conversation.preview = draft;
        conversation.time = 'now';
        conversation.unread = false;
        adminMessageDraft.value = '';
        renderAdminMessages();
    });

    adminMessageMarkRead?.addEventListener('click', () => {
        const conversation = getActiveAdminMessage();

        if (!conversation) {
            return;
        }

        conversation.unread = false;
        renderAdminMessages();
        updateNavCounts();
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

        const messageThread = event.target.closest('[data-admin-message-thread]');
        if (messageThread) {
            activeAdminMessageId = Number(messageThread.dataset.adminMessageThread);
            const conversation = getActiveAdminMessage();
            if (conversation) {
                conversation.unread = false;
            }
            renderAdminMessages();
            updateNavCounts();
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

            const orderId = orderCard?.dataset.orderId;
            const requestConfig = {
                method: 'PATCH',
                headers: {
                    'X-CSRF-TOKEN': csrfToken,
                    'Accept': 'application/json',
                    'Content-Type': 'application/json',
                },
            };

            const submitWorkflowUpdate = async (url, payload) => {
                orderAction.disabled = true;

                try {
                    const response = await fetch(url, {
                        ...requestConfig,
                        body: JSON.stringify(payload),
                    });

                    const responsePayload = await response.json().catch(() => null);

                    if (!response.ok) {
                        throw new Error(responsePayload?.message || 'Unable to update the order right now.');
                    }

                    window.location.reload();
                } catch (error) {
                    window.alert(error.message || 'Unable to update the order right now.');
                    orderAction.disabled = false;
                }
            };

            if (action === 'mark-paid' && orderId) {
                submitWorkflowUpdate(`/admin/orders/${orderId}/payment-status`, { payment_status: 'paid' });
                return;
            }

            if (action === 'advance' && orderId) {
                const nextStatus = orderAction.dataset.nextStatus;

                if (!nextStatus) {
                    return;
                }

                submitWorkflowUpdate(`/admin/orders/${orderId}/status`, { status: nextStatus });
                return;
            }

            return;
        }

        const viewCustomer = event.target.closest('[data-view-person]');
        if (viewCustomer) {
            const row = viewCustomer.closest('[data-person-card]');
            customerPanel.hidden = false;
            customerPanelTitle.textContent = row.dataset.personName;
            customerPanelMeta.textContent = `${row.dataset.personRole} | ${row.dataset.personEmail}`;
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
        const productId = inventoryId ? inventoryId.value : '';
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
    openWalkinOrderBtn?.addEventListener('click', () => {
        openDialog(walkinOrderModal);
    });
    closeWalkinOrderButtons.forEach((button) => button.addEventListener('click', () => {
        closeDialog(walkinOrderModal);
    }));
    openBulkUploadBtn?.addEventListener('click', () => {
        openDialog(bulkUploadModal);
    });
    closeBulkUploadButtons.forEach((button) => button.addEventListener('click', () => {
        closeDialog(bulkUploadModal);
    }));
    addWalkinItemButton?.addEventListener('click', () => {
        addWalkinItemRow();
    });
    walkinItemsContainer?.addEventListener('click', (event) => {
        const removeButton = event.target.closest('[data-remove-walkin-item]');
        if (!removeButton) {
            return;
        }

        const rows = walkinItemsContainer.querySelectorAll('[data-walkin-item]');
        if (rows.length <= 1) {
            return;
        }

        removeButton.closest('[data-walkin-item]')?.remove();
        syncWalkinRows();
    });
    walkinOrderModal?.addEventListener('click', (event) => {
        if (event.target === walkinOrderModal) {
            closeDialog(walkinOrderModal);
        }
    });
    bulkUploadModal?.addEventListener('click', (event) => {
        if (event.target === bulkUploadModal) {
            closeDialog(bulkUploadModal);
        }
    });
    downloadBulkTemplateButton?.addEventListener('click', downloadBulkTemplate);

    syncWalkinRows();
    if (walkinItemsContainer && !walkinItemsContainer.children.length) {
        addWalkinItemRow();
    }

    renderLineChart();
    renderTypeBars();
    renderAdminMessages();

    paginationKeys.forEach((key) => renderPagination(key));
    updateNavCounts();
    syncPeoplePaginationControls(getActivePeopleKey());

    showSection(dashboard.dataset.defaultSection || 'dashboard');

    if (dashboard.dataset.openModal === 'walkin') {
        openDialog(walkinOrderModal);
    }

    if (dashboard.dataset.openModal === 'bulk-upload') {
        openDialog(bulkUploadModal);
    }
}
