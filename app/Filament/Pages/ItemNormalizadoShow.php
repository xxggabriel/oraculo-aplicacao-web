<?php

namespace App\Filament\Pages;

use App\Domain\Jurimetria\Dto\NormalizedItem;
use App\Domain\Jurimetria\Services\JurimetriaSearchService;
use Filament\Notifications\Notification;
use Filament\Pages\Page;

class ItemNormalizadoShow extends Page
{
    protected static bool $shouldRegisterNavigation = false;

    protected static ?string $slug = 'jurimetria/itens/{record}';

    protected static string $view = 'filament.pages.item-normalizado-show';

    public ?NormalizedItem $item = null;

    public function mount(string $record, JurimetriaSearchService $service): void
    {
        $this->item = $service->find($record);

        if (! $this->item) {
            Notification::make()
                ->title('Item não encontrado no índice itens-normalizados.')
                ->danger()
                ->send();

            abort(404);
        }
    }
}
