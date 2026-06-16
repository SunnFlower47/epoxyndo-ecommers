<?php

namespace App\Filament\Pages;

use App\Settings\GeneralSettings;
use Filament\Forms\Components\FileUpload;
use Filament\Schemas\Components\Section;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Pages\SettingsPage;
use Filament\Schemas\Schema;

class GeneralSettingsPage extends SettingsPage
{
    protected static ?string $slug = 'pengaturan-umum';
    protected static string $settings = GeneralSettings::class;

    public static function getNavigationIcon(): string|\Illuminate\Contracts\Support\Htmlable|null
    {
        return 'heroicon-o-cog-6-tooth';
    }

    public static function getNavigationGroup(): ?string
    {
        return 'Pengaturan';
    }

    public static function getNavigationSort(): ?int
    {
        return 100;
    }

    public function getTitle(): string|\Illuminate\Contracts\Support\Htmlable
    {
        return 'Profil Perusahaan';
    }

    public static function getNavigationLabel(): string
    {
        return 'Profil Perusahaan';
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->schema([
                Section::make('Identitas Perusahaan')
                    ->schema([
                        TextInput::make('company_name')
                            ->label('Nama Perusahaan')
                            ->required(),
                        FileUpload::make('company_logo')
                            ->label('Logo Perusahaan')
                            ->image()
                            ->directory('settings'),
                    ])->columns(2),

                Section::make('Lokasi Perusahaan (Alamat)')
                    ->schema([
                        Textarea::make('office_address')
                            ->label('Office & Training')
                            ->rows(2)
                            ->columnSpanFull(),
                        Textarea::make('marketing_address')
                            ->label('Marketing Office')
                            ->rows(2)
                            ->columnSpanFull(),
                        Textarea::make('factory_address')
                            ->label('Factory')
                            ->rows(2)
                            ->columnSpanFull(),
                    ]),

                Section::make('Kontak & Support')
                    ->schema([
                        TextInput::make('support_phone')
                            ->label('Nomor Telepon / WhatsApp')
                            ->tel(),
                        TextInput::make('support_email')
                            ->label('Email Dukungan')
                            ->email(),
                        TextInput::make('marketing_email')
                            ->label('Email Marketing')
                            ->email(),
                    ])->columns(2),

                Section::make('Media Sosial')
                    ->schema([
                        TextInput::make('social_media.instagram')
                            ->label('Instagram URL')
                            ->url(),
                        TextInput::make('social_media.facebook')
                            ->label('Facebook URL')
                            ->url(),
                        TextInput::make('social_media.youtube')
                            ->label('YouTube URL')
                            ->url(),
                        TextInput::make('social_media.tiktok')
                            ->label('TikTok URL')
                            ->url(),
                        TextInput::make('social_media.whatsapp')
                            ->label('WhatsApp Link / URL')
                            ->url(),
                    ])->columns(2),

                Section::make('Keuangan & Pajak')
                    ->schema([
                        TextInput::make('tax_percentage')
                            ->label('Persentase Pajak (%)')
                            ->numeric()
                            ->required()
                            ->minValue(0)
                            ->maxValue(100),
                    ])->columns(2),
            ]);
    }
}
