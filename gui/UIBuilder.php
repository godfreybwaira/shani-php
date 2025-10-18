<?php

/**
 * Description of UIBuilder
 * @author goddy
 *
 * Created on: Oct 18, 2025 at 2:11:32 PM
 */

namespace gui {

    final class UIBuilder
    {

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
        public function data(): ?\JsonSerializable
        {
            return $this->data;
        }

        public function view(string $path): self
        {
            $this->viewPath = $path;
            return $this;
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
         * @param string $src Path to JavaScript file(s) relative to asset directory.
         * @param array $attributes Script attributes, must conform to HTML
         * attributes naming standard
         * @return self
         */
        public function script(string $src, array $attributes = []): self
        {
            $this->scripts[$src] = $attributes;
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
            $this->styles[$href] = $attributes;
            return $this;
        }
    }

}
