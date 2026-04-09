@extends('layouts.admin')

@section('title', 'Attributes Hub')

@section('content')
    <style>
        .attribute-hub-page {
            padding: 2rem;
            min-height: 100vh;
            background:
                radial-gradient(circle at top left, rgba(99, 102, 241, 0.14), transparent 28%),
                radial-gradient(circle at top right, rgba(14, 165, 233, 0.08), transparent 24%),
                linear-gradient(180deg, #f8fafc 0%, #eef2ff 100%);
        }

        .attribute-hub-shell {
            max-width: 1500px;
            margin: 0 auto;
        }

        .attribute-hub-header {
            background: rgba(255, 255, 255, 0.88);
            backdrop-filter: blur(12px);
            border: 1px solid rgba(226, 232, 240, 0.9);
            border-radius: 24px;
            padding: 1.75rem 2rem;
            box-shadow: 0 20px 60px rgba(15, 23, 42, 0.08);
            margin-bottom: 1.5rem;
        }

        .attribute-hub-breadcrumb {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            flex-wrap: wrap;
            color: #64748b;
            font-size: 0.875rem;
            margin-bottom: 1rem;
        }

        .attribute-hub-breadcrumb a {
            color: #64748b;
            text-decoration: none;
        }

        .attribute-hub-breadcrumb a:hover {
            color: #4f46e5;
        }

        .attribute-hub-title-row {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            gap: 1.5rem;
            flex-wrap: wrap;
        }

        .attribute-hub-title {
            margin: 0;
            display: flex;
            align-items: center;
            gap: 0.9rem;
            font-size: clamp(1.8rem, 2vw, 2.5rem);
            font-weight: 800;
            color: #0f172a;
        }

        .attribute-hub-title i {
            color: #4f46e5;
        }

        .attribute-hub-subtitle {
            margin: 0.5rem 0 0;
            color: #475569;
            font-size: 0.98rem;
            max-width: 72ch;
        }

        .attribute-hub-pills {
            display: flex;
            flex-wrap: wrap;
            gap: 0.75rem;
        }

        .attribute-hub-pill {
            display: inline-flex;
            align-items: center;
            gap: 0.4rem;
            padding: 0.6rem 0.9rem;
            border-radius: 999px;
            background: rgba(99, 102, 241, 0.1);
            color: #4338ca;
            font-weight: 700;
            font-size: 0.875rem;
            white-space: nowrap;
        }

        .attribute-hub-grid {
            display: grid;
            grid-template-columns: 320px minmax(0, 1fr);
            gap: 1.5rem;
        }

        .attribute-hub-sidebar,
        .attribute-hub-main,
        .attribute-hub-card,
        .attribute-hub-empty {
            background: rgba(255, 255, 255, 0.92);
            border: 1px solid rgba(226, 232, 240, 0.9);
            border-radius: 24px;
            box-shadow: 0 18px 50px rgba(15, 23, 42, 0.08);
        }

        .attribute-hub-sidebar {
            padding: 1.25rem;
            align-self: start;
            position: sticky;
            top: 1.25rem;
        }

        .attribute-hub-sidebar h2 {
            margin: 0 0 0.35rem;
            font-size: 1rem;
            font-weight: 800;
            color: #0f172a;
        }

        .attribute-hub-sidebar p {
            margin: 0 0 1rem;
            color: #64748b;
            font-size: 0.875rem;
        }

        .attribute-hub-list {
            display: grid;
            gap: 0.75rem;
        }

        .attribute-hub-item {
            display: grid;
            grid-template-columns: auto minmax(0, 1fr) auto;
            gap: 0.9rem;
            align-items: center;
            width: 100%;
            text-decoration: none;
            color: inherit;
            padding: 0.95rem 1rem;
            border-radius: 18px;
            border: 1px solid transparent;
            background: #f8fafc;
            transition: all 0.2s ease;
        }

        .attribute-hub-item:hover {
            transform: translateY(-1px);
            border-color: rgba(99, 102, 241, 0.22);
            background: #eef2ff;
        }

        .attribute-hub-item:focus-visible,
        .attribute-btn:focus-visible,
        .attribute-icon-btn:focus-visible,
        .attribute-name-button:focus-visible {
            outline: 3px solid rgba(79, 70, 229, 0.28);
            outline-offset: 2px;
        }

        .attribute-hub-item.active {
            background: #eef2ff;
            color: #1e3a8a;
            border-color: #c7d2fe;
            box-shadow: none;
        }

        .attribute-hub-icon {
            width: 42px;
            height: 42px;
            border-radius: 14px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            background: rgba(99, 102, 241, 0.12);
            color: #4f46e5;
            font-size: 1.15rem;
            flex-shrink: 0;
        }

        .attribute-hub-item.active .attribute-hub-icon {
            background: #fff;
            color: #4f46e5;
        }

        .attribute-hub-item-body {
            min-width: 0;
        }

        .attribute-hub-item-title {
            margin: 0;
            font-size: 1rem;
            font-weight: 800;
            line-height: 1.2;
        }

        .attribute-hub-item-subtitle {
            margin: 0.35rem 0 0;
            color: #64748b;
            font-size: 0.84rem;
            line-height: 1.4;
        }

        .attribute-hub-item.active .attribute-hub-item-subtitle {
            color: #475569;
        }

        .attribute-hub-count {
            min-width: 42px;
            padding: 0.45rem 0.7rem;
            border-radius: 999px;
            background: rgba(99, 102, 241, 0.12);
            color: #4338ca;
            text-align: center;
            font-size: 0.82rem;
            font-weight: 800;
        }

        .attribute-hub-item.active .attribute-hub-count {
            background: #fff;
            color: #4f46e5;
        }

        .attribute-hub-main {
            padding: 1.5rem;
        }

        .attribute-hub-stats {
            display: grid;
            grid-template-columns: repeat(3, minmax(0, 1fr));
            gap: 1rem;
            margin-bottom: 1.25rem;
        }

        .attribute-hub-stat {
            padding: 1rem 1.1rem;
            border-radius: 18px;
            background: linear-gradient(180deg, #ffffff 0%, #f8fafc 100%);
            border: 1px solid #e2e8f0;
        }

        .attribute-hub-stat label {
            display: block;
            margin-bottom: 0.35rem;
            color: #64748b;
            font-size: 0.78rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.06em;
        }

        .attribute-hub-stat strong {
            font-size: 1.55rem;
            line-height: 1.1;
            color: #0f172a;
        }

        .attribute-hub-card {
            padding: 1.5rem;
        }

        .attribute-hub-card-head {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            gap: 1rem;
            flex-wrap: wrap;
            margin-bottom: 1.25rem;
        }

        .attribute-hub-eyebrow {
            margin: 0 0 0.35rem;
            color: #4f46e5;
            font-size: 0.8rem;
            font-weight: 800;
            text-transform: uppercase;
            letter-spacing: 0.08em;
        }

        .attribute-hub-card-title {
            margin: 0;
            font-size: 1.7rem;
            font-weight: 800;
            color: #0f172a;
        }

        .attribute-hub-card-text {
            margin: 0.45rem 0 0;
            color: #475569;
            max-width: 65ch;
        }

        .attribute-hub-badge {
            display: inline-flex;
            align-items: center;
            gap: 0.45rem;
            padding: 0.7rem 0.95rem;
            border-radius: 999px;
            background: rgba(16, 185, 129, 0.1);
            color: #059669;
            font-weight: 800;
            white-space: nowrap;
        }

        .attribute-hub-meta {
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 1rem;
            margin: 1rem 0 1.25rem;
        }

        .attribute-hub-meta-box {
            padding: 1rem;
            border-radius: 18px;
            background: #f8fafc;
            border: 1px solid #e2e8f0;
        }

        .attribute-hub-meta-box span {
            display: block;
            margin-bottom: 0.35rem;
            color: #64748b;
            font-size: 0.8rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.06em;
        }

        .attribute-hub-meta-box strong {
            color: #0f172a;
            font-size: 1rem;
        }

        .attribute-hub-chips {
            display: flex;
            flex-wrap: wrap;
            gap: 0.6rem;
        }

        .attribute-hub-chip {
            display: inline-flex;
            align-items: center;
            gap: 0.4rem;
            padding: 0.6rem 0.85rem;
            border-radius: 999px;
            background: rgba(99, 102, 241, 0.09);
            color: #4338ca;
            font-size: 0.88rem;
            font-weight: 700;
        }

        .attribute-hub-actions {
            display: flex;
            gap: 0.8rem;
            flex-wrap: wrap;
            margin-top: 1.25rem;
        }

        .attribute-hub-btn {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            padding: 0.85rem 1.1rem;
            border-radius: 14px;
            text-decoration: none;
            font-weight: 800;
            transition: all 0.2s ease;
        }

        .attribute-hub-btn-primary {
            background: linear-gradient(135deg, #6366f1 0%, #4f46e5 100%);
            color: white;
            box-shadow: 0 14px 32px rgba(79, 70, 229, 0.24);
        }

        .attribute-hub-btn-primary:hover {
            transform: translateY(-1px);
            color: white;
        }

        .attribute-hub-btn-secondary {
            background: white;
            color: #334155;
            border: 1px solid #cbd5e1;
        }

        .attribute-hub-btn-secondary:hover {
            transform: translateY(-1px);
            color: #4f46e5;
            border-color: #4f46e5;
        }

        .attribute-hub-note {
            margin-top: 1.25rem;
            padding: 1rem 1.1rem;
            border-radius: 18px;
            background: linear-gradient(135deg, rgba(14, 165, 233, 0.08), rgba(99, 102, 241, 0.08));
            color: #334155;
            border: 1px solid rgba(99, 102, 241, 0.12);
            line-height: 1.5;
        }

        .attribute-hub-empty {
            padding: 2.5rem 1.5rem;
            text-align: center;
        }

        .attribute-hub-empty-icon {
            width: 72px;
            height: 72px;
            margin: 0 auto 1rem;
            border-radius: 22px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            background: rgba(99, 102, 241, 0.1);
            color: #4f46e5;
            font-size: 2rem;
        }

        .attribute-hub-empty h3 {
            margin: 0 0 0.45rem;
            color: #0f172a;
            font-size: 1.3rem;
        }

        .attribute-hub-empty p {
            margin: 0;
            color: #64748b;
        }

        @media (max-width: 1100px) {
            .attribute-hub-grid {
                grid-template-columns: 1fr;
            }

            .attribute-hub-sidebar {
                position: static;
            }
        }

        @media (max-width: 768px) {
            .attribute-hub-page {
                padding: 1rem;
            }

            .attribute-hub-header,
            .attribute-hub-sidebar,
            .attribute-hub-main {
                padding: 1.1rem;
                border-radius: 20px;
            }

            .attribute-hub-stats,
            .attribute-hub-meta {
                grid-template-columns: 1fr;
            }

            .attribute-hub-item {
                grid-template-columns: auto minmax(0, 1fr);
            }

            .attribute-hub-count {
                grid-column: 2;
                justify-self: start;
            }
        }
    </style>

    <div class="attribute-hub-page" id="attributeHubApp" data-index-url="{{ route('attributes.index') }}"
        data-fragment-url="{{ route('attributes.fragment') }}">
        <div class="attribute-hub-shell">
            <div class="attribute-hub-header">
                <div class="attribute-hub-breadcrumb">
                    <a href="{{ route('admin.dashboard') }}">
                        <i class="bi bi-house-door"></i> Dashboard
                    </a>
                    <i class="bi bi-chevron-right"></i>
                    <span>Attributes Hub</span>
                </div>

                <div class="attribute-hub-title-row">
                    <div>
                        <h1 class="attribute-hub-title">
                            <i class="bi bi-grid-1x2-fill"></i>
                            Attributes Hub
                        </h1>
                        <p class="attribute-hub-subtitle">
                            Manage all reusable stock attributes from one place. Each module still keeps its own
                            table, permissions, and FK relations, but the client now uses one unified entry point.
                        </p>
                    </div>

                    <div class="attribute-hub-pills">
                        <span class="attribute-hub-pill">
                            <i class="bi bi-boxes"></i> {{ $moduleCount }} Modules
                        </span>
                        <span class="attribute-hub-pill">
                            <i class="bi bi-database"></i> {{ $totalRecords }} Records
                        </span>
                        <span class="attribute-hub-pill">
                            <i class="bi bi-shield-check"></i> Separate Tables
                        </span>
                    </div>
                </div>
            </div>

            @if ($modules->isEmpty())
                <div class="attribute-hub-empty">
                    <div class="attribute-hub-empty-icon">
                        <i class="bi bi-lock"></i>
                    </div>
                    <h3>No attribute modules available</h3>
                    <p>Your current admin account does not have access to any attribute masters.</p>
                </div>
            @else
                <div class="attribute-hub-grid">
                    <aside class="attribute-hub-sidebar">
                        <h2>Categories</h2>
                        <p>Pick a module to open its list and create screens.</p>

                        <div class="attribute-hub-list">
                            @foreach ($modules as $module)
                                <button type="button"
                                    class="attribute-hub-item {{ $module['is_selected'] ? 'active' : '' }}"
                                    data-hub-module="{{ $module['key'] }}">
                                    <span class="attribute-hub-icon">
                                        <i class="bi {{ $module['icon'] }}"></i>
                                    </span>

                                    <span class="attribute-hub-item-body">
                                        <strong class="attribute-hub-item-title">{{ $module['label'] }}</strong>
                                        <span class="attribute-hub-item-subtitle">
                                            Shared CRUD template with dedicated table and FK usage.
                                        </span>
                                    </span>

                                    <span class="attribute-hub-count">{{ $module['count'] }}</span>
                                </button>
                            @endforeach
                        </div>
                    </aside>

                    <main class="attribute-hub-main">
                        <div class="attribute-workspace-alert" id="attributeHubAlert" aria-live="polite"></div>
                        <div class="attribute-workspace-panel" id="attributeHubWorkspace">
                            @if ($workspace && $selectedModule)
                                @include('attributes.partials.workspace', $workspace)
                            @else
                                <div class="attribute-workspace-empty">
                                    <div class="attribute-workspace-empty-icon">
                                        <i class="bi bi-grid"></i>
                                    </div>
                                    <h3>Select a module</h3>
                                    <p>Choose one of the attribute masters from the left sidebar to load its list.</p>
                                </div>
                            @endif
                        </div>
                    </main>
                </div>
            @endif
        </div>
    </div>
    <style>
        .attribute-hub-page {
            padding: 2rem;
            min-height: 100vh;
            background: #f8fafc;
        }

        .attribute-hub-shell {
            width: 100%;
            max-width: 1800px;
            margin: 0 auto;
        }

        .attribute-hub-header,
        .attribute-hub-sidebar,
        .attribute-hub-card,
        .attribute-hub-empty {
            background: white;
            border-radius: 16px;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.05);
            border: 1px solid #e2e8f0;
        }

        .attribute-hub-header {
            padding: 2rem;
            margin-bottom: 2rem;
        }

        .attribute-hub-breadcrumb {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            flex-wrap: wrap;
            color: #64748b;
            font-size: 0.875rem;
            margin-bottom: 1rem;
        }

        .attribute-hub-breadcrumb a {
            color: #64748b;
            text-decoration: none;
        }

        .attribute-hub-breadcrumb a:hover {
            color: #4f46e5;
        }

        .attribute-hub-title {
            margin: 0;
            display: flex;
            align-items: center;
            gap: 0.75rem;
            font-size: 1.75rem;
            font-weight: 700;
            color: #1e293b;
        }

        .attribute-hub-title i {
            color: #6366f1;
        }

        .attribute-hub-subtitle {
            color: #64748b;
            margin: 0.5rem 0 0;
            font-size: 0.95rem;
        }

        .attribute-hub-pills {
            display: flex;
            flex-wrap: wrap;
            gap: 0.75rem;
        }

        .attribute-hub-pill {
            display: inline-flex;
            align-items: center;
            gap: 0.4rem;
            padding: 0.65rem 0.9rem;
            border-radius: 999px;
            background: white;
            color: #334155;
            border: 1px solid #e2e8f0;
            font-weight: 700;
            font-size: 0.875rem;
        }

        .attribute-hub-grid {
            display: grid;
            grid-template-columns: 320px minmax(0, 1fr);
            gap: 1.5rem;
        }

        .attribute-hub-sidebar {
            padding: 1.25rem;
            align-self: start;
            position: sticky;
            top: 1.25rem;
        }

        .attribute-hub-sidebar h2 {
            margin: 0 0 0.35rem;
            font-size: 1rem;
            font-weight: 800;
            color: #0f172a;
        }

        .attribute-hub-sidebar p {
            margin: 0 0 1rem;
            color: #64748b;
            font-size: 0.875rem;
        }

        .attribute-hub-list {
            display: grid;
            gap: 0.75rem;
        }

        .attribute-hub-item {
            display: grid;
            grid-template-columns: auto minmax(0, 1fr) auto;
            gap: 0.9rem;
            align-items: center;
            width: 100%;
            text-decoration: none;
            color: inherit;
            padding: 0.95rem 1rem;
            border-radius: 14px;
            border: 1px solid #e2e8f0;
            border-left: 4px solid transparent;
            background: white;
            cursor: pointer;
            font: inherit;
            transition: all 0.2s ease;
        }

        .attribute-hub-item:hover {
            transform: translateY(-2px);
            border-left-color: #6366f1;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.08);
        }

        .attribute-hub-item.active {
            background: #eef2ff;
            color: #1e3a8a;
            border-color: #c7d2fe;
            box-shadow: none;
        }

        .attribute-hub-icon {
            width: 48px;
            height: 48px;
            border-radius: 12px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            background: rgba(99, 102, 241, 0.1);
            color: #6366f1;
            font-size: 1.15rem;
            flex-shrink: 0;
        }

        .attribute-hub-item.active .attribute-hub-icon {
            background: #fff;
            color: #4f46e5;
        }

        .attribute-hub-item-title {
            margin: 0;
            font-size: 1rem;
            font-weight: 800;
            line-height: 1.2;
        }

        .attribute-hub-item-subtitle {
            margin: 0.35rem 0 0;
            color: #64748b;
            font-size: 0.84rem;
            line-height: 1.4;
        }

        .attribute-hub-item.active .attribute-hub-item-subtitle {
            color: #475569;
        }

        .attribute-hub-count {
            min-width: 42px;
            padding: 0.45rem 0.7rem;
            border-radius: 999px;
            background: rgba(99, 102, 241, 0.1);
            color: #4f46e5;
            text-align: center;
            font-size: 0.82rem;
            font-weight: 800;
        }

        .attribute-hub-item.active .attribute-hub-count {
            background: #fff;
            color: #4f46e5;
        }

        .attribute-hub-main {
            min-width: 0;
        }

        .attribute-hub-stats {
            display: grid;
            grid-template-columns: repeat(3, minmax(0, 1fr));
            gap: 1rem;
            margin-bottom: 1.5rem;
        }

        .attribute-hub-stat {
            padding: 1rem 1.1rem;
            border-radius: 16px;
            background: white;
            border: 1px solid #e2e8f0;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.05);
        }

        .attribute-hub-stat label {
            display: block;
            margin-bottom: 0.35rem;
            color: #64748b;
            font-size: 0.78rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.06em;
        }

        .attribute-hub-stat strong {
            font-size: 1.5rem;
            line-height: 1.1;
            color: #0f172a;
        }

        .attribute-hub-card {
            padding: 1.5rem;
        }

        .attribute-hub-card-head {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            gap: 1rem;
            flex-wrap: wrap;
            margin-bottom: 1.25rem;
        }

        .attribute-hub-eyebrow {
            margin: 0 0 0.35rem;
            color: #4f46e5;
            font-size: 0.8rem;
            font-weight: 800;
            text-transform: uppercase;
            letter-spacing: 0.08em;
        }

        .attribute-hub-card-title {
            margin: 0;
            font-size: 1.5rem;
            font-weight: 800;
            color: #0f172a;
        }

        .attribute-hub-card-text {
            margin: 0.45rem 0 0;
            color: #475569;
        }

        .attribute-hub-badge,
        .attribute-hub-chip {
            display: inline-flex;
            align-items: center;
            gap: 0.45rem;
            border-radius: 999px;
            font-weight: 700;
        }

        .attribute-hub-badge {
            padding: 0.65rem 0.9rem;
            background: rgba(16, 185, 129, 0.1);
            color: #059669;
            white-space: nowrap;
        }

        .attribute-hub-meta {
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 1rem;
            margin: 1rem 0 1.25rem;
        }

        .attribute-hub-meta-box {
            padding: 1rem;
            border-radius: 14px;
            background: #f8fafc;
            border: 1px solid #e2e8f0;
        }

        .attribute-hub-meta-box span {
            display: block;
            margin-bottom: 0.35rem;
            color: #64748b;
            font-size: 0.8rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.06em;
        }

        .attribute-hub-meta-box strong {
            color: #0f172a;
            font-size: 1rem;
        }

        .attribute-hub-chips {
            display: flex;
            flex-wrap: wrap;
            gap: 0.6rem;
        }

        .attribute-hub-chip {
            padding: 0.55rem 0.8rem;
            background: white;
            color: #334155;
            border: 1px solid #e2e8f0;
        }

        .attribute-hub-actions {
            display: flex;
            gap: 0.75rem;
            flex-wrap: wrap;
            margin-top: 1.25rem;
        }

        .attribute-hub-btn {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            padding: 0.8rem 1.15rem;
            border-radius: 10px;
            text-decoration: none;
            font-weight: 700;
            transition: all 0.2s ease;
        }

        .attribute-hub-btn-primary {
            background: linear-gradient(135deg, #6366f1, #4f46e5);
            color: white;
            box-shadow: 0 4px 12px rgba(99, 102, 241, 0.22);
        }

        .attribute-hub-btn-primary:hover {
            transform: translateY(-2px);
            color: white;
        }

        .attribute-hub-btn-secondary {
            background: white;
            color: #334155;
            border: 2px solid #e2e8f0;
        }

        .attribute-hub-btn-secondary:hover {
            transform: translateY(-2px);
            color: #4f46e5;
            border-color: #4f46e5;
        }

        .attribute-hub-note {
            margin-top: 1.25rem;
            padding: 1rem 1.1rem;
            border-radius: 14px;
            background: #f8fafc;
            border: 1px solid #e2e8f0;
            color: #334155;
        }

        .attribute-hub-empty {
            padding: 2.5rem 1.5rem;
            text-align: center;
        }

        .attribute-hub-empty-icon {
            width: 72px;
            height: 72px;
            margin: 0 auto 1rem;
            border-radius: 20px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            background: rgba(99, 102, 241, 0.1);
            color: #4f46e5;
            font-size: 2rem;
        }

        .attribute-hub-empty h3 {
            margin: 0 0 0.45rem;
            color: #0f172a;
            font-size: 1.3rem;
        }

        .attribute-hub-empty p {
            margin: 0;
            color: #64748b;
        }

        @media (max-width: 1100px) {
            .attribute-hub-grid {
                grid-template-columns: 1fr;
            }

            .attribute-hub-sidebar {
                position: static;
            }
        }

        @media (max-width: 768px) {
            .attribute-hub-page {
                padding: 1rem;
            }

            .attribute-hub-header,
            .attribute-hub-sidebar,
            .attribute-hub-card {
                padding: 1.1rem;
            }

            .attribute-hub-stats,
            .attribute-hub-meta {
                grid-template-columns: 1fr;
            }

            .attribute-hub-item {
                grid-template-columns: auto minmax(0, 1fr);
            }

            .attribute-hub-count {
                grid-column: 2;
                justify-self: start;
            }
        }
    </style>
    <style>
        .attribute-workspace-panel {
            background: white;
            border: 1px solid #e2e8f0;
            border-radius: 16px;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.05);
            padding: 1.25rem;
        }

        .attribute-workspace-card {
            border: 0;
            padding: 0;
        }

        .attribute-workspace-header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            gap: 1rem;
            flex-wrap: wrap;
            margin-bottom: 1rem;
        }

        .attribute-workspace-title {
            margin: 0;
            font-size: 1.5rem;
            font-weight: 800;
            color: #0f172a;
        }

        .attribute-workspace-subtitle {
            margin: 0.45rem 0 0;
            color: #475569;
            max-width: 70ch;
            line-height: 1.55;
        }

        .attribute-workspace-header-actions {
            display: flex;
            gap: 0.75rem;
            flex-wrap: wrap;
        }

        .attribute-stat-content {
            min-width: 0;
        }

        .attribute-table-card,
        .attribute-form-card {
            background: white;
            border: 1px solid #e2e8f0;
            border-radius: 14px;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.04);
            padding: 1.25rem;
        }

        .attribute-stats-grid {
            display: grid;
            grid-template-columns: repeat(3, minmax(0, 1fr));
            gap: 1rem;
            margin-bottom: 1rem;
        }

        .attribute-stat-card {
            display: flex;
            align-items: center;
            gap: 0.85rem;
            padding: 0.9rem 1rem;
            border-radius: 14px;
            border: 1px solid #e2e8f0;
            background: #fff;
        }

        .attribute-stat-icon {
            width: 44px;
            height: 44px;
            border-radius: 12px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
        }

        .attribute-stat-primary .attribute-stat-icon {
            background: rgba(99, 102, 241, 0.1);
            color: #4f46e5;
        }

        .attribute-stat-success .attribute-stat-icon {
            background: rgba(16, 185, 129, 0.12);
            color: #059669;
        }

        .attribute-stat-warning .attribute-stat-icon {
            background: rgba(245, 158, 11, 0.12);
            color: #d97706;
        }

        .attribute-stat-label {
            color: #64748b;
            font-size: 0.75rem;
            text-transform: uppercase;
            letter-spacing: 0.08em;
            font-weight: 700;
        }

        .attribute-stat-value {
            color: #0f172a;
            font-weight: 800;
            font-size: 1.2rem;
        }

        .attribute-workspace-alert {
            margin-bottom: 1rem;
        }

        .attribute-workspace-empty {
            text-align: center;
            padding: 2.5rem 1.5rem;
        }

        .attribute-workspace-empty-icon {
            width: 72px;
            height: 72px;
            border-radius: 20px;
            margin: 0 auto 1rem;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            background: rgba(99, 102, 241, 0.1);
            color: #4f46e5;
            font-size: 1.8rem;
        }

        .attribute-workspace-empty h3 {
            margin: 0 0 0.45rem;
            color: #0f172a;
            font-size: 1.25rem;
        }

        .attribute-workspace-empty p {
            margin: 0;
            color: #64748b;
        }

        .attribute-detail-row td {
            background: #f8fafc;
            padding-top: 0;
        }

        .attribute-detail-box {
            border: 1px solid #e2e8f0;
            border-radius: 12px;
            background: white;
            padding: 1rem;
            margin-top: 0.25rem;
        }

        .attribute-detail-grid {
            display: grid;
            grid-template-columns: repeat(5, minmax(0, 1fr));
            gap: 0.75rem;
        }

        .attribute-detail-item span {
            display: block;
            font-size: 0.75rem;
            letter-spacing: 0.08em;
            text-transform: uppercase;
            color: #64748b;
            font-weight: 700;
            margin-bottom: 0.25rem;
        }

        .attribute-detail-item strong {
            color: #0f172a;
        }

        .attribute-name-button {
            border: none;
            background: transparent;
            color: #0f172a;
            font-weight: 600;
            padding: 0;
            cursor: pointer;
        }

        .attribute-filter-section {
            margin: 1rem 0;
            padding: 1rem;
            border: 1px solid #e2e8f0;
            border-radius: 14px;
            background: #f8fafc;
        }

        .attribute-filter-form {
            display: flex;
            flex-wrap: wrap;
            gap: 0.75rem;
            align-items: center;
        }

        .attribute-search-box {
            position: relative;
            flex: 1;
            min-width: 240px;
        }

        .attribute-search-icon {
            position: absolute;
            left: 1rem;
            top: 50%;
            transform: translateY(-50%);
            color: #94a3b8;
        }

        .attribute-search-input {
            width: 100%;
            padding: 0.75rem 0.9rem 0.75rem 2.5rem;
            border-radius: 12px;
            border: 1px solid #dbe4ef;
            background: white;
        }

        .attribute-filter-meta {
            margin-top: 0.75rem;
            color: #64748b;
            font-size: 0.85rem;
        }

        .attribute-btn {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            border: 1px solid transparent;
            border-radius: 12px;
            padding: 0.78rem 1rem;
            font-weight: 800;
            cursor: pointer;
            transition: all 0.2s ease;
            text-decoration: none;
            background: #fff;
            color: #334155;
        }

        .attribute-btn:hover {
            transform: translateY(-1px);
        }

        .attribute-btn-primary {
            background: linear-gradient(135deg, #6366f1, #4f46e5);
            color: #fff;
            box-shadow: 0 10px 24px rgba(79, 70, 229, 0.22);
        }

        .attribute-btn-primary:hover {
            color: #fff;
        }

        .attribute-btn-secondary {
            border-color: #cbd5e1;
            background: #fff;
            color: #334155;
        }

        .attribute-btn-secondary:hover {
            border-color: #4f46e5;
            color: #4f46e5;
        }

        .attribute-btn-ghost {
            border-color: #e2e8f0;
            background: #f8fafc;
            color: #334155;
        }

        .attribute-btn-ghost:hover {
            border-color: #c7d2fe;
            color: #4f46e5;
        }

        .attribute-form-group {
            margin-bottom: 1.25rem;
        }

        .attribute-form-label {
            display: flex;
            align-items: center;
            gap: 0.45rem;
            margin-bottom: 0.65rem;
            color: #0f172a;
            font-weight: 700;
        }

        .attribute-required {
            color: #dc2626;
        }

        .attribute-form-input {
            width: 100%;
            padding: 0.88rem 1rem;
            border: 1px solid #dbe4ef;
            border-radius: 12px;
            background: #f8fafc;
            transition: all 0.2s ease;
        }

        .attribute-form-input:focus {
            outline: none;
            background: #fff;
            border-color: #4f46e5;
            box-shadow: 0 0 0 4px rgba(79, 70, 229, 0.08);
        }

        .attribute-form-input.is-invalid {
            border-color: #dc2626;
            background: #fff5f5;
        }

        .attribute-status-toggle-group {
            display: flex;
            gap: 0.75rem;
            flex-wrap: wrap;
        }

        .attribute-status-toggle {
            flex: 1;
            min-width: 180px;
            cursor: pointer;
        }

        .attribute-status-toggle input {
            display: none;
        }

        .attribute-toggle-indicator {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 0.45rem;
            padding: 0.95rem 1rem;
            border: 1px solid #dbe4ef;
            border-radius: 12px;
            background: #fff;
            font-weight: 800;
            color: #334155;
            transition: all 0.2s ease;
        }

        .attribute-status-toggle input:checked + .attribute-toggle-indicator.active {
            border-color: #10b981;
            color: #059669;
            background: rgba(16, 185, 129, 0.08);
        }

        .attribute-status-toggle input:checked + .attribute-toggle-indicator.inactive {
            border-color: #64748b;
            color: #64748b;
            background: rgba(100, 116, 139, 0.08);
        }

        .attribute-form-actions {
            display: flex;
            justify-content: flex-end;
            gap: 0.75rem;
            flex-wrap: wrap;
            margin-top: 1.5rem;
            padding-top: 1.25rem;
            border-top: 1px solid #e2e8f0;
        }

        .attribute-error-message {
            margin-top: 0.5rem;
            color: #dc2626;
            display: flex;
            align-items: center;
            gap: 0.35rem;
            font-size: 0.875rem;
            font-weight: 600;
        }

        .attribute-icon-btn {
            width: 44px;
            height: 44px;
            border-radius: 12px;
            border: 1px solid #e2e8f0;
            background: #fff;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            transition: all 0.2s ease;
        }

        .attribute-icon-btn:hover {
            transform: translateY(-1px);
        }

        .attribute-icon-btn-edit {
            color: #4f46e5;
            background: rgba(99, 102, 241, 0.08);
        }

        .attribute-icon-btn-delete {
            color: #dc2626;
            background: rgba(239, 68, 68, 0.08);
        }

        .attribute-delete-form {
            display: inline;
            margin: 0;
        }

        .attribute-empty-state {
            text-align: center;
            padding: 2.5rem 1.5rem;
        }

        .attribute-empty-icon {
            width: 72px;
            height: 72px;
            border-radius: 20px;
            margin: 0 auto 1rem;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            background: rgba(99, 102, 241, 0.1);
            color: #4f46e5;
            font-size: 1.8rem;
        }

        .attribute-table {
            width: 100%;
            border-collapse: collapse;
        }

        .attribute-table thead {
            background: #f8fafc;
            border-bottom: 1px solid #e2e8f0;
        }

        .attribute-table th,
        .attribute-table td {
            padding: 1rem 1.1rem;
            text-align: left;
            vertical-align: middle;
        }

        .attribute-table th {
            color: #334155;
            font-size: 0.8rem;
            text-transform: uppercase;
            letter-spacing: 0.08em;
            font-weight: 800;
        }

        .attribute-row {
            border-bottom: 1px solid #eef2f7;
        }

        .attribute-row:hover {
            background: #f8fafc;
        }

        .attribute-id-badge {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            padding: 0.3rem 0.7rem;
            border-radius: 999px;
            background: rgba(99, 102, 241, 0.1);
            color: #4f46e5;
            font-weight: 800;
            font-size: 0.84rem;
        }

        .attribute-status-badge {
            display: inline-flex;
            align-items: center;
            gap: 0.35rem;
            padding: 0.35rem 0.8rem;
            border-radius: 999px;
            font-weight: 800;
            font-size: 0.84rem;
        }

        .attribute-status-badge.active {
            background: rgba(16, 185, 129, 0.1);
            color: #059669;
        }

        .attribute-status-badge.inactive {
            background: rgba(100, 116, 139, 0.1);
            color: #64748b;
        }

        .attribute-table-card {
            margin-top: 1rem;
        }

        .attribute-actions {
            display: flex;
            gap: 0.45rem;
            justify-content: center;
        }

        .attribute-pagination {
            padding: 1.5rem;
            border-top: 1px solid #e2e8f0;
            display: flex;
            justify-content: flex-end;
        }

        .attribute-pagination .pagination {
            margin: 0;
        }

        .attribute-pagination .page-link {
            color: #4f46e5;
            border: 2px solid #e2e8f0;
            border-radius: 8px;
            margin: 0 0.25rem;
            padding: 0.5rem 0.75rem;
            font-weight: 600;
            transition: all 0.2s;
        }

        .attribute-pagination .page-link:hover {
            background: rgba(99, 102, 241, 0.05);
            border-color: #4f46e5;
            color: #4f46e5;
        }

        .attribute-pagination .page-item.active .page-link {
            background: #4f46e5;
            border-color: #4f46e5;
            color: white;
        }

        .attribute-pagination .page-item.disabled .page-link {
            color: #64748b;
            border-color: #e2e8f0;
            opacity: 0.5;
        }

        @media (max-width: 1100px) {
            .attribute-detail-grid {
                grid-template-columns: repeat(2, minmax(0, 1fr));
            }
        }

        @media (max-width: 768px) {
            .attribute-workspace-panel {
                padding: 1rem;
            }

            .attribute-detail-grid {
                grid-template-columns: 1fr;
            }
        }
    </style>
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const app = document.getElementById('attributeHubApp');
            const workspace = document.getElementById('attributeHubWorkspace');
            const flash = document.getElementById('attributeHubAlert');

            if (!app || !workspace) {
                return;
            }

            const indexUrl = app.dataset.indexUrl;
            const fragmentUrl = app.dataset.fragmentUrl;

            const initialState = {
                module: @json($selectedModule['key'] ?? null),
                view: @json($workspace ? $workspace['mode'] : 'list'),
                id: @json($workspace && ($workspace['item'] ?? null) ? $workspace['item']->id : null),
                search: @json($workspace ? $workspace['search'] : ''),
                detail: @json($workspace ? $workspace['expandedItemId'] : null),
            };

            if (initialState.module) {
                workspace.dataset.module = initialState.module;
                workspace.dataset.mode = initialState.view || 'list';
            }

            function buildUrl(params) {
                const url = new URL(fragmentUrl, window.location.origin);
                Object.entries(params).forEach(([key, value]) => {
                    if (value !== null && value !== undefined && value !== '') {
                        url.searchParams.set(key, value);
                    }
                });
                return url.toString();
            }

            function pushHubState(state) {
                if (!state.module) {
                    return;
                }

                const url = new URL(indexUrl, window.location.origin);
                url.searchParams.set('module', state.module);
                if (state.view && state.view !== 'list') {
                    url.searchParams.set('view', state.view);
                }
                if (state.id) {
                    url.searchParams.set('id', state.id);
                }
                if (state.search) {
                    url.searchParams.set('search', state.search);
                }
                if (state.detail) {
                    url.searchParams.set('detail', state.detail);
                }
                window.history.pushState(state, '', url.toString());
            }

            function showAlert(type, message) {
                if (!flash) {
                    return;
                }

                flash.innerHTML = `
                    <div class="alert alert-${type} alert-dismissible fade show mb-0" role="alert">
                        ${message}
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                `;
            }

            function clearAlert() {
                if (flash) {
                    flash.innerHTML = '';
                }
            }

            async function loadWorkspace(params, pushState = true) {
                if (!params.module) {
                    return;
                }

                clearAlert();

                try {
                    const response = await fetch(buildUrl(params), {
                        headers: {
                            'X-Requested-With': 'XMLHttpRequest',
                            'Accept': 'text/html',
                        },
                    });

                    if (!response.ok) {
                        throw new Error('Failed to load workspace');
                    }

                    const html = await response.text();
                    workspace.innerHTML = html;
                    workspace.dataset.module = params.module;
                    workspace.dataset.mode = params.view || 'list';
                    document.querySelectorAll('.attribute-hub-item').forEach((button) => {
                        button.classList.toggle('active', button.dataset.hubModule === params.module);
                    });
                    if (pushState) {
                        pushHubState(params);
                    }
                } catch (error) {
                    showAlert('danger', 'Unable to load the selected module.');
                }
            }

            function clearValidationErrors(form) {
                form.querySelectorAll('.is-invalid').forEach((el) => el.classList.remove('is-invalid'));
                form.querySelectorAll('.attribute-error-message').forEach((el) => el.remove());
            }

            function renderValidationErrors(form, errors) {
                clearValidationErrors(form);
                Object.entries(errors || {}).forEach(([field, messages]) => {
                    const input = form.querySelector(`[name="${field}"]`);
                    if (!input) {
                        return;
                    }
                    input.classList.add('is-invalid');
                    const group = input.closest('.attribute-form-group') || input.parentElement;
                    if (!group) {
                        return;
                    }
                    const error = document.createElement('div');
                    error.className = 'attribute-error-message';
                    error.innerHTML = `<i class="bi bi-exclamation-circle"></i><span>${messages[0]}</span>`;
                    group.appendChild(error);
                });
            }

            async function submitWorkspaceForm(form) {
                clearAlert();
                clearValidationErrors(form);

                try {
                    const response = await fetch(form.action, {
                        method: form.method || 'POST',
                        headers: {
                            'X-Requested-With': 'XMLHttpRequest',
                            'Accept': 'application/json',
                        },
                        body: new FormData(form),
                    });

                    const payload = await response.json().catch(() => ({}));

                    if (!response.ok) {
                        if (response.status === 422 && payload.errors) {
                            renderValidationErrors(form, payload.errors);
                            showAlert('danger', payload.message || 'Please fix the highlighted fields.');
                            return;
                        }
                        throw new Error(payload.message || 'Save failed.');
                    }

                    await loadWorkspace({ module: form.dataset.moduleKey, view: 'list' }, true);
                    showAlert('success', payload.message || 'Saved successfully.');
                } catch (error) {
                    showAlert('danger', error.message || 'Unable to save the record.');
                }
            }

            async function submitDeleteForm(form) {
                const confirmed = window.confirm('Delete this item?');
                if (!confirmed) {
                    return;
                }

                clearAlert();

                try {
                    const response = await fetch(form.action, {
                        method: 'POST',
                        headers: {
                            'X-Requested-With': 'XMLHttpRequest',
                            'Accept': 'application/json',
                        },
                        body: new FormData(form),
                    });

                    const payload = await response.json().catch(() => ({}));

                    if (!response.ok) {
                        throw new Error(payload.message || 'Delete failed.');
                    }

                    await loadWorkspace({ module: form.dataset.moduleKey, view: 'list' }, false);
                    showAlert('success', payload.message || 'Deleted successfully.');
                } catch (error) {
                    showAlert('danger', error.message || 'Unable to delete the record.');
                }
            }

            app.addEventListener('click', (event) => {
                const navButton = event.target.closest('[data-hub-module]');
                if (navButton && navButton.closest('.attribute-hub-list')) {
                    event.preventDefault();
                    loadWorkspace({ module: navButton.dataset.hubModule, view: 'list' });
                    return;
                }

                const actionButton = event.target.closest('[data-hub-action]');
                if (actionButton) {
                    const action = actionButton.dataset.hubAction;
                    const module = actionButton.dataset.hubModule || workspace.dataset.module;
                    if (!module) {
                        return;
                    }

                    if (action === 'create') {
                        event.preventDefault();
                        loadWorkspace({ module, view: 'create' });
                        return;
                    }

                    if (action === 'edit') {
                        event.preventDefault();
                        loadWorkspace({ module, view: 'edit', id: actionButton.dataset.hubId });
                        return;
                    }

                    if (action === 'list') {
                        event.preventDefault();
                        loadWorkspace({ module, view: 'list' });
                        return;
                    }

                    if (action === 'toggle-detail') {
                        event.preventDefault();
                        const targetId = actionButton.dataset.detailTarget;
                        const row = targetId ? document.getElementById(targetId) : null;
                        if (row) {
                            row.classList.toggle('d-none');
                        }
                        return;
                    }
                }

                const pageLink = event.target.closest('.attribute-pagination a');
                if (pageLink) {
                    event.preventDefault();
                    const url = new URL(pageLink.href);
                    loadWorkspace({
                        module: workspace.dataset.module,
                        view: 'list',
                        search: url.searchParams.get('search') || '',
                        page: url.searchParams.get('page') || '',
                    }, true);
                }
            });

            app.addEventListener('submit', (event) => {
                const searchForm = event.target.closest('[data-hub-search-form]');
                if (searchForm) {
                    event.preventDefault();
                    const formData = new FormData(searchForm);
                    loadWorkspace({
                        module: formData.get('module'),
                        view: 'list',
                        search: formData.get('search'),
                    });
                    return;
                }

                const form = event.target.closest('[data-attribute-form]');
                if (form) {
                    event.preventDefault();
                    form.dataset.moduleKey = form.querySelector('input[name="module"]')?.value || workspace.dataset.module;
                    submitWorkspaceForm(form);
                    return;
                }

                const deleteForm = event.target.closest('[data-hub-delete-form]');
                if (deleteForm) {
                    event.preventDefault();
                    deleteForm.dataset.moduleKey = workspace.dataset.module;
                    submitDeleteForm(deleteForm);
                }
            });

            window.addEventListener('popstate', (event) => {
                const state = event.state || initialState;
                if (state && state.module) {
                    loadWorkspace(state, false);
                }
            });

            history.replaceState(initialState, '', window.location.href);
        });
    </script>
@endsection
