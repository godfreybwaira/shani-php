<?php

/**
 * Application class is the core application engine that run the entire user
 * application. This is where application execution and routing takes place
 * @author coder
 *
 * @since Feb 11, 2024 at 6:50:02 PM
 */

namespace shani\launcher {

    use features\assets\StaticAssetRequest;
    use features\assets\StaticAssetServers;
    use features\authentication\AuthenticationManager;
    use features\ds\map\ReadMap;
    use features\ds\map\WriteMap;
    use features\exceptions\client\AuthenticationException;
    use features\exceptions\client\AuthorizationException;
    use features\exceptions\client\BadRequestException;
    use features\exceptions\client\NotFoundException;
    use features\exceptions\client\TooManyRequestsException;
    use features\exceptions\client\ValidationException;
    use features\exceptions\ErrorResponse;
    use features\logging\Logger;
    use features\middleware\MiddlewareHandler;
    use features\middleware\MiddlewareHandlerInterface;
    use features\session\SessionStorage;
    use features\storage\StorageInterface;
    use features\storage\StorageMediaInterface;
    use shani\config\AppConfig;
    use shani\config\PathConfig;
    use shani\contracts\BasicConfiguration;
    use shani\contracts\ResponseWriterInterface;
    use shani\http\enums\HttpStatus;
    use shani\http\HttpResponseWriter;
    use shani\http\RequestEntity;
    use shani\http\ResponseEntity;
    use shani\launcher\Framework;
    use shani\utils\RequestPreference;

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
        public readonly StorageInterface $session;
        private ?string $lang = null;

        /**
         * HTTP response writer
         * @var ResponseWriterInterface
         */
        private readonly ResponseWriterInterface $writer;

        /**
         * Request Preference
         * @var RequestPreference
         */
        public readonly RequestPreference $preference;

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
         * Application file storage
         * @var StorageMediaInterface
         */
        public readonly StorageMediaInterface $storage;

        /**
         * Application paths
         * @var PathConfig
         */
        private readonly PathConfig $path;

        /**
         * Application related configurations
         * @var AppConfig
         */
        private readonly AppConfig $preset;

        /**
         * Create an application instance.
         *
         * @param RequestPreference $preference Virtual host configuration.
         * @param ResponseEntity $res Response entity object.
         * @param ResponseWriterInterface $writer Response writer object.
         * @param Framework $framework Framework configuration object.
         */
        public function __construct(RequestPreference $preference, ResponseEntity $res, ResponseWriterInterface $writer, Framework $framework)
        {
            $this->preference = $preference;
            $this->response = $res;
            $this->writer = $writer;
            $this->framework = $framework;
            $this->request = $res->request;
            $class = $preference->vhost->getOne('config');
            $this->config = new $class($this);
            $this->session = $this->getSession();
            $this->auth = new AuthenticationManager($this);
            $this->path = $this->config->pathConfig();
            $this->preset = $this->config->appConfig();
            $this->storage = $this->config->getStorageMedia();
        }

        /**
         * Launching user application
         * @return void
         */
        public function launch(): void
        {
            $userMiddleware = $this->config->getMiddlewareHandler();
            $middleware = new MiddlewareHandler($this);
            $response = $this->runWithErrorHandling($middleware, $userMiddleware);
            ///////---Step 4---////////
            $writer = new HttpResponseWriter($this, $this->writer);
            $writer->handleResponse($response);
            ///////---Step 5---////////
            if (function_exists('fastcgi_finish_request')) {
                fastcgi_finish_request();
            }
            $middleware->afterResponse();
            $userMiddleware?->afterResponse();
        }

