<!-- Company Details Modal -->
<div id="companyModal" class="modal fade" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-scrollable">
        <div class="modal-content">
            <!-- Modal Header -->
            <div class="modal-header">
                <div class="modal-title-wrapper">
                    <div id="modalCompanyLogo" class="modal-company-logo">
                        <i class="bi bi-building"></i>
                    </div>
                    <div>
                        <h5 class="modal-title" id="modalCompanyName"></h5>
                        <small id="modalCompanyStatus" class="text-muted"></small>
                    </div>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>

            <!-- Modal Body -->
            <div class="modal-body">
                <!-- Basic Information -->
                <div class="modal-section">
                    <h6 class="modal-section-title">
                        <i class="bi bi-info-circle"></i> Basic Information
                    </h6>
                    <div class="modal-grid">
                        <div class="modal-item">
                            <label class="modal-label">Company Name</label>
                            <p id="modalName" class="modal-value">-</p>
                        </div>
                        <div class="modal-item">
                            <label class="modal-label">Email Address</label>
                            <p id="modalEmail" class="modal-value">-</p>
                        </div>
                        <div class="modal-item">
                            <label class="modal-label">Phone Number</label>
                            <p id="modalPhone" class="modal-value">-</p>
                        </div>
                    </div>
                </div>

                <!-- Tax & Regulatory Information -->
                <div class="modal-section">
                    <h6 class="modal-section-title">
                        <i class="bi bi-receipt"></i> Tax & Regulatory Information
                    </h6>
                    <div class="modal-grid">
                        <div class="modal-item">
                            <label class="modal-label">GST Number</label>
                            <p id="modalGstNo" class="modal-value">-</p>
                        </div>
                        <div class="modal-item">
                            <label class="modal-label">EIN/CIN Number</label>
                            <p id="modalEinCinNo" class="modal-value">-</p>
                        </div>
                        <div class="modal-item">
                            <label class="modal-label">State Code</label>
                            <p id="modalStateCode" class="modal-value">-</p>
                        </div>
                    </div>
                </div>

                <!-- Location & Address -->
                <div class="modal-section">
                    <h6 class="modal-section-title">
                        <i class="bi bi-geo-alt"></i> Location & Address
                    </h6>
                    <div class="modal-grid">
                        <div class="modal-item full-width">
                            <label class="modal-label">Address</label>
                            <p id="modalAddress" class="modal-value">-</p>
                        </div>
                        <div class="modal-item">
                            <label class="modal-label">Country</label>
                            <p id="modalCountry" class="modal-value">-</p>
                        </div>
                    </div>
                </div>

                <!-- Bank Account Details -->
                <div class="modal-section">
                    <h6 class="modal-section-title">
                        <i class="bi bi-bank2"></i> Bank Account Details
                    </h6>
                    <div class="modal-grid">
                        <div class="modal-item">
                            <label class="modal-label">Bank Name</label>
                            <p id="modalBankName" class="modal-value">-</p>
                        </div>
                        <div class="modal-item">
                            <label class="modal-label">Account Holder Name</label>
                            <p id="modalAccountHolderName" class="modal-value">-</p>
                        </div>
                        <div class="modal-item">
                            <label class="modal-label">Account Number</label>
                            <p id="modalAccountNo" class="modal-value">-</p>
                        </div>
                        <div class="modal-item">
                            <label class="modal-label">IFSC Code</label>
                            <p id="modalIfscCode" class="modal-value">-</p>
                        </div>
                    </div>
                </div>

                <!-- International Bank Details -->
                <div class="modal-section">
                    <h6 class="modal-section-title">
                        <i class="bi bi-lightning"></i> International Bank Details
                    </h6>
                    <div class="modal-grid">
                        <div class="modal-item">
                            <label class="modal-label">IBAN</label>
                            <p id="modalIban" class="modal-value">-</p>
                        </div>
                        <div class="modal-item">
                            <label class="modal-label">SWIFT Code</label>
                            <p id="modalSwiftCode" class="modal-value">-</p>
                        </div>
                        <div class="modal-item">
                            <label class="modal-label">Sort Code</label>
                            <p id="modalSortCode" class="modal-value">-</p>
                        </div>
                        <div class="modal-item">
                            <label class="modal-label">AD Code</label>
                            <p id="modalAdCode" class="modal-value">-</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Modal Footer -->
            <div class="modal-footer">
                <a id="modalEditBtn" href="#" class="btn btn-primary">
                    <i class="bi bi-pencil"></i> Edit
                </a>
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                    <i class="bi bi-x-circle"></i> Close
                </button>
            </div>
        </div>
    </div>
