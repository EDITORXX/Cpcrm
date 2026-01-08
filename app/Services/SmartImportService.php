<?php

namespace App\Services;

use App\Models\Lead;
use App\Services\ExcelParserService;
use App\Services\DuplicateDetectionService;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class SmartImportService
{
    protected $excelParser;
    protected $duplicateService;

    public function __construct(
        ExcelParserService $excelParser,
        DuplicateDetectionService $duplicateService
    ) {
        $this->excelParser = $excelParser;
        $this->duplicateService = $duplicateService;
    }

    /**
     * Parse and validate file
     */
    public function parseFile($file): array
    {
        return $this->excelParser->parseFile($file);
    }

    /**
     * Get preview rows (first 10 rows + header)
     */
    public function getPreview($file, int $limit = 10): array
    {
        $allRows = $this->parseFile($file);
        return array_slice($allRows, 0, $limit + 1); // +1 for header
    }

    /**
     * Validate file structure
     */
    public function validateFile($file): array
    {
        $errors = [];
        $rows = $this->parseFile($file);

        if (empty($rows)) {
            $errors[] = "File is empty or contains no data.";
            return $errors;
        }

        // Check if header row exists
        if (count($rows) < 2) {
            $errors[] = "File must contain at least a header row and one data row.";
        }

        // Check file size (max 10MB)
        if ($file->getSize() > 10 * 1024 * 1024) {
            $errors[] = "File size exceeds 10MB limit.";
        }

        return $errors;
    }

    /**
     * Map columns to lead fields
     */
    public function mapColumns(array $rows, array $columnMapping): array
    {
        if (empty($rows)) {
            return [];
        }

        $headerRow = $rows[0];
        $dataRows = array_slice($rows, 1);
        $mappedData = [];

        foreach ($dataRows as $rowIndex => $row) {
            $mappedRow = [];
            
            foreach ($columnMapping as $crmField => $fileColumnIndex) {
                if ($fileColumnIndex !== null && isset($row[$fileColumnIndex])) {
                    $mappedRow[$crmField] = trim($row[$fileColumnIndex]);
                } else {
                    $mappedRow[$crmField] = null;
                }
            }

            // Skip completely empty rows
            if (!empty(array_filter($mappedRow))) {
                $mappedData[] = $mappedRow;
            }
        }

        return $mappedData;
    }

    /**
     * Detect duplicates in mapped data
     */
    public function detectDuplicates(array $mappedData): array
    {
        $duplicates = [];
        $seenPhones = [];
        $seenEmails = [];

        foreach ($mappedData as $index => $row) {
            $phone = isset($row['phone']) ? $this->duplicateService->sanitizePhone($row['phone']) : null;
            $email = isset($row['email']) ? strtolower(trim($row['email'])) : null;

            $isDuplicate = false;
            $duplicateReason = [];

            // Check if blacklisted
            if ($phone && $this->duplicateService->isBlacklisted($phone)) {
                $isDuplicate = true;
                $duplicateReason[] = 'blacklisted';
            }

            // Check phone (duplicate check includes blacklist check, but we check separately for clearer reason)
            if ($phone && !in_array('blacklisted', $duplicateReason) && $this->duplicateService->isDuplicate($phone)) {
                $isDuplicate = true;
                $duplicateReason[] = 'phone';
            }

            // Check email if provided
            if ($email && !empty($email) && Lead::where('email', $email)->exists()) {
                $isDuplicate = true;
                $duplicateReason[] = 'email';
            }

            // Check within current import
            if ($phone && isset($seenPhones[$phone])) {
                $isDuplicate = true;
                $duplicateReason[] = 'phone_in_file';
            }
            if ($email && !empty($email) && isset($seenEmails[$email])) {
                $isDuplicate = true;
                $duplicateReason[] = 'email_in_file';
            }

            if ($isDuplicate) {
                $duplicates[] = [
                    'row' => $index + 2, // +2 because header is row 1, and array is 0-indexed
                    'data' => $row,
                    'reasons' => $duplicateReason,
                ];
            }

            if ($phone) {
                $seenPhones[$phone] = true;
            }
            if ($email) {
                $seenEmails[$email] = true;
            }
        }

        return $duplicates;
    }

    /**
     * Handle duplicate based on strategy
     */
    public function handleDuplicate(array $rowData, string $strategy, ?Lead $existingLead = null): ?Lead
    {
        switch ($strategy) {
            case 'skip':
                return null; // Don't import

            case 'merge':
                if ($existingLead) {
                    // Merge data - update existing lead with new data where fields are empty
                    foreach ($rowData as $field => $value) {
                        if (!empty($value) && empty($existingLead->$field)) {
                            $existingLead->$field = $value;
                        }
                    }
                    $existingLead->save();
                    return $existingLead;
                }
                return null;

            case 'update':
                if ($existingLead) {
                    // Update existing lead with new data
                    $existingLead->update($rowData);
                    return $existingLead;
                }
                return null;

            default:
                return null;
        }
    }

    /**
     * Validate mapped data
     */
    public function validateMappedData(array $mappedData): array
    {
        $errors = [];
        $requiredFields = ['name', 'phone'];

        foreach ($mappedData as $index => $row) {
            $rowErrors = [];

            // Check required fields
            foreach ($requiredFields as $field) {
                if (empty($row[$field] ?? null)) {
                    $rowErrors[] = "Missing required field: {$field}";
                }
            }

            // Validate phone
            if (!empty($row['phone'])) {
                if (!$this->duplicateService->isValidPhone($row['phone'])) {
                    $rowErrors[] = "Invalid phone number format";
                }
            }

            // Validate email if provided
            if (!empty($row['email']) && !filter_var($row['email'], FILTER_VALIDATE_EMAIL)) {
                $rowErrors[] = "Invalid email format";
            }

            if (!empty($rowErrors)) {
                $errors[] = [
                    'row' => $index + 2, // +2 for header and 0-index
                    'errors' => $rowErrors,
                    'data' => $row,
                ];
            }
        }

        return $errors;
    }

    /**
     * Store file and return path
     */
    public function storeFile($file): string
    {
        $fileName = 'smart-imports/' . time() . '_' . $file->getClientOriginalName();
        $path = $file->storeAs('public', $fileName);
        return str_replace('public/', '', $path);
    }

    /**
     * Auto-detect column mapping
     */
    public function autoDetectMapping(array $headerRow): array
    {
        $mapping = [];
        $headerRow = array_map('strtolower', array_map('trim', $headerRow));

        // Common field mappings
        $fieldPatterns = [
            'name' => ['name', 'full name', 'fullname', 'customer name', 'client name'],
            'phone' => ['phone', 'mobile', 'contact', 'phone number', 'mobile number', 'number'],
            'email' => ['email', 'e-mail', 'email address'],
            'address' => ['address', 'street', 'street address'],
            'city' => ['city'],
            'state' => ['state', 'province'],
            'pincode' => ['pincode', 'pin code', 'zip', 'zipcode', 'postal code'],
            'source' => ['source', 'lead source', 'referral source'],
            'budget' => ['budget', 'price range', 'price'],
            'preferred_location' => ['location', 'preferred location', 'area'],
            'notes' => ['notes', 'remarks', 'comments', 'description'],
        ];

        foreach ($fieldPatterns as $crmField => $patterns) {
            foreach ($headerRow as $index => $header) {
                foreach ($patterns as $pattern) {
                    if (strpos($header, $pattern) !== false) {
                        $mapping[$crmField] = $index;
                        break 2; // Break both loops
                    }
                }
            }
        }

        return $mapping;
    }
}

