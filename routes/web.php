<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\TicketController;
use App\Http\Controllers\EmailAccountController;
use App\Http\Controllers\EmailRuleController;
use App\Http\Controllers\GoogleTaskController;
use App\Http\Controllers\ProfileController;

Route::view('/', 'welcome');

Route::view('dashboard', 'dashboard')
    ->middleware(['auth', 'verified'])
    ->name('dashboard');

Route::middleware(['auth'])->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'delete'])->name('profile.destroy');

    Route::post('/email-accounts/{emailAccount}/test-connection', [EmailAccountController::class, 'testConnection'])->name('email-accounts.test-connection');
    Route::post('/email-accounts/{emailAccount}/fetch-emails', [EmailAccountController::class, 'fetchEmails'])->name('email-accounts.fetch-emails');
    Route::post('/email-accounts/{emailAccount}/toggle-status', [EmailAccountController::class, 'toggleStatus'])->name('email-accounts.toggle-status');
    Route::post('/emails/fetch-all', [TicketController::class, 'fetchAllEmails'])->name('emails.fetch-all');

    // OAuth Routes for Email Accounts
    Route::post('/email-accounts/google/redirect', [EmailAccountController::class, 'redirectToGoogle'])->name('email-accounts.google.redirect');
    Route::get('/email-accounts/google/callback', [EmailAccountController::class, 'handleGoogleCallback'])->name('email-accounts.google.callback');
    Route::post('/email-accounts/google-tasks/redirect', [EmailAccountController::class, 'redirectToGoogleTasks'])->name('email-accounts.google-tasks.redirect');
    Route::get('/email-accounts/google-tasks/callback', [EmailAccountController::class, 'handleGoogleTasksCallback'])->name('email-accounts.google-tasks.callback');
    Route::post('/email-accounts/microsoft/redirect', [EmailAccountController::class, 'redirectToMicrosoft'])->name('email-accounts.microsoft.redirect');
    Route::get('/email-accounts/microsoft/callback', [EmailAccountController::class, 'handleMicrosoftCallback'])->name('email-accounts.microsoft.callback');

    // OAuth Routes
    Route::get('/auth/google-tasks/redirect', [GoogleTaskController::class, 'redirectToProvider'])->name('google-tasks.redirect');
    Route::get('/auth/google-tasks/callback', [GoogleTaskController::class, 'handleProviderCallback'])->name('google-tasks.callback');

    // CRUD for Google Tasks
    Route::post('/google-tasks/{googleTask}/duplicate', [GoogleTaskController::class, 'duplicate'])->name('google-tasks.duplicate');
    Route::post('/google-tasks/{googleTask}/auto-save', [GoogleTaskController::class, 'autoSave'])->name('google-tasks.auto-save');

    Route::post('/tickets/{ticket}/reply', [TicketController::class, 'sendReply'])->name('tickets.reply');
    Route::post('/tickets/rewrite-message', [TicketController::class, 'rewriteMessage'])->name('tickets.rewrite-message');
    Route::post('/tickets/{ticket}/generate-response', [TicketController::class, 'generateResponse'])->name('tickets.generate-response');
    Route::post('/tickets/{ticket}/create-task', [GoogleTaskController::class, 'createTaskFromTicket'])->name('tickets.create-task');
    Route::get('/tickets/{ticket}/extract-action-items', [TicketController::class, 'extractActionItems'])->name('tickets.extract-action-items');
    Route::get('/tickets/{ticket}/summarize', [TicketController::class, 'summarize'])->name('tickets.summarize');
    
    // Notes routes
    Route::post('/tickets/{ticket}/notes', [TicketController::class, 'storeNote'])->name('tickets.notes.store');
    Route::put('/tickets/{ticket}/notes/{note}', [TicketController::class, 'updateNote'])->name('tickets.notes.update');
    Route::delete('/tickets/{ticket}/notes/{note}', [TicketController::class, 'destroyNote'])->name('tickets.notes.destroy');
    
    Route::post('/tickets/bulk-update', [TicketController::class, 'bulkUpdate'])->name('tickets.bulk-update');
    Route::delete('/tickets/delete-filtered', [TicketController::class, 'deleteFiltered'])->name('tickets.delete-filtered');
    Route::resource('tickets', TicketController::class);
    Route::resource('email-accounts', EmailAccountController::class);
    Route::resource('email-rules', EmailRuleController::class);
    Route::resource('google-tasks', GoogleTaskController::class);

    Route::post('/google-tasks/sync', [\App\Http\Controllers\GoogleTaskController::class, 'sync'])->name('google-tasks.sync');
});

require __DIR__.'/auth.php';
