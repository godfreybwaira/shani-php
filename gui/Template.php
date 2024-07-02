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
        private ?string $title, $details, $icon;
        private ?array $scripts, $styles, $data, $attributes;

        public function __construct(App &$app)
        {
            $this->scripts = $this->styles = $this->data = $this->attributes = [];
            $this->title = $this->details = $this->icon = null;
            $this->app = $app;
        }

        public function icon(string $path, string $mime): self
        {
            $this->icon = '<link rel="icon" href="' . $path . '" type="' . $mime . '"/>';
            return $this;
        }

        public function description(string $content): self
        {
            $this->details = '<meta name="description" content="' . $content . '"/>';
            return $this;
        }

        /**
         * Set title to HTML document.
         * @param string $name HTML title
         * @return self
         */
        public function title(string $name): self
        {
            $this->title = $name;
            return $this;
        }

        /**
         * Set link to external script file for HTML document.
         * @param type $srcs can be array where values are URL to script
         * file, or string
         * @return self
         */
        public function scripts($srcs): self
        {
            self::createHeader($this->scripts, $srcs);
            return $this;
        }

        /**
         * Set link to external CSS file for HTML document.
         * @param type $hrefs can be array where values are URL to css
         * file, or string
         * @return self
         */
        public function css($hrefs): self
        {
            self::createHeader($this->styles, $hrefs);
            return $this;
        }

        private static function createHeader(array &$head, $urls): void
        {
            if (!is_array($urls)) {
                $head[$urls] = null;
                return;
            }
            foreach ($urls as $url => $attr) {
                $head[$url] = null;
                foreach ($attr as $key => $val) {
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
                self::load($this->app, \shani\engine\core\Path::GUI . '/html/main.php');
            }
        }

        /**
         * Read immutable data
         * @return array|null Data read
         */
        public function data(): ?array
        {
            return $this->data;
        }

        /**
         * Set or get mutable attribute
         * @param string $name Attribute name
         * @param type $value Attribute value
         * @return type
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
            foreach ($this->scripts as $url => $attr) {
                $head .= '<script ' . $attr . ' src="' . $this->app->asset()->url($url) . '"></script>';
            }
            foreach ($this->styles as $url => $attr) {
                $head .= '<link ' . $attr . ' rel="stylesheet" href="' . $this->app->asset()->url($url) . '"/>';
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

        public static function load(App &$app, string $loadedFile): void
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