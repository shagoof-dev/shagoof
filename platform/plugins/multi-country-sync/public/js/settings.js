document.addEventListener('DOMContentLoaded', function() {
    // Generate API Key
    document.querySelectorAll('.generate-api-key-btn').forEach(btn => {
        btn.addEventListener('click', function() {
            const instance = this.dataset.instance;
            const resultDiv = document.getElementById(`${instance}_api_key_result`);
            const inputField = document.getElementById(`${instance}_api_key`);
            
            if (!confirm('Are you sure you want to generate a new API key? This will replace the existing key.')) {
                return;
            }
            
            btn.disabled = true;
            btn.innerHTML = '<i class="ti ti-loader"></i>';
            resultDiv.innerHTML = '';
            
            const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content || 
                             document.querySelector('input[name="_token"]')?.value;
            
            const generateUrl = window.location.origin + '/admin/multi-country-sync/settings/generate-api-key';
            
            fetch(generateUrl, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrfToken,
                    'X-Requested-With': 'XMLHttpRequest'
                },
                body: JSON.stringify({ instance: instance })
            })
            .then(response => response.json())
            .then(data => {
                if (data.error) {
                    resultDiv.innerHTML = `<div class="alert alert-danger">${data.message || 'Error generating API key'}</div>`;
                } else {
                    inputField.value = data.data.api_key;
                    inputField.type = 'text'; // Show the new key
                    resultDiv.innerHTML = `<div class="alert alert-success">
                        <strong>API Key Generated!</strong><br>
                        <small>The key has been saved automatically.</small><br>
                        <small>Please copy it now as it won't be shown again.</small>
                    </div>`;
                    
                    // Auto-copy to clipboard
                    inputField.select();
                    try {
                        document.execCommand('copy');
                        if (navigator.clipboard) {
                            navigator.clipboard.writeText(data.data.api_key);
                        }
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
            
            if (!inputField) return;
            
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
    
    // Test Connection
    const testConnectionBtn = document.getElementById('test-connection-btn');
    const testConnectionResult = document.getElementById('test-connection-result');
    
    if (testConnectionBtn && testConnectionResult) {
        testConnectionBtn.addEventListener('click', function() {
            const instances = ['uae', 'sa', 'eg'];
            const currentCountryInput = document.querySelector('[name="current_country"]');
            const currentCountry = currentCountryInput ? currentCountryInput.value : 'eg';
            const enabledInstances = instances.filter(i => i !== currentCountry);
            
            if (enabledInstances.length === 0) {
                testConnectionResult.innerHTML = '<div class="alert alert-warning">No other instances to test</div>';
                return;
            }
            
            testConnectionBtn.disabled = true;
            testConnectionBtn.innerHTML = '<i class="ti ti-loader"></i> Testing...';
            testConnectionResult.innerHTML = '';
            
            let completed = 0;
            const results = [];
            
            enabledInstances.forEach(instance => {
                const urlInput = document.querySelector(`[name="${instance}_api_url"]`);
                const apiKeyInput = document.querySelector(`[name="${instance}_api_key"]`);
                
                const url = urlInput ? urlInput.value : '';
                const apiKey = apiKeyInput ? apiKeyInput.value : '';
                
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
                
                const testUrl = window.location.origin + '/admin/multi-country-sync/settings/test-connection';
                const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content || 
                                 document.querySelector('input[name="_token"]')?.value;
                
                fetch(testUrl, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': csrfToken,
                        'X-Requested-With': 'XMLHttpRequest'
                    },
                    body: JSON.stringify({ instance: instance })
                })
                .then(response => response.json())
                .then(data => {
                    results.push({
                        instance: instance.toUpperCase(),
                        status: data.error ? 'error' : (data.data?.status || 'success'),
                        message: data.message || data.data?.message || 'Unknown error'
                    });
                    completed++;
                    checkComplete();
                })
                .catch(error => {
                    results.push({
                        instance: instance.toUpperCase(),
                        status: 'error',
                        message: error.message || 'Connection failed'
                    });
                    completed++;
                    checkComplete();
                });
            });
            
            function checkComplete() {
                if (completed === enabledInstances.length) {
                    testConnectionBtn.disabled = false;
                    testConnectionBtn.innerHTML = '<i class="ti ti-plug"></i> Test Connection';
                    
                    let html = '<div class="mt-2">';
                    results.forEach(r => {
                        const alertClass = r.status === 'success' ? 'alert-success' : 
                                         r.status === 'skipped' ? 'alert-warning' : 'alert-danger';
                        html += `<div class="alert ${alertClass} mb-2">
                            <strong>${r.instance}:</strong> ${r.message}
                        </div>`;
                    });
                    html += '</div>';
                    testConnectionResult.innerHTML = html;
                }
            }
        });
    }
});

