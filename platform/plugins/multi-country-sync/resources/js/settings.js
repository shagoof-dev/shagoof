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
            
            fetch(window.location.origin + '/admin/multi-country-sync/settings/generate-api-key', {
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
});

