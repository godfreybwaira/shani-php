<?php

/**
 * Application class is the core application engine that run the entire user
 * application. This is where application execution and routing takes place
 * @author coder
 *
 * Created on: Feb 11, 2024 at 6:50:02 PM
 */

namespace shani\launcher {

    use features\ds\map\ReadableMap;
    use features\exceptions\CustomException;
    use features\logging\Logger;
    use features\persistence\LocalStorage;
    use features\session\SessionStorage;
    use features\session\SessionStorageInterface;
    use shani\advisors\Configuration;
    use shani\advisors\SecurityMiddleware;
    use shani\contracts\ResponseWriter;
    use shani\contracts\StorageMediaInterface;
    use shani\http\enums\HttpStatus;
    use shani\http\HttpWriter;
    use shani\http\Middleware;
    use shani\http\RequestEntity;
    use shani\http\ResponseEntity;
    use shani\launcher\Framework;

    final class App
    {

        private StorageMediaInterface $storage;
        private ?Logger $logger = null;
        public readonly SessionStorageInterface $session;
        private ?string $lang = null;

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
         * @var Framework
         */
        public readonly Framework $framework;

        /**
         * Create an application instance
         * @param ReadableMap $vhost Virtual host
         * @param ResponseEntity $res Response entity object
         * @param ResponseWriter $writer response writer object
         * @param Framework $framework Framework configuration object
         */
        public function __construct(ReadableMap $vhost, ResponseEntity $res, ResponseWriter $writer, Framework $framework)
        {
            $this->vhost = $vhost;
            $this->response = $res;
            $this->framework = $framework;
            $this->request = $res->request;
            $this->writer = new HttpWriter($this, $writer);
            $class = $vhost->getOne('config');
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
                $this->request->changeRoute($this->config->homePath());
            }
            $middleware = new Middleware($this);
            $this->config->registerMiddleware($middleware);
            $middleware->runWith(new SecurityMiddleware($this));
        }

        public function csrfToken(): ReadableMap
        {
            return $this->session->cart('_gGOd2y$oN');
        }

        private function getSession(): SessionStorageInterface
        {
            $conn = $this->config->getSessionConnection();
            return SessionStorage::getStorage($this, $conn);
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
         * @return StorageMediaInterface
         */
        public function storage(): StorageMediaInterface
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
            $response = $obj->$callback();
            if (!$this->writer->isClosed()) {
                if ($response instanceof \Closure) {
                    $this->writer->stream($response);
                } else {
                    $this->writer->send($response);
                }
            }
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
