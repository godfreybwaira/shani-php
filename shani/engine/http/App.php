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
    use library\HttpStatus;
    use shani\advisors\Configuration;

    final class App
    {

        private Asset $asset;
        private Request $req;
        private Response $res;
        private ?string $lang;
        private Storage $storage;
        private Configuration $config;
        private ?Template $template = null;
        private ?array $appCart = null, $dict = null;

        public function __construct(\shani\contracts\ServerRequest $req, \shani\contracts\ServerResponse $res)
        {
            $this->lang = null;
            $this->req = new Request($req);
            $this->res = new Response($this, $res);
            try {
                $cnf = $this->getHostConfiguration();
                $env = $cnf['ENVIRONMENTS'][$cnf['ACTIVE_ENVIRONMENT']];
                $this->config = new $env($this, $cnf);
                UploadedFile::setDefaultStorage($this->storage()->pathTo());
                if (!Asset::tryServe($this)) {
                    $this->catchErrors();
                    $this->start();
                }
            } catch (\ErrorException $ex) {
                $this->response()->setStatus(HttpStatus::BAD_REQUEST)->send($ex->getMessage());
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
            $host = \shani\ServerConfig::host($hostname);
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
                $this->config->handleApplicationErrors(new \ErrorException($errstr, $errno, E_ALL, $errfile, $errline));
                return true;
            });
            set_exception_handler(fn(\Throwable $e) => $this->config->handleApplicationErrors($e));
        }

        /**
         * Execute a callback function only when a application is in web context.
         * This occurs when user, via accept-version header selects 'web' as the
         * default application context
         * @param callable $cb A callback to execute that accept application object
         * as argument.
         * @return self
         */
        public function web(callable $cb): self
        {
            if ($this->req->platform() === 'web') {
                $cb($this);
            }
            return $this;
        }

        /**
         * Execute a callback function only when a application is in api context.
         * This occurs when user, via accept-version header selects 'api' as the
         * default application context
         * @param callable $cb A callback to execute that accept application object
         * as argument.
         */
        public function api(callable $cb): self
        {
            if ($this->req->platform() === 'api') {
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
         * @return Request Request object
         */
        public function request(): Request
        {
            return $this->req;
        }

        /**
         * Get HTTP response object
         * @return Response Response object
         */
        public function response(): Response
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
         * @return Storage
         */
        public function storage(string $name = null): Storage
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
         * @return \gui\Template
         */
        public function template(): \gui\Template
        {
            if (!isset($this->template)) {
                $this->template = new \gui\Template($this);
            }
            return $this->template;
        }

        /**
         * Render HTML document to user agent and close the HTTP connection.
         * @param array|null $data Values to be passed on view file
         * @return void
         */
        public function render(?array $data = null): void
        {
            $type = $this->res->type();
            if ($type === null || $type === 'html') {
                ob_start();
                $this->template()->render($data);
                $this->res->sendAsHtml(ob_get_clean());
            } else if ($type !== 'event-stream') {
                $this->res->send($data);
            } else {
                ob_start();
                $this->template()->render($data);
                $this->res->sendAsSse(ob_get_clean());
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
            return \shani\engine\core\Definitions::DIR_APPS . $this->config->root() . $this->config->moduleDir() . $this->req->module() . $path;
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
            $class = \shani\engine\core\Definitions::DIRNAME_APPS . $this->config->root();
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
            return \shani\engine\core\UserAppDoc::generate($this);
        }

        /**
         * Dynamically route a user request to mapped resource. The routing mechanism
         * always depends on HTTP method and application endpoint provided by the user.
         * @return void
         */
        public function route(): void
        {
            $method = $this->req->method();
            if (!in_array($method, $this->config->requestMethods())) {
                $this->res->setStatus(HttpStatus::METHOD_NOT_ALLOWED);
                $this->config->handleHttpErrors();
                return;
            }
            $classPath = $this->getClassPath($method);
            if (!is_file(SERVER_ROOT . $classPath . '.php')) {
                $this->res->setStatus(HttpStatus::NOT_FOUND);
                $this->config->handleHttpErrors();
            } else {
                try {
                    $className = str_replace('/', '\\', $classPath);
                    $cb = \library\Utils::kebab2camelCase(substr($this->req->callback(), 1));
                    (new $className($this))->$cb();
                } catch (\Exception $ex) {
                    $this->res->setStatus(HttpStatus::INTERNAL_SERVER_ERROR);
                    $this->config->handleHttpErrors($ex->getMessage());
                }
            }
        }

        public static function digest(string $str, string $algorithm = 'crc32b'): string
        {
            return hash($algorithm, $str);
        }

        /**
         * Set and/or get URL safe from CSRF attack. if CSRF is enabled, then the
         * application will be protected against CSRF attack and the URL will be
         * returned, otherwise the URL will be returned but CSRF will be turned off.
         * @param string|null $path
         * @return string URL safe from CSRF attack
         */
        public function csrf(?string $path = null): string
        {
            $protection = $this->config->csrf();
            $url = $path ?? $this->req->uri()->path();
            if ($protection !== Configuration::CSRF_OFF) {
                $token = base64_encode(random_bytes(6));
                if ($protection === Configuration::CSRF_STRICT) {
                    $this->csrfToken()->add([self::digest($url) => $token]);
                } else {
                    $this->csrfToken()->add([$token => self::digest($url)]);
                }
                $cookie = (new \library\HttpCookie())->setName('csrf_token')
                        ->setSameSite(\library\HttpCookie::SAME_SITE_STRICT)
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
