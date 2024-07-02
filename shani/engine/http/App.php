<?php

/**
 * Description of Response
 * @author coder
 *
 * Created on: Feb 11, 2024 at 6:50:02 PM
 */

namespace shani\engine\http {

    use library\HttpStatus;
    use shani\engine\core\AutoConfig;

    final class App
    {

        private $template;
        private Host $host;
        private Asset $asset;
        private Request $req;
        private Response $res;
        private ?string $lang;
        private AutoConfig $config;
        private array $appCart = [], $dict = [];

        private const CSRF_TOKENS = '_gGOd2y$oNO6W';

        public function __construct(\shani\contracts\Request $req, \shani\contracts\Response $res, Host $host)
        {
            $this->lang = null;
            $this->host = $host;
            $this->req = new Request($req);
            $this->res = new Response($this->req, $res);
            $cnf = $host->getEnvironment($this->req->version());
            if ($cnf !== null) {
                $this->config = new $cnf($this);
                if (!Asset::tryServe($this)) {
                    $this->catchErrors();
                    $this->start();
                }
            } else {
                $this->response()->setStatus(HttpStatus::BAD_REQUEST)->send('Unsupported application version');
            }
        }

        private function catchErrors(): void
        {
            set_error_handler(function (int $errno, string $errstr, string $errfile, int $errline) {
                $this->config->handleApplicationErrors(new \ErrorException($errstr, $errno, E_ALL, $errfile, $errline));
                return true;
            });
            set_exception_handler(fn(\Throwable $e) => $this->config->handleApplicationErrors($e));
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

        public function config(): AutoConfig
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

        public function csrfToken(): Session
        {
            return $this->cart(self::CSRF_TOKENS);
        }

        public function close(): void
        {
            Session::stop();
            $this->res->redirect('/');
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

        public function gui(string $version = '1.0')
        {
            if ($version !== null && !isset($this->template)) {
                $controller = \shani\ServerConfig::template($version);
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

        private function start(): void
        {
            $path = $this->req->uri()->location();
            $this->req->forward($path === '/' ? $this->config->homepage() : $path);
            Session::start($this);
            $middleware = new Middleware($this);
            $this->config->middleware($middleware);
            $middleware->run();
        }

        private function getClass(string $resource, string $method): string
        {
            $class = \shani\engine\core\Path::DIR_APPS . $this->config->root() . $this->config->moduleDir();
            $class .= $this->req->module() . $this->config->requestMethodsDir() . '/';
            $class .= ($method !== 'head' ? $method : 'get');
            return $class . '/' . str_replace('-', '', ucwords(substr($resource, 1), '-'));
        }

        public function documentation(): array
        {
            return \shani\engine\core\Documentor::generate($this);
        }

        public function submit(): void
        {
            $classPath = $this->getClass($this->req->resource(), $this->req->method());
            if (is_file(SERVER_ROOT . $classPath . '.php')) {
                $className = str_replace('/', '\\', $classPath);
                $obj = new $className($this);
                $cb = \library\Utils::kebab2camelCase(substr($this->req->callback(), 1));
                if (is_callable([$obj, $cb])) {
                    $obj->$cb();
                } else {
                    $this->res->setStatus(HttpStatus::NOT_FOUND);
                    $this->config->handleHttpErrors();
                }
            } else {
                $this->res->setStatus(HttpStatus::METHOD_NOT_ALLOWED);
                $this->config->handleHttpErrors();
            }
        }

        public function csrf(string $path = null): string
        {
            $protection = $this->config->csrf();
            $url = $path ?? $this->req->uri()->path();
            if ($protection !== AutoConfig::CSRF_OFF) {
                $token = base64_encode(random_bytes(6));
                if ($protection === AutoConfig::CSRF_STRICT) {
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
                $this->lang = $this->config->defaultLanguage();
            }
            return $this->lang;
        }
    }

}
