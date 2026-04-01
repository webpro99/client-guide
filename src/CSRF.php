<?php
/**
 * CSRF — Synchronizer Token Pattern implementation.
 *
 * Usage in forms:   <?= CSRF::field() ?>
 * Usage in verify:  CSRF::verify();   // throws on failure
 */
class CSRF
{
    private const TOKEN_KEY    = '_csrf_token';
    private const INPUT_NAME   = '_csrf';
    private const TOKEN_LENGTH = 32; // bytes → 64 hex chars

    /**
     * Get (or generate) the CSRF token for this session.
     */
    public static function token(): string
    {
        if (empty($_SESSION[self::TOKEN_KEY])) {
            $_SESSION[self::TOKEN_KEY] = bin2hex(random_bytes(self::TOKEN_LENGTH));
        }
        return $_SESSION[self::TOKEN_KEY];
    }

    /**
     * Render a hidden input field with the CSRF token.
     */
    public static function field(): string
    {
        return sprintf(
            '<input type="hidden" name="%s" value="%s">',
            self::INPUT_NAME,
            htmlspecialchars(self::token(), ENT_QUOTES, 'UTF-8')
        );
    }

    /**
     * Verify the CSRF token in the current request.
     * Calls jsonError(403) or redirects on failure.
     *
     * @param bool $json  true → send JSON error; false → redirect to referer
     */
    public static function verify(bool $json = false): void
    {
        $submitted = $_POST[self::INPUT_NAME]
            ?? getallheaders()['X-CSRF-Token']
            ?? '';

        $valid = isset($_SESSION[self::TOKEN_KEY])
            && hash_equals($_SESSION[self::TOKEN_KEY], $submitted);

        if (!$valid) {
            if ($json) {
                jsonError('Invalid or missing CSRF token.', 403);
            } else {
                http_response_code(403);
                die('CSRF token mismatch. Please go back and try again.');
            }
        }
    }
}
