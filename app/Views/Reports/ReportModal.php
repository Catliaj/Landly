<?php
/**
 * Report Modal - Shared modal for both listing and message reports
 * Include this in your view with: <?= view('Reports/ReportModal') ?>
 */
?>

<!-- Report Modal -->
<div class="modal fade" id="reportModal" tabindex="-1" aria-labelledby="reportModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content" style="border: 1px solid rgba(149, 213, 178, 0.2); border-radius: 12px; background: #0d2818; color: #fefae0; box-shadow: 0 20px 60px rgba(13, 40, 24, 0.45);">
            <div class="modal-header border-0" style="background: linear-gradient(135deg, #183127 0%, #234236 100%); padding: 24px 24px 18px;">
                <h5 class="modal-title" id="reportModalLabel" style="color: #fefae0; font-weight: 700; font-size: 1.15rem;">Report Issue</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            
            <div class="modal-body" style="padding: 24px; background: linear-gradient(180deg, rgba(13, 40, 24, 0.98) 0%, rgba(27, 63, 40, 0.96) 100%);">
                <form id="reportForm">
                    <input type="hidden" id="reportType" name="report_type" value="">
                    <input type="hidden" id="reportTargetId" name="target_id" value="">
                    
                    <!-- Reason Selection -->
                    <div class="mb-3">
                        <label for="reportReason" class="form-label" style="color: #fefae0; font-weight: 600; font-size: 0.95rem;">
                            Reason <span style="color: #e74c3c;">*</span>
                        </label>
                        <select class="form-select" id="reportReason" name="reason" required style="border: 1px solid rgba(149, 213, 178, 0.22); padding: 12px 14px; border-radius: 10px; background: rgba(255, 255, 255, 0.95); color: #0f1b1b;">
                            <option value="">-- Select a reason --</option>
                        </select>
                        <small id="reasonError" class="text-danger" style="display: none; margin-top: 5px;"></small>
                    </div>

                    <!-- Other Reason (conditional) -->
                    <div class="mb-3" id="otherReasonSection" style="display: none;">
                        <label for="otherReason" class="form-label" style="color: #fefae0; font-weight: 600; font-size: 0.95rem;">
                            Please describe the issue <span style="color: #e74c3c;">*</span>
                        </label>
                        <textarea class="form-control" id="otherReason" name="other_reason" rows="3" placeholder="Describe the issue..." style="border: 1px solid rgba(149, 213, 178, 0.22); padding: 12px 14px; border-radius: 10px; background: rgba(255, 255, 255, 0.95); color: #0f1b1b; font-size: 0.9rem; resize: vertical;"></textarea>
                        <small id="otherReasonError" class="text-danger" style="display: none; margin-top: 5px;"></small>
                    </div>

                    <!-- Description -->
                    <div class="mb-3">
                        <label for="reportDescription" class="form-label" style="color: #fefae0; font-weight: 600; font-size: 0.95rem;">
                            Additional details (optional)
                        </label>
                        <textarea class="form-control" id="reportDescription" name="description" rows="3" placeholder="Provide any additional information..." style="border: 1px solid rgba(149, 213, 178, 0.22); padding: 12px 14px; border-radius: 10px; background: rgba(255, 255, 255, 0.95); color: #0f1b1b; font-size: 0.9rem; resize: vertical; max-height: 120px;"></textarea>
                        <small style="color: rgba(254, 250, 224, 0.72); font-size: 0.8rem;">Max 1000 characters</small>
                    </div>

                    <!-- Evidence Upload (Listing reports only) -->
                    <div class="mb-3" id="evidenceSection" style="display: none;">
                        <label for="reportEvidence" class="form-label" style="color: #fefae0; font-weight: 600; font-size: 0.95rem;">
                            Upload evidence (optional)
                        </label>
                        <div class="file-upload-small">
                            <input type="file" id="reportEvidence" name="evidence" accept="image/*,.pdf" style="display: none;">
                            <label for="reportEvidence" style="cursor: pointer; display: block; padding: 15px; border: 1px dashed rgba(149, 213, 178, 0.32); border-radius: 10px; text-align: center; background: rgba(149, 213, 178, 0.06); transition: all 0.3s ease;">
                                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="var(--accent)" stroke-width="2" style="display: inline; margin-bottom: 5px;">
                                    <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"></path>
                                    <polyline points="17 8 12 3 7 8"></polyline>
                                    <line x1="12" y1="3" x2="12" y2="15"></line>
                                </svg>
                                <br>
                                <small style="color: rgba(254, 250, 224, 0.82);">Click to upload or drag & drop</small>
                                <br>
                                <small style="color: rgba(254, 250, 224, 0.58); font-size: 0.75rem;">Images (JPG, PNG, GIF) or PDF • Max 5MB</small>
                            </label>
                            <small id="evidenceFileName" style="display: none; color: var(--accent); margin-top: 8px; display: block;"></small>
                        </div>
                    </div>

                    <!-- Error Messages -->
                    <div id="reportErrorBox" class="alert alert-danger" role="alert" style="display: none; margin-bottom: 15px; border-radius: 10px;"></div>
                </form>
            </div>

            <div class="modal-footer border-0" style="padding: 18px 24px; background: rgba(13, 40, 24, 0.98);">
                <button type="button" class="btn btn-outline-light" data-bs-dismiss="modal" style="border-radius: 10px; padding: 10px 22px;">Cancel</button>
                <button type="button" class="btn btn-primary" id="submitReportBtn" style="border-radius: 10px; padding: 10px 22px; background: var(--accent); border: none; color: #0d2818; font-weight: 700;">Submit Report</button>
            </div>
        </div>
    </div>
