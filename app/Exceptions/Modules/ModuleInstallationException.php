<?php

namespace App\Exceptions\Modules;

use Exception;

/**
 * Exception thrown when module installation fails.
 */
class ModuleInstallationException extends Exception
{
    /**
     * Additional error context.
     */
    protected array $context = [];

    /**
     * Error code for categorization.
     */
    protected string $errorCode;

    /**
     * Create a new module installation exception.
     *
     * @param string $message Error message
     * @param string $errorCode Error code (e.g., 'DEPENDENCY_MISSING', 'PLAN_RESTRICTED')
     * @param array $context Additional context data
     * @param int $code HTTP status code
     * @param \Throwable|null $previous Previous exception
     */
    public function __construct(
        string $message = '',
        string $errorCode = 'INSTALLATION_FAILED',
        array $context = [],
        int $code = 0,
        ?\Throwable $previous = null
    ) {
        parent::__construct($message, $code, $previous);
        $this->errorCode = $errorCode;
        $this->context = $context;
    }

    /**
     * Get the error code.
     */
    public function getErrorCode(): string
    {
        return $this->errorCode;
    }

    /**
     * Get additional context.
     */
    public function getContext(): array
    {
        return $this->context;
    }

    /**
     * Convert to array for API responses.
     */
    public function toArray(): array
    {
        return [
            'error' => $this->errorCode,
            'message' => $this->getMessage(),
            'context' => $this->context,
        ];
    }

    /**
     * Create exception for missing dependencies.
     */
    public static function missingDependencies(array $dependencies): self
    {
        return new self(
            message: 'Module requires dependencies that are not installed: ' . implode(', ', $dependencies),
            errorCode: 'DEPENDENCY_MISSING',
            context: ['dependencies' => $dependencies]
        );
    }

    /**
     * Create exception for plan restrictions.
     */
    public static function planRestricted(string $moduleKey, string $reason): self
    {
        return new self(
            message: "Module '{$moduleKey}' is not available on your current plan: {$reason}",
            errorCode: 'PLAN_RESTRICTED',
            context: ['module' => $moduleKey, 'reason' => $reason]
        );
    }

    /**
     * Create exception for duplicate installation.
     */
    public static function alreadyInstalled(string $moduleKey): self
    {
        return new self(
            message: "Module '{$moduleKey}' is already installed",
            errorCode: 'ALREADY_INSTALLED',
            context: ['module' => $moduleKey]
        );
    }

    /**
     * Create exception for module conflicts.
     */
    public static function moduleConflict(string $moduleKey, array $conflicts): self
    {
        return new self(
            message: "Module '{$moduleKey}' conflicts with installed modules: " . implode(', ', $conflicts),
            errorCode: 'MODULE_CONFLICT',
            context: ['module' => $moduleKey, 'conflicts' => $conflicts]
        );
    }

    /**
     * Create exception for approval required.
     */
    public static function requiresApproval(string $moduleKey): self
    {
        return new self(
            message: "Module '{$moduleKey}' requires administrator approval",
            errorCode: 'REQUIRES_APPROVAL',
            context: ['module' => $moduleKey]
        );
    }
}
