<?php

use Spatie\LaravelSettings\Migrations\SettingsMigration;

return new class extends SettingsMigration
{
    public function up(): void
    {
        $this->migrator->add('general.company_name', 'PT Epoxyndo Art Lestari');
        $this->migrator->add('general.company_logo', null);
        $this->migrator->add('general.company_address', null);
        $this->migrator->add('general.support_phone', null);
        $this->migrator->add('general.support_email', null);
        $this->migrator->add('general.tax_percentage', 11);
        $this->migrator->add('general.social_media', [
            'instagram' => null,
            'facebook' => null,
            'youtube' => null,
            'tiktok' => null,
            'whatsapp' => null,
        ]);
    }
    
    public function down(): void
    {
        $this->migrator->delete('general.company_name');
        $this->migrator->delete('general.company_logo');
        $this->migrator->delete('general.company_address');
        $this->migrator->delete('general.support_phone');
        $this->migrator->delete('general.support_email');
        $this->migrator->delete('general.tax_percentage');
        $this->migrator->delete('general.social_media');
    }
};
