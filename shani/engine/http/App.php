<?php

/**
 * Application class is the core application engine that run the entire user
 * application. This is where application execution and routing takes place
 * @author coder
 *
 * Created on: Feb 11, 2024 at 6:50:02 PM
 */

namespace shani\engine\http {

    use gui\Template;
    use library\Cookie;
    use library\DataConvertor;
    use library\http\HttpHeader;
    use library\http\HttpStatus;
    use library\http\RequestEntity;
    use library\Utils;
    use shani\advisors\Configuration;
    use shani\contracts\ResponseDto;
    use shani\contracts\ResponseWriter;
    use shani\engine\core\Definitions;
    use shani\engine\documentation\Generator;
    use library\http\ResponseEntity;
    use shani\ServerConfig;
    use shani\engine\core\Framework;
    use library\MediaType;

    final class App
    {

        private Asset $asset;
        private Storage $storage;
        private ?string $lang = null;
        private readonly RequestEntity $req;
        private readonly ResponseEntity $res;
        private readonly Configuration $config;
        private readonly ResponseWriter $writer;
        private ?Template $template = null;
        private ?array $appCart = null, $dict = null;
        private ?string $platform = null, $requestVersion = null;

        public function __construct(ResponseEntity &$res, ResponseWriter $writer)
        {
            $this->res = $res;
            $this->writer = $writer;
            $this->req = $res->request();
            $this->res->header()
                    ->set(HttpHeader::X_CONTENT_TYPE_OPTIONS, 'nosniff')
                    ->set(HttpHeader::SERVER, Framework::NAME);
            try {
                $cnf = $this->getHostConfiguration();
                $env = $cnf['ENVIRONMENTS'][$cnf['ACTIVE_ENVIRONMENT']];
                $this->config = new $env($this, $cnf);
                $this->res->setCompression($this->config->compressionLevel(), $this->config->compressionMinSize());
                if (!Asset::tryServe($this)) {
                    $this->registerErrorHandler();
                    $this->start();
                }
            } catch (\ErrorException $ex) {
                $this->res->setStatus(HttpStatus::BAD_REQUEST)->setBody($ex->getMessage());
                $this->send();
            }
        }

        /**
         * Send content to a client application
         * @param bool $useBuffer Set output buffer on so that output can be sent
         * in chunks without closing connection. If false, then connection will
         * be closed and no output can be sent.
         * @return void
         */
        public function send(bool $useBuffer = false): void
        {
            if ($this->req->method === 'head') {
                $this->res->setStatus(HttpStatus::NO_CONTENT);
                $this->writer->send($this->res, true);
            } else if ($useBuffer) {
                $this->writer->send($this->res);
            } else {
                $this->writer->close($this->res);
            }
        }

