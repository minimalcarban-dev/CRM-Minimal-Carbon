@extends('layouts.admin')

@section('title', $email->subject)

@section('content')
    <div class="container-fluid mt-4">
        <div class="row">
            <!-- Sidebar -->
            <div class="col-md-3 col-lg-2">
                @include('email._sidebar')
            </div>

            <!-- Main Content -->
            <div class="col-md-9 col-lg-10">
                <div class="card border-0 shadow-sm mb-4">
                    <div class="card-header bg-white border-bottom py-3 d-flex justify-content-between align-items-center">
                        <div class="d-flex align-items-center">
                            <a href="{{ route('email.inbox', $account->id) }}" class="btn btn-light btn-sm me-3">
                                <i class="bi bi-arrow-left"></i>
                            </a>
                            <h5 class="mb-0 fw-bold">Message Details</h5>
                        </div>
                        <div class="d-flex gap-2">
                            <button class="btn btn-light btn-sm" id="btnPrintEmail" title="Print"><i
                                    class="bi bi-printer"></i></button>
                            <form id="deleteEmailForm"
                                action="{{ route('email.email.delete', [$account->id, $email->id]) }}" method="POST">
                                @csrf
                                @method('DELETE')
                                <button type="button" class="btn btn-light btn-sm text-danger" id="btnDeleteEmail"
                                    title="Delete">
                                    <i class="bi bi-trash"></i>
                                </button>
                            </form>
                        </div>
                    </div>

                    <div class="card-body p-4">
                        <div class="mb-4">
                            <h3 class="fw-bold mb-3">{{ $email->subject }}</h3>
                            <div class="d-flex justify-content-between align-items-start border-bottom pb-4 mb-4">
                                <div class="d-flex align-items-center">
                                    @php
                                        $domain = Str::after($email->from_email, '@');
                                        $parts = explode('.', $domain);
                                        $brandDomain = count($parts) > 2 ? $parts[count($parts) - 2] . '.' . $parts[count($parts) - 1] : $domain;
                                        $logoUrl = "https://logo.clearbit.com/{$brandDomain}";
                                        $fallbackUrl = "https://www.google.com/s2/favicons?domain={$brandDomain}&sz=128";
                                    @endphp
                                    <div class="bg-white rounded-circle d-flex align-items-center justify-content-center me-3 shadow-sm border"
                                        style="width: 48px; height: 48px; min-width: 48px; overflow: hidden;">
                                        <img src="{{ $logoUrl }}"
                                            alt="{{ strtoupper(substr($email->from_name ?: $email->from_email, 0, 1)) }}"
                                            style="width: 100%; height: 100%; object-fit: cover;"
                                            onerror="this.src='{{ $fallbackUrl }}'; this.onerror=function(){this.style.display='none'; this.nextElementSibling.style.display='flex';};">
                                        <span class="fw-bold fs-5 text-primary" style="display: none;">
                                            {{ strtoupper(substr($email->from_name ?: $email->from_email, 0, 1)) }}
                                        </span>
                                    </div>
                                    <div>
                                        <div class="fw-bold fs-5">{{ $email->from_name }} <span class="text-muted fw-normal"
                                                style="font-size: 0.9rem;">&lt;{{ $email->from_email }}&gt;</span></div>
                                        <div class="text-muted" style="font-size: 0.85rem;">to
                                            {{ $email->to_recipients ?: 'me' }}
                                        </div>
                                    </div>
                                </div>
                                <div class="text-end">
                                    <div class="text-muted" style="font-size: 0.9rem;">
                                        {{ $email->received_at->format('M d, Y, h:i A') }}
                                    </div>
                                    <div class="text-muted small">({{ $email->received_at->diffForHumans() }})</div>
                                </div>
                            </div>
                        </div>

                        <div class="email-body mb-5" style="line-height: 1.6; color: #333;">
                            @if ($email->body_html)
                                {!! $email->body_html !!}
                            @else
                                <pre style="white-space: pre-wrap; font-family: inherit;">{{ $email->body_plain }}</pre>
                            @endif
                        </div>

                        @if ($email->has_attachments && $email->attachments->count() > 0)
                            <div class="attachments border-top pt-4">
                                <h6 class="fw-bold mb-3"><i class="bi bi-paperclip me-2"></i>Attachments
                                    ({{ $email->attachments->count() }})</h6>
                                <div class="row">
                                    @foreach ($email->attachments as $attachment)
                                        <div class="col-md-3 mb-3">
                                            <div class="card border bg-light h-100 attachment-card">
                                                <div class="card-body p-3">
                                                    <div class="d-flex align-items-center mb-2">
                                                        <i class="bi bi-file-earmark-text fs-4 me-3 text-primary"></i>
                                                        <div class="text-truncate">
                                                            <div class="fw-bold small text-truncate">
                                                                {{ $attachment->filename }}
                                                            </div>
                                                            <small class="text-muted">{{ round($attachment->size_bytes / 1024, 1) }}
                                                                KB</small>
                                                        </div>
                                                    </div>
                                                    <div class="d-grid">
                                                        <button class="btn btn-white btn-sm border shadow-sm" disabled>
                                                            <i class="bi bi-download me-1"></i>Download
                                                        </button>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        @endif
                    </div>

                    <div class="card-footer bg-white py-4">
                        <div class="d-flex gap-2">
                            <button class="btn btn-outline-primary" id="btnReply"><i
                                    class="bi bi-reply me-2"></i>Reply</button>
                            <button class="btn btn-outline-secondary" id="btnForward"><i
                                    class="bi bi-arrow-right me-2"></i>Forward</button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Professional Print Container (Hidden by default, shown only in @media print) -->
    <div class="email-print-container">
        <div class="print-header">
            <h1 class="print-subject">{{ $email->subject }}</h1>
            <div class="print-meta">
                <div class="meta-row">
                    <span class="meta-label">From:</span>
                    <span class="meta-value">{{ $email->from_name }} <{{ $email->from_email }}></span>
                </div>
                <div class="meta-row">
                    <span class="meta-label">To:</span>
                    <span class="meta-value">{{ $email->to_recipients ?: 'me' }}</span>
                </div>
                <div class="meta-row">
                    <span class="meta-label">Date:</span>
                    <span class="meta-value">{{ $email->received_at->format('M d, Y, h:i A') }}</span>
                </div>
            </div>
        </div>
        <div class="print-body">
            @if ($email->body_html)
                {!! $email->body_html !!}
            @else
                <pre style="white-space: pre-wrap; font-family: inherit;">{{ $email->body_plain }}</pre>
            @endif
        </div>
    </div>

    @include('email._compose_modal')

    <style>
        :root {
            --primary: #6366f1;
            --primary-dark: #4f46e5;
            --success: #10b981;
            --warning: #f59e0b;
            --danger: #ef4444;
            --info: #3b82f6;
            --dark: #1e293b;
            --gray: #64748b;
            --light-gray: #f1f5f9;
            --border: #e2e8f0;
            --shadow: rgba(0, 0, 0, 0.05);
            --shadow-md: rgba(0, 0, 0, 0.1);
        }

        .email-body img {
            max-width: 100%;
            height: auto;
        }

        .attachment-card:hover {
            border-color: #0d6efd !important;
            cursor: pointer;
        }

        /* Modal Styles copy from index */
        .modal-overlay {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(15, 23, 42, 0.6);
            backdrop-filter: blur(4px);
            display: none;
            align-items: center;
            justify-content: center;
            z-index: 1000;
            padding: 1.5rem;
        }

        .modal-content-card {
            background: white;
            width: 100%;
            max-width: 600px;
            border-radius: 20px;
            box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.25);
            overflow: hidden;
            animation: modalFadeIn 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        }

        @keyframes modalFadeIn {
            from {
                opacity: 0;
                transform: translateY(20px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .modal-header {
            padding: 1.5rem;
            background: linear-gradient(135deg, var(--light-gray), white);
            border-bottom: 2px solid var(--border);
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .modal-title {
            margin: 0;
            font-size: 1.25rem;
            font-weight: 700;
            color: var(--dark);
            display: flex;
            align-items: center;
            gap: 0.75rem;
        }

        .modal-title i {
            color: var(--primary);
        }

        .btn-close-modal {
            background: white;
            border: 2px solid var(--border);
            width: 36px;
            height: 36px;
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            transition: all 0.2s;
            color: var(--gray);
        }

        .btn-close-modal:hover {
            border-color: var(--danger);
            color: var(--danger);
            background: rgba(239, 68, 68, 0.05);
        }

        .modal-body {
            padding: 1.5rem;
            display: flex;
            flex-direction: column;
            gap: 1.25rem;
        }

        .form-group-custom {
            display: flex;
            flex-direction: column;
            gap: 0.5rem;
        }

        .form-group-custom label {
            font-size: 0.875rem;
            font-weight: 600;
            color: var(--gray);
            padding-left: 0.25rem;
        }

        .form-control-custom {
            padding: 0.75rem 1rem;
            border: 2px solid var(--border);
            border-radius: 10px;
            font-size: 0.95rem;
            transition: all 0.2s;
            width: 100%;
        }

        .form-control-custom:focus {
            outline: none;
            border-color: var(--primary);
            box-shadow: 0 0 0 4px rgba(99, 102, 241, 0.1);
        }

        .textarea-custom {
            min-height: 200px;
            resize: vertical;
        }

        .modal-footer {
            padding: 1.25rem 1.5rem;
            background: #f8fafc;
            border-top: 2px solid var(--border);
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .btn-primary-custom {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            background: linear-gradient(135deg, var(--primary), var(--primary-dark));
            color: white !important;
            padding: 0.75rem 1.5rem;
            border-radius: 10px;
            text-decoration: none;
            font-weight: 600;
            transition: all 0.2s;
            border: none;
            cursor: pointer;
            box-shadow: 0 4px 12px rgba(99, 102, 241, 0.3);
        }

        .btn-primary-custom:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 16px rgba(99, 102, 241, 0.4);
            filter: brightness(1.1);
        }

        .btn-secondary-custom {
            background: white;
            color: var(--gray);
            border: 2px solid var(--border);
            padding: 0.75rem 1.25rem;
            border-radius: 10px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.2s;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .btn-secondary-custom:hover {
            border-color: var(--gray);
            color: var(--dark);
            background: var(--light-gray);
        }

        @media print {

            .col-md-3,
            .col-lg-2,
            .btn-light,
            .d-flex.gap-2,
            .card-footer,
            .breadcrumb-nav {
                display: none !important;
            }

            .card {
                border: none !important;
                box-shadow: none !important;
            }

            .container-fluid {
                padding: 0 !important;
            }
        }
    </style>

    @push('scripts')
        <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
        <script>
            document.addEventListener('DOMContentLoaded', function () {
                // --- Direct Professional Print ---
                        const btnPrint = document.getElementById('btnPrintEmail');
                        if (btnPrint) {
                            btnPrint.addEventListener('click', function () {
                                window.print();
                            });
                        }

                        // --- Delete Functionality ---
                        const btnDelete = document.getElementById('btnDeleteEmail');
                        const deleteForm = document.getElementById('deleteEmailForm');

                        if (btnDelete) {
                            btnDelete.addEventListener('click', function () {
                                Swal.fire({
                                    title: 'Move to Trash?',
                                    text: "You can recover this from the Trash folder later.",
                                    icon: 'warning',
                                    showCancelButton: true,
                                    confirmButtonColor: '#ef4444',
                                    cancelButtonColor: '#64748b',
                                    confirmButtonText: 'Yes, delete it!',
                                    cancelButtonText: 'Cancel',
                                    reverseButtons: true
                                }).then((result) => {
                                    if (result.isConfirmed) {
                                        performDelete();
                                    }
                                });
                            });
                        }

                        async function performDelete() {
                            const formData = new FormData(deleteForm);

                            try {
                                const response = await fetch(deleteForm.action, {
                                    method: 'POST',
                                    headers: {
                                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                                        'Accept': 'application/json'
                                    },
                                    body: formData
                                });

                                const data = await response.json();

                                if (data.success) {
                                    Swal.fire({
                                        title: 'Deleted!',
                                        text: 'Your email has been moved to trash.',
                                        icon: 'success',
                                        timer: 1500,
                                        showConfirmButton: false
                                    }).then(() => {
                                        window.location.href = "{{ route('email.inbox', $account->id) }}";
                                    });
                                } else {
                                    Swal.fire('Error', data.message || 'Failed to delete email', 'error');
                                }
                            } catch (error) {
                                console.error('Delete error:', error);
                                Swal.fire('Error', 'An unexpected error occurred', 'error');
                            }
                        }

                        // --- Compose Modal Logic (Reply/Forward) ---
                        const composeModal = document.getElementById('composeModal');
                        const btnCloseCompose = document.getElementById('btnCloseCompose');
                        const composeForm = document.getElementById('composeForm');
                        const btnReply = document.getElementById('btnReply');
                        const btnForward = document.getElementById('btnForward');
                        const modalTitle = document.getElementById('composeModalTitle');
                        const inputTo = document.getElementById('to');
                        const inputSubject = document.getElementById('subject');
                        const inputBody = document.getElementById('body');

                        function openCompose(type) {
                            const originalDate = '{{ $email->received_at->format('M d, Y, h:i A') }}';
                            const originalFrom = '{{ $email->from_name }} <{{ $email->from_email }}>';
                            const originalSubject = '{{ $email->subject }}';
                            const quotedBody = `\n\n--- Original Message ---\nFrom: ${originalFrom}\nDate: ${originalDate}\nSubject: ${originalSubject}\n\n{{ trim(strip_tags($email->body_plain)) }}`;

                            if (type === 'reply') {
                                modalTitle.innerText = 'Reply to Message';
                                inputTo.value = '{{ $email->from_email }}';
                                inputSubject.value = 'Re: ' + originalSubject;
                                inputBody.value = quotedBody;
                            } else if (type === 'forward') {
                                modalTitle.innerText = 'Forward Message';
                                inputTo.value = '';
                                inputSubject.value = 'Fwd: ' + originalSubject;
                                inputBody.value = quotedBody;
                            }

                            composeModal.style.display = 'flex';
                        }

                        if (btnReply) btnReply.addEventListener('click', () => openCompose('reply'));
                        if (btnForward) btnForward.addEventListener('click', () => openCompose('forward'));

                        if (btnCloseCompose) {
                            btnCloseCompose.addEventListener('click', () => {
                                composeModal.style.display = 'none';
                            });
                        }

                        window.addEventListener('click', (e) => {
                            if (e.target === composeModal) {
                                composeModal.style.display = 'none';
                            }
                        });

                        // Handle Sending
                        composeForm.addEventListener('submit', async function (e) {
                            e.preventDefault();
                            const btnSend = document.getElementById('btnSend');
                            const originalContent = btnSend.innerHTML;

                            btnSend.disabled = true;
                            btnSend.innerHTML = '<i class="bi bi-hourglass-split"></i> Sending...';

                            const formData = new FormData(this);

                            try {
                                const response = await fetch(`/admin/email/{{ $account->id }}/compose/send`, {
                                    method: 'POST',
                                    headers: {
                                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                                        'Accept': 'application/json'
                                    },
                                    body: formData
                                });

                                const data = await response.json();
                                if (data.success) {
                                    Swal.fire({
                                        title: 'Sent!',
                                        text: 'Email sent successfully!',
                                        icon: 'success',
                                        timer: 2000,
                                        showConfirmButton: false
                                    }).then(() => {
                                        composeModal.style.display = 'none';
                                        composeForm.reset();
                                    });
                                } else {
                                    Swal.fire('Error', data.message, 'error');
                                }
                            } catch (error) {
                                console.error('Send error:', error);
                                Swal.fire('Error', 'Failed to send email.', 'error');
                            } finally {
                                btnSend.disabled = false;
                                btnSend.innerHTML = originalContent;
                            }
                        });

                        // Handle Draft Saving
                        const btnSaveDraft = document.getElementById('btnSaveDraft');
                        if (btnSaveDraft) {
                            btnSaveDraft.addEventListener('click', async function () {
                                const originalContent = this.innerHTML;
                                this.disabled = true;
                                this.innerHTML = '<i class="bi bi-hourglass-split"></i> Saving...';

                                const formData = new FormData(composeForm);

                                try {
                                    const response = await fetch(`/admin/email/{{ $account->id }}/compose/draft`, {
                                        method: 'POST',
                                        headers: {
                                            'X-CSRF-TOKEN': '{{ csrf_token() }}',
                                            'Accept': 'application/json'
                                        },
                                        body: formData
                                    });

                                    const data = await response.json();
                                    if (data.success) {
                                        Swal.fire({
                                            title: 'Draft Saved',
                                            text: 'Your message has been saved as a draft.',
                                            icon: 'success',
                                            timer: 1500,
                                            showConfirmButton: false
                                        });
                                    } else {
                                        Swal.fire('Error', 'Error saving draft: ' + data.message, 'error');
                                    }
                                } catch (error) {
                                    console.error('Draft error:', error);
                                    Swal.fire('Error', 'Failed to save draft', 'error');
                                } finally {
                                    this.disabled = false;
                                    this.innerHTML = originalContent;
                                }
                            });
                        }
                    });
                </script>
    @endpush
@endsection
```