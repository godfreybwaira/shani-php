<?php

/**
 * Description of Endpoints
 * @author goddy
 *
 * Created on: Jul 29, 2025 at 11:29:43 AM
 */

namespace shani\documentation\scanners {

    use shani\documentation\Generator;
    use shani\http\App;

    final class Endpoints implements \JsonSerializable
    {

        private readonly string $hash, $path, $name;
        private readonly ?string $details;

        /**
         * Scan endpoints in a user application class
         * @param string $reqMethod Request method
         * @param string $moduleName Name of a module
         * @param \ReflectionMethod $method Method or a function to document
         */
        public function __construct(string $reqMethod, string $moduleName, \ReflectionMethod $method)
        {
            $comment = $method->getDocComment();
            $this->name = $method->getShortName();
            $this->details = !empty($comment) ? Generator::cleanComment($comment) : null;
            $this->path = strtolower(
                    $reqMethod . '/' . $moduleName . '/' .
                    $method->getDeclaringClass()->getShortName() . '/' . $this->name
            );
            $this->hash = App::digest($this->path);
        }

        #[\Override]
        public function jsonSerialize(): array
        {
            return [
                'details' => $this->details,
                'name' => $this->name,
                'path' => $this->path,
                'hash' => $this->hash
            ];
        }
    }

}
