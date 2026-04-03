<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>403 Access Denied - Security Alert</title>
    <link rel="icon" type="image/png" href="{{ asset('images/Luxurious-Logo.png') }}">
    <link rel="shortcut icon" type="image/png" href="{{ asset('images/Luxurious-Logo.png') }}">
    <style>
        /* ── Reset & Base ── */
        *,
        *::before,
        *::after {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        :root {
            --bg: #f3f4f6;
            --card-bg: rgba(255, 255, 255, 0.85);
            --inner-bg: rgba(255, 255, 255, 0.5);
            --text: #1e293b;
            --text-muted: #64748b;
            --text-faint: #94a3b8;
            --border: rgba(226, 232, 240, 0.8);
            --inner-border: rgba(255, 255, 255, 0.3);
            --primary: #f97316;
            --primary-dark: #ea580c;
            --danger: #ef4444;
            --green: #22c55e;
            --green-bg: rgba(34, 197, 94, 0.1);
            --green-border: rgba(34, 197, 94, 0.2);
            --terminal-bg: rgba(0, 0, 0, 0.92);
            --terminal-border: #334155;
            --circuit-dot: rgba(148, 163, 184, 0.15);
            --circuit-line: rgba(148, 163, 184, 0.05);
            --glow-1: rgba(249, 115, 22, 0.08);
            --glow-2: rgba(239, 68, 68, 0.04);
            --scan-icon-bg: linear-gradient(135deg, #f8fafc, #f1f5f9);
            --scan-icon-border: #e2e8f0;
            --scan-icon-shadow: rgba(0, 0, 0, 0.08);
            --radar-outer: rgba(226, 232, 240, 1);
            --radar-inner: rgba(226, 232, 240, 0.5);
        }

        .dark {
            --bg: #0b1120;
            --card-bg: rgba(15, 23, 42, 0.65);
            --inner-bg: rgba(15, 23, 42, 0.5);
            --text: #f1f5f9;
            --text-muted: #94a3b8;
            --text-faint: #475569;
            --border: rgba(51, 65, 85, 0.5);
            --inner-border: rgba(255, 255, 255, 0.05);
            --circuit-dot: rgba(255, 255, 255, 0.08);
            --circuit-line: rgba(255, 255, 255, 0.03);
            --glow-1: rgba(249, 115, 22, 0.1);
            --glow-2: rgba(239, 68, 68, 0.08);
            --scan-icon-bg: linear-gradient(135deg, #1e293b, #0f172a);
            --scan-icon-border: #334155;
            --scan-icon-shadow: rgba(0, 0, 0, 0.3);
            --radar-outer: rgba(51, 65, 85, 0.5);
            --radar-inner: rgba(51, 65, 85, 0.3);
        }

        body {
            font-family: 'Segoe UI', -apple-system, BlinkMacSystemFont, sans-serif;
            background: var(--bg);
            color: var(--text);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            position: relative;
            overflow-x: hidden;
            overflow-y: auto;
            padding: 24px 0;
            transition: background 0.3s, color 0.3s;
        }

        /* ── Circuit Board Background ── */
        .circuit-bg {
            position: fixed;
            inset: 0;
            z-index: 0;
            opacity: 0.6;
            pointer-events: none;
            background-image:
                radial-gradient(circle at 2px 2px, var(--circuit-dot) 1px, transparent 0),
                linear-gradient(to right, var(--circuit-line) 1px, transparent 1px),
                linear-gradient(to bottom, var(--circuit-line) 1px, transparent 1px);
            background-size: 24px 24px, 48px 48px, 48px 48px;
        }

        /* ── Ambient Glows ── */
        .glow-1 {
            position: fixed;
            top: -20%;
            left: -10%;
            width: 500px;
            height: 500px;
            background: var(--glow-1);
            border-radius: 50%;
            filter: blur(120px);
            pointer-events: none;
            animation: pulse-slow 4s ease-in-out infinite;
        }

        .glow-2 {
            position: fixed;
            bottom: -20%;
            right: -10%;
            width: 600px;
            height: 600px;
            background: var(--glow-2);
            border-radius: 50%;
            filter: blur(120px);
            pointer-events: none;
            animation: pulse-slow 4s ease-in-out infinite 2s;
        }

        /* ── Theme Toggle ── */
        .theme-toggle {
            position: fixed;
            top: 24px;
            right: 24px;
            z-index: 50;
            width: 44px;
            height: 44px;
            border-radius: 50%;
            background: var(--card-bg);
            border: 1px solid var(--border);
            color: var(--text);
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 18px;
            backdrop-filter: blur(8px);
            transition: transform 0.2s, box-shadow 0.2s;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.08);
        }

        .theme-toggle:hover {
            transform: scale(1.1);
            box-shadow: 0 4px 20px rgba(249, 115, 22, 0.2);
        }

        /* ── Main Card ── */
        .main-card {
            position: relative;
            z-index: 10;
            width: calc(100% - 48px);
            max-width: 700px;
            margin: 24px;
        }

        .glass-panel {
            background: var(--card-bg);
            backdrop-filter: blur(16px);
            -webkit-backdrop-filter: blur(16px);
            border-radius: 16px;
            padding: 4px;
            box-shadow: 0 8px 32px rgba(0, 0, 0, 0.12);
            border: 1px solid var(--border);
            position: relative;
            overflow: hidden;
            animation: float 6s ease-in-out infinite;
        }

        .inner-panel {
            position: relative;
            z-index: 1;
            background: var(--inner-bg);
            border-radius: 12px;
            padding: 36px;
            border: 1px solid var(--inner-border);
        }

        /* ── Tech Grid Overlay ── */
        .tech-grid {
            position: absolute;
            inset: 0;
            z-index: 0;
            opacity: 0.2;
            pointer-events: none;
            background-size: 40px 40px;
            background-image:
                linear-gradient(to right, rgba(249, 115, 22, 0.05) 1px, transparent 1px),
                linear-gradient(to bottom, rgba(249, 115, 22, 0.05) 1px, transparent 1px);
            mask-image: radial-gradient(circle at center, black 40%, transparent 100%);
            -webkit-mask-image: radial-gradient(circle at center, black 40%, transparent 100%);
        }

        /* ── System Verified Badge ── */
        .verified-badge {
            position: absolute;
            top: 16px;
            right: 16px;
            display: flex;
            align-items: center;
            gap: 8px;
            padding: 4px 12px;
            border-radius: 100px;
            background: var(--green-bg);
            border: 1px solid var(--green-border);
            font-family: 'Courier New', monospace;
            font-size: 10px;
            font-weight: 700;
            color: var(--green);
            text-transform: uppercase;
            letter-spacing: 0.1em;
        }

        .verified-dot {
            position: relative;
            width: 8px;
            height: 8px;
        }

        .verified-dot::before {
            content: '';
            position: absolute;
            inset: 0;
            border-radius: 50%;
            background: var(--green);
            animation: ping 2s cubic-bezier(0, 0, 0.2, 1) infinite;
            opacity: 0.75;
        }

        .verified-dot::after {
            content: '';
            position: absolute;
            inset: 0;
            border-radius: 50%;
            background: var(--green);
        }

        /* ── Lock Icon with Radar ── */
        .icon-container {
            position: relative;
            width: 128px;
            height: 128px;
            margin: 0 auto 32px;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .radar-ring-outer {
            position: absolute;
            inset: 0;
            border-radius: 50%;
            border: 1px solid var(--radar-outer);
        }

        .radar-ring-inner {
            position: absolute;
            inset: 8px;
            border-radius: 50%;
            border: 1px dashed var(--radar-inner);
        }

        .radar-sweep {
            position: absolute;
            inset: 0;
            border-radius: 50%;
            overflow: hidden;
            opacity: 0.25;
        }

        .dark .radar-sweep {
            opacity: 0.4;
        }

        .radar-line {
            width: 50%;
            height: 50%;
            position: absolute;
            top: 0;
            left: 0;
            transform-origin: bottom right;
            animation: radar 4s linear infinite;
            background: linear-gradient(90deg, transparent 50%, rgba(249, 115, 22, 0.4) 100%);
            border-right: 1px solid rgba(249, 115, 22, 0.8);
            box-shadow: 2px 0 10px rgba(249, 115, 22, 0.5);
        }

        .radar-glow {
            position: absolute;
            inset: 0;
            background: rgba(239, 68, 68, 0.05);
            border-radius: 50%;
            filter: blur(20px);
            animation: pulse-slow 3s ease-in-out infinite;
        }

        .lock-box {
            position: relative;
            z-index: 10;
            width: 76px;
            height: 76px;
            background: var(--scan-icon-bg);
            /* border-radius: 14px; */
            display: flex;
            align-items: center;
            justify-content: center;
            box-shadow: 0 0 20px var(--scan-icon-shadow);
            border: 1px solid var(--scan-icon-border);
            overflow: hidden;
        }

        .lock-box svg {
            color: var(--danger);
            filter: drop-shadow(0 2px 4px rgba(239, 68, 68, 0.3));
        }

        /* Corner Brackets */
        .corner {
            position: absolute;
            width: 12px;
            height: 12px;
            border-color: rgba(249, 115, 22, 0.5);
            border-style: solid;
        }

        .corner-tl {
            top: 0;
            left: 0;
            border-width: 2px 0 0 2px;
        }

        .corner-tr {
            top: 0;
            right: 0;
            border-width: 2px 2px 0 0;
        }

        .corner-bl {
            bottom: 0;
            left: 0;
            border-width: 0 0 2px 2px;
        }

        .corner-br {
            bottom: 0;
            right: 0;
            border-width: 0 2px 2px 0;
        }

        /* Scan line inside lock box */
        .scan-line {
            position: absolute;
            width: 100%;
            height: 2px;
            background: var(--danger);
            box-shadow: 0 0 15px var(--danger);
            animation: scan 3s linear infinite;
            opacity: 0.3;
        }

        /* Access Denied Badge */
        .denied-badge {
            position: absolute;
            bottom: -16px;
            left: 50%;
            transform: translateX(-50%);
            z-index: 20;
            background: var(--danger);
            color: white;
            font-family: 'Courier New', monospace;
            font-size: 10px;
            font-weight: 700;
            padding: 4px 14px;
            border-radius: 4px;
            box-shadow: 0 4px 12px rgba(239, 68, 68, 0.4);
            border: 1px solid #f87171;
            text-transform: uppercase;
            letter-spacing: 0.15em;
            white-space: nowrap;
            display: flex;
            align-items: center;
            gap: 6px;
        }

        /* ── Protocol Labels ── */
        .protocol-labels {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 16px;
            margin-bottom: 8px;
            opacity: 0.7;
        }

        .protocol-text {
            font-family: 'Courier New', monospace;
            font-size: 9px;
            color: var(--primary);
            text-transform: uppercase;
            letter-spacing: 0.15em;
            animation: pulse-slow 3s ease-in-out infinite;
        }

        .protocol-divider {
            height: 1px;
            width: 48px;
            background: rgba(249, 115, 22, 0.3);
        }

        /* ── 403 Heading ── */
        .heading {
            text-align: center;
            margin-bottom: 24px;
        }

        .heading h1 {
            font-size: clamp(2.4rem, 6vw, 3.5rem);
            font-weight: 700;
            letter-spacing: -0.03em;
            line-height: 1.1;
        }

        .heading .code {
            background: linear-gradient(135deg, var(--primary), var(--danger));
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }

        .heading .separator {
            color: var(--text-faint);
            font-weight: 300;
            margin: 0 8px;
        }

        .heading .forbidden {
            -webkit-text-fill-color: initial;
            background: none;
            color: var(--text);
        }

        /* ── Error Message ── */
        .error-msg {
            font-size: 12px;
            line-height: 1.7;
            color: var(--text-muted);
            margin-bottom: 24px;
            max-width: 420px;
            margin-left: auto;
            margin-right: auto;
            border-left: 2px solid rgba(249, 115, 22, 0.5);
            padding-left: 14px;
            text-align: left;
            font-weight: 300;
        }

        .error-msg .label {
            display: block;
            font-family: 'Courier New', monospace;
            font-size: 11px;
            color: var(--primary);
            margin-bottom: 4px;
            opacity: 0.8;
        }

        /* ── Terminal ── */
        .terminal {
            background: var(--terminal-bg);
            border-radius: 8px;
            padding: 14px;
            margin-bottom: 24px;
            border: 1px solid var(--terminal-border);
            position: relative;
            overflow: hidden;
            text-align: left;
            font-family: 'Courier New', 'Lucida Console', monospace;
            box-shadow: inset 0 2px 4px rgba(0, 0, 0, 0.2);
        }

        .terminal-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 12px;
            padding-bottom: 8px;
            border-bottom: 1px solid rgba(51, 65, 85, 0.5);
        }

        .terminal-title {
            font-size: 10px;
            color: #64748b;
            text-transform: uppercase;
            letter-spacing: 0.15em;
        }

        .terminal-dots {
            display: flex;
            gap: 6px;
        }

        .terminal-dots span {
            width: 8px;
            height: 8px;
            border-radius: 50%;
        }

        .terminal-dots span:nth-child(1) {
            background: rgba(239, 68, 68, 0.5);
        }

        .terminal-dots span:nth-child(2) {
            background: rgba(234, 179, 8, 0.5);
        }

        .terminal-dots span:nth-child(3) {
            background: rgba(34, 197, 94, 0.5);
        }

        .terminal-line {
            font-size: 11px;
            line-height: 1.7;
        }

        .terminal .prompt {
            color: #22c55e;
        }

        .terminal .cmd {
            color: #cbd5e1;
        }

        .terminal .label-text {
            color: #94a3b8;
        }

        .terminal .ip-value {
            color: var(--primary);
            font-weight: 700;
            letter-spacing: 0.05em;
        }

        .terminal .blue {
            color: #60a5fa;
        }

        .terminal .error-text {
            color: rgba(239, 68, 68, 0.7);
            font-style: italic;
        }

        .cursor-blink {
            display: inline-block;
            width: 8px;
            height: 16px;
            background: var(--primary);
            margin-left: 4px;
            vertical-align: middle;
            animation: blink 1s step-end infinite;
        }

        .terminal-scan {
            position: absolute;
            inset: 0;
            background: linear-gradient(180deg, transparent, rgba(255, 255, 255, 0.04), transparent);
            pointer-events: none;
            animation: scan 4s linear infinite;
            opacity: 0.3;
        }

        /* ── Buttons ── */
        .btn-row {
            display: flex;
            gap: 16px;
            justify-content: center;
            flex-wrap: wrap;
        }

        .btn-primary {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 10px 22px;
            background: linear-gradient(135deg, var(--primary), var(--primary-dark));
            color: white;
            font-weight: 700;
            font-size: 11px;
            text-transform: uppercase;
            letter-spacing: 0.15em;
            border: 1px solid rgba(251, 146, 60, 0.6);
            border-radius: 6px;
            cursor: pointer;
            transition: all 0.3s;
            box-shadow: 0 0 20px rgba(249, 115, 22, 0.3);
            font-family: inherit;
        }

        .btn-primary:hover {
            box-shadow: 0 0 30px rgba(249, 115, 22, 0.5);
            transform: translateY(-1px);
        }

        .btn-secondary {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 10px 22px;
            background: transparent;
            color: var(--text-muted);
            font-weight: 500;
            font-size: 11px;
            text-transform: uppercase;
            letter-spacing: 0.15em;
            border: 1px solid var(--border);
            border-radius: 6px;
            cursor: pointer;
            transition: all 0.2s;
            font-family: inherit;
        }

        .btn-secondary:hover {
            background: rgba(148, 163, 184, 0.1);
            border-color: var(--text-muted);
        }

        .btn-arrow {
            transition: transform 0.2s;
        }

        .btn-primary:hover .btn-arrow {
            transform: translateX(3px);
        }

        /* ── Footer ── */
        .card-footer {
            margin-top: 24px;
            padding-top: 12px;
            border-top: 1px solid var(--border);
            display: flex;
            justify-content: space-between;
            font-family: 'Courier New', monospace;
            font-size: 9px;
            color: var(--text-faint);
        }

        .card-footer .right {
            text-align: right;
        }

        .card-footer .highlight {
            color: var(--primary);
        }

        /* ── Animations ── */
        @keyframes float {

            0%,
            100% {
                transform: translateY(0);
            }

            50% {
                transform: translateY(-8px);
            }
        }

        @keyframes pulse-slow {

            0%,
            100% {
                opacity: 1;
            }

            50% {
                opacity: 0.5;
            }
        }

        @keyframes ping {

            75%,
            100% {
                transform: scale(2);
                opacity: 0;
            }
        }

        @keyframes radar {
            0% {
                transform: rotate(0deg);
            }

            100% {
                transform: rotate(360deg);
            }
        }

        @keyframes scan {
            0% {
                top: 0%;
                opacity: 0;
            }

            10% {
                opacity: 1;
            }

            90% {
                opacity: 1;
            }

            100% {
                top: 100%;
                opacity: 0;
            }
        }

        @keyframes blink {

            0%,
            100% {
                opacity: 1;
            }

            50% {
                opacity: 0;
            }
        }

        /* ── Responsive ── */
        @media (max-width: 640px) {
            .main-card {
                width: calc(100% - 32px);
                margin: 16px;
                max-width: 420px;
            }

            .inner-panel {
                padding: 24px 16px;
            }

            .icon-container {
                width: 90px;
                height: 90px;
                margin-bottom: 20px;
            }

            .lock-box {
                width: 56px;
                height: 56px;
                border-radius: 10px;
            }

            .lock-box svg {
                width: 24px;
                height: 24px;
            }

            .heading h1 {
                font-size: 2rem;
            }

            .heading .separator {
                margin: 0 4px;
            }

            .protocol-labels {
                gap: 8px;
                margin-bottom: 4px;
            }

            .protocol-text {
                font-size: 8px;
                letter-spacing: 0.1em;
            }

            .protocol-divider {
                width: 24px;
            }

            .error-msg {
                font-size: 12px;
                margin-bottom: 20px;
                padding-left: 12px;
            }

            .error-msg .label {
                font-size: 9px;
            }

            .terminal {
                padding: 12px;
                margin-bottom: 20px;
            }

            .terminal-title {
                font-size: 8px;
            }

            .terminal-dots span {
                width: 6px;
                height: 6px;
            }

            .terminal-line {
                font-size: 11px;
                line-height: 1.6;
            }

            .denied-badge {
                font-size: 8px;
                padding: 3px 10px;
                bottom: -12px;
            }

            .btn-row {
                flex-direction: column;
                gap: 10px;
            }

            .btn-primary,
            .btn-secondary {
                width: 100%;
                justify-content: center;
                padding: 10px 20px;
                font-size: 11px;
            }

            .card-footer {
                flex-direction: column;
                gap: 6px;
                margin-top: 20px;
                padding-top: 12px;
                font-size: 8px;
            }

            .card-footer .right {
                text-align: left;
            }

            .verified-badge {
                font-size: 7px;
                padding: 3px 8px;
                top: 8px;
                right: 8px;
            }

            .verified-dot {
                width: 6px;
                height: 6px;
            }

            .theme-toggle {
                top: 10px;
                right: 10px;
                width: 32px;
                height: 32px;
                font-size: 14px;
            }

            #request-form-container div:first-child {
                font-size: 9px !important;
            }

            #req-name,
            #req-reason {
                font-size: 12px !important;
                padding: 8px 12px !important;
            }
        }
    </style>
</head>

<body>
    <!-- Background layers -->
    <div class="circuit-bg"></div>
    <div class="glow-1"></div>
    <div class="glow-2"></div>

    <!-- Theme Toggle -->
    <button class="theme-toggle" onclick="document.documentElement.classList.toggle('dark')" title="Toggle theme">
        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
            stroke-linecap="round">
            <circle cx="12" cy="12" r="5" />
            <line x1="12" y1="1" x2="12" y2="3" />
            <line x1="12" y1="21" x2="12" y2="23" />
            <line x1="4.22" y1="4.22" x2="5.64" y2="5.64" />
            <line x1="18.36" y1="18.36" x2="19.78" y2="19.78" />
            <line x1="1" y1="12" x2="3" y2="12" />
            <line x1="21" y1="12" x2="23" y2="12" />
            <line x1="4.22" y1="19.78" x2="5.64" y2="18.36" />
            <line x1="18.36" y1="5.64" x2="19.78" y2="4.22" />
        </svg>
    </button>

    <!-- Main Card -->
    <main class="main-card">
        <div class="glass-panel">
            <div class="tech-grid"></div>
            <div class="inner-panel">

                <!-- System Verified Badge -->
                <div class="verified-badge">
                    <span class="verified-dot"></span>
                    System Authenticity: Verified
                </div>

                <!-- Lock Icon with Radar -->
                <div class="icon-container">
                    <div class="radar-ring-outer"></div>
                    <div class="radar-ring-inner"></div>
                    <div class="radar-sweep">
                        <div class="radar-line"></div>
                    </div>
                    <div class="radar-glow"></div>

                    <div class="lock-box">
                        <svg width="40" height="40" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                            <path d="M12 1.5C9.24 1.5 7 3.74 7 6.5V9H17V6.5C17 3.74 14.76 1.5 12 1.5Z"
                                stroke="currentColor" stroke-width="2" stroke-linecap="round" />
                            <path
                                d="M6 10C6 8.9 6.9 8 8 8H16C17.1 8 18 8.9 18 10V19C18 20.1 17.1 21 16 21H8C6.9 21 6 20.1 6 19V10Z"
                                fill="currentColor" fill-opacity="0.1" stroke="currentColor" stroke-width="2" />
                            <circle cx="12" cy="14.5" r="1.5" fill="currentColor" />
                        </svg>
                        <div class="scan-line"></div>
                        <div class="corner corner-tl"></div>
                        <div class="corner corner-tr"></div>
                        <div class="corner corner-bl"></div>
                        <div class="corner corner-br"></div>
                    </div>

                    <div class="denied-badge">
                        <svg width="10" height="10" viewBox="0 0 24 24" fill="currentColor">
                            <path
                                d="M12 22C6.477 22 2 17.523 2 12S6.477 2 12 2s10 4.477 10 10-4.477 10-10 10zm0-2a8 8 0 1 0 0-16 8 8 0 0 0 0 16zm-1-5h2v2h-2v-2zm0-8h2v6h-2V7z" />
                        </svg>
                        Access Denied
                    </div>
                </div>

                <!-- Protocol Labels -->
                <div class="protocol-labels">
                    <span class="protocol-text">&lt; NET_SEC_PROTOCOL /&gt;</span>
                    <span class="protocol-divider"></span>
                    <span class="protocol-text">STATUS: MONITORING</span>
                </div>

                <!-- 403 Heading -->
                <div class="heading">
                    <h1>
                        <span class="code">403</span>
                        <span class="separator">|</span>
                        <span class="forbidden">Forbidden</span>
                    </h1>
                </div>

                <!-- Error Message -->
                <div class="error-msg">
                    <span class="label">&gt; ERROR_LOG_DUMP:</span>
                    This browser does not currently have a trusted device token. If your IP changed after a router
                    restart, request access again or use the approved device trust flow.
                </div>

                <!-- Terminal -->
                <div class="terminal">
                    <div class="terminal-header">
                        <span class="terminal-title">Terminal Session</span>
                        <div class="terminal-dots">
                            <span></span><span></span><span></span>
                        </div>
                    </div>
                    <div class="terminal-line">
                        <span class="prompt">root@firewall:~#</span>
                        <span class="cmd"> analyze --ip-source</span>
                    </div>
                    <div class="terminal-line">
                        <span class="blue">&gt;</span>
                        <span class="label-text"> Client IP: </span>
                        <span class="ip-value">{{ $ip ?? 'Unknown' }}</span>
                        <span class="cursor-blink"></span>
                    </div>
                    <div class="terminal-line">
                        <span class="error-text">[!] Trusted device token not found or expired.</span>
                    </div>
                    <div class="terminal-scan"></div>
                </div>

                <!-- Access Request Form -->
                <div id="request-section">
                    <div id="request-form-container">
                        <div
                            style="text-align:center; margin-bottom:16px; font-family:'Courier New',monospace; font-size:11px; color:var(--primary); text-transform:uppercase; letter-spacing:0.15em;">
                            &gt; REQUEST_ACCESS_PROTOCOL
                        </div>
                        <div style="display:flex; flex-direction:column; gap:12px; margin-bottom:16px;">
                            <input type="text" id="req-name" placeholder="Your Name"
                                style="padding:10px 14px; border-radius:6px; border:1px solid var(--border); background:var(--inner-bg); color:var(--text); font-family:inherit; font-size:13px; outline:none; transition:border 0.2s;"
                                onfocus="this.style.borderColor='var(--primary)'"
                                onblur="this.style.borderColor='var(--border)'">
                            <textarea id="req-reason" placeholder="Reason for access (optional)" rows="2"
                                style="padding:10px 14px; border-radius:6px; border:1px solid var(--border); background:var(--inner-bg); color:var(--text); font-family:inherit; font-size:13px; outline:none; resize:vertical; transition:border 0.2s;"
                                onfocus="this.style.borderColor='var(--primary)'"
                                onblur="this.style.borderColor='var(--border)'"></textarea>
                        </div>
                        <div class="btn-row">
                            <button class="btn-primary" onclick="submitAccessRequest()">
                                <span id="btn-text">REQUEST ACCESS</span>
                                <span class="btn-arrow" id="btn-arrow">→</span>
                            </button>
                            <button class="btn-secondary" onclick="window.history.back()">
                                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                    stroke-width="2">
                                    <path d="M21 15a2 2 0 0 1-2 2H7l-4-4 4-4h12a2 2 0 0 1 2 2z" />
                                </svg>
                                <span>Go Back</span>
                            </button>
                        </div>
                    </div>

                    <!-- Success/Error feedback -->
                    <div id="request-feedback" style="display:none; text-align:center; padding:24px 0;">
                        <div id="feedback-icon" style="font-size:40px; margin-bottom:12px;"></div>
                        <div id="feedback-title" style="font-weight:700; font-size:16px; margin-bottom:8px;"></div>
                        <div id="feedback-msg" style="font-size:13px; color:var(--text-muted); line-height:1.6;"></div>
                    </div>
                </div>

                <!-- Footer -->
                <div class="card-footer">
                    <div class="left">
                        <div>REF: #ERR-403-{{ rand(1000, 9999) }}</div>
                        <div>NODE: FIREWALL-SEC-01</div>
                    </div>
                    <div class="right">
                        <div>ENCRYPTION: AES-256</div>
                        <div class="highlight">SECURE CONNECTION</div>
                    </div>
                </div>

            </div>
        </div>
    </main>

    <script>
        function submitAccessRequest() {
            var btn = document.querySelector('.btn-primary');
            var btnText = document.getElementById('btn-text');
            var btnArrow = document.getElementById('btn-arrow');
            var name = document.getElementById('req-name').value;
            var reason = document.getElementById('req-reason').value;

            // Disable button & show loading
            btn.disabled = true;
            btn.style.opacity = '0.6';
            btnText.textContent = 'SUBMITTING...';
            btnArrow.textContent = '⟳';

            var xhr = new XMLHttpRequest();
            xhr.open('POST', '/ip/request-access', true);
            xhr.setRequestHeader('Content-Type', 'application/json');
            xhr.setRequestHeader('Accept', 'application/json');
            xhr.setRequestHeader('X-CSRF-TOKEN', '{{ csrf_token() }}');

            xhr.onload = function () {
                var data = JSON.parse(xhr.responseText);
                var formContainer = document.getElementById('request-form-container');
                var feedback = document.getElementById('request-feedback');

                formContainer.style.display = 'none';
                feedback.style.display = 'block';

                if (xhr.status === 200 && data.success) {
                    document.getElementById('feedback-icon').textContent = '✅';
                    document.getElementById('feedback-title').textContent = 'Request Submitted!';
                    document.getElementById('feedback-title').style.color = 'var(--green)';
                    document.getElementById('feedback-msg').textContent = data.message;
                } else {
                    document.getElementById('feedback-icon').textContent = '⚠️';
                    document.getElementById('feedback-title').textContent = 'Request Failed';
                    document.getElementById('feedback-title').style.color = 'var(--primary)';
                    document.getElementById('feedback-msg').textContent = data.message || 'Something went wrong. Please try again later.';
                }
            };

            xhr.onerror = function () {
                btn.disabled = false;
                btn.style.opacity = '1';
                btnText.textContent = 'REQUEST ACCESS';
                btnArrow.textContent = '→';
                alert('Network error. Please try again.');
            };

            xhr.send(JSON.stringify({ name: name, reason: reason }));
        }
    </script>
</body>

</html>
