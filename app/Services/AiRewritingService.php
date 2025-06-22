<?php

namespace App\Services;

use App\Models\Ticket;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class AiRewritingService
{
    protected ?string $apiKey;
    protected string $baseUrl;

    public function __construct()
    {
        $this->apiKey = config('services.gemini.api_key');
        $this->baseUrl = 'https://generativelanguage.googleapis.com/v1/models/gemini-1.5-pro:generateContent';
    }

    public function rewriteTicket(Ticket $ticket): void
    {
        if (!$this->isEnabled()) {
            Log::info('AI rewriting is disabled or API key not configured');
            return;
        }

        try {
            $rewrittenContent = $this->rewriteEmailContent($ticket->original_content, $ticket->subject);
            
            $ticket->update([
                'ai_rewritten_content' => $rewrittenContent,
                'is_ai_rewritten' => true,
                'ai_prompt_used' => $this->generatePrompt($ticket->subject),
            ]);
        } catch (\Exception $e) {
            Log::error('Error rewriting ticket with AI: ' . $e->getMessage(), [
                'ticket_id' => $ticket->id
            ]);
        }
    }

    public function rewriteEmailContent(string $content, string $subject): string
    {
        if (!$this->isEnabled()) {
            return $content; // Return original content if AI is disabled
        }

        $prompt = $this->generatePrompt($subject);
        
        $response = Http::withHeaders([
            'Content-Type' => 'application/json',
        ])->post($this->baseUrl . '?key=' . $this->apiKey, [
            'contents' => [
                [
                    'parts' => [
                        [
                            'text' => $prompt . "\n\nOriginal email:\n" . $content
                        ]
                    ]
                ]
            ],
            'generationConfig' => [
                'temperature' => 0.7,
                'topK' => 40,
                'topP' => 0.95,
                'maxOutputTokens' => 2048,
            ]
        ]);

        if ($response->successful()) {
            $result = $response->json();
            return $result['candidates'][0]['content']['parts'][0]['text'] ?? $content;
        }

        Log::error('Gemini API error: ' . $response->body());
        return $content;
    }

    public function generateResponse(string $originalEmail, string $context = ''): string
    {
        if (!$this->isEnabled()) {
            return ''; // Return empty if AI is disabled
        }

        $prompt = "You are a professional email assistant. Generate a polite, professional, and helpful response to the following email. " .
                  "Make sure the response is clear, concise, and addresses all points mentioned in the original email. " .
                  "If this is a customer service context, be empathetic and solution-oriented.\n\n" .
                  "Context: " . $context . "\n\n" .
                  "Original email:\n" . $originalEmail . "\n\n" .
                  "Professional response:";

        $response = Http::withHeaders([
            'Content-Type' => 'application/json',
        ])->post($this->baseUrl . '?key=' . $this->apiKey, [
            'contents' => [
                [
                    'parts' => [
                        [
                            'text' => $prompt
                        ]
                    ]
                ]
            ],
            'generationConfig' => [
                'temperature' => 0.7,
                'topK' => 40,
                'topP' => 0.95,
                'maxOutputTokens' => 1024,
            ]
        ]);

        if ($response->successful()) {
            $result = $response->json();
            return $result['candidates'][0]['content']['parts'][0]['text'] ?? '';
        }

        Log::error('Gemini API error: ' . $response->body());
        return '';
    }

    public function summarizeEmail(string $content): string
    {
        if (!$this->isEnabled()) {
            return ''; // Return empty if AI is disabled
        }

        $prompt = "Please provide a brief, professional summary of the following email, highlighting the key points and any required actions:\n\n" . $content;

        $response = Http::withHeaders([
            'Content-Type' => 'application/json',
        ])->post($this->baseUrl . '?key=' . $this->apiKey, [
            'contents' => [
                [
                    'parts' => [
                        [
                            'text' => $prompt
                        ]
                    ]
                ]
            ],
            'generationConfig' => [
                'temperature' => 0.3,
                'topK' => 40,
                'topP' => 0.95,
                'maxOutputTokens' => 512,
            ]
        ]);

        if ($response->successful()) {
            $result = $response->json();
            return $result['candidates'][0]['content']['parts'][0]['text'] ?? '';
        }

        Log::error('Gemini API error: ' . $response->body());
        return '';
    }

    public function extractActionItems(string $content): array
    {
        if (!$this->isEnabled()) {
            return []; // Return empty array if AI is disabled
        }

        $prompt = "Please extract action items, tasks, or follow-up items from the following email. " .
                  "Return them as a JSON array of objects with 'action', 'assignee' (if mentioned), and 'due_date' (if mentioned) fields:\n\n" . $content;

        $response = Http::withHeaders([
            'Content-Type' => 'application/json',
        ])->post($this->baseUrl . '?key=' . $this->apiKey, [
            'contents' => [
                [
                    'parts' => [
                        [
                            'text' => $prompt
                        ]
                    ]
                ]
            ],
            'generationConfig' => [
                'temperature' => 0.3,
                'topK' => 40,
                'topP' => 0.95,
                'maxOutputTokens' => 1024,
            ]
        ]);

        if ($response->successful()) {
            $result = $response->json();
            $text = $result['candidates'][0]['content']['parts'][0]['text'] ?? '';
            
            // Try to parse JSON from the response
            if (preg_match('/\[.*\]/s', $text, $matches)) {
                $json = json_decode($matches[0], true);
                if (json_last_error() === JSON_ERROR_NONE) {
                    return $json;
                }
            }
        }

        Log::error('Gemini API error: ' . $response->body());
        return [];
    }

    protected function generatePrompt(string $subject): string
    {
        return "Please rewrite the following email to make it more professional, clear, and well-structured. " .
               "Maintain the original meaning and tone while improving grammar, clarity, and organization. " .
               "Subject: " . $subject . "\n\n" .
               "Please provide the rewritten version:";
    }

    protected function isEnabled(): bool
    {
        return config('services.gemini.enabled', false) && !empty($this->apiKey);
    }
} 