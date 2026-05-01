<?php

namespace App\Services\Mail;

class MailProtocolRegistry
{
    protected array $protocols = [];

    /**
     * Register a new mail protocol.
     *
     * @param string $driver The mail driver key (e.g., 'smtp', 'sendmail', 'mailgun').
     * @param string $label Human-readable label for the protocol.
     * @param array $fields Configuration fields required for this protocol.
     *                      Each field should have: key, label, type, description (optional), default (optional).
     * @return void
     */
    public function register(string $driver, string $label, array $fields): void
    {
        $this->protocols[$driver] = [
            'driver' => $driver,
            'label' => $label,
            'fields' => $fields,
        ];
    }

    /**
     * Get all registered protocols.
     *
     * @return array
     */
    public function getProtocols(): array
    {
        return $this->protocols;
    }

    /**
     * Get definition for a specific protocol.
     *
     * @param string $driver
     * @return array|null
     */
    public function getDefinition(string $driver): ?array
    {
        return $this->protocols[$driver] ?? null;
    }
}
