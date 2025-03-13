<?php

/**
 * Application class is the core application engine that run the entire user
 * application. This is where application execution and routing takes place
 * @author coder
 *
 * Created on: Feb 11, 2024 at 6:50:02 PM
 */

namespace shani\http {

    use gui\UI;
    use library\Cookie;
    use library\DataConvertor;
    use library\http\HttpHeader;
    use library\http\HttpStatus;
    use library\http\RequestEntity;
    use library\Utils;
    use shani\advisors\Configuration;
    use shani\contracts\ResponseWriter;
    use shani\core\Definitions;
    use shani\documentation\Generator;
    use library\http\ResponseEntity;
    use shani\ServerConfig;
    use shani\core\Framework;
    use library\MediaType;
    use shani\core\VirtualHost;

    final class App
    {

        private Asset $asset;
        private Storage $storage;
        private ?string $lang = null;
        private readonly ResponseWriter $writer;
        private ?UI $ui = null;
        private ?array $appCart = null, $dict = null;
        private ?string $platform = null;

        /**
         * Application virtual host configuration
         * @var VirtualHost
         */
        public readonly VirtualHost $vhost;

        /**
         * HTTP request object
         * @var RequestEntity
         */
        public readonly RequestEntity $request;

        /**
         * HTTP response object
         * @var ResponseEntity
         */
        public readonly ResponseEntity $response;

        /**
         * Current loaded application configurations.
         * @var Configuration
         */
        public readonly Configuration $config;

        public function __construct(ResponseEntity &$res, ResponseWriter $writer)
        {
            $this->response = $res;
            $this->writer = $writer;
            $this->request = $res->request;
            $this->response->header()->setAll([
                HttpHeader::X_CONTENT_TYPE_OPTIONS => 'nosniff',
                HttpHeader::SERVER => Framework::NAME
            ]);
            try {
                $this->vhost = ServerConfig::host($this->request->uri->hostname);
                $this->config = new $this->vhost->config($this);
                $this->runApp();
            } catch (\ErrorException $ex) {
                $this->response->setStatus(HttpStatus::BAD_REQUEST);
                if (isset($this->config)) {
                    $this->config->errorHandler($ex);
                } else {
                    $this->send();
                }
            }
        }

        /**
         * Start executing user application
         * @return void
         */
        private function runApp(): void
        {
            $this->registerErrorHandler();
            $this->response->setCompression($this->config->compressionLevel(), $this->config->compressionMinSize());
            if (!$this->vhost->running) {
                $this->response->setStatus(HttpStatus::SERVICE_UNAVAILABLE);
            }
            if (!Asset::tryServe($this)) {
                if ($this->request->uri->path === '/') {
                    $this->request->changeRoute($this->config->homepage());
                }
                Session::start($this);
                $middleware = new Middleware($this);
                $securityAdvisor = $this->config->middleware($middleware);
                $middleware->runWith($securityAdvisor);
            }
        }

        /**
         * Send content to a client application
         * @param bool $useBuffer Set output buffer on so that output can be sent
         * in chunks without closing connection. If false, then connection will
         * be closed and no output will be sent afterward.
         * @return void
         */
        public function send(bool $useBuffer = false): void
        {
            if ($this->request->method === 'head') {
                $this->response->setStatus(HttpStatus::NO_CONTENT);
                $this->writer->send($this->response, true);
            } else if ($useBuffer) {
                $this->writer->send($this->response);
            } else {
                $this->writer->close($this->response);
            }
        }

        /**
         * Stream  a file as HTTP response
         * @param string $filepath Path to a file to stream
         * @param int $chunkSize Number of bytes to stream every turn, default is 1MB
         * @return self
         */
        public function stream(string $filepath, int $chunkSize = null): self
        {
            if (!is_file($filepath)) {
                $this->response->setStatus(HttpStatus::NOT_FOUND);
                $this->writer->close($this->response);
                return $this;
            }
            $file = stat($filepath);
            $range = $this->request->header()->get(HttpHeader::RANGE) ?? '=0-';
            $start = (int) substr($range, strpos($range, '=') + 1, strpos($range, '-'));
            $end = min($start + ($chunkSize ?? Definitions::BUFFER_SIZE), $file['size'] - 1);
            $this->response->setStatus(HttpStatus::PARTIAL_CONTENT)->header()->setAll([
                HttpHeader::CONTENT_RANGE => 'bytes ' . $start . '-' . $end . '/' . $file['size'],
                HttpHeader::ACCEPT_RANGES => 'bytes'
            ]);
            return $this->doStream($filepath, $start, $end);
        }