</div>

<style>
    #reportModal {
        z-index: 7700;
    }

    #reportModal .modal-content {
        box-shadow: 0 20px 60px rgba(24, 49, 39, 0.15);
    }

    body.report-modal-open #propertyModal {
        pointer-events: none;
    }

    #reportModal .form-select,
    #reportModal .form-control {
        box-shadow: none;
    }

    #reportModal .form-select:focus,
    #reportModal .form-control:focus {
        border-color: var(--accent);
        box-shadow: 0 0 0 0.2rem rgba(149, 213, 178, 0.18);
    }

    .file-upload-small {
        margin-top: 10px;
    }

    .file-upload-small label:hover {
        background: rgba(202, 164, 110, 0.1);
        border-color: var(--accent);
    }

    .text-danger {
        font-size: 0.85rem;
    }
</style>

<script>
    // Report Modal Management
    (function() {
        if (window.__landlyReportModalInitialized) {
            return;
        }
        window.__landlyReportModalInitialized = true;

        const reportModalInstances = Array.from(document.querySelectorAll('#reportModal'));
        const reportModal = reportModalInstances[0] || null;
        if (!reportModal) {
            return;
        }

        reportModalInstances.slice(1).forEach((duplicateModal) => duplicateModal.remove());

        if (reportModal.parentElement !== document.body) {
            document.body.appendChild(reportModal);
        }

        const reportForm = reportModal.querySelector('#reportForm');
        const reportReasonSelect = reportModal.querySelector('#reportReason');
        const otherReasonSection = reportModal.querySelector('#otherReasonSection');
        const otherReasonInput = reportModal.querySelector('#otherReason');
        const submitReportBtn = reportModal.querySelector('#submitReportBtn');
        const reportErrorBox = reportModal.querySelector('#reportErrorBox');
        const evidenceSection = reportModal.querySelector('#evidenceSection');
        const reportEvidenceInput = reportModal.querySelector('#reportEvidence');
        const evidenceFileName = reportModal.querySelector('#evidenceFileName');

        function cleanupModalState() {
            document.body.classList.remove('report-modal-open');
            document.querySelectorAll('.modal-backdrop').forEach((backdrop) => backdrop.remove());

            if (!document.querySelector('.modal.show')) {
                document.body.classList.remove('modal-open');
                document.body.style.removeProperty('overflow');
                document.body.style.removeProperty('padding-right');
            }
        }

        // Clear form when modal is hidden
        reportModal.addEventListener('hidden.bs.modal', () => {
            cleanupModalState();
            reportForm.reset();
            otherReasonSection.style.display = 'none';
            evidenceSection.style.display = 'none';
            reportErrorBox.style.display = 'none';
            submitReportBtn.disabled = false;
            clearErrors();
        });

        // Show/hide "Other" reason section
        reportReasonSelect.addEventListener('change', () => {
            const isOther = reportReasonSelect.value === 'Other';
            otherReasonSection.style.display = isOther ? 'block' : 'none';
            if (!isOther) {
                clearError('otherReasonError');
            }
        });

        // File upload handling
        reportEvidenceInput.addEventListener('change', (e) => {
            if (e.target.files.length > 0) {
                const fileName = e.target.files[0].name;
                const fileSize = (e.target.files[0].size / 1024 / 1024).toFixed(2);
                evidenceFileName.textContent = `✓ ${fileName} (${fileSize}MB)`;
                evidenceFileName.style.display = 'block';
                evidenceFileName.style.color = 'var(--accent)';
            }
        });

        // Drag and drop
        const fileLabel = reportModal.querySelector('.file-upload-small label');
        fileLabel?.addEventListener('dragover', (e) => {
            e.preventDefault();
            fileLabel.style.background = 'rgba(202, 164, 110, 0.15)';
            fileLabel.style.borderColor = 'var(--accent)';
        });

        fileLabel?.addEventListener('dragleave', () => {
            fileLabel.style.background = 'rgba(202, 164, 110, 0.05)';
            fileLabel.style.borderColor = 'rgba(15, 27, 27, 0.2)';
        });

        fileLabel?.addEventListener('drop', (e) => {
            e.preventDefault();
            fileLabel.style.background = 'rgba(202, 164, 110, 0.05)';
            fileLabel.style.borderColor = 'rgba(15, 27, 27, 0.2)';
            if (e.dataTransfer.files.length > 0) {
                reportEvidenceInput.files = e.dataTransfer.files;
                reportEvidenceInput.dispatchEvent(new Event('change'));
            }
        });

        // Submit report
        submitReportBtn.addEventListener('click', async () => {
            if (!validateForm()) {
                return;
            }

            submitReportBtn.disabled = true;
            const originalText = submitReportBtn.textContent;
            submitReportBtn.textContent = 'Submitting...';

            const formData = new FormData(reportForm);
            const reportType = document.getElementById('reportType').value;
            const targetId = document.getElementById('reportTargetId').value;

            // Map target_id to correct field based on report type
            if (reportType === 'listing') {
                formData.append('listing_id', targetId);
            } else if (reportType === 'message') {
                formData.append('message_id', targetId);
            }

            try {
                const response = await fetch(`<?= base_url('reports') ?>/${reportType}`, {
                    method: 'POST',
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest'
                    },
                    body: formData
                });

                const data = await response.json();

                if (response.ok) {
                    showSuccess(data.message || 'Report submitted successfully.');
                    reportForm.reset();
                    setTimeout(() => {
                        const modalInstance = bootstrap.Modal.getInstance(reportModal);
                        if (modalInstance) {
                            modalInstance.hide();
                        }
                        cleanupModalState();
                    }, 1500);
                } else {
                    showError(data.message || 'Failed to submit report. Please try again.');
                }
            } catch (error) {
                console.error('Report submission error:', error);
                showError('An error occurred. Please try again.');
            } finally {
                submitReportBtn.disabled = false;
                submitReportBtn.textContent = originalText;
            }
        });

        // Form validation
        function validateForm() {
            clearErrors();
            let isValid = true;

            const reason = reportReasonSelect.value;
            if (!reason) {
                showFieldError('reasonError', 'Please select a reason');
                isValid = false;
            }

            if (reason === 'Other' && !otherReasonInput.value.trim()) {
                showFieldError('otherReasonError', 'Please describe the issue');
                isValid = false;
            }

            return isValid;
        }

        function showFieldError(elementId, message) {
            const errorElement = document.getElementById(elementId);
            errorElement.textContent = message;
            errorElement.style.display = 'block';
        }

        function clearError(elementId) {
            const errorElement = document.getElementById(elementId);
            if (errorElement) {
                errorElement.style.display = 'none';
                errorElement.textContent = '';
            }
        }

        function clearErrors() {
            clearError('reasonError');
            clearError('otherReasonError');
        }

        function showError(message) {
            reportErrorBox.textContent = message;
            reportErrorBox.style.display = 'block';
        }

        function showSuccess(message) {
            // You can integrate with SweetAlert2 if available
            if (typeof Swal !== 'undefined') {
                Swal.fire({
                    icon: 'success',
                    title: 'Report Submitted',
                    text: message,
                    customClass: {
                        container: 'sweet-alert-container',
                        popup: 'sweet-alert-popup',
                        header: 'sweet-alert-header',
                        title: 'sweet-alert-title',
                        content: 'sweet-alert-content',
                        confirmButton: 'sweet-alert-button'
                    }
                });
            } else {
                alert(message);
            }
        }

        // Global function to open report modal
        window.openReportModal = function(reportType, targetId, reasons) {
            if (reportModal.parentElement !== document.body) {
                document.body.appendChild(reportModal);
            }

            document.getElementById('reportType').value = reportType;
            document.getElementById('reportTargetId').value = targetId;
            
            // Clear and populate reason dropdown
            reportReasonSelect.innerHTML = '<option value="">-- Select a reason --</option>';
            reasons.forEach(reason => {
                const option = document.createElement('option');
                option.value = reason;
                option.textContent = reason;
                reportReasonSelect.appendChild(option);
            });

            // Show/hide evidence section based on report type
            evidenceSection.style.display = reportType === 'listing' ? 'block' : 'none';

            document.body.classList.add('report-modal-open');

            // Show modal
            const modal = bootstrap.Modal.getOrCreateInstance(reportModal);
            modal.show();

            window.setTimeout(() => {
                const backdrops = document.querySelectorAll('.modal-backdrop');
                const topBackdrop = backdrops[backdrops.length - 1];
                if (topBackdrop) {
                    topBackdrop.style.zIndex = '7690';
                }
            }, 0);
        };

        window.getActiveMessageIdForReport = function() {
            const candidates = ['sellerChatBody', 'buyerChatBody'];

            for (const chatBodyId of candidates) {
                const chatBody = document.getElementById(chatBodyId);
                if (!chatBody) {
                    continue;
                }

                const messages = Array.from(chatBody.querySelectorAll('.chat-message.received[data-message-id]'));
                const latestMessage = messages[messages.length - 1];
                const messageId = Number(latestMessage?.dataset?.messageId || 0);
                if (messageId > 0) {
                    return messageId;
                }
            }

            return 0;
        };

        window.openMessageReportModal = function() {
            const messageReportReasons = [
                'Harassment',
                'Scam or fraud',
                'Spam',
                'Offensive content',
                'Fake information',
                'Unsafe payment request',
                'Other'
            ];
            const messageId = window.getActiveMessageIdForReport();

            if (messageId > 0) {
                window.openReportModal('message', messageId, messageReportReasons);
                return;
            }

            alert('Please select a conversation with a message from the other user first.');
        };
    })();
</script>
