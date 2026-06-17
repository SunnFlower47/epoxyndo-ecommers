<?php

use Spatie\LaravelSettings\Migrations\SettingsMigration;

return new class extends SettingsMigration
{
    public function up(): void
    {
        $this->migrator->add('general.warehouse_address', null);
        $this->migrator->add('general.warehouse_latitude', null);
        $this->migrator->add('general.warehouse_longitude', null);
    }

    public function down(): void
    {
        $this->migrator->delete('general.warehouse_address');
        $this->migrator->delete('general.warehouse_latitude');
        $this->migrator->delete('general.warehouse_longitude');
    }
};