        /**
         * Stream  a file as HTTP response
         * @param string $filepath Path to a file to stream
         * @param int|null $chunkSize Number of bytes to stream every turn, default is 1MB
         * @return self
         */
        public function stream(string $filepath, ?int $chunkSize = null): self
        {
            if (!is_file($filepath)) {
                $this->res->setStatus(HttpStatus::NOT_FOUND);
                $this->writer->close($this->res);
                return $this;
            }
            $file = stat($filepath);
            $range = $this->req->header()->get(HttpHeader::RANGE) ?? '=0-';
            $start = (int) substr($range, strpos($range, '=') + 1, strpos($range, '-'));
            $end = min($start + ($chunkSize ?? Definitions::BUFFER_SIZE), $file['size'] - 1);
            $this->res->setStatus(HttpStatus::PARTIAL_CONTENT)
                    ->header()->setAll([
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
            $url = $this->req->header()->get(HttpHeader::REFERER);
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
                $this->res->setStatus(HttpStatus::BAD_REQUEST);
                $this->writer->close($this->res);
                return $this;
            }
            $chunk = min($size, Definitions::BUFFER_SIZE);
            $length = $end <= 0 ? $size - $start : $chunk = $end - $start + 1;
            $this->res->header()->setAll([
                HttpHeader::CONTENT_LENGTH => $length,
                HttpHeader::CONTENT_TYPE => MediaType::fromFilename($path)
            ]);
            if ($this->req->method !== 'head') {
                $this->writer->sendFile($this->res, $path, $start, $chunk);
            } else {
                $this->res->setStatus(HttpStatus::NO_CONTENT);
                $this->writer->close($this->res);
            }
            return $this;
        }

        /**
         * Get configuration from host application configuration file.
         * @return array Application configuration
         * @throws \ErrorException If bad application version
         */
        private function getHostConfiguration(): array
        {
            $hostname = $this->req->uri->hostname();
            $host = ServerConfig::host($hostname);
            $version = $this->version();
            if ($version === null) {
                return $host['VERSIONS'][$host['DEFAULT_VERSION']];
            }
            if (!empty($host['VERSIONS'][$version])) {
                return $host['VERSIONS'][$version];
            }
            throw new \ErrorException('Unsupported application version "' . $version . '"');
        }

        private function registerErrorHandler(): void
        {
            set_error_handler(function (int $errno, string $errstr, string $errfile, int $errline) {
                $this->config->applicationErrorHandler(new \ErrorException($errstr, $errno, E_ALL, $errfile, $errline));
                return true;
            });
            set_exception_handler(fn(\Throwable $e) => $this->config->applicationErrorHandler($e));
        }

        /**
         * Execute a callback function only when a application is in a given context
         * execution environment. Currently, 'web' and 'api' context are supported by
         * the framework. Developer is encouraged to use 'web' for web application and 'api'
         * for API application, but can define and handle different application context
         * depending on type of application and the need. Client application can supply
         * this context via accept-version header.
         * @param string $context Application execution context.
         * @param callable $cb A callback to execute that accept application object as argument.
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
                $str = $this->req->header()->get(HttpHeader::ACCEPT_VERSION);
                if ($str === null) {
                    $this->platform = 'web';
                } else {
                    $list = explode(';', strtolower($str));
                    $this->requestVersion = !empty($list[1]) ? trim($list[1]) : null;
                    $this->platform = $list[0];
                }
            }
            return $this->platform;
        }

        /**
         * Get application version requested via HTTP.
         * @return string|null
         */
        public function version(): ?string
        {
            if ($this->requestVersion === null) {
                $this->platform();
            }
            return $this->requestVersion;
        }

        /**
         * Get current loaded application configurations.
         * @return Configuration Current application loaded configurations
         */
        public function config(): Configuration
        {
            return $this->config;
        }

        /**
         * Get HTTP request object
         * @return RequestEntity Request object
         */
        public function request(): RequestEntity
        {
            return $this->req;
        }

        /**
         * Get HTTP response object
         * @return ResponseEntity Response object
         */
        public function response(): ResponseEntity
        {
            return $this->res;
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
         * Get the current HTML template. This function can be used to customize
         * HTML template before sending to user agent.
         * @return Template
         */
        public function template(): Template
        {
            if (!isset($this->template)) {
                $this->template = new Template($this);
            }
            return $this->template;
        }

        /**
         * Render HTML document to user agent and close the HTTP connection.
         * @param ResponseDto $dto Data object to be passed to a view file
         * @param bool $useBuffer Set output buffer on so that output can be sent
         * in chunks without closing connection. If false, then connection will
         * be closed and no output can be sent.
         * @return void
         */
        public function render(ResponseDto $dto = null, bool $useBuffer = false): void
        {
            $type = $this->res->type();
            if ($type === DataConvertor::TYPE_HTML) {
                ob_start();
                $this->template()->render($dto);
                $this->sendHtml(ob_get_clean(), $type);
            } else if ($type === DataConvertor::TYPE_SSE) {
                ob_start();
                $this->template()->render($dto);
                $this->sendSse(ob_get_clean(), $type);
            } else if ($type === DataConvertor::TYPE_JS) {
                $this->sendJsonp($dto, $type);
            } else if ($dto !== null) {
                $this->res->setBody(DataConvertor::convertTo($dto->asMap(), $type), $type);
            }
            $this->send($useBuffer);
        }

        private function sendHtml(string $content, string $type): void
        {
            $this->res->setBody($content, $type)->header()
                    ->setIfAbsent(HttpHeader::CONTENT_TYPE, MediaType::TEXT_HTML);
        }

        private function sendJsonp(ResponseDto $dto, string $type): void
        {
            $callback = $this->req->query('callback') ?? 'callback';
            $data = $callback . '(' . json_encode($dto->asMap()) . ');';
            $this->res->setBody($data, $type)->header()
                    ->setIfAbsent(HttpHeader::CONTENT_TYPE, MediaType::JS);
        }

        private function sendSse(string $content, string $type): void
        {
            $this->res->setBody(DataConvertor::toEventStream($content), $type)
                    ->header()->setIfAbsent(HttpHeader::CACHE_CONTROL, 'no-cache')
                    ->setIfAbsent(HttpHeader::CONTENT_TYPE, MediaType::EVENT_STREAM);
        }

        /**
         * Get use request language codes. These values will be used for application
         * language selection if the values are supported.
         * @return array users accepted languages
         */
        public function languages(): array
        {
            $accept = $this->req->header()->get(HttpHeader::ACCEPT_LANGUAGE);
            if ($accept !== null) {
                $langs = explode(',', $accept);
                return array_map(fn($val) => strtolower(trim(explode(';', $val)[0])), $langs);
            }
            return [];
        }

        /**
         * Get dictionary of words/sentences from current application dictionary file.
         * Current dictionary directory name must match with current executing function name
         * and the dictionary file name must be language code supported by your application.
         * @param ResponseDto $dto Data to pass to dictionary file. These data are
         * available on dictionary file via $data variable
         * @return array Associative array where key is the word/sentence unique
         * code and the value is the actual word/sentence.
         */
        public function dictionary(ResponseDto $dto = null): array
        {
            if ($this->dict === null) {
                $route = $this->req->route();
                $file = $this->module($this->config->languageDir() . $route->resource . $route->callback . '/' . $this->language() . '.php');
                $this->dict = self::getFile($file, $dto?->asMap());
            }
            return $this->dict;
        }

        private static function getFile(string $loadedFile, ?array $data): array
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
            return $this->module($this->config->viewDir() . $this->req->route()->resource . ($path ?? $this->req->route()->callback) . '.php');
        }

        /**
         * Get path to current executing module directory
         * @param string|null $path Path to other resource relative to current module directory
         * @return string Path to module directory
         */
        public function module(?string $path = null): string
        {
            return Definitions::DIR_APPS . $this->config->root() . $this->config->moduleDir() . $this->req->route()->module . $path;
        }

        /**
         * Start executing user application
         * @return void
         */
        private function start(): void
        {
            if ($this->req->uri->path() === '/') {
                $this->req->setRoute($this->config->homepage());
            }
            Session::start($this);
            $middleware = new Middleware($this);
            $securityAdvisor = $this->config->middleware($middleware);
            $middleware->runWith($securityAdvisor);
        }

        private function getClassPath(string $method): string
        {
            $class = Definitions::DIRNAME_APPS . $this->config->root();
            $class .= $this->config->moduleDir() . $this->req->route()->module;
            $class .= $this->config->controllers() . '/' . ($method !== 'head' ? $method : 'get');
            return $class . '/' . str_replace('-', '', ucwords(substr($this->req->route()->resource, 1), '-'));
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
         * @param string $targetId Target id, see self::documentation()
         * @return bool True a user has enough privileges, false otherwise.
         * @see self::documentation()
         */
        public function hasAuthority(string $targetId): bool
        {
            if ($this->config->disableSecurityAdvisor()) {
                return true;
            }
            return (preg_match('\b' . $targetId . '\b', $this->config->userPermissions()) === 1);
        }

        /**
         * Dynamically route a user request to mapped resource. The routing mechanism
         * always depends on HTTP method and application endpoint provided by the user.
         * @return void
         */
        public function processRequest(): void
        {
            $classPath = $this->getClassPath($this->req->method);
            if (!is_file(SERVER_ROOT . $classPath . '.php')) {
                $this->res->setStatus(HttpStatus::NOT_FOUND);
                $this->config->httpErrorHandler();
                return;
            }
            try {
                $className = str_replace('/', '\\', $classPath);
                $cb = Utils::kebab2camelCase(substr($this->req->route()->callback, 1));
                (new $className($this))->$cb();
            } catch (\Exception $ex) {
                $this->res->setStatus(HttpStatus::INTERNAL_SERVER_ERROR);
                $this->config->httpErrorHandler($ex->getMessage());
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
            $url = $urlPath ?? $this->req->uri->path();
            if ($this->config->csrfProtectionEnabled()) {
                $token = base64_encode(random_bytes(6));
                $this->csrfToken()->add([$token => null]);
                $cookie = (new Cookie())->setName($this->config->csrfTokenName())
                        ->setSameSite(Cookie::SAME_SITE_LAX)
                        ->setValue($token)->setPath($url)->setHttpOnly(true)
                        ->setSecure($this->req->uri->secure());
                $this->res->setCookie($cookie);
            }
            return $this->req->uri->host() . $url;
        }

        /**
         * Get request language code. The request language is obtained from HTTP
         * header accept-language.
         * @return string language code
         */
        public function language(): string
        {
            if (!$this->lang) {
                $reqLangs = $this->languages();
                $appLangs = $this->config->languages();
                foreach ($reqLangs as $lang) {
                    if (!empty($appLangs[$lang])) {
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