        private function runWithErrorHandling(MiddlewareHandler $middleware, ?MiddlewareHandlerInterface $userMiddleware): mixed
        {
            if ($this->framework->config->isTruthy('display_errors')) {
                return $this->handleRequestFlow($middleware, $userMiddleware);
            }
            try {
                return $this->handleRequestFlow($middleware, $userMiddleware);
            } catch (NotFoundException $ex) {
                return $this->handleException(HttpStatus::NOT_FOUND, $ex, $middleware);
            } catch (BadRequestException $ex) {
                return $this->handleException(HttpStatus::BAD_REQUEST, $ex, $middleware);
            } catch (ValidationException $ex) {
                return $this->handleException(HttpStatus::UNPROCESSABLE_ENTITY, $ex, $middleware);
            } catch (AuthorizationException $ex) {
                return $this->handleException(HttpStatus::FORBIDDEN, $ex, $middleware);
            } catch (AuthenticationException $ex) {
                return $this->handleException(HttpStatus::UNAUTHORIZED, $ex, $middleware);
            } catch (TooManyRequestsException $ex) {
                return $this->handleException(HttpStatus::TOO_MANY_REQUESTS, $ex, $middleware);
            } catch (\Throwable $ex) {
                $status = $this->response->status();
                return $this->handleException($status->isError() ? $status : HttpStatus::INTERNAL_SERVER_ERROR, $ex, $middleware);
            }
        }

        private function handleRequestFlow(MiddlewareHandler $middleware, ?MiddlewareHandlerInterface $userMiddleware): mixed
        {
            // Step 1: pre-request hooks
            $middleware->preRequest();
            $userMiddleware?->preRequest();

            // Step 2: main request handling
            $response = $this->handleRequest($middleware);

            // Step 3: pre-response hooks
            $middleware->preResponse();
            $userMiddleware?->preResponse();

            return $response;
        }

        public function csrfToken(): WriteMap
        {
            return $this->session->container('fiJ9Gce5osud7s');
        }

        private function getSession(): StorageInterface
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
                    $filename = $this->storage->pathTo('/' . $filename);
                }
                $this->logger = new Logger($filename);
            }
            return $this->logger;
        }

        /**
         * Get dictionary of words/sentences from current application dictionary file.
         * Current dictionary directory name must match with current executing function name
         * and the dictionary file name must be language code supported by your application.
         * @param array $data Optional data, will be available in the dictionary
         * file using $data object
         * @param string $name Dictionary directory name
         * @return ReadMap An object of words in the dictionary file.
         */
        public function dictionary(array $data = null, string $name = null): ReadMap
        {
            $route = $this->request->route();
            $file = $this->module() . $this->path->languages . '/';
            $file .= ($name ?? $route->action) . '/' . $this->language() . '.php';
            return self::getFile($file, new ReadMap($data));
        }

        private static function getFile(string $loadedFile, ReadMap $data): ReadMap
        {
            return new ReadMap(require $loadedFile);
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
            return $class . '/' . $this->request->route()->controller;
        }

        /**
         * Dynamically route a user request to mapped resource. The routing mechanism
         * always depends on HTTP method and application endpoint provided by the user.
         *
         * @param MiddlewareHandler $middleware Middleware handler object
         *
         * @return mixed
         */
        private function handleRequest(MiddlewareHandler $middleware): mixed
        {
            $classPath = $this->getClassPath();
            if (!is_file(SHANI_SERVER_ROOT . $classPath . '.php')) {
                if ($this->config->getStaticAssetServer() === StaticAssetServers::DISABLE) {
                    throw new NotFoundException('Resource Not Found!');
                }
                $staticRequest = StaticAssetRequest::fromPath($this->preference->mapper, $this->request->uri->path());
                if ($staticRequest === null) {
                    throw new NotFoundException('Resource Not Found!');
                }
                return $staticRequest->handleRequest($this);
            }
            $className = str_replace('/', '\\', $classPath);
            $methodName = $this->request->route()->action;
            $obj = new $className($this);
            if (!is_callable([$obj, $methodName])) {
                throw new NotFoundException('Resource Not Found!');
            }
            $middleware->handleAttributes($obj, $methodName);
            return $obj->$methodName();
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

        private function handleException(HttpStatus $status, \Throwable $ex, MiddlewareHandler $middleware): mixed
        {
            $this->response->setStatus($status);
            $fallbackRoute = $this->config->errorHandler($ex);
            if ($fallbackRoute !== null) {
                $this->request->changeRoute($fallbackRoute);
                return $this->handleRequest($middleware);
            }
            return ErrorResponse::create($status->value, $ex);
        }
    }

}
