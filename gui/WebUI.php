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
    use features\storage\StorageMediaInterface;
    use shani\launcher\App;
    use shani\launcher\Framework;

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
            $this->attr = $builder->attr;
            $builder->style($this->app->storage->assetUri('/css/icons/mdi.css'));
            $builder->style($this->app->storage->assetUri('/css/main.css'));
            $builder->script($this->app->storage->assetUri('/js/shani-ob-2.0.js'), ['defer']);
            $builder->script($this->app->storage->assetUri('/js/app.js'), ['defer']);
            $this->builder = $builder;
        }

        /**
         * Get asset URI as string
         * @param string $path asset location relative to asset directory
         * @return string URI pointing to asset
         */
        public function asset(string $path): string
        {
            return $this->app->storage->assetUri($path)->asString();
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
            $appPath = $this->app->config->pathConfig();
            if ($moduleName === null) {
                return $this->app->module() . $appPath->views . '/' . $route->controller . $file;
            }
            return $this->app->module($moduleName) . $appPath->views . $file;
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
            $head = null;
            $meta = $this->builder->getMeta();
            foreach ($meta as $name => $value) {
                $head .= '<meta name="' . $name . '" content="' . $value . '"/>';
            }
            $links = $this->builder->getLinks();
            foreach ($links as $rel => $attrbites) {
                $head .= '<link ' . $attrbites . ' rel="' . $rel . '"/>';
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
                $head .= '<link ' . $attr . ' rel="stylesheet" href="' . $url . '"/>';
            }
            $scripts = $this->builder->getScripts();
            foreach ($scripts as $url => $attr) {
                $head .= '<script ' . $attr . ' src="' . $url . '"></script>';
            }
            return $head . '<title>' . ($this->builder->getTitle() ?? $this->app->config->appConfig()->appName) . '</title>';
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
         * Create HTML input element and save CSRF token in user session.
         * @return string|null Hidden HTML input element with CSRF token
         * if protection enabled, otherwise null is returned.
         */
        public function csrf(): ?string
        {
            $csrf = $this->app->config->csrfConfig();
            if ($csrf->enabled) {
                $token = $this->app->csrfToken()->getOne($csrf->tokenName, bin2hex(random_bytes(32)));
                $this->app->csrfToken()->addOne($csrf->tokenName, $token);
                return '<input type="hidden" name="' . $csrf->tokenName . '" value="' . $token . '"/>';
            }
            return null;
        }

        /**
         * Returns currently requesting URI or $url, whichever is not null. Useful
         * when used in HTML form action attribute.
         * @param string|null $newUrl URL to replace currently requesting URI
         * @return string
         */
        public function url(?string $newUrl = null): string
        {
            return $newUrl ?? $this->app->request->uri->asString();
        }
    }

}