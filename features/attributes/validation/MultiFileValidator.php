<?php

/**
 * Description of MultiFileValidator
 * @author goddy
 *
 * Created on: May 23, 2026 at 2:06:57 PM
 */

namespace features\attributes\validation {

    use features\exceptions\client\NotFoundException;
    use features\exceptions\client\ValidationException;
    use features\exceptions\CustomException;
    use features\validation\ValidationInterface;
    use shani\contracts\AttributeInterface;
    use shani\launcher\App;

    /**
     * MultiFileValidator
     *
     * Attribute-based validator for multiple uploaded files. This validator
     * applies a custom `ValidationInterface` implementation to each file in
     * the request. It ensures that files are present when required and that
     * each file passes the provided validation logic.
     *
     * Can be applied at the method or class level to enforce file validation
     * rules during request handling.
     *
     * @author goddy
     * @created May 23, 2026 at 2:06:57 PM
     */
    #[\Attribute(\Attribute::TARGET_METHOD | \Attribute::TARGET_CLASS)]
    final class MultiFileValidator implements AttributeInterface
    {

        /**
         * @var ValidationInterface The validator instance used to validate each file.
         */
        private readonly ValidationInterface $validator;

        /**
         * @var bool Whether files are required. If true and no files are provided,
         *           a NotFoundException will be thrown.
         */
        private readonly bool $required;

        /**
         * Constructs a new MultiFileValidator attribute.
         *
         * @param string $validator Fully qualified class name of a validator
         *                          implementing ValidationInterface.
         * @param bool   $required  Whether files are required (default true).
         *
         * @throws \Error If the provided validator class does not exist or
         *                does not implement ValidationInterface.
         */
        public function __construct(string $validator, bool $required = true)
        {
            $this->validator = new $validator();
            $this->required = $required;
        }

        /**
         * Executes validation against all uploaded files in the request.
         *
         * Iterates over the request’s files and applies the configured validator
         * to each one. Collects validation errors and throws a ValidationException
         * if any file fails validation.
         *
         * @param App $app The application context providing the request object.
         *
         * @throws NotFoundException    If the file is required but not found.
         * @throws ValidationException  If required files are missing or validation fails.
         *
         * @return void
         */
        public function execute(App $app): void
        {
            if (empty($app->request->files)) {
                if ($this->required) {
                    throw CustomException::notFound('Files cannot be empty');
                }
                return;
            }

            $errors = [];
            foreach ($app->request->files as $name => $file) {
                $result = $this->validator->validate($name, $file);
                if ($result !== null) {
                    $errors[] = $result;
                }
            }

            if (!empty($errors)) {
                throw CustomException::validation($app, json_encode(['errors' => $errors]));
            }
        }
    }

}
