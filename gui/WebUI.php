<?php

/**
 * Description of WebUI
 * @author coder
 *
 * Created on: Feb 19, 2024 at 1:07:12 PM
 */

namespace gui {

    use features\ds\map\MutableMap;
    use features\ds\map\ReadableMap;
    use shani\http\HttpCookie;
    use shani\http\enums\HttpSameSite;
    use shani\launcher\Framework;
    use shani\launcher\App;
    use features\persistence\LocalStorage;

    final class WebUI
    {

        /**
         * Iterable MutableMap representing UIBilder attributes object
         * @var MutableMap
         */
        public readonly MutableMap $attr;
        private readonly WebUIBuilder $builder;
        private readonly App $app;

        private function __construct(App $app, WebUIBuilder $builder)
        {
            $this->app = $app;
            $this->builder = $builder;
            $this->attr = $builder->attr;
        }

        /**
         * Get asset URI
         * @param string $path asset location relative to asset directory
         * @return string URI pointing to asset
         */
        public function assetUri(string $path): string
        {
            return $this->app->storage()->uri(LocalStorage::ACCESS_ASSET . $path)->asString();
        }

        /**
         * Get asset real path
         * @param string $path asset location relative to asset directory
         * @return string real path pointing to asset
         */
        public static function assetPath(string $path): string
        {
            return Framework::DIR_ASSETS . $path;
        }

        /**
         * Set and/or get current view file to be rendered as HTML to client.
         * @param string $path Case sensitive Path to view file, if not provided then
         * the view file will be the same as current executing function name. All views
         * have access to application object as $app
         * @param string $moduleName Module name
         * @return string Path to a view file
         * @see App::render()
         */
        public function view(string $path = null, string $moduleName = null): string
        {
            $route = $this->app->request->route();
            $file = ($path ?? '/' . $route->action) . '.php';
            if ($moduleName === null) {
                return $this->app->module() . $this->app->config->viewDir() . '/' . $route->controller . $file;
            }
            return $this->app->module($moduleName) . $this->app->config->viewDir() . $file;
        }

        /**
         * Get dictionary object of the current web view
         * @return ReadableMap Dictionary object
         */
        public function dictionary(): ReadableMap
        {
            return $this->app->dictionary($this->builder->getDictionaryData(), $this->builder->getView());
        }

        /**
         * Render HTML document to user agent
         * @param App $app Application object
         * @param WebUIBuilder $builder UI builder object
         * @return string The HTML string
         */
        public static function render(App $app, WebUIBuilder $builder): string
        {
            $web = new WebUI($app, $builder);
            ob_start();
            if ($app->config->isAsync()) {
                self::load($web, $web->view($builder->getView()));
            } else {
                self::load($web, Framework::DIR_GUI . '/html/main.php');
            }
            return ob_get_clean();
        }

        /**
         * Get the application language
         * @return string The application language
         */
        public function language(): string
        {
            return $this->app->language();
        }

        /**
         * Set HTML scripts, links and title elements to HTML head element.
         * @return string HTML scripts, links and title elements that were set on
         * HTML header tag.
         */
        public function head(): string
        {
            $head = $this->builder->getIcon();
            $meta = $this->builder->getMeta();
            foreach ($meta as $name => $value) {
                $head .= '<meta name="' . $name . '" content="' . $value . '"/>';
            }
            $pwaBuilder = $this->builder->getPwaBuilder();
            if ($pwaBuilder !== null) {
                $head .= '<link rel="manifest" crossorigin="use-credentials" href="';
                $head .= $pwaBuilder->manifest->asString() . '"/>';
                $head .= '<script>if("serviceWorker" in navigator)navigator.serviceWorker.register("';
                $head .= $pwaBuilder->serviceWorker->asString() . '",{scope:"' . $pwaBuilder->scope . '"});</script>';
            }
            $styles = $this->builder->getStyles();
            foreach ($styles as $url => $attr) {
                $head .= '<link ' . $attr . ' rel="stylesheet" href="' . $this->assetUri($url) . '"/>';
            }
            $scripts = $this->builder->getScripts();
            foreach ($scripts as $url => $attr) {
                $head .= '<script ' . $attr . ' src="' . $this->assetUri($url) . '"></script>';
            }
            return $head . '<title>' . ($this->builder->getTitle() ?? $this->app->config->appName()) . '</title>';
        }

        /**
         * Load the default HTML layout
         * @param string $navbar Full path to HTML navbar file
         * @param string $body Full path to HTML body file
         * @param string|null $menu Full path to HTML menu file
         * @param string|null $id HTML id attribute for reference in JavaScript
         * @return void
         */
        public function layout(string $navbar, string $body, ?string $menu = null, ?string $id = null): void
        {
            self::loadLayout($this, $navbar, $body, $id, $menu);
        }

        private static function loadLayout(WebUI $web, string $navbar_, string $body_, ?string $id_, ?string $menu_): void
        {
            require Framework::DIR_GUI . '/html/layout.php';
        }

        /**
         * Import a template file. The template imported also has access to $web object
         * @param string $template Path to template file
         * @param bool $success If true then import will be done, false otherwise.
         * @return void
         */
        public function import(string $template, bool $success = true): self
        {
            if ($success) {
                self::load($this, $template);
            }
            return $this;
        }

        private static function load(WebUI $web, string $loadedFile): void
        {
            require $loadedFile;
        }

        /**
         * Get data passed via UIBuilder constructor
         * @return \JsonSerializable|null
         */
        public function data(): ?\JsonSerializable
        {
            return $this->builder->getData();
        }

        /**
         * Set and get URL safe from CSRF attack. if CSRF is enabled, then the
         * application will be protected against CSRF attack and the URL will be
         * returned, otherwise the URL will be returned but CSRF will be turned off.
         * @param string|null $url URL to protect from CSRF. If not supplied
         * then the request URI path will be used.
         * @return string URL safe from CSRF attack
         */
        public function csrf(?string $url = null): string
        {
            if (!$this->app->config->skipCsrfProtection()) {
                $tokenName = $this->app->config->csrfTokenName();
                $token = $this->app->csrfToken()->getOne($tokenName, base64_encode(random_bytes(21)));
                $this->app->csrfToken()->addOne($tokenName, $token);
                $cookie = (new HttpCookie())->setName($tokenName)
                        ->setSameSite(HttpSameSite::LAX)
                        ->setValue($token)->setHttpOnly(true)
                        ->setSecure($this->app->request->uri->secure());
                $this->app->response->header()->setCookie($cookie);
            }
            return $url ?? $this->app->request->uri;
        }
    }

}