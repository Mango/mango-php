<?php
/**
 * Mango Library Exceptions
 */

namespace Mango;

/**
 * Base Mango Exception
 */
class MangoException extends \Exception {
    protected $message = "Base Mango Exception";
}


/**
 * Input validation error
 */
class InputValidationError extends MangoException {
    protected $message = "Input validation error";
}


/**
 * Authentication error
 */
class AuthenticationError extends MangoException {
    protected $message = "Authentication error";
}


/**
 * Resource not found
 */
class NotFound extends MangoException {
    protected $message = "Resource not found";
}


/**
 * Method not allowed
 */
class MethodNotAllowed extends MangoException {
    protected $message = "Method not allowed";
}


/**
 * Unhandled error
 */
class UnhandledError extends MangoException {
    protected $message = "Unhandled error";
}


/**
 * Invalid API Key
 */
class InvalidApiKey extends MangoException {
    protected $message = "Invalid API Key";
}


/**
 * Unable to connect to Mango API
 */
class UnableToConnect extends MangoException {
    protected $message = "Unable to connect to Mango API";
}

?>
