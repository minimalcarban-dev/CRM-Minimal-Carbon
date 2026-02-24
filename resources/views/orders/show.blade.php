@extends('layouts.admin')

@section('title', 'Order #' . $order->id)

@section('content')

    <style>
        /* =============================================
                               THEME VARIABLES — matches project palette
                            ============================================= */
        :root {
            --primary: #4f46e5;
            --primary-hover: #4338ca;
            --primary-soft: #eef2ff;
            --success: #10b981;
            --success-soft: #d1fae5;
            --warning: #f59e0b;
            --warning-soft: #fef3c7;
            --danger: #ef4444;
            --danger-soft: #fee2e2;
            --dark: #0f172a;
            --body: #334155;
            --muted: #94a3b8;
            --border: #e2e8f0;
            --surface: #ffffff;
            --bg: #f8fafc;
            --accent: #6366f1;
            --radius-sm: 6px;
            --radius: 10px;
            --radius-lg: 14px;
            --shadow-sm: 0 1px 3px rgba(15, 23, 42, .06), 0 1px 2px rgba(15, 23, 42, .04);
            --shadow: 0 4px 16px rgba(15, 23, 42, .08);
            --shadow-md: 0 8px 28px rgba(15, 23, 42, .12);
            --transition: all .2s ease;
            --font-sans: 'DM Sans', system-ui, sans-serif;
            --font-mono: 'DM Mono', ui-monospace, monospace;
        }

        /* ── Base ────────────────────────────────── */
        .od-wrap {
            max-width: 1440px;
            margin: 0 auto;
            padding: 1.75rem 1.5rem 3rem;
            font-family: var(--font-sans);
            color: var(--body);
        }

        /* ── Page Header ─────────────────────────── */
        .od-header {
            display: flex;
            align-items: flex-start;
            justify-content: space-between;
            gap: 1rem;
            margin-bottom: 1.75rem;
            padding-bottom: 1.5rem;
            border-bottom: 1px solid var(--border);
        }

        .od-header-left {}

        .od-order-id {
            font-size: 1.875rem;
            font-weight: 800;
            color: var(--dark);
            letter-spacing: -0.5px;
            margin: 0 0 .25rem;
            line-height: 1.1;
        }

        .od-order-id span {
            color: var(--primary);
        }

        .od-order-date {
            font-size: .9375rem;
            color: var(--muted);
            margin: 0;
            display: flex;
            align-items: center;
            gap: .35rem;
        }

        .od-header-actions {
            display: flex;
            align-items: center;
            gap: .625rem;
            flex-shrink: 0;
        }

        .btn-od {
            display: inline-flex;
            align-items: center;
            gap: .5rem;
            padding: .625rem 1.25rem;
            font-size: .9375rem;
            font-weight: 600;
            border-radius: var(--radius-sm);
            border: 1.5px solid var(--border);
            background: var(--surface);
            color: var(--body);
            cursor: pointer;
            text-decoration: none;
            transition: var(--transition);
            line-height: 1;
            white-space: nowrap;
        }

        .btn-od:hover {
            border-color: var(--primary);
            color: var(--primary);
            background: var(--primary-soft);
        }

        .btn-od-primary {
            background: var(--primary);
            border-color: var(--primary);
            color: #fff;
        }

        .btn-od-primary:hover {
            background: var(--primary-hover);
            border-color: var(--primary-hover);
            color: #fff;
        }

        .btn-od i {
            font-size: .875rem;
        }

        /* ── Meta Strip ──────────────────────────── */
        .od-meta-strip {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(160px, 1fr));
            gap: 1rem;
            margin-bottom: 1.75rem;
        }

        .od-meta-card {
            display: flex;
            flex-direction: column;
            gap: .4rem;
            background: var(--surface);
            border: 2px solid var(--border);
            border-radius: var(--radius);
            padding: 1rem 1.125rem;
            transition: var(--transition);
        }

        .od-meta-card:hover {
            box-shadow: var(--shadow-sm);
            border-color: #c7d2fe;
        }

        .od-meta-label {
            font-size: .75rem;
            font-weight: 700;
            color: var(--muted);
            text-transform: uppercase;
            letter-spacing: .6px;
        }

        .od-meta-value {
            font-size: 1rem;
            font-weight: 700;
            color: var(--dark);
            line-height: 1.2;
        }

        .od-meta-sub {
            font-size: .8125rem;
            color: var(--muted);
            margin-top: .1rem;
        }

        /* ── Status Badges ───────────────────────── */
        .s-badge {
            display: inline-flex;
            align-items: center;
            gap: .3rem;
            padding: .375rem .75rem;
            border-radius: 100px;
            font-size: .8125rem;
            font-weight: 700;
            letter-spacing: .1px;
        }

        .s-badge::before {
            content: '';
            width: 6px;
            height: 6px;
            border-radius: 50%;
            background: currentColor;
            opacity: .7;
        }

        /* status colours */
        .status-ready_to_ship {
            background: #dbeafe;
            color: #1e40af;
        }

        .status-custom_diamond {
            background: #fef3c7;
            color: #92400e;
        }

        .status-custom_jewellery {
            background: #e0e7ff;
            color: #3730a3;
        }

        .status-processed {
            background: #ddd6fe;
            color: #5b21b6;
        }

        .status-completed,
        .status-diamond_completed {
            background: #d1fae5;
            color: #065f46;
        }

        .status-diamond_purchased {
            background: #fce7f3;
            color: #9f1239;
        }

        .status-factory_making {
            background: #fed7aa;
            color: #9a3412;
        }

        .status-priority {
            background: #fee2e2;
            color: #991b1b;
        }

        .status-non_priority {
            background: #f1f5f9;
            color: #475569;
        }

        .status-r_order_in_process {
            background: #dbeafe;
            color: #1e40af;
        }

        .status-r_order_shipped {
            background: #d1fae5;
            color: #065f46;
        }

        .status-d_diamond_in_discuss {
            background: #dbeafe;
            color: #1e40af;
        }

        .status-d_diamond_in_making {
            background: #fef3c7;
            color: #92400e;
        }

        .status-d_diamond_completed {
            background: #d1fae5;
            color: #065f46;
        }

        .status-d_diamond_in_certificate {
            background: #f3e8ff;
            color: #7c3aed;
        }

        .status-d_order_shipped {
            background: #1e293b;
            color: #fff;
        }

        .status-j_diamond_in_progress {
            background: #dbeafe;
            color: #1e40af;
        }

        .status-j_diamond_completed {
            background: #d1fae5;
            color: #065f46;
        }

        .status-j_diamond_in_discuss {
            background: #cffafe;
            color: #0e7490;
        }

        .status-j_cad_in_progress {
            background: #fef3c7;
            color: #92400e;
        }

        .status-j_cad_done {
            background: #f3e8ff;
            color: #7c3aed;
        }

        .status-j_order_completed {
            background: #d1fae5;
            color: #065f46;
        }

        .status-j_order_in_qc {
            background: #fef3c7;
            color: #92400e;
        }

        .status-j_qc_done {
            background: #d1fae5;
            color: #065f46;
        }

        .status-j_order_shipped {
            background: #1e293b;
            color: #fff;
        }

        .status-j_order_hold {
            background: #fee2e2;
            color: #991b1b;
        }

        .status-delivered {
            background: #d1fae5;
            color: #065f46;
        }

        .status-in_transit {
            background: #dbeafe;
            color: #1e40af;
        }

        .status-out_for_delivery {
            background: #fae8ff;
            color: #86198f;
        }

        .status-failed_attempt {
            background: #fee2e2;
            color: #be123c;
        }

        /* ── Two-column Layout ───────────────────── */
        .od-grid {
            display: grid;
            grid-template-columns: 3fr 2fr;
            gap: 1.5rem;
            align-items: start;
        }

        @media (max-width: 1100px) {
            .od-grid {
                grid-template-columns: 1fr;
            }
        }

        .od-col {
            display: flex;
            flex-direction: column;
            gap: 1.25rem;
        }

        /* ── Card ────────────────────────────────── */
        .od-card {
            background: var(--surface);
            border: 2px solid var(--border);
            border-radius: var(--radius-lg);
            overflow: hidden;
            box-shadow: var(--shadow-sm);
            transition: var(--transition);
        }

        .od-card:hover {
            box-shadow: var(--shadow);
        }

        .od-card-head {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 1rem 1.25rem;
            background: var(--bg);
            border-bottom: 2px solid var(--border);
            gap: .75rem;
        }

        .od-card-title {
            display: flex;
            align-items: center;
            gap: .5rem;
            font-size: 1rem;
            font-weight: 700;
            color: var(--dark);
            margin: 0;
            letter-spacing: -.1px;
        }

        .od-card-title i {
            font-size: 1.0625rem;
            color: var(--primary);
        }

        .od-card-body {
            padding: 1.25rem;
        }

        /* ── Info Rows ───────────────────────────── */
        .od-info-table {
            display: flex;
            flex-direction: column;
        }

        .od-info-row {
            display: grid;
            grid-template-columns: 140px 1fr;
            gap: 1.5rem;
            padding: .875rem 0;
            border-bottom: 1px solid var(--border);
            align-items: start;
        }

        .od-info-row:first-child {
            padding-top: 0;
        }

        .od-info-row:last-child {
            border-bottom: none;
            padding-bottom: 0;
        }

        .od-info-key {
            font-size: .875rem;
            font-weight: 700;
            color: var(--muted);
            text-transform: uppercase;
            letter-spacing: .4px;
            padding-top: .1rem;
        }

        .od-info-val {
            font-size: .9375rem;
            font-weight: 500;
            color: var(--dark);
            line-height: 1.6;
            word-break: break-word;
        }

        .od-info-val.address {
            white-space: pre-line;
        }

        /* ── Detail Group ────────────────────────── */
        .od-detail-group {
            margin-bottom: 1rem;
        }

        .od-detail-group:last-child {
            margin-bottom: 0;
        }

        .od-detail-label {
            display: flex;
            align-items: center;
            gap: .35rem;
            font-size: .8125rem;
            font-weight: 700;
            color: var(--muted);
            text-transform: uppercase;
            letter-spacing: .4px;
            margin-bottom: .4rem;
        }

        .od-detail-text {
            font-size: .9375rem;
            color: var(--dark);
            line-height: 1.65;
            margin: 0;
            white-space: pre-wrap;
        }

        /* ── SKU Item ────────────────────────────── */
        .sku-row {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: .5rem .75rem;
            border-radius: var(--radius-sm);
            background: var(--bg);
            border: 1px solid var(--border);
            margin-bottom: .5rem;
            gap: .75rem;
        }

        .sku-row:last-child {
            margin-bottom: 0;
        }

        .sku-code {
            font-family: var(--font-mono);
            font-size: .875rem;
            color: var(--primary);
            background: var(--primary-soft);
            padding: .2rem .5rem;
            border-radius: 4px;
        }

        .sku-price {
            font-size: .875rem;
            font-weight: 700;
            color: var(--success);
            background: var(--success-soft);
            padding: .25rem .65rem;
            border-radius: 100px;
        }

        /* ── Specs Grid ──────────────────────────── */
        .od-specs {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(130px, 1fr));
            gap: .875rem;
        }

        .od-spec {
            background: var(--bg);
            border: 1px solid var(--border);
            border-radius: var(--radius-sm);
            padding: .65rem .875rem;
        }

        .od-spec-label {
            font-size: .75rem;
            font-weight: 700;
            color: var(--muted);
            text-transform: uppercase;
            letter-spacing: .4px;
            margin-bottom: .2rem;
            display: block;
        }

        .od-spec-val {
            font-size: .9375rem;
            font-weight: 700;
            color: var(--dark);
        }

        /* ── Price Hero ──────────────────────────── */
        .od-price-hero {
            background: linear-gradient(135deg, #10b981 0%, #059669 100%);
            border-radius: var(--radius);
            padding: 1.5rem 2rem;
            text-align: center;
            color: #fff;
            position: relative;
            overflow: hidden;
        }

        .od-price-hero::before {
            content: '';
            position: absolute;
            top: -40px;
            right: -40px;
            width: 130px;
            height: 130px;
            border-radius: 50%;
            background: rgba(255, 255, 255, .08);
            pointer-events: none;
        }

        .od-price-hero::after {
            content: '';
            position: absolute;
            bottom: -30px;
            left: -30px;
            width: 100px;
            height: 100px;
            border-radius: 50%;
            background: rgba(255, 255, 255, .06);
            pointer-events: none;
        }

        .od-price-hero-label {
            font-size: .875rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: .6px;
            opacity: .85;
            margin-bottom: .5rem;
            position: relative;
            z-index: 1;
        }

        .od-price-hero-value {
            font-size: 2.25rem;
            font-weight: 800;
            letter-spacing: -1px;
            line-height: 1;
            position: relative;
            z-index: 1;
        }

        /* ── Notes ───────────────────────────────── */
        .od-notes-text {
            font-size: .9375rem;
            line-height: 1.7;
            color: var(--body);
            white-space: pre-wrap;
            background: #fffbeb;
            border: 1px solid #fde68a;
            border-radius: var(--radius-sm);
            padding: .875rem 1rem;
            margin: 0;
        }

        /* ── Shipping Specs ──────────────────────── */
        .od-ship-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(130px, 1fr));
            gap: .875rem;
            margin-bottom: 1.25rem;
        }

        /* ── Tracking Timeline ───────────────────── */
        .od-timeline-title {
            font-size: .875rem;
            font-weight: 700;
            color: var(--dark);
            text-transform: uppercase;
            letter-spacing: .5px;
            margin: 0 0 1rem;
            display: flex;
            align-items: center;
            gap: .4rem;
        }

        .od-timeline-title::after {
            content: '';
            flex: 1;
            height: 1px;
            background: var(--border);
        }

        .od-tl {
            position: relative;
            padding-left: 1.375rem;
        }

        .od-tl::before {
            content: '';
            position: absolute;
            left: 7px;
            top: 8px;
            bottom: 8px;
            width: 1.5px;
            background: var(--border);
        }

        .od-tl-item {
            position: relative;
            padding: 0 0 1.125rem .75rem;
        }

        .od-tl-item:last-child {
            padding-bottom: 0;
        }

        .od-tl-dot {
            position: absolute;
            left: -1.375rem;
            top: 5px;
            width: 14px;
            height: 14px;
            border-radius: 50%;
            background: var(--border);
            border: 2.5px solid var(--surface);
            box-shadow: 0 0 0 1.5px var(--border);
        }

        .od-tl-item:first-child .od-tl-dot {
            background: var(--primary);
            box-shadow: 0 0 0 3px var(--primary-soft), 0 0 0 1.5px var(--primary);
        }

        .od-tl-status {
            font-size: .9375rem;
            font-weight: 700;
            color: var(--dark);
            margin-bottom: .15rem;
        }

        .od-tl-date {
            font-size: .8125rem;
            color: var(--muted);
            font-family: var(--font-mono);
            letter-spacing: .2px;
        }

        .od-tl-location {
            font-size: .8125rem;
            color: var(--primary);
            margin-top: .2rem;
            display: flex;
            align-items: center;
            gap: .25rem;
        }

        .od-tl-desc {
            font-size: .8125rem;
            color: var(--muted);
            margin-top: .15rem;
        }

        .od-sync-info {
            font-size: .8125rem;
            color: var(--muted);
            margin-top: .875rem;
            display: flex;
            align-items: center;
            gap: .3rem;
        }

        /* ── Images Grid ─────────────────────────── */
        .od-img-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(130px, 1fr));
            gap: .625rem;
        }

        .od-img-item {
            aspect-ratio: 1;
            border-radius: var(--radius-sm);
            overflow: hidden;
            cursor: pointer;
            border: 1.5px solid var(--border);
            position: relative;
            transition: var(--transition);
        }

        .od-img-item:hover {
            transform: translateY(-2px);
            box-shadow: var(--shadow);
            border-color: var(--primary);
        }

        .od-img-item img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            display: block;
        }

        .od-img-overlay {
            position: absolute;
            inset: 0;
            background: rgba(79, 70, 229, .55);
            display: flex;
            align-items: center;
            justify-content: center;
            opacity: 0;
            transition: opacity .2s;
            color: #fff;
            font-size: 1.25rem;
        }

        .od-img-item:hover .od-img-overlay {
            opacity: 1;
        }

        /* ── PDF List ────────────────────────────── */
        .od-pdf-item {
            display: flex;
            align-items: center;
            gap: .75rem;
            padding: .75rem 1rem;
            border: 1.5px solid var(--border);
            border-radius: var(--radius-sm);
            margin-bottom: .5rem;
            transition: var(--transition);
            background: var(--surface);
        }

        .od-pdf-item:last-child {
            margin-bottom: 0;
        }

        .od-pdf-item:hover {
            border-color: #c7d2fe;
            background: var(--primary-soft);
        }

        .od-pdf-icon {
            width: 38px;
            height: 38px;
            border-radius: var(--radius-sm);
            background: #fee2e2;
            color: #dc2626;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.125rem;
            flex-shrink: 0;
        }

        .od-pdf-info {
            flex: 1;
            min-width: 0;
        }

        .od-pdf-name {
            font-size: .9375rem;
            font-weight: 600;
            color: var(--dark);
            margin: 0 0 .1rem;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        .od-pdf-size {
            font-size: .8125rem;
            color: var(--muted);
        }

        .od-pdf-actions {
            display: flex;
            gap: .375rem;
            flex-shrink: 0;
        }

        .od-pdf-btn {
            width: 30px;
            height: 30px;
            border-radius: var(--radius-sm);
            border: 1.5px solid var(--border);
            background: var(--surface);
            color: var(--muted);
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: .8125rem;
            transition: var(--transition);
        }

        .od-pdf-btn:hover {
            background: var(--primary);
            border-color: var(--primary);
            color: #fff;
        }

        /* ── Melee Diamond Box ───────────────────── */
        .od-melee-box {
            background: linear-gradient(135deg, var(--primary-soft), #f0f4ff);
            border: 1.5px solid #c7d2fe;
            border-radius: var(--radius);
            padding: 1rem 1.125rem;
            margin-top: 1rem;
        }

        .od-melee-title {
            font-size: .8125rem;
            font-weight: 700;
            color: var(--primary);
            margin-bottom: .75rem;
            display: flex;
            align-items: center;
            gap: .35rem;
        }

        .od-melee-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: .625rem;
        }

        .od-melee-item {}

        .od-melee-item small {
            display: block;
            font-size: .7rem;
            font-weight: 700;
            color: var(--muted);
            text-transform: uppercase;
            letter-spacing: .4px;
            margin-bottom: .15rem;
        }

        .od-melee-item span {
            font-size: .875rem;
            font-weight: 600;
            color: var(--dark);
        }

        /* ── Edit History Timeline ───────────────── */
        .od-edit-tl {
            display: flex;
            flex-direction: column;
            gap: 1rem;
        }

        .od-edit-item {
            border: 1.5px solid var(--border);
            border-radius: var(--radius-sm);
            overflow: hidden;
            transition: var(--transition);
        }

        .od-edit-item:hover {
            border-color: #c7d2fe;
        }

        .od-edit-header {
            display: flex;
            align-items: center;
            gap: .75rem;
            padding: .75rem 1rem;
            background: var(--bg);
            flex-wrap: wrap;
        }

        .od-edit-admin {
            display: flex;
            align-items: center;
            gap: .35rem;
            font-size: .9375rem;
            font-weight: 700;
            color: var(--dark);
        }

        .od-edit-time {
            font-size: .875rem;
            color: var(--muted);
            display: flex;
            align-items: center;
            gap: .25rem;
        }

        .od-edit-ago {
            font-size: .8125rem;
            color: var(--primary);
            background: var(--primary-soft);
            padding: .1rem .45rem;
            border-radius: 100px;
            font-weight: 600;
            margin-left: auto;
        }

        .changes-toggle {
            display: flex;
            align-items: center;
            gap: .4rem;
            padding: .5rem 1rem;
            background: none;
            border: none;
            font-size: .875rem;
            font-weight: 600;
            color: var(--primary);
            cursor: pointer;
            width: 100%;
            text-align: left;
            border-top: 1px solid var(--border);
            transition: var(--transition);
        }

        .changes-toggle:hover {
            background: var(--primary-soft);
        }

        .toggle-icon {
            margin-left: auto;
            transition: transform .2s;
        }

        .timeline-changes.expanded .toggle-icon {
            transform: rotate(180deg);
        }

        .changes-detail {
            display: none;
        }

        .timeline-changes.expanded .changes-detail {
            display: block;
        }

        .changes-table {
            width: 100%;
            font-size: .875rem;
            border-collapse: collapse;
        }

        .changes-table th {
            background: var(--bg);
            padding: .5rem .75rem;
            font-size: .75rem;
            font-weight: 700;
            color: var(--muted);
            text-transform: uppercase;
            letter-spacing: .4px;
            text-align: left;
            border-bottom: 1px solid var(--border);
        }

        .changes-table td {
            padding: .5rem .75rem;
            border-bottom: 1px solid var(--border);
            vertical-align: top;
        }

        .changes-table tr:last-child td {
            border-bottom: none;
        }

        .field-name {
            font-weight: 600;
            color: var(--dark);
        }

        .val-badge {
            display: inline-block;
            padding: .2rem .5rem;
            border-radius: 4px;
            font-size: .8125rem;
            font-family: var(--font-mono);
        }

        .val-old {
            background: #fee2e2;
            color: #991b1b;
        }

        .val-new {
            background: var(--success-soft);
            color: #065f46;
        }

        /* ── Modals ──────────────────────────────── */
        .od-modal {
            display: none;
            position: fixed;
            inset: 0;
            background: rgba(15, 23, 42, .75);
            z-index: 9999;
            align-items: center;
            justify-content: center;
            padding: 1.5rem;
            backdrop-filter: blur(3px);
        }

        .od-modal.active {
            display: flex;
        }

        .od-modal-box {
            background: var(--surface);
            border-radius: var(--radius-lg);
            overflow: hidden;
            display: flex;
            flex-direction: column;
            height: 95vh;
            width: 95vw;
            max-width: 1300px;
            box-shadow: var(--shadow-md);
        }

        .od-modal-head {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 1rem 1.25rem;
            border-bottom: 1.5px solid var(--border);
            background: var(--bg);
            gap: .75rem;
        }

        .od-modal-head h3 {
            font-size: 1rem;
            font-weight: 700;
            color: var(--dark);
            margin: 0;
        }

        .od-modal-close {
            width: 32px;
            height: 32px;
            border-radius: var(--radius-sm);
            border: 1.5px solid var(--border);
            background: var(--surface);
            color: var(--muted);
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: .875rem;
            transition: var(--transition);
        }

        .od-modal-close:hover {
            background: var(--danger-soft);
            border-color: var(--danger);
            color: var(--danger);
        }

        .od-modal-body {
            flex: 1;
            overflow: hidden;
            min-height: 0;
            display: flex;
            flex-direction: column;
        }

        .od-modal-body iframe,
        .od-modal-body img {
            width: 100%;
            height: 100%;
            object-fit: contain;
            border: none;
            display: block;
        }

        /* ── Spin animation ──────────────────────── */
        @keyframes od-spin {
            to {
                transform: rotate(360deg);
            }
        }

        .spin {
            animation: od-spin 1s linear infinite;
            display: inline-block;
        }



        /* ── Horizontal Stepper ──────────────────── */
        .od-stepper-wrap {
            margin-top: 1.25rem;
        }

        .od-stepper-label-row {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 1rem;
        }

        .od-stepper-heading {
            font-size: .8rem;
            font-weight: 700;
            color: var(--muted);
            text-transform: uppercase;
            letter-spacing: .5px;
            display: flex;
            align-items: center;
            gap: .35rem;
        }

        .od-stepper-heading i {
            color: var(--primary);
        }

        .od-stepper-sync {
            font-size: .75rem;
            color: var(--muted);
            display: flex;
            align-items: center;
            gap: .3rem;
        }

        /* The progress rail */
        .od-stepper {
            display: flex;
            align-items: flex-start;
            position: relative;
            padding-bottom: .5rem;
            overflow-x: auto;
            gap: 0;
            scrollbar-width: none;
        }

        .od-stepper::-webkit-scrollbar {
            display: none;
        }

        .od-step {
            display: flex;
            flex-direction: column;
            align-items: center;
            flex: 1;
            min-width: 0;
            position: relative;
            cursor: default;
        }

        /* Connector line between steps */
        .od-step:not(:last-child)::after {
            content: '';
            position: absolute;
            top: 14px;
            left: 50%;
            width: 100%;
            height: 2px;
            background: var(--border);
            z-index: 0;
            transition: background .3s;
        }

        .od-step.done:not(:last-child)::after {
            background: var(--primary);
        }

        .od-step.done:not(:last-child)::after {
            background: var(--primary);
        }

        /* Step circle */
        .od-step-circle {
            width: 28px;
            height: 28px;
            border-radius: 50%;
            border: 2.5px solid var(--border);
            background: var(--surface);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: .75rem;
            color: var(--muted);
            position: relative;
            z-index: 1;
            transition: all .3s;
            flex-shrink: 0;
        }

        .od-step.done .od-step-circle {
            background: var(--primary);
            border-color: var(--primary);
            color: #fff;
        }

        .od-step.active .od-step-circle {
            background: var(--primary);
            border-color: var(--primary);
            color: #fff;
            box-shadow: 0 0 0 4px var(--primary-soft);
        }

        .od-step.active .od-step-circle::after {
            content: '';
            position: absolute;
            inset: -5px;
            border-radius: 50%;
            border: 2px solid var(--primary);
            opacity: .3;
            animation: stepPulse 1.8s ease-in-out infinite;
        }

        @keyframes stepPulse {

            0%,
            100% {
                transform: scale(1);
                opacity: .3;
            }

            50% {
                transform: scale(1.3);
                opacity: 0;
            }
        }

        /* Step label */
        .od-step-body {
            margin-top: .5rem;
            text-align: center;
            padding: 0 .25rem;
            min-width: 0;
        }

        .od-step-name {
            font-size: .72rem;
            font-weight: 700;
            color: var(--muted);
            line-height: 1.3;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
            max-width: 80px;
            margin: 0 auto;
            display: block;
        }

        .od-step.done .od-step-name,
        .od-step.active .od-step-name {
            color: var(--dark);
        }

        .od-step-date {
            font-size: .65rem;
            color: var(--muted);
            margin-top: .15rem;
            white-space: nowrap;
            display: block;
        }

        .od-step.active .od-step-date {
            color: var(--primary);
            font-weight: 600;
        }

        /* Latest event detail box */
        .od-stepper-latest {
            margin-top: 1rem;
            padding: .75rem 1rem;
            background: var(--primary-soft);
            border: 1.5px solid #c7d2fe;
            border-radius: var(--radius-sm);
        }

        .od-stepper-latest-label {
            font-size: .7rem;
            font-weight: 700;
            color: var(--primary);
            text-transform: uppercase;
            letter-spacing: .4px;
            margin-bottom: .3rem;
        }

        .od-stepper-latest-status {
            font-size: .9rem;
            font-weight: 700;
            color: var(--dark);
            margin-bottom: .2rem;
        }

        .od-stepper-latest-meta {
            font-size: .78rem;
            color: var(--muted);
            display: flex;
            flex-wrap: wrap;
            gap: .5rem .875rem;
            margin-top: .3rem;
        }

        .od-stepper-latest-meta span {
            display: flex;
            align-items: center;
            gap: .25rem;
        }

        .od-stepper-latest-desc {
            font-size: .8125rem;
            color: var(--body);
            margin-top: .35rem;
            line-height: 1.5;
        }

        /* View all toggle */
        .od-stepper-all-toggle {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: .35rem;
            margin-top: .875rem;
            padding: .45rem;
            font-size: .8rem;
            font-weight: 600;
            color: var(--muted);
            cursor: pointer;
            border: 1.5px dashed var(--border);
            border-radius: var(--radius-sm);
            transition: var(--transition);
            background: none;
            width: 100%;
        }

        .od-stepper-all-toggle:hover {
            border-color: var(--primary);
            color: var(--primary);
            background: var(--primary-soft);
        }

        .od-stepper-all-toggle i {
            transition: transform .25s;
        }

        .od-stepper-all-toggle.open i {
            transform: rotate(180deg);
        }

        /* Full event list (hidden by default) */
        .od-stepper-all-list {
            display: none;
            margin-top: .75rem;
        }

        .od-stepper-all-list.visible {
            display: block;
        }

        .od-sal-item {
            display: grid;
            grid-template-columns: 90px 1fr;
            gap: .5rem .875rem;
            padding: .6rem 0;
            border-bottom: 1px solid var(--border);
            align-items: start;
        }

        .od-sal-item:last-child {
            border-bottom: none;
        }

        .od-sal-date {
            font-size: .72rem;
            font-family: var(--font-mono);
            color: var(--muted);
            padding-top: .1rem;
            line-height: 1.4;
        }

        .od-sal-status {
            font-size: .84rem;
            font-weight: 700;
            color: var(--dark);
            line-height: 1.3;
        }

        .od-sal-loc {
            font-size: .75rem;
            color: var(--primary);
            display: flex;
            align-items: center;
            gap: .2rem;
            margin-top: .15rem;
        }

        .od-sal-desc {
            font-size: .75rem;
            color: var(--muted);
            margin-top: .1rem;
            line-height: 1.4;
        }

        /* ── Print ───────────────────────────────── */
        @media print {
            .no-print {
                display: none !important;
            }
        }

        /* ── Responsive ──────────────────────────── */
        @media (max-width: 640px) {
            .od-header {
                flex-direction: column;
                align-items: flex-start;
            }

            .od-order-id {
                font-size: 1.375rem;
            }

            .od-meta-strip {
                gap: .5rem;
            }

            .od-meta-card {
                min-width: 100px;
            }
        }
    </style>

    <div class="od-wrap">

        {{-- ─── PAGE HEADER ─────────────────────────────────────────────────── --}}
        <div class="od-header no-print">
            <div class="od-header-left">
                <h1 class="od-order-id">Order <span>#{{ $order->id }}</span></h1>
                <p class="od-order-date">
                    <i class="bi bi-calendar3"></i>
                    Created {{ $order->created_at->format('d M Y, h:i A') }}
                </p>
            </div>
            <div class="od-header-actions">
                <button onclick="window.history.back()" class="btn-od no-print">
                    <i class="bi bi-arrow-left"></i> Back
                </button>
                @if (auth()->guard('admin')->user() && auth()->guard('admin')->user()->hasPermission('orders.cancel') && !in_array($order->diamond_status, ['r_order_cancelled', 'd_order_cancelled', 'j_order_cancelled']))
                    <button type="button" class="btn-od no-print"
                        onclick="openCancelModal({{ $order->id }}, '{{ addslashes($order->client_name ?? $order->client_details) }}')"
                        style="color: var(--danger); border-color: var(--danger);">
                        <i class="bi bi-x-circle"></i> Cancel Order
                    </button>
                @endif
                <a href="{{ route('orders.edit', $order) }}" class="btn-od btn-od-primary no-print">
                    <i class="bi bi-pencil"></i> Edit
                </a>
            </div>
        </div>

        {{-- ─── META STRIP ──────────────────────────────────────────────────── --}}
        <div class="od-meta-strip">

            <div class="od-meta-card">
                <span class="od-meta-label">Order Type</span>
                <span class="s-badge status-{{ $order->order_type }}">
                    {{ ucfirst(str_replace('_', ' ', $order->order_type)) }}
                </span>
            </div>

            @if($order->diamond_status)
                <div class="od-meta-card">
                    <span class="od-meta-label">Diamond Status</span>
                    <span class="s-badge status-{{ $order->diamond_status }}">
                        {{ ucfirst(str_replace('_', ' ', $order->diamond_status)) }}
                    </span>
                </div>
            @endif

            @if($order->note)
                <div class="od-meta-card">
                    <span class="od-meta-label">Priority</span>
                    <span class="s-badge status-{{ $order->note }}">
                        {{ ucfirst(str_replace('_', ' ', $order->note)) }}
                    </span>
                </div>
            @endif

            @if($order->company)
                <div class="od-meta-card">
                    <span class="od-meta-label">Company</span>
                    <span class="od-meta-value">{{ $order->company->name }}</span>
                </div>
            @endif

            @if($order->creator)
                <div class="od-meta-card">
                    <span class="od-meta-label">Created By</span>
                    <span class="od-meta-value">{{ $order->creator->name }}</span>
                </div>
            @endif

            @if($order->lastModifier)
                <div class="od-meta-card">
                    <span class="od-meta-label">Last Edited By</span>
                    <span class="od-meta-value">{{ $order->lastModifier->name }}</span>
                    <span class="od-meta-sub">{{ $order->updated_at->format('d M Y, h:i A') }}</span>
                </div>
            @endif

        </div>

        @if($order->cancel_reason)
            <div class="od-card mb-4" style="border-color: var(--danger);">
                <div class="od-card-head" style="background: var(--danger-soft); border-bottom-color: var(--danger);">
                    <h3 class="od-card-title" style="color: var(--danger);"><i
                            class="bi bi-exclamation-triangle-fill text-danger"></i> Cancellation Details</h3>
                </div>
                <div class="od-card-body">
                    <div class="od-detail-group">
                        <div class="od-detail-label" style="color: var(--danger);"><i class="bi bi-info-circle"></i> Reason
                        </div>
                        <p class="od-detail-text" style="color: var(--danger);">{{ $order->cancel_reason }}</p>
                        <div class="mt-2 text-muted" style="font-size: 0.8rem;">
                            Cancelled by: <strong>{{ $order->cancelledBy->name ?? 'System' }}</strong> on
                            {{ $order->cancelled_at ? $order->cancelled_at->format('d M Y, h:i A') : '' }}
                        </div>
                    </div>
                </div>
            </div>
        @endif

        {{-- ─── MAIN GRID ───────────────────────────────────────────────────── --}}
        <div class="od-grid">

            {{-- ── LEFT COLUMN ─────────────────────────────────────────── --}}
            <div class="od-col">

                {{-- Client Details --}}
                <div class="od-card">
                    <div class="od-card-head">
                        <h3 class="od-card-title"><i class="bi bi-person-circle"></i> Client Details</h3>
                    </div>
                    <div class="od-card-body">
                        <div class="od-info-table">
                            <div class="od-info-row">
                                <span class="od-info-key">Name</span>
                                <span
                                    class="od-info-val">{{ $order->display_client_name ?? ($order->client_details ?? 'N/A') }}</span>
                            </div>
                            <div class="od-info-row">
                                <span class="od-info-key">Email</span>
                                <span class="od-info-val">{{ $order->display_client_email ?? 'N/A' }}</span>
                            </div>
                            @if($order->display_client_mobile)
                                <div class="od-info-row">
                                    <span class="od-info-key">Mobile</span>
                                    <span class="od-info-val">{{ $order->display_client_mobile }}</span>
                                </div>
                            @endif
                            <div class="od-info-row">
                                <span class="od-info-key">Address</span>
                                <span class="od-info-val address">{{ $order->display_client_address ?? 'N/A' }}</span>
                            </div>
                            @if($order->display_client_tax_id)
                                <div class="od-info-row">
                                    <span
                                        class="od-info-key">{{ \App\Models\Order::TAX_ID_TYPES[$order->client_tax_id_type] ?? 'Tax ID' }}</span>
                                    <span class="od-info-val">{{ $order->display_client_tax_id }}</span>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>

                {{-- Product Details --}}
                <div class="od-card">
                    <div class="od-card-head">
                        <h3 class="od-card-title"><i class="bi bi-gem"></i> Product Details</h3>
                    </div>
                    <div class="od-card-body">

                        @if($order->jewellery_details)
                            <div class="od-detail-group">
                                <div class="od-detail-label"><i class="bi bi-ring"></i> Jewellery</div>
                                <p class="od-detail-text">{{ $order->jewellery_details }}</p>
                            </div>
                        @endif

                        @if($order->diamond_details)
                            <div class="od-detail-group">
                                <div class="od-detail-label"><i class="bi bi-diamond"></i> Diamond Description</div>
                                <p class="od-detail-text">{{ $order->diamond_details }}</p>
                            </div>
                        @endif

                        @php
                            $skus = is_array($order->diamond_skus) ? $order->diamond_skus : (!empty($order->diamond_sku) ? [$order->diamond_sku] : []);
                            $prices = is_array($order->diamond_prices) ? $order->diamond_prices : [];
                        @endphp

                        @if(!empty($skus))
                            <div class="od-detail-group">
                                <div class="od-detail-label"><i class="bi bi-upc-scan"></i> Diamond SKUs</div>
                                @foreach($skus as $sku)
                                    <div class="sku-row">
                                        <span class="sku-code">{{ $sku }}</span>
                                        @if(isset($prices[$sku]))
                                            <span class="sku-price">$ {{ number_format($prices[$sku], 2) }}</span>
                                        @endif
                                    </div>
                                @endforeach
                            </div>
                        @endif

                        @if($order->melee_diamond_id)
                            <div class="od-melee-box">
                                <div class="od-melee-title"><i class="bi bi-stars"></i> Melee Diamond Details</div>
                                <div class="od-melee-grid">
                                    <div class="od-melee-item" style="grid-column: span 2;">
                                        <small>Melee Item</small>
                                        <span>{{ $order->meleeDiamond->category->name ?? 'Melee' }} —
                                            {{ str_replace('-', ' ', $order->meleeDiamond->size_label ?? 'N/A') }}</span>
                                    </div>
                                    @if($order->melee_pieces)
                                        <div class="od-melee-item">
                                            <small>Pieces</small>
                                            <span>{{ $order->melee_pieces }} pcs</span>
                                        </div>
                                    @endif
                                    @if($order->melee_carat)
                                        <div class="od-melee-item">
                                            <small>Carat</small>
                                            <span>{{ number_format($order->melee_carat, 3) }} ct</span>
                                        </div>
                                    @endif
                                    @if($order->melee_price_per_ct)
                                        <div class="od-melee-item">
                                            <small>Price / ct</small>
                                            <span>$ {{ number_format($order->melee_price_per_ct, 2) }}</span>
                                        </div>
                                        <div class="od-melee-item">
                                            <small>Total Value</small>
                                            <span>$
                                                {{ number_format($order->melee_total_value ?? ($order->melee_carat * $order->melee_price_per_ct), 2) }}</span>
                                        </div>
                                    @endif
                                </div>
                            </div>
                        @endif

                    </div>
                </div>

                {{-- Specifications --}}
                @if($order->goldDetail || $order->ringSize || $order->settingType || $order->earringDetail)
                    <div class="od-card">
                        <div class="od-card-head">
                            <h3 class="od-card-title"><i class="bi bi-sliders"></i> Specifications</h3>
                        </div>
                        <div class="od-card-body">
                            <div class="od-specs">
                                @if($order->goldDetail)
                                    <div class="od-spec">
                                        <span class="od-spec-label">Metal Type</span>
                                        <span class="od-spec-val">{{ $order->goldDetail->name }}</span>
                                    </div>
                                @endif
                                @if($order->ringSize)
                                    <div class="od-spec">
                                        <span class="od-spec-label">Ring Size</span>
                                        <span class="od-spec-val">{{ $order->ringSize->name }}</span>
                                    </div>
                                @endif
                                @if($order->settingType)
                                    <div class="od-spec">
                                        <span class="od-spec-label">Setting Type</span>
                                        <span class="od-spec-val">{{ $order->settingType->name }}</span>
                                    </div>
                                @endif
                                @if($order->earringDetail)
                                    <div class="od-spec">
                                        <span class="od-spec-label">Earring Type</span>
                                        <span class="od-spec-val">{{ $order->earringDetail->name }}</span>
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>
                @endif

                {{-- Pricing --}}
                @if($order->gross_sell)
                    <div class="od-card">
                        <div class="od-card-head">
                            <h3 class="od-card-title"><i class="bi bi-currency-dollar"></i> Pricing</h3>
                        </div>
                        <div class="od-card-body">
                            <div class="od-price-hero">
                                <div class="od-price-hero-label">Gross Sell Amount</div>
                                <div class="od-price-hero-value">$ {{ number_format((float) $order->gross_sell, 2) }}</div>
                            </div>
                        </div>
                    </div>
                @endif

                {{-- Special Notes --}}
                @if($order->special_notes)
                    <div class="od-card">
                        <div class="od-card-head">
                            <h3 class="od-card-title"><i class="bi bi-journal-text"></i> Special Notes</h3>
                        </div>
                        <div class="od-card-body">
                            <p class="od-notes-text">{{ $order->special_notes }}</p>
                        </div>
                    </div>
                @endif

            </div>{{-- /left col --}}

            {{-- ── RIGHT COLUMN ────────────────────────────────────────── --}}
            <div class="od-col">

                {{-- Product Images --}}
                @php
                    $images = $order->images;
                    if (is_string($images)) {
                        $images = json_decode($images, true);
                    }
                    $images = is_array($images) ? $images : [];
                @endphp
                @if(!empty($images))
                    <div class="od-card">
                        <div class="od-card-head">
                            <h3 class="od-card-title">
                                <i class="bi bi-images"></i> Product Images
                                <span
                                    style="font-size:.72rem; background:var(--primary-soft); color:var(--primary); padding:.1rem .45rem; border-radius:100px; font-weight:700; margin-left:.25rem;">{{ count($images) }}</span>
                            </h3>
                        </div>
                        <div class="od-card-body">
                            <div class="od-img-grid">
                                @foreach($images as $index => $image)
                                    <div class="od-img-item"
                                        onclick="viewImage('{{ $image['url'] }}', '{{ addslashes($image['name'] ?? 'Image') }}')">
                                        <img src="{{ $image['url'] }}" alt="{{ $image['name'] ?? 'Image' }}" loading="lazy">
                                        <div class="od-img-overlay"><i class="bi bi-eye"></i></div>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    </div>
                @endif

                {{-- Documents --}}
                @php
                    $pdfs = $order->order_pdfs;
                    if (is_string($pdfs)) {
                        $pdfs = json_decode($pdfs, true);
                    }
                    $pdfs = is_array($pdfs) ? $pdfs : [];
                @endphp
                @if(!empty($pdfs))
                    <div class="od-card">
                        <div class="od-card-head">
                            <h3 class="od-card-title">
                                <i class="bi bi-file-pdf"></i> Documents
                                <span
                                    style="font-size:.72rem; background:#fee2e2; color:#dc2626; padding:.1rem .45rem; border-radius:100px; font-weight:700; margin-left:.25rem;">{{ count($pdfs) }}</span>
                            </h3>
                        </div>
                        <div class="od-card-body">
                            @foreach($pdfs as $pdf)
                                <div class="od-pdf-item">
                                    <div class="od-pdf-icon"><i class="bi bi-file-pdf-fill"></i></div>
                                    <div class="od-pdf-info">
                                        <p class="od-pdf-name">{{ $pdf['name'] ?? 'Document.pdf' }}</p>
                                        <span class="od-pdf-size">
                                            {{ isset($pdf['size']) ? number_format($pdf['size'] / (1024 * 1024), 2) . ' MB' : '' }}
                                        </span>
                                    </div>
                                    <div class="od-pdf-actions no-print">
                                        <button class="od-pdf-btn"
                                            onclick="viewPDF('{{ $pdf['url'] }}', '{{ addslashes($pdf['name'] ?? 'Document.pdf') }}')"
                                            title="View">
                                            <i class="bi bi-eye"></i>
                                        </button>
                                        <button class="od-pdf-btn"
                                            onclick="downloadPDF('{{ $pdf['url'] }}', '{{ addslashes($pdf['name'] ?? 'Document.pdf') }}')"
                                            title="Download">
                                            <i class="bi bi-download"></i>
                                        </button>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endif

                {{-- Shipping Information --}}
                @if($order->shipping_company_name || $order->tracking_number || $order->dispatch_date)
                    <div class="od-card">
                        <div class="od-card-head">
                            <h3 class="od-card-title"><i class="bi bi-truck"></i> Shipping Information</h3>
                            <div class="d-flex align-items-center gap-2 no-print">
                                @if($order->tracking_url)
                                    <a href="{{ $order->tracking_url }}" target="_blank" class="btn-od">
                                        <i class="bi bi-box-arrow-up-right"></i> Official Page
                                    </a>
                                @endif
                                @if($order->tracking_number && ($order->shipping_company_name || $order->tracking_url))
                                    <form action="{{ route('orders.sync-tracking', $order) }}" method="POST" class="m-0">
                                        @csrf
                                        <button type="submit" class="btn-od"
                                            onclick="this.innerHTML='<i class=\'bi bi-arrow-repeat spin\'></i> Syncing...'">
                                            <i class="bi bi-arrow-repeat"></i> Sync Status
                                        </button>
                                    </form>
                                @endif
                            </div>
                        </div>
                        <div class="od-card-body">
                            <div class="od-ship-grid">
                                @if($order->shipping_company_name)
                                    <div class="od-spec">
                                        <span class="od-spec-label">Company</span>
                                        <span class="od-spec-val">{{ $order->shipping_company_name }}</span>
                                    </div>
                                @endif
                                @if($order->tracking_number)
                                    <div class="od-spec">
                                        <span class="od-spec-label">Tracking #</span>
                                        <span class="od-spec-val"
                                            style="font-family: var(--font-mono); font-size:.8rem;">{{ $order->tracking_number }}</span>
                                    </div>
                                @endif
                                @if($order->dispatch_date)
                                    <div class="od-spec">
                                        <span class="od-spec-label">Dispatch Date</span>
                                        <span
                                            class="od-spec-val">{{ \Carbon\Carbon::parse($order->dispatch_date)->format('d M Y') }}</span>
                                    </div>
                                @endif
                                @if($order->tracking_status)
                                    <div class="od-spec">
                                        <span class="od-spec-label">Live Status</span>
                                        <span
                                            class="s-badge status-{{ strtolower(str_replace(' ', '_', $order->tracking_status)) }}">
                                            {{ $order->tracking_status }}
                                        </span>
                                    </div>
                                @endif
                            </div>

                            {{-- Tracking History — Horizontal Stepper --}}
                            @if(!empty($order->tracking_history) && is_array($order->tracking_history) && count($order->tracking_history) > 0)
                                @php
                                    $history = $order->tracking_history;
                                    $totalEvents = count($history);
                                    $latestEvent = $history[0] ?? [];

                                    // Define the 5 canonical milestone stages
                                    $milestones = [
                                        'dispatched' => ['label' => 'Dispatched', 'icon' => 'bi-box-seam', 'keywords' => ['dispatch', 'pickup', 'pickedup', 'collected', 'info']],
                                        'in_transit' => ['label' => 'In Transit', 'icon' => 'bi-airplane', 'keywords' => ['transit', 'departure', 'arrival', 'sorting', 'airport', 'processing', 'processed', 'facility', 'hub', 'on its way', 'departed']],
                                        'customs' => ['label' => 'Customs', 'icon' => 'bi-shield-check', 'keywords' => ['custom', 'clearance', 'cleared', 'held']],
                                        'out_delivery' => ['label' => 'Out for Del.', 'icon' => 'bi-truck', 'keywords' => ['out_for', 'out for', 'out for delivery', 'delivering']],
                                        'delivered' => ['label' => 'Delivered', 'icon' => 'bi-check-circle', 'keywords' => ['delivered', 'received by', 'complete']],
                                    ];

                                    // Determine which milestones are reached
                                    $reached = [];
                                    $chronologicalHistory = array_reverse($history);
                                    $milestoneKeys = array_keys($milestones);
                                    $highestIndexReached = 0;

                                    foreach ($chronologicalHistory as $ev) {
                                        $evStatus = strtolower($ev['status'] ?? '');
                                        $evDesc = strtolower($ev['description'] ?? '');
                                        $combined = $evStatus . ' ' . $evDesc;

                                        foreach ($milestones as $key => $m) {
                                            $keyIndex = array_search($key, $milestoneKeys);
                                            foreach ($m['keywords'] as $kw) {
                                                if (str_contains($combined, $kw)) {
                                                    $reached[$key] = $ev;
                                                    if ($keyIndex > $highestIndexReached) {
                                                        $highestIndexReached = $keyIndex;
                                                    }
                                                    break;
                                                }
                                            }
                                        }
                                    }

                                    // Fallback Protections
                                    // 1. Never show delivered if the global API status isn't actually Delivered.
                                    if (strtolower($order->tracking_status) === 'delivered') {
                                        $highestIndexReached = 4;
                                        if (!isset($reached['delivered']))
                                            $reached['delivered'] = $latestEvent;
                                    } else {
                                        // Erase premature delivered triggers from loose keywords
                                        if ($highestIndexReached === 4) {
                                            $highestIndexReached = 3;
                                            unset($reached['delivered']);
                                        }
                                    }

                                    // Always mark dispatched as reached if any history exists
                                    if (!isset($reached['dispatched']) && $totalEvents > 0) {
                                        $reached['dispatched'] = end($history);
                                    }

                                    // Force visual continuity (no grey gaps before active step)
                                    for ($i = 0; $i <= $highestIndexReached; $i++) {
                                        $k = $milestoneKeys[$i];
                                        if (!isset($reached[$k])) {
                                            $reached[$k] = null; // Mark structurally as done
                                        }
                                    }

                                    $activeStep = $milestoneKeys[$highestIndexReached] ?? 'dispatched';
                                @endphp

                                <div class="od-stepper-wrap">
                                    <div class="od-stepper-label-row">
                                        <span class="od-stepper-heading">
                                            <i class="bi bi-geo-alt-fill"></i> Journey
                                        </span>
                                        @if($order->last_tracker_sync)
                                            <span class="od-stepper-sync">
                                                <i class="bi bi-clock"></i> {{ $order->last_tracker_sync->diffForHumans() }}
                                            </span>
                                        @endif
                                    </div>

                                    {{-- Stepper rail --}}
                                    <div class="od-stepper">
                                        @foreach($milestones as $key => $m)
                                            @php
                                                $isDone = array_key_exists($key, $reached) && $key !== $activeStep;
                                                $isActive = $key === $activeStep;
                                                $stepClass = $isDone ? 'done' : ($isActive ? 'active' : '');
                                                $stepEvent = $reached[$key] ?? null;
                                            @endphp
                                            <div class="od-step {{ $stepClass }}">
                                                <div class="od-step-circle">
                                                    @if($isDone)
                                                        <i class="bi bi-check-lg"></i>
                                                    @elseif($isActive)
                                                        <i class="{{ $m['icon'] }}"></i>
                                                    @else
                                                        <i class="{{ $m['icon'] }}" style="opacity:.35;"></i>
                                                    @endif
                                                </div>
                                                <div class="od-step-body">
                                                    <span class="od-step-name" title="{{ $m['label'] }}">{{ $m['label'] }}</span>
                                                    @if($stepEvent)
                                                        <span class="od-step-date">
                                                            {{ !empty($stepEvent['date']) ? \Carbon\Carbon::parse($stepEvent['date'])->format('d M') : '' }}
                                                        </span>
                                                    @endif
                                                </div>
                                            </div>
                                        @endforeach
                                    </div>

                                    {{-- Latest event detail --}}
                                    <div class="od-stepper-latest">
                                        <div class="od-stepper-latest-label">Latest Update</div>
                                        <div class="od-stepper-latest-status">{{ $latestEvent['status'] ?? 'No status' }}</div>
                                        <div class="od-stepper-latest-meta">
                                            @if(!empty($latestEvent['date']))
                                                <span><i class="bi bi-calendar3"></i> {{ $latestEvent['date'] }}</span>
                                            @endif
                                            @if(!empty($latestEvent['location']))
                                                <span><i class="bi bi-geo-alt"></i> {{ $latestEvent['location'] }}</span>
                                            @endif
                                        </div>
                                        @if(!empty($latestEvent['description']))
                                            <div class="od-stepper-latest-desc">{{ $latestEvent['description'] }}</div>
                                        @endif
                                    </div>

                                    {{-- View all events toggle --}}
                                    <button class="od-stepper-all-toggle" onclick="toggleAllEvents(this)" type="button"
                                        data-count="View all {{ $totalEvents }} events">
                                        <i class="bi bi-list-ul"></i>
                                        <span class="od-toggle-text">View all {{ $totalEvents }} events</span>
                                        <i class="bi bi-chevron-down"></i>
                                    </button>

                                    <div class="od-stepper-all-list">
                                        @foreach($history as $ev)
                                            <div class="od-sal-item">
                                                <span class="od-sal-date">{{ $ev['date'] ?? '' }}</span>
                                                <div>
                                                    <div class="od-sal-status">{{ $ev['status'] ?? '' }}</div>
                                                    @if(!empty($ev['location']))
                                                        <div class="od-sal-loc"><i class="bi bi-geo-alt-fill"></i> {{ $ev['location'] }}
                                                        </div>
                                                    @endif
                                                    @if(!empty($ev['description']))
                                                        <div class="od-sal-desc">{{ $ev['description'] }}</div>
                                                    @endif
                                                </div>
                                            </div>
                                        @endforeach
                                    </div>

                                </div>
                            @endif

                        </div>
                    </div>
                @endif

                {{-- Edit History (Superadmin) --}}
                @if(Auth::guard('admin')->user()?->is_super && $editHistory->count() > 0)
                    <div class="od-card">
                        <div class="od-card-head">
                            <h3 class="od-card-title">
                                <i class="bi bi-clock-history"></i> Edit History
                                <span
                                    style="font-size:.72rem; background:var(--primary-soft); color:var(--primary); padding:.1rem .45rem; border-radius:100px; font-weight:700; margin-left:.25rem;">{{ $editHistory->count() }}</span>
                            </h3>
                        </div>
                        <div class="od-card-body" style="padding: .875rem;">
                            <div class="od-edit-tl">
                                @foreach($editHistory as $log)
                                    <div class="od-edit-item">
                                        <div class="od-edit-header">
                                            <span class="od-edit-admin">
                                                <i class="bi bi-person-fill" style="color:var(--primary);"></i>
                                                {{ $log->admin->name ?? 'Unknown Admin' }}
                                            </span>
                                            <span class="od-edit-time">
                                                <i class="bi bi-calendar3"></i>
                                                {{ $log->created_at->format('d M Y, h:i A') }}
                                            </span>
                                            <span class="od-edit-ago">{{ $log->created_at->diffForHumans() }}</span>
                                        </div>
                                        @if(!empty($log->old_values) || !empty($log->new_values))
                                            <div class="timeline-changes">
                                                <button type="button" class="changes-toggle"
                                                    onclick="this.parentElement.classList.toggle('expanded')">
                                                    <i class="bi bi-list-check"></i>
                                                    {{ count($log->new_values ?? []) }} field(s) changed
                                                    <i class="bi bi-chevron-down toggle-icon"></i>
                                                </button>
                                                <div class="changes-detail">
                                                    <table class="changes-table">
                                                        <thead>
                                                            <tr>
                                                                <th>Field</th>
                                                                <th>Old Value</th>
                                                                <th>New Value</th>
                                                            </tr>
                                                        </thead>
                                                        <tbody>
                                                            @foreach(($log->new_values ?? []) as $field => $newVal)
                                                                <tr>
                                                                    <td class="field-name">{{ $field }}</td>
                                                                    <td><span
                                                                            class="val-badge val-old">{{ Str::limit($log->old_values[$field] ?? '—', 80) }}</span>
                                                                    </td>
                                                                    <td><span
                                                                            class="val-badge val-new">{{ Str::limit($newVal ?? '—', 80) }}</span>
                                                                    </td>
                                                                </tr>
                                                            @endforeach
                                                        </tbody>
                                                    </table>
                                                </div>
                                            </div>
                                        @endif
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    </div>
                @endif

            </div>{{-- /right col --}}

        </div>{{-- /grid --}}

    </div>{{-- /wrap --}}

    {{-- ─── IMAGE MODAL ──────────────────────────────────────────────────────── --}}
    <div id="imageModal" class="od-modal no-print" onclick="closeImageModal()">
        <div class="od-modal-box" style="max-width:1000px; height:auto; max-height:92vh;" onclick="event.stopPropagation()">
            <div class="od-modal-head">
                <h3 id="imageModalTitle">Image Viewer</h3>
                <button class="od-modal-close" onclick="closeImageModal()"><i class="bi bi-x-lg"></i></button>
            </div>
            <div class="od-modal-body" style="background:#000; display:flex; align-items:center; justify-content:center;">
                <img id="imageViewer" src="" alt="Image" style="max-width:100%; max-height:80vh; object-fit:contain;">
            </div>
        </div>
    </div>

    {{-- ─── PDF MODAL ────────────────────────────────────────────────────────── --}}
    <div id="pdfModal" class="od-modal no-print" onclick="closePDFModal()">
        <div class="od-modal-box" onclick="event.stopPropagation()">
            <div class="od-modal-head">
                <h3 id="pdfModalTitle">Document Viewer</h3>
                <button class="od-modal-close" onclick="closePDFModal()"><i class="bi bi-x-lg"></i></button>
            </div>
            <div class="od-modal-body">
                <iframe id="pdfViewer" src="" frameborder="0"
                    style="width:100%; height:100%; min-height:0; flex:1;"></iframe>
            </div>
        </div>
    </div>

    <script>
        /* ── Image viewer ─────────────────────────── */
        function viewImage(url, name) {
            document.getElementById('imageViewer').src = url;
            document.getElementById('imageModalTitle').textContent = name || 'Image Viewer';
            document.getElementById('imageModal').classList.add('active');
            document.body.style.overflow = 'hidden';
        }
        function closeImageModal() {
            document.getElementById('imageModal').classList.remove('active');
            document.getElementById('imageViewer').src = '';
            document.body.style.overflow = '';
        }

        /* ── PDF viewer ───────────────────────────── */
        function viewPDF(url, name) {
            const viewerUrl = `https://docs.google.com/viewer?url=${encodeURIComponent(url)}&embedded=true`;
            document.getElementById('pdfViewer').src = viewerUrl;
            document.getElementById('pdfModalTitle').textContent = name || 'Document Viewer';
            document.getElementById('pdfModal').classList.add('active');
            document.body.style.overflow = 'hidden';
        }
        function closePDFModal() {
            document.getElementById('pdfModal').classList.remove('active');
            document.getElementById('pdfViewer').src = '';
            document.body.style.overflow = '';
        }

        /* ── PDF download ─────────────────────────── */
        async function downloadPDF(url, filename) {
            const btn = event.target.closest('button');
            const orig = btn.innerHTML;
            btn.innerHTML = '<i class="bi bi-hourglass-split"></i>';
            btn.disabled = true;
            try {
                const res = await fetch(url);
                const blob = await res.blob();
                const a = document.createElement('a');
                a.href = URL.createObjectURL(blob);
                a.download = filename;
                document.body.appendChild(a);
                a.click();
                document.body.removeChild(a);
                URL.revokeObjectURL(a.href);
            } catch (e) {
                alert('Download failed. Please try again.');
            } finally {
                btn.innerHTML = orig;
                btn.disabled = false;
            }
        }

        /* ── Keyboard shortcuts ───────────────────── */
        document.addEventListener('keydown', e => {
            if (e.key === 'Escape') { closeImageModal(); closePDFModal(); }
        });

        /* ── All events toggle ───────────────────── */
        function toggleAllEvents(btn) {
            const list = btn.nextElementSibling;
            const isOpen = list.classList.contains('visible');
            if (isOpen) {
                list.classList.remove('visible');
                btn.classList.remove('open');
                btn.querySelector('.od-toggle-text').textContent = btn.getAttribute('data-count');
            } else {
                list.classList.add('visible');
                btn.classList.add('open');
                btn.querySelector('.od-toggle-text').textContent = 'Hide events';
            }
        }
        /* ── Cancel Order Modal ──────────────────── */
        function openCancelModal(orderId, clientName) {
            document.getElementById('cancelOrderId').value = orderId;
            document.getElementById('cancelClientName').textContent = clientName;
            document.getElementById('cancelOrderForm').action = `/admin/orders/${orderId}/cancel`;
            document.getElementById('cancelModalContainer').classList.add('active');
            document.body.style.overflow = 'hidden';
        }
        function closeCancelModal() {
            document.getElementById('cancelModalContainer').classList.remove('active');
            document.body.style.overflow = '';
        }
    </script>

    <!-- Cancel Order Modal (Custom) -->
    <div class="od-modal" id="cancelModalContainer" style="z-index: 10000;">
        <div class="od-modal-box" style="height: auto; width: 90vw; max-width: 500px;">
            <form id="cancelOrderForm" method="POST">
                @csrf
                <input type="hidden" id="cancelOrderId" name="order_id" value="">

                <div class="od-modal-head" style="background: var(--danger); color: white; border-bottom: none;">
                    <h3 style="color: white; display: flex; align-items: center; gap: .5rem;"><i class="bi bi-x-circle"></i>
                        Cancel Order</h3>
                    <button type="button" class="od-modal-close" onclick="closeCancelModal()"
                        style="color: white; border-color: rgba(255,255,255,0.3); background: transparent;">
                        <i class="bi bi-x"></i>
                    </button>
                </div>

                <div class="od-modal-body" style="padding: 1.5rem; overflow-y: auto;">
                    <p style="margin-bottom: 1rem;">Are you sure you want to cancel the order for <strong
                            id="cancelClientName"></strong>?</p>

                    <div
                        style="background: #fee2e2; border: 1px solid #fca5a5; color: #b91c1c; padding: .75rem; border-radius: var(--radius-sm); font-size: .875rem; margin-bottom: 1.5rem; display: flex; gap: .5rem; align-items: flex-start;">
                        <i class="bi bi-info-circle" style="margin-top: .15rem;"></i>
                        <span>This will return the associated diamond SKU(s) and melee stock back to inventory.</span>
                    </div>

                    <div style="display: flex; flex-direction: column; gap: .5rem;">
                        <label for="cancel_reason" style="font-size: .875rem; font-weight: 600; color: var(--dark);">Reason
                            for Cancellation
                            <span style="color: var(--danger);">*</span></label>
                        <textarea name="cancel_reason" id="cancel_reason" rows="3" required
                            style="width: 100%; border: 1.5px solid var(--border); border-radius: var(--radius-sm); padding: .75rem; font-family: var(--font-sans); font-size: .9375rem;"
                            placeholder="Please detail why the order is being cancelled..."></textarea>
                    </div>
                </div>

                <div
                    style="padding: 1rem 1.5rem; background: var(--bg); border-top: 1px solid var(--border); display: flex; justify-content: flex-end; gap: .75rem;">
                    <button type="button" class="btn-od" onclick="closeCancelModal()">Go Back</button>
                    <button type="submit" class="btn-od"
                        style="background: var(--danger); border-color: var(--danger); color: white;">Confirm
                        Cancellation</button>
                </div>
            </form>
        </div>
    </div>

@endsection