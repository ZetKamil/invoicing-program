<?php

namespace App\Exceptions;

use RuntimeException;

/**
 * Thrown when Peppol UBL 2.1 XML generation fails.
 *
 * This exception separates XML compliance failures from general application
 * errors. It allows callers (e.g. GenerateInvoicePdfAction) to catch and
 * handle UBL failures independently — ensuring PDF generation succeeds even
 * when the XML pipeline is broken, and providing structured logging for
 * Peppol Access Point rejection debugging.
 *
 * Typical failure scenarios:
 *  - Storage write failure (disk full, permission denied)
 *  - Malformed XML output (Blade rendering error produces invalid XML)
 *  - Missing mandatory tenant/customer data (null VAT number, etc.)
 */
class UblGenerationException extends RuntimeException
{
    /**
     * Create an exception for a storage write failure.
     */
    public static function storageFailure(string $path, \Throwable $previous = null): static
    {
        return new static(
            "Failed to write UBL XML file to storage path: {$path}",
            500,
            $previous
        );
    }

    /**
     * Create an exception for malformed XML output.
     */
    public static function malformedXml(string $error): static
    {
        return new static(
            "Generated UBL document is not well-formed XML: {$error}",
            422
        );
    }
}
