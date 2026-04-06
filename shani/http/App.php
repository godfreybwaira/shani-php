<?php

/**
 * Application class is the core application engine that run the entire user
 * application. This is where application execution and routing takes place
 * @author coder
 *
 * Created on: Feb 11, 2024 at 6:50:02 PM
 */

namespace shani\http {

    use lib\ds\map\ReadableMap;
    use lib\http\HttpHeader;
    use lib\http\HttpStatus;
    use lib\http\RequestEntity;
    use lib\http\ResponseEntity;
    use shani\advisors\Configuration;
    use shani\advisors\SecurityMiddleware;
    use shani\contracts\ResponseWriter;
    use shani\contracts\StorageMedia;
    use shani\core\Framework;
    use shani\core\log\Logger;
    use shani\exceptions\CustomException;
    use shani\FrameworkConfig;
    use shani\persistence\LocalStorage;
    use shani\persistence\session\PersistentSessionStorage;
    use shani\persistence\session\SessionStorageInterface;

    final class App
    {

        private StorageMedia $storage;
        private ?Logger $logger = null;
        public readonly SessionStorageInterface $session;
        private ?string $lang = null, $platform = null;

        /**
         * HTTP response writer
         * @var HttpWriter
         */
        public readonly HttpWriter $writer;

        /**
         * Application virtual host configuration
         * @var ReadableMap
         */
        public readonly ReadableMap $vhost;

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

        /**
         * Application framework configuration
         * @var FrameworkConfig
         */
        public readonly FrameworkConfig $framework;

        /**
         * Create an application instance
         * @param ReadableMap $vhost Virtual host
         * @param ResponseEntity $res Response entity object
         * @param ResponseWriter $writer response writer object
         * @param FrameworkConfig $framework Framework configuration object
         */
        public function __construct(ReadableMap $vhost, ResponseEntity $res, ResponseWriter $writer, FrameworkConfig $framework)
        {
            $this->vhost = $vhost;
            $this->response = $res;
            $this->framework = $framework;
            $this->request = $res->request;
            $this->writer = new HttpWriter($this, $writer);
            $class = $vhost->getOne('classpath');
            $this->config = new $class($this);
            $this->session = $this->getSession();
        }

        /**
         * Launching user application
         * @return void
         */
        public function launch(): void
        {
            if ($this->framework->config->isTruthy('display_errors')) {
                $this->runApplication();
            } else {
                try {
                    $this->runApplication();
                } catch (\Throwable $ex) {
                    $this->handleException($ex);
                }
            }
        }

        private function runApplication(): void
        {
            $this->config->requestMutator();
            if (!$this->config->isRunning()) {
                throw CustomException::offline($this);
            }
            if (LocalStorage::tryServe($this)) {
                return;
            }
            if ($this->request->uri->path() === '/') {
                $this->request->changeRoute($this->config->home());
            }
            $middleware = new Middleware($this);
            $this->config->registerMiddleware($middleware);
            $middleware->runWith(new SecurityMiddleware($this));
        }

        /**
         * Execute a callback function only when a application is in a given context
         * execution environment. Currently, 'web' and 'api' context are supported by
         * the framework. Developer is advised to use 'web' for web application and 'api'
         * for API application, but can define and handle different application context
         * depending on type of application needs. Client application can supply
         * this context via accept-version header.
         * @param string $version Application execution context.
         * @param callable $cb A callback to execute that accept application object as an argument.
         * @return self
         */
        public function on(string $version, callable $cb): self
        {
            if ($this->platform() === $version) {
                $cb($this);
            }
            return $this;
        }

        /**
         * Get HTTP application platform set by client application.
         * This value is set via HTTP accept-version header. Currently, accepted
         * values are 'web' for web application and 'api' for api application.
         * If none given, 'web' is assumed.
         * @example accept-version:web
         * @return string|null
         */
        public function platform(): ?string
        {
            if ($this->platform === null) {
                $str = $this->request->header()->getOne(HttpHeader::ACCEPT_VERSION) ?? 'web';
                $this->platform = strtolower($str);
            }
            return $this->platform;
        }

