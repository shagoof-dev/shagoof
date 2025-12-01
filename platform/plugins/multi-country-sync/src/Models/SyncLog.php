<?php

namespace Botble\MultiCountrySync\Models;

use Botble\Base\Models\BaseModel;

class SyncLog extends BaseModel
{
    protected $table = 'multi_country_sync_logs';

    protected $fillable = [
        'product_id',
        'instance',
        'action',
        'status',
        'error_message',
        'response_data',
    ];

    protected $casts = [
        'response_data' => 'array',
    ];
}

