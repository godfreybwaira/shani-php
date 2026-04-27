<?php

/**
 * Description of CgiServer
 * @author coder
 *
 * Created on: May 22, 2025 at 9:38:19 AM
 */

namespace shani\servers\cgi {

    use features\utils\DataConvertor;
    use features\utils\File;
    use shani\http\RequestEntity;
    use features\utils\MediaType;
    use features\utils\RequestEntityBuilder;
    use shani\contracts\ConcurrencyInterface;
    use shani\contracts\EventHandler;
    use shani\servers\SupportedWebServer;
    use shani\launcher\Framework;
    use shani\launcher\ApplicationLauncher;

    final class CgiServer implements SupportedWebServer
    {

        private static array $ipHeaders = [
            'HTTP_CLIENT_IP', 'HTTP_X_FORWARDED_FOR',
            'HTTP_X_FORWARDED', 'HTTP_X_CLUSTER_CLIENT_IP',
            'HTTP_FORWARDED_FOR', 'HTTP_FORWARDED', 'REMOTE_ADDR'
        ];
        private readonly Framework $config;

        public function __construct(Framework $config)
        {
            $this->config = $config;
        }

        public function request(\Closure $callback): SupportedWebServer
        {
            $request = self::createRequest();
            $writer = new CgiHttpResponseWriter();
            $callback($request, $writer, $this->config);
            return $this;
        }

        private static function createRequest(): RequestEntity
        {
            $scheme = $_SERVER['REQUEST_SCHEME'] ?? (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http');
            $path = $scheme . '://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
            $raw = file_get_contents('php://input');
            $request = (new RequestEntityBuilder())
                    ->protocol($_SERVER['SERVER_PROTOCOL'])
                    ->files(self::getPostedFiles($_FILES))
                    ->method($_SERVER['REQUEST_METHOD'])
                    ->time($_SERVER['REQUEST_TIME'])
                    ->ip(ApplicationLauncher::getClientIP($_SERVER, self::$ipHeaders))
                    ->body(self::getPostedBody($raw))
                    ->headers(getallheaders())
                    ->cookies($_COOKIE)
                    ->rawBody($raw)
                    ->query($_GET)
                    ->uri($path)
                    ->build();
            return $request;
        }

        private static function getPostedBody(string $raw): ?array
        {
            $contentType = $_SERVER['CONTENT_TYPE'] ?? null;
            if (!empty($_POST) || empty($contentType)) {
                return $_POST;
            }
            $type = MediaType::subtype(strtolower($contentType));
            return DataConvertor::convertFrom($raw, $type);
        }

        private static function normalizeFileArray(array $fileInfo): array
        {
            $normalized = [];
            foreach ($fileInfo['name'] as $key => $name) {
                if (is_array($name)) {
                    $subFileInfo = [];
                    foreach ($fileInfo as $attribute => $values) {
                        $subFileInfo[$attribute] = $values[$key];
                    }
                    $normalized[$key] = self::normalizeFileArray($subFileInfo);
                } else {
                    $normalized[$key] = new File(
                            path: $fileInfo['tmp_name'][$key], type: $fileInfo['type'][$key],
                            size: $fileInfo['size'][$key], name: $fileInfo['name'][$key],
                            error: $fileInfo['error'][$key]
                    );
                }
            }
            return $normalized;
        }

        /**
         * Processes the entire uploaded files array recursively.
         *
         * This function iterates over each element in the provided files array.
         * If an element is detected as a file info structure (i.e. it contains the key 'name')
         * and its 'name' is an array, we use the normalization function.
         * Otherwise, if it’s a simple file or another nested structure, we handle accordingly.
         *
         * @param array $files The complete uploaded files array (usually from $_FILES).
         * @return array The normalized files array.
         */
        private static function getPostedFiles(array $files): array
        {
            $result = [];
            foreach ($files as $field => $value) {
                if (is_array($value) && isset($value['name'])) {
                    if (is_array($value['name'])) {
                        $result[$field] = self::normalizeFileArray($value);
                    } else {
                        $result[$field] = new File(
                                path: $value['tmp_name'], type: $value['type'],
                                size: $value['size'], name: $value['name'],
                                error: $value['error']
                        );
                    }
                } elseif (is_array($value)) {
                    $result[$field] = self::getPostedFiles($value);
                } else {
                    $result[$field] = new File(
                            path: $value['tmp_name'], type: $value['type'],
                            size: $value['size'], name: $value['name'],
                            error: $value['error']
                    );
                }
            }
            return $result;
        }

        public function getEventHandler(): EventHandler
        {
            return new CgiEvent();
        }

        public function getConcurrencyHandler(): ConcurrencyInterface
        {
            return new CgiConcurrency();
        }
    }

}