</div>

<style>
    .modal-title-wrapper {
        display: flex;
        align-items: center;
        gap: 1rem;
        flex: 1;
    }

    .modal-company-logo {
        width: 50px;
        height: 50px;
        border-radius: 8px;
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        display: flex;
        align-items: center;
        justify-content: center;
        color: white;
        font-size: 1.5rem;
        overflow: hidden;
        flex-shrink: 0;
    }

    .modal-company-logo img {
        width: 100%;
        height: 100%;
        object-fit: cover;
    }

    .modal-section {
        margin-bottom: 2rem;
        padding-bottom: 1.5rem;
        border-bottom: 1px solid #e9ecef;
    }

    .modal-section:last-child {
        border-bottom: none;
        margin-bottom: 0;
        padding-bottom: 0;
    }

    .modal-section-title {
        font-size: 0.95rem;
        font-weight: 600;
        color: #2c3e50;
        margin-bottom: 1rem;
        display: flex;
        align-items: center;
        gap: 0.5rem;
    }

    .modal-section-title i {
        color: #667eea;
    }

    .modal-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
        gap: 1.5rem;
    }

    .modal-item {
        display: flex;
        flex-direction: column;
    }

    .modal-item.full-width {
        grid-column: 1 / -1;
    }

    .modal-label {
        font-size: 0.85rem;
        font-weight: 600;
        color: #666;
        margin-bottom: 0.5rem;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }

    .modal-value {
        font-size: 0.95rem;
        color: #2c3e50;
        margin: 0;
        word-break: break-word;
    }

    .modal-value.text-muted {
        color: #999;
    }
</style>

<script>
    function showCompanyModal(companyId) {
        // Fetch company data via AJAX
        fetch(`/admin/companies/${companyId}`)
            .then(response => response.json())
            .then(data => {
                // Populate modal with company data
                document.getElementById('modalName').textContent = data.name || '-';
                document.getElementById('modalCompanyName').textContent = data.name || 'Company';
                document.getElementById('modalEmail').textContent = data.email || '-';
                document.getElementById('modalPhone').textContent = data.phone || '-';
                document.getElementById('modalGstNo').textContent = data.gst_no || '-';
                document.getElementById('modalEinCinNo').textContent = data.ein_cin_no || '-';
                document.getElementById('modalStateCode').textContent = data.state_code || '-';
                document.getElementById('modalAddress').textContent = data.address || '-';
                document.getElementById('modalCountry').textContent = data.country || '-';
                document.getElementById('modalBankName').textContent = data.bank_name || '-';
                document.getElementById('modalAccountHolderName').textContent = data.account_holder_name || '-';
                document.getElementById('modalAccountNo').textContent = data.account_no || '-';
                document.getElementById('modalIfscCode').textContent = data.ifsc_code || '-';
                document.getElementById('modalIban').textContent = data.iban || '-';
                document.getElementById('modalSwiftCode').textContent = data.swift_code || '-';
                document.getElementById('modalSortCode').textContent = data.sort_code || '-';
                document.getElementById('modalAdCode').textContent = data.ad_code || '-';

                // Set status badge
                const statusBadge = document.getElementById('modalCompanyStatus');
                if (data.status === 'active') {
                    statusBadge.innerHTML = '<span class="badge bg-success"><i class="bi bi-check-circle"></i> Active</span>';
                } else {
                    statusBadge.innerHTML = '<span class="badge bg-secondary"><i class="bi bi-pause-circle"></i> Inactive</span>';
                }

                // Set logo or initials
                const logoElement = document.getElementById('modalCompanyLogo');
                if (data.logo) {
                    logoElement.innerHTML = `<img src="${data.logo}" alt="${data.name}">`;
                } else {
                    logoElement.innerHTML = `<span style="font-size: 1.5rem; font-weight: bold;">${data.name.substring(0, 2).toUpperCase()}</span>`;
                }

                // Set edit button link
                document.getElementById('modalEditBtn').href = `/admin/companies/${companyId}/edit`;

                // Show modal
                const modal = new bootstrap.Modal(document.getElementById('companyModal'));
                modal.show();
            })
            .catch(error => console.error('Error:', error));
    }
</script>
