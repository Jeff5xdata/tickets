<?php

namespace App\Http\Controllers;

use App\Models\EmailAccount;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Auth;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

class EmailSignatureController extends Controller
{
    use AuthorizesRequests;

    public function edit(EmailAccount $emailAccount)
    {
        $this->authorize('update', $emailAccount);
        
        return view('email-accounts.signature', compact('emailAccount'));
    }

    public function index()
    {
        $this->authorize('viewAny', EmailAccount::class);
        
        $emailAccounts = auth()->user()->emailAccounts()
            ->where('is_active', true)
            ->with(['tickets' => function($query) {
                $query->select('id', 'email_account_id', 'status');
            }])
            ->get();
        
        return view('email-accounts.signatures.index', compact('emailAccounts'));
    }

    public function update(Request $request, EmailAccount $emailAccount)
    {
        $this->authorize('update', $emailAccount);
        
        $request->validate([
            'signature_text' => 'nullable|string|max:2000',
            'signature_image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);

        $data = [
            'signature_text' => $request->signature_text,
        ];

        // Handle image upload
        if ($request->hasFile('signature_image')) {
            // Delete old image if exists
            if ($emailAccount->signature_image_path) {
                Storage::disk('public')->delete($emailAccount->signature_image_path);
            }

            // Store new image
            $imagePath = $request->file('signature_image')->store('signatures', 'public');
            $data['signature_image_path'] = $imagePath;
        }

        $emailAccount->update($data);

        return redirect()->route('email-accounts.show', $emailAccount)
            ->with('success', 'Signature updated successfully.');
    }

    public function removeImage(EmailAccount $emailAccount)
    {
        $this->authorize('update', $emailAccount);
        
        if ($emailAccount->signature_image_path) {
            Storage::disk('public')->delete($emailAccount->signature_image_path);
            $emailAccount->update(['signature_image_path' => null]);
        }

        return redirect()->route('email-accounts.signature.edit', $emailAccount)
            ->with('success', 'Signature image removed successfully.');
    }

    public function destroy(EmailAccount $emailAccount)
    {
        $this->authorize('update', $emailAccount);
        
        // Remove signature image if exists
        if ($emailAccount->signature_image_path) {
            Storage::disk('public')->delete($emailAccount->signature_image_path);
        }
        
        // Clear signature data
        $emailAccount->update([
            'signature_text' => null,
            'signature_image_path' => null,
        ]);

        return redirect()->route('email-accounts.signatures.index')
            ->with('success', 'Signature deleted successfully.');
    }
}
