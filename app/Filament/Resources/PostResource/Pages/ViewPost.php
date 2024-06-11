<?php

namespace App\Filament\Resources\PostResource\Pages;

use Filament\Actions;
use ListRecords\Concerns\Translatable;
use App\Filament\Resources\PostResource;
use Filament\Resources\Pages\ViewRecord;

class ViewPost extends ViewRecord
{
    use ViewRecord\Concerns\Translatable;

    protected static string $resource = PostResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\LocaleSwitcher::make()
                ->options([
                    'en_US' => 'English',
                    'ms_MY' => 'Malay',
                    'zh_CN' => 'Chinese (Simplified)',
                    'zh_TW' => 'Chinese (Traditional)',
                    'zh_HK' => 'Chinese (HK)',
                    'vi_VN' => 'Vietnamese'
                ]),
            Actions\EditAction::make(),
        ];
    }
}
