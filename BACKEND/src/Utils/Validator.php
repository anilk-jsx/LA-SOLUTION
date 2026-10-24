<?php
/**
 * LA SOLUTION PARTNERSHIP - INPUT VALIDATOR & SANITIZER
 * Validates request data, handles JSON/Form-data parsing, and provides sanitization.
 */

class Validator {

    /**
     * Parse incoming request payload (handles both JSON raw body and $_POST)
     *
     * @return array
     */
    public static function getRequestData(): array {
        $raw = file_get_contents('php://input');
        $jsonData = json_decode($raw, true);

        if (json_last_error() === JSON_ERROR_NONE && is_array($jsonData)) {
            return $jsonData;
        }

        // Fallback to standard form POST or GET
        return $_POST ?: $_GET ?: [];
    }

    /**
     * Clean and sanitize string input
     */
    public static function sanitize(mixed $value): string {
        if ($value === null || !is_scalar($value)) {
            return '';
        }
        $val = trim((string)$value);
        // Remove null bytes
        $val = str_replace(chr(0), '', $val);
        // Strip tags
        return strip_tags($val);
    }

    /**
     * Validate an Inquiry submission
     *
     * @param array $data Input data
     * @param bool $isUpdate True if partial update (PUT)
     * @return array Array of validation errors indexed by field name. Empty if valid.
     */
    public static function validateInquiry(array $data, bool $isUpdate = false): array {
        $errors = [];

        // Name
        if (!$isUpdate || isset($data['name']) || isset($data['fullName'])) {
            $name = self::sanitize($data['name'] ?? $data['fullName'] ?? '');
            if (empty($name)) {
                $errors['name'] = 'Full name is required.';
            } elseif (mb_strlen($name) < 2 || mb_strlen($name) > 150) {
                $errors['name'] = 'Name must be between 2 and 150 characters.';
            }
        }

        // Phone
        if (!$isUpdate || isset($data['phone']) || isset($data['phoneNumber'])) {
            $phone = self::sanitize($data['phone'] ?? $data['phoneNumber'] ?? '');
            $digitsOnly = preg_replace('/[^0-9]/', '', $phone);
            if (empty($phone)) {
                $errors['phone'] = 'Phone / WhatsApp number is required.';
            } elseif (strlen($digitsOnly) < 10 || strlen($digitsOnly) > 15) {
                $errors['phone'] = 'Please enter a valid phone number with at least 10 digits.';
            }
        }

        // Email
        if (!$isUpdate || isset($data['email']) || isset($data['emailAddress'])) {
            $email = filter_var(trim($data['email'] ?? $data['emailAddress'] ?? ''), FILTER_SANITIZE_EMAIL);
            if (empty($email)) {
                $errors['email'] = 'Email address is required.';
            } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                $errors['email'] = 'Please provide a valid email address.';
            }
        }

        // Partnership Type / Nature of Interest
        if (!$isUpdate || isset($data['type']) || isset($data['partnershipType']) || isset($data['interest_type'])) {
            $type = self::sanitize($data['type'] ?? $data['partnershipType'] ?? $data['interest_type'] ?? '');
            if (empty($type)) {
                $errors['type'] = 'Please select your nature of interest.';
            }
        }

        // Vertical
        if (!$isUpdate || isset($data['vertical']) || isset($data['verticalInterest'])) {
            $vertical = self::sanitize($data['vertical'] ?? $data['verticalInterest'] ?? '');
            if (empty($vertical)) {
                $errors['vertical'] = 'Please select a vertical of interest.';
            }
        }

        // Status (if provided)
        if (isset($data['status'])) {
            $allowedStatuses = ['New', 'Contacted', 'In Discussion', 'MOU Signed', 'Closed', 'Archived'];
            if (!in_array($data['status'], $allowedStatuses, true)) {
                $errors['status'] = 'Invalid status value. Allowed: ' . implode(', ', $allowedStatuses);
            }
        }

        // Priority (if provided)
        if (isset($data['priority'])) {
            $allowedPriorities = ['Low', 'Medium', 'High', 'Urgent'];
            if (!in_array($data['priority'], $allowedPriorities, true)) {
                $errors['priority'] = 'Invalid priority value. Allowed: ' . implode(', ', $allowedPriorities);
            }
        }

        return $errors;
    }
}
