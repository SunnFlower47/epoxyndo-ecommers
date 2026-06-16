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

    public static function group(): string
    {
        return 'general';
    }
}
