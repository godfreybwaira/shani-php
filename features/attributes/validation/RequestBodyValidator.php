<?php

/**
 * Description of RequestBodyValidator
 * @author goddy
 *
 * Created on: May 23, 2026 at 2:06:57 PM
 */

namespace features\attributes\validation {

    use features\exceptions\CustomException;
    use features\validation\ValidationInterface;
    use shani\contracts\AttributeInterface;
    use shani\launcher\App;

    final class RequestBodyValidator implements AttributeInterface
    {

        private readonly ValidationInterface $validator;
        private readonly bool $required;

        #[\Attribute(\Attribute::TARGET_METHOD | \Attribute::TARGET_CLASS)]
        public function __construct(string $validator, bool $required = true)
        {
            $this->validator = new $validator();
            $this->required = $required;
        }

        public function execute(App $app): void
        {
            $body = $app->request->body();
            if ($body->isEmpty()) {
                if ($this->required) {
                    throw CustomException::notFound('Request body cannot be empty');
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
                throw CustomException::validation($app, json_encode(['errors' => $errors]));
            }
        }
    }

}
