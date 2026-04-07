@extends('layouts.admin')

@section('title', 'Jewellery Price Calculator')

@push('styles')
    <link rel="preconnect" href="https://fonts.googleapis.com" />
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600&display=swap" rel="stylesheet" />
    <style>
        .jpc {
            --bg: #f4f5f7;
            --surface: #ffffff;
            --surface-2: #fafafa;
            --border: #e5e7eb;
            --border-2: #eef0f3;
            --gold: #b8932a;
            --gold-bg: #fef9ee;
            --gold-border: #f0d98a;
            --text-1: #1f2937;
            --text-2: #6b7280;
            --text-3: #9ca3af;
            --text-4: #d1d5db;
            --green: #16a34a;
            --green-bg: #f0fdf4;
            --red: #dc2626;
            --blue-bg: #eff6ff;
            --radius: 12px;
            --radius-sm: 8px;
            --shadow: 0 1px 3px rgba(0, 0, 0, 0.08), 0 1px 2px rgba(0, 0, 0, 0.04);
            --shadow-md: 0 4px 12px rgba(0, 0, 0, 0.08);
            --font: 'Inter', -apple-system, sans-serif;
            --ease: cubic-bezier(0.4, 0, 0.2, 1);
        }

        .jpc {
            padding: 32px 36px 64px;
            font-family: var(--font);
            color: var(--text-1);
            -webkit-font-smoothing: antialiased;
            background: transparent;
        }

        /* ── Page Header ─────────────────────────────── */
        .jpc-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 20px;
            margin-bottom: 28px;
            animation: jpc-in 0.4s var(--ease) both;
        }

        .jpc-title-wrap {}

        .jpc-breadcrumb {
            display: flex;
            align-items: center;
            gap: 6px;
            font-size: 0.72rem;
            color: var(--text-3);
            margin-bottom: 6px;
            font-weight: 400;
            letter-spacing: 0.01em;
        }

        .jpc-breadcrumb svg {
            opacity: 0.6;
        }

        .jpc-page-title {
            font-size: 1.45rem;
            font-weight: 600;
            color: var(--text-1);
            letter-spacing: -0.02em;
            margin: 0 0 3px;
            line-height: 1.2;
        }

        .jpc-page-sub {
            font-size: 0.82rem;
            color: var(--text-2);
            font-weight: 400;
        }

        .jpc-live-pill {
            display: inline-flex;
            align-items: center;
            gap: 7px;
            background: var(--green-bg);
            border: 1px solid #bbf7d0;
            border-radius: 20px;
            padding: 6px 14px;
            font-size: 0.72rem;
            font-weight: 500;
            color: var(--green);
            letter-spacing: 0.01em;
            white-space: nowrap;
            flex-shrink: 0;
        }

        .jpc-live-dot {
            width: 6px;
            height: 6px;
            border-radius: 50%;
            background: var(--green);
            animation: jpc-pulse 2s ease-in-out infinite;
            flex-shrink: 0;
        }

        .jpc-live-dot.err {
            background: var(--red);
            animation: none;
        }

        /* ── Rate Card ───────────────────────────────── */
        .jpc-rate-card {
            background: var(--surface);
            border: 1px solid var(--border);
            border-radius: var(--radius);
            padding: 22px 28px;
            margin-bottom: 20px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 20px;
            box-shadow: var(--shadow);
            animation: jpc-in 0.4s 0.05s var(--ease) both;
        }

        .jpc-rate-card-left {
            display: flex;
            align-items: center;
            gap: 16px;
        }

        .jpc-rate-icon {
            width: 44px;
            height: 44px;
            border-radius: 10px;
            background: var(--gold-bg);
            border: 1px solid var(--gold-border);
            display: flex;
            align-items: center;
            justify-content: center;
            color: var(--gold);
            flex-shrink: 0;
        }

        .jpc-rate-info {}

        .jpc-rate-label {
            font-size: 0.72rem;
            font-weight: 500;
            color: var(--text-2);
            margin-bottom: 3px;
            letter-spacing: 0.01em;
        }

        .jpc-rate-value {
            font-size: 1.75rem;
            font-weight: 600;
            color: var(--gold);
            letter-spacing: -0.03em;
            line-height: 1;
            transition: color 0.2s;
        }

        .jpc-rate-value.loading {
            font-size: 1rem;
            font-weight: 400;
            color: var(--text-3);
            letter-spacing: 0;
        }

        .jpc-rate-value.err {
            color: var(--red);
            font-size: 1rem;
            font-weight: 400;
        }

        .jpc-rate-meta {
            display: flex;
            align-items: center;
            gap: 8px;
            margin-top: 4px;
            font-size: 0.7rem;
            color: var(--text-3);
        }

        .jpc-ts {
            color: var(--green);
            font-weight: 500;
        }

        .jpc-ts.err {
            color: var(--red);
        }

        .jpc-err-msg {
            display: none;
            color: var(--red);
        }

        .jpc-rate-card-right {
            display: flex;
            align-items: center;
            gap: 24px;
        }

        .jpc-rate-stat {
            text-align: center;
            padding: 0 20px;
            border-left: 1px solid var(--border-2);
        }

        .jpc-rate-stat-label {
            font-size: 0.68rem;
            color: var(--text-3);
            font-weight: 400;
            margin-bottom: 3px;
        }

        .jpc-rate-stat-val {
            font-size: 0.9rem;
            font-weight: 600;
            color: var(--text-1);
        }

        .jpc-calc-btn {
            background: var(--gold);
            color: #fff;
            border: none;
            border-radius: var(--radius-sm);
            font-family: var(--font);
            font-size: 0.8rem;
            font-weight: 500;
            padding: 9px 20px;
            cursor: pointer;
            transition: all 0.2s var(--ease);
            white-space: nowrap;
            letter-spacing: 0.01em;
        }

        .jpc-calc-btn:hover {
            background: #a07820;
            box-shadow: 0 4px 12px rgba(184, 147, 42, 0.3);
            transform: translateY(-1px);
        }

        /* ── Stats Row ───────────────────────────────── */
        .jpc-stats-row {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 12px;
            margin-bottom: 20px;
            animation: jpc-in 0.4s 0.1s var(--ease) both;
        }

        .jpc-stat-card {
            background: var(--surface);
            border: 1px solid var(--border);
            border-radius: var(--radius);
            padding: 16px 20px;
            box-shadow: var(--shadow);
            display: flex;
            align-items: center;
            gap: 14px;
        }

        .jpc-stat-icon {
            width: 36px;
            height: 36px;
            border-radius: 8px;
            display: flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
            font-size: 0.85rem;
        }

        .jpc-stat-icon.gold {
            background: var(--gold-bg);
            color: var(--gold);
            border: 1px solid var(--gold-border);
        }

        .jpc-stat-icon.blue {
            background: var(--blue-bg);
            color: #3b82f6;
            border: 1px solid #bfdbfe;
        }

        .jpc-stat-icon.green {
            background: var(--green-bg);
            color: var(--green);
            border: 1px solid #bbf7d0;
        }

        .jpc-stat-info {}

        .jpc-stat-label {
            font-size: 0.68rem;
            color: var(--text-3);
            font-weight: 400;
            margin-bottom: 2px;
            white-space: nowrap;
        }

        .jpc-stat-val {
            font-size: 1rem;
            font-weight: 600;
            color: var(--text-1);
            letter-spacing: -0.01em;
            line-height: 1.2;
        }

        /* ── Grid ────────────────────────────────────── */
        .jpc-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 16px;
            align-items: start;
        }

        /* ── Card ────────────────────────────────────── */
        .jpc-card {
            background: var(--surface);
            border: 1px solid var(--border);
            border-radius: var(--radius);
            padding: 24px;
            box-shadow: var(--shadow);
        }

        .jpc-card-inputs {
            animation: jpc-in 0.4s 0.15s var(--ease) both;
        }

        .jpc-card-breakdown {
            animation: jpc-in 0.4s 0.2s var(--ease) both;
        }

        .jpc-card-header {
            display: flex;
            align-items: center;
            gap: 10px;
            margin-bottom: 22px;
            padding-bottom: 16px;
            border-bottom: 1px solid var(--border-2);
        }

        .jpc-card-icon {
            width: 32px;
            height: 32px;
            border-radius: 8px;
            background: var(--surface-2);
            border: 1px solid var(--border);
            display: flex;
            align-items: center;
            justify-content: center;
            color: var(--text-2);
        }

        .jpc-card-title {
            font-size: 0.92rem;
            font-weight: 600;
            color: var(--text-1);
            letter-spacing: -0.01em;
            margin: 0;
        }

        /* ── Form ────────────────────────────────────── */
        .jpc-form {
            display: flex;
            flex-direction: column;
            gap: 16px;
        }

        .jpc-row2 {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 12px;
        }

        .jpc-field {
            display: flex;
            flex-direction: column;
            gap: 6px;
        }

        .jpc-label {
            font-size: 0.72rem;
            font-weight: 500;
            color: var(--text-2);
            letter-spacing: 0.01em;
        }

        .jpc-input,
        .jpc-select {
            background: var(--surface-2);
            border: 1px solid var(--border);
            border-radius: var(--radius-sm);
            color: var(--text-1);
            font-family: var(--font);
            font-size: 0.88rem;
            font-weight: 400;
            padding: 9px 12px;
            width: 100%;
            outline: none;
            -webkit-appearance: none;
            appearance: none;
            transition: border-color 0.18s, box-shadow 0.18s, background 0.18s;
        }

        .jpc-input::placeholder {
            color: var(--text-4);
        }

        .jpc-input:focus,
        .jpc-select:focus {
            background: var(--surface);
            border-color: var(--gold);
            box-shadow: 0 0 0 3px rgba(184, 147, 42, 0.1);
        }

        .jpc-input:hover:not(:focus),
        .jpc-select:hover:not(:focus) {
            border-color: #d1d5db;
            background: var(--surface);
        }

        .jpc-select {
            background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 20 20' fill='%239ca3af'%3E%3Cpath fill-rule='evenodd' d='M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z' clip-rule='evenodd'/%3E%3C/svg%3E");
            background-repeat: no-repeat;
            background-position: right 10px center;
            background-size: 14px;
            padding-right: 32px;
            cursor: pointer;
        }

        /* ── Breakdown List ──────────────────────────── */
        .jpc-blist {
            display: flex;
            flex-direction: column;
        }

        .jpc-brow {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 10px 0;
            border-bottom: 1px solid var(--border-2);
        }

        .jpc-brow:last-of-type {
            border-bottom: none;
        }

        .jpc-brow.hidden {
            display: none;
        }

        .jpc-bkey {
            font-size: 0.78rem;
            color: var(--text-2);
            font-weight: 400;
        }

        .jpc-bval {
            font-size: 0.84rem;
            font-weight: 600;
            color: var(--text-1);
            text-align: right;
        }

        .jpc-bval .u {
            font-size: 0.68rem;
            font-weight: 400;
            color: var(--text-3);
            margin-left: 2px;
        }

        .jpc-bval.add {
            color: #16a34a;
        }

        .jpc-bval.add::before {
            content: '+ ';
            font-weight: 400;
        }

        .jpc-bval.sub {
            color: #dc2626;
        }

        .jpc-bval.sub::before {
            content: '− ';
            font-weight: 400;
        }

        /* ── Final Price ─────────────────────────────── */
        .jpc-final-box {
            margin-top: 18px;
            background: var(--gold-bg);
            border: 1px solid var(--gold-border);
            border-radius: var(--radius-sm);
            padding: 18px 20px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 12px;
        }

        .jpc-final-label {
            font-size: 0.75rem;
            font-weight: 500;
            color: var(--text-2);
            margin-bottom: 3px;
        }

        .jpc-final-amount {
            font-size: 1.65rem;
            font-weight: 700;
            color: var(--gold);
            letter-spacing: -0.03em;
            line-height: 1;
        }

        .jpc-final-icon {
            width: 40px;
            height: 40px;
            border-radius: 10px;
            background: #fff;
            border: 1px solid var(--gold-border);
            display: flex;
            align-items: center;
            justify-content: center;
            color: var(--gold);
            flex-shrink: 0;
        }

        /* ── Note ────────────────────────────────────── */
        .jpc-note {
            display: flex;
            gap: 10px;
            align-items: flex-start;
            background: var(--surface-2);
            border: 1px solid var(--border-2);
            border-radius: var(--radius-sm);
            padding: 12px 14px;
            margin-top: 14px;
        }

        .jpc-note-icon {
            color: var(--text-3);
            flex-shrink: 0;
            margin-top: 1px;
        }

        .jpc-note-text {
            font-size: 0.72rem;
            color: var(--text-2);
            line-height: 1.65;
            font-weight: 400;
        }

        .jpc-note-text strong {
            color: var(--text-1);
            font-weight: 500;
        }

        /* ── Animations ──────────────────────────────── */
        @keyframes jpc-in {
            from {
                opacity: 0;
                transform: translateY(10px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        @keyframes jpc-pulse {

            0%,
            100% {
                opacity: 1;
            }

            50% {
                opacity: 0.4;
            }
        }

        @keyframes jpc-flash {
            0% {
                background: var(--gold-bg);
            }

            50% {
                background: #fef3c7;
            }

            100% {
                background: var(--gold-bg);
            }
        }

        .jpc-flash-anim {
            animation: jpc-flash 0.5s var(--ease);
        }

        /* hide spinners */
        .jpc-input[type=number]::-webkit-inner-spin-button,
        .jpc-input[type=number]::-webkit-outer-spin-button {
            -webkit-appearance: none;
        }

        .jpc-input[type=number] {
            -moz-appearance: textfield;
        }

        /* ── Responsive ──────────────────────────────── */
        @media (max-width: 900px) {
            .jpc {
                padding: 24px 16px 48px;
            }

            .jpc-grid {
                grid-template-columns: 1fr;
            }

            .jpc-rate-card {
                flex-direction: column;
                align-items: flex-start;
            }

            .jpc-rate-card-right {
                flex-direction: row;
                flex-wrap: wrap;
                gap: 12px;
            }

            .jpc-rate-stat {
                border-left: none;
                border-top: 1px solid var(--border-2);
                padding: 12px 0 0;
            }
        }

        @media (max-width: 580px) {
            .jpc-stats-row {
                grid-template-columns: 1fr;
            }

            .jpc-row2 {
                grid-template-columns: 1fr;
            }

            .jpc-header {
                flex-direction: column;
                align-items: flex-start;
            }
        }
    </style>
@endpush

@section('content')
    <div class="jpc">

        {{-- ── Page Header ── --}}
        <header class="jpc-header">
            <div class="jpc-title-wrap">
                <div class="jpc-breadcrumb">
                    <span>Tools</span>
                    <svg width="10" height="10" viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd"
                            d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z"
                            clip-rule="evenodd" />
                    </svg>
                    <span>Price Calculator</span>
                </div>
                <h1 class="jpc-page-title">Jewellery Price Calculator</h1>
                <p class="jpc-page-sub">Calculate accurate gold prices with live market rates</p>
            </div>
            <div class="jpc-live-pill">
                <span class="jpc-live-dot" id="live-dot"></span>
                Live Rates
            </div>
        </header>

        {{-- ── Rate Card ── --}}
        <div class="jpc-rate-card">
            <div class="jpc-rate-card-left">
                <div class="jpc-rate-icon">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                        stroke-linecap="round" stroke-linejoin="round">
                        <polyline points="22 12 18 12 15 21 9 3 6 12 2 12" />
                    </svg>
                </div>
                <div class="jpc-rate-info">
                    <div class="jpc-rate-label">24K Gold — Live Market Rate</div>
                    <div class="jpc-rate-value loading" id="display-live-rate">Fetching rate…</div>
                    <div class="jpc-rate-meta">
                        <span class="jpc-live-dot" id="rate-dot" style="width:5px;height:5px;"></span>
                        <span class="jpc-ts" id="display-timestamp">—</span>
                        <span>·</span>
                        <span>Per gram</span>
                        <span class="jpc-err-msg" id="display-error"></span>
                    </div>
                </div>
            </div>
            <div class="jpc-rate-card-right">
                <div class="jpc-rate-stat">
                    <div class="jpc-rate-stat-label">22K Rate</div>
                    <div class="jpc-rate-stat-val" id="rate-22k">—</div>
                </div>
                <div class="jpc-rate-stat">
                    <div class="jpc-rate-stat-label">18K Rate</div>
                    <div class="jpc-rate-stat-val" id="rate-18k">—</div>
                </div>
                <button class="jpc-calc-btn" onclick="document.getElementById('input-weight').focus()">
                    Start Calculating
                </button>
            </div>
        </div>

        {{-- ── Stats Row ── --}}
        <div class="jpc-stats-row">
            <div class="jpc-stat-card">
                <div class="jpc-stat-icon gold">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                        stroke-linecap="round" stroke-linejoin="round">
                        <circle cx="12" cy="12" r="10" />
                        <line x1="12" y1="8" x2="12" y2="12" />
                        <line x1="12" y1="16" x2="12.01" y2="16" />
                    </svg>
                </div>
                <div class="jpc-stat-info">
                    <div class="jpc-stat-label">Pure Gold Weight</div>
                    <div class="jpc-stat-val"><span id="stat-pure">0.000</span> g</div>
                </div>
            </div>
            <div class="jpc-stat-card">
                <div class="jpc-stat-icon blue">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                        stroke-linecap="round" stroke-linejoin="round">
                        <rect x="2" y="5" width="20" height="14" rx="2" />
                        <line x1="2" y1="10" x2="22" y2="10" />
                    </svg>
                </div>
                <div class="jpc-stat-info">
                    <div class="jpc-stat-label">Gold Cost</div>
                    <div class="jpc-stat-val" id="stat-cost">$0.00</div>
                </div>
            </div>
            <div class="jpc-stat-card">
                <div class="jpc-stat-icon green">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                        stroke-linecap="round" stroke-linejoin="round">
                        <line x1="12" y1="1" x2="12" y2="23" />
                        <path d="M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6" />
                    </svg>
                </div>
                <div class="jpc-stat-info">
                    <div class="jpc-stat-label">Final Price</div>
                    <div class="jpc-stat-val" id="stat-final" style="color:var(--gold);">$0.00</div>
                </div>
            </div>
        </div>

        {{-- ── Main Grid ── --}}
        <div class="jpc-grid">

            {{-- Inputs Card --}}
            <div class="jpc-card jpc-card-inputs">
                <div class="jpc-card-header">
                    <span class="jpc-card-icon">
                        <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                            stroke-linecap="round" stroke-linejoin="round">
                            <path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7" />
                            <path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z" />
                        </svg>
                    </span>
                    <h2 class="jpc-card-title">Calculator Inputs</h2>
                </div>

                <form id="calculator-form" class="jpc-form" onsubmit="return false">

                    <div class="jpc-row2">
                        <div class="jpc-field">
                            <label class="jpc-label" for="input-weight">Weight (grams)</label>
                            <input id="input-weight" class="jpc-input" type="number" step="0.01" min="0"
                                placeholder="e.g. 5.25" />
                        </div>
                        <div class="jpc-field">
                            <label class="jpc-label" for="input-karat">Gold Purity</label>
                            <select id="input-karat" class="jpc-select">
                                <option value="24">24K — 99.9%</option>
                                <option value="22" selected>22K — 91.7%</option>
                                <option value="18">18K — 75.0%</option>
                                <option value="14">14K — 58.5%</option>
                            </select>
                        </div>
                    </div>

                    <div class="jpc-field">
                        <label class="jpc-label" for="input-wastage">Wastage (%)</label>
                        <input id="input-wastage" class="jpc-input" type="number" step="0.1" min="0" max="100"
                            placeholder="e.g. 8" />
                    </div>

                    <div class="jpc-row2">
                        <div class="jpc-field">
                            <label class="jpc-label" for="input-making">Making Charges ($)</label>
                            <input id="input-making" class="jpc-input" type="number" min="0" placeholder="e.g. 1200" />
                        </div>
                        <div class="jpc-field">
                            <label class="jpc-label" for="input-discount">Discount ($)</label>
                            <input id="input-discount" class="jpc-input" type="number" min="0" placeholder="e.g. 500" />
                        </div>
                    </div>

                </form>
            </div>

            {{-- Breakdown Card --}}
            <div class="jpc-card jpc-card-breakdown">
                <div class="jpc-card-header">
                    <span class="jpc-card-icon">
                        <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                            stroke-linecap="round" stroke-linejoin="round">
                            <line x1="8" y1="6" x2="21" y2="6" />
                            <line x1="8" y1="12" x2="21" y2="12" />
                            <line x1="8" y1="18" x2="21" y2="18" />
                            <line x1="3" y1="6" x2="3.01" y2="6" />
                            <line x1="3" y1="12" x2="3.01" y2="12" />
                            <line x1="3" y1="18" x2="3.01" y2="18" />
                        </svg>
                    </span>
                    <h2 class="jpc-card-title">Price Breakdown</h2>
                </div>

                <div class="jpc-blist">
                    <div class="jpc-brow">
                        <span class="jpc-bkey">Original Weight</span>
                        <span class="jpc-bval"><span id="res-weight">0.00</span><span class="u">g</span></span>
                    </div>
                    <div class="jpc-brow">
                        <span class="jpc-bkey">Weight with Wastage (<span id="res-wastage-pct">0</span>%)</span>
                        <span class="jpc-bval"><span id="res-weight-wastage">0.00</span><span class="u">g</span></span>
                    </div>
                    <div class="jpc-brow">
                        <span class="jpc-bkey">Gold Purity</span>
                        <span class="jpc-bval"><span id="res-purity">0.0</span><span class="u">%</span></span>
                    </div>
                    <div class="jpc-brow">
                        <span class="jpc-bkey">Pure Gold Weight</span>
                        <span class="jpc-bval"><span id="res-pure-weight">0.000</span><span class="u">g</span></span>
                    </div>
                    <div class="jpc-brow">
                        <span class="jpc-bkey">Gold Cost</span>
                        <span class="jpc-bval" id="res-gold-cost">$0.00</span>
                    </div>
                    <div class="jpc-brow hidden" id="row-making">
                        <span class="jpc-bkey">Making Charges</span>
                        <span class="jpc-bval add" id="res-making">$0.00</span>
                    </div>
                    <div class="jpc-brow hidden" id="row-discount">
                        <span class="jpc-bkey">Discount Applied</span>
                        <span class="jpc-bval sub" id="res-discount">$0.00</span>
                    </div>
                </div>

                {{-- Final Price Box --}}
                <div class="jpc-final-box" id="final-box">
                    <div>
                        <div class="jpc-final-label">Estimated Final Price</div>
                        <div class="jpc-final-amount" id="res-final-price">$0.00</div>
                    </div>
                    <div class="jpc-final-icon">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                            stroke-linecap="round" stroke-linejoin="round">
                            <line x1="12" y1="1" x2="12" y2="23" />
                            <path d="M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6" />
                        </svg>
                    </div>
                </div>

                {{-- Note --}}
                <div class="jpc-note">
                    <span class="jpc-note-icon">
                        <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                            stroke-linecap="round" stroke-linejoin="round">
                            <circle cx="12" cy="12" r="10" />
                            <line x1="12" y1="8" x2="12" y2="12" />
                            <line x1="12" y1="16" x2="12.01" y2="16" />
                        </svg>
                    </span>
                    <p class="jpc-note-text">
                        <strong>Tip:</strong> Wastage is typically 6–12% for intricate designs.
                        Making charges vary by vendor. Always confirm with your jeweller before finalising.
                    </p>
                </div>
            </div>

        </div>{{-- /grid --}}
    </div>{{-- /jpc --}}
@endsection

@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', () => {

            const state = {
                weight: 0, karat: 22, wastage: 0,
                makingCharges: 0, discount: 0,
                liveRatePerGram: null, isLoading: true
            };

            const $ = id => document.getElementById(id);

            const fmt = v => new Intl.NumberFormat('en-US', {
                style: 'currency', currency: 'USD',
                minimumFractionDigits: 2, maximumFractionDigits: 2
            }).format(v || 0);

            const flashEl = el => {
                el.classList.remove('jpc-flash-anim');
                void el.offsetWidth;
                el.classList.add('jpc-flash-anim');
                el.addEventListener('animationend', () => el.classList.remove('jpc-flash-anim'), { once: true });
            };

            const calculate = () => {
                $('res-weight').textContent = state.weight.toFixed(2);
                $('res-wastage-pct').textContent = state.wastage;

                state.makingCharges > 0
                    ? ($('row-making').classList.remove('hidden'), $('res-making').textContent = fmt(state.makingCharges))
                    : $('row-making').classList.add('hidden');

                state.discount > 0
                    ? ($('row-discount').classList.remove('hidden'), $('res-discount').textContent = fmt(state.discount))
                    : $('row-discount').classList.add('hidden');

                if (!state.liveRatePerGram) return;

                const weightWithWastage = state.weight * (1 + state.wastage / 100);
                const purityFactor = state.karat / 24;
                const pureGoldWeight = weightWithWastage * purityFactor;
                const goldCost = pureGoldWeight * state.liveRatePerGram;
                const finalPrice = Math.max(0, goldCost + state.makingCharges - state.discount);

                $('res-weight-wastage').textContent = weightWithWastage.toFixed(2);
                $('res-purity').textContent = (purityFactor * 100).toFixed(1);
                $('res-pure-weight').textContent = pureGoldWeight.toFixed(3);
                $('res-gold-cost').textContent = fmt(goldCost);

                $('res-final-price').textContent = fmt(finalPrice);
                $('stat-pure').textContent = pureGoldWeight.toFixed(3);
                $('stat-cost').textContent = fmt(goldCost);
                $('stat-final').textContent = fmt(finalPrice);

                if (state.weight > 0) flashEl($('final-box'));
            };

            // Input bindings
            const inputMap = {
                'input-weight': 'weight',
                'input-karat': 'karat',
                'input-wastage': 'wastage',
                'input-making': 'makingCharges',
                'input-discount': 'discount'
            };

            Object.entries(inputMap).forEach(([id, key]) => {
                $(id).addEventListener('input', e => {
                    state[key] = parseFloat(e.target.value) || 0;
                    calculate();
                });
            });

            // Rate fetch
            const RATE_REFRESH_MS = 30000; // 30s
            let isFetchingRates = false;
            let lastRateFetchAt = 0;
            const fetchRates = async () => {
                if (isFetchingRates) return;
                const rateEl = $('display-live-rate');
                const tsEl = $('display-timestamp');
                const errEl = $('display-error');
                const dot1 = $('live-dot');
                const dot2 = $('rate-dot');

                try {
                    isFetchingRates = true;
                    const res = await fetch("{{ route('tools.gold-rate') }}");
                    if (!res.ok) throw new Error('Network error');
                    const data = await res.json();

                    if (data.success && data.rate) {
                        state.liveRatePerGram = parseFloat(data.rate);

                        rateEl.className = 'jpc-rate-value';
                        rateEl.textContent = fmt(state.liveRatePerGram);

                        // Derived karat rates
                        $('rate-22k').textContent = fmt(state.liveRatePerGram * 22 / 24);
                        $('rate-18k').textContent = fmt(state.liveRatePerGram * 18 / 24);

                        tsEl.className = 'jpc-ts';
                        tsEl.textContent = new Date().toLocaleTimeString();
                        dot1.className = 'jpc-live-dot';
                        dot2.className = 'jpc-live-dot';
                        errEl.style.display = 'none';
                        state.isLoading = false;
                        lastRateFetchAt = Date.now();
                        calculate();
                    } else {
                        throw new Error(data.message || 'Invalid data');
                    }
                } catch (err) {
                    // console.error('Rate fetch error:', err);
                    if (state.isLoading) {
                        rateEl.className = 'jpc-rate-value err';
                        rateEl.textContent = 'Unavailable';
                    }
                    dot1.className = 'jpc-live-dot err';
                    dot2.className = 'jpc-live-dot err';
                    tsEl.className = 'jpc-ts err';
                    errEl.style.display = 'inline';
                    // Use error message if it's not a network error
                    errEl.textContent = '· ' + (err.message && err.message !== 'Network error' ? err.message : 'Connection lost');
                } finally {
                    isFetchingRates = false;
                }
            };

            // Init
            fetchRates(); // Immediate
            setInterval(fetchRates, RATE_REFRESH_MS); // Every 30s

            // If user returns to the tab after a while, refresh immediately.
            document.addEventListener('visibilitychange', () => {
                if (document.visibilityState !== 'visible') return;
                if (Date.now() - lastRateFetchAt >= RATE_REFRESH_MS) {
                    fetchRates();
                }
            });
        });
    </script>
@endpush
