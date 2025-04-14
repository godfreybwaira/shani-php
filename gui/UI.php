<?php

/**
 * Description of UI
 * @author coder
 *
 * Created on: Feb 19, 2024 at 1:07:12 PM
 */

namespace gui {

    use lib\map\ReadableMap;
    use shani\core\Definitions;
    use shani\http\App;
    use shani\persistence\LocalStorage;

    final class UI
    {

        private readonly App $app;
        private array $details = [];
        private ?string $title, $icon;
        private array $scripts, $styles;
        private ?ReadableMap $data = null;

        public function __construct(App &$app)
        {
            $this->scripts = $this->styles = [];
            $this->title = $this->icon = null;
            $this->app = $app;
        }

        /**
         * Set HTML document icon (favicon)
         * @param string $path Path to icon file
         * @param string $mediaType media type of a file
         * @return self
         */
        public function icon(string $path, string $mediaType): self
        {
            $this->icon = '<link rel="icon" href="' . $path . '" type="' . $mediaType . '"/>';
            return $this;
        }

        /**
         * Set meta description on a HTML document.
         * @param string $content Descriptive content about your application
         * @return self
         */
        public function description(string $content): self
        {
            return $this->meta('description', $content);
        }

        /**
         * Create an HTML meta tag
         * @param string $name meta name
         * @param string $value meta value
         * @return self
         */
        public function meta(string $name, string $value): self
        {
            $this->details[$name] = $value;
            return $this;
        }

        /**
         * Set title to HTML document. If not set, then the default title will be
         * application name, or empty string.
         * @param string $content HTML title
         * @return self
         */
        public function title(string $content): self
        {
            $this->title = $content;
            return $this;
        }

        /**
         * Set link to external script file for HTML document relative to asset directory.
         * @param string $src Path to Javascript file(s) relative to asset directory.
         * @param array $attributes Script attributes, must conform to HTML
         * attributes naming standard
         * @return self
         */
        public function script(string $src, array $attributes = []): self
        {
            self::createHeader($this->scripts, $src, $attributes);
            return $this;
        }

        /**
         * Set link to external CSS file for HTML document relative to asset directory.
         * @param string $href Path to CSS file relative to asset directory.
         * @param array $attributes Style attributes, must conform to HTML
         * attributes naming standard
         * @return self
         */
        public function style(string $href, array $attributes = []): self
        {
            self::createHeader($this->styles, $href, $attributes);
            return $this;
        }

        private static function createHeader(array &$head, string $url, array &$attributes): void
        {
            $url = LocalStorage::ACCESS_ASSET . $url;
            $head[$url] = null;
            foreach ($attributes as $key => $val) {
                $head[$url] .= is_int($key) ? $val . ' ' : $key . '="' . $val . '" ';
            }
        }

        /**
         * Render HTML document to user agent
         * @param array $data Data object to be passed to a view component.
         * @return void
         */
        public function render(?array $data): void
        {
            $this->data = new ReadableMap($data ?? []);
            if ($this->app->config->isAsync()) {
                self::load($this->app, $this->app->view());
            } else {
                self::load($this->app, Definitions::DIR_GUI . '/html/main.php');
            }
        }

        /**
         * Get immutable data object.
         * @return ReadableMap ReadableMap object
         */
        public function data(): ReadableMap
        {
            return $this->data;
        }

        /**
         * Set HTML scripts, links and title elements to HTML head element.
         * @return string HTML scripts, links and title elements that were set on
         * HTML header tag.
         */
        public function head(): string
        {
            $head = $this->icon;
            $asset = $this->app->storage();
            foreach ($this->styles as $url => $attr) {
                $head .= '<link ' . $attr . ' rel="stylesheet" href="' . $asset->url($url) . '"/>';
            }
            foreach ($this->scripts as $url => $attr) {
                $head .= '<script ' . $attr . ' src="' . $asset->url($url) . '"></script>';
            }
            foreach ($this->details as $name => $value) {
                $head .= '<meta name="' . $name . '" content="' . $value . '"/>';
            }
            return $head . '<title>' . ($this->title ?? $this->app->config->appName()) . '</title>';
        }

        /**
         * Create and return breadcrumb.
         * @return string Created breadcrumb
         */
        public function breadcrumb(): string
        {
            $route = $this->app->request->route();
            $bc = $route->module . $this->app->config->breadcrumbDir();
            $str = self::loadFile($bc . $route->module);
            $str .= self::loadFile($bc . $route->controller . $route->controller);
            $str .= self::loadFile($bc . $route->controller . $this->app->config->breadcrumbMethodsDir() . $route->action);
            return $str;
        }

        /**
         * Import a template file. The template imported also has access to $app object
         * @param string $template Path to template file
         * @param bool $success If true then import will be done, false otherwise.
         * @return void
         */
        public function import(string $template, bool $success = true): self
        {
            if ($success) {
                self::load($this->app, $template);
            }
            return $this;
        }

        private static function load(App &$app, string $loadedFile): void
        {
            require $loadedFile;
        }

        private static function loadFile(string $file): ?string
        {
            if (is_file($file . '.php')) {
                ob_start();
                require $file . '.php';
                return '<li>' . ob_get_clean() . '</li>';
            }
            return null;
        }
    }

}