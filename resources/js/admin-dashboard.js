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
    const inventoryDrawer = dashboard.querySelector('[data-inventory-drawer]');
    const inventoryDrawerClose = inventoryDrawer?.querySelector('[data-inventory-close]');
    const inventoryForm = inventoryDrawer?.querySelector('[data-inventory-form]');
    const inventoryTitle = inventoryDrawer?.querySelector('[data-inventory-title]');
    const inventorySubtitle = inventoryDrawer?.querySelector('[data-inventory-subtitle]');
    const inventoryFeedback = inventoryDrawer?.querySelector('[data-inventory-feedback]');
    const inventoryName = inventoryForm?.querySelector('[data-inventory-name]');
    const inventoryDescription = inventoryForm?.querySelector('[data-inventory-description]');
    const inventoryPrice = inventoryForm?.querySelector('[data-inventory-price]');
    const inventoryType = inventoryForm?.querySelector('[data-inventory-type]');
    const inventoryIsActive = inventoryForm?.querySelector('[data-inventory-is-active]');
    const inventoryMethod = inventoryForm?.querySelector('[data-inventory-method]');
    const inventoryId = inventoryForm?.querySelector('[data-inventory-id]');
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

    if (window.axios) {
        window.axios.defaults.headers.common['X-CSRF-TOKEN'] = csrfToken;
    }

    const paginationKeys = ['inventory', 'promos', 'orders', 'customers', 'admins', 'notifications'];
    const paginations = {
        inventory: { page: 1, size: 10 },
        promos: { page: 1, size: 10 },
        orders: { page: 1, size: 10 },
        customers: { page: 1, size: 10 },
        admins: { page: 1, size: 10 },
        notifications: { page: 1, size: 10 },
    };
    const filters = {
        inventory: { query: '', value: 'all' },
        promos: { query: '', value: 'all' },
        orders: { query: '', value: 'all' },
        notifications: { query: '', value: 'all' },
    };

    let modalAction = null;
    let activeAdminMessageId = adminMessages[0]?.id || null;
    let adminMessagePollTimer = null;
    let adminConversationPollTimer = null;
    let adminConversationsRequest = null;
    let adminMessagesRequest = null;
    const adminUserId = Number(reportData?.user?.user_id || reportData?.user?.id || 0);
    const ADMIN_ACTIVE_MESSAGE_POLL_MS = 1000;
    const ADMIN_CONVERSATION_POLL_MS = 1500;

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

    const escapeHtml = (value) => String(value ?? '')
        .replace(/&/g, '&amp;')
        .replace(/</g, '&lt;')
        .replace(/>/g, '&gt;')
        .replace(/"/g, '&quot;')
        .replace(/'/g, '&#039;');

    const formatMessageTime = (dateValue) => {
        if (!dateValue) {
            return 'Now';
        }

        return new Date(dateValue).toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' });
    };

    const newestAdminMessageId = (conversation) => Math.max(
        0,
        ...(conversation?.messages || []).map((message) => Number(message.id) || 0),
    );

    const normalizeAdminMessage = (message) => ({
        id: message.id,
        sender: Number(message.sender_id) === adminUserId ? 'me' : 'them',
        text: message.content,
        time: formatMessageTime(message.created_at),
    });

    const normalizeAdminConversation = (conversation, existing = null) => ({
        id: conversation.id,
        name: conversation.name || 'Customer',
        avatar: conversation.avatar || String(conversation.name || 'Customer').slice(0, 2).toUpperCase(),
        label: conversation.label || 'Direct Message',
        subtitle: conversation.subtitle || 'Customer support',
        time: conversation.last_message_at_human || conversation.time || 'No messages',
        unread: Number(conversation.unread_count || 0) > 0 || Boolean(conversation.unread),
        preview: conversation.last_message_content || conversation.preview || 'No messages yet',
        messages: existing?.messages || (Array.isArray(conversation.messages) ? conversation.messages : []),
        loaded: existing?.loaded ?? Array.isArray(conversation.messages),
        latestMessageId: Number(conversation.latest_message_id || existing?.latestMessageId || newestAdminMessageId(existing || conversation)) || 0,
    });

    adminMessages.forEach((conversation) => {
        conversation.loaded = true;
        conversation.latestMessageId = newestAdminMessageId(conversation);
    });

    const sortAdminConversations = () => {
        adminMessages.sort((first, second) => Number(second.latestMessageId || 0) - Number(first.latestMessageId || 0));
    };

    const mergeAdminMessages = (conversation, incomingMessages = []) => {
        if (!conversation || !incomingMessages.length) {
            return false;
        }

        const seen = new Set(conversation.messages.map((message) => Number(message.id)));
        let changed = false;

        incomingMessages.forEach((message) => {
            const normalized = normalizeAdminMessage(message);
            if (seen.has(Number(normalized.id))) {
                return;
            }

            conversation.messages.push(normalized);
            conversation.latestMessageId = Math.max(Number(conversation.latestMessageId || 0), Number(normalized.id || 0));
            conversation.preview = normalized.text;
            conversation.time = 'Just now';
            seen.add(Number(normalized.id));
            changed = true;
        });

        if (changed) {
            conversation.messages.sort((first, second) => Number(first.id) - Number(second.id));
        }

        return changed;
    };

    const upsertAdminConversation = (summary) => {
        const existing = adminMessages.find((conversation) => Number(conversation.id) === Number(summary.id));
        const normalized = normalizeAdminConversation(summary, existing);

        if (existing) {
            Object.assign(existing, normalized, {
                messages: existing.messages,
                loaded: existing.loaded,
                latestMessageId: Math.max(Number(existing.latestMessageId || 0), Number(normalized.latestMessageId || 0)),
            });
            return existing;
        }

        adminMessages.push(normalized);
        return normalized;
    };

    const fetchAdminMessages = async (conversationId, { incremental = false, silent = false } = {}) => {
        const conversation = adminMessages.find((item) => Number(item.id) === Number(conversationId));
        if (!conversation || (conversation.loaded && !incremental)) {
            return;
        }

        if (adminMessagesRequest) {
            if (incremental) {
                return;
            }

            adminMessagesRequest.abort();
        }

        adminMessagesRequest = new AbortController();

        try {
            const response = await axios.get(`/api/conversations/${conversationId}/messages`, {
                params: incremental ? { after_id: newestAdminMessageId(conversation) } : {},
                signal: adminMessagesRequest.signal,
            });
            const incoming = Array.isArray(response.data) ? response.data : [];
            const changed = incremental
                ? mergeAdminMessages(conversation, incoming)
                : (() => {
                    conversation.messages = incoming.map(normalizeAdminMessage);
                    conversation.latestMessageId = newestAdminMessageId(conversation);
                    return true;
                })();

            conversation.loaded = true;
            if (changed && (!silent || Number(conversation.id) === Number(activeAdminMessageId))) {
                renderAdminMessages();
                updateNavCounts();
            }
        } catch (error) {
            if (error.name !== 'CanceledError' && error.code !== 'ERR_CANCELED') {
                console.error('Failed to fetch admin messages:', error);
            }
        } finally {
            adminMessagesRequest = null;
        }
    };

    const fetchAdminConversations = async ({ silent = false } = {}) => {
        if (adminConversationsRequest) {
            return;
        }

        adminConversationsRequest = new AbortController();

        try {
            const response = await axios.get('/api/conversations', {
                signal: adminConversationsRequest.signal,
            });
            const summaries = Array.isArray(response.data) ? response.data : [];
            summaries.forEach(upsertAdminConversation);
            sortAdminConversations();

            if (!activeAdminMessageId && adminMessages.length) {
                activeAdminMessageId = adminMessages[0].id;
            }

            if (!silent) {
                renderAdminMessages();
                updateNavCounts();
            }
        } catch (error) {
            if (error.name !== 'CanceledError' && error.code !== 'ERR_CANCELED') {
                console.error('Failed to fetch admin conversations:', error);
            }
        } finally {
            adminConversationsRequest = null;
        }
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
                <div class="rounded-3xl border border-dashed border-slate-200 bg-slate-50/50 px-5 py-10 text-center">
                    <p class="text-sm font-medium text-slate-400">No conversations found</p>
                </div>
            `;
            return;
        }

        adminMessageList.innerHTML = filtered.map((conversation) => `
            <button
                type="button"
                data-admin-message-thread="${conversation.id}"
                class="group relative flex w-full items-start gap-3 rounded-2xl p-3 text-left transition-all hover:bg-slate-50 active:scale-[0.98] ${conversation.id === activeAdminMessageId ? 'bg-slate-50 ring-1 ring-slate-100' : ''}"
                data-active="${conversation.id === activeAdminMessageId ? 'true' : 'false'}"
            >
                <div class="relative flex h-12 w-12 shrink-0 items-center justify-center rounded-full bg-slate-100 text-xs font-bold text-slate-500 shadow-sm transition-transform group-hover:scale-105">
                    ${escapeHtml(conversation.avatar)}
                    ${conversation.unread ? '<span class="absolute -top-0.5 -right-0.5 h-3.5 w-3.5 rounded-full border-2 border-white bg-rose-500"></span>' : ''}
                </div>
                <div class="min-w-0 flex-1">
                    <div class="flex items-center justify-between gap-2">
                        <p class="truncate text-sm font-bold text-slate-900">${escapeHtml(conversation.name)}</p>
                        <span class="shrink-0 text-[10px] font-medium text-slate-400">${escapeHtml(conversation.time)}</span>
                    </div>
                    <p class="mt-1 truncate text-xs font-medium text-slate-600">${escapeHtml(conversation.preview)}</p>
                </div>
                ${conversation.id === activeAdminMessageId ? '<div class="absolute left-0 top-3 bottom-3 w-1 rounded-r-full bg-[var(--brand)]"></div>' : ''}
            </button>
        `).join('');
    };

    const renderAdminMessageFeed = (options = {}) => {
        const conversation = getActiveAdminMessage();

        if (!adminMessageName || !adminMessageSubtitle || !adminMessageAvatar || !adminMessageLabel || !adminMessageFeed) {
            return;
        }

        if (!conversation) {
            adminMessageAvatar.textContent = '--';
            adminMessageName.textContent = 'Select a conversation';
            adminMessageSubtitle.textContent = 'Pick a thread from the list to start chatting.';
            adminMessageLabel.textContent = 'Inbound';
            adminMessageFeed.innerHTML = `
                <div class="flex h-full items-center justify-center text-center">
                    <div class="max-w-xs">
                        <div class="mx-auto mb-4 flex h-16 w-16 items-center justify-center rounded-full bg-slate-100 text-slate-300">
                             <svg class="h-8 w-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z" />
                            </svg>
                        </div>
                        <h3 class="font-display text-xl font-bold text-slate-900">No thread selected</h3>
                        <p class="mt-2 text-sm text-slate-500">Select a customer conversation on the left to view the full message history.</p>
                    </div>
                </div>
            `;
            return;
        }

        // Check if user is near bottom before re-rendering
        const threshold = 100; // pixels
        const isNearBottom = adminMessageFeed.scrollHeight - adminMessageFeed.scrollTop - adminMessageFeed.clientHeight < threshold;

        adminMessageAvatar.textContent = conversation.avatar;
        adminMessageName.textContent = conversation.name;
        adminMessageSubtitle.textContent = conversation.subtitle;
        adminMessageLabel.textContent = conversation.label;
        
        adminMessageFeed.innerHTML = `
            <div class="flex flex-col gap-4">
                ${conversation.messages.map((message) => `
                    <div class="flex ${message.sender === 'me' ? 'justify-end' : 'justify-start'}">
                        <div class="flex flex-col ${message.sender === 'me' ? 'items-end' : 'items-start'} max-w-[85%] md:max-w-[70%]">
                            <div class="rounded-2xl px-4 py-3 shadow-sm ${message.sender === 'me'
                                ? 'bg-[var(--brand)] text-white rounded-br-none'
                                : 'bg-white text-slate-700 border border-slate-100 rounded-bl-none'}">
                                <p class="text-sm leading-relaxed">${escapeHtml(message.text)}</p>
                            </div>
                            <span class="mt-1.5 px-1 text-[10px] font-bold uppercase tracking-wider text-slate-400">${escapeHtml(message.time)}</span>
                        </div>
                    </div>
                `).join('')}
            </div>
        `;

        // Scroll to bottom if requested, or if it's a new conversation, or if we were already at the bottom
        if (options.forceScroll || options.isNewConversation || isNearBottom) {
            adminMessageFeed.scrollTop = adminMessageFeed.scrollHeight;
        }
    };

    const renderAdminMessages = (options = {}) => {
        renderAdminMessageSummary();
        renderAdminMessageList();
        renderAdminMessageFeed(options);
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

    const matchesPromosFilter = (item) => {
        const query = filters.promos.query.trim().toLowerCase();
        const active = filters.promos.value;
        const haystack = [
            item.dataset.promoCode,
            item.dataset.promoDescription,
        ].join(' ').toLowerCase();

        if (active !== 'all') {
            const isActive = item.dataset.promoIsActive === '1';
            if (active === 'active' && !isActive) return false;
            if (active === 'inactive' && isActive) return false;
        }

        return !query || haystack.includes(query);
    };

    const getFilteredItems = (key) => {
        const items = getPageItems(key);

        if (key === 'inventory') {
            return items.filter(matchesInventoryFilter);
        }

        if (key === 'promos') {
            return items.filter(matchesPromosFilter);
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

        if (key === 'promos') {
            const emptyState = dashboard.querySelector('[data-promos-empty]');
            if (emptyState) emptyState.hidden = total > 0;
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

    adminMessageDraft?.addEventListener('input', () => {
        adminMessageDraft.style.height = 'auto';
        adminMessageDraft.style.height = `${adminMessageDraft.scrollHeight}px`;
    });

    adminMessageDraft?.addEventListener('keydown', (event) => {
        if (event.key === 'Enter' && !event.shiftKey) {
            event.preventDefault();
            adminMessageSend?.click();
        }
    });

    let isAdminSendingMessage = false;

    adminMessageSend?.addEventListener('click', async () => {
        const draft = adminMessageDraft?.value.trim();
        const conversation = getActiveAdminMessage();

        if (!draft || !conversation || isAdminSendingMessage) {
            return;
        }

        const currentConvId = activeAdminMessageId;
        const tempId = Date.now();
        isAdminSendingMessage = true;
        adminMessageSend.disabled = true;

        conversation.messages.push({
            id: tempId,
            sender: 'me',
            text: draft,
            time: 'Sending...',
        });
        
        const messageContent = draft;
        adminMessageDraft.value = '';
        adminMessageDraft.style.height = 'auto';
        renderAdminMessageFeed({ forceScroll: true });

        try {
            const response = await axios.post('/api/messages', {
                conversation_id: currentConvId,
                content: messageContent,
            });
            const savedMessage = normalizeAdminMessage(response.data);
            const msgIndex = conversation.messages.findIndex(m => m.id === tempId);
            if (msgIndex !== -1) {
                conversation.messages[msgIndex] = savedMessage;
            } else {
                mergeAdminMessages(conversation, [response.data]);
            }

            conversation.latestMessageId = Math.max(Number(conversation.latestMessageId || 0), Number(savedMessage.id || 0));
            conversation.preview = messageContent;
            conversation.time = 'Just now';
            sortAdminConversations();
            renderAdminMessages();
        } catch (error) {
            console.error('Failed to send message:', error);
            conversation.messages = conversation.messages.filter(m => m.id !== tempId);
            adminMessageDraft.value = messageContent;
            renderAdminMessageFeed();
        } finally {
            isAdminSendingMessage = false;
            adminMessageSend.disabled = false;
        }
    });

    adminMessageMarkRead?.addEventListener('click', async () => {
        const conversation = getActiveAdminMessage();

        if (!conversation) {
            return;
        }

        try {
            await axios.post(`/api/conversations/${conversation.id}/read`);
            conversation.unread = false;
            renderAdminMessages();
            updateNavCounts();
        } catch (error) {
            console.error('Failed to mark as read:', error);
        }
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
            const threadId = Number(messageThread.dataset.adminMessageThread);
            const isNewThread = threadId !== activeAdminMessageId;
            activeAdminMessageId = threadId;
            
            const conversation = getActiveAdminMessage();
            if (conversation) {
                if (!conversation.loaded) {
                    fetchAdminMessages(conversation.id, { silent: false });
                }
                if (conversation.unread) {
                    axios.post(`/api/conversations/${conversation.id}/read`).catch(console.error);
                    conversation.unread = false;
                }
            }
            renderAdminMessages({ isNewConversation: isNewThread });
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

        const closeInventory = event.target.closest('[data-inventory-close]');
        if (closeInventory) {
            closeInventoryDrawer();
            return;
        }

        const editProduct = event.target.closest('[data-edit-product]');
        if (editProduct) {
            const row = editProduct.closest('[data-product-row]');
            if (row) {
                openInventoryDrawer('edit', {
                    id: row.dataset.productId,
                    name: row.dataset.productName,
                    description: row.dataset.productDescription,
                    price: row.dataset.productPrice,
                    category: row.dataset.productCategory,
                    is_active: row.dataset.productIsActive === '1',
                });
            }
            return;
        }

        const addPromo = event.target.closest('[data-open-add-promo]');
        if (addPromo) {
            openPromoDrawer('add');
            return;
        }

        const closePromo = event.target.closest('[data-promo-close]');
        if (closePromo) {
            closePromoDrawer();
            return;
        }

        const editPromo = event.target.closest('[data-edit-promo]');
        if (editPromo) {
            const row = editPromo.closest('[data-promo-row]');
            if (row) {
                let applicable = [];
                try {
                    applicable = JSON.parse(row.dataset.promoApplicableProducts || '[]');
                } catch (e) {
                    applicable = [];
                }

                const promo = {
                    id: row.dataset.promoId,
                    code: row.dataset.promoCode,
                    description: row.dataset.promoDescription,
                    discount_type: row.dataset.promoDiscountType,
                    discount_value: row.dataset.promoDiscountValue,
                    min_purchase: row.dataset.promoMinPurchase,
                    max_discount: row.dataset.promoMaxDiscount,
                    usage_limit: row.dataset.promoUsageLimit,
                    limit_per_user: row.dataset.promoLimitPerUser,
                    starts_at: row.dataset.promoStartsAt,
                    expires_at: row.dataset.promoExpiresAt,
                    applicable_products: applicable,
                    is_active: row.dataset.promoIsActive === '1',
                };

                openPromoDrawer('edit', promo);
            }
            return;
        }

        const removePromo = event.target.closest('[data-remove-promo]');
        if (removePromo) {
            const row = removePromo.closest('[data-promo-row]');
            if (row) {
                const promoId = row.dataset.promoId;
                const promoCode = row.dataset.promoCode;

                openModal({
                    title: 'Remove Promo Code',
                    message: `Are you sure you want to permanently delete the promo code "${promoCode}"? This action cannot be undone.`,
                    confirmLabel: 'Delete Promo',
                    promoId: promoId,
                });
            }
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
            customerPanel.closest('[data-section="customers"]').classList.remove('panel-open');
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
            customerPanel.closest('[data-section="customers"]').classList.add('panel-open');
            customerPanelTitle.textContent = row.dataset.personName;
            customerPanelMeta.textContent = `${row.dataset.personRole} | ${row.dataset.personEmail}`;
            customerPanel.querySelector('[data-customer-panel-body]').innerHTML = row.querySelector('[data-person-details]').innerHTML;
            return;
        }

        const togglePerson = event.target.closest('[data-toggle-person]');
        if (togglePerson) {
            const row = togglePerson.closest('[data-person-card]');
            const personId = togglePerson.dataset.personId || (row ? row.dataset.personId : null);
            const personStatus = togglePerson.dataset.personStatus || (row ? row.dataset.personStatus : null);
            
            if (personId) {
                openModal({
                    title: personStatus === 'active' ? 'Suspend Account' : 'Unsuspend Account',
                    message: personStatus === 'active'
                        ? 'Are you sure you want to suspend this account?'
                        : 'Are you sure you want to unsuspend this account?',
                    confirmLabel: personStatus === 'active' ? 'Suspend' : 'Unsuspend',
                    personId: personId,
                });
            }
            return;
        }

        const removeNotification = event.target.closest('[data-remove-notification]');
        if (removeNotification) {
            const item = removeNotification.closest('[data-notification-item]');
            if (item) {
                const notificationId = item.dataset.notificationId;
                
                // Optmistic UI removal
                item.dataset.removed = '1';
                item.hidden = true;
                renderPagination('notifications');

                // Persist to database
                if (notificationId && window.axios) {
                    window.axios.delete(`/api/notifications/${notificationId}`)
                        .catch(err => console.error('Error removing notification:', err));
                }
            }
            return;
        }

        const clearNotifications = event.target.closest('[data-clear-notifications]');
        if (clearNotifications) {
            dashboard.querySelectorAll('[data-notification-item]').forEach((item) => {
                item.dataset.removed = '1';
                item.hidden = true;
            });
            paginations.notifications.page = 1;
            renderPagination('notifications');

            // Persist to database
            if (window.axios) {
                window.axios.delete('/api/notifications')
                    .catch(err => console.error('Error clearing notifications:', err));
            }
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

        if (modalAction.promoId) {
            fetch(`/admin/promos/${modalAction.promoId}`, {
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
            if (window.axios) {
                window.axios.patch(`/admin/users/${modalAction.personId}/status`)
                    .then(response => {
                        if (response.data.success) {
                            window.location.reload();
                        }
                    })
                    .catch(err => {
                        console.error('Error toggling user status:', err);
                        if (err.response && err.response.data && err.response.data.message) {
                            alert(err.response.data.message);
                        }
                    });
            }
            closeModal();
            return;
        }

        closeModal();
    });

    modalShell?.addEventListener('click', (event) => {
        if (event.target === modalShell || event.target.closest('[data-modal-cancel]')) {
            closeModal();
        }
    });

    sidebarCompactButton?.addEventListener('click', () => {
        dashboard.classList.toggle('is-sidebar-compact');
    });

    // Removed direct listener to close inventory drawer - handled in delegation
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

    const pollAdminMessages = () => {
        if (document.hidden) {
            return;
        }

        fetchAdminConversations({ silent: true });

        if (activeAdminMessageId) {
            fetchAdminMessages(activeAdminMessageId, { incremental: true, silent: true });
        }
    };

    const startAdminMessagePolling = () => {
        if (adminMessagePollTimer || adminConversationPollTimer) {
            return;
        }

        adminMessagePollTimer = window.setInterval(() => {
            if (activeAdminMessageId && !document.hidden) {
                fetchAdminMessages(activeAdminMessageId, { incremental: true, silent: true });
            }
        }, ADMIN_ACTIVE_MESSAGE_POLL_MS);

        adminConversationPollTimer = window.setInterval(() => {
            if (!document.hidden) {
                fetchAdminConversations({ silent: true });
            }
        }, ADMIN_CONVERSATION_POLL_MS);
    };

    const stopAdminMessagePolling = () => {
        if (adminMessagePollTimer) {
            window.clearInterval(adminMessagePollTimer);
            adminMessagePollTimer = null;
        }

        if (adminConversationPollTimer) {
            window.clearInterval(adminConversationPollTimer);
            adminConversationPollTimer = null;
        }

        adminConversationsRequest?.abort();
        adminMessagesRequest?.abort();
    };

    const handleAdminMessageVisibility = () => {
        if (!document.hidden) {
            pollAdminMessages();
        }
    };

    document.addEventListener('visibilitychange', handleAdminMessageVisibility);
    window.addEventListener('beforeunload', stopAdminMessagePolling);

    syncWalkinRows();
    if (walkinItemsContainer && !walkinItemsContainer.children.length) {
        addWalkinItemRow();
    }

    renderLineChart();
    renderTypeBars();
    renderAdminMessages();
    startAdminMessagePolling();

    // Echo Real-time listeners
    if (window.Echo) {
        window.Echo.private('admin.messages')
            .listen('.message.sent', (event) => {
                const message = event.message;
                let conversation = adminMessages.find(c => c.id === message.conversation_id);
                
                if (!conversation) {
                    fetchAdminConversations({ silent: false });
                    return;
                }

                conversation.preview = message.content;
                conversation.time = 'Just now';
                conversation.latestMessageId = Math.max(Number(conversation.latestMessageId || 0), Number(message.id || 0));

                if (conversation.id === activeAdminMessageId) {
                    mergeAdminMessages(conversation, [message]);
                    if (Number(message.sender_id) !== adminUserId) {
                        axios.post(`/api/conversations/${conversation.id}/read`).catch(console.error);
                        conversation.unread = false;
                    }
                } else if (Number(message.sender_id) !== adminUserId) {
                    conversation.unread = true;
                }

                sortAdminConversations();
                renderAdminMessages();
                updateNavCounts();
            });
    }

    // ==========================================
    // PROMOS & DISCOUNTS DRAWER & HANDLERS
    // ==========================================
    const promoDrawer = dashboard.querySelector('[data-promo-drawer]');
    const promoForm = promoDrawer?.querySelector('[data-promo-form]');
    const promoTitle = promoDrawer?.querySelector('[data-promo-title]');
    const promoSubtitle = promoDrawer?.querySelector('[data-promo-subtitle]');
    const promoIdInput = promoForm?.querySelector('[data-promo-id]');
    const promoMethodInput = promoForm?.querySelector('[data-promo-method]');
    const promoCodeInput = promoForm?.querySelector('[data-promo-code-input]');
    const promoDescriptionInput = promoForm?.querySelector('[data-promo-description]');
    const promoDiscountTypeInput = promoForm?.querySelector('[data-promo-discount-type]');
    const promoDiscountValueInput = promoForm?.querySelector('[data-promo-discount-value]');
    const promoMinPurchaseInput = promoForm?.querySelector('[data-promo-min-purchase]');
    const promoMaxDiscountInput = promoForm?.querySelector('[data-promo-max-discount]');
    const promoUsageLimitInput = promoForm?.querySelector('[data-promo-usage-limit]');
    const promoLimitPerUserInput = promoForm?.querySelector('[data-promo-limit-per-user]');
    const promoStartsAtInput = promoForm?.querySelector('[data-promo-starts-at]');
    const promoExpiresAtInput = promoForm?.querySelector('[data-promo-expires-at]');
    const promoIsActiveInput = promoForm?.querySelector('[data-promo-is-active]');
    const promoProductCheckboxes = Array.from(promoForm?.querySelectorAll('[data-promo-product-checkbox]') || []);

    const openPromoDrawer = (mode, promo = null) => {
        if (!promoDrawer || !promoForm) return;

        promoForm.dataset.mode = mode;
        if (promoTitle) {
            promoTitle.textContent = mode === 'edit' ? 'Edit Promo' : 'Add Promo';
        }
        if (promoSubtitle) {
            promoSubtitle.textContent = mode === 'edit' ? 'Update the details and restrictions for this discount code.' : 'Create a new discount code with limit rules.';
        }

        if (mode === 'edit' && promo) {
            if (promoIdInput) promoIdInput.value = promo.id;
            if (promoCodeInput) promoCodeInput.value = promo.code;
            if (promoDescriptionInput) promoDescriptionInput.value = promo.description || '';
            if (promoDiscountTypeInput) promoDiscountTypeInput.value = promo.discount_type;
            if (promoDiscountValueInput) promoDiscountValueInput.value = promo.discount_value;
            if (promoMinPurchaseInput) promoMinPurchaseInput.value = promo.min_purchase || '';
            if (promoMaxDiscountInput) promoMaxDiscountInput.value = promo.max_discount || '';
            if (promoUsageLimitInput) promoUsageLimitInput.value = promo.usage_limit || '';
            if (promoLimitPerUserInput) promoLimitPerUserInput.value = promo.limit_per_user || '';
            
            if (promoStartsAtInput) {
                promoStartsAtInput.value = promo.starts_at ? promo.starts_at.replace(' ', 'T') : '';
            }
            if (promoExpiresAtInput) {
                promoExpiresAtInput.value = promo.expires_at ? promo.expires_at.replace(' ', 'T') : '';
            }
            
            if (promoIsActiveInput) promoIsActiveInput.value = promo.is_active ? '1' : '0';

            const applicableIds = Array.isArray(promo.applicable_products) 
                ? promo.applicable_products.map(id => Number(id))
                : [];
            
            promoProductCheckboxes.forEach((checkbox) => {
                checkbox.checked = applicableIds.includes(Number(checkbox.value));
            });
        } else {
            promoForm.reset();
            if (promoIdInput) promoIdInput.value = '';
            promoProductCheckboxes.forEach((checkbox) => {
                checkbox.checked = false;
            });
        }

        if (promoDrawer) {
            promoDrawer.hidden = false;
        }
    };

    const closePromoDrawer = () => {
        if (promoDrawer) {
            promoDrawer.hidden = true;
        }
    };

    promoForm?.addEventListener('submit', (event) => {
        event.preventDefault();

        const mode = promoForm.dataset.mode || 'add';
        const promoId = promoIdInput ? promoIdInput.value : '';
        const baseUrl = promoForm.dataset.updateUrlBase;

        if (mode === 'edit' && promoId) {
            promoForm.action = `${baseUrl}/${promoId}`;
            if (promoMethodInput) {
                promoMethodInput.disabled = false;
                promoMethodInput.value = 'PUT';
            }
        } else {
            promoForm.action = promoForm.dataset.storeUrl || promoForm.action;
            if (promoMethodInput) {
                promoMethodInput.disabled = true;
                promoMethodInput.value = '';
            }
        }

        promoForm.submit();
    });

    // Removed direct listener for removing promo - handled in delegation

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
