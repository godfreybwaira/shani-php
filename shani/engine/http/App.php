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
    use library\HttpHeader;
    use library\HttpStatus;
    use library\Utils;
    use shani\advisors\Configuration;
    use shani\contracts\ResponseDto;
    use shani\contracts\ResponseWriter;
    use shani\engine\core\Definitions;
    use shani\engine\documentation\Generator;
    use shani\engine\http\bado\RequestEntity;
    use shani\engine\http\bado\ResponseEntity;
    use shani\ServerConfig;

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

        public function __construct(RequestEntity &$req, ResponseEntity &$res, ResponseWriter $writer)
        {
            $this->req = $req;
            $this->res = $res;
            $this->writer = $writer;
            try {
                $cnf = $this->getHostConfiguration();
                $env = $cnf['ENVIRONMENTS'][$cnf['ACTIVE_ENVIRONMENT']];
                $this->config = new $env($this, $cnf);
                $this->res->setCompression($this->config->compressionLevel(), $this->config->compressionMinSize());
                if (!Asset::tryServe($this, $req, $res)) {
                    $this->catchErrors();
                    $this->start();
                }
            } catch (\ErrorException $ex) {
                $this->res->setStatus(HttpStatus::BAD_REQUEST)
                        ->setBody(new HttpResponseDto(HttpStatus::BAD_REQUEST, $ex->getMessage()));
                $this->send($this->res);
            }
        }

        /**
         * Send content to a client application
         * @param ResponseEntity $res
         * @param bool $useBuffer Set output buffer on so that output can be sent
         * in chunks without closing connection. If false, then connection will
         * be closed and no output can be sent.
         * @return void
         */
        public function send(ResponseEntity &$res, bool $useBuffer = false): void
        {
            if ($this->req->method() === 'head') {
                $res->setStatus(HttpStatus::NO_CONTENT);
                $this->writer->send($res, true);
            } else if ($useBuffer) {
                $this->writer->send($res);
            } else {
                $this->writer->close($res);
            }
        }

        /**
         * Get configuration from host application configuration file.
         * @return array Application configuration
         * @throws \ErrorException If bad application version
         */
        private function getHostConfiguration(): array
        {
            $hostname = $this->req->uri()->hostname();
            $host = ServerConfig::host($hostname);
            $requestVersion = $this->req->version();
            if ($requestVersion === null) {
                return $host['VERSIONS'][$host['DEFAULT_VERSION']];
            }
            if (!empty($host['VERSIONS'][$requestVersion])) {
                return $host['VERSIONS'][$requestVersion];
            }
            throw new \ErrorException('Unsupported application version "' . $requestVersion . '"');
        }

        private function catchErrors(): void
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
            if ($this->req->platform() === $context) {
                $cb($this);
            }
            return $this;
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
         * @return void
         */
        public function render(ResponseDto $dto = null): void
        {
            $customDto = $dto ?? new HttpResponseDto($this->response()->status());
            $type = $this->res->type();
            if ($type === 'html' || $type === 'htm') {
                ob_start();
                $this->template()->render($customDto);
                $this->sendAsHtml(ob_get_clean());
            } else if ($type === 'event-stream') {
                ob_start();
                $this->template()->render($customDto);
                $this->sendAsSse(ob_get_clean());
            } else {
                $this->send($customDto, $type);
            }
        }

        /**
         * Get dictionary of words/sentences from current application dictionary file.
         * Current dictionary directory name must match with current executing function name
         * and the dictionary file name must be language code supported by your application.
         * @param array|null $data Data to pass to dictionary file. These data are
         * available on dictionary file via $data variable
         * @return array Associative array where key is the word/sentence unique
         * code and the value is the actual word/sentence.
         */
        public function dictionary(?array $data = null): array
        {
            if ($this->dict === null) {
                $file = $this->module($this->config->languageDir() . $this->req->resource() . $this->req->callback() . '/' . $this->language() . '.php');
                $this->dict = self::getFile($file, $data);
            }
            return $this->dict;
        }

        private static function getFile(string $loadedFile, ?array &$data): array
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
            return $this->module($this->config->viewDir() . $this->req->resource() . ($path ?? $this->req->callback()) . '.php');
        }

        /**
         * Get path to current executing module directory
         * @param string|null $path Path to other resource relative to current module directory
         * @return string Path to module directory
         */
        public function module(?string $path = null): string
        {
            return Definitions::DIR_APPS . $this->config->root() . $this->config->moduleDir() . $this->req->module() . $path;
        }

        /**
         * Start executing user application
         * @return void
         */
        private function start(): void
        {
            if ($this->req->uri()->path() === '/') {
                $this->req->rewriteUrl($this->config->homepage());
            }
            Session::start($this);
            $middleware = new Middleware($this);
            $securityAdvisor = $this->config->middleware($middleware);
            $middleware->runWith($securityAdvisor);
        }

        private function getClassPath(string $method): string
        {
            $class = Definitions::DIRNAME_APPS . $this->config->root();
            $class .= $this->config->moduleDir() . $this->req->module();
            $class .= $this->config->controllers() . '/' . ($method !== 'head' ? $method : 'get');
            return $class . '/' . str_replace('-', '', ucwords(substr($this->req->resource(), 1), '-'));
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
        public function route(): void
        {
            $method = $this->req->method();
            $classPath = $this->getClassPath($method);
            if (!is_file(SERVER_ROOT . $classPath . '.php')) {
                $this->res->setStatus(HttpStatus::NOT_FOUND);
                $this->config->httpErrorHandler();
                return;
            }
            try {
                $className = str_replace('/', '\\', $classPath);
                $cb = Utils::kebab2camelCase(substr($this->req->callback(), 1));
                (new $className($this))->$cb($this->req, $this->res);
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
            $url = $urlPath ?? $this->req->uri()->path();
            if ($this->config->csrfProtectionEnabled()) {
                $token = base64_encode(random_bytes(6));
                $this->csrfToken()->add([$token => null]);
                $cookie = (new Cookie())->setName($this->config->csrfTokenName())
                        ->setSameSite(Cookie::SAME_SITE_LAX)
                        ->setValue($token)->setPath($url)->setHttpOnly(true)
                        ->setSecure($this->req->uri()->secure());
                $this->res->setCookie($cookie);
            }
            return $this->req->uri()->host() . $url;
        }

        /**
         * Get request language code. The request language is obtained from HTTP
         * header accept-language.
         * @return string language code
         */
        public function language(): string
        {
            if (!$this->lang) {
                $reqLangs = $this->req->languages();
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