        /**
         * Send HTTP response redirect using a given HTTP referrer, if no referrer given
         * false is returned and redirection fails
         * @param HttpStatus $status HTTP status code, default is 302
         * @return bool
         */
        public function redirectBack(HttpStatus $status = HttpStatus::FOUND): bool
        {
            $url = $this->request->header()->get(HttpHeader::REFERER);
            if ($url !== null) {
                $this->writer->redirect($url, $status);
                return true;
            }
            return false;
        }

        /**
         * Send HTTP response redirect
         * @param string $url new destination
         * @param HttpStatus $status HTTP status code, default is 302
         * @return self
         */
        public function redirect(string $url, HttpStatus $status = HttpStatus::FOUND): self
        {
            $this->writer->redirect($url, $status);
            return $this;
        }

        /**
         * Stream a file to a client
         * @param string $path Path to a file to stream
         * @param int $start Start bytes to stream
         * @param int $end End bytes to stream
         * @return self
         */
        private function doStream(string $path, int $start = 0, int $end = null): self
        {
            $size = filesize($path);
            if ($size <= $start || ($end !== null && $start >= $end)) {
                $this->response->setStatus(HttpStatus::BAD_REQUEST);
                $this->writer->close($this->response);
                return $this;
            }
            $chunk = min($size, Definitions::BUFFER_SIZE);
            $length = $end <= 0 ? $size - $start : $chunk = $end - $start + 1;
            $this->response->header()->setAll([
                HttpHeader::CONTENT_LENGTH => $length,
                HttpHeader::CONTENT_TYPE => MediaType::fromFilename($path)
            ]);
            if ($this->request->method !== 'head') {
                $this->writer->sendFile($this->response, $path, $start, $chunk);
            } else {
                $this->response->setStatus(HttpStatus::NO_CONTENT);
                $this->writer->close($this->response);
            }
            return $this;
        }

        private function registerErrorHandler(): void
        {
            set_error_handler(function (int $errno, string $errstr, string $errfile, int $errline) {
                $this->config->errorHandler(new \ErrorException($errstr, $errno, E_ALL, $errfile, $errline));
                return true;
            });
            set_exception_handler(fn(\Throwable $e) => $this->config->errorHandler($e));
        }

        /**
         * Execute a callback function only when a application is in a given context
         * execution environment. Currently, 'web' and 'api' context are supported by
         * the framework. Developer is advised to use 'web' for web application and 'api'
         * for API application, but can define and handle different application context
         * depending on type of application needs. Client application can supply
         * this context via accept-version header.
         * @param string $context Application execution context.
         * @param callable $cb A callback to execute that accept application object as an argument.
         * @return self
         */
        public function on(string $context, callable $cb): self
        {
            if ($this->platform() === $context) {
                $cb($this);
            }
            return $this;
        }

        /**
         * Get HTTP preferred request context (platform) set by user agent. This
         * value is set via HTTP accept-version header and the accepted values are
         * 'web' and 'api'. User can also set application version after request context,
         * separated by semicolon. If none given, the 'web' context is assumed.
         * @example accept-version=web;1.0
         * @return string|null
         */
        public function platform(): ?string
        {
            if ($this->platform === null) {
                $str = $this->request->header()->get(HttpHeader::ACCEPT_VERSION) ?? 'web';
                $this->platform = strtolower($str);
            }
            return $this->platform;
        }

        public function csrfToken(): Session
        {
            return $this->cart('_gGOd2y$oNO6W');
        }

