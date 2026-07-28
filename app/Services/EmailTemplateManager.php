<?php

namespace App\Services;

use App\Models\EmailTemplate;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\HtmlString;

class EmailTemplateManager
{
    protected array $registeredTemplates = [];

    /**
     * Register a new email template metadata.
     */
    public function register(string $key, array $options): void
    {
        $this->registeredTemplates[$key] = [
            'label' => $options['label'] ?? $key,
            'group' => $options['group'] ?? 'general',
            'default_subject' => $options['default_subject'] ?? '',
            'default_body' => $options['default_body'] ?? '',
            'variables' => $options['variables'] ?? [],
        ];
    }

    /**
     * Synchronize registered templates with the database.
     */
    public function syncDatabase(): void
    {
        foreach ($this->registeredTemplates as $key => $options) {
            EmailTemplate::updateOrCreate(
                ['key' => $key],
                [
                    'group' => $options['group'],
                    'variables' => $options['variables'],
                    // Only set subject/body if creating for the first time
                    // to avoid overwriting user customizations
                ] + (EmailTemplate::where('key', $key)->exists() ? [] : [
                    'subject' => $options['default_subject'],
                    'body' => $options['default_body'],
                    'type' => 'html',
                ])
            );
        }
    }

    /**
     * Get a registered template.
     */
    public function getTemplate(string $key): ?EmailTemplate
    {
        return EmailTemplate::where('key', $key)
            ->where('is_active', true)
            ->first();
    }

    /**
     * Render a template with data.
     */
    public function render(string $key, array $data = []): array
    {
        $template = $this->getTemplate($key);

        if (!$template) {
            return [
                'subject' => '',
                'body' => '',
            ];
        }

        $data = array_merge($this->baseTemplateData(), $data);

        $subject = $this->replaceVariables($template->subject, $data);
        $body = $this->replaceVariables($template->body, $data);

        return [
            'subject' => $subject,
            'body' => $body,
        ];
    }

    /**
     * Render raw subject/body content with provided data.
     */
    public function renderRaw(string $subject, string $body, array $data = []): array
    {
        $data = array_merge($this->baseTemplateData(), $data);

        return [
            'subject' => $this->replaceVariables($subject, $data),
            'body' => $this->replaceVariables($body, $data),
        ];
    }

    protected function baseTemplateData(): array
    {
        return [
            'site_name' => config('app.name'),
            'site_url' => url('/'),
            'account_login_url' => url('/account/login'),
            'account_dashboard_url' => url('/account/dashboard'),
            'account_orders_url' => url('/account/orders'),
            'account_subscriptions_url' => url('/account/subscriptions'),
            'account_licenses_url' => url('/account/licenses'),
            'account_profile_url' => url('/account/profile'),
        ];
    }

    /**
     * Replace variables in text.
     */
    protected function replaceVariables(string $text, array $data): string
    {
        foreach ($data as $key => $value) {
            $name = trim($key, '{} ');
            $text = str_replace("{{$name}}", $value, $text);
        }

        return $text;
    }

    /**
     * Get all registered templates metadata.
     */
    public function getRegisteredTemplates(): array
    {
        return $this->registeredTemplates;
    }
}
