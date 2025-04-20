<?php

/**
 * Description of Theme
 * @author coder
 *
 * Created on: Mar 25, 2025 at 12:07:49 PM
 */

namespace gui\v2\decoration {

    abstract class Theme
    {

        private readonly string $themeName, $themeId;

        /**
         * Create a theme with unique name
         * @param string $themeName Theme name
         */
        protected function __construct(string $themeName)
        {
            $this->themeName = $themeName;
            $this->themeId = 'id' . substr(hrtime(true), 8);
        }

        /**
         * Get default component theme
         */
        public static abstract function getDefaultTheme(): self;

        /**
         * Get theme name
         * @return string
         */
        public final function getName(): string
        {
            return $this->themeName;
        }

        /**
         * Get unique theme identifier. This is usually a CSS class
         * @return string
         */
        public final function getId(): string
        {
            return $this->themeId;
        }

        /**
         * Get Component decoration (styles)
         */
        public abstract function getDecoration(): ?string;
    }

}