        /**
         * Create and return cart for storing session values
         * @param string $name Cart name
         * @return Session
         */
        public function cart(string $name): Session
        {
            if (empty($this->appCart[$name])) {
                $this->appCart[$name] = new Session($this, $name);
            }
            return $this->appCart[$name];
        }

        /**
         * Get Storage object representing application web root directory
         * @return Storage A storage object
         */
        public function storage(): Storage
        {
            if (!isset($this->storage)) {
                $this->storage = new Storage($this);
            }
            return $this->storage;
        }

        /**
         * Get static assets object representing application static asset directory
         * @return Asset
         */
        public function asset(): Asset
        {
            if (!isset($this->asset)) {
                $this->asset = new Asset($this);
            }
            return $this->asset;
        }

        /**
         * Manage Graphical User interface with this function
         * @return UI
         */
        public function ui(): UI
        {
            if (!isset($this->ui)) {
                $this->ui = new UI($this);
            }
            return $this->ui;
        }

        /**
         * Render HTML document to user agent and close the HTTP connection.
         * @param \JsonSerializable $dto Data object to be passed to a view file
         * @param bool $useBuffer Set output buffer on so that output can be sent
         * in chunks without closing connection. If false, then connection will
         * be closed and no output can be sent.
         * @return void
         */
        public function render(\JsonSerializable $dto = null, bool $useBuffer = false): void
        {
            $customDto = $dto ?? new HttpMessageDto($this->response->status());
            $type = $this->response->type();
            if ($type === DataConvertor::TYPE_HTML) {
                ob_start();
                $this->ui()->render($customDto->jsonSerialize());
                $this->sendHtml(ob_get_clean(), $type);
            } else if ($type === DataConvertor::TYPE_SSE) {
                ob_start();
                $this->ui()->render($customDto->jsonSerialize());
                $this->sendSse(ob_get_clean(), $type);
            } else if ($type === DataConvertor::TYPE_JS) {
                $this->sendJsonp($customDto->jsonSerialize(), $type);
            } else {
                $this->response->setBody(DataConvertor::convertTo($customDto->jsonSerialize(), $type), $type);
            }
            $this->send($useBuffer);
        }

        private function sendHtml(string $content, string $type): void
        {
            $this->response->setBody($content, $type)->header()
                    ->setIfAbsent(HttpHeader::CONTENT_TYPE, MediaType::TEXT_HTML);
        }

        private function sendJsonp(?array $content, string $type): void
        {
            $callback = $this->request->query('callback') ?? 'callback';
            $data = $callback . '(' . json_encode($content) . ');';
            $this->response->setBody($data, $type)->header()
                    ->setIfAbsent(HttpHeader::CONTENT_TYPE, MediaType::JS);
        }

        private function sendSse(string $content, string $type): void
        {
            $this->response->setBody(DataConvertor::toEventStream($content), $type)
                    ->header()->setIfAbsent(HttpHeader::CACHE_CONTROL, 'no-cache')
                    ->setIfAbsent(HttpHeader::CONTENT_TYPE, MediaType::EVENT_STREAM);
        }

        /**
         * Get dictionary of words/sentences from current application dictionary file.
         * Current dictionary directory name must match with current executing function name
         * and the dictionary file name must be language code supported by your application.
         * @param \JsonSerializable $dto Data to pass to dictionary file.
         * @return array Associative array where key is the word/sentence unique
         * code and the value is the actual word/sentence.
         */
        public function dictionary(\JsonSerializable $dto = null): array
        {
            if ($this->dict === null) {
                $route = $this->request->route();
                $file = $this->module($this->config->languageDir() . $route->resource . $route->callback . '/' . $this->language() . '.php');
                $this->dict = self::getFile($file, $dto);
            }
            return $this->dict;
        }

        private static function getFile(string $loadedFile, \JsonSerializable $dto = null): array
        {
            return require $loadedFile;
        }

