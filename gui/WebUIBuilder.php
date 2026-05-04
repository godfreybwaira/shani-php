<?php

/**
 * Description of WebUIBuilder
 * @author goddy
 *
 * Created on: Oct 18, 2025 at 2:11:32 PM
 */

namespace gui {

    use features\ds\map\WritableMap;
    use features\pwa\PwaBuilder;
    use features\utils\URI;

    final class WebUIBuilder
    {

        /**
         * Application temporary data storage. This object is ideal for data
         * exchange within application web views
         * @var WritableMap
         */
        public readonly WritableMap $attr;
        private ?array $dictionary = null;
        private ?string $viewPath, $title;
        private ?\JsonSerializable $data;
        private ?PwaBuilder $pwaBuilder = null;
        private array $scripts, $styles;
        private array $metadata = [], $links = [];

        public function __construct(\JsonSerializable $data = null)
        {
            $this->data = $data;
            $this->title = null;
            $this->viewPath = null;
            $this->attr = new WritableMap();
            $this->scripts = $this->styles = [];
            $this->link('icon', 'data:,');
        }

        /**
         * Get Progressive Web Application (PWA) object
         * @return PwaBuilder|null
         */
        public function getPwaBuilder(): ?PwaBuilder
        {
            return $this->pwaBuilder;
        }

        /**
         * Convert your application to Progressive Web Application (PWA)
         * @param PwaBuilder $builder
         * @return self
         */
        public function setPwaBuilder(PwaBuilder $builder): self
        {
            $this->pwaBuilder = $builder;
            return $this;
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
         * @param URI $path Path to icon file
         * @param string $mediaType media type of a file
         * @return self
         */
        public function icon(URI $path, string $mediaType): self
        {
            return $this->link('icon', $path->asString(), ['type' => $mediaType]);
        }

        /**
         * Set HTML link tag
         * @param string $rel Link relationship
         * @param string $href Link href
         * @param array $attributes Other attributes (key-value pair
         * @return self
         */
        public function link(string $rel, string $href, array $attributes = []): self
        {
            $attributes['href'] = $href;
            $this->links[$rel] = self::getAttributeString($attributes);
            return $this;
        }

        /**
         * Get HTML links
         * @return array
         */
        public function getLinks(): array
        {
            return $this->links;
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
            $this->metadata[$name] = $value;
            return $this;
        }

        /**
         * Get existing HTML meta values
         * @return array
         */
        public function getMeta(): array
        {
            return $this->metadata;
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
         * Set link to external script file.
         * @param URI $src Path to JavaScript.
         * @param array $attributes Script attributes, must conform to HTML
         * attributes naming standard
         * @return self
         */
        public function script(URI $src, array $attributes = []): self
        {
            $this->scripts[$src->asString()] = self::getAttributeString($attributes);
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
         * Set css style file.
         * @param URI $href Path to CSS file.
         * @param array $attributes Style attributes, must conform to HTML
         * attributes naming standard
         * @return self
         */
        public function style(URI $href, array $attributes = []): self
        {
            $this->styles[$href->asString()] = self::getAttributeString($attributes);
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

        private static function getAttributeString(array $attributes): string
        {
            $str = null;
            foreach ($attributes as $key => $val) {
                $str .= is_int($key) ? $val . ' ' : $key . '="' . $val . '" ';
            }
            return trim($str);
        }
    }

}
