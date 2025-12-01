<div class="mb-3">
    <button type="button" class="btn btn-info" id="test-connection-btn">
        <i class="ti ti-plug"></i> {{ trans('plugins/multi-country-sync::sync.settings.test_connection') }}
    </button>
    <div id="test-connection-result" class="mt-2"></div>
</div>

@push('footer')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const btn = document.getElementById('test-connection-btn');
            const result = document.getElementById('test-connection-result');
            
            if (btn) {
                btn.addEventListener('click', function() {
                    const instances = ['uae', 'sa', 'eg'];
                    const currentCountry = document.querySelector('[name="current_country"]')?.value || 'eg';
                    const enabledInstances = instances.filter(i => i !== currentCountry);
                    
                    if (enabledInstances.length === 0) {
                        result.innerHTML = '<div class="alert alert-warning">No other instances to test</div>';
                        return;
                    }
                    
                    btn.disabled = true;
                    btn.innerHTML = '<i class="ti ti-loader"></i> Testing...';
                    result.innerHTML = '';
                    
                    let completed = 0;
                    const results = [];
                    
                    enabledInstances.forEach(instance => {
                        const url = document.querySelector(`[name="${instance}_api_url"]`)?.value;
                        const apiKey = document.querySelector(`[name="${instance}_api_key"]`)?.value;
                        
                        if (!url || !apiKey) {
                            results.push({
                                instance: instance.toUpperCase(),
                                status: 'skipped',
                                message: 'URL or API Key not configured'
                            });
                            completed++;
                            checkComplete();
                            return;
                        }
                        
                        fetch('{{ route("multi-country-sync.settings.test-connection") }}', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content
                            },
                            body: JSON.stringify({ instance: instance })
                        })
                        .then(response => response.json())
                        .then(data => {
                            results.push({
                                instance: instance.toUpperCase(),
                                status: data.data?.status || 'error',
                                message: data.message || data.data?.message || 'Unknown error'
                            });
                            completed++;
                            checkComplete();
                        })
                        .catch(error => {
                            results.push({
                                instance: instance.toUpperCase(),
                                status: 'error',
                                message: error.message
                            });
                            completed++;
                            checkComplete();
                        });
                    });
                    
                    function checkComplete() {
                        if (completed === enabledInstances.length) {
                            btn.disabled = false;
                            btn.innerHTML = '<i class="ti ti-plug"></i> {{ trans('plugins/multi-country-sync::sync.settings.test_connection') }}';
                            
                            let html = '<div class="mt-2">';
                            results.forEach(r => {
                                const alertClass = r.status === 'success' ? 'alert-success' : 
                                                 r.status === 'skipped' ? 'alert-warning' : 'alert-danger';
                                html += `<div class="alert ${alertClass} mb-2">
                                    <strong>${r.instance}:</strong> ${r.message}
                                </div>`;
                            });
                            html += '</div>';
                            result.innerHTML = html;
                        }
                    }
                });
            }
        });
    </script>
@endpush

