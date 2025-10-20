<?php

/**
 * Description of WebUIBuilder
 * @author goddy
 *
 * Created on: Oct 18, 2025 at 2:11:32 PM
 */

namespace gui {

    use lib\ds\map\MutableMap;

    final class WebUIBuilder
    {

        private readonly MutableMap $attributes;
        private ?array $dictionary = null;
        private ?string $viewPath, $title;
        private ?\JsonSerializable $data;
        private array $scripts, $styles;
        private array $details = [];
        private string $icon;

        public function __construct(\JsonSerializable $data = null)
        {
            $this->data = $data;
            $this->title = null;
            $this->viewPath = null;
            $this->attributes = new MutableMap();
            $this->scripts = $this->styles = [];
            $this->icon = '<link rel="icon" href="data:,">';
            $this->style('/css/main.css');
            $this->style('/css/icons/mdi.css');
            $this->script('/js/shani-ob-2.0.js', ['defer']);
            $this->script('/js/app.js', ['defer']);
        }

        /**
         * Get immutable data object.
         * @return \JsonSerializable|null
         */
        public function getData(): ?\JsonSerializable
        {
            return $this->data;
        }

        /**
         * Set data to pass into dictionary
         * @param array $data Data to pass into dictionary
         * @return self
         */
        public function dictionaryData(array $data): self
        {
            $this->dictionary = $data;
            return $this;
        }

        /**
         * Get dictionary object
         * @return array|null Dictionary object
         */
        public function getDictionaryData(): ?array
        {
            return $this->dictionary;
        }

        /**
         * Override default web view
         * @param string $path Relative path to the view file (without extension)
         * @return self
         */
        public function view(string $path): self
        {
            $this->viewPath = $path;
            return $this;
        }

        /**
         * Get application web view (not default one)
         * @return string|null
         */
        public function getView(): ?string
        {
            return $this->viewPath;
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
         * Get HTML document icon (favicon)
         * @return string
         */
        public function getIcon(): string
        {
            return $this->icon;
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
         * Get existing HTML meta values
         * @return array
         */
        public function getMeta(): array
        {
            return $this->details;
        }

        /**
         * Set HTML document title.
         * @param string $content HTML document title
         * @return self
         */
        public function title(string $content): self
        {
            $this->title = $content;
            return $this;
        }

        /**
         * Get HTML document title
         * @return string|null
         */
        public function getTitle(): ?string
        {
            return $this->title;
        }

        /**
         * Set link to external script file for HTML document relative to asset directory.
         * @param string $src Path to JavaScript file(s) relative to asset directory.
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
         * Get links to external script file relative to asset directory.
         * @return array
         */
        public function getScripts(): array
        {
            return $this->scripts;
        }

        /**
         * Set link to external CSS file relative to asset directory.
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

        /**
         * Get links to external CSS file relative to asset directory.
         * @return array
         */
        public function getStyles(): array
        {
            return $this->styles;
        }

        /**
         * Create application temporary data storage. This function is ideal for
         * data exchange within application web views
         * @return MutableMap Iterable object
         */
        public function attr(): MutableMap
        {
            return $this->attributes;
        }

        private static function createHeader(array &$head, string $url, array &$attributes): void
        {
            $head[$url] = null;
            foreach ($attributes as $key => $val) {
                $head[$url] .= is_int($key) ? $val . ' ' : $key . '="' . $val . '" ';
            }
        }
    }

}
