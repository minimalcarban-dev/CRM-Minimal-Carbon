<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Terms of Service - {{ config('legal.product_name') }}</title>
    <style>
        :root {
            --bg: #f8fafc;
            --card: #ffffff;
            --text: #0f172a;
            --muted: #475569;
            --line: #e2e8f0;
            --accent: #1d4ed8;
        }

        * { box-sizing: border-box; }
        body {
            margin: 0;
            font-family: "Segoe UI", Arial, sans-serif;
            background: linear-gradient(180deg, #f8fafc 0%, #eff6ff 100%);
            color: var(--text);
            line-height: 1.65;
        }

        .wrap {
            max-width: 980px;
            margin: 0 auto;
            padding: 32px 20px 80px;
        }

        .card {
            background: var(--card);
            border: 1px solid var(--line);
            border-radius: 20px;
            box-shadow: 0 12px 30px rgba(15, 23, 42, 0.06);
            overflow: hidden;
        }

        .hero {
            padding: 36px 32px 24px;
            background: linear-gradient(135deg, #0f172a 0%, #1d4ed8 100%);
            color: #fff;
        }

        .hero h1 {
            margin: 0 0 10px;
            font-size: 40px;
            line-height: 1.1;
        }

        .hero p {
            margin: 0;
            max-width: 760px;
            color: rgba(255,255,255,0.88);
        }

        .meta {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
            gap: 12px;
            padding: 18px 32px;
            border-bottom: 1px solid var(--line);
            background: #f8fafc;
        }

        .meta-item {
            padding: 12px 14px;
            border: 1px solid var(--line);
            border-radius: 14px;
            background: #fff;
        }

        .meta-label {
            display: block;
            font-size: 12px;
            text-transform: uppercase;
            letter-spacing: 0.08em;
            color: var(--muted);
            margin-bottom: 6px;
        }

        .content {
            padding: 30px 32px 40px;
        }

        h2 {
            margin: 32px 0 12px;
            font-size: 24px;
        }

        p, li {
            color: #1e293b;
        }

        ul {
            padding-left: 22px;
        }

        .notice {
            margin-top: 24px;
            padding: 16px 18px;
            border-left: 4px solid var(--accent);
            border-radius: 12px;
            background: #eff6ff;
        }

        a { color: #1d4ed8; }

        @media (max-width: 640px) {
            .hero, .content, .meta {
                padding-left: 20px;
                padding-right: 20px;
            }

            .hero h1 {
                font-size: 30px;
            }
        }
    </style>
</head>
<body>
    <div class="wrap">
        <div class="card">
            <section class="hero">
                <h1>Terms of Service</h1>
                <p>
                    These Terms of Service govern your access to and use of {{ config('legal.product_name') }}.
                    By using the service, you agree to these Terms.
                </p>
            </section>

            <section class="meta">
                <div class="meta-item">
                    <span class="meta-label">Service</span>
                    <strong>{{ config('legal.product_name') }}</strong>
                </div>
                <div class="meta-item">
                    <span class="meta-label">Operator</span>
                    <strong>{{ config('legal.entity_name') }}</strong>
                </div>
                <div class="meta-item">
                    <span class="meta-label">Effective Date</span>
                    <strong>{{ config('legal.effective_date') }}</strong>
                </div>
                <div class="meta-item">
                    <span class="meta-label">Support Contact</span>
                    <strong>{{ config('legal.support_email') }}</strong>
                </div>
            </section>

            <section class="content">
                <h2>1. Overview</h2>
                <p>
                    {{ config('legal.product_name') }} is business software that may include CRM, email integration,
                    internal messaging, order management, invoice handling, analytics, and related operational features.
                </p>

                <h2>2. Eligibility and Authorized Use</h2>
                <p>
                    You may use the service only if you are authorized by the account owner or organization using the service.
                    You are responsible for ensuring that your use complies with applicable law, your internal policies,
                    and any third-party platform terms that apply to connected accounts.
                </p>

                <h2>3. Account Security</h2>
                <ul>
                    <li>You are responsible for maintaining the confidentiality of your login credentials.</li>
                    <li>You must not share credentials or allow unauthorized access to the platform.</li>
                    <li>You must promptly notify us of suspected unauthorized access or security incidents.</li>
                </ul>

                <h2>4. Gmail and Third-Party Integrations</h2>
                <p>
                    The service may allow you to connect third-party accounts, including Gmail. By connecting such accounts,
                    you authorize the service to access and process data necessary to provide the requested features.
                </p>
                <ul>
                    <li>Viewing and synchronizing mailbox content inside the CRM.</li>
                    <li>Saving drafts, sending email, and updating mailbox state.</li>
                    <li>Maintaining operational logs, sync status, and security records related to the integration.</li>
                </ul>
                <p>
                    You remain responsible for ensuring that you have the right to connect and use any third-party account
                    or data source through the platform. You may disconnect integrations or revoke Google access through
                    your Google account settings where applicable.
                </p>

                <h2>5. Acceptable Use</h2>
                <p>You agree not to:</p>
                <ul>
                    <li>Use the service for unlawful, fraudulent, harmful, or abusive activity.</li>
                    <li>Attempt to bypass authentication, authorization, or system restrictions.</li>
                    <li>Upload malware, harmful content, or materials that infringe third-party rights.</li>
                    <li>Interfere with the integrity, performance, or security of the service.</li>
                    <li>Use Gmail or other integration features in violation of Google or other third-party policies.</li>
                </ul>

                <h2>6. Customer Data and Gmail Data</h2>
                <p>
                    As between the parties, you or your organization retain ownership of the business data, email content,
                    and other content you submit or connect through the service. You grant us only the limited rights needed
                    to host, process, secure, synchronize, maintain, and support the service.
                </p>
                <div class="notice">
                    Gmail data accessed through Google APIs is used only to provide user-facing mail features and related
                    security or support operations. It is not sold, not used for advertising, and not used to train
                    generalized AI or machine learning models.
                </div>

                <h2>7. Privacy</h2>
                <p>
                    Your use of the service is also governed by our Privacy Policy:
                    <a href="{{ route('legal.privacy') }}">{{ route('legal.privacy') }}</a>.
                </p>

                <h2>8. Service Availability and Changes</h2>
                <p>
                    We may modify, suspend, or discontinue any part of the service from time to time, including integrations,
                    features, limits, and technical requirements. We do not guarantee uninterrupted or error-free availability.
                </p>

                <h2>9. Suspension and Termination</h2>
                <p>
                    We may suspend or terminate access if we reasonably believe there has been a violation of these Terms,
                    a security issue, a legal requirement, misuse of third-party integrations, or a risk to the platform or others.
                </p>

                <h2>10. Disclaimers</h2>
                <div class="notice">
                    The service is provided on an "as is" and "as available" basis, without warranties of any kind,
                    except to the extent such warranties cannot be excluded under applicable law.
                </div>
                <p>
                    We do not warrant that the service will be uninterrupted, secure, error-free, or fully compatible
                    with every third-party system or workflow.
                </p>

                <h2>11. Limitation of Liability</h2>
                <p>
                    To the maximum extent permitted by law, {{ config('legal.entity_name') }} will not be liable for indirect,
                    incidental, special, consequential, exemplary, or punitive damages, or for loss of profits, revenue, data,
                    goodwill, or business opportunity arising out of or related to the service.
                </p>

                <h2>12. Indemnity</h2>
                <p>
                    You agree to indemnify and hold harmless {{ config('legal.entity_name') }} from claims, losses,
                    liabilities, and expenses arising from your misuse of the service, violation of these Terms,
                    infringement of rights, or unlawful use of connected data or third-party accounts.
                </p>

                <h2>13. Governing Law</h2>
                <p>
                    These Terms are governed by the laws applicable to the operating entity of the service,
                    unless otherwise required by mandatory local law. If you want jurisdiction and venue stated with precision,
                    that should be finalized with legal counsel before public commercial release.
                </p>

                <h2>14. Changes to These Terms</h2>
                <p>
                    We may update these Terms from time to time. Continued use of the service after updated Terms are posted
                    constitutes acceptance of the updated version.
                </p>

                <h2>15. Contact</h2>
                <p>
                    For questions regarding these Terms, contact
                    <a href="mailto:{{ config('legal.support_email') }}">{{ config('legal.support_email') }}</a>.
                </p>
            </section>
        </div>
    </div>
</body>
</html>
