<?php

namespace Botble\MultiCountrySync\Forms\Settings;

use Botble\Base\Forms\FieldOptions\LabelFieldOption;
use Botble\Base\Forms\Fields\HtmlField;
use Botble\Base\Forms\Fields\LabelField;
use Botble\Base\Forms\Fields\OnOffField;
use Botble\Base\Forms\Fields\SelectField;
use Botble\Base\Forms\Fields\TextField;
use Botble\MultiCountrySync\Http\Requests\Settings\SyncSettingRequest;
use Botble\Setting\Forms\SettingForm;

class SyncSettingForm extends SettingForm
{
    public function setup(): void
    {
        parent::setup();

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
                    'value' => setting('multi_country_sync_current_country', 'eg'),
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
                'test_connection_button',
                HtmlField::class,
                [
                    'html' => view('plugins/multi-country-sync::settings.test-connection-button')->render(),
                    'colspan' => 12,
                ]
            );
    }
}

