<div class="form-group">
    <label class="control-label">
        {{ trans('plugins/multi-country-sync::sync.settings.api_key') }}
    </label>
    <div class="input-group" style="display: flex; flex-wrap: nowrap;">
        <input 
            type="password" 
            class="form-control" 
            name="{{ $instance }}_api_key" 
            id="{{ $instance }}_api_key"
            value="{{ $value }}"
            placeholder="{{ trans('plugins/multi-country-sync::sync.settings.api_key_placeholder') }}"
            style="flex: 1; min-width: 0;"
        >
        <div class="input-group-append" style="display: flex;">
            <button 
                type="button" 
                class="btn btn-info generate-api-key-btn" 
                data-instance="{{ $instance }}"
                title="{{ trans('plugins/multi-country-sync::sync.settings.generate_api_key') }}"
                style="border-radius: 0; border-left: 0;"
            >
                <i class="ti ti-key"></i>
            </button>
            <button 
                type="button" 
                class="btn btn-secondary toggle-api-key-visibility" 
                data-target="{{ $instance }}_api_key"
                title="{{ trans('plugins/multi-country-sync::sync.settings.show_hide_key') }}"
                style="border-radius: 0 4px 4px 0; border-left: 0;"
            >
                <i class="ti ti-eye"></i>
            </button>
        </div>
    </div>
    <div id="{{ $instance }}_api_key_result" class="mt-2"></div>
    <small class="form-text text-muted">
        {{ trans('plugins/multi-country-sync::sync.settings.api_key_helper') }}
    </small>
</div>

