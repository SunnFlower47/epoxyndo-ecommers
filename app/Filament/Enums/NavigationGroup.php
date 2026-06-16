<?php

namespace App\Filament\Enums;

enum NavigationGroup: string
{
    case Dashboard  = 'Dashboard';
    case Katalog    = 'Katalog';
    case Transaksi  = 'Transaksi';
    case Pengguna   = 'Pengguna';
    case Review     = 'Review';
    case Pengaturan = 'Pengaturan';
}
