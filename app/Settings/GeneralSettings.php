<?php

namespace App\Settings;

use Spatie\LaravelSettings\Settings;

class GeneralSettings extends Settings
{
    public string $company_name;
    public ?string $company_logo;
    public ?string $company_address;
    public ?string $office_address;
    public ?string $marketing_address;
    public ?string $factory_address;
    public ?string $support_phone;
    public ?string $support_email;
    public ?string $marketing_email;
    public int $tax_percentage;
    public array $social_media;
    
    // Warehouse Settings for Biteship
    public ?string $warehouse_address;
    public ?string $warehouse_latitude;
    public ?string $warehouse_longitude;

    public ?array $client_logos;

    public static function group(): string
    {
        return 'general';
    }
}
