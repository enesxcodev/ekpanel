<?php

namespace App\Services\Settings;

use Illuminate\Contracts\Config\Repository;
use Illuminate\Mail\MailManager;
use Illuminate\Support\Facades\Schema;

class SettingsMailConfigurator
{
    /** @var array<string, mixed> */
    private array $originalMail;

    public function __construct(private SettingsService $settings, private Repository $config)
    {
        $this->originalMail = $config->get('mail');
    }

    public function apply(): void
    {
        $this->config->set('mail', $this->originalMail);
        $this->config->set('mail.mailers.settings_smtp', $this->originalMail['mailers'][$this->originalMail['default']]);
        if (Schema::hasTable('setting_definitions') && $this->settings->get('mail.enabled', false)) {
            $encryption = $this->settings->get('mail.encryption', 'tls');
            $this->config->set('mail.mailers.settings_smtp', [
                'transport' => 'smtp', 'scheme' => $encryption === 'ssl' ? 'smtps' : 'smtp',
                'host' => $this->settings->get('mail.host'), 'port' => $this->settings->get('mail.port', 587),
                'username' => $this->settings->get('mail.username'), 'password' => $this->settings->get('mail.password'),
                'auto_tls' => $encryption !== 'none', 'require_tls' => $encryption === 'tls',
                'timeout' => 15,
                'local_domain' => $this->originalMail['mailers']['smtp']['local_domain'] ?? null,
            ]);
            $this->config->set('mail.default', 'settings_smtp');
            $this->config->set('mail.from', [
                'address' => $this->settings->get('mail.from_address'), 'name' => $this->settings->get('mail.from_name'),
            ]);
        }
        if (app()->resolved('mail.manager')) {
            app(MailManager::class)->forgetMailers();
        }
    }
}
