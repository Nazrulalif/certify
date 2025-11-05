<?php

use App\Http\Controllers\Web\App\CertificateController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth'])->group(function () {
    Route::get('/certificates', [CertificateController::class, 'index'])->name('certificates.index');
    Route::get('/certificates/create', [CertificateController::class, 'create'])->name('certificates.create');
    Route::get('/certificates/{certificate}', [CertificateController::class, 'show'])->name('certificates.show');
    Route::delete('/certificates/{certificate}', [CertificateController::class, 'destroy'])->name('certificates.destroy');
    Route::post('/certificates/bulk-destroy', [CertificateController::class, 'bulkDestroy'])->name('certificates.bulk-destroy');
    Route::post('/certificates/bulk-download', [CertificateController::class, 'bulkDownload'])->name('certificates.bulk-download');
    Route::get('/certificates/{certificate}/download', [CertificateController::class, 'download'])->name('certificates.download');
    Route::get('/certificates/{certificate}/preview', [CertificateController::class, 'preview'])->name('certificates.preview');

    // Generation routes
    Route::get('/v1/events/{event}/registrations', [CertificateController::class, 'getEventRegistrations'])->name('get.event.registrations');
    Route::post('/certificates/generate-from-registrations', [CertificateController::class, 'generateFromRegistrations'])->name('certificates.generate-from-registrations');
    Route::post('/certificates/generate-manual', [CertificateController::class, 'generateManual'])->name('certificates.generate-manual');
    Route::post('/certificates/{certificate}/regenerate', [CertificateController::class, 'regenerate'])->name('certificates.regenerate');
    
    // Excel import/export routes
    Route::get('/events/{event}/certificate-template', [CertificateController::class, 'downloadTemplate'])->name('certificates.download-template');
    Route::post('/certificates/import-excel', [CertificateController::class, 'importExcel'])->name('certificates.import-excel');
    Route::post('/certificates/generate-from-excel', [CertificateController::class, 'generateFromExcel'])->name('certificates.generate-from-excel');
});
