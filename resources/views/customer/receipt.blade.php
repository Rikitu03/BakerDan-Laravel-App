<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Bakerdan - Order Receipt</title>
  <link href="https://fonts.googleapis.com/css2?family=Epilogue:wght@400;500;600;700&display=swap" rel="stylesheet" />
  <style>
    *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

    body {
      font-family: 'Epilogue', sans-serif;
      background-color: #fff8f5;
      min-height: 100vh;
      display: flex;
      justify-content: center;
      align-items: flex-start;
      padding: 40px 16px;
    }

    /* ── Receipt Card ── */
    .receipt-card {
      width: 100%;
      max-width: 560px;
      background-color: #ffffff;
      border-radius: 16px;
      border: 1px solid rgba(217, 194, 184, 0.4);
      box-shadow: 0 2px 20px rgba(144, 76, 41, 0.06);
      padding: 48px;
      animation: fadeSlideUp 0.4s ease both;
    }

    @keyframes fadeSlideUp {
      from { opacity: 0; transform: translateY(12px); }
      to   { opacity: 1; transform: translateY(0); }
    }

    /* ── Header ── */
    .receipt-header {
      text-align: center;
      padding-bottom: 32px;
      margin-bottom: 32px;
      border-bottom: 1px solid #ece0da;
    }

    .receipt-header .icon-wrap {
      margin-bottom: 12px;
    }

    .receipt-header h1 {
      font-size: 28px;
      font-weight: 700;
      color: #201a17;
      letter-spacing: -0.5px;
      margin-bottom: 6px;
    }

    .receipt-header p {
      font-size: 13px;
      color: #86736b;
    }

    /* ── Meta Grid ── */
    .receipt-meta {
      display: grid;
      grid-template-columns: 1fr 1fr;
      gap: 24px;
      margin-bottom: 32px;
    }

    .meta-block.right { text-align: right; }

    .label-caps {
      font-size: 11px;
      font-weight: 700;
      letter-spacing: 0.08em;
      text-transform: uppercase;
      color: #86736b;
      margin-bottom: 6px;
    }

    .meta-value {
      font-size: 16px;
      font-weight: 600;
      color: #201a17;
      margin-bottom: 2px;
    }

    .meta-sub {
      font-size: 13px;
      color: #86736b;
    }

    /* ── Items Table ── */
    .items-table { margin-bottom: 28px; }

    .table-header {
      display: grid;
      grid-template-columns: 1fr 48px 80px;
      gap: 12px;
      padding-bottom: 10px;
      border-bottom: 1px solid #ece0da;
      margin-bottom: 4px;
    }

    .table-header .col { font-size: 11px; font-weight: 700; letter-spacing: 0.08em; text-transform: uppercase; color: #86736b; }
    .table-header .col.right { text-align: right; }

    .item-row {
      display: grid;
      grid-template-columns: 1fr 48px 80px;
      gap: 12px;
      align-items: center;
      padding: 12px 0;
      border-bottom: 1px solid #f2e6e0;
    }

    .item-row:last-child { border-bottom: none; }

    .item-name {
      font-size: 15px;
      font-weight: 500;
      color: #201a17;
      margin-bottom: 2px;
    }

    .item-desc { font-size: 12px; color: #86736b; }

    .item-qty,
    .item-price {
      font-size: 15px;
      color: #201a17;
      text-align: right;
      font-variant-numeric: tabular-nums;
    }

    /* ── Totals ── */
    .totals-section {
      border-top: 1px solid #ece0da;
      padding-top: 20px;
      margin-bottom: 28px;
    }

    .totals-inner {
      margin-left: auto;
      width: 220px;
      display: flex;
      flex-direction: column;
      gap: 10px;
    }

    .totals-row {
      display: flex;
      justify-content: space-between;
      font-size: 13px;
      color: #86736b;
    }

    .totals-row span { font-variant-numeric: tabular-nums; }

    .totals-final {
      display: flex;
      justify-content: space-between;
      border-top: 1px solid #ece0da;
      padding-top: 12px;
      margin-top: 2px;
    }

    .totals-final .total-label {
      font-size: 20px;
      font-weight: 600;
      color: #201a17;
    }

    .totals-final .total-amount {
      font-size: 20px;
      font-weight: 700;
      color: #904c29;
      font-variant-numeric: tabular-nums;
    }

    /* ── Payment Info ── */
    .payment-info {
      background-color: #fdf1eb;
      border-radius: 10px;
      padding: 14px 18px;
      margin-bottom: 32px;
      display: flex;
      align-items: center;
      gap: 14px;
    }

    .payment-icon { font-size: 22px; }

    .payment-method { font-size: 15px; color: #201a17; font-weight: 500; }

    /* ── Download Button ── */
    .receipt-actions { display: flex; justify-content: center; }

    .btn-download {
      background: #904c29;
      color: #fff;
      border: none;
      border-radius: 9999px;
      padding: 14px 32px;
      font-family: 'Epilogue', sans-serif;
      font-size: 15px;
      font-weight: 600;
      cursor: pointer;
      display: flex;
      align-items: center;
      gap: 10px;
      box-shadow: 0 2px 10px rgba(144, 76, 41, 0.18);
      transition: background 0.2s, transform 0.15s, box-shadow 0.2s;
    }

    .btn-download:hover {
      background: #d2815a;
      transform: translateY(-1px);
      box-shadow: 0 4px 16px rgba(144, 76, 41, 0.22);
    }

    .btn-download:active { transform: scale(0.97); }

    .btn-download svg { flex-shrink: 0; }
  </style>
</head>
<body>

  <!-- ══════════════════════════════════════
       ORDER RECEIPT COMPONENT
  ══════════════════════════════════════ -->
  <div class="receipt-card">

    <!-- Header -->
    <div class="receipt-header">
      <div class="icon-wrap">
        <svg width="48" height="48" viewBox="0 0 48 48" fill="none" xmlns="http://www.w3.org/2000/svg">
          <path d="M24 8C16 8 10 13 10 20C10 24 12 27 15 29L14 40H34L33 29C36 27 38 24 38 20C38 13 32 8 24 8Z" fill="#D2815A" opacity="0.15"/>
          <path d="M19 14C19 14 21 18 24 18C27 18 29 14 29 14" stroke="#D2815A" stroke-width="1.5" stroke-linecap="round"/>
          <path d="M15 22C15 22 18 26 24 26C30 26 33 22 33 22" stroke="#D2815A" stroke-width="1.5" stroke-linecap="round"/>
          <path d="M16 30L15 40H33L32 30" stroke="#D2815A" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
          <ellipse cx="24" cy="20" rx="10" ry="8" stroke="#D2815A" stroke-width="1.5"/>
        </svg>
      </div>
      <h1>Order Receipt</h1>
      <p>Thank you for supporting artisanal baking.</p>
    </div>

    <!-- Meta Information -->
    <div class="receipt-meta">
      <div class="meta-block left">
        <div class="label-caps">Order Details</div>
        <div class="meta-value">#BD-8902</div>
        <div class="meta-sub">October 24, 2023</div>
      </div>
      <div class="meta-block right">
        <div class="label-caps">Billed To</div>
        <div class="meta-value">Jane Doe</div>
        <div class="meta-sub">123 Artisan Lane</div>
      </div>
    </div>

    <!-- Items Table -->
    <div class="items-table">

      <!-- Table Header -->
      <div class="table-header">
        <div class="col">Item</div>
        <div class="col right">Qty</div>
        <div class="col right">Price</div>
      </div>

      <!-- Item: Classic Sourdough -->
      <div class="item-row">
        <div class="item-info">
          <div class="item-name">Classic Sourdough</div>
          <div class="item-desc">Naturally leavened</div>
        </div>
        <div class="item-qty">2</div>
        <div class="item-price">$16.00</div>
      </div>

      <!-- Item: Cinnamon Swirl Rolls -->
      <div class="item-row">
        <div class="item-info">
          <div class="item-name">Cinnamon Swirl Rolls</div>
          <div class="item-desc">Box of 4</div>
        </div>
        <div class="item-qty">1</div>
        <div class="item-price">$18.00</div>
      </div>

    </div>

    <!-- Totals -->
    <div class="totals-section">
      <div class="totals-inner">
        <div class="totals-row">
          <span>Subtotal</span>
          <span>$34.00</span>
        </div>
        <div class="totals-row">
          <span>Tax (12%)</span>
          <span>$4.08</span>
        </div>
        <div class="totals-final">
          <span class="total-label">Total</span>
          <span class="total-amount">$38.08</span>
        </div>
      </div>
    </div>

    <!-- Payment Info -->
    <div class="payment-info">
      <div class="payment-icon">💳</div>
      <div>
        <div class="label-caps">Payment Method</div>
        <div class="payment-method">GCash e-Wallet</div>
      </div>
    </div>

    <!-- Download Button -->
    <div class="receipt-actions">
      <button class="btn-download" onclick="this.textContent = '✓ Downloaded!'; setTimeout(() => { this.innerHTML = downloadBtnHTML; }, 2000);">
        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
          <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/>
          <polyline points="7 10 12 15 17 10"/>
          <line x1="12" y1="15" x2="12" y2="3"/>
        </svg>
        Download Receipt
      </button>
    </div>

  </div>
  <!-- ══ END RECEIPT COMPONENT ══ -->

  <script>
    // Store the original button HTML for reset after feedback
    const btn = document.querySelector('.btn-download');
    const downloadBtnHTML = btn.innerHTML;

    btn.addEventListener('click', () => {
      btn.innerHTML = '✓ Downloaded!';
      setTimeout(() => { btn.innerHTML = downloadBtnHTML; }, 2000);
    });
  </script>

</body>
</html>
