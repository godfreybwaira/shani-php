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

    final class App
    {

        private $template;
        private Host $host;
        private Asset $asset;
        private Request $req;
        private Response $res;
        private AppConfig $config;
        private \library\Logger $logger;
        private array $cart = [], $dict = [];
        private ?string $lang, $root, $sessId;

        private const USER_NAME = '_u0s4e5R0s$s', USER_ROLES = '_mGnUs$nrWM0', APP_TOKENS = '_gGOd2y$oNO6W';

        public function __construct(\shani\adaptor\Request $req, \shani\adaptor\Response $res, Host $host)
        {
            $this->host = $host;
            $this->lang = $this->root = null;
            $this->req = new Request($req);
            $this->res = new Response($this->req, $res);
            if (!Asset::tryServe($this)) {
                self::catchErrors($this);
                $this->start();
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
                $this->logger = new \library\Logger($this->asset()->private());
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

        public function authenticated(): bool
        {
            return $this->user()->exists();
        }

        public function homepage(): string
        {
            return $this->authenticated() ? $this->config->homeAuth() : $this->config->homeGuest();
        }

        public function authorized(string $path = null): bool
        {
            $url = $path === '/' ? $this->homepage() : $path;
            $code = \library\Utils::digest($this->req->path($url));
            return strpos($this->roles(), $code) !== false;
        }

        public function roles(string $roles = null): ?string
        {
            if ($roles === null) {
                return $this->session(self::USER_ROLES)->get();
            }
            $this->session(self::USER_ROLES)->put($roles);
        }

        public function user(): Session
        {
            return $this->session(self::USER_NAME);
        }

        public function token(): Session
        {
            return $this->session(self::APP_TOKENS);
        }

        public function close(): void
        {
            Session::stop($this);
            $this->res->redirect('/');
        }

        public function session(string $name): Session
        {
            if (empty($this->cart[$name])) {
                $this->cart[$name] = new Session($this->sessId, $name);
            }
            return $this->cart[$name];
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
            return \shani\engine\core\Path::APP . $this->root . $this->config->moduleDir() . $this->req->module();
        }

        private function boot(): callable
        {
            return function (string $event) {
                if ($event === 'before') {
                    $code = $this->response()->statusCode();
                    if ($code === HttpStatus::OK) {
                        return $this->submit($this->request()->method());
                    }
                    $this->error($code);
                }
            };
        }

        private function start(): void
        {
            $cnf = $this->host->configuration($this->req->version());
            if ($cnf !== null) {
                $path = $this->req->uri()->location();
                $this->req->forward($path === '/' ? $this->homepage() : $path);
                $this->config = new $cnf($this);
                $this->root = $this->config->root();
                $this->sessId = Session::start($this);
                $middleware = new \shani\engine\middleware\Register($this, $this->boot());
                $this->config->middleware($middleware);
                $middleware->run();
                return;
            }
            $this->response()->setStatus(HttpStatus::BAD_REQUEST)->send('Unsupported application version');
        }

        private function getClass(string $resource, string $method): string
        {
            $class = \shani\engine\core\Directory::APP . $this->root . $this->config->moduleDir();
            $class .= $this->req->module() . $this->config->sourceDir() . '/';
            $class .= ($method !== 'head' ? $method : 'get');
            $class .= '\\' . str_replace('-', '', ucwords(substr($resource, 1), '-'));
            return str_replace('/', '\\', $class);
        }

        private function submit(string $method, int $trials = 1): void
        {
            try {
                $Class = $this->getClass($this->req->resource(), $method);
                $obj = new $Class($this);
                $cb = \library\Utils::kebab2camelCase(substr($this->req->callback(), 1));
                if (is_callable([$obj, $cb])) {
                    $obj->$cb();
                } elseif (class_exists($Class)) {
                    $this->error(HttpStatus::NOT_FOUND, $trials);
                } else {
                    $this->error(HttpStatus::METHOD_NOT_ALLOWED, $trials);
                }
            } catch (\Exception $e) {
                $this->logger()->exception($e);
                $this->error(HttpStatus::INTERNAL_SERVER_ERROR, $trials);
            }
        }

        private function error(int $statusCode, int $trials = 1): void
        {
            $this->res->setStatus($statusCode);
            $fallback = $this->config->fallback();
            if ($fallback !== null && $trials < 3) {
                $this->req->forward($fallback . '/s' . $statusCode);
                $this->submit('get', $trials + 1);
            } else {
                $this->res->sendHeaders();
            }
        }

        public function csrf(string $path = null): string
        {
            $csrf = $this->config->csrf();
            $url = $path ?? $this->req->uri()->path();
            if ($csrf !== \shani\engine\config\CSRF::PROTECTION_OFF) {
                $token = base64_encode(random_bytes(6));
                if ($csrf === \shani\engine\config\CSRF::PROTECTION_STRICT) {
                    $this->token()->add([\library\Utils::digest($url) => $token]);
                } else {
                    $this->token()->add([$token => \library\Utils::digest($url)]);
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
