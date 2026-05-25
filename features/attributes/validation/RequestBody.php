<?php

namespace features\attributes\validation {

    use features\exceptions\client\BadRequestException;
    use features\exceptions\client\ValidationException;
    use features\validation\ValidationInterface;
    use shani\contracts\AttributeInterface;
    use shani\launcher\App;

    /**
     * RequestBody
     *
     * Attribute-based validator for request body content. This validator
     * applies a custom `ValidationInterface` implementation to each key/value
     * pair in the request body. It ensures that the body is present when required
     * and that each field passes the provided validation logic.
     *
     * Can be applied at the method or class level to enforce body validation
     * rules during request handling.
     *
     * @author goddy
     * @created May 23, 2026 at 2:06:57 PM
     */
    #[\Attribute(\Attribute::TARGET_METHOD | \Attribute::TARGET_CLASS)]
    final class RequestBody implements AttributeInterface
    {

        /**
         * @var ValidationInterface The validator instance used to validate each field.
         */
        private readonly ValidationInterface $validator;

        /**
         * @var bool Whether the request body is required. If true and body is empty,
         *           a notFound exception will be thrown.
         */
        private readonly bool $required;

        /**
         * Constructs a new RequestBody attribute.
         *
         * @param string $validator Fully qualified class name of a validator
         *                          implementing ValidationInterface.
         * @param bool   $required  Whether the request body is required (default true).
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
         * Executes validation against all fields in the request body.
         *
         * Iterates over the request body and applies the configured validator
         * to each key/value pair. Collects validation errors and throws a
         * ValidationException if any field fails validation.
         *
         * @param App $app The application context providing the request object.
         *
         * @throws BadRequestException If required body is missing.
         *
         * @return void
         */
        public function execute(App $app): void
        {
            $body = $app->request->body();
            if ($body->isEmpty()) {
                if ($this->required) {
                    throw new BadRequestException('Request body cannot be empty');
                }
                return;
            }

            $errors = [];
            $body->each(function (string|int $key, mixed $value) use (&$errors) {
                $result = $this->validator->validate($key, $value);
                if ($result !== null) {
                    $errors[] = $result;
                }
            });

            if (!empty($errors)) {
                throw new ValidationException(json_encode(['errors' => $errors]));
            }
        }
    }

}
