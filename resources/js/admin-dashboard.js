import './bootstrap';
import ExcelJS from 'exceljs';

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
    const inventoryImageInput = inventoryForm?.querySelector('[data-inventory-image]');
    const inventoryImagePreviewContainer = inventoryForm?.querySelector('[data-inventory-image-preview-container]');
    const inventoryImagePreview = inventoryForm?.querySelector('[data-inventory-image-preview]');
    const removeInventoryImageBtn = inventoryForm?.querySelector('[data-remove-inventory-image]');
    const customerPanel = dashboard.querySelector('[data-customer-panel]');
    const customerPanelTitle = dashboard.querySelector('[data-customer-panel-title]');
    const customerPanelMeta = dashboard.querySelector('[data-customer-panel-meta]');
    const notificationCount = dashboard.querySelector('[data-notification-count]');
    const inventoryEmptyState = dashboard.querySelector('[data-inventory-empty]');
    const lineChart = dashboard.querySelector('[data-line-chart]');
    const linePath = dashboard.querySelector('[data-line-path]');
    const linePoints = dashboard.querySelector('[data-line-points]');
    const lineLabels = dashboard.querySelector('[data-line-labels]');
    const reportRange = dashboard.querySelector('[data-report-range]');
    const reportKicker = dashboard.querySelector('[data-report-kicker]');
    const reportTitle = dashboard.querySelector('[data-report-title]');
    const reportBadge = dashboard.querySelector('[data-report-badge]');
    const typeBars = dashboard.querySelector('[data-type-bars]');
    const exportTarget = dashboard.querySelector('[data-export-target]');
    const exportFeedback = dashboard.querySelector('[data-export-feedback]');
    const loadingOverlay = dashboard.querySelector('[data-dashboard-loading]');
    const loadingText = dashboard.querySelector('[data-dashboard-loading-text]');
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
    let loadingCount = 0;
    const adminUserId = Number(reportData?.user?.user_id || reportData?.user?.id || 0);
    const ADMIN_ACTIVE_MESSAGE_POLL_MS = 1000;
    const ADMIN_CONVERSATION_POLL_MS = 1500;

    const sectionLabels = navButtons.reduce((acc, button) => {
        const key = button.dataset.nav;
        const textNode = button.querySelector('[data-sidebar-text]');
        acc[key] = textNode ? textNode.textContent.trim() : key;
        return acc;
    }, {});

    const flattenCsvRow = (row, prefix = '', output = {}) => {
        Object.entries(row || {}).forEach(([key, value]) => {
            const path = prefix ? `${prefix}.${key}` : key;

            if (Array.isArray(value)) {
                if (value.every((item) => item === null || ['string', 'number', 'boolean'].includes(typeof item))) {
                    output[path] = value.join(' | ');
                    return;
                }

                output[path] = JSON.stringify(value);
                return;
            }

            if (value && typeof value === 'object') {
                flattenCsvRow(value, path, output);
                return;
            }

            output[path] = value;
        });

        return output;
    };

    const csvHeaderLabels = {
        generated_at: 'Generated At',
        metric: 'Metric',
        value: 'Value',
        label: 'Period',
        name: 'Name',
        product_id: 'Product ID',
        quantity_sold: 'Quantity Sold',
        buyers_count: 'Number of Buyers',
        revenue: 'Revenue',
        id: 'ID',
        product_name: 'Product Name',
        formatted_price: 'Formatted Price',
        image_url: 'Image URL',
        is_active: 'Active',
        total_amount: 'Total Amount',
        formatted_total: 'Formatted Total',
        payment_status: 'Payment Status',
        payment_method: 'Payment Method',
        payment_provider: 'Payment Provider',
        customer_name: 'Customer Name',
        created_at: 'Created At',
        updated_at: 'Updated At',
        status: 'Status',
        role: 'Role',
        username: 'Username',
        email: 'Email',
        phone: 'Phone',
        contact: 'Contact',
        customer: 'Customer',
        amount: 'Amount',
        placed_at: 'Placed At',
        payment_method_label: 'Payment Method',
        payment_status_label: 'Payment Status',
        status_label: 'Order Status',
        items: 'Items',
        category_label: 'Category',
        message: 'Message',
        title: 'Title',
        date: 'Date',
    };

    const csvColumnPriority = [
        'metric',
        'label',
        'name',
        'product_name',
        'product_id',
        'quantity_sold',
        'buyers_count',
        'revenue',
        'value',
        'status',
        'payment_status',
        'total_amount',
        'formatted_total',
        'customer_name',
        'email',
        'phone',
        'username',
        'role',
        'created_at',
        'updated_at',
        'generated_at',
        'id',
        'image_url',
        'is_active',
    ];

    const humanizeCsvHeader = (header) => {
        if (csvHeaderLabels[header]) {
            return csvHeaderLabels[header];
        }

        return header
            .split('.')
            .map((part) => part
                .replace(/_/g, ' ')
                .replace(/\b\w/g, (letter) => letter.toUpperCase()))
            .join(' - ');
    };

    const sortCsvHeaders = (headers) => [...headers].sort((left, right) => {
        const leftPriority = csvColumnPriority.indexOf(left);
        const rightPriority = csvColumnPriority.indexOf(right);

        if (leftPriority !== -1 || rightPriority !== -1) {
            if (leftPriority === -1) return 1;
            if (rightPriority === -1) return -1;
            if (leftPriority !== rightPriority) return leftPriority - rightPriority;
        }

        return left.localeCompare(right);
    });

    const csvDatasetColumns = {
        weeklyCompletions: ['label', 'value'],
        monthlyCompletions: ['label', 'value'],
        yearlyCompletions: ['label', 'value'],
        usersWeekly: ['label', 'value'],
        usersMonthly: ['label', 'value'],
        usersYearly: ['label', 'value'],
        ordersWeekly: ['label', 'value'],
        ordersMonthly: ['label', 'value'],
        ordersYearly: ['label', 'value'],
        revenueWeekly: ['label', 'value'],
        revenueMonthly: ['label', 'value'],
        revenueYearly: ['label', 'value'],
        productSales: ['product_id', 'name', 'quantity_sold', 'buyers_count', 'revenue'],
        products: ['product_id', 'product_name', 'category', 'price', 'formatted_price', 'status'],
        orders: ['id', 'customer', 'items', 'amount', 'payment_method_label', 'payment_status_label', 'status_label', 'placed_at'],
        customers: ['id', 'name', 'username', 'email', 'contact', 'address', 'status'],
        admins: ['id', 'name', 'username', 'email', 'contact', 'address', 'status'],
        notifications: ['id', 'customer_name', 'title', 'message', 'category_label', 'status', 'payment_status', 'date'],
    };

    const pickCsvColumns = (row, columns) => {
        if (!columns?.length) {
            return row;
        }

        return columns.reduce((output, column) => {
            output[column] = row?.[column] ?? '';
            return output;
        }, {});
    };

    const getCsvRows = (target, rows) => {
        if (!Array.isArray(rows)) {
            return [];
        }

        const columns = csvDatasetColumns[target];
        return rows.map((row) => pickCsvColumns(row, columns));
    };

    const toCsv = (rows, options = {}) => {
        if (!Array.isArray(rows) || !rows.length) {
            return '';
        }

        const flatRows = rows.map((row) => flattenCsvRow(row));
        const rawHeaders = Array.from(new Set(flatRows.flatMap((row) => Object.keys(row))));
        const headers = sortCsvHeaders(rawHeaders);
        const lines = [];

        if (options.title) {
            lines.push(`"${String(options.title).replace(/"/g, '""')}"`);
        }

        if (options.meta && Array.isArray(options.meta)) {
            options.meta.forEach(({ label, value }) => {
                lines.push(`"${String(label).replace(/"/g, '""')}","${String(value ?? '').replace(/"/g, '""')}"`);
            });
        }

        if (options.includeSeparator !== false) {
            lines.push('sep=,');
        }

        if (lines.length) {
            lines.push('');
        }

        lines.push(headers.map((header) => `"${humanizeCsvHeader(header)}"`).join(','));

        flatRows.forEach((row) => {
            const values = headers.map((header) => {
                const value = row[header] ?? '';
                const escaped = String(value).replace(/"/g, '""').replace(/\r?\n/g, ' ');
                return `"${escaped}"`;
            });
            lines.push(values.join(','));
        });

        return lines.join('\n');
    };

    const buildCsvSection = (title, rows, meta = []) => {
        const csv = toCsv(rows, { title, meta });
        if (!csv) {
            return '';
        }

        return csv;
    };

    const getLatestSeriesValue = (series) => {
        if (!Array.isArray(series) || !series.length) {
            return 0;
        }

        return Number(series[series.length - 1]?.value ?? 0);
    };

    const getSeriesWindowTotal = (series) => (
        Array.isArray(series)
            ? series.reduce((total, item) => total + Number(item?.value ?? 0), 0)
            : 0
    );

    const buildSummaryRows = () => {
        if (!reportData) {
            return [];
        }

        const summary = reportData.summary || {};

        return [
            { metric: 'Generated At', value: summary.generated_at || new Date().toISOString() },
            { metric: 'Total Products', value: Number(summary.total_products || reportData.products?.length || 0) },
            { metric: 'Total Orders', value: Number(summary.total_orders || reportData.orders?.length || 0) },
            { metric: 'Total Customers', value: Number(summary.total_customers || reportData.customers?.length || 0) },
            { metric: 'Total Items Sold', value: Number(summary.total_items_sold || 0) },
            { metric: 'Total Revenue', value: Number(summary.total_revenue || 0).toFixed(2) },
            { metric: 'Users This Week', value: getSeriesWindowTotal(reportData.usersWeekly) },
            { metric: 'Users This Month', value: getLatestSeriesValue(reportData.usersMonthly) },
            { metric: 'Users This Year', value: getLatestSeriesValue(reportData.usersYearly) },
            { metric: 'Orders This Week', value: getSeriesWindowTotal(reportData.ordersWeekly) },
            { metric: 'Orders This Month', value: getLatestSeriesValue(reportData.ordersMonthly) },
            { metric: 'Orders This Year', value: getLatestSeriesValue(reportData.ordersYearly) },
            { metric: 'Revenue This Week', value: getSeriesWindowTotal(reportData.revenueWeekly).toFixed(2) },
            { metric: 'Revenue This Month', value: Number(getLatestSeriesValue(reportData.revenueMonthly)).toFixed(2) },
            { metric: 'Revenue This Year', value: Number(getLatestSeriesValue(reportData.revenueYearly)).toFixed(2) },
        ];
    };

    const buildSummarySections = () => [
        { title: 'Summary Overview', rows: buildSummaryRows() },
        { title: 'Product Sales', rows: getCsvRows('productSales', reportData?.productSales || []) },
        { title: 'Completed Orders Weekly', rows: getCsvRows('weeklyCompletions', reportData?.weeklyCompletions || []) },
        { title: 'Completed Orders Monthly', rows: getCsvRows('monthlyCompletions', reportData?.monthlyCompletions || []) },
        { title: 'Completed Orders Yearly', rows: getCsvRows('yearlyCompletions', reportData?.yearlyCompletions || []) },
        { title: 'Users Weekly', rows: getCsvRows('usersWeekly', reportData?.usersWeekly || []) },
        { title: 'Users Monthly', rows: getCsvRows('usersMonthly', reportData?.usersMonthly || []) },
        { title: 'Users Yearly', rows: getCsvRows('usersYearly', reportData?.usersYearly || []) },
        { title: 'Orders Weekly', rows: getCsvRows('ordersWeekly', reportData?.ordersWeekly || []) },
        { title: 'Orders Monthly', rows: getCsvRows('ordersMonthly', reportData?.ordersMonthly || []) },
        { title: 'Orders Yearly', rows: getCsvRows('ordersYearly', reportData?.ordersYearly || []) },
        { title: 'Revenue Weekly', rows: getCsvRows('revenueWeekly', reportData?.revenueWeekly || []) },
        { title: 'Revenue Monthly', rows: getCsvRows('revenueMonthly', reportData?.revenueMonthly || []) },
        { title: 'Revenue Yearly', rows: getCsvRows('revenueYearly', reportData?.revenueYearly || []) },
    ].filter((section) => Array.isArray(section.rows) && section.rows.length);

    const buildSummaryCsv = () => buildSummarySections()
        .map((section) => buildCsvSection(section.title, section.rows, [
            { label: 'Report', value: 'BakerDan Summary Export' },
            { label: 'Section', value: section.title },
            { label: 'Generated At', value: reportData?.summary?.generated_at || new Date().toISOString() },
        ]))
        .filter(Boolean)
        .join('\n\n');

    const getExportPayload = (target) => {
        if (!reportData) {
            return [];
        }

        if (target === 'summary') {
            return {
                overview: buildSummaryRows(),
                product_sales: reportData.productSales || [],
                completed_orders: {
                    weekly: reportData.weeklyCompletions || [],
                    monthly: reportData.monthlyCompletions || [],
                    yearly: reportData.yearlyCompletions || [],
                },
                users: {
                    weekly: reportData.usersWeekly || [],
                    monthly: reportData.usersMonthly || [],
                    yearly: reportData.usersYearly || [],
                },
                orders: {
                    weekly: reportData.ordersWeekly || [],
                    monthly: reportData.ordersMonthly || [],
                    yearly: reportData.ordersYearly || [],
                },
                revenue: {
                    weekly: reportData.revenueWeekly || [],
                    monthly: reportData.revenueMonthly || [],
                    yearly: reportData.revenueYearly || [],
                },
            };
        }

        return reportData[target] || [];
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
        const payload = getExportPayload(target);

        if (target === 'summary') {
            return buildSummaryRows();
        }

        return Array.isArray(payload) ? getCsvRows(target, payload) : [];
    };

    const getExportCsv = (target, rows) => {
        if (target === 'summary') {
            return buildSummaryCsv();
        }

        const datasetLabel = exportTarget?.selectedOptions?.[0]?.textContent?.trim() || target;
        return toCsv(rows, {
            title: datasetLabel,
            meta: [
                { label: 'Report', value: 'BakerDan Export' },
                { label: 'Dataset', value: datasetLabel },
                { label: 'Generated At', value: reportData?.summary?.generated_at || new Date().toISOString() },
                { label: 'Row Count', value: rows.length },
            ],
        });
    };

    const spreadsheetColumnWidths = {
        metric: 20,
        value: 16,
        label: 14,
        name: 24,
        product_name: 26,
        product_id: 12,
        quantity_sold: 14,
        buyers_count: 14,
        revenue: 16,
        category: 16,
        status: 14,
        price: 14,
        formatted_price: 16,
        amount: 16,
        customer: 24,
        customer_name: 24,
        items: 42,
        payment_method_label: 18,
        payment_status_label: 18,
        status_label: 18,
        placed_at: 20,
        username: 16,
        email: 30,
        contact: 18,
        address: 34,
        title: 24,
        message: 44,
        category_label: 16,
        date: 20,
    };

    const sanitizeWorksheetName = (value, fallback = 'Sheet') => {
        const cleaned = String(value || fallback)
            .replace(/[\\/*?:[\]]/g, '')
            .trim()
            .slice(0, 31);

        return cleaned || fallback;
    };

    const isSpreadsheetCurrencyField = (key) => currencyFields.has(String(key));

    const isSpreadsheetDateField = (key) => /(generated at|placed at|date)/i.test(String(key));

    const getSpreadsheetCellValue = (key, value) => {
        if (value === null || value === undefined || value === '') {
            return '';
        }

        if (typeof value === 'number') {
            return value;
        }

        if (typeof value === 'string' && !Number.isNaN(Number(value)) && value.trim() !== '' && !/^0\d+/.test(value.trim())) {
            return Number(value);
        }

        return formatPdfValue(key, value);
    };

    const buildSpreadsheetSheet = (workbook, title, sections, meta = []) => {
        const safeTitle = sanitizeWorksheetName(title);
        const sheet = workbook.addWorksheet(safeTitle, {
            views: [{ state: 'frozen', ySplit: 4 }],
            properties: { defaultRowHeight: 20 },
        });

        const normalizedSections = Array.isArray(sections) ? sections : [];
        const allHeaders = Array.from(new Set(normalizedSections.flatMap((section) => {
            const normalizedRows = Array.isArray(section?.rows) ? section.rows : [];
            return normalizedRows.flatMap((row) => Object.keys(row || {}));
        })));
        const sheetHeaders = allHeaders.length ? sortCsvHeaders(allHeaders) : ['message'];

        sheet.columns = sheetHeaders.map((header) => ({
            key: header,
            width: spreadsheetColumnWidths[header] || 18,
        }));

        let currentRow = 1;
        const totalColumns = Math.max(sheetHeaders.length, 1);

        const writeMergedTitle = (rowNumber, text, endColumn, options = {}) => {
            if (endColumn > 1) {
                sheet.mergeCells(rowNumber, 1, rowNumber, endColumn);
            }

            const cell = sheet.getCell(rowNumber, 1);
            cell.value = text;
            cell.font = options.font || { name: 'Calibri', size: 18, bold: true, color: { argb: 'FFFFFFFF' } };
            cell.fill = options.fill || { type: 'pattern', pattern: 'solid', fgColor: { argb: 'FFB76539' } };
            cell.alignment = options.alignment || { vertical: 'middle', horizontal: 'left' };
            sheet.getRow(rowNumber).height = options.height || 28;
        };

        writeMergedTitle(currentRow, title, totalColumns);
        currentRow += 1;

        meta.forEach(({ label, value }, index) => {
            const rowNumber = currentRow + index;
            sheet.getCell(rowNumber, 1).value = label;
            sheet.getCell(rowNumber, 2).value = String(value ?? '');
            sheet.getCell(rowNumber, 1).font = { name: 'Calibri', size: 10, bold: true, color: { argb: 'FF7F3E1F' } };
            sheet.getCell(rowNumber, 2).font = { name: 'Calibri', size: 10, color: { argb: 'FF5E4B41' } };
            sheet.getCell(rowNumber, 1).fill = { type: 'pattern', pattern: 'solid', fgColor: { argb: 'FFF9EFE5' } };
            sheet.getCell(rowNumber, 2).fill = { type: 'pattern', pattern: 'solid', fgColor: { argb: 'FFFFF9F3' } };
        });

        currentRow += meta.length + 1;

        normalizedSections.forEach((section, sectionIndex) => {
            const normalizedRows = Array.isArray(section?.rows) ? section.rows : [];
            const headers = normalizedRows.length
                ? sortCsvHeaders(Array.from(new Set(normalizedRows.flatMap((row) => Object.keys(row || {})))))
                : ['message'];
            const sectionStartRow = currentRow;

            writeMergedTitle(sectionStartRow, section.title, headers.length, {
                font: { name: 'Calibri', size: 14, bold: true, color: { argb: 'FF7F3E1F' } },
                fill: { type: 'pattern', pattern: 'solid', fgColor: { argb: 'FFF9EFE5' } },
                alignment: { vertical: 'middle', horizontal: 'left' },
                height: 24,
            });

            const headerRowNumber = sectionStartRow + 1;
            const headerRow = sheet.getRow(headerRowNumber);
            headers.forEach((header, index) => {
                const cell = headerRow.getCell(index + 1);
                cell.value = humanizeCsvHeader(header);
                cell.font = { name: 'Calibri', size: 11, bold: true, color: { argb: 'FF7F3E1F' } };
                cell.fill = { type: 'pattern', pattern: 'solid', fgColor: { argb: 'FFF6E8DA' } };
                cell.alignment = { vertical: 'middle', horizontal: 'center', wrapText: true };
            });
            headerRow.height = 24;

            if (!normalizedRows.length) {
                const row = sheet.getRow(headerRowNumber + 1);
                row.getCell(1).value = 'No data available for this section.';
                if (headers.length > 1) {
                    sheet.mergeCells(headerRowNumber + 1, 1, headerRowNumber + 1, headers.length);
                }
            } else {
                normalizedRows.forEach((row, rowIndex) => {
                    const rowNumber = headerRowNumber + 1 + rowIndex;
                    headers.forEach((header, index) => {
                        const effectiveKey = header === 'value' && row?.metric ? row.metric : header;
                        const cell = sheet.getCell(rowNumber, index + 1);
                        cell.value = getSpreadsheetCellValue(effectiveKey, row?.[header]);

                        if (isSpreadsheetCurrencyField(effectiveKey)) {
                            cell.numFmt = '"PHP" #,##0.00';
                        }

                        cell.alignment = {
                            vertical: 'top',
                            horizontal: typeof cell.value === 'number' ? 'right' : 'left',
                            wrapText: true,
                        };
                    });
                });
            }

            const sectionEndRow = normalizedRows.length ? headerRowNumber + normalizedRows.length : headerRowNumber + 1;
            for (let rowNumber = sectionStartRow; rowNumber <= sectionEndRow; rowNumber += 1) {
                const row = sheet.getRow(rowNumber);
                row.eachCell((cell) => {
                    cell.border = {
                        top: { style: 'thin', color: { argb: 'FFE8DCCF' } },
                        left: { style: 'thin', color: { argb: 'FFE8DCCF' } },
                        bottom: { style: 'thin', color: { argb: 'FFE8DCCF' } },
                        right: { style: 'thin', color: { argb: 'FFE8DCCF' } },
                    };

                    if (rowNumber > sectionStartRow + 1) {
                        cell.font = { name: 'Calibri', size: 11, color: { argb: 'FF2F241D' } };
                        cell.fill = {
                            type: 'pattern',
                            pattern: 'solid',
                            fgColor: { argb: rowNumber % 2 === 0 ? 'FFFFFFFF' : 'FFFFFBF7' },
                        };
                    }
                });
            }

            currentRow = sectionEndRow + 2;

            if (sectionIndex < normalizedSections.length - 1) {
                sheet.getRow(currentRow - 1).height = 8;
            }
        });

        return sheet;
    };

    const getExcelSections = (target, rows) => {
        if (target === 'summary') {
            return buildSummarySections();
        }

        return [{
            title: getDatasetLabel(target),
            rows,
        }];
    };

    const buildExcelWorkbook = async (target, rows) => {
        const datasetLabel = getDatasetLabel(target);
        const generatedAt = formatPdfValue('Generated At', reportData?.summary?.generated_at || new Date().toISOString());
        const sections = getExcelSections(target, rows);
        const workbook = new ExcelJS.Workbook();
        workbook.creator = 'OpenAI Codex';
        workbook.company = 'BakerDan Bakery';
        workbook.created = new Date();
        workbook.modified = new Date();

        buildSpreadsheetSheet(workbook, datasetLabel, sections, [
            { label: 'Report', value: 'BakerDan Bakery Report' },
            { label: 'Dataset', value: datasetLabel },
            { label: 'Generated At', value: generatedAt },
            { label: 'Export Type', value: 'Formatted Excel Workbook' },
        ]);

        const buffer = await workbook.xlsx.writeBuffer();
        return buffer;
    };

    const currencyFields = new Set([
        'revenue',
        'total_revenue',
        'amount',
        'price',
        'discount_amount',
        'Total Revenue',
        'Revenue This Week',
        'Revenue This Month',
        'Revenue This Year',
    ]);

    const formatPdfValue = (key, value) => {
        if (value === null || value === undefined || value === '') {
            return '—';
        }

        const isCurrency = currencyFields.has(String(key));

        if (typeof value === 'number') {
            if (isCurrency) {
                return `PHP ${value.toLocaleString(undefined, { minimumFractionDigits: 2, maximumFractionDigits: 2 })}`;
            }

            return value.toLocaleString();
        }

        if (typeof value === 'string' && isCurrency && !Number.isNaN(Number(value))) {
            return `PHP ${Number(value).toLocaleString(undefined, { minimumFractionDigits: 2, maximumFractionDigits: 2 })}`;
        }

        if (typeof value === 'string' && /(generated at|placed at|date)/i.test(String(key))) {
            const parsed = new Date(value);
            if (!Number.isNaN(parsed.getTime())) {
                return parsed.toLocaleString();
            }
        }

        return String(value);
    };

    const getDatasetLabel = (target) => exportTarget?.selectedOptions?.[0]?.textContent?.trim() || target;

    const setLoadingState = (isLoading, text = 'Updating dashboard...') => {
        if (!loadingOverlay) {
            return;
        }

        if (loadingText) {
            loadingText.textContent = text;
        }

        loadingOverlay.hidden = !isLoading;
        dashboard.classList.toggle('dashboard-is-loading', isLoading);
        dashboard.setAttribute('aria-busy', isLoading ? 'true' : 'false');
    };

    const beginLoading = (text) => {
        loadingCount += 1;
        setLoadingState(true, text);
    };

    const endLoading = () => {
        loadingCount = Math.max(0, loadingCount - 1);
        if (loadingCount === 0) {
            setLoadingState(false);
        }
    };

    const getInventoryRowSelector = (productId) => `[data-product-row][data-product-id="${productId}"]`;
    const getPromoRowSelector = (promoId) => `[data-promo-row][data-promo-id="${promoId}"]`;
    const getOrderRowSelector = (orderId) => `[data-order-card][data-order-id="${orderId}"]`;
    const getPersonRowSelector = (personId) => `[data-person-card][data-person-id="${personId}"]`;

    const replaceOrInsertRow = (selector, rowHtml, containerSelector) => {
        const row = dashboard.querySelector(selector);
        if (row) {
            row.outerHTML = rowHtml;
            return;
        }

        const container = dashboard.querySelector(containerSelector);
        if (container) {
            container.insertAdjacentHTML('afterbegin', rowHtml);
        }
    };

    const renderInventoryRow = (product) => {
        const imageHtml = product.image_url
            ? `<img src="${escapeHtml(product.image_url)}" alt="${escapeHtml(product.name)}" class="h-14 w-14 rounded-2xl object-cover ring-1 ring-slate-200">`
            : '<div class="grid h-14 w-14 place-items-center rounded-2xl bg-slate-100 text-xs font-semibold text-slate-500">IMG</div>';

        return `
            <tr data-product-row data-page-item="inventory" data-product-id="${escapeHtml(product.id)}"
                data-product-name="${escapeHtml(product.name)}"
                data-product-description="${escapeHtml(product.description || '')}"
                data-product-price="${escapeHtml(product.price)}"
                data-product-category="${escapeHtml(product.category)}"
                data-product-image-url="${escapeHtml(product.image_url || '')}"
                data-product-is-active="${product.is_active ? '1' : '0'}"
                class="align-top">
                <td class="px-5 py-4 font-semibold text-slate-900">${escapeHtml(product.name)}</td>
                <td class="px-5 py-4 text-slate-600">${escapeHtml(product.description || '')}</td>
                <td class="px-5 py-4 font-semibold text-slate-900">${escapeHtml(product.formatted_price || product.price)}</td>
                <td class="px-5 py-4"><span class="rounded-full bg-slate-100 px-3 py-1 text-xs font-semibold uppercase tracking-[0.18em] text-slate-600">${escapeHtml(product.category)}</span></td>
                <td class="px-5 py-4">${imageHtml}</td>
                <td class="px-5 py-4">
                    <div class="flex flex-wrap gap-2">
                        <button type="button" data-edit-product class="rounded-full bg-slate-900 px-3 py-2 text-xs font-semibold text-white">Edit</button>
                        <button type="button" data-remove-product class="rounded-full bg-rose-50 px-3 py-2 text-xs font-semibold text-rose-700">Remove</button>
                    </div>
                </td>
            </tr>
        `;
    };

    const renderPromoRow = (promo) => {
        const statusTone = promo.is_active
            ? 'bg-emerald-50 text-emerald-700 border border-emerald-100'
            : 'bg-rose-50 text-rose-700 border border-rose-100';
        const applicableProducts = JSON.stringify(promo.applicable_products || []);
        const productCount = Array.isArray(promo.applicable_products) ? promo.applicable_products.length : 0;
        const productLabel = productCount > 0
            ? `<span class="rounded bg-slate-100 px-1.5 py-0.5 font-medium text-slate-700 text-[10px]">${productCount} specific</span>`
            : '<span class="rounded bg-emerald-50 px-1.5 py-0.5 font-medium text-emerald-700 text-[10px]">All Catalog</span>';

        return `
            <tr data-promo-row data-page-item="promos" data-promo-id="${escapeHtml(promo.id)}"
                data-promo-code="${escapeHtml(promo.code)}"
                data-promo-description="${escapeHtml(promo.description || '')}"
                data-promo-discount-type="${escapeHtml(promo.discount_type)}"
                data-promo-discount-value="${escapeHtml(promo.discount_value)}"
                data-promo-min-purchase="${escapeHtml(promo.min_purchase || '')}"
                data-promo-max-discount="${escapeHtml(promo.max_discount || '')}"
                data-promo-usage-limit="${escapeHtml(promo.usage_limit || '')}"
                data-promo-limit-per-user="${escapeHtml(promo.limit_per_user || '')}"
                data-promo-starts-at="${escapeHtml(promo.starts_at || '')}"
                data-promo-expires-at="${escapeHtml(promo.expires_at || '')}"
                data-promo-applicable-products="${escapeHtml(applicableProducts)}"
                data-promo-is-active="${promo.is_active ? '1' : '0'}" class="align-top">
                <td class="px-5 py-4 font-semibold text-slate-900">
                    <div class="font-bold text-slate-900 uppercase tracking-wide bg-orange-50 text-orange-850 rounded-lg px-2.5 py-1 inline-block border border-orange-100 mb-1">${escapeHtml(promo.code)}</div>
                    <div class="text-xs text-slate-500 max-w-[200px] line-clamp-2 mt-1">${escapeHtml(promo.description || 'No description.')}</div>
                </td>
                <td class="px-5 py-4">
                    <span class="font-semibold text-slate-900">${escapeHtml(promo.formatted_discount)}</span>
                    <div class="text-xs text-slate-500 capitalize">${escapeHtml(promo.discount_type)} discount</div>
                </td>
                <td class="px-5 py-4 space-y-1">
                    <div class="text-xs"><span class="text-slate-500">Min Purchase:</span> <span class="font-semibold">${escapeHtml(promo.formatted_min_purchase)}</span></div>
                    <div class="text-xs"><span class="text-slate-500">Max Discount:</span> <span class="font-semibold">${escapeHtml(promo.formatted_max_discount)}</span></div>
                    <div class="text-xs"><span class="text-slate-500">Products:</span> ${productLabel}</div>
                </td>
                <td class="px-5 py-4 space-y-1">
                    <div class="text-xs"><span class="text-slate-500">Starts:</span> <span class="font-semibold text-slate-700">${escapeHtml(promo.formatted_starts_at)}</span></div>
                    <div class="text-xs"><span class="text-slate-500">Expires:</span> <span class="font-semibold text-slate-700">${escapeHtml(promo.formatted_expires_at)}</span></div>
                </td>
                <td class="px-5 py-4">
                    <div class="font-semibold text-slate-900">${escapeHtml(promo.usage_count)} used</div>
                    ${promo.usage_limit ? `<div class="text-[11px] text-slate-400">Limit: ${escapeHtml(promo.usage_limit)} total</div>` : ''}
                </td>
                <td class="px-5 py-4">
                    <span class="rounded-full px-2.5 py-1 text-xs font-bold uppercase tracking-wider ${statusTone}">${promo.is_active ? 'Active' : 'Inactive'}</span>
                </td>
                <td class="px-5 py-4">
                    <div class="flex flex-wrap gap-2">
                        <button type="button" data-edit-promo class="rounded-full bg-slate-900 px-3 py-2 text-xs font-semibold text-white">Edit</button>
                        <button type="button" data-remove-promo class="rounded-full bg-rose-50 px-3 py-2 text-xs font-semibold text-rose-700">Remove</button>
                    </div>
                </td>
            </tr>
        `;
    };

    const renderOrderRow = (order) => {
        const itemLines = (order.item_lines || []).map((line) => `
            <div class="rounded-2xl bg-slate-50 p-4">
                <p class="font-semibold text-slate-900">${escapeHtml(line.summary)}</p>
                ${line.detail ? `<p class="mt-1 text-xs text-slate-500">${escapeHtml(line.detail)}</p>` : ''}
            </div>
        `).join('');

        const customItems = order.contains_custom && Array.isArray(order.custom_items)
            ? `
                <div class="rounded-2xl border border-[#F0DCCC] bg-[#FFF8F1] p-4">
                    <p class="text-xs font-semibold uppercase tracking-[0.18em] text-[#C9876C]">Customization Brief</p>
                    ${order.custom_items.map((customItem) => `
                        <div class="mt-3 flex gap-4">
                            <img src="${escapeHtml(customItem.image_url)}" alt="${escapeHtml(customItem.name)}" class="h-16 w-16 rounded-[1rem] object-cover ring-1 ring-[#EACFBC]">
                            <div class="min-w-0 flex-1">
                                <p class="font-semibold text-slate-900">${escapeHtml(customItem.name)}</p>
                                <p class="mt-1 text-xs text-slate-500">Qty ${escapeHtml(customItem.quantity)}${customItem.size ? ` | Size ${escapeHtml(customItem.size)}` : ''}${customItem.flavor ? ` | Flavor ${escapeHtml(customItem.flavor)}` : ''}</p>
                                ${customItem.design_description ? `<p class="mt-2 text-sm leading-6 text-slate-700">${escapeHtml(customItem.design_description)}</p>` : ''}
                                ${customItem.dedication_message ? `<p class="mt-2 text-xs font-medium text-[#8E5632]">Dedication: ${escapeHtml(customItem.dedication_message)}</p>` : ''}
                            </div>
                        </div>
                    `).join('')}
                    <p class="mt-3 text-xs text-[#8E5632]">${escapeHtml(order.workflow_note || '')}</p>
                </div>
            `
            : '';

        return `
            <tr data-order-card data-page-item="orders" data-order-id="${escapeHtml(order.id)}"
                data-order-status="${escapeHtml(order.status)}" data-payment-status="${escapeHtml(order.payment_status)}"
                data-next-status="${escapeHtml(order.next_status || '')}"
                data-order-customer="${escapeHtml(order.customer)}"
                data-order-custom="${order.contains_custom ? '1' : '0'}"
                class="align-top text-slate-700 hover:bg-slate-50/70">
                <td class="px-4 py-5">
                    <div class="min-w-0">
                        <div class="flex flex-wrap items-center gap-2">
                            <h3 class="font-display text-xl font-bold text-slate-900">Order #${escapeHtml(order.id)}</h3>
                            ${order.contains_custom ? '<span class="rounded-full bg-[#FFF4EB] px-3 py-1 text-[11px] font-semibold uppercase tracking-[0.18em] text-[#C9876C]">Custom</span>' : ''}
                        </div>
                        <p class="mt-2 text-sm text-slate-600">${escapeHtml(order.placed_at || 'No timestamp available')}</p>
                        <p class="mt-1 text-xs text-slate-500">Payment method: ${escapeHtml(order.payment_method_label || 'N/A')}</p>
                        ${order.payment_reference ? `<p class="mt-1 text-xs text-slate-500">Ref: ${escapeHtml(order.payment_reference)}</p>` : ''}
                        ${order.customer_details?.source ? `<p class="mt-2 inline-flex rounded-full bg-slate-100 px-3 py-1 text-[11px] font-semibold uppercase tracking-[0.18em] text-slate-600">${escapeHtml(order.customer_details.source)}</p>` : ''}
                    </div>
                </td>
                <td class="px-4 py-5">
                    <div class="grid gap-1.5 text-xs leading-5 text-slate-600">
                        <p><span class="font-semibold text-slate-900">Name:</span> ${escapeHtml(order.customer_details?.name || order.customer)}</p>
                        <p><span class="font-semibold text-slate-900">Linked account:</span> ${escapeHtml(order.customer_details?.linked_account || 'N/A')}</p>
                        <p><span class="font-semibold text-slate-900">Username:</span> ${escapeHtml(order.customer_details?.username || 'N/A')}</p>
                        <p><span class="font-semibold text-slate-900">Age:</span> ${escapeHtml(order.customer_details?.age || 'N/A')}</p>
                        <p><span class="font-semibold text-slate-900">Email:</span> ${escapeHtml(order.customer_details?.email || 'N/A')}</p>
                        <p><span class="font-semibold text-slate-900">Contact:</span> ${escapeHtml(order.customer_details?.contact || 'N/A')}</p>
                        <p><span class="font-semibold text-slate-900">Address:</span> ${escapeHtml(order.customer_details?.address || 'N/A')}</p>
                    </div>
                </td>
                <td class="px-4 py-5">
                    <div class="space-y-3 text-sm text-slate-600">
                        ${itemLines}
                        ${customItems}
                    </div>
                </td>
                <td class="px-4 py-5">
                    <div data-order-status-label class="inline-flex rounded-full bg-amber-50 px-3 py-1 text-xs font-semibold uppercase tracking-[0.18em] text-amber-700">${escapeHtml(order.status_label)}</div>
                </td>
                <td class="px-4 py-5">
                    <div class="space-y-2">
                        <span data-payment-status class="inline-flex rounded-full bg-slate-100 px-3 py-1 text-xs font-semibold text-slate-600">${escapeHtml(order.payment_status_label)}</span>
                        <p class="text-xs text-slate-500">${escapeHtml(order.payment_method_label || 'Payment pending')}</p>
                    </div>
                </td>
                <td class="px-4 py-5">
                    <div class="space-y-1">
                        <p class="font-semibold text-slate-900">${escapeHtml(order.amount)}</p>
                        ${order.discount_amount_label ? `<p class="text-xs text-emerald-700">${escapeHtml(order.discount_amount_label)}</p>` : ''}
                        ${order.promo_code ? `<p class="text-xs text-slate-500">Promo: ${escapeHtml(order.promo_code)}</p>` : ''}
                    </div>
                </td>
                <td class="px-4 py-5">
                    <div class="flex flex-wrap gap-2">
                        <button data-order-action="advance" data-next-status="${escapeHtml(order.next_status || '')}" ${order.next_status ? '' : 'hidden'} type="button" class="rounded-full bg-slate-900 px-3 py-2 text-xs font-semibold text-white">${escapeHtml(order.next_status_label || 'Advance Workflow')}</button>
                        <button data-order-action="mark-paid" ${order.payment_status === 'paid' ? 'hidden' : ''} type="button" class="rounded-full bg-emerald-50 px-3 py-2 text-xs font-semibold text-emerald-700">Mark as Paid</button>
                    </div>
                </td>
            </tr>
        `;
    };

    const getPersonToggleClasses = (status, variant = 'list') => {
        if (variant === 'panel') {
            return status === 'active'
                ? 'w-full text-center rounded-full bg-amber-50 text-amber-700 hover:bg-amber-100 px-4 py-2.5 text-xs font-semibold active:scale-95 transition-all'
                : 'w-full text-center rounded-full bg-emerald-50 text-emerald-700 hover:bg-emerald-100 px-4 py-2.5 text-xs font-semibold active:scale-95 transition-all';
        }

        return status === 'active'
            ? 'rounded-full bg-amber-50 text-amber-700 px-3 py-2 text-xs font-semibold'
            : 'rounded-full bg-emerald-50 text-emerald-700 px-3 py-2 text-xs font-semibold';
    };

    const applyPersonStatus = (personId, status) => {
        const label = status === 'active' ? 'Suspend' : 'Unsuspend';
        const panelLabel = status === 'active' ? 'Suspend Account' : 'Unsuspend Account';
        const statusText = status === 'active' ? 'Active' : 'Inactive';

        dashboard.querySelectorAll(`[data-person-card][data-person-id="${personId}"]`).forEach((row) => {
            row.dataset.personStatus = status;
            const statusNode = row.querySelector('[data-person-status]');
            if (statusNode) {
                statusNode.textContent = statusText;
            }

            row.querySelectorAll('[data-toggle-person]').forEach((button) => {
                button.textContent = label;
                button.dataset.personStatus = status;
                button.className = getPersonToggleClasses(status, 'list');
            });
        });

        customerPanel?.querySelectorAll(`[data-toggle-person][data-person-id="${personId}"]`).forEach((button) => {
            button.textContent = panelLabel;
            button.dataset.personStatus = status;
            button.className = getPersonToggleClasses(status, 'panel');
        });

        customerPanel?.querySelectorAll('[data-person-status]').forEach((statusNode) => {
            statusNode.textContent = statusText;
        });
    };

    const applyMutationResult = (payload) => {
        const data = payload?.data || {};

        if (data.product) {
            if (data.product.is_active) {
                replaceOrInsertRow(getInventoryRowSelector(data.product.id), renderInventoryRow(data.product), '[data-section="inventory"] tbody');
            } else {
                dashboard.querySelector(getInventoryRowSelector(data.product.id))?.remove();
            }
            renderPagination('inventory');
            return;
        }

        if (data.product_id) {
            dashboard.querySelector(getInventoryRowSelector(data.product_id))?.remove();
            renderPagination('inventory');
            return;
        }

        if (data.promo) {
            replaceOrInsertRow(getPromoRowSelector(data.promo.id), renderPromoRow(data.promo), '[data-section="promos"] tbody');
            renderPagination('promos');
            return;
        }

        if (data.promo_id) {
            dashboard.querySelector(getPromoRowSelector(data.promo_id))?.remove();
            renderPagination('promos');
            return;
        }

        if (data.order) {
            replaceOrInsertRow(getOrderRowSelector(data.order.id), renderOrderRow(data.order), '[data-section="orders"] tbody');
            renderPagination('orders');
            return;
        }

        const toggledUserId = data.user?.user_id ?? data.user_id;
        const toggledUserStatus = data.user?.status ?? data.status;

        if (toggledUserId && toggledUserStatus) {
            applyPersonStatus(toggledUserId, toggledUserStatus);
            const activePeopleKey = getActivePeopleKey();
            renderPagination(activePeopleKey);
            updateNavCounts();
        }
    };

    const buildPdfTable = (rows) => {
        if (!Array.isArray(rows) || !rows.length) {
            return '<p class="pdf-empty">No data available for this section.</p>';
        }

        const flatRows = rows.map((row) => flattenCsvRow(row));
        const headers = sortCsvHeaders(Array.from(new Set(flatRows.flatMap((row) => Object.keys(row)))));

        const headerHtml = headers.map((header) => `<th>${escapeHtml(humanizeCsvHeader(header))}</th>`).join('');
        const bodyHtml = flatRows.map((row) => {
            const cells = headers.map((header) => {
                const effectiveKey = header === 'value' && row?.metric ? row.metric : header;
                return `<td>${escapeHtml(formatPdfValue(effectiveKey, row[header]))}</td>`;
            }).join('');
            return `<tr>${cells}</tr>`;
        }).join('');

        return `
            <div class="pdf-table-wrap">
                <table class="pdf-table">
                    <thead>
                        <tr>${headerHtml}</tr>
                    </thead>
                    <tbody>${bodyHtml}</tbody>
                </table>
            </div>
        `;
    };

    const buildPdfSummaryCards = () => {
        const rows = buildSummaryRows().slice(1, 6);

        return rows.map((row, index) => {
            const tones = ['warm', 'gold', 'sage', 'clay'];
            const tone = tones[index % tones.length];

            return `
                <div class="pdf-card ${tone}">
                    <p class="pdf-card-label">${escapeHtml(row.metric)}</p>
                    <p class="pdf-card-value">${escapeHtml(formatPdfValue(row.metric, row.value))}</p>
                </div>
            `;
        }).join('');
    };

    const getPdfSections = (target, payload, rows) => {
        if (target === 'summary') {
            return buildSummarySections().map((section) => ({
                title: section.title,
                rows: section.rows,
            }));
        }

        return [{
            title: getDatasetLabel(target),
            rows: Array.isArray(rows) ? rows : [],
        }];
    };

    const buildPdfHtml = (target, payload, rows) => {
        const datasetLabel = getDatasetLabel(target);
        const generatedAt = formatPdfValue('Generated At', reportData?.summary?.generated_at || new Date().toISOString());
        const sections = getPdfSections(target, payload, rows);
        const sectionHtml = sections.map((section) => `
            <section class="pdf-section">
                <div class="pdf-section-header">
                    <p class="pdf-section-kicker">Report Section</p>
                    <h2>${escapeHtml(section.title)}</h2>
                </div>
                ${buildPdfTable(section.rows)}
            </section>
        `).join('');

        return `
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>${escapeHtml(`BakerDan Report - ${datasetLabel}`)}</title>
    <style>
        :root {
            color-scheme: light;
            --ink: #2f241d;
            --muted: #75655c;
            --line: #e7d8ca;
            --paper: #fffaf5;
            --card: #ffffff;
            --accent: #b76539;
            --accent-deep: #7f3e1f;
            --warm: #fff2e7;
            --gold: #fff7da;
            --sage: #edf6ec;
            --clay: #f8ece8;
        }
        * { box-sizing: border-box; }
        body {
            margin: 0;
            font-family: Arial, Helvetica, sans-serif;
            color: var(--ink);
            background: linear-gradient(180deg, #f8ead9 0%, #fffaf5 22%, #ffffff 100%);
        }
        .pdf-page {
            width: 100%;
            max-width: 1120px;
            margin: 0 auto;
            padding: 28px;
        }
        .pdf-hero {
            background: linear-gradient(135deg, var(--accent-deep), var(--accent));
            color: #fff;
            border-radius: 24px;
            padding: 28px;
            box-shadow: 0 20px 40px rgba(127, 62, 31, 0.16);
        }
        .pdf-hero-kicker {
            margin: 0 0 8px;
            font-size: 12px;
            letter-spacing: 0.18em;
            text-transform: uppercase;
            opacity: 0.82;
        }
        .pdf-hero h1 {
            margin: 0;
            font-size: 32px;
            line-height: 1.1;
        }
        .pdf-meta {
            display: grid;
            grid-template-columns: repeat(3, minmax(0, 1fr));
            gap: 12px;
            margin-top: 18px;
        }
        .pdf-meta-item {
            background: rgba(255, 255, 255, 0.14);
            border: 1px solid rgba(255, 255, 255, 0.18);
            border-radius: 16px;
            padding: 12px 14px;
        }
        .pdf-meta-label {
            margin: 0;
            font-size: 11px;
            text-transform: uppercase;
            letter-spacing: 0.12em;
            opacity: 0.74;
        }
        .pdf-meta-value {
            margin: 6px 0 0;
            font-size: 15px;
            font-weight: 700;
        }
        .pdf-card-grid {
            display: grid;
            grid-template-columns: repeat(3, minmax(0, 1fr));
            gap: 14px;
            margin-top: 18px;
            break-inside: avoid;
            page-break-inside: avoid;
        }
        .pdf-card {
            border-radius: 18px;
            padding: 14px 16px;
            background: var(--card);
            border: 1px solid var(--line);
            break-inside: avoid;
            page-break-inside: avoid;
        }
        .pdf-card.warm { background: var(--warm); }
        .pdf-card.gold { background: var(--gold); }
        .pdf-card.sage { background: var(--sage); }
        .pdf-card.clay { background: var(--clay); }
        .pdf-card-label {
            margin: 0;
            font-size: 12px;
            text-transform: uppercase;
            letter-spacing: 0.12em;
            color: var(--muted);
        }
        .pdf-card-value {
            margin: 8px 0 0;
            font-size: 19px;
            font-weight: 700;
            line-height: 1.2;
        }
        .pdf-section {
            margin-top: 18px;
            background: var(--card);
            border: 1px solid var(--line);
            border-radius: 24px;
            padding: 18px;
            box-shadow: 0 12px 28px rgba(88, 59, 37, 0.06);
            break-inside: avoid;
        }
        .pdf-section-header {
            margin-bottom: 14px;
        }
        .pdf-section-kicker {
            margin: 0 0 4px;
            font-size: 11px;
            text-transform: uppercase;
            letter-spacing: 0.14em;
            color: var(--accent);
            font-weight: 700;
        }
        .pdf-section-header h2 {
            margin: 0;
            font-size: 22px;
        }
        .pdf-table-wrap {
            overflow: hidden;
            border-radius: 18px;
            border: 1px solid var(--line);
        }
        .pdf-table {
            width: 100%;
            border-collapse: collapse;
            table-layout: auto;
            font-size: 12px;
        }
        .pdf-table thead th {
            background: #f7ecdf;
            color: var(--accent-deep);
            text-align: left;
            padding: 12px 14px;
            border-bottom: 1px solid var(--line);
        }
        .pdf-table tbody td {
            padding: 11px 14px;
            border-bottom: 1px solid #f1e4d7;
            vertical-align: top;
            word-break: break-word;
        }
        .pdf-table tbody tr:nth-child(even) td {
            background: #fffaf6;
        }
        .pdf-empty {
            margin: 0;
            padding: 14px 0;
            color: var(--muted);
        }
        @page {
            size: A4 landscape;
            margin: 12mm;
        }
        @media print {
            body {
                background: #fff;
            }
            .pdf-page {
                max-width: none;
                padding: 0;
            }
            .pdf-meta {
                grid-template-columns: repeat(3, minmax(0, 1fr));
            }
            .pdf-card-grid {
                grid-template-columns: repeat(3, minmax(0, 1fr));
            }
            .pdf-section,
            .pdf-hero {
                box-shadow: none;
            }
        }
    </style>
</head>
<body>
    <main class="pdf-page">
        <section class="pdf-hero">
            <p class="pdf-hero-kicker">BakerDan Bakery Report</p>
            <h1>${escapeHtml(datasetLabel)}</h1>
            <div class="pdf-meta">
                <div class="pdf-meta-item">
                    <p class="pdf-meta-label">Generated At</p>
                    <p class="pdf-meta-value">${escapeHtml(generatedAt)}</p>
                </div>
                <div class="pdf-meta-item">
                    <p class="pdf-meta-label">Dataset</p>
                    <p class="pdf-meta-value">${escapeHtml(datasetLabel)}</p>
                </div>
                <div class="pdf-meta-item">
                    <p class="pdf-meta-label">Rows / Sections</p>
                    <p class="pdf-meta-value">${escapeHtml(target === 'summary' ? String(sections.length) : String(rows.length || 0))}</p>
                </div>
            </div>
        </section>
        ${target === 'summary' ? `<section class="pdf-card-grid">${buildPdfSummaryCards()}</section>` : ''}
        ${sectionHtml}
    </main>
</body>
</html>
        `;
    };

    const exportPdfReport = (target, payload, rows) => {
        const existingFrame = document.getElementById('report-print-frame');
        if (existingFrame) {
            existingFrame.remove();
        }

        const frame = document.createElement('iframe');
        frame.id = 'report-print-frame';
        frame.style.position = 'fixed';
        frame.style.right = '0';
        frame.style.bottom = '0';
        frame.style.width = '0';
        frame.style.height = '0';
        frame.style.border = '0';
        frame.setAttribute('aria-hidden', 'true');

        document.body.appendChild(frame);

        const frameWindow = frame.contentWindow;
        const frameDocument = frame.contentDocument || frameWindow?.document;

        if (!frameWindow || !frameDocument) {
            frame.remove();
            if (exportFeedback) {
                exportFeedback.textContent = 'PDF export is not available in this browser.';
            }
            return false;
        }

        frameDocument.open();
        frameDocument.write(buildPdfHtml(target, payload, rows));
        frameDocument.close();

        const cleanup = () => {
            window.setTimeout(() => {
                frame.remove();
            }, 1000);
        };

        frame.onload = () => {
            window.setTimeout(() => {
                try {
                    frameWindow.focus();
                    frameWindow.print();
                } finally {
                    cleanup();
                }
            }, 250);
        };

        return true;
    };

    const reportSeriesMeta = {
        weekly: {
            kicker: 'Weekly report graph',
            title: 'Completed orders trend',
            badge: '7 days',
            data: reportData?.weeklyCompletions || [],
        },
        monthly: {
            kicker: 'Monthly report graph',
            title: 'Completed orders trend',
            badge: '12 months',
            data: reportData?.monthlyCompletions || [],
        },
        yearly: {
            kicker: 'Yearly report graph',
            title: 'Completed orders trend',
            badge: '5 years',
            data: reportData?.yearlyCompletions || [],
        },
    };

    const renderLineChart = (range = reportRange?.value || 'weekly') => {
        if (!lineChart || !linePath || !linePoints || !lineLabels) {
            return;
        }

        const meta = reportSeriesMeta[range] || reportSeriesMeta.weekly;
        const data = meta.data;

        if (!data.length) {
            return;
        }

        const width = 680;
        const height = 240;
        const paddingX = 54;
        const chartBottom = 190;
        const chartTop = 45;
        const maxValue = Math.max(...data.map((item) => item.value), 1);
        const stepX = (width - paddingX * 2) / Math.max(data.length - 1, 1);

        if (reportKicker) {
            reportKicker.textContent = meta.kicker;
        }

        if (reportTitle) {
            reportTitle.textContent = meta.title;
        }

        if (reportBadge) {
            reportBadge.textContent = meta.badge;
        }

        lineLabels.style.gridTemplateColumns = `repeat(${data.length}, minmax(0, 1fr))`;

        const points = data.map((item, index) => {
            const x = paddingX + index * stepX;
            const y = chartBottom - ((item.value / maxValue) * (chartBottom - chartTop));
            return { x, y, label: item.label, value: item.value };
        });

        linePath.setAttribute('d', points.map((point, index) => `${index === 0 ? 'M' : 'L'} ${point.x.toFixed(2)} ${point.y.toFixed(2)}`).join(' '));
        linePoints.innerHTML = points.map((point) => `<circle class="chart-point" cx="${point.x.toFixed(2)}" cy="${point.y.toFixed(2)}" r="4"></circle>`).join('');
        lineLabels.innerHTML = points.map((point) => `<span>${point.label}</span>`).join('');
    };

    reportRange?.addEventListener('change', () => {
        renderLineChart(reportRange.value);
    });

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
                class="group relative flex w-full items-start gap-3 overflow-hidden rounded-[1.35rem] border p-3 text-left transition-all active:scale-[0.98] ${conversation.id === activeAdminMessageId ? 'border-[rgba(201,135,108,0.22)] bg-white shadow-[0_18px_36px_-30px_rgba(71,45,29,0.38)] ring-1 ring-[rgba(201,135,108,0.12)]' : 'border-transparent bg-white/60 hover:border-slate-200 hover:bg-white'}"
                data-active="${conversation.id === activeAdminMessageId ? 'true' : 'false'}"
            >
                ${conversation.id === activeAdminMessageId ? '<div class="absolute left-0 top-3 bottom-3 w-1 rounded-r-full bg-[var(--brand)]"></div>' : ''}
                <div class="relative flex h-12 w-12 shrink-0 items-center justify-center rounded-full bg-[rgba(201,135,108,0.12)] text-xs font-bold text-[var(--brand-deep)] shadow-sm transition-transform group-hover:scale-105">
                    ${escapeHtml(conversation.avatar)}
                    ${conversation.unread ? '<span class="absolute -top-0.5 -right-0.5 h-3.5 w-3.5 rounded-full border-2 border-white bg-rose-500"></span>' : ''}
                </div>
                <div class="min-w-0 flex-1">
                    <div class="flex items-center justify-between gap-2">
                        <p class="truncate text-sm font-bold text-slate-900">${escapeHtml(conversation.name)}</p>
                        <span class="shrink-0 text-[10px] font-semibold uppercase tracking-[0.14em] text-slate-400">${escapeHtml(conversation.time)}</span>
                    </div>
                    <p class="mt-1 text-[10px] font-bold uppercase tracking-[0.18em] text-[var(--brand-deep)]">${escapeHtml(conversation.label || 'Direct Message')}</p>
                    <p class="mt-1 truncate text-xs font-medium text-slate-600">${escapeHtml(conversation.preview)}</p>
                </div>
                <div class="flex shrink-0 flex-col items-end gap-2">
                    ${conversation.unread
                        ? '<span class="rounded-full bg-rose-500 px-2 py-1 text-[10px] font-bold uppercase tracking-[0.12em] text-white">New</span>'
                        : '<span class="text-[10px] font-semibold uppercase tracking-[0.12em] text-slate-400">Read</span>'}
                </div>
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
                        <div class="mx-auto mb-4 flex h-16 w-16 items-center justify-center rounded-full bg-[rgba(201,135,108,0.12)] text-[var(--brand)]">
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
                <div class="self-center rounded-full border border-white/80 bg-white/85 px-4 py-1.5 text-[11px] font-semibold uppercase tracking-[0.18em] text-slate-400 shadow-sm">
                    Active conversation
                </div>
                ${conversation.messages.map((message) => `
                    <div class="flex ${message.sender === 'me' ? 'justify-end' : 'justify-start'}">
                        <div class="flex flex-col ${message.sender === 'me' ? 'items-end' : 'items-start'} max-w-[85%] md:max-w-[70%]">
                            <div class="rounded-[1.35rem] px-4 py-3 shadow-[0_18px_36px_-32px_rgba(57,36,22,0.48)] ${message.sender === 'me'
                                ? 'bg-[var(--brand)] text-white rounded-br-[0.45rem]'
                                : 'bg-white text-slate-700 border border-slate-100 rounded-bl-[0.45rem]'}">
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
            if (product.image_url && inventoryImagePreview && inventoryImagePreviewContainer) {
                inventoryImagePreview.src = product.image_url;
                inventoryImagePreviewContainer.hidden = false;
            } else if (inventoryImagePreviewContainer) {
                inventoryImagePreviewContainer.hidden = true;
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
            if (inventoryImagePreviewContainer) {
                inventoryImagePreviewContainer.hidden = true;
            }
            if (inventoryImagePreview) {
                inventoryImagePreview.src = '';
            }
        }
    };

    inventoryImageInput?.addEventListener('change', (event) => {
        const file = event.target.files[0];
        if (file && inventoryImagePreview && inventoryImagePreviewContainer) {
            const reader = new FileReader();
            reader.onload = (e) => {
                inventoryImagePreview.src = e.target.result;
                inventoryImagePreviewContainer.hidden = false;
            };
            reader.readAsDataURL(file);
        }
    });

    removeInventoryImageBtn?.addEventListener('click', () => {
        if (inventoryImageInput) {
            inventoryImageInput.value = '';
        }
        if (inventoryImagePreviewContainer) {
            inventoryImagePreviewContainer.hidden = true;
        }
        if (inventoryImagePreview) {
            inventoryImagePreview.src = '';
        }
    });

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

    const resizeAdminMessageDraft = () => {
        if (!adminMessageDraft) {
            return;
        }

        adminMessageDraft.style.height = 'auto';
        adminMessageDraft.style.height = `${adminMessageDraft.scrollHeight}px`;
    };

    const setAdminMessageDraftValue = (value = '') => {
        if (!adminMessageDraft) {
            return;
        }

        adminMessageDraft.value = value;
        resizeAdminMessageDraft();
    };

    adminMessageDraft?.addEventListener('input', () => {
        resizeAdminMessageDraft();
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
        setAdminMessageDraftValue('');
        renderAdminMessageFeed({ forceScroll: true });

        let response;
        try {
            response = await axios.post('/api/messages', {
                conversation_id: currentConvId,
                content: messageContent,
            });
        } catch (error) {
            console.error('Failed to send message:', error);
            conversation.messages = conversation.messages.filter(m => m.id !== tempId);
            setAdminMessageDraftValue(messageContent);
            renderAdminMessageFeed();
            isAdminSendingMessage = false;
            adminMessageSend.disabled = false;
            return;
        }

        try {
            const savedMessage = normalizeAdminMessage(response.data);
            const msgIndex = conversation.messages.findIndex((message) => message.id === tempId);

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
            console.error('Failed to update admin message UI after send:', error);
        } finally {
            setAdminMessageDraftValue('');
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

    dashboard.addEventListener('click', async (event) => {
        const exportButton = event.target.closest('[data-export-format]');
        if (exportButton) {
            const format = exportButton.dataset.exportFormat;
            const target = exportTarget?.value || 'summary';
            const payload = getExportPayload(target);
            const rows = getReportRows(target);
            const stamp = new Date().toISOString().slice(0, 10);

            if (format === 'excel') {
                const workbook = await buildExcelWorkbook(target, rows);
                if (!workbook) {
                    if (exportFeedback) exportFeedback.textContent = 'No data available for Excel export.';
                    return;
                }
                downloadContent(`report-${target}-${stamp}.xlsx`, workbook, 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
            }

            if (format === 'pdf') {
                const opened = exportPdfReport(target, payload, rows);
                if (!opened) {
                    return;
                }
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
                    image_url: row.dataset.productImageUrl,
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
                beginLoading('Updating order...');

                try {
                    const response = await fetch(url, {
                        ...requestConfig,
                        body: JSON.stringify(payload),
                    });

                    const responsePayload = await response.json().catch(() => null);

                    if (!response.ok) {
                        throw new Error(responsePayload?.message || 'Unable to update the order right now.');
                    }

                    applyMutationResult(responsePayload);
                } catch (error) {
                    window.alert(error.message || 'Unable to update the order right now.');
                    orderAction.disabled = false;
                } finally {
                    endLoading();
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

    inventoryForm?.addEventListener('submit', async (event) => {
        event.preventDefault();

        const mode = inventoryForm.dataset.mode || 'add';
        const productId = inventoryId ? inventoryId.value : '';
        const baseUrl = inventoryForm.dataset.updateUrlBase;
        const formData = new FormData(inventoryForm);
        beginLoading(mode === 'edit' ? 'Updating product...' : 'Creating product...');

        if (mode === 'edit' && productId) {
            inventoryForm.action = `${baseUrl}/${productId}`;
            if (inventoryMethod) {
                inventoryMethod.disabled = false;
                inventoryMethod.value = 'PUT';
            }
            formData.set('_method', 'PUT');
        } else {
            inventoryForm.action = inventoryForm.dataset.storeUrl || inventoryForm.action;
            if (inventoryMethod) {
                inventoryMethod.disabled = true;
                inventoryMethod.value = '';
            }
            formData.delete('_method');
        }

        inventoryFeedback && (inventoryFeedback.textContent = 'Saving...');

        try {
            const response = await fetch(inventoryForm.action, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': csrfToken,
                    'Accept': 'application/json',
                },
                body: formData,
            });

            const responsePayload = await response.json().catch(() => null);

            if (!response.ok) {
                throw new Error(responsePayload?.message || 'Unable to save the product right now.');
            }

            applyMutationResult(responsePayload);
            closeInventoryDrawer();
        } catch (error) {
            if (inventoryFeedback) {
                inventoryFeedback.textContent = error.message || 'Unable to save the product right now.';
            }
        } finally {
            endLoading();
        }
    });

    modalConfirm?.addEventListener('click', async () => {
        if (!modalAction) return;

        if (modalAction.productId) {
            beginLoading('Removing product...');
            try {
                const response = await fetch(`/admin/inventory/${modalAction.productId}`, {
                    method: 'DELETE',
                    headers: {
                        'X-CSRF-TOKEN': csrfToken,
                        'Accept': 'application/json',
                    },
                });
                const body = await response.json().catch(() => null);

                if (!response.ok) {
                    throw new Error(body?.message || 'Unable to remove the product right now.');
                }

                applyMutationResult(body);
            } catch (error) {
                window.alert(error.message || 'Unable to remove the product right now.');
            } finally {
                endLoading();
            }
            closeModal();
            return;
        }

        if (modalAction.promoId) {
            beginLoading('Removing promo...');
            try {
                const response = await fetch(`/admin/promos/${modalAction.promoId}`, {
                    method: 'DELETE',
                    headers: {
                        'X-CSRF-TOKEN': csrfToken,
                        'Accept': 'application/json',
                    },
                });
                const body = await response.json().catch(() => null);

                if (!response.ok) {
                    throw new Error(body?.message || 'Unable to remove the promo right now.');
                }

                applyMutationResult(body);
            } catch (error) {
                window.alert(error.message || 'Unable to remove the promo right now.');
            } finally {
                endLoading();
            }
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
            beginLoading('Updating account...');
            try {
                const response = await fetch(`/admin/users/${modalAction.personId}/status`, {
                    method: 'PATCH',
                    headers: {
                        'X-CSRF-TOKEN': csrfToken,
                        'Accept': 'application/json',
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify({}),
                });
                const body = await response.json().catch(() => null);

                if (!response.ok || !body?.success) {
                    throw new Error(body?.message || 'Unable to update the account right now.');
                }

                applyMutationResult(body);
            } catch (error) {
                console.error('Error toggling user status:', error);
                window.alert(error.message || 'Unable to update the account right now.');
            } finally {
                endLoading();
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

    promoForm?.addEventListener('submit', async (event) => {
        event.preventDefault();

        const mode = promoForm.dataset.mode || 'add';
        const promoId = promoIdInput ? promoIdInput.value : '';
        const baseUrl = promoForm.dataset.updateUrlBase;
        const formData = new FormData(promoForm);
        beginLoading(mode === 'edit' ? 'Updating promo...' : 'Creating promo...');

        if (mode === 'edit' && promoId) {
            promoForm.action = `${baseUrl}/${promoId}`;
            if (promoMethodInput) {
                promoMethodInput.disabled = false;
                promoMethodInput.value = 'PUT';
            }
            formData.set('_method', 'PUT');
        } else {
            promoForm.action = promoForm.dataset.storeUrl || promoForm.action;
            if (promoMethodInput) {
                promoMethodInput.disabled = true;
                promoMethodInput.value = '';
            }
            formData.delete('_method');
        }

        if (promoSubtitle) {
            promoSubtitle.textContent = 'Saving...';
        }

        try {
            const response = await fetch(promoForm.action, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': csrfToken,
                    'Accept': 'application/json',
                },
                body: formData,
            });

            const responsePayload = await response.json().catch(() => null);

            if (!response.ok) {
                throw new Error(responsePayload?.message || 'Unable to save the promo right now.');
            }

            applyMutationResult(responsePayload);
            closePromoDrawer();
        } catch (error) {
            if (promoSubtitle) {
                promoSubtitle.textContent = error.message || 'Unable to save the promo right now.';
            }
        } finally {
            endLoading();
        }
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
