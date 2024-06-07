<?php

/**
 * Description of Response
 * @author coder
 *
 * Created on: Feb 11, 2024 at 6:50:02 PM
 */

namespace shani\engine\http {

    use library\HttpStatus;
    use shani\engine\config\AppConfig;
    use shani\engine\authorization\Authorization;

    final class App
    {

        private $template;
        private Host $host;
        private Asset $asset;
        private Request $req;
        private Response $res;
        private ?string $lang;
        private AppConfig $config;
        private ?Authorization $auth;
        private \library\Logger $logger;
        private array $appCart = [], $dict = [];

        private const USER_NAME = '_u0s4e5R0s$s', CSRF_TOKENS = '_gGOd2y$oNO6W';

        public function __construct(\shani\adaptor\Request $req, \shani\adaptor\Response $res, Host $host)
        {
            $this->host = $host;
            $this->lang = $this->auth = null;
            $this->req = new Request($req);
            $this->res = new Response($this->req, $res);
            $cnf = $host->getConfig($this->req->version());
            if ($cnf !== null) {
                $this->config = new $cnf($this);
                if (!Asset::tryServe($this)) {
                    self::catchErrors($this);
                    $this->start();
                }
            } else {
                $this->response()->setStatus(HttpStatus::BAD_REQUEST)->send('Unsupported application version');
            }
        }

        private static function catchErrors(App &$app): void
        {
            $logger = $app->logger();
            set_error_handler(function (int $errno, string $errstr, string $errfile, int $errline) use (&$logger) {
                $logger->appError($errno, $errstr, $errfile, $errline);
                return true;
            });
            set_exception_handler(fn(\Throwable $e) => $logger->exception($e));
        }

        public function logger(): \library\Logger
        {
            if (!isset($this->logger)) {
                $this->logger = new \library\Logger($this->asset()->directory('/.logs'));
            }
            return $this->logger;
        }

        public function web(callable $cb): self
        {
            if ($this->req->platform() === 'web') {
                $cb($this);
            }
            return $this;
        }

        public function api(callable $cb): self
        {
            if ($this->req->platform() === 'api') {
                $cb($this);
            }
            return $this;
        }

        public function config(): AppConfig
        {
            return $this->config;
        }

        public function request(): Request
        {
            return $this->req;
        }

        public function response(): Response
        {
            return $this->res;
        }

        public function host(): Host
        {
            return $this->host;
        }

        public function auth(): Authorization
        {
            if ($this->auth === null) {
                $this->auth = new Authorization($this->config);
            }
            return $this->auth;
        }

        public function homepage(): string
        {
            return $this->auth()->verified() ? $this->config->homeAuth() : $this->config->homeGuest();
        }

        public function csrfToken(): Session
        {
            return $this->cart(self::CSRF_TOKENS);
        }

        public function close(): void
        {
            Session::stop();
            $this->res->redirect('/');
        }

        public function user(): Session
        {
            return $this->cart(self::USER_NAME);
        }

        public function cart(string $name): Session
        {
            if (empty($this->appCart[$name])) {
                $this->appCart[$name] = new Session($name);
            }
            return $this->appCart[$name];
        }

        public function asset(): Asset
        {
            if (!isset($this->asset)) {
                $this->asset = new Asset($this);
            }
            return $this->asset;
        }

        public function gui()
        {
            if (!isset($this->template)) {
                $version = $this->config->templateVersion();
                $controller = \shani\Config::template($version);
                $this->template = new $controller($this);
            }
            return $this->template;
        }

        public function render($data = null, $state = null): void
        {
            $type = $this->res->type();
            if ($type === null || $type === 'html') {
                ob_start();
                $this->gui()->render($data, $state);
                $this->res->sendHtml(ob_get_clean());
            } else if ($type === 'event-stream') {
                ob_start();
                $this->gui()->render($data, $state);
                $this->res->sendSse(ob_get_clean());
            } else {
                $this->res->send($data);
            }
        }

        public function dictionary(string $path = null): array
        {
            if ($path !== null) {
                $this->dict = require $path;
            } else if (empty($this->dict)) {
                $file = $this->module() . $this->config->languageDir() . $this->req->resource();
                $this->dict = require $file . $this->req->callback() . '/' . $this->language() . '.php';
            }
            return $this->dict;
        }

        public function view(string $path = null): string
        {
            return $this->module() . $this->config->viewDir() . $this->req->resource() . ($path ?? $this->req->callback()) . '.php';
        }

        public function module(): string
        {
            return \shani\engine\core\Path::APPS . $this->config->root() . $this->config->moduleDir() . $this->req->module();
        }

        private function boot(): callable
        {
            return function (string $event) {
                if ($event === 'before') {
                    $code = $this->response()->statusCode();
                    if ($code === HttpStatus::OK) {
                        return $this->submit($this->request()->method());
                    }
                    $this->showError($code);
                }
            };
        }

        private function start(): void
        {
            $path = $this->req->uri()->location();
            $this->req->forward($path === '/' ? $this->homepage() : $path);
            Session::start($this);
            $middleware = new \shani\engine\middleware\Register($this, $this->boot());
            $this->config->middleware($middleware);
            $middleware->run();
        }

        private function getClass(string $resource, string $method): string
        {
            $class = \shani\engine\core\Path::DIR_APPS . $this->config->root() . $this->config->moduleDir();
            $class .= $this->req->module() . $this->config->sourceDir() . '/';
            $class .= ($method !== 'head' ? $method : 'get');
            return $class . '/' . str_replace('-', '', ucwords(substr($resource, 1), '-'));
        }

        public function documentation(): array
        {
            return \shani\engine\core\Documentor::generate($this);
        }

        private function submit(string $method, int $trials = 1): void
        {
            $classPath = $this->getClass($this->req->resource(), $method);
            if (is_file(SERVER_ROOT . $classPath . '.php')) {
                $className = str_replace('/', '\\', $classPath);
                $obj = new $className($this);
                $cb = \library\Utils::kebab2camelCase(substr($this->req->callback(), 1));
                if (is_callable([$obj, $cb])) {
                    $obj->$cb();
                } else {
                    $this->showError(HttpStatus::NOT_FOUND, $trials);
                }
            } else {
                $this->showError(HttpStatus::METHOD_NOT_ALLOWED, $trials);
            }
        }

        private function showError(int $statusCode, int $trials = 1): void
        {
            $this->res->setStatus($statusCode);
            $fallback = $this->config->fallback();
            if ($fallback !== null && $trials < 3) {
                $this->req->forward($fallback . '/s' . $statusCode);
                $this->submit('get', $trials + 1);
            } else {
                $this->res->send();
            }
        }

        public function csrf(string $path = null): string
        {
            $protection = $this->config->csrf();
            $url = $path ?? $this->req->uri()->path();
            if ($protection !== \shani\engine\config\CSRF::PROTECTION_OFF) {
                $token = base64_encode(random_bytes(6));
                if ($protection === \shani\engine\config\CSRF::PROTECTION_STRICT) {
                    $this->csrfToken()->add([\library\Utils::digest($url) => $token]);
                } else {
                    $this->csrfToken()->add([$token => \library\Utils::digest($url)]);
                }
                $cookie = (new \library\HttpCookie())->setName('csrf_token')
                        ->setSameSite(\library\HttpCookie::SAME_SITE_STRICT)
                        ->setValue($token)->setPath($url)->setHttpOnly(true)
                        ->setSecure($this->req->secure());
                $this->res->setCookie($cookie);
            }
            return $this->req->uri()->hostname() . $url;
        }

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
                $this->lang = $this->config->languageDefault();
            }
            return $this->lang;
        }
    }

}
