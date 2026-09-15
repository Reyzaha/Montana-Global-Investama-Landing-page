<?php
/**
 * PT MONTANA GLOBAL INVESTAMA — TWO-FACTOR AUTHENTICATION (TOTP / RFC 6238)
 * Standard TOTP Implementation (Google Authenticator Compatible)
 * Tanpa library external / composer, kompatibel penuh PHP 8.x
 */

class GoogleAuthenticator {
    private static string $base32Alphabet = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ234567';

    /**
     * Generate 16-character random secret key (Base32)
     */
    public static function generateSecret(int $length = 16): string {
        $secret = '';
        $alphabet = self::$base32Alphabet;
        $maxIndex = strlen($alphabet) - 1;
        
        for ($i = 0; $i < $length; $i++) {
            $secret .= $alphabet[random_int(0, $maxIndex)];
        }
        return $secret;
    }

    /**
     * Calculate 6-digit TOTP code for a given timestamp
     */
    public static function getCode(string $secret, ?int $timeSlice = null): string {
        if ($timeSlice === null) {
            $timeSlice = (int)floor(time() / 30);
        }

        $secretKey = self::base32Decode($secret);
        if ($secretKey === false) {
            return '';
        }

        // Pack time into binary 64-bit big-endian
        $time = chr(0) . chr(0) . chr(0) . chr(0) . pack('N*', $timeSlice);

        // HMAC-SHA1
        $hmac = hash_hmac('sha1', $time, $secretKey, true);

        // Dynamic truncation
        $offset = ord(substr($hmac, -1)) & 0x0F;
        $hashPart = substr($hmac, $offset, 4);

        $value = unpack('N', $hashPart)[1] & 0x7FFFFFFF;
        $modulo = pow(10, 6);

        return str_pad((string)($value % $modulo), 6, '0', STR_PAD_LEFT);
    }

    /**
     * Verify user code with tolerance window (+/- 1 interval of 30 seconds)
     */
    public static function verifyCode(string $secret, string $code, int $discrepancy = 1): bool {
        $code = trim($code);
        if (strlen($code) !== 6 || !ctype_digit($code)) {
            return false;
        }

        $currentTimeSlice = (int)floor(time() / 30);

        for ($i = -$discrepancy; $i <= $discrepancy; $i++) {
            $calculatedCode = self::getCode($secret, $currentTimeSlice + $i);
            if (hash_equals($calculatedCode, $code)) {
                return true;
            }
        }
        return false;
    }

    /**
     * Generate otpauth:// URI for Google Authenticator QR Code
     */
    public static function getQrUri(string $company, string $account, string $secret): string {
        $label = rawurlencode($company) . ':' . rawurlencode($account);
        $issuer = rawurlencode($company);
        return "otpauth://totp/{$label}?secret={$secret}&issuer={$issuer}&algorithm=SHA1&digits=6&period=30";
    }

    /**
     * Decode Base32 string to binary
     */
    private static function base32Decode(string $base32) {
        $base32 = strtoupper(trim($base32));
        if (empty($base32)) return false;

        $alphabet = self::$base32Alphabet;
        $binaryString = '';

        for ($i = 0; $i < strlen($base32); $i++) {
            $char = $base32[$i];
            if ($char === '=') break;
            $pos = strpos($alphabet, $char);
            if ($pos === false) return false;
            $binaryString .= str_pad(decbin($pos), 5, '0', STR_PAD_LEFT);
        }

        $octets = str_split($binaryString, 8);
        $result = '';
        foreach ($octets as $octet) {
            if (strlen($octet) === 8) {
                $result .= chr(bindec($octet));
            }
        }
        return $result;
    }
}
