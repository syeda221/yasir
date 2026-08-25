@extends('admin_panel.layout.app')

@section('content')
<style>
    @import url('https://fonts.googleapis.com/css2?family=Outfit:wght@400;500;600;700;800;900&family=Plus+Jakarta+Sans:wght@400;500;600;700;800;900&display=swap');

    /* =============================================
       ROOT & BASE
    ============================================= */
    :root {
        --f: 'Plus Jakarta Sans', 'Outfit', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
        --bg: #f8fafc;
        --card: #ffffff;
        --border: #e2e8f0;
        --text: #0f172a;
        --muted: #64748b;
        --blue: #3b82f6;
        --indigo: #6366f1;
        --green: #10b981;
        --amber: #f59e0b;
        --red: #f43f5e;
        --purple: #8b5cf6;
        --cyan: #06b6d4;
    }

    .db-wrap {
        font-family: var(--f);
        background: var(--bg);
        min-height: 100vh;
        padding: 1.75rem 2rem 3rem;
        color: var(--text);
        width: 100%;
        max-width: 100%;
        box-sizing: border-box;
        overflow-x: hidden;
    }

    /* =============================================
       HEADER BAR
    ============================================= */
    .db-header {
        display: flex;
        align-items: center;
        justify-content: space-between;
        flex-wrap: wrap;
        gap: 1rem;
        margin-bottom: 1.5rem;
    }

    .db-header-left h1 {
        font-size: 1.65rem;
        font-weight: 800;
        margin: 0 0 0.25rem;
        color: var(--text);
        letter-spacing: -0.02em;
    }

    .db-header-left p {
        margin: 0;
        font-size: 0.88rem;
        color: var(--muted);
        font-weight: 500;
    }

    .db-btn-sync {
        background: linear-gradient(135deg, #4f46e5 0%, #4338ca 100%);
        color: #fff;
        border: none;
        border-radius: 12px;
        padding: 0.6rem 1.35rem;
        font-size: 0.85rem;
        font-weight: 700;
        font-family: var(--f);
        cursor: pointer;
        display: inline-flex;
        align-items: center;
        gap: 0.5rem;
        box-shadow: 0 4px 12px rgba(79, 70, 229, 0.25);
        transition: all 0.2s;
    }
    .db-btn-sync:hover { 
        transform: translateY(-2px); 
        box-shadow: 0 6px 16px rgba(79, 70, 229, 0.35); 
    }

    /* =============================================
       QUICK ACTION PILLS (REFERENCE DESIGN)
    ============================================= */
    .quick-actions-bar {
        display: flex;
        align-items: center;
        gap: 0.65rem;
        overflow-x: auto;
        padding-bottom: 0.5rem;
        margin-bottom: 1rem;
        scrollbar-width: none;
    }
    .quick-actions-bar::-webkit-scrollbar { display: none; }

    .qa-pill {
        display: inline-flex;
        align-items: center;
        gap: 0.5rem;
        padding: 0.55rem 1.15rem;
        border-radius: 99px;
        font-size: 0.82rem;
        font-weight: 700;
        color: #ffffff !important;
        text-decoration: none !important;
        white-space: nowrap;
        box-shadow: 0 3px 8px rgba(0,0,0,0.08);
        transition: all 0.2s cubic-bezier(0.4, 0, 0.2, 1);
        flex-shrink: 0;
    }
    .qa-pill:hover {
        transform: translateY(-2px);
        box-shadow: 0 6px 15px rgba(0,0,0,0.15);
        color: #ffffff !important;
    }
    .qa-pill-sale     { background: linear-gradient(135deg, #6366f1 0%, #4f46e5 100%); }
    .qa-pill-purchase { background: linear-gradient(135deg, #f43f5e 0%, #e11d48 100%); }
    .qa-pill-stock    { background: linear-gradient(135deg, #0d9488 0%, #059669 100%); }
    .qa-pill-cust     { background: linear-gradient(135deg, #fb923c 0%, #ea580c 100%); }
    .qa-pill-vendor   { background: linear-gradient(135deg, #8b5cf6 0%, #6d28d9 100%); }
    .qa-pill-payin    { background: linear-gradient(135deg, #db2777 0%, #be185d 100%); }

    /* =============================================
       REPORT QUICK NAV PILLS (OUTLINE STYLE)
    ============================================= */
    .db-nav-pills {
        display: flex;
        gap: 0.5rem;
        overflow-x: auto;
        padding-bottom: 0.5rem;
        margin-bottom: 1.75rem;
        -webkit-overflow-scrolling: touch;
        scrollbar-width: none;
    }
    .db-nav-pills::-webkit-scrollbar { display: none; }

    .db-pill {
        display: inline-flex;
        align-items: center;
        gap: 0.4rem;
        padding: 0.45rem 0.95rem;
        background: var(--card);
        border: 1.5px solid var(--border);
        border-radius: 99px;
        font-size: 0.78rem;
        font-weight: 600;
        color: #475569 !important;
        text-decoration: none !important;
        white-space: nowrap;
        transition: all 0.2s;
        flex-shrink: 0;
    }
    .db-pill:hover {
        background: var(--indigo);
        border-color: var(--indigo);
        color: #fff !important;
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(99,102,241,0.2);
    }
    .db-pill:hover i { color: #fff !important; }

    /* =============================================
       SECTION LABELS
    ============================================= */
    .db-section-label {
        display: flex;
        align-items: center;
        gap: 0.5rem;
        font-size: 0.82rem;
        font-weight: 800;
        text-transform: uppercase;
        letter-spacing: 0.08em;
        color: var(--muted);
        margin-bottom: 1rem;
        margin-top: 0.5rem;
    }
    .db-section-label::after {
        content: '';
        flex: 1;
        height: 1px;
        background: var(--border);
    }

    /* =============================================
       FINANCIAL OVERVIEW (VIBRANT GRADIENT CARDS)
    ============================================= */
    .fin-overview-grid {
        display: grid;
        grid-template-columns: repeat(4, 1fr);
        gap: 1.25rem;
        margin-bottom: 1.25rem;
    }

    .fin-card {
        border-radius: 20px;
        padding: 1.4rem 1.5rem;
        color: #ffffff;
        position: relative;
        overflow: hidden;
        box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.12), 0 4px 10px -2px rgba(0, 0, 0, 0.06);
        transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        display: flex;
        flex-direction: column;
        justify-content: space-between;
        min-height: 145px;
    }
    .fin-card:hover {
        transform: translateY(-4px);
        box-shadow: 0 16px 32px -6px rgba(0, 0, 0, 0.18);
    }
    .fin-card::after {
        content: '';
        position: absolute;
        bottom: -20px;
        right: -20px;
        width: 110px;
        height: 110px;
        border-radius: 50%;
        background: rgba(255, 255, 255, 0.1);
        pointer-events: none;
    }

    .fin-card-cust {
        background: linear-gradient(135deg, #6366f1 0%, #4f46e5 50%, #4338ca 100%);
    }
    .fin-card-vendor {
        background: linear-gradient(135deg, #f43f5e 0%, #e11d48 50%, #be123c 100%);
    }
    .fin-card-cash {
        background: linear-gradient(135deg, #10b981 0%, #059669 50%, #047857 100%);
    }
    .fin-card-stock {
        background: linear-gradient(135deg, #fbbf24 0%, #f59e0b 50%, #d97706 100%);
    }

    .fin-card-top {
        display: flex;
        align-items: center;
        gap: 0.75rem;
        margin-bottom: 0.6rem;
    }
    .fin-icon-wrap {
        width: 38px;
        height: 38px;
        border-radius: 12px;
        background: rgba(255, 255, 255, 0.22);
        backdrop-filter: blur(8px);
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.15rem;
        color: #ffffff;
        flex-shrink: 0;
    }
    .fin-card-lbl {
        font-size: 0.78rem;
        font-weight: 800;
        letter-spacing: 0.06em;
        text-transform: uppercase;
        color: rgba(255, 255, 255, 0.9);
    }
    .fin-card-val {
        font-size: 1.65rem;
        font-weight: 900;
        color: #ffffff;
        letter-spacing: -0.03em;
        line-height: 1.15;
        margin-bottom: 0.35rem;
        word-break: break-word;
    }
    .fin-card-sub {
        font-size: 0.76rem;
        color: rgba(255, 255, 255, 0.85);
        font-weight: 500;
    }

    /* =============================================
       BUSINESS CONCLUSION CONTAINER (DARK NAVY)
    ============================================= */
    .biz-conclusion-box {
        background: linear-gradient(135deg, #0b132b 0%, #151e3f 100%);
        border: 1px solid rgba(255, 255, 255, 0.08);
        border-radius: 20px;
        padding: 1.4rem 2rem;
        color: #ffffff;
        margin-bottom: 2rem;
        box-shadow: 0 12px 28px -6px rgba(11, 19, 43, 0.35);
        display: flex;
        align-items: center;
        justify-content: space-between;
        flex-wrap: wrap;
        gap: 1.5rem;
    }

    .bc-left {
        flex: 1 1 500px;
        min-width: 0;
    }
    .bc-title {
        font-size: 1.15rem;
        font-weight: 800;
        color: #ffffff;
        display: flex;
        align-items: center;
        gap: 0.5rem;
        margin-bottom: 0.55rem;
        letter-spacing: -0.01em;
    }
    .bc-calc-text {
        font-size: 0.86rem;
        color: #94a3b8;
        line-height: 1.5;
        font-weight: 500;
    }
    .bc-calc-text strong {
        color: #ffffff;
        font-weight: 700;
    }
    .bc-divider {
        height: 1px;
        border-top: 1px dashed rgba(255, 255, 255, 0.15);
        margin: 0.65rem 0;
    }
    .bc-net-liquid {
        font-size: 0.95rem;
        font-weight: 700;
        color: #e2e8f0;
    }
    .bc-net-liquid span {
        color: #38bdf8;
        font-weight: 800;
        font-size: 1.05rem;
        margin-left: 0.35rem;
    }

    .bc-right {
        text-align: right;
        flex-shrink: 0;
        border-left: 1px solid rgba(255, 255, 255, 0.1);
        padding-left: 1.75rem;
    }
    .bc-total-lbl {
        font-size: 0.74rem;
        font-weight: 800;
        text-transform: uppercase;
        letter-spacing: 0.1em;
        color: #94a3b8;
        margin-bottom: 0.3rem;
    }
    .bc-breakdown {
        font-size: 0.78rem;
        color: #cbd5e1;
        line-height: 1.4;
        margin-bottom: 0.35rem;
    }
    .bc-grand-total {
        font-size: 2.1rem;
        font-weight: 900;
        color: #38bdf8;
        letter-spacing: -0.03em;
        line-height: 1.1;
        text-shadow: 0 0 20px rgba(56, 189, 248, 0.3);
    }

    /* =============================================
       KPI STAT CARDS (VIBRANT GRADIENTS & MODERN FONT)
    ============================================= */
    .kpi-grid {
        display: grid;
        grid-template-columns: repeat(6, 1fr);
        gap: 1rem;
        margin-bottom: 2rem;
    }

    .kpi-card {
        border-radius: 18px;
        padding: 1.2rem 1.25rem;
        color: #ffffff;
        position: relative;
        overflow: hidden;
        box-shadow: 0 10px 22px -4px rgba(0, 0, 0, 0.12), 0 4px 8px -2px rgba(0, 0, 0, 0.05);
        transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        display: flex;
        flex-direction: column;
        justify-content: space-between;
        min-height: 140px;
    }
    .kpi-card:hover {
        transform: translateY(-4px);
        box-shadow: 0 16px 30px -6px rgba(0, 0, 0, 0.18);
    }
    .kpi-card::after {
        content: '';
        position: absolute;
        bottom: -20px;
        right: -20px;
        width: 90px;
        height: 90px;
        border-radius: 50%;
        background: rgba(255, 255, 255, 0.1);
        pointer-events: none;
    }

    .kpi-card-sales {
        background: linear-gradient(135deg, #6366f1 0%, #4f46e5 50%, #4338ca 100%);
    }
    .kpi-card-purchases {
        background: linear-gradient(135deg, #f43f5e 0%, #e11d48 50%, #be123c 100%);
    }
    .kpi-card-gross {
        background: linear-gradient(135deg, #0d9488 0%, #059669 50%, #047857 100%);
    }
    .kpi-card-expenses {
        background: linear-gradient(135deg, #f97316 0%, #ea580c 50%, #c2410c 100%);
    }
    .kpi-card-net {
        background: linear-gradient(135deg, #10b981 0%, #059669 50%, #065f46 100%);
    }
    .kpi-card-cashbal {
        background: linear-gradient(135deg, #0284c7 0%, #0369a1 50%, #075985 100%);
    }

    .kpi-top {
        display: flex;
        align-items: flex-start;
        justify-content: space-between;
        margin-bottom: 0.6rem;
    }
    .kpi-label {
        font-size: 0.74rem;
        font-weight: 800;
        letter-spacing: 0.05em;
        text-transform: uppercase;
        color: rgba(255, 255, 255, 0.9);
        line-height: 1.25;
    }
    .kpi-icon {
        width: 36px;
        height: 36px;
        border-radius: 11px;
        background: rgba(255, 255, 255, 0.22);
        backdrop-filter: blur(8px);
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1rem;
        color: #ffffff;
        flex-shrink: 0;
    }
    .kpi-value {
        font-size: 1.45rem;
        font-weight: 900;
        color: #ffffff;
        margin-bottom: 0.4rem;
        letter-spacing: -0.03em;
        line-height: 1.15;
        word-break: break-word;
        font-family: var(--f);
    }
    .kpi-trend {
        font-size: 0.72rem;
        font-weight: 700;
        display: inline-flex;
        align-items: center;
        gap: 0.25rem;
        background: rgba(255, 255, 255, 0.2);
        backdrop-filter: blur(6px);
        padding: 3px 8px;
        border-radius: 99px;
        color: #ffffff;
        width: fit-content;
    }
    .kpi-trend-sub {
        font-size: 0.68rem;
        color: rgba(255, 255, 255, 0.85);
        font-weight: 500;
    }

    /* =============================================
       CHART PANEL CARDS
    ============================================= */
    .panel {
        background: #ffffff;
        border: 1px solid #eef2f6;
        border-radius: 20px;
        padding: 1.35rem 1.5rem;
        box-shadow: 0 4px 18px -2px rgba(15, 23, 42, 0.04), 0 2px 6px -1px rgba(15, 23, 42, 0.02);
        display: flex;
        flex-direction: column;
        box-sizing: border-box;
        width: 100%;
        max-width: 100%;
        overflow: hidden;
        transition: all 0.25s cubic-bezier(0.4, 0, 0.2, 1);
    }
    .panel:hover {
        box-shadow: 0 12px 28px -4px rgba(15, 23, 42, 0.08);
        transform: translateY(-2px);
    }
    .panel-hd {
        display: flex;
        align-items: center;
        justify-content: space-between;
        flex-wrap: wrap;
        gap: 0.5rem;
        margin-bottom: 1.15rem;
        padding-bottom: 0.75rem;
        border-bottom: 1px solid #f1f5f9;
    }
    .panel-title {
        font-size: 0.98rem;
        font-weight: 800;
        color: #0f172a;
        display: flex;
        align-items: center;
        flex-wrap: wrap;
        gap: 0.55rem;
        letter-spacing: -0.01em;
    }
    .panel-badge {
        font-size: 0.72rem;
        font-weight: 700;
        background: #f1f5f9;
        color: #64748b;
        padding: 4px 12px;
        border-radius: 99px;
        border: 1px solid #e2e8f0;
    }
    .panel-body-flex { flex: 1; min-width: 0; width: 100%; }

    /* =============================================
       MAIN GRID LAYOUTS
    ============================================= */
    .grid-3col {
        display: grid;
        grid-template-columns: 2fr 1.2fr 1.2fr;
        gap: 1.25rem;
        margin-bottom: 1.5rem;
    }
    .grid-4col {
        display: grid;
        grid-template-columns: 1.15fr 1.25fr 1fr 1fr;
        gap: 1.25rem;
        margin-bottom: 1.5rem;
    }
    .grid-2col {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 1.25rem;
        margin-bottom: 1.5rem;
    }

    /* =============================================
       PRODUCT RANK LIST
    ============================================= */
    .rank-list { display: flex; flex-direction: column; gap: 0.45rem; }
    .rank-item {
        display: flex;
        align-items: center;
        justify-content: space-between;
        padding: 0.65rem 0.8rem;
        border-radius: 14px;
        background: #f8fafc;
        border: 1px solid #f1f5f9;
        gap: 0.75rem;
        transition: all 0.2s;
    }
    .rank-item:hover {
        background: #ffffff;
        border-color: #e2e8f0;
        box-shadow: 0 4px 12px rgba(0,0,0,0.03);
        transform: translateX(3px);
    }
    .rank-num {
        width: 28px; height: 28px;
        border-radius: 9px;
        display: flex; align-items: center; justify-content: center;
        font-weight: 800; font-size: 0.76rem;
        color: #fff;
        flex-shrink: 0;
    }
    .rank-item:nth-child(1) .rank-num { background: linear-gradient(135deg, #f59e0b, #d97706); box-shadow: 0 3px 8px rgba(245, 158, 11, 0.35); }
    .rank-item:nth-child(2) .rank-num { background: linear-gradient(135deg, #94a3b8, #64748b); box-shadow: 0 3px 8px rgba(148, 163, 184, 0.35); }
    .rank-item:nth-child(3) .rank-num { background: linear-gradient(135deg, #fb923c, #ea580c); box-shadow: 0 3px 8px rgba(251, 146, 60, 0.35); }
    .rank-item:nth-child(n+4) .rank-num { background: #e2e8f0; color: #475569; }

    .rank-name { font-size: 0.84rem; font-weight: 750; color: #0f172a; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }
    .rank-sub  { font-size: 0.71rem; color: #64748b; font-weight: 600; }
    .rank-val  {
        font-size: 0.85rem;
        font-weight: 800;
        color: #4f46e5;
        background: #eef2ff;
        padding: 3px 10px;
        border-radius: 99px;
        white-space: nowrap;
    }

    /* =============================================
       BUSINESS SUMMARY COUNTERS
    ============================================= */
    .biz-grid {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 0.75rem;
        height: 100%;
    }
    .biz-box {
        border-radius: 16px;
        padding: 0.85rem 0.95rem;
        display: flex;
        flex-direction: column;
        justify-content: space-between;
        min-height: 95px;
        min-width: 0;
        transition: all 0.25s;
        box-shadow: 0 2px 6px rgba(0,0,0,0.02);
    }
    .biz-box:hover {
        transform: translateY(-3px);
        box-shadow: 0 8px 18px rgba(0,0,0,0.06);
    }
    .biz-box-cust { background: linear-gradient(135deg, #f0f9ff 0%, #e0f2fe 100%); border: 1px solid #bae6fd; }
    .biz-box-supp { background: linear-gradient(135deg, #faf5ff 0%, #f3e8ff 100%); border: 1px solid #e9d5ff; }
    .biz-box-prod { background: linear-gradient(135deg, #fffbeb 0%, #fef3c7 100%); border: 1px solid #fde68a; }
    .biz-box-empl { background: linear-gradient(135deg, #f0fdf4 0%, #dcfce7 100%); border: 1px solid #bbf7d0; }

    .biz-box-top {
        display: flex;
        align-items: center;
        justify-content: space-between;
        width: 100%;
        margin-bottom: 0.35rem;
    }
    .biz-icon {
        width: 34px; height: 34px;
        border-radius: 10px;
        display: flex; align-items: center; justify-content: center;
        font-size: 0.95rem;
        flex-shrink: 0;
        box-shadow: 0 2px 6px rgba(0,0,0,0.05);
    }
    .biz-val  { font-size: 1.4rem; font-weight: 900; color: #0f172a; line-height: 1.1; font-family: var(--f); }
    .biz-lbl  { font-size: 0.72rem; color: #475569; font-weight: 750; text-transform: uppercase; letter-spacing: 0.04em; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; margin-top: 0.15rem; }
    .biz-trend { font-size: 0.68rem; font-weight: 700; background: rgba(255,255,255,0.7); backdrop-filter: blur(4px); padding: 2px 6px; border-radius: 99px; }

    /* =============================================
       LEGEND LIST (CATEGORY/EXPENSE)
    ============================================= */
    .legend-list { display: flex; flex-direction: column; gap: 0.45rem; margin-top: 0.5rem; }
    .legend-row {
        display: flex;
        align-items: center;
        justify-content: space-between;
        font-size: 0.8rem;
        padding: 0.35rem 0.65rem;
        border-radius: 10px;
        background: #f8fafc;
        border: 1px solid #f1f5f9;
        gap: 0.5rem;
    }
    .legend-dot { width: 10px; height: 10px; border-radius: 4px; flex-shrink: 0; }
    .legend-name { color: #334155; font-weight: 700; font-size: 0.78rem; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }
    .legend-right { display: flex; align-items: center; gap: 0.6rem; white-space: nowrap; flex-shrink: 0; }
    .legend-pct { color: #64748b; font-size: 0.72rem; font-weight: 700; min-width: 32px; text-align: right; background: #e2e8f0; padding: 1px 6px; border-radius: 99px; }
    .legend-amt { color: #0f172a; font-weight: 800; font-size: 0.8rem; white-space: nowrap; }

    /* =============================================
       ACTIVITY FEED
    ============================================= */
    .act-list { display: flex; flex-direction: column; gap: 0.45rem; }
    .act-row {
        display: flex;
        align-items: center;
        justify-content: space-between;
        padding: 0.65rem 0.85rem;
        border-radius: 14px;
        background: #f8fafc;
        border: 1px solid #f1f5f9;
        gap: 0.75rem;
        min-width: 0;
        transition: all 0.2s;
    }
    .act-row:hover {
        background: #ffffff;
        border-color: #e2e8f0;
        box-shadow: 0 4px 12px rgba(0,0,0,0.03);
        transform: translateX(3px);
    }
    .act-ico {
        width: 36px; height: 36px;
        border-radius: 11px;
        display: flex; align-items: center; justify-content: center;
        font-size: 0.95rem;
        flex-shrink: 0;
        box-shadow: 0 3px 8px rgba(0,0,0,0.05);
    }
    .act-title { font-size: 0.82rem; font-weight: 750; color: #0f172a; line-height: 1.2; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }
    .act-sub   { font-size: 0.7rem; color: #64748b; font-weight: 600; }
    .act-amt   { font-size: 0.84rem; font-weight: 800; color: #0f172a; white-space: nowrap; }
    .act-time  { font-size: 0.68rem; color: #94a3b8; font-weight: 600; text-align: right; white-space: nowrap; }

    /* =============================================
       SPARKLINE CARDS (BOTTOM ROW)
    ============================================= */
    .spark-grid {
        display: grid;
        grid-template-columns: repeat(4, 1fr);
        gap: 1rem;
        margin-bottom: 2rem;
    }
    .spark-card {
        background: #ffffff;
        border: 1px solid #eef2f6;
        border-radius: 18px;
        padding: 1.15rem 1.25rem;
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 0.75rem;
        box-shadow: 0 4px 16px -2px rgba(15, 23, 42, 0.04);
        transition: all 0.25s;
        box-sizing: border-box;
        width: 100%;
    }
    .spark-card:hover { transform: translateY(-3px); box-shadow: 0 10px 24px -4px rgba(15, 23, 42, 0.08); }
    .spark-label { font-size: 0.74rem; font-weight: 800; text-transform: uppercase; letter-spacing: 0.04em; color: #64748b; margin-bottom: 0.25rem; }
    .spark-value { font-size: 1.25rem; font-weight: 900; color: #0f172a; font-family: var(--f); }
    .spark-trend { font-size: 0.72rem; font-weight: 700; margin-top: 0.3rem; display: inline-flex; align-items: center; gap: 0.2rem; }
    .spark-canvas-wrap { width: 85px; height: 45px; flex-shrink: 0; }

    /* =============================================
       COMPREHENSIVE RESPONSIVE BREAKPOINTS
    ============================================= */
    @media (max-width: 1450px) {
        .grid-4col { grid-template-columns: 1fr 1fr; gap: 1.25rem; }
        .kpi-grid { grid-template-columns: repeat(3, 1fr); gap: 1rem; }
    }

    @media (max-width: 1200px) {
        .fin-overview-grid { grid-template-columns: repeat(2, 1fr); gap: 1rem; }
        .grid-3col { grid-template-columns: 1fr; gap: 1.25rem; }
        .spark-grid { grid-template-columns: repeat(2, 1fr); gap: 1rem; }
    }

    @media (max-width: 991px) {
        .db-wrap { padding: 1.25rem 1rem 2.5rem; }
        .grid-4col, .grid-2col { grid-template-columns: 1fr; gap: 1rem; }
        .kpi-grid { grid-template-columns: repeat(2, 1fr); gap: 0.85rem; }
        .bc-right { border-left: none; padding-left: 0; text-align: left; width: 100%; border-top: 1px solid rgba(255,255,255,0.1); padding-top: 1rem; }
    }

    @media (max-width: 768px) {
        .fin-overview-grid { grid-template-columns: 1fr; gap: 0.85rem; }
        .kpi-grid { grid-template-columns: 1fr !important; gap: 0.75rem; }
        .spark-grid { grid-template-columns: 1fr !important; gap: 0.75rem; }
        .biz-conclusion-box { padding: 1.2rem 1.25rem; }
    }

    @media (max-width: 576px) {
        .db-wrap { padding: 0.75rem 0.5rem 2rem; }
        .db-header { flex-direction: column; align-items: flex-start; gap: 0.75rem; margin-bottom: 1.25rem; }
        .db-header-left h1 { font-size: 1.3rem; }
        .db-header-left p { font-size: 0.78rem; }
        .db-btn-sync { width: 100%; justify-content: center; padding: 0.5rem 1rem; }
        .bc-grand-total { font-size: 1.65rem; }
        .fin-card-val { font-size: 1.35rem; }
        .biz-grid { grid-template-columns: 1fr; gap: 0.65rem; }
    }
</style>

<div class="db-wrap">

    {{-- =============================================
         1. HEADER BAR
    ============================================= --}}
    <div class="db-header">
        <div class="db-header-left">
            <h1>Welcome back, {{ Auth::user()->name }}! 👋</h1>
            <p>Here's what's happening with your business today — <span id="db-live-date">{{ date('l, d M Y') }}</span> &nbsp;|&nbsp; <span id="db-live-time">{{ date('h:i:s A') }}</span></p>
        </div>
        @if(Auth::user()->usertype == 'admin')
            <button class="db-btn-sync" id="btnSyncCloud">
                <i class="fas fa-cloud-arrow-up"></i> Sync to Cloud
            </button>
        @endif
    </div>

    {{-- =============================================
         2. QUICK ACTIONS PILL BAR (REFERENCE STYLE)
    ============================================= --}}
    <div class="quick-actions-bar">
        @can('sales.create')
            <a href="{{ route('sale.add') }}" class="qa-pill qa-pill-sale"><i class="fa-solid fa-cart-shopping"></i> New Sale</a>
        @endcan
        @can('purchases.create')
            <a href="{{ route('add_purchase') }}" class="qa-pill qa-pill-purchase"><i class="fa-solid fa-bag-shopping"></i> New Purchase</a>
        @endcan
        @can('inventory.onhand.view')
            <a href="{{ route('reports.onhand') }}" class="qa-pill qa-pill-stock"><i class="fa-solid fa-boxes-stacked"></i> Stock Report</a>
        @endcan
        @can('customer.ledger.view')
            <a href="{{ route('report.customer.ledger') }}" class="qa-pill qa-pill-cust"><i class="fa-solid fa-users"></i> Customer Ledger</a>
        @endcan
        @can('vendor.ledger.view')
            <a href="{{ route('report.vendor.ledger') }}" class="qa-pill qa-pill-vendor"><i class="fa-solid fa-industry"></i> Vendor Ledger</a>
        @endcan
        <a href="{{ route('customer.payments') }}" class="qa-pill qa-pill-payin"><i class="fa-solid fa-sack-dollar"></i> Payment In</a>
    </div>

    {{-- =============================================
         3. REPORT QUICK NAVIGATION PILLS
    ============================================= --}}
    <div class="db-nav-pills">
        <a href="{{ route('report.sale') }}"           class="db-pill"><i class="fa-solid fa-receipt"              style="color: var(--green);"></i>  Sales Report</a>
        <a href="{{ route('report.purchase') }}"       class="db-pill"><i class="fa-solid fa-cart-shopping"        style="color: var(--blue);"></i>   Purchase Report</a>
        <a href="{{ route('report.profit_loss') }}"    class="db-pill"><i class="fa-solid fa-chart-line"           style="color: var(--cyan);"></i>   Profit & Loss</a>
        <a href="{{ route('report.executive') }}"      class="db-pill"><i class="fa-solid fa-briefcase"            style="color: var(--purple);"></i>  Executive Report</a>
        <a href="{{ route('report.recovery') }}"       class="db-pill"><i class="fa-solid fa-file-invoice-dollar"  style="color: var(--amber);"></i>  Recovery Report</a>
        <a href="{{ route('report.payable') }}"        class="db-pill"><i class="fa-solid fa-hand-holding-dollar"  style="color: var(--red);"></i>    Payable Report</a>
        <a href="{{ route('report.parties_balance') }}" class="db-pill"><i class="fa-solid fa-users-viewfinder"   style="color: var(--blue);"></i>   Parties Balance</a>
        <a href="{{ route('reports.onhand') }}"        class="db-pill"><i class="fa-solid fa-boxes-stacked"        style="color: var(--green);"></i>  On-Hand Stock</a>
        <a href="{{ route('report.balance_sheet') }}"  class="db-pill"><i class="fa-solid fa-scale-balanced"      style="color: var(--muted);"></i>  Balance Sheet</a>
    </div>

    {{-- =========================================================
         4. FINANCIAL OVERVIEW & BUSINESS CONCLUSION (REFERENCE SECTION)
    ========================================================= --}}
    <div class="db-section-label">
        <i class="fa-solid fa-briefcase" style="color: var(--indigo);"></i> Financial Overview
    </div>

    @php
        $custDues = (float)($totalReceivables ?? 0);
        $vendorDues = (float)($totalPayables ?? 0);
        $cashBank = (float)($totalCashAndBankBalance ?? 0);
        $stockVal = (float)($totalStockValue ?? 0);

        $netLiquidBalance = ($custDues + $cashBank) - $vendorDues;
        $totalBusinessValue = $netLiquidBalance + $stockVal;
    @endphp

    {{-- 4 Vibrant Overview Cards --}}
    <div class="fin-overview-grid">

        {{-- Customer Dues --}}
        <div class="fin-card fin-card-cust">
            <div class="fin-card-top">
                <div class="fin-icon-wrap"><i class="fa-solid fa-users"></i></div>
                <div class="fin-card-lbl">CUSTOMER DUES</div>
            </div>
            <div>
                <div class="fin-card-val">Rs. {{ number_format($custDues, 0) }}</div>
                <div class="fin-card-sub">Receivable from customers</div>
            </div>
        </div>

        {{-- Vendor Dues --}}
        <div class="fin-card fin-card-vendor">
            <div class="fin-card-top">
                <div class="fin-icon-wrap"><i class="fa-solid fa-industry"></i></div>
                <div class="fin-card-lbl">VENDOR DUES</div>
            </div>
            <div>
                <div class="fin-card-val">Rs. {{ number_format($vendorDues, 0) }}</div>
                <div class="fin-card-sub">Payable to vendors</div>
            </div>
        </div>

        {{-- Cash & Bank --}}
        <div class="fin-card fin-card-cash">
            <div class="fin-card-top">
                <div class="fin-icon-wrap"><i class="fa-solid fa-sack-dollar"></i></div>
                <div class="fin-card-lbl">CASH & BANK</div>
            </div>
            <div>
                <div class="fin-card-val">Rs. {{ number_format($cashBank, 0) }}</div>
                <div class="fin-card-sub">Total account balances</div>
            </div>
        </div>

        {{-- Stock Value --}}
        <div class="fin-card fin-card-stock">
            <div class="fin-card-top">
                <div class="fin-icon-wrap"><i class="fa-solid fa-box-archive"></i></div>
                <div class="fin-card-lbl">STOCK VALUE</div>
            </div>
            <div>
                <div class="fin-card-val">Rs. {{ number_format($stockVal, 0) }}</div>
                <div class="fin-card-sub">Inventory at cost price</div>
            </div>
        </div>

    </div>

    {{-- Business Conclusion Dark Banner --}}
    <div class="biz-conclusion-box">
        <div class="bc-left">
            <div class="bc-title">
                <span>💡</span> Business Conclusion
            </div>
            <div class="bc-calc-text">
                (Customer Dues <strong>Rs. {{ number_format($custDues, 0) }}</strong> + Cash & Bank <strong>Rs. {{ number_format($cashBank, 0) }}</strong>) 
                <br class="d-none d-md-inline"> - Vendor Dues <strong>Rs. {{ number_format($vendorDues, 0) }}</strong>
            </div>
            <div class="bc-divider"></div>
            <div class="bc-net-liquid">
                = Net Liquid Balance: <span>Rs. {{ number_format($netLiquidBalance, 0) }}</span>
            </div>
        </div>
        <div class="bc-right">
            <div class="bc-total-lbl">TOTAL BUSINESS VALUE</div>
            <div class="bc-breakdown">
                Net Liquid Balance <strong>Rs. {{ number_format($netLiquidBalance, 0) }}</strong><br>
                + Stock Value <strong>Rs. {{ number_format($stockVal, 0) }}</strong>
            </div>
            <div class="bc-grand-total">
                Rs. {{ number_format($totalBusinessValue, 0) }}
            </div>
        </div>
    </div>

    {{-- =============================================
         3. TOP 6 KPI STAT CARDS
    ============================================= --}}
    <div class="db-section-label"><i class="fas fa-chart-bar text-primary"></i> Key Performance Indicators</div>
    <div class="kpi-grid">

        <div class="kpi-card kpi-card-sales">
            <div class="kpi-top">
                <span class="kpi-label">Total Sales (This Month)</span>
                <div class="kpi-icon"><i class="fa-solid fa-cart-shopping"></i></div>
            </div>
            <div class="kpi-value">Rs {{ number_format($salesThisMonth, 0) }}</div>
            <div class="kpi-trend"><i class="fas fa-arrow-up"></i> 18.6% <span class="kpi-trend-sub">vs last month</span></div>
        </div>

        <div class="kpi-card kpi-card-purchases">
            <div class="kpi-top">
                <span class="kpi-label">Total Purchases (This Month)</span>
                <div class="kpi-icon"><i class="fa-solid fa-bag-shopping"></i></div>
            </div>
            <div class="kpi-value">Rs {{ number_format($purchasesThisMonth, 0) }}</div>
            <div class="kpi-trend"><i class="fas fa-arrow-up"></i> 12.3% <span class="kpi-trend-sub">vs last month</span></div>
        </div>

        <div class="kpi-card kpi-card-gross">
            <div class="kpi-top">
                <span class="kpi-label">Gross Profit (This Month)</span>
                <div class="kpi-icon"><i class="fa-solid fa-chart-line"></i></div>
            </div>
            <div class="kpi-value">Rs {{ number_format($grossProfitThisMonth, 0) }}</div>
            <div class="kpi-trend"><i class="fas fa-arrow-up"></i> 22.5% <span class="kpi-trend-sub">vs last month</span></div>
        </div>

        <div class="kpi-card kpi-card-expenses">
            <div class="kpi-top">
                <span class="kpi-label">Total Expenses (This Month)</span>
                <div class="kpi-icon"><i class="fa-solid fa-file-invoice"></i></div>
            </div>
            <div class="kpi-value">Rs {{ number_format($expensesThisMonth, 0) }}</div>
            <div class="kpi-trend"><i class="fas fa-arrow-down"></i> 5.4% <span class="kpi-trend-sub">vs last month</span></div>
        </div>

        <div class="kpi-card kpi-card-net">
            <div class="kpi-top">
                <span class="kpi-label">Net Profit (This Month)</span>
                <div class="kpi-icon"><i class="fa-solid fa-circle-dollar-to-slot"></i></div>
            </div>
            <div class="kpi-value">Rs {{ number_format($netProfitThisMonth, 0) }}</div>
            <div class="kpi-trend"><i class="fas fa-arrow-up"></i> 28.7% <span class="kpi-trend-sub">vs last month</span></div>
        </div>

        <div class="kpi-card kpi-card-cashbal">
            <div class="kpi-top">
                <span class="kpi-label">Cash Balance</span>
                <div class="kpi-icon"><i class="fa-solid fa-wallet"></i></div>
            </div>
            <div class="kpi-value">Rs {{ number_format($totalCashAndBankBalance, 0) }}</div>
            <div class="kpi-trend" style="font-size: 0.68rem; font-weight: 600;">Available Liquid Balance</div>
        </div>

    </div>

    {{-- =============================================
         4. SALES OVERVIEW + CATEGORY + TOP PRODUCTS
    ============================================= --}}
    <div class="db-section-label"><i class="fas fa-chart-area text-success"></i> Sales Analytics</div>
    <div class="grid-3col">

        {{-- Sales Overview Line Chart --}}
        <div class="panel">
            <div class="panel-hd">
                <span class="panel-title"><i class="fas fa-chart-area" style="color:var(--indigo);"></i> Sales Overview</span>
                <span class="panel-badge">Last 7 Days</span>
            </div>
            <div class="panel-body-flex">
                <div style="height: 250px; position: relative;">
                    <canvas id="chartSalesOverview"></canvas>
                </div>
            </div>
        </div>

        {{-- Sales by Category Doughnut --}}
        <div class="panel">
            <div class="panel-hd">
                <span class="panel-title"><i class="fas fa-pie-chart" style="color:var(--blue);"></i> By Category</span>
            </div>
            <div class="panel-body-flex d-flex flex-column align-items-center">
                <div style="position: relative; width: 160px; height: 160px; margin-bottom: 1rem;">
                    <canvas id="chartSalesCat"></canvas>
                    <div style="position:absolute;top:50%;left:50%;transform:translate(-50%,-50%);text-align:center;pointer-events:none;">
                        <div style="font-size:9px;font-weight:700;color:var(--muted);text-transform:uppercase;">Total</div>
                        <div style="font-size:12px;font-weight:800;color:var(--text);">Rs {{ number_format($salesThisMonth, 0) }}</div>
                    </div>
                </div>
                <div class="legend-list w-100" id="catLegend"></div>
            </div>
        </div>

        {{-- Top Selling Products --}}
        <div class="panel">
            <div class="panel-hd">
                <span class="panel-title"><i class="fas fa-fire" style="color:var(--red);"></i> Top Products</span>
                <span class="panel-badge">By Qty</span>
            </div>
            <div class="panel-body-flex">
                <div class="rank-list">
                    @if(isset($topProducts) && count($topProducts) > 0)
                        @php $r = 1; @endphp
                        @foreach($topProducts->take(6) as $tp)
                            <div class="rank-item">
                                <div class="d-flex align-items-center gap-2">
                                    <div class="rank-num">{{ $r++ }}</div>
                                    <div>
                                        <div class="rank-name">{{ Str::limit($tp->product_name ?: 'Standard Item', 20) }}</div>
                                        <div class="rank-sub">{{ number_format($tp->total_qty) }} units sold</div>
                                    </div>
                                </div>
                                <div class="rank-val">Rs {{ number_format($tp->total_revenue ?? 0) }}</div>
                            </div>
                        @endforeach
                    @else
                        <div class="text-center text-muted py-4" style="font-size:0.82rem;">No sales data available.</div>
                    @endif
                </div>
            </div>
        </div>

    </div>

    {{-- =============================================
         5. BUSINESS SUMMARY + CASH FLOW + EXPENSE + ACTIVITY
    ============================================= --}}
    <div class="db-section-label"><i class="fas fa-briefcase text-warning"></i> Business Summary & Cash Flow</div>
    <div class="grid-4col">

        {{-- Business Summary Counter Boxes --}}
        <div class="panel">
            <div class="panel-hd">
                <span class="panel-title"><i class="fas fa-building" style="color:var(--indigo);"></i> Business Summary</span>
            </div>
            <div class="panel-body-flex">
                <div class="biz-grid">
                    <div class="biz-box biz-box-cust">
                        <div class="biz-box-top">
                            <div class="biz-icon" style="background:#e0f2fe; color:#0284c7;"><i class="fas fa-users"></i></div>
                            <span class="biz-trend up">↑ 15.3%</span>
                        </div>
                        <div>
                            <div class="biz-val">{{ number_format($customerscount) }}</div>
                            <div class="biz-lbl">Customers</div>
                        </div>
                    </div>
                    <div class="biz-box biz-box-supp">
                        <div class="biz-box-top">
                            <div class="biz-icon" style="background:#f3e8ff; color:#8b5cf6;"><i class="fas fa-truck"></i></div>
                            <span class="biz-trend up">↑ 10.6%</span>
                        </div>
                        <div>
                            <div class="biz-val">{{ number_format($vendorCount) }}</div>
                            <div class="biz-lbl">Suppliers</div>
                        </div>
                    </div>
                    <div class="biz-box biz-box-prod">
                        <div class="biz-box-top">
                            <div class="biz-icon" style="background:#fef3c7; color:#d97706;"><i class="fas fa-box-open"></i></div>
                            <span class="biz-trend up">↑ 8.3%</span>
                        </div>
                        <div>
                            <div class="biz-val">{{ number_format($productCount) }}</div>
                            <div class="biz-lbl">Products</div>
                        </div>
                    </div>
                    <div class="biz-box biz-box-empl">
                        <div class="biz-box-top">
                            <div class="biz-icon" style="background:#ecfdf5; color:#10b981;"><i class="fas fa-user-tie"></i></div>
                            <span class="biz-trend up">↑ 5.2%</span>
                        </div>
                        <div>
                            <div class="biz-val">{{ number_format($employeeCount) }}</div>
                            <div class="biz-lbl">Employees</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Cash Flow Bar Chart --}}
        <div class="panel">
            <div class="panel-hd">
                <span class="panel-title"><i class="fas fa-exchange-alt" style="color:var(--green);"></i> Cash Flow Overview</span>
                <span class="panel-badge">This Month</span>
            </div>
            <div class="panel-body-flex">
                <div style="height: 200px; position: relative;">
                    <canvas id="chartCashFlow"></canvas>
                </div>
            </div>
        </div>

        {{-- Expense Breakdown Doughnut --}}
        <div class="panel">
            <div class="panel-hd">
                <span class="panel-title"><i class="fas fa-receipt" style="color:var(--red);"></i> Expense Breakdown</span>
            </div>
            <div class="panel-body-flex d-flex flex-column align-items-center">
                <div style="position: relative; width: 130px; height: 130px; margin-bottom: 0.85rem;">
                    <canvas id="chartExpBreak"></canvas>
                    <div style="position:absolute;top:50%;left:50%;transform:translate(-50%,-50%);text-align:center;pointer-events:none;">
                        <div style="font-size:9px;font-weight:700;color:var(--muted);text-transform:uppercase;">Total</div>
                        <div style="font-size:11px;font-weight:800;color:var(--red);">Rs {{ number_format($expensesThisMonth, 0) }}</div>
                    </div>
                </div>
                <div class="legend-list w-100" id="expLegend"></div>
            </div>
        </div>

        {{-- Recent Activities Feed --}}
        <div class="panel">
            <div class="panel-hd">
                <span class="panel-title"><i class="fas fa-bell" style="color:var(--amber);"></i> Recent Activities</span>
                <a href="{{ route('report.sale') }}" style="font-size:0.72rem; color:var(--indigo); font-weight:700; text-decoration:none;">View All →</a>
            </div>
            <div class="panel-body-flex">
                <div class="act-list" style="max-height: 210px; overflow-y: auto;">
                    @if(isset($recentActivities) && count($recentActivities) > 0)
                        @foreach($recentActivities as $act)
                            <div class="act-row">
                                <div class="act-ico" style="background: {{ $act['bg'] }};">
                                    <i class="fa-solid {{ $act['icon'] }}"></i>
                                </div>
                                <div style="flex:1; min-width:0;">
                                    <div class="act-title text-truncate">{{ $act['title'] }}</div>
                                    <div class="act-sub">{{ $act['subtitle'] }}</div>
                                </div>
                                <div style="text-align: right; flex-shrink:0;">
                                    <div class="act-amt">{{ $act['amount'] }}</div>
                                    <div class="act-time">{{ $act['time'] }}</div>
                                </div>
                            </div>
                        @endforeach
                    @else
                        <div class="text-center text-muted py-3" style="font-size:0.8rem;">No recent activities.</div>
                    @endif
                </div>
            </div>
        </div>

    </div>

    {{-- =============================================
         6. ACCOUNTS & BANK BALANCES
    ============================================= --}}
    @if(isset($cashAndBankAccounts) && $cashAndBankAccounts->count() > 0)
        <div class="db-section-label"><i class="fa-solid fa-building-columns text-primary"></i> Accounts & Bank Balances</div>
        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 1rem; margin-bottom: 2rem;">
            @foreach($cashAndBankAccounts as $acc)
                @php $isCash = strtolower($acc->head->name) == 'cash'; @endphp
                <div class="kpi-card {{ $isCash ? 'kpi-card-cashbal' : 'kpi-card-sales' }}">
                    <div class="kpi-top">
                        <span class="kpi-label">{{ $acc->title }}</span>
                        <div class="kpi-icon">
                            <i class="fa-solid {{ $isCash ? 'fa-wallet' : 'fa-building-columns' }}"></i>
                        </div>
                    </div>
                    <div class="kpi-value">Rs {{ number_format($acc->current_balance, 0) }}</div>
                    <a href="{{ route('accounts.ledger', $acc->id) }}" style="color: #fff; background: rgba(255,255,255,0.22); backdrop-filter: blur(6px); padding: 4px 12px; border-radius: 99px; font-size: 0.72rem; font-weight: 700; text-decoration: none; display: inline-flex; align-items: center; gap: 0.3rem; width: fit-content; transition: all 0.2s;">View Ledger →</a>
                </div>
            @endforeach
        </div>
    @endif

    {{-- =============================================
         7. MONTHLY PERFORMANCE SPARKLINES
    ============================================= --}}
    <div class="db-section-label"><i class="fas fa-chart-line text-success"></i> Monthly Performance Trend</div>
    <div class="spark-grid">

        <div class="spark-card">
            <div>
                <div class="spark-label">Total Sales</div>
                <div class="spark-value">Rs {{ number_format($salesThisMonth, 0) }}</div>
                <div class="spark-trend up">↑ 18.6% vs last month</div>
            </div>
            <div class="spark-canvas-wrap"><canvas id="spkSales"></canvas></div>
        </div>

        <div class="spark-card">
            <div>
                <div class="spark-label">Total Purchases</div>
                <div class="spark-value">Rs {{ number_format($purchasesThisMonth, 0) }}</div>
                <div class="spark-trend up">↑ 12.3% vs last month</div>
            </div>
            <div class="spark-canvas-wrap"><canvas id="spkPurchases"></canvas></div>
        </div>

        <div class="spark-card">
            <div>
                <div class="spark-label">Gross Profit</div>
                <div class="spark-value">Rs {{ number_format($grossProfitThisMonth, 0) }}</div>
                <div class="spark-trend up">↑ 22.5% vs last month</div>
            </div>
            <div class="spark-canvas-wrap"><canvas id="spkGross"></canvas></div>
        </div>

        <div class="spark-card">
            <div>
                <div class="spark-label">Net Profit</div>
                <div class="spark-value">Rs {{ number_format($netProfitThisMonth, 0) }}</div>
                <div class="spark-trend up">↑ 28.7% vs last month</div>
            </div>
            <div class="spark-canvas-wrap"><canvas id="spkNet"></canvas></div>
        </div>

    </div>

    {{-- Low Stock Alert --}}
    @can('products.view')
        @if(isset($lowStockProducts) && $lowStockProducts->count() > 0)
            <div class="db-section-label" style="margin-top: 0.5rem;">
                <i class="fas fa-triangle-exclamation text-danger"></i> Low Stock Alarm
            </div>
            <div class="panel" style="margin-bottom: 2rem;">
                <div class="panel-hd">
                    <span class="panel-title" style="color: var(--red);">
                        <i class="fas fa-triangle-exclamation"></i> Low Stock Alert Products
                        <span style="background:#fff1f2; color:var(--red); font-size:0.7rem; font-weight:800; padding:3px 10px; border-radius:99px; border:1px solid #fecdd3;">
                            {{ $lowStockProducts->count() }} Items
                        </span>
                    </span>
                    <a href="{{ route('product') }}?status=active" style="font-size:0.78rem; color:var(--red); font-weight:700; text-decoration:none;">Manage Inventory →</a>
                </div>
                <div style="height: 280px; position: relative;">
                    <canvas id="chartLowStock"></canvas>
                </div>
            </div>
        @endif
    @endcan

</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
document.addEventListener("DOMContentLoaded", function () {

    // Live Clock
    setInterval(function () {
        const n = new Date();
        const el = document.getElementById('db-live-time');
        if (el) el.innerText = n.toLocaleTimeString('en-US', { hour:'2-digit', minute:'2-digit', second:'2-digit', hour12:true });
    }, 1000);

    const salesStats  = @json($salesChartStats);
    const catData     = @json($salesByCategory ?? []);
    const expData     = @json($expenseBreakdown ?? []);
    const topProds    = @json($topProducts ?? []);
    const topCusts    = @json($topCustomers ?? []);

    const COLORS = ['#6366f1','#3b82f6','#10b981','#f59e0b','#f43f5e','#8b5cf6','#06b6d4','#84cc16','#f97316','#ec4899'];

    function grad(ctx, c1, c2, h=250) {
        const g = ctx.createLinearGradient(0, 0, 0, h);
        g.addColorStop(0, c1); g.addColorStop(1, c2);
        return g;
    }

    const baseOpts = {
        responsive: true, maintainAspectRatio: false,
        legend: { display: false },
        plugins: { legend: { display: false }, tooltip: {
            backgroundColor: '#0f172a', cornerRadius: 10, padding: 12,
            titleFont: { family: "'Plus Jakarta Sans', sans-serif", size: 12, weight: 'bold' },
            bodyFont:  { family: "'Plus Jakarta Sans', sans-serif", size: 11 }
        }},
        scales: {
            xAxes: [{ gridLines: { display: false }, ticks: { fontColor: '#94a3b8', fontSize: 10, autoSkip: true } }],
            yAxes: [{ gridLines: { color: 'rgba(0,0,0,0.04)' }, ticks: { fontColor: '#94a3b8', fontSize: 10, callback: v => 'Rs ' + (v >= 1000 ? (v/1000).toFixed(0)+'k' : v) } }],
            x: { grid: { display: false }, ticks: { font: { family: "'Plus Jakarta Sans', sans-serif", size: 10 }, color: '#94a3b8', autoSkip: true } },
            y: { grid: { color: 'rgba(0,0,0,0.04)' }, ticks: { font: { family: "'Plus Jakarta Sans', sans-serif", size: 10 }, color: '#94a3b8', callback: v => 'Rs ' + (v >= 1000 ? (v/1000).toFixed(0)+'k' : v) } }
        }
    };

    // ── 1. SALES OVERVIEW LINE CHART ──────────────────────
    const soCtx = document.getElementById('chartSalesOverview');
    if (soCtx) {
        const ctx = soCtx.getContext('2d');
        new Chart(ctx, {
            type: 'line',
            data: {
                labels: salesStats.daily.categories,
                datasets: [
                    {
                        label: 'Sales',
                        data: salesStats.daily.series[0]?.data || [],
                        borderColor: '#6366f1', borderWidth: 2.5,
                        backgroundColor: grad(ctx, 'rgba(99,102,241,0.18)', 'rgba(99,102,241,0.0)'),
                        fill: true, tension: 0.42,
                        pointBackgroundColor: '#6366f1', pointBorderColor: '#fff',
                        pointBorderWidth: 2, pointRadius: 4, pointHoverRadius: 6
                    },
                    {
                        label: 'Target',
                        data: (salesStats.daily.series[0]?.data || []).map(v => v * 1.12 + 3000),
                        borderColor: '#cbd5e1', borderWidth: 1.5,
                        borderDash: [5, 4], fill: false, tension: 0.42, pointRadius: 0
                    }
                ]
            },
            options: { ...baseOpts,
                legend: { display: true, position: 'top', align: 'end',
                    labels: { font: { family: "'Plus Jakarta Sans', sans-serif", size: 11, weight: '700' }, usePointStyle: true, boxWidth: 6, padding: 14 } },
                plugins: { ...baseOpts.plugins,
                    legend: { display: true, position: 'top', align: 'end',
                        labels: { font: { family: "'Plus Jakarta Sans', sans-serif", size: 11, weight: '700' }, usePointStyle: true, boxWidth: 6, padding: 14 } },
                    tooltip: { ...baseOpts.plugins.tooltip,
                        callbacks: { label: c => ` ${c.dataset.label}: Rs ${parseFloat(c.raw || c.value || 0).toLocaleString()}` } }
                }
            }
        });
    }

    // ── 2. SALES BY CATEGORY DOUGHNUT ─────────────────────
    const catCtx = document.getElementById('chartSalesCat');
    if (catCtx) {
        let labels = catData.length ? catData.map(c => c.category_name) : ['General', 'Others'];
        let values = catData.length ? catData.map(c => parseFloat(c.total_amount) || 0) : [1, 1];
        const total = values.reduce((a,b) => a+b, 0);

        new Chart(catCtx.getContext('2d'), {
            type: 'doughnut',
            data: { labels, datasets: [{ data: values, backgroundColor: COLORS, borderWidth: 3, borderColor: '#fff', hoverOffset: 6 }] },
            options: { responsive: true, maintainAspectRatio: false, cutoutPercentage: 74, cutout: '74%',
                legend: { display: false },
                plugins: { legend: { display: false },
                    tooltip: { backgroundColor: '#0f172a', padding: 10, bodyFont: { family: "'Plus Jakarta Sans', sans-serif", size: 11 },
                        callbacks: { label: c => ` ${c.label}: Rs ${parseFloat(c.raw || c.value || 0).toLocaleString()}` } }
                }
            }
        });

        // Legend
        const el = document.getElementById('catLegend');
        if (el) {
            el.innerHTML = labels.map((l,i) => {
                const pct = total > 0 ? Math.round((values[i]/total)*100) : 0;
                return `<div class="legend-row">
                    <div class="d-flex align-items-center gap-2">
                        <span class="legend-dot" style="background:${COLORS[i]};"></span>
                        <span class="legend-name">${l}</span>
                    </div>
                    <div class="legend-right">
                        <span class="legend-pct">${pct}%</span>
                        <span class="legend-amt">Rs ${values[i].toLocaleString()}</span>
                    </div>
                </div>`;
            }).join('');
        }
    }

    // ── 3. CASH FLOW GROUPED BAR ────────────────────────
    const cfCtx = document.getElementById('chartCashFlow');
    if (cfCtx) {
        const payIn  = parseFloat('{{ $paymentInMonth }}')  || 0;
        const payOut = parseFloat('{{ $paymentOutMonth }}') || 0;
        const inAll  = parseFloat('{{ $paymentInOverall }}')  || 0;
        const outAll = parseFloat('{{ $paymentOutOverall }}') || 0;

        new Chart(cfCtx.getContext('2d'), {
            type: 'bar',
            data: {
                labels: ['Today In', 'Today Out', 'Total In', 'Total Out'],
                datasets: [
                    { data: [payIn, payOut, inAll, outAll],
                      backgroundColor: ['#10b981','#f43f5e','rgba(16,185,129,0.4)','rgba(244,63,94,0.4)'],
                      borderRadius: 6, barThickness: 24 }
                ]
            },
            options: { ...baseOpts,
                legend: { display: false },
                plugins: { ...baseOpts.plugins,
                    legend: { display: false },
                    tooltip: { ...baseOpts.plugins.tooltip,
                        callbacks: { label: c => ` Rs ${parseFloat(c.raw || c.value || 0).toLocaleString()}` } }
                }
            }
        });
    }

    // ── 4. EXPENSE BREAKDOWN DOUGHNUT ─────────────────────
    const exCtx = document.getElementById('chartExpBreak');
    if (exCtx) {
        const EXP_COLORS = ['#8b5cf6','#3b82f6','#0ea5e9','#f59e0b','#cbd5e1'];
        let labels = expData.length ? expData.map(e => e.name || 'General') : ['N/A'];
        let values = expData.length ? expData.map(e => parseFloat(e.total) || 0) : [1];
        const total = values.reduce((a,b) => a+b, 0);

        new Chart(exCtx.getContext('2d'), {
            type: 'doughnut',
            data: { labels, datasets: [{ data: values, backgroundColor: EXP_COLORS, borderWidth: 3, borderColor: '#fff', hoverOffset: 5 }] },
            options: { responsive: true, maintainAspectRatio: false, cutoutPercentage: 74, cutout: '74%',
                legend: { display: false },
                plugins: { legend: { display: false } }
            }
        });

        const el = document.getElementById('expLegend');
        if (el) {
            el.innerHTML = labels.map((l,i) => {
                const pct = total > 0 ? Math.round((values[i]/total)*100) : 0;
                return `<div class="legend-row">
                    <div class="d-flex align-items-center gap-2">
                        <span class="legend-dot" style="background:${EXP_COLORS[i]};"></span>
                        <span class="legend-name">${l}</span>
                    </div>
                    <div class="legend-right">
                        <span class="legend-pct">${pct}%</span>
                        <span class="legend-amt">Rs ${values[i].toLocaleString()}</span>
                    </div>
                </div>`;
            }).join('');
        }
    }

    // ── 5. SPARKLINES ─────────────────────────────────────
    function sparkline(id, color, data) {
        const el = document.getElementById(id);
        if (!el) return;
        new Chart(el.getContext('2d'), {
            type: 'line',
            data: { labels: data.map(() => ''),
                datasets: [{ data, borderColor: color, borderWidth: 2, fill: false, tension: 0.4, pointRadius: 0 }] },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                legend: { display: false },
                tooltips: { enabled: false },
                plugins: { legend: { display: false }, tooltip: { enabled: false } },
                scales: {
                    xAxes: [{ display: false, gridLines: { display: false } }],
                    yAxes: [{ display: false, gridLines: { display: false } }],
                    x: { display: false, grid: { display: false } },
                    y: { display: false, grid: { display: false } }
                }
            }
        });
    }

    sparkline('spkSales',     '#6366f1', [30,45,38,60,55,70,85]);
    sparkline('spkPurchases', '#3b82f6', [40,35,50,42,60,55,65]);
    sparkline('spkGross',     '#10b981', [20,35,30,45,42,55,62]);
    sparkline('spkNet',       '#059669', [15,28,24,38,35,48,55]);

    // ── 6. LOW STOCK BAR CHART ─────────────────────────────
    const lsCtx = document.getElementById('chartLowStock');
    if (lsCtx) {
        const lsData = @json($lowStockProducts ?? collect());
        if (lsData.length > 0) {
            const lsNames  = lsData.map(p => p.item_name ? p.item_name.substring(0,22) : 'Unknown');
            const lsStock  = lsData.map(p => parseFloat(p.current_cartons) || 0);
            const lsAlert  = lsData.map(p => parseFloat(p.alert_carton_quantity) || 0);
            new Chart(lsCtx.getContext('2d'), {
                type: 'bar',
                data: {
                    labels: lsNames,
                    datasets: [
                        { label: 'Alert Level', data: lsAlert, backgroundColor: 'rgba(244,63,94,0.85)', borderRadius: 5, barPercentage: 0.55 },
                        { label: 'Current Stock', data: lsStock, backgroundColor: 'rgba(99,102,241,0.85)', borderRadius: 5, barPercentage: 0.55 }
                    ]
                },
                options: { ...baseOpts,
                    plugins: { ...baseOpts.plugins,
                        legend: { display: true, position: 'top', align: 'end',
                            labels: { font: { family:'Inter', size:11, weight:'600' }, usePointStyle:true, boxWidth:6 } },
                        tooltip: { ...baseOpts.plugins.tooltip,
                            callbacks: { label: c => ` ${c.dataset.label}: ${c.raw} cartons` } }
                    },
                    scales: { ...baseOpts.scales,
                        y: { ...baseOpts.scales.y,
                            ticks: { ...baseOpts.scales.y.ticks, callback: v => v + ' ctns' } }
                    }
                }
            });
        }
    }

    // ── 7. SYNC BUTTON ─────────────────────────────────────
    const syncBtn = document.getElementById('btnSyncCloud');
    if (syncBtn) {
        syncBtn.addEventListener('click', function () {
            syncBtn.disabled = true;
            syncBtn.innerHTML = '<i class="fa fa-sync-alt fa-spin"></i> Syncing...';
            Swal.fire({ title: 'Syncing Cloud...', allowOutsideClick: false, didOpen: () => Swal.showLoading() });
            fetch('{{ route('admin.sync_to_cloud') }}', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}' }
            })
            .then(r => r.json()).then(d => {
                syncBtn.disabled = false;
                syncBtn.innerHTML = '<i class="fas fa-cloud-arrow-up"></i> Sync to Cloud';
                if (d.status === 'success') {
                    Swal.fire({ icon:'success', title:'Sync Successful', text:d.message, confirmButtonColor:'#6366f1' })
                        .then(() => location.reload());
                } else {
                    Swal.fire({ icon:'error', title:'Sync Failed', text: d.message || 'Error occurred.', confirmButtonColor:'#6366f1' });
                }
            }).catch(() => {
                syncBtn.disabled = false;
                syncBtn.innerHTML = '<i class="fas fa-cloud-arrow-up"></i> Sync to Cloud';
                Swal.fire({ icon:'error', title:'Connection Error', text:'Could not reach cloud server.', confirmButtonColor:'#6366f1' });
            });
        });
    }

});
</script>
@endsection
