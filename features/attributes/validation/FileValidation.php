<?php

/**
 * Description of FileValidation
 * @author goddy
 *
 * Created on: May 23, 2026 at 11:30:08 AM
 */

namespace features\attributes\validation {

    use features\exceptions\client\NotFoundException;
    use features\exceptions\client\ValidationException;
    use shani\contracts\AttributeInterface;
    use shani\launcher\App;

    /**
     * Attribute-based file validation for request handling.
     *
     * This attribute can be applied to methods to enforce file validation
     * rules such as required presence, maximum size, and allowed MIME types.
     * It integrates with the application request lifecycle to validate
     * uploaded files before command execution.
     *
     * @author goddy
     * @created May 23, 2026 at 11:30:08 AM
     */
    #[\Attribute(\Attribute::TARGET_METHOD)]
    final class FileValidation implements AttributeInterface
    {

        /**
         * @var bool Whether the file is required. If false and file is missing,
         *           a NotFoundException will be thrown.
         */
        private readonly bool $optional;

        /**
         * @var string The internal name of the file field in the request.
         */
        private readonly string $name;

        /**
         * @var string Human-readable display name for error messages.
         */
        private readonly string $displayName;

        /**
         * @var int Maximum allowed file size in bytes.
         */
        private readonly int $maxSize;

        /**
         * @var int|null Optional position index for multi-file inputs.
         */
        private readonly ?int $position;

        /**
         * @var array List of allowed file types (MIME types).
         */
        private readonly array $types;

        /**
         * Constructs a new FileValidation attribute.
         *
         * @param string      $name        The request field name for the file.
         * @param int         $maxSize     Maximum allowed file size in bytes.
         * @param array       $types       Allowed MIME types for the file.
         * @param int|null    $position    Optional position index for multi-file inputs.
         * @param bool        $optional    Whether the file is optional (default false).
         * @param string|null $displayName Human-readable name for error reporting.
         */
        public function __construct(
                string $name,
                int $maxSize,
                array $types,
                ?int $position = null,
                bool $optional = false,
                string $displayName = null
        )
        {
            $this->name = $name;
            $this->maxSize = $maxSize;
            $this->types = $types;
            $this->position = $position;
            $this->optional = $optional;
            $this->displayName = $displayName ?? $name;
        }

        /**
         * Executes the file validation against the current request.
         *
         * Retrieves the file from the request and validates it against the
         * configured rules (required presence, maximum size, allowed types).
         * If validation fails, a ValidationException is thrown with details.
         *
         * @param App $app The application context providing the request object.
         *
         * @throws NotFoundException    If the file is required but not found.
         * @throws ValidationException If the file fails size or type validation.
         *
         * @return void
         */
        #[\Override]
        public function execute(App $app): void
        {
            $file = $app->request->file($this->name, $this->position);
            if ($file === null) {
                if ($this->optional) {
                    return;
                }
                throw new NotFoundException('File name "' . $this->name . '" is required but missing');
            }
            $errors = [];
            if ($file->size > $this->maxSize) {
                $errors[] = 'File size exceed maximum size of ' . number_format($this->maxSize, 0, ',') . ' bytes';
            }
            if (!in_array($file->type, $this->types)) {
                $errors[] = 'Required file types are: ' . implode(', ', $this->types) . ', but ' . $file->type . ' provided';
            }
            if (!empty($errors)) {
                throw new ValidationException(implode(PHP_EOL, $errors));
            }
        }
    }

}