        public function csrfToken(): ReadableMap
        {
            return $this->session->cart('_gGOd2y$oN');
        }

        private function getSession(): SessionStorageInterface
        {
            $conn = $this->config->getSessionConnection();
            return PersistentSessionStorage::getStorage($this, $conn);
        }

        /**
         * Create and return logger object.
         * @return Logger
         */
        public function logger(): Logger
        {
            if (!isset($this->logger)) {
                $filename = $this->config->logFileName();
                if (strpos($filename, '://') === false) {
                    $filename = $this->storage()->pathTo('/' . $filename);
                }
                $this->logger = new Logger($filename);
            }
            return $this->logger;
        }

        /**
         * Get storage object representing application storage directory
         * @return StorageMedia
         */
        public function storage(): StorageMedia
        {
            return $this->storage ??= $this->config->getStorageMedia();
        }

        /**
         * Get dictionary of words/sentences from current application dictionary file.
         * Current dictionary directory name must match with current executing function name
         * and the dictionary file name must be language code supported by your application.
         * @param array $data Optional data to that will be available in the dictionary
         * file using $data object
         * @param string $name Dictionary directory name
         * @return ReadableMap An object of words in the dictionary file.
         */
        public function dictionary(array $data = null, string $name = null): ReadableMap
        {
            $route = $this->request->route();
            $file = $this->module() . $this->config->languageDir() . '/' . $route->controller;
            $file .= ($name ?? '/' . $route->action) . '/' . $this->language() . '.php';
            return self::getFile($file, new ReadableMap($data));
        }

        private static function getFile(string $loadedFile, ReadableMap $data): ReadableMap
        {
            return new ReadableMap(require $loadedFile);
        }

        /**
         * Get path to a module directory
         * @param string $name Module name or default current module if null is provided.
         * @return string Path to a module directory
         */
        public function module(string $name = null): string
        {
            return $this->config->root() . $this->config->moduleDir() . ($name ?? '/' . $this->request->route()->module);
        }

        private function getClassPath(): string
        {
            $class = substr($this->config->root(), strrpos(Framework::DIR_APPS, '/'));
            $class .= $this->config->moduleDir() . '/' . $this->request->route()->module;
            $class .= $this->config->controllers() . '/' . ($this->request->method !== 'head' ? $this->request->method : 'get');
            return $class . '/' . str_replace('-', '', ucwords($this->request->route()->controller, '-'));
        }

        /**
         * Dynamically route a user request to mapped resource. The routing mechanism
         * always depends on HTTP method and application endpoint provided by the user.
         * @return void
         */
        public function processRequest(): void
        {
            $classPath = $this->getClassPath();
            if (!is_file(SHANI_SERVER_ROOT . $classPath . '.php')) {
                throw CustomException::notFound($this);
            }
            $className = str_replace('/', '\\', $classPath);
            $callback = self::kebab2camelCase($this->request->route()->action);
            $obj = new $className($this);
            if (!is_callable([$obj, $callback])) {
                throw CustomException::notFound($this);
            }
            $obj->$callback();
        }

        private static function kebab2camelCase(string $str, string $separator = '-'): string
        {
            if (str_contains($str, $separator)) {
//                $str = preg_replace_callback('/(?<=-)[a-z]/', fn($ch) => mb_strtoupper($ch[0]), $str);
                $str = lcfirst(ucwords($str, $separator));
                return str_replace($separator, '', $str);
            }
            return $str;
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
                    if (in_array($lang, $supportedLangs)) {
                        $this->lang = $lang;
                        return $lang;
                    }
                }
                $this->lang = $this->config->defaultLanguage();
            }
            return $this->lang;
        }

        private function handleException(\Throwable $ex): void
        {
            if (isset($this->config)) {
                $fallback = $this->config->errorHandler($ex);
                if (!empty($fallback)) {
                    $this->request->changeRoute($fallback);
                    $this->processRequest();
                    return;
                }
            } else {
                $this->response->setStatus(HttpStatus::INTERNAL_SERVER_ERROR);
                //log error
            }
            $this->writer->send(null);
        }
    }

}
