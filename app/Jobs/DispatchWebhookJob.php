<?php

namespace App\Jobs;

use App\Models\Webhook;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Http;

class DispatchWebhookJob implements ShouldQueue
{
    use Queueable;

    public $tries = 3;
    public $backoff = [300, 600, 1800];

    /**
     * Create a new job instance.
     */
    public function __construct(
        public Webhook $webhook,
        public string $event,
        public array $payload
    ) {}

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $headers = [
            'User-Agent' => 'PolyCMS-Webhook/1.0',
            'X-PolyCMS-Event' => $this->event,
            'Content-Type' => 'application/json',
            'Accept' => 'application/json',
        ];

        // If secret is set, sign the payload using HMAC SHA256
        if (!empty($this->webhook->secret)) {
            $signature = hash_hmac('sha256', json_encode($this->payload), $this->webhook->secret);
            $headers['X-PolyCMS-Signature'] = $signature;
        }

        try {
            $response = Http::withHeaders($headers)
                ->timeout(10)
                ->post($this->webhook->url, $this->payload);

            $this->webhook->deliveries()->create([
                'event' => $this->event,
                'payload' => json_encode($this->payload),
                'status_code' => $response->status(),
                'response_body' => substr($response->body(), 0, 10000), // truncate if too large
                'is_successful' => $response->successful(),
                'delivered_at' => now(),
            ]);

            if ($response->serverError()) {
                $this->release($this->backoff[$this->attempts() - 1] ?? 60);
            }
        } catch (\Exception $e) {
            $this->webhook->deliveries()->create([
                'event' => $this->event,
                'payload' => json_encode($this->payload),
                'status_code' => null,
                'response_body' => substr($e->getMessage(), 0, 10000),
                'is_successful' => false,
                'delivered_at' => now(),
            ]);

            if ($this->attempts() < $this->tries) {
                $this->release($this->backoff[$this->attempts() - 1] ?? 60);
            }
        }
    }
}
