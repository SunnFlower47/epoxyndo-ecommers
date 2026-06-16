<?php

use Spatie\LaravelSettings\Migrations\SettingsMigration;

return new class extends SettingsMigration
{
    public function up(): void
    {
        $this->migrator->add('general.marketing_email', null);
        $this->migrator->add('general.office_address', null);
        $this->migrator->add('general.marketing_address', null);
        $this->migrator->add('general.factory_address', null);
    }
    
    public function down(): void
    {
        $this->migrator->delete('general.marketing_email');
        $this->migrator->delete('general.office_address');
        $this->migrator->delete('general.marketing_address');
        $this->migrator->delete('general.factory_address');
    }
};
