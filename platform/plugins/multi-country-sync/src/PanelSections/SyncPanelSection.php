<?php

namespace Botble\MultiCountrySync\PanelSections;

use Botble\Base\PanelSections\PanelSection;
use Botble\Base\PanelSections\PanelSectionItem;

class SyncPanelSection extends PanelSection
{
    public function setup(): void
    {
        $this
            ->setId('settings.multi-country-sync')
            ->setTitle(trans('plugins/multi-country-sync::sync.name'))
            ->withPriority(2000)
            ->addItems([
                PanelSectionItem::make('settings.multi-country-sync.settings')
                    ->setTitle(trans('plugins/multi-country-sync::sync.settings.title'))
                    ->withIcon('ti ti-settings')
                    ->withDescription(trans('plugins/multi-country-sync::sync.settings.description'))
                    ->withPriority(10)
                    ->withRoute('multi-country-sync.settings'),
            ]);
    }
}