        /**
         * Set and/or get current view file to be rendered as HTML to client.
         * @param string|null $path Case sensitive Path to view file, if not provided then
         * the view file will be the same as current executing function name. All views
         * have access to application object as $app
         * @return string Path to view file
         * @see App::render()
         */
        public function view(?string $path = null): string
        {
            return $this->module($this->config->viewDir() . $this->request->route()->resource . ($path ?? $this->request->route()->callback) . '.php');
        }

        /**
         * Get path to current executing module directory
         * @param string|null $path Path to other resource relative to current module directory
         * @return string Path to module directory
         */
        public function module(?string $path = null): string
        {
            return Definitions::DIR_APPS . $this->config->root() . $this->config->moduleDir() . $this->request->route()->module . $path;
        }

        private function classPath(string $method): string
        {
            $class = Definitions::DIRNAME_APPS . $this->config->root();
            $class .= $this->config->moduleDir() . $this->request->route()->module;
            $class .= $this->config->controllers() . '/' . ($method !== 'head' ? $method : 'get');
            return $class . '/' . str_replace('-', '', ucwords(substr($this->request->route()->resource, 1), '-'));
        }

        /**
         * Generate current user application documentation
         * @return array application documentation
         */
        public function documentation(): array
        {
            return (new Generator($this))->generate();
        }

        /**
         * Check whether a user has enough privileges to access a target resource
         * @param string $target Route target, see self::documentation()
         * @return bool True a user has enough privileges, false otherwise.
         * @see self::documentation()
         */
        public function accessGranted(string $target): bool
        {
            if ($this->config->authorizationDisabled()) {
                return true;
            }
            if (empty($this->config->permissionList)) {
                return false;
            }
            return (preg_match('\b' . self::digest($target) . '\b', $this->config->permissionList) === 1);
        }

        /**
         * Dynamically route a user request to mapped resource. The routing mechanism
         * always depends on HTTP method and application endpoint provided by the user.
         * @return void
         */
        public function processRequest(): void
        {
            $classPath = $this->classPath($this->request->method);
            if (!is_file(SERVER_ROOT . $classPath . '.php')) {
                $this->response->setStatus(HttpStatus::NOT_FOUND);
                $this->config->errorHandler();
                return;
            }
            try {
                $className = str_replace('/', '\\', $classPath);
                $cb = Utils::kebab2camelCase(substr($this->request->route()->callback, 1));
                (new $className($this))->$cb();
            } catch (\Exception $ex) {
                $this->response->setStatus(HttpStatus::INTERNAL_SERVER_ERROR);
                $this->config->errorHandler($ex);
            }
        }

        public static function digest(string $str, string $algorithm = 'sha1', int $length = 7): string
        {
            return substr(hash($algorithm, $str), 0, $length);
        }

        /**
         * Set and/or get URL safe from CSRF attack. if CSRF is enabled, then the
         * application will be protected against CSRF attack and the URL will be
         * returned, otherwise the URL will be returned but CSRF will be turned off.
         * @param string|null $urlPath
         * @return string URL safe from CSRF attack
         */
        public function csrf(?string $urlPath = null): string
        {
            $url = $urlPath ?? $this->request->uri->path;
            if ($this->config->csrfProtectionEnabled()) {
                $token = base64_encode(random_bytes(6));
                $this->csrfToken()->add([$token => null]);
                $cookie = (new Cookie())->setName($this->config->csrfTokenName())
                        ->setSameSite(Cookie::SAME_SITE_LAX)
                        ->setValue($token)->setPath($url)->setHttpOnly(true)
                        ->setSecure($this->request->uri->secure());
                $this->response->setCookie($cookie);
            }
            return $this->request->uri->host() . $url;
        }

        /**
         * Get request language code. The request language is obtained from HTTP
         * header accept-language.
         * @return string language code
         */
        public function language(): string
        {
            if (!$this->lang) {
                $reqLangs = $this->request->languages();
                $supportedLangs = $this->config->supportedLanguages();
                foreach ($reqLangs as $lang) {
                    if (!empty($supportedLangs[$lang])) {
                        $this->lang = $lang;
                        return $lang;
                    }
                }
                $this->lang = $this->config->defaultLanguage();
            }
            return $this->lang;
        }
    }

}
