<?php

namespace App\Http\Controllers;

use App\Models\EmailRule;
use App\Models\EmailAccount;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\View\View;

class EmailRuleController extends Controller
{
    public function index(): View
    {
        $emailRules = auth()->user()->emailRules()->with(['emailAccount'])->orderBy('priority_order', 'asc')->get();
        $emailAccounts = auth()->user()->emailAccounts()->where('is_active', true)->get();
        
        return view('email-rules.index', compact('emailRules', 'emailAccounts'));
    }

    public function create(): View
    {
        $emailAccounts = auth()->user()->emailAccounts()->where('is_active', true)->get();
        
        return view('email-rules.create', compact('emailAccounts'));
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'email_account_id' => 'required|exists:email_accounts,id',
            'name' => 'required|string|max:255',
            'action' => 'required|in:auto_reply,delete,move_to_folder,mark_as_read,forward,create_task',
            'condition_type' => 'required|in:from_email,to_email,subject_contains,body_contains,has_attachment,priority',
            'condition_value' => 'required|string|max:255',
            'auto_reply_message' => 'required_if:action,auto_reply|nullable|string',
            'forward_to' => 'required_if:action,forward|nullable|email',
            'move_to_folder_name' => 'required_if:action,move_to_folder|nullable|string|max:255',
            'is_active' => 'boolean',
            'priority_order' => 'nullable|integer|min:0',
        ]);

        // Verify the email account belongs to the user
        $emailAccount = auth()->user()->emailAccounts()->findOrFail($validated['email_account_id']);
        $validated['user_id'] = auth()->id();

        try {
            $emailRule = EmailRule::create($validated);

            return response()->json([
                'message' => 'Email rule created successfully',
                'email_rule' => $emailRule->load('emailAccount')
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Error creating email rule: ' . $e->getMessage()
            ], 500);
        }
    }

    public function edit(EmailRule $emailRule): View
    {
        $this->authorize('update', $emailRule);

        $emailAccounts = auth()->user()->emailAccounts()->where('is_active', true)->get();
        
        return view('email-rules.edit', compact('emailRule', 'emailAccounts'));
    }

    public function update(Request $request, EmailRule $emailRule): JsonResponse
    {
        $this->authorize('update', $emailRule);

        $validated = $request->validate([
            'email_account_id' => 'required|exists:email_accounts,id',
            'name' => 'required|string|max:255',
            'action' => 'required|in:auto_reply,delete,move_to_folder,mark_as_read,forward,create_task',
            'condition_type' => 'required|in:from_email,to_email,subject_contains,body_contains,has_attachment,priority',
            'condition_value' => 'required|string|max:255',
            'auto_reply_message' => 'required_if:action,auto_reply|nullable|string',
            'forward_to' => 'required_if:action,forward|nullable|email',
            'move_to_folder_name' => 'required_if:action,move_to_folder|nullable|string|max:255',
            'is_active' => 'boolean',
            'priority_order' => 'nullable|integer|min:0',
        ]);

        // Verify the email account belongs to the user
        $emailAccount = auth()->user()->emailAccounts()->findOrFail($validated['email_account_id']);

        try {
            $emailRule->update($validated);

            return response()->json([
                'message' => 'Email rule updated successfully',
                'email_rule' => $emailRule->fresh()->load('emailAccount')
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Error updating email rule: ' . $e->getMessage()
            ], 500);
        }
    }

    public function destroy(EmailRule $emailRule): JsonResponse
    {
        $this->authorize('delete', $emailRule);

        try {
            $emailRule->delete();

            return response()->json([
                'message' => 'Email rule deleted successfully'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Error deleting email rule: ' . $e->getMessage()
            ], 500);
        }
    }

    public function toggle(EmailRule $emailRule): JsonResponse
    {
        $this->authorize('update', $emailRule);

        try {
            $emailRule->update(['is_active' => !$emailRule->is_active]);

            return response()->json([
                'message' => 'Email rule ' . ($emailRule->is_active ? 'activated' : 'deactivated') . ' successfully',
                'email_rule' => $emailRule->fresh()
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Error toggling email rule: ' . $e->getMessage()
            ], 500);
        }
    }

    public function reorder(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'rules' => 'required|array',
            'rules.*.id' => 'required|exists:email_rules,id',
            'rules.*.priority_order' => 'required|integer|min:0',
        ]);

        try {
            foreach ($validated['rules'] as $ruleData) {
                $rule = auth()->user()->emailRules()->findOrFail($ruleData['id']);
                $rule->update(['priority_order' => $ruleData['priority_order']]);
            }

            return response()->json([
                'message' => 'Email rules reordered successfully'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Error reordering email rules: ' . $e->getMessage()
            ], 500);
        }
    }

    public function test(EmailRule $emailRule, Request $request): JsonResponse
    {
        $this->authorize('view', $emailRule);

        $validated = $request->validate([
            'test_email' => 'required|string',
            'test_subject' => 'required|string',
            'test_body' => 'required|string',
        ]);

        $testData = [
            'from_email' => $validated['test_email'],
            'subject' => $validated['test_subject'],
            'body' => $validated['test_body'],
            'to_email' => $emailRule->emailAccount->email,
            'attachments' => [],
            'priority' => 'medium',
        ];

        $matches = $emailRule->matchesCondition($testData);

        return response()->json([
            'matches' => $matches,
            'test_data' => $testData,
            'rule_condition' => [
                'type' => $emailRule->condition_type,
                'value' => $emailRule->condition_value,
            ]
        ]);
    }
}
