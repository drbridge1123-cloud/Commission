/**
 * Shared API call helper with CSRF token support.
 * Used across BridgeLaw, Admin, and Chong dashboards.
 *
 * NOTE: The global variable `csrfToken` must be set by the PHP page
 * before any API calls are made. Typically this is done inline:
 *   <script>let csrfToken = '<?php echo $_SESSION["csrf_token"]; ?>';</script>
 */

async function apiCall(url, options = {}) {
    const defaultOptions = {
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-Token': csrfToken
        }
    };

    const mergedOptions = {
        ...defaultOptions,
        ...options,
        headers: {
            ...defaultOptions.headers,
            ...(options.headers || {})
        }
    };

    const response = await fetch(url, mergedOptions);
    const text = await response.text();

    let data;
    try {
        data = JSON.parse(text);
    } catch (e) {
        console.error('Invalid JSON response:', text.substring(0, 500));
        throw new Error('Server returned invalid response. Check PHP error logs.');
    }

    // Update CSRF token if returned
    if (data.csrf_token) {
        csrfToken = data.csrf_token;
    }

    if (!response.ok) {
        throw new Error(data.error || 'API call failed');
    }

    return data;
}
