"use strict";

// Verification Form Handler
(function () {
    const form = $('#verification-form');
    const submitBtn = $('#verify-submit-btn');
    const certificateInput = $('#certificate-number-input');
    const resultDiv = $('#verification-result');
    const scanBtn = $('#scan-qr-btn');
    const scannerModal = $('#qr-scanner-modal');
    
    let html5QrCode = null;

    // Form submission
    form.on('submit', function (e) {
        e.preventDefault();

        const certificateNumber = certificateInput.val().trim();

        if (!certificateNumber) {
            Swal.fire({
                text: "Please enter a certificate number",
                icon: "warning",
                buttonsStyling: false,
                confirmButtonText: "Ok, got it!",
                customClass: {
                    confirmButton: "btn fw-bold btn-primary",
                }
            });
            return;
        }

        verifyCertificate(certificateNumber);
    });

    // Verify certificate function
    function verifyCertificate(certificateNumber) {
        // Show loading state
        submitBtn.attr('data-kt-indicator', 'on').prop('disabled', true);
        resultDiv.addClass('d-none').html('');

        $.ajax({
            url: '/verify',
            type: 'POST',
            data: {
                _token: $('meta[name="csrf-token"]').attr('content'),
                certificate_number: certificateNumber
            },
            success: function (response) {
                submitBtn.removeAttr('data-kt-indicator').prop('disabled', false);

                if (response.success) {
                    // Redirect to certificate details page
                    window.location.href = `/verify/${certificateNumber}`;
                } else {
                    showError(response.message);
                }
            },
            error: function (xhr) {
                submitBtn.removeAttr('data-kt-indicator').prop('disabled', false);
                showError(xhr.responseJSON?.message || "Failed to verify certificate. Please try again.");
            }
        });
    }

    // Show error
    function showError(message) {
        resultDiv.removeClass('d-none').html(`
            <div class="alert alert-dismissible bg-light-danger d-flex flex-column flex-sm-row p-5">
                <i class="ki-duotone ki-cross-circle fs-2hx text-danger me-4 mb-5 mb-sm-0">
                    <span class="path1"></span>
                    <span class="path2"></span>
                </i>
                <div class="d-flex flex-column pe-0 pe-sm-10">
                    <h4 class="fw-semibold">Certificate Not Found</h4>
                    <span>${message}</span>
                </div>
                <button type="button" class="position-absolute position-sm-relative m-2 m-sm-0 top-0 end-0 btn btn-icon ms-sm-auto" data-bs-dismiss="alert">
                    <i class="ki-duotone ki-cross fs-1 text-danger">
                        <span class="path1"></span>
                        <span class="path2"></span>
                    </i>
                </button>
            </div>
        `);

        Swal.fire({
            text: message,
            icon: "error",
            buttonsStyling: false,
            confirmButtonText: "Ok, got it!",
            customClass: {
                confirmButton: "btn fw-bold btn-primary",
            }
        });
    }

    // QR Code Scanner
    scanBtn.on('click', function () {
        scannerModal.modal('show');
        startQrScanner();
    });

    // Start QR scanner
    function startQrScanner() {
        if (html5QrCode) {
            return; // Already initialized
        }

        html5QrCode = new Html5Qrcode("qr-reader");

        Html5Qrcode.getCameras().then(cameras => {
            if (cameras && cameras.length) {
                const cameraId = cameras[0].id;
                
                html5QrCode.start(
                    cameraId,
                    {
                        fps: 10,
                        qrbox: { width: 250, height: 250 }
                    },
                    (decodedText, decodedResult) => {
                        // QR code scanned successfully
                        stopQrScanner();
                        scannerModal.modal('hide');
                        
                        // Extract certificate number from URL if it's a full URL
                        let certificateNumber = decodedText;
                        if (decodedText.includes('/verify/')) {
                            certificateNumber = decodedText.split('/verify/').pop();
                        }
                        
                        certificateInput.val(certificateNumber);
                        verifyCertificate(certificateNumber);
                    },
                    (errorMessage) => {
                        // Parse errors, ignore them
                    }
                ).catch(err => {
                    console.error("Unable to start camera:", err);
                    Swal.fire({
                        text: "Unable to access camera. Please check permissions or enter certificate number manually.",
                        icon: "error",
                        buttonsStyling: false,
                        confirmButtonText: "Ok, got it!",
                        customClass: {
                            confirmButton: "btn fw-bold btn-primary",
                        }
                    });
                    scannerModal.modal('hide');
                });
            }
        }).catch(err => {
            console.error("No cameras found:", err);
            Swal.fire({
                text: "No camera found. Please enter certificate number manually.",
                icon: "warning",
                buttonsStyling: false,
                confirmButtonText: "Ok, got it!",
                customClass: {
                    confirmButton: "btn fw-bold btn-primary",
                }
            });
            scannerModal.modal('hide');
        });
    }

    // Stop QR scanner
    function stopQrScanner() {
        if (html5QrCode) {
            html5QrCode.stop().then(() => {
                html5QrCode = null;
            }).catch(err => {
                console.error("Failed to stop scanner:", err);
            });
        }
    }

    // Stop scanner when modal is closed
    scannerModal.on('hidden.bs.modal', function () {
        stopQrScanner();
    });

    // Auto-focus on certificate input
    certificateInput.focus();
})();
