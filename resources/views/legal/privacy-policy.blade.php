<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Privacy Policy - {{ config('legal.product_name') }}</title>
    <style>
        :root {
            --bg: #f8fafc;
            --card: #ffffff;
            --text: #0f172a;
            --muted: #475569;
            --line: #e2e8f0;
            --accent: #0f766e;
        }

        * { box-sizing: border-box; }
        body {
            margin: 0;
            font-family: "Segoe UI", Arial, sans-serif;
            background: linear-gradient(180deg, #f8fafc 0%, #eef2ff 100%);
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
            background: linear-gradient(135deg, #0f172a 0%, #134e4a 100%);
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
            color: #0f172a;
        }

        h3 {
            margin: 20px 0 8px;
            font-size: 18px;
            color: #134e4a;
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
            background: #f0fdfa;
        }

        a { color: #0f766e; }

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
                <h1>Privacy Policy</h1>
                <p>
                    This Privacy Policy explains how {{ config('legal.entity_name') }} collects, uses, stores,
                    and protects information when you use {{ config('legal.product_name') }}, including CRM,
                    collaboration, and Gmail integration features.
                </p>
            </section>

            <section class="meta">
                <div class="meta-item">
                    <span class="meta-label">Product</span>
                    <strong>{{ config('legal.product_name') }}</strong>
                </div>
                <div class="meta-item">
                    <span class="meta-label">Website</span>
                    <strong>{{ config('legal.website_url') }}</strong>
                </div>
                <div class="meta-item">
                    <span class="meta-label">Effective Date</span>
                    <strong>{{ config('legal.effective_date') }}</strong>
                </div>
                <div class="meta-item">
                    <span class="meta-label">Privacy Contact</span>
                    <strong>{{ config('legal.privacy_email') }}</strong>
                </div>
            </section>

            <section class="content">
                <p>
                    {{ config('legal.product_name') }} is a business operations platform used to manage CRM activities,
                    clients, companies, orders, invoices, internal communication, and connected mailbox workflows.
                </p>

                <h2>1. Information We Collect</h2>
                <h3>1.1 Information you provide directly</h3>
                <ul>
                    <li>Administrator account information such as name, email address, phone number, and login credentials.</li>
                    <li>Business records entered into the platform, including client, company, order, invoice, and internal workflow data.</li>
                    <li>Messages, notes, and files shared through internal communication or collaboration features.</li>
                </ul>

                <h3>1.2 Information obtained through Gmail integration</h3>
                <p>
                    If you choose to connect a Google account, we access Gmail data only as needed to provide the mail
                    features you request inside the application. Depending on the permissions granted, this may include:
                </p>
                <ul>
                    <li>Mailbox identity information such as the connected Google email address.</li>
                    <li>Email metadata such as sender, recipients, subject line, labels, thread identifiers, and timestamps.</li>
                    <li>Email content, including plain-text and HTML message bodies.</li>
                    <li>Attachment metadata and attachment content where required to support mailbox features.</li>
                    <li>Draft, sent, trash, and mailbox state information needed to synchronize your mailbox in the CRM.</li>
                </ul>

                <h3>1.3 Technical and usage data</h3>
                <ul>
                    <li>IP address, browser information, device information, authentication events, and audit logs.</li>
                    <li>System activity, mailbox sync status, error diagnostics, and security-related logs.</li>
                </ul>

                <h2>2. How We Use Information</h2>
                <p>We use personal and business information to:</p>
                <ul>
                    <li>Provide, maintain, secure, and support the CRM platform.</li>
                    <li>Authenticate administrators and enforce access permissions.</li>
                    <li>Display, organize, search, synchronize, draft, send, and manage email inside the product.</li>
                    <li>Support operational features such as CRM records, orders, invoices, chat, notifications, and audit logging.</li>
                    <li>Detect misuse, investigate incidents, troubleshoot technical issues, and protect the platform.</li>
                    <li>Comply with legal obligations and enforce our contractual rights.</li>
                </ul>

                <h2>3. Google User Data and Gmail API Data</h2>
                <p>
                    Gmail data is processed only to provide the Gmail-related features that the user has explicitly enabled,
                    such as viewing email inside the CRM, syncing mailbox state, saving drafts, and sending messages.
                </p>
                <ul>
                    <li>We do not sell Gmail data.</li>
                    <li>We do not use Gmail data for advertising.</li>
                    <li>We do not use Gmail data to train generalized AI or machine learning models.</li>
                    <li>We do not use Gmail data for any purpose unrelated to providing or securing the user-facing mail features.</li>
                </ul>
                <div class="notice">
                    <strong>Google API Services disclosure:</strong>
                    Use of information received from Google APIs will adhere to the Google API Services User Data Policy,
                    including the Limited Use requirements.
                </div>

                <h2>4. Access to Connected Mailboxes</h2>
                <p>
                    Connected Gmail accounts are intended to be accessible only within the authorized account context of the
                    application. We use application-level access controls to limit mailbox visibility to authorized users.
                    We may access data internally only when necessary for security, abuse prevention, legal compliance,
                    or support requested by the customer.
                </p>

                <h2>5. Data Sharing</h2>
                <p>We may share data only in limited situations, such as:</p>
                <ul>
                    <li>With service providers and infrastructure vendors who help us host, secure, maintain, and operate the platform.</li>
                    <li>Within your organization and only to users authorized through the service's access controls.</li>
                    <li>To comply with law, regulation, court order, or lawful government request.</li>
                    <li>To protect rights, security, and integrity of the service, users, or the public.</li>
                    <li>In connection with a merger, acquisition, financing, or asset transfer, subject to applicable safeguards.</li>
                </ul>

                <h2>6. Data Retention</h2>
                <p>
                    We retain information for as long as needed to provide the service, maintain records, resolve disputes,
                    enforce agreements, comply with legal obligations, and support legitimate business operations.
                    Synced Gmail data may remain stored until the connected account is disconnected, the data is deleted,
                    or retention is required for legal or security reasons.
                </p>

                <h2>7. Security</h2>
                <p>
                    We use reasonable administrative, technical, and organizational safeguards to protect information,
                    including access controls, authentication safeguards, token protection, audit logging, and least-privilege practices.
                    No method of transmission or storage is completely secure, and we cannot guarantee absolute security.
                </p>

                <h2>8. Your Choices and Rights</h2>
                <ul>
                    <li>You may disconnect a connected Gmail account from the application, if that feature is available to your role.</li>
                    <li>You may revoke Google account access from your Google account permissions settings.</li>
                    <li>You may request access, correction, or deletion of personal data where legally applicable.</li>
                    <li>You may contact us to request account closure or data deletion support.</li>
                </ul>
                <p>
                    To request privacy assistance, contact
                    <a href="mailto:{{ config('legal.privacy_email') }}">{{ config('legal.privacy_email') }}</a>.
                </p>

                <h2>9. International Data Transfers</h2>
                <p>
                    Your information may be processed in countries other than your own. Where required, we will use
                    appropriate safeguards for cross-border data transfers.
                </p>

                <h2>10. Children's Privacy</h2>
                <p>
                    This service is intended for business and organizational use and is not directed to children.
                    We do not knowingly collect personal information from children.
                </p>

                <h2>11. Changes to this Policy</h2>
                <p>
                    We may update this Privacy Policy from time to time. The updated version will be posted on this page
                    with a revised effective date.
                </p>

                <h2>12. Contact Us</h2>
                <p>
                    If you have questions about this Privacy Policy or our data practices, contact us at
                    <a href="mailto:{{ config('legal.privacy_email') }}">{{ config('legal.privacy_email') }}</a>
                    or
                    <a href="{{ config('legal.website_url') }}">{{ config('legal.website_url') }}</a>.
                </p>
            </section>
        </div>
    </div>
</body>
</html>
