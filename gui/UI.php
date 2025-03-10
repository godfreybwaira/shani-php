<?php

/**
 * Description of UI
 * @author coder
 *
 * Created on: Feb 19, 2024 at 1:07:12 PM
 */

namespace gui {

    use shani\advisors\Configuration;
    use shani\contracts\DataDto;
    use shani\core\Definitions;
    use shani\http\App;

    final class UI
    {

        private readonly App $app;
        private array $details = [];
        private ?string $title, $icon;
        private ?array $scripts, $styles, $attributes;
        private ?DataDto $dto = null;

        private const REFERRER_PRIVACIES = [
            Configuration::BROWSING_PRIVACY_STRICT => 'no-referrer',
            Configuration::BROWSING_PRIVACY_THIS_DOMAIN => 'same-origin',
            Configuration::BROWSING_PRIVACY_PARTIALLY => 'strict-origin',
            Configuration::BROWSING_PRIVACY_NONE => 'strict-origin-when-cross-origin'
        ];

        public function __construct(App &$app)
        {
            $this->meta('referrer-policy', self::REFERRER_PRIVACIES[$app->config->browsingPrivacy()]);
            $this->scripts = $this->styles = $this->attributes = [];
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
         * Set title to HTML document. If not set, then the default title will be
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
         * @param string $src Path to Javascript file(s) relative to asset directory.
         * @param array $attributes Script attributes, must conform to HTML
         * attributes naming standard
         * @return self
         */
        public function scripts(string $src, array $attributes = []): self
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
        public function styles(string $href, array $attributes = []): self
        {
            self::createHeader($this->styles, $href, $attributes);
            return $this;
        }

        private static function createHeader(array &$head, string $url, array &$attributes): void
        {
            $head[$url] = null;
            foreach ($attributes as $key => $val) {
                $head[$url] .= is_int($key) ? $val . ' ' : $key . '="' . $val . '" ';
            }
        }

        /**
         * Render HTML document to user agent
         * @param DataDto $dto Data object to be passed to a view component.
         * @return void
         */
        public function render(?DataDto $dto): void
        {
            $this->dto = $dto;
            if ($this->app->config->isAsync()) {
                self::load($this->app, $this->app->view());
            } else {
                self::load($this->app, Definitions::DIR_GUI . '/html/main.php');
            }
        }

        public function dto(): ?DataDto
        {
            return $this->dto;
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
            $str .= self::loadFile($bc . $route->resource . $route->resource);
            $str .= self::loadFile($bc . $route->resource . $this->app->config->breadcrumbMethodsDir() . $route->callback);
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