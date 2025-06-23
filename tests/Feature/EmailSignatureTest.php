<?php

use App\Models\User;
use App\Models\EmailAccount;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

uses(RefreshDatabase::class);

test('user can view signature edit page', function () {
    $user = User::factory()->create();
    $emailAccount = EmailAccount::factory()->create([
        'user_id' => $user->id,
        'name' => 'Test Account',
        'email' => 'test@example.com',
    ]);

    $response = $this->actingAs($user)
        ->get(route('email-accounts.signature.edit', $emailAccount));

    $response->assertStatus(200);
    $response->assertViewIs('email-accounts.signature');
    $response->assertViewHas('emailAccount', $emailAccount);
});

test('user can update signature with text', function () {
    $user = User::factory()->create();
    $emailAccount = EmailAccount::factory()->create([
        'user_id' => $user->id,
    ]);

    $signatureText = "Best regards,\nJohn Doe\nCEO, Example Corp";

    $response = $this->actingAs($user)
        ->put(route('email-accounts.signature.update', $emailAccount), [
            'signature_text' => $signatureText,
        ]);

    $response->assertRedirect(route('email-accounts.show', $emailAccount));
    
    $emailAccount->refresh();
    expect($emailAccount->signature_text)->toBe($signatureText);
    expect($emailAccount->hasSignature())->toBeTrue();
});

test('user can update signature with image', function () {
    Storage::fake('public');
    
    $user = User::factory()->create();
    $emailAccount = EmailAccount::factory()->create([
        'user_id' => $user->id,
    ]);

    $file = UploadedFile::fake()->image('signature.png');

    $response = $this->actingAs($user)
        ->put(route('email-accounts.signature.update', $emailAccount), [
            'signature_image' => $file,
        ]);

    $response->assertRedirect(route('email-accounts.show', $emailAccount));
    
    $emailAccount->refresh();
    expect($emailAccount->signature_image_path)->not->toBeNull();
    expect($emailAccount->hasSignature())->toBeTrue();
    expect(Storage::disk('public')->exists($emailAccount->signature_image_path))->toBeTrue();
});

test('user can update signature with both text and image', function () {
    Storage::fake('public');
    
    $user = User::factory()->create();
    $emailAccount = EmailAccount::factory()->create([
        'user_id' => $user->id,
    ]);

    $signatureText = "Best regards,\nJohn Doe";
    $file = UploadedFile::fake()->image('signature.png');

    $response = $this->actingAs($user)
        ->put(route('email-accounts.signature.update', $emailAccount), [
            'signature_text' => $signatureText,
            'signature_image' => $file,
        ]);

    $response->assertRedirect(route('email-accounts.show', $emailAccount));
    
    $emailAccount->refresh();
    expect($emailAccount->signature_text)->toBe($signatureText);
    expect($emailAccount->signature_image_path)->not->toBeNull();
    expect($emailAccount->hasSignature())->toBeTrue();
});

test('user can remove signature image', function () {
    Storage::fake('public');
    
    $user = User::factory()->create();
    $emailAccount = EmailAccount::factory()->create([
        'user_id' => $user->id,
        'signature_image_path' => 'signatures/test.png',
    ]);

    Storage::disk('public')->put('signatures/test.png', 'fake image content');

    $response = $this->actingAs($user)
        ->delete(route('email-accounts.signature.remove-image', $emailAccount));

    $response->assertRedirect(route('email-accounts.signature.edit', $emailAccount));
    
    $emailAccount->refresh();
    expect($emailAccount->signature_image_path)->toBeNull();
});

test('signature helper methods work correctly', function () {
    $user = User::factory()->create();
    $emailAccount = EmailAccount::factory()->create([
        'user_id' => $user->id,
        'signature_text' => "Best regards,\nJohn Doe",
        'signature_image_path' => 'signatures/test.png',
    ]);

    expect($emailAccount->hasSignature())->toBeTrue();
    expect($emailAccount->getSignatureText())->toBe("Best regards,\nJohn Doe");
    expect($emailAccount->getSignatureImageUrl())->toContain('signatures/test.png');
    expect($emailAccount->getFormattedSignature())->toContain('Best regards');
    expect($emailAccount->getFormattedSignature())->toContain('Signature Image');
});

test('user cannot access signature of another user', function () {
    $user1 = User::factory()->create();
    $user2 = User::factory()->create();
    $emailAccount = EmailAccount::factory()->create([
        'user_id' => $user2->id,
    ]);

    $response = $this->actingAs($user1)
        ->get(route('email-accounts.signature.edit', $emailAccount));

    $response->assertStatus(403);
});

test('user can view signatures index page', function () {
    $user = User::factory()->create();
    $emailAccount = EmailAccount::factory()->create([
        'user_id' => $user->id,
        'name' => 'Test Account',
        'email' => 'test@example.com',
    ]);

    $response = $this->actingAs($user)
        ->get(route('email-accounts.signatures.index'));

    $response->assertStatus(200);
    $response->assertViewIs('email-accounts.signatures.index');
    $response->assertViewHas('emailAccounts');
});

test('user can delete signature', function () {
    $user = User::factory()->create();
    $emailAccount = EmailAccount::factory()->create([
        'user_id' => $user->id,
        'signature_text' => 'Test signature',
        'signature_image_path' => 'signatures/test.png',
    ]);

    $response = $this->actingAs($user)
        ->delete(route('email-accounts.signature.destroy', $emailAccount));

    $response->assertRedirect(route('email-accounts.signatures.index'));
    
    $emailAccount->refresh();
    expect($emailAccount->signature_text)->toBeNull();
    expect($emailAccount->signature_image_path)->toBeNull();
    expect($emailAccount->hasSignature())->toBeFalse();
});

test('user cannot delete signature of another user', function () {
    $user1 = User::factory()->create();
    $user2 = User::factory()->create();
    $emailAccount = EmailAccount::factory()->create([
        'user_id' => $user2->id,
        'signature_text' => 'Test signature',
    ]);

    $response = $this->actingAs($user1)
        ->delete(route('email-accounts.signature.destroy', $emailAccount));

    $response->assertStatus(403);
});
