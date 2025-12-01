<div class="form-group">
    <label class="control-label">
        {{ trans('plugins/multi-country-sync::sync.settings.api_key') }}
    </label>
    <div class="input-group">
        <input 
            type="password" 
            class="form-control" 
            name="{{ $instance }}_api_key" 
            id="{{ $instance }}_api_key"
            value="{{ $value }}"
            placeholder="{{ trans('plugins/multi-country-sync::sync.settings.api_key_placeholder') }}"
        >
        <button 
            type="button" 
            class="btn btn-info generate-api-key-btn" 
            data-instance="{{ $instance }}"
            title="{{ trans('plugins/multi-country-sync::sync.settings.generate_api_key') }}"
        >
            <i class="ti ti-key"></i>
        </button>
        <button 
            type="button" 
            class="btn btn-secondary toggle-api-key-visibility" 
            data-target="{{ $instance }}_api_key"
            title="{{ trans('plugins/multi-country-sync::sync.settings.show_hide_key') }}"
        >
            <i class="ti ti-eye"></i>
        </button>
    </div>
    <div id="{{ $instance }}_api_key_result" class="mt-2"></div>
    <small class="form-text text-muted">
        {{ trans('plugins/multi-country-sync::sync.settings.api_key_helper') }}
    </small>
</div>

@push('footer')
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Generate API Key
    document.querySelectorAll('.generate-api-key-btn').forEach(btn => {
        btn.addEventListener('click', function() {
            const instance = this.dataset.instance;
            const resultDiv = document.getElementById(`${instance}_api_key_result`);
            const inputField = document.getElementById(`${instance}_api_key`);
            
            if (!confirm('{{ trans("plugins/multi-country-sync::sync.settings.generate_api_key_confirm") }}')) {
                return;
            }
            
            btn.disabled = true;
            btn.innerHTML = '<i class="ti ti-loader"></i>';
            resultDiv.innerHTML = '';
            
            fetch('{{ route("multi-country-sync.settings.generate-api-key") }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content
                },
                body: JSON.stringify({ instance: instance })
            })
            .then(response => response.json())
            .then(data => {
                if (data.error) {
                    resultDiv.innerHTML = `<div class="alert alert-danger">${data.message}</div>`;
                } else {
                    inputField.value = data.data.api_key;
                    inputField.type = 'text'; // Show the new key
                    resultDiv.innerHTML = `<div class="alert alert-success">
                        <strong>{{ trans("plugins/multi-country-sync::sync.settings.api_key_generated") }}!</strong><br>
                        <small>{{ trans("plugins/multi-country-sync::sync.settings.api_key_saved") }}</small><br>
                        <small>{{ trans("plugins/multi-country-sync::sync.settings.api_key_copy_warning") }}</small>
                    </div>`;
                    
                    // Auto-copy to clipboard
                    inputField.select();
                    try {
                        document.execCommand('copy');
                    } catch (e) {
                        // Fallback if clipboard API fails
                    }
                    
                    setTimeout(() => {
                        inputField.type = 'password';
                        resultDiv.innerHTML = '';
                    }, 10000);
                }
                btn.disabled = false;
                btn.innerHTML = '<i class="ti ti-key"></i>';
            })
            .catch(error => {
                resultDiv.innerHTML = `<div class="alert alert-danger">Error: ${error.message}</div>`;
                btn.disabled = false;
                btn.innerHTML = '<i class="ti ti-key"></i>';
            });
        });
    });
    
    // Toggle visibility
    document.querySelectorAll('.toggle-api-key-visibility').forEach(btn => {
        btn.addEventListener('click', function() {
            const targetId = this.dataset.target;
            const inputField = document.getElementById(targetId);
            const icon = this.querySelector('i');
            
            if (inputField.type === 'password') {
                inputField.type = 'text';
                icon.classList.remove('ti-eye');
                icon.classList.add('ti-eye-off');
            } else {
                inputField.type = 'password';
                icon.classList.remove('ti-eye-off');
                icon.classList.add('ti-eye');
            }
        });
    });
});
</script>
@endpush

