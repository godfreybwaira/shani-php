<?php

/**
 * Description of Template
 * @author coder
 *
 * Created on: Feb 19, 2024 at 1:07:12 PM
 */

namespace gui {

    use shani\engine\http\App;

    final class Template
    {

        private App $app;
        private ?string $title, $icon;
        private array $details = [];
        private ?array $scripts, $styles, $data, $attributes;

        public function __construct(App &$app)
        {
            $this->scripts = $this->styles = $this->data = $this->attributes = [];
            $this->title = $this->icon = null;
            $this->app = $app;
        }

        /**
         * Set HTML document icon (favicon)
         * @param string $path Path to icon file
         * @param string $mime MIME type of a file
         * @return self
         */
        public function icon(string $path, string $mime): self
        {
            $this->icon = '<link rel="icon" href="' . $path . '" type="' . $mime . '"/>';
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
         * Set title to HTML document. If not set, thn the default title will be
         * application name, or empty string.
         * @param string $name HTML title
         * @return self
         */
        public function title(string $name): self
        {
            $this->title = $name;
            return $this;
        }

        /**
         * Set link to external script file for HTML document relative to asset directory.
         * @param string|array $srcs Path to Javascript file(s) relative to asset directory.
         * @param array $attributes Script attributes, must conform to HTML
         * attributes naming standard
         * @return self
         */
        public function scripts(string|array $srcs, array $attributes = []): self
        {
            self::createHeader($this->scripts, $srcs, $attributes);
            return $this;
        }

        /**
         * Set link to external CSS file for HTML document relative to asset directory.
         * @param string|array $hrefs Path to CSS file(s) relative to asset directory.
         * @param array $attributes Style attributes, must conform to HTML
         * attributes naming standard
         * @return self
         */
        public function styles(string|array $hrefs, array $attributes = []): self
        {
            self::createHeader($this->styles, $hrefs, $attributes);
            return $this;
        }

        private static function createHeader(array &$head, $urls, array &$attributes): void
        {
            if (!is_array($urls)) {
                $urls = [$urls];
            }
            foreach ($urls as $url) {
                $head[$url] = null;
                foreach ($attributes as $key => $val) {
                    $head[$url] .= is_int($key) ? $val . ' ' : $key . '="' . $val . '" ';
                }
            }
        }

        /**
         * Render HTML document to user agent
         * @param array|null $data Values to be passed on view component
         * @return void
         */
        public function render(?array $data): void
        {
            $this->data = $data;
            if ($this->app->request()->isAsync()) {
                self::load($this->app, $this->app->view());
            } else {
                self::load($this->app, \shani\engine\core\Definitions::DIR_GUI . '/html/main.php');
            }
        }

        /**
         * Get immutable data that will be available to all views.
         * @param type $key Key
         */
        public function data($key = null)
        {
            return $key === null ? $this->data : $this->data[$key] ?? null;
        }

        /**
         * Set or get mutable data. Use this function to pass data from one view to another
         * @param string $name Attribute name
         * @param type $value Attribute value
         * @return type On get, returns attribute/data specified by $name
         */
        public function attrib(string $name, $value = null)
        {
            if ($value === null) {
                return $this->attributes[$name];
            }
            $this->attributes[$name] = $value;
        }

        /**
         * Set HTML scripts, links and title elements to HTML head element.
         * @return string HTML scripts, links and title elements that were set on
         * HTML header tag.
         */
        public function head(): string
        {
            $head = $this->icon;
            $asset = $this->app->asset();
            foreach ($this->styles as $url => $attr) {
                $head .= '<link ' . $attr . ' rel="stylesheet" href="' . $asset->urlTo($url) . '"/>';
            }
            foreach ($this->scripts as $url => $attr) {
                $head .= '<script ' . $attr . ' src="' . $asset->urlTo($url) . '"></script>';
            }
            foreach ($this->details as $name => $value) {
                $head .= '<meta name="' . $name . '" content="' . $value . '"/>';
            }
            return $head . '<title>' . ($this->title ?? $this->app->config()->appName()) . '</title>';
        }

        /**
         * Create and return breadcrumb.
         * @return string Created breadcrumb
         */
        public function breadcrumb(): string
        {
            $rs = $this->app->request()->resource();
            $bc = $this->app->module() . $this->app->config()->breadcrumbDir();
            $str = self::loadFile($bc . $this->app->request()->module());
            $str .= self::loadFile($bc . $rs . $rs);
            $str .= self::loadFile($bc . $rs . $this->app->config()->breadcrumbMethodsDir() . $this->app->request()->callback());
            return $str;
        }

        /**
         * Import a template file. The template imported also has access to $app object
         * @param string $template Path to template file
         * @return void
         */
        public function import(string $template): void
        {
            self::load($this->app, $template);
        }

        private static function load(App &$app, string $loadedFile): void
        {
            require $loadedFile;
        }

        private static function loadFile($file): ?string
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