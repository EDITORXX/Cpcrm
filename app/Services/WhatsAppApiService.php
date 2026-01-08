<?php

namespace App\Services;

use App\Models\WhatsAppApiSettings;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class WhatsAppApiService
{
    protected $baseUrl;
    protected $endpoint;
    protected $token;
    protected $settings;

    public function __construct()
    {
        $this->settings = WhatsAppApiSettings::getSettings();
        $this->baseUrl = rtrim($this->settings->base_url ?? $this->settings->api_endpoint ?? 'https://rengage.mcube.com', '/');
        $this->endpoint = rtrim($this->settings->api_endpoint ?? $this->baseUrl, '/');
        $this->token = $this->settings->api_token;
    }

    /**
     * Build full URL from endpoint path
     */
    protected function buildUrl($endpointPath, $replacements = [])
    {
        $url = $this->baseUrl . $endpointPath;
        
        // Replace placeholders like {id}, {contact}, {templateID}
        foreach ($replacements as $key => $value) {
            $url = str_replace('{' . $key . '}', $value, $url);
        }
        
        return $url;
    }

    /**
     * Make API request
     */
    protected function makeRequest($method, $endpointPath, $data = [], $replacements = [])
    {
        $url = $this->buildUrl($endpointPath, $replacements);
        
        $headers = [
            'Authorization' => 'Bearer ' . $this->token,
            'Content-Type' => 'application/json',
            'Accept' => 'application/json',
        ];

        try {
            $response = Http::withHeaders($headers)->timeout(30);
            
            if ($method === 'GET') {
                $response = $response->get($url, $data);
            } elseif ($method === 'POST') {
                $response = $response->post($url, $data);
            } elseif ($method === 'PUT') {
                $response = $response->put($url, $data);
            } elseif ($method === 'DELETE') {
                $response = $response->delete($url, $data);
            }

            if ($response->successful()) {
                return [
                    'success' => true,
                    'data' => $response->json() ?? $response->body(),
                    'endpoint_used' => $endpointPath,
                ];
            }

            $errorMsg = null;
            $responseJson = $response->json();
            if ($responseJson) {
                $errorMsg = $responseJson['message'] ?? $responseJson['error'] ?? $responseJson['msg'] ?? null;
            }
            
            return [
                'success' => false,
                'error' => $errorMsg ?? "HTTP {$response->status()}",
                'status' => $response->status(),
                'endpoint_used' => $endpointPath,
            ];
        } catch (\Illuminate\Http\Client\ConnectionException $e) {
            Log::error('WhatsApp API Connection Error: ' . $e->getMessage(), [
                'url' => $url,
                'method' => $method,
                'endpoint' => $endpointPath,
            ]);
            return [
                'success' => false,
                'error' => 'Connection failed: ' . $e->getMessage() . '. Please check the Base URL in settings.',
            ];
        } catch (\Exception $e) {
            Log::error('WhatsApp API Request Error: ' . $e->getMessage(), [
                'url' => $url,
                'method' => $method,
                'endpoint' => $endpointPath,
                'trace' => $e->getTraceAsString(),
            ]);
            $errorMsg = $e->getMessage();
            if (str_contains($errorMsg, 'Could not resolve host') || str_contains($errorMsg, 'cURL error 6')) {
                $errorMsg = 'Cannot connect to API server. Please check the Base URL: ' . $this->baseUrl;
            }
            return [
                'success' => false,
                'error' => $errorMsg,
            ];
        }
    }

    /**
     * Send WhatsApp message
     */
    public function sendMessage(string $to, string $message, ?string $templateId = null)
    {
        if (!$this->isConfigured()) {
            throw new \Exception('WhatsApp API is not configured');
        }

        try {
            // Format phone number and extract country code
            $phone = preg_replace('/[^0-9]/', '', $to);
            
            // Extract country code (default to 91 for India if 10 digits)
            $countryCode = '91'; // Default to India
            if (strlen($phone) == 10) {
                // 10 digit number, add country code
                $phone = $countryCode . $phone;
            } elseif (strlen($phone) > 10) {
                // Number already has country code, extract it
                if (str_starts_with($phone, '91') && strlen($phone) == 12) {
                    $countryCode = '91';
                } elseif (strlen($phone) >= 12) {
                    // Extract first 1-3 digits as country code
                    $countryCode = substr($phone, 0, strlen($phone) - 10);
                    $phone = $phone; // Keep full number
                }
            }
            
            // Use configured endpoint or fallback to default
            $endpointPath = $this->settings->send_message_endpoint ?? '/api/wpbox/sendmessage';
            
            // Build payload - try different formats with country code
            $payloadFormats = [
                ['to' => $phone, 'message' => $message, 'country_code' => $countryCode],
                ['phone' => $phone, 'message' => $message, 'country_code' => $countryCode],
                ['number' => $phone, 'message' => $message, 'country_code' => $countryCode],
                ['to' => $phone, 'text' => $message, 'country_code' => $countryCode],
                ['phone' => $phone, 'text' => $message, 'country_code' => $countryCode],
                // Also try without country_code field (in case API doesn't need it)
                ['to' => $phone, 'message' => $message],
                ['phone' => $phone, 'message' => $message],
            ];

            if ($templateId) {
                // If template ID is provided, use template endpoint instead
                return $this->sendTemplateMessage($to, $templateId);
            }

            $lastError = null;

            // Try different payload formats with the configured endpoint
            foreach ($payloadFormats as $payload) {
                try {
                    $result = $this->makeRequest('POST', $endpointPath, $payload);
                    
                    if ($result['success']) {
                        Log::info('WhatsApp API Success', [
                            'endpoint' => $endpointPath,
                            'phone' => $phone,
                            'country_code' => $countryCode,
                            'payload_format' => json_encode($payload),
                        ]);
                        return [
                            'success' => true,
                            'data' => $result['data'],
                            'endpoint_used' => $endpointPath,
                            'payload_format_used' => json_encode($payload),
                        ];
                    } else {
                        // If error mentions country code, try next format
                        $errorMsg = $result['error'] ?? 'Request failed';
                        if (str_contains(strtolower($errorMsg), 'country code')) {
                            $lastError = $errorMsg;
                            continue; // Try next format
                        }
                        $lastError = $errorMsg;
                    }
                } catch (\Exception $e) {
                    $lastError = $e->getMessage();
                    continue;
                }
            }

            // If all payload formats failed
            Log::error('WhatsApp API Error: All payload formats failed', [
                'endpoint' => $endpointPath,
                'base_url' => $this->baseUrl,
                'phone' => $to,
                'last_error' => $lastError,
            ]);
            return [
                'success' => false,
                'error' => $lastError ?? 'Failed to send message. Please check the API endpoint configuration.',
            ];
        } catch (\Exception $e) {
            Log::error('WhatsApp API Error: ' . $e->getMessage(), [
                'endpoint' => $endpointPath ?? 'N/A',
                'base_url' => $this->baseUrl,
                'phone' => $to,
                'trace' => $e->getTraceAsString(),
            ]);
            return [
                'success' => false,
                'error' => $e->getMessage(),
            ];
        }
    }

    /**
     * Verify API connection
     */
    public function verifyConnection()
    {
        if (!$this->isConfigured()) {
            return [
                'success' => false,
                'error' => 'API not configured',
            ];
        }

        try {
            // Try multiple common endpoints
            $testEndpoints = [
                '/status',
                '/health',
                '/api/status',
                '/api/health',
                '/v1/status',
                '/',
            ];

            $lastError = null;
            $lastStatus = null;
            $lastResponse = null;

            foreach ($testEndpoints as $endpoint) {
                try {
                    $url = $this->baseUrl . $endpoint;
                    
                    $response = Http::withHeaders([
                        'Authorization' => 'Bearer ' . $this->token,
                        'Content-Type' => 'application/json',
                        'Accept' => 'application/json',
                    ])->timeout(10)->get($url);

                    $lastStatus = $response->status();
                    $lastResponse = $response->body();

                    if ($response->successful()) {
                        return [
                            'success' => true,
                            'data' => $response->json() ?? $response->body(),
                            'message' => 'Connection verified successfully',
                            'endpoint' => $endpoint,
                        ];
                    }

                    // If we get a 401/403, it means endpoint exists but auth failed
                    if (in_array($response->status(), [401, 403])) {
                        return [
                            'success' => false,
                            'error' => 'Authentication failed. Please check your API token.',
                            'status' => $response->status(),
                            'endpoint' => $endpoint,
                            'response' => $response->json() ?? $response->body(),
                        ];
                    }

                    // If we get 404, try next endpoint
                    if ($response->status() === 404) {
                        $lastError = "Endpoint {$endpoint} not found (404)";
                        continue;
                    }

                    // For other errors, save and continue
                    $lastError = $response->json()['message'] ?? $response->body() ?? "HTTP {$response->status()}";
                    
                } catch (\Illuminate\Http\Client\ConnectionException $e) {
                    $lastError = "Connection failed: " . $e->getMessage();
                    continue;
                } catch (\Exception $e) {
                    $lastError = $e->getMessage();
                    continue;
                }
            }

            // If all endpoints failed, return the last error
            return [
                'success' => false,
                'error' => $lastError ?? 'All endpoints failed. Please check the API endpoint URL.',
                'status' => $lastStatus,
                'response' => $lastResponse,
                'suggestion' => 'Please check the debug page for detailed error information.',
            ];
        } catch (\Exception $e) {
            Log::error('WhatsApp API Verification Error: ' . $e->getMessage(), [
                'endpoint' => $this->endpoint,
                'trace' => $e->getTraceAsString(),
            ]);
            return [
                'success' => false,
                'error' => $e->getMessage(),
                'type' => get_class($e),
            ];
        }
    }

    /**
     * Check if API is configured
     */
    public function isConfigured(): bool
    {
        return !empty($this->token) && (!empty($this->baseUrl) || !empty($this->endpoint));
    }

    /**
     * Get API status
     */
    public function getStatus()
    {
        $settings = WhatsAppApiSettings::getSettings();
        return [
            'configured' => $this->isConfigured(),
            'active' => $settings->is_active,
            'verified' => $settings->is_verified,
            'endpoint' => $this->endpoint,
        ];
    }

    /**
     * Send template message
     */
    public function sendTemplateMessage(string $to, string $templateId, array $parameters = [])
    {
        if (!$this->isConfigured()) {
            throw new \Exception('WhatsApp API is not configured');
        }

        // Format phone number and extract country code
        $phone = preg_replace('/[^0-9]/', '', $to);
        
        // Extract country code (default to 91 for India if 10 digits)
        $countryCode = '91'; // Default to India
        if (strlen($phone) == 10) {
            // 10 digit number, add country code
            $phone = $countryCode . $phone;
        } elseif (strlen($phone) > 10) {
            // Number already has country code, extract it
            if (str_starts_with($phone, '91') && strlen($phone) == 12) {
                $countryCode = '91';
            } elseif (strlen($phone) >= 12) {
                // Extract first 1-3 digits as country code
                $countryCode = substr($phone, 0, strlen($phone) - 10);
                $phone = $phone; // Keep full number
            }
        }
        
        // Use configured endpoint or fallback to default
        $endpointPath = $this->settings->send_template_endpoint ?? '/api/wpbox/sendtemplatmessage';
        
        // Try with country_code first, then without
        $payload = [
            'phone' => $phone,
            'template_id' => $templateId,
            'country_code' => $countryCode,
        ];

        // Add parameters if provided
        if (!empty($parameters)) {
            $payload['parameters'] = $parameters;
        }

        // Try with country_code first
        $result = $this->makeRequest('POST', $endpointPath, $payload);
        
        // If it fails with country code error, try without country_code field
        if (!$result['success'] && isset($result['error']) && str_contains(strtolower($result['error']), 'country code')) {
            unset($payload['country_code']);
            $result = $this->makeRequest('POST', $endpointPath, $payload);
        }
        
        return $result;
    }

    /**
     * Send normal text message (alias for sendMessage)
     */
    public function sendTextMessage(string $to, string $message)
    {
        return $this->sendMessage($to, $message);
    }

    /**
     * Get conversation history from API
     */
    public function getConversations(string $phone = null)
    {
        if (!$this->isConfigured()) {
            return [
                'success' => false,
                'error' => 'API not configured',
            ];
        }

        $endpointPath = $this->settings->get_conversations_endpoint ?? '/api/wpbox/getConversations';
        
        $queryParams = [];
        if ($phone) {
            $queryParams['filter'] = 'all'; // Default filter
            $queryParams['phone'] = preg_replace('/[^0-9]/', '', $phone);
        } else {
            $queryParams['filter'] = 'all';
        }

        return $this->makeRequest('GET', $endpointPath, $queryParams);
    }

    /**
     * Get messages for a specific contact/phone number
     */
    public function getMessages(string $phone)
    {
        if (!$this->isConfigured()) {
            return [
                'success' => false,
                'error' => 'API not configured',
            ];
        }

        $endpointPath = $this->settings->get_messages_endpoint ?? '/api/wpbox/getMessages/{contact}';
        
        // Format phone number
        $formattedPhone = preg_replace('/[^0-9]/', '', $phone);
        
        return $this->makeRequest('GET', $endpointPath, [], ['contact' => $formattedPhone]);
    }

    /**
     * Get available templates from API
     */
    public function getTemplates()
    {
        if (!$this->isConfigured()) {
            return [
                'success' => false,
                'error' => 'API not configured',
            ];
        }

        $endpointPath = $this->settings->get_templates_endpoint ?? '/api/wpbox/getTemplates';
        
        $result = $this->makeRequest('GET', $endpointPath);
        
        if ($result['success'] && isset($result['data'])) {
            // Handle different response formats
            $data = $result['data'];
            if (isset($data['templates'])) {
                $result['data'] = $data['templates'];
            } elseif (isset($data['data'])) {
                $result['data'] = $data['data'];
            } elseif (is_array($data)) {
                $result['data'] = $data;
            } else {
                $result['data'] = [];
            }
        }
        
        return $result;
    }

    /**
     * Get specific template by ID
     */
    public function getTemplate(string $templateId)
    {
        if (!$this->isConfigured()) {
            return [
                'success' => false,
                'error' => 'API not configured',
            ];
        }

        $endpointPath = $this->settings->get_template_endpoint ?? '/api/wpbox/get-template/{templateID}';
        
        return $this->makeRequest('GET', $endpointPath, [], ['templateID' => $templateId]);
    }

    /**
     * Create a new template
     */
    public function createTemplate(array $data)
    {
        if (!$this->isConfigured()) {
            return [
                'success' => false,
                'error' => 'API not configured',
            ];
        }

        $endpointPath = $this->settings->create_template_endpoint ?? '/api/wpbox/createTemplate';
        
        return $this->makeRequest('POST', $endpointPath, $data);
    }

    /**
     * Delete a template
     */
    public function deleteTemplate(string $templateId)
    {
        if (!$this->isConfigured()) {
            return [
                'success' => false,
                'error' => 'API not configured',
            ];
        }

        $endpointPath = $this->settings->delete_template_endpoint ?? '/api/wpbox/deleteTemplate';
        
        return $this->makeRequest('POST', $endpointPath, ['template_id' => $templateId]);
    }

    /**
     * Get all groups
     */
    public function getGroups()
    {
        if (!$this->isConfigured()) {
            return [
                'success' => false,
                'error' => 'API not configured',
            ];
        }

        $endpointPath = $this->settings->get_groups_endpoint ?? '/api/wpbox/getGroups';
        
        return $this->makeRequest('GET', $endpointPath);
    }

    /**
     * Create a new group
     */
    public function makeGroup(array $data)
    {
        if (!$this->isConfigured()) {
            return [
                'success' => false,
                'error' => 'API not configured',
            ];
        }

        $endpointPath = $this->settings->make_group_endpoint ?? '/api/wpbox/makeGroups';
        
        return $this->makeRequest('POST', $endpointPath, $data);
    }

    /**
     * Update a group
     */
    public function updateGroup(string $id, array $data)
    {
        if (!$this->isConfigured()) {
            return [
                'success' => false,
                'error' => 'API not configured',
            ];
        }

        $endpointPath = $this->settings->update_group_endpoint ?? '/api/wpbox/updateGroups/{id}';
        
        return $this->makeRequest('PUT', $endpointPath, $data, ['id' => $id]);
    }

    /**
     * Remove a group
     */
    public function removeGroup(string $id)
    {
        if (!$this->isConfigured()) {
            return [
                'success' => false,
                'error' => 'API not configured',
            ];
        }

        $endpointPath = $this->settings->remove_group_endpoint ?? '/api/wpbox/removeGroups/{id}';
        
        return $this->makeRequest('DELETE', $endpointPath, [], ['id' => $id]);
    }

    /**
     * Import a contact
     */
    public function importContact(array $data)
    {
        if (!$this->isConfigured()) {
            return [
                'success' => false,
                'error' => 'API not configured',
            ];
        }

        $endpointPath = $this->settings->import_contact_endpoint ?? '/api/wpbox/importContact';
        
        return $this->makeRequest('POST', $endpointPath, $data);
    }

    /**
     * Update a contact
     */
    public function updateContact(string $id, array $data)
    {
        if (!$this->isConfigured()) {
            return [
                'success' => false,
                'error' => 'API not configured',
            ];
        }

        $endpointPath = $this->settings->update_contact_endpoint ?? '/api/wpbox/updateContact/{id}';
        
        return $this->makeRequest('PUT', $endpointPath, $data, ['id' => $id]);
    }

    /**
     * Remove a contact
     */
    public function removeContact(string $id)
    {
        if (!$this->isConfigured()) {
            return [
                'success' => false,
                'error' => 'API not configured',
            ];
        }

        $endpointPath = $this->settings->remove_contact_endpoint ?? '/api/wpbox/removeContact/{id}';
        
        return $this->makeRequest('DELETE', $endpointPath, [], ['id' => $id]);
    }

    /**
     * Add bulk contacts
     */
    public function addContacts(array $contacts)
    {
        if (!$this->isConfigured()) {
            return [
                'success' => false,
                'error' => 'API not configured',
            ];
        }

        $endpointPath = $this->settings->add_contacts_endpoint ?? '/api/wpbox/addContacts';
        
        return $this->makeRequest('POST', $endpointPath, ['contacts' => $contacts]);
    }

    /**
     * Get media files
     */
    public function getMedia(array $data)
    {
        if (!$this->isConfigured()) {
            return [
                'success' => false,
                'error' => 'API not configured',
            ];
        }

        $endpointPath = $this->settings->get_media_endpoint ?? '/api/wpbox/getMedia';
        
        return $this->makeRequest('POST', $endpointPath, $data);
    }

    /**
     * Get campaigns
     */
    public function getCampaigns()
    {
        if (!$this->isConfigured()) {
            return [
                'success' => false,
                'error' => 'API not configured',
            ];
        }

        $endpointPath = $this->settings->get_campaigns_endpoint ?? '/api/wpbox/getCampaigns';
        
        return $this->makeRequest('GET', $endpointPath);
    }

    /**
     * Send campaign
     */
    public function sendCampaign(array $data)
    {
        if (!$this->isConfigured()) {
            return [
                'success' => false,
                'error' => 'API not configured',
            ];
        }

        $endpointPath = $this->settings->send_campaign_endpoint ?? '/api/wpbox/sendwpcampaigns';
        
        return $this->makeRequest('POST', $endpointPath, $data);
    }

    /**
     * Get message delivery status
     */
    public function getMessageStatus(string $messageId)
    {
        if (!$this->isConfigured()) {
            return [
                'success' => false,
                'error' => 'API not configured',
            ];
        }

        try {
            $endpoints = [
                "/api/messages/{$messageId}/status",
                "/messages/{$messageId}/status",
                "/api/v1/messages/{$messageId}/status",
                "/api/message/{$messageId}",
            ];

            foreach ($endpoints as $endpoint) {
                try {
                    $url = $this->endpoint . $endpoint;
                    
                    $response = Http::withHeaders([
                        'Authorization' => 'Bearer ' . $this->token,
                        'Content-Type' => 'application/json',
                        'Accept' => 'application/json',
                    ])->timeout(10)->get($url);

                    if ($response->successful()) {
                        return [
                            'success' => true,
                            'data' => $response->json() ?? $response->body(),
                            'endpoint_used' => $endpoint,
                        ];
                    }
                } catch (\Exception $e) {
                    continue;
                }
            }

            return [
                'success' => false,
                'error' => 'Could not fetch message status from API',
            ];
        } catch (\Exception $e) {
            Log::error('WhatsApp Get Message Status Error: ' . $e->getMessage());
            return [
                'success' => false,
                'error' => $e->getMessage(),
            ];
        }
    }
}
