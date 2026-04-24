<?php

/**
 * Application class is the core application engine that run the entire user
 * application. This is where application execution and routing takes place
 * @author coder
 *
 * Created on: Feb 11, 2024 at 6:50:02 PM
 */

namespace shani\launcher {

    use features\authentication\AuthenticationManager;
    use features\ds\map\MutableMap;
    use features\ds\map\ReadableMap;
    use features\exceptions\CustomException;
    use features\logging\Logger;
    use features\middleware\MiddlewareHandler;
    use features\session\SessionStorage;
    use features\session\SessionStorageInterface;
    use features\storage\LocalStorage;
    use features\storage\StorageMediaInterface;
    use shani\contracts\BasicConfiguration;
    use shani\contracts\ResponseWriter;
    use shani\http\enums\HttpStatus;
    use shani\http\HttpWriter;
    use shani\http\RequestEntity;
    use shani\http\ResponseEntity;
    use shani\launcher\Framework;
    use shani\config\AppConfig;
    use shani\config\PathConfig;

    /**
     * Core application class that manages request handling, response writing,
     * session management, authentication, configuration, and error handling.
     *
     * This class serves as the central entry point for the framework. It ties together:
     * - Virtual host configuration
     * - HTTP request and response entities
     * - Application configurations and framework configuration
     * - Session storage and authentication manager
     * - Middleware handling
     * - Routing and controller dispatch
     * - Logging and storage management
     * - Language and dictionary support
     *
     * Responsibilities:
     * - Launch and run the application lifecycle
     * - Process incoming HTTP requests and route them to controllers
     * - Handle CSRF tokens, sessions, and authentication
     * - Provide access to storage, logging, and configuration
     * - Manage error handling and fallback routes
     */
    final class App
    {

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
         * @var BasicConfiguration
         */
        public readonly BasicConfiguration $config;

        /**
         * Application framework configuration
         * @var Framework
         */
        public readonly Framework $framework;

        /**
         * Authentication service
         * @var AuthenticationManager
         */
        public readonly AuthenticationManager $auth;

        /**
         * Application paths
         * @var PathConfig
         */
        private PathConfig $path;

        /**
         * Application related configurations
         * @var AppConfig
         */
        private AppConfig $preset;

        /**
         * Create an application instance.
         *
         * @param ReadableMap $vhost Virtual host configuration.
         * @param ResponseEntity $res Response entity object.
         * @param ResponseWriter $writer Response writer object.
         * @param Framework $framework Framework configuration object.
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
            $this->auth = new AuthenticationManager($this);
            $this->path = $this->config->pathConfig();
            $this->preset = $this->config->appConfig();
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
            if (!$this->preset->isRunning) {
                throw CustomException::offline($this);
            }
            if (LocalStorage::tryServe($this)) {
                return;
            }
            $userMiddleware = $this->config->getMiddlewareHandler();
            $middleware = new MiddlewareHandler($this);
            $middleware->preRequest();
            $userMiddleware?->preRequest();
            $this->processRequest();
        }

        public function csrfToken(): MutableMap
        {
            return $this->session->cart('f91c57f35097fa6d');
        }

        private function getSession(): SessionStorageInterface
        {
            $conn = $this->config->sessionConfig()->connection;
            return SessionStorage::getStorage($this, $conn);
        }

        /**
         * Create and return logger object.
         * @return Logger
         */
        public function logger(): Logger
        {
            if (!isset($this->logger)) {
                $filename = $this->preset->logFileName;
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
            return $this->config->getStorageMedia();
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
            $file = $this->module() . $this->path->languages . '/' . $route->controller;
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
            return $this->path->root . $this->path->modules . ($name ?? '/' . $this->request->route()->module);
        }

        private function getClassPath(): string
        {
            $class = substr($this->path->root, strrpos(Framework::DIR_APPS, '/'));
            $class .= $this->path->modules . '/' . $this->request->route()->module;
            $class .= $this->path->controllers . '/' . ($this->request->method !== 'head' ? $this->request->method : 'get');
            return $class . '/' . str_replace('-', '', ucwords($this->request->route()->controller, '-'));
        }

        /**
         * Dynamically route a user request to mapped resource. The routing mechanism
         * always depends on HTTP method and application endpoint provided by the user.
         * @return void
         */
        private function processRequest(): void
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
                foreach ($reqLangs as $lang) {
                    if (in_array($lang, $this->preset->supportedLanguages)) {
                        $this->lang = $lang;
                        return $lang;
                    }
                }
                $this->lang = $this->preset->language;
            }
            return $this->lang;
        }

        private function handleException(\Throwable $ex): void
        {
            if (isset($this->config)) {
                $fallbackRoute = $this->config->errorHandler($ex);
                if ($fallbackRoute !== null) {
                    $this->request->changeRoute($fallbackRoute);
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
