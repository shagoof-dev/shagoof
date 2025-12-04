<?php

namespace Botble\MultiCountrySync\Forms\Settings;

use Botble\Base\Facades\Assets;
use Botble\Base\Forms\FieldOptions\LabelFieldOption;
use Botble\Base\Forms\Fields\HtmlField;
use Botble\Base\Forms\Fields\LabelField;
use Botble\Base\Forms\Fields\OnOffField;
use Botble\Base\Forms\Fields\SelectField;
use Botble\Base\Forms\Fields\TextField;
use Botble\MultiCountrySync\Http\Requests\Settings\SyncSettingRequest;
use Botble\Setting\Forms\SettingForm;
use Botble\Setting\Models\Setting as SettingModel;

class SyncSettingForm extends SettingForm
{
    public function setup(): void
    {
        parent::setup();

        Assets::addScriptsDirectly('vendor/core/plugins/multi-country-sync/js/settings.js');
        
        // Get fresh value from database directly (bypass cache)
        $currentCountry = SettingModel::query()
            ->where('key', 'multi_country_sync_current_country')
            ->value('value') ?: 'eg';

        $this
            ->setSectionTitle(trans('plugins/multi-country-sync::sync.settings.title'))
            ->setSectionDescription(trans('plugins/multi-country-sync::sync.settings.description'))
            ->setValidatorClass(SyncSettingRequest::class)
            ->columns(12)
            ->add(
                'multi_country_sync_enabled',
                OnOffField::class,
                [
                    'label' => trans('plugins/multi-country-sync::sync.settings.enabled'),
                    'value' => setting('multi_country_sync_enabled', false),
                    'help_block' => [
                        'text' => trans('plugins/multi-country-sync::sync.settings.enabled_helper'),
                    ],
                ]
            )
            ->add(
                'current_country',
                SelectField::class,
                [
                    'label' => trans('plugins/multi-country-sync::sync.settings.current_country'),
                    'value' => $currentCountry,
                    'selected' => $currentCountry,
                    'choices' => [
                        'eg' => 'Egypt (EG)',
                        'uae' => 'United Arab Emirates (UAE)',
                        'sa' => 'Saudi Arabia (SA)',
                    ],
                    'help_block' => [
                        'text' => trans('plugins/multi-country-sync::sync.settings.current_country_helper'),
                    ],
                ]
            )
            ->add(
                'uae_section_title',
                LabelField::class,
                LabelFieldOption::make()
                    ->label('UAE Instance')
                    ->colspan(12)
            )
            ->add(
                'uae_api_url',
                TextField::class,
                [
                    'label' => trans('plugins/multi-country-sync::sync.settings.api_url'),
                    'value' => setting('multi_country_sync_uae_api_url', 'https://uae.shagoof.com'),
                    'placeholder' => 'https://uae.shagoof.com',
                    'colspan' => 6,
                ]
            )
            ->add(
                'uae_api_key_wrapper',
                HtmlField::class,
                [
                    'html' => view('plugins/multi-country-sync::settings.api-key-field', [
                        'instance' => 'uae',
                        'value' => setting('multi_country_sync_uae_api_key'),
                    ])->render(),
                    'colspan' => 6,
                ]
            )
            ->add(
                'uae_sync_enabled',
                OnOffField::class,
                [
                    'label' => trans('plugins/multi-country-sync::sync.settings.instance_enabled'),
                    'value' => setting('multi_country_sync_uae_enabled', true),
                    'colspan' => 12,
                ]
            )
            ->add(
                'sa_section_title',
                LabelField::class,
                LabelFieldOption::make()
                    ->label('SA Instance')
                    ->colspan(12)
            )
            ->add(
                'sa_api_url',
                TextField::class,
                [
                    'label' => trans('plugins/multi-country-sync::sync.settings.api_url'),
                    'value' => setting('multi_country_sync_sa_api_url', 'https://sa.shagoof.com'),
                    'placeholder' => 'https://sa.shagoof.com',
                    'colspan' => 6,
                ]
            )
            ->add(
                'sa_api_key_wrapper',
                HtmlField::class,
                [
                    'html' => view('plugins/multi-country-sync::settings.api-key-field', [
                        'instance' => 'sa',
                        'value' => setting('multi_country_sync_sa_api_key'),
                    ])->render(),
                    'colspan' => 6,
                ]
            )
            ->add(
                'sa_sync_enabled',
                OnOffField::class,
                [
                    'label' => trans('plugins/multi-country-sync::sync.settings.instance_enabled'),
                    'value' => setting('multi_country_sync_sa_enabled', true),
                    'colspan' => 12,
                ]
            )
            ->add(
                'eg_section_title',
                LabelField::class,
                LabelFieldOption::make()
                    ->label('EG Instance')
                    ->colspan(12)
            )
            ->add(
                'eg_api_url',
                TextField::class,
                [
                    'label' => trans('plugins/multi-country-sync::sync.settings.api_url'),
                    'value' => setting('multi_country_sync_eg_api_url', 'https://eg.shagoof.com'),
                    'placeholder' => 'https://eg.shagoof.com',
                    'colspan' => 6,
                ]
            )
            ->add(
                'eg_api_key_wrapper',
                HtmlField::class,
                [
                    'html' => view('plugins/multi-country-sync::settings.api-key-field', [
                        'instance' => 'eg',
                        'value' => setting('multi_country_sync_eg_api_key'),
                    ])->render(),
                    'colspan' => 6,
                ]
            )
            ->add(
                'eg_sync_enabled',
                OnOffField::class,
                [
                    'label' => trans('plugins/multi-country-sync::sync.settings.instance_enabled'),
                    'value' => setting('multi_country_sync_eg_enabled', true),
                    'colspan' => 12,
                ]
            )
            ->add(
                'advanced_section_title',
                LabelField::class,
                LabelFieldOption::make()
                    ->label(trans('plugins/multi-country-sync::sync.settings.advanced'))
                    ->colspan(12)
            )
            ->add(
                'sync_on_create',
                OnOffField::class,
                [
                    'label' => trans('plugins/multi-country-sync::sync.settings.sync_on_create'),
                    'value' => setting('multi_country_sync_on_create', true),
                    'colspan' => 6,
                ]
            )
            ->add(
                'sync_on_update',
                OnOffField::class,
                [
                    'label' => trans('plugins/multi-country-sync::sync.settings.sync_on_update'),
                    'value' => setting('multi_country_sync_on_update', true),
                    'colspan' => 6,
                ]
            )
            ->add(
                'use_queue',
                OnOffField::class,
                [
                    'label' => trans('plugins/multi-country-sync::sync.settings.use_queue'),
                    'value' => setting('multi_country_sync_use_queue', true),
                    'help_block' => [
                        'text' => trans('plugins/multi-country-sync::sync.settings.use_queue_helper'),
                    ],
                    'colspan' => 12,
                ]
            )
            ->add(
                'max_retries',
                TextField::class,
                [
                    'label' => trans('plugins/multi-country-sync::sync.settings.max_retries'),
                    'value' => setting('multi_country_sync_max_retries', 3),
                    'type' => 'number',
                    'colspan' => 6,
                ]
            )
            ->add(
                'retry_delay',
                TextField::class,
                [
                    'label' => trans('plugins/multi-country-sync::sync.settings.retry_delay'),
                    'value' => setting('multi_country_sync_retry_delay', 60),
                    'type' => 'number',
                    'help_block' => [
                        'text' => trans('plugins/multi-country-sync::sync.settings.retry_delay_helper'),
                    ],
                    'colspan' => 6,
                ]
            )
            ->add(
                'currency_section_title',
                LabelField::class,
                LabelFieldOption::make()
                    ->label(trans('plugins/multi-country-sync::sync.settings.currency_conversion'))
                    ->colspan(12)
            )
            ->add(
                'convert_currency',
                OnOffField::class,
                [
                    'label' => trans('plugins/multi-country-sync::sync.settings.enable_currency_conversion'),
                    'value' => setting('multi_country_sync_convert_currency', true),
                    'help_block' => [
                        'text' => trans('plugins/multi-country-sync::sync.settings.enable_currency_conversion_helper'),
                    ],
                    'colspan' => 12,
                ]
            )
            ->add(
                'eg_currency',
                TextField::class,
                [
                    'label' => 'EG Currency',
                    'value' => setting('multi_country_sync_eg_currency', 'EGP'),
                    'placeholder' => 'EGP',
                    'colspan' => 4,
                ]
            )
            ->add(
                'uae_currency',
                TextField::class,
                [
                    'label' => 'UAE Currency',
                    'value' => setting('multi_country_sync_uae_currency', 'AED'),
                    'placeholder' => 'AED',
                    'colspan' => 4,
                ]
            )
            ->add(
                'sa_currency',
                TextField::class,
                [
                    'label' => 'SA Currency',
                    'value' => setting('multi_country_sync_sa_currency', 'SAR'),
                    'placeholder' => 'SAR',
                    'colspan' => 4,
                ]
            )
            ->add(
                'exchange_rates_title',
                LabelField::class,
                LabelFieldOption::make()
                    ->label('Exchange Rates (Base: EGP)')
                    ->colspan(12)
            )
            ->add(
                'eg_to_sa_rate',
                TextField::class,
                [
                    'label' => 'EGP to SAR Rate',
                    'value' => setting('multi_country_sync_eg_to_sa_rate', '0.16'),
                    'placeholder' => '0.16',
                    'help_block' => [
                        'text' => '1 EGP = ? SAR (Example: 0.16 means 1 EGP = 0.16 SAR)',
                    ],
                    'colspan' => 6,
                ]
            )
            ->add(
                'eg_to_uae_rate',
                TextField::class,
                [
                    'label' => 'EGP to AED Rate',
                    'value' => setting('multi_country_sync_eg_to_uae_rate', '0.15'),
                    'placeholder' => '0.15',
                    'help_block' => [
                        'text' => '1 EGP = ? AED (Example: 0.15 means 1 EGP = 0.15 AED)',
                    ],
                    'colspan' => 6,
                ]
            )
            ->add(
                'sa_to_eg_rate',
                TextField::class,
                [
                    'label' => 'SAR to EGP Rate',
                    'value' => setting('multi_country_sync_sa_to_eg_rate', '6.25'),
                    'placeholder' => '6.25',
                    'help_block' => [
                        'text' => '1 SAR = ? EGP (Example: 6.25 means 1 SAR = 6.25 EGP)',
                    ],
                    'colspan' => 6,
                ]
            )
            ->add(
                'uae_to_eg_rate',
                TextField::class,
                [
                    'label' => 'AED to EGP Rate',
                    'value' => setting('multi_country_sync_uae_to_eg_rate', '6.67'),
                    'placeholder' => '6.67',
                    'help_block' => [
                        'text' => '1 AED = ? EGP (Example: 6.67 means 1 AED = 6.67 EGP)',
                    ],
                    'colspan' => 6,
                ]
            )
            ->add(
                'sa_to_uae_rate',
                TextField::class,
                [
                    'label' => 'SAR to AED Rate',
                    'value' => setting('multi_country_sync_sa_to_uae_rate', '0.94'),
                    'placeholder' => '0.94',
                    'help_block' => [
                        'text' => '1 SAR = ? AED (Example: 0.94 means 1 SAR = 0.94 AED)',
                    ],
                    'colspan' => 6,
                ]
            )
            ->add(
                'uae_to_sa_rate',
                TextField::class,
                [
                    'label' => 'AED to SAR Rate',
                    'value' => setting('multi_country_sync_uae_to_sa_rate', '1.07'),
                    'placeholder' => '1.07',
                    'help_block' => [
                        'text' => '1 AED = ? SAR (Example: 1.07 means 1 AED = 1.07 SAR)',
                    ],
                    'colspan' => 6,
                ]
            )
            ->add(
                'test_connection_button',
                HtmlField::class,
                [
                    'html' => view('plugins/multi-country-sync::settings.test-connection-button')->render(),
                    'colspan' => 12,
                ]
            );
    }
}

