<?php

/**
 * Description of newPHPClass
 * @author goddy
 *
 * Created on: Apr 8, 2026 at 1:27:25 PM
 */

namespace features\pwa {

    use features\pwa\enums\PwaCategory;
    use features\pwa\enums\PwaDisplayMode;
    use features\pwa\enums\PwaDisplayOverride;
    use features\pwa\enums\PwaFormFactor;
    use features\pwa\enums\PwaOrientation;
    use features\pwa\enums\PwaTextDirection;
    use lib\ds\map\ReadableMap;
    use features\utils\MediaType;
    use features\utils\URI;

    /**
     * PwaManifestBuilder handles the generation of a W3C-compliant manifest.json file.
     * This class ensures that the Progressive Web App (PWA) has the necessary identity,
     * display properties, and OS integration features to be installable and high-quality.
     * @see https://developer.mozilla.org/en-US/docs/Web/Manifest
     */
    final class PwaManifestBuilder
    {

        private array $data = [];

        /**
         * Initializes the builder with the absolute minimum identity requirements.
         * @param string $appName Full name of the app (used in install prompts).
         * @param string $appShortName Name used where space is limited (e.g., Home Screen).
         * @param URI $appId The globally unique identifier for this PWA instance.
         */
        public function __construct(string $appName, string $appShortName, URI $appId)
        {
            $this->data['name'] = $appName;
            $this->data['short_name'] = $appShortName;
            $this->data['id'] = $appId->asString();
            $this->data['start_url'] = '/';
            $this->data['scope'] = '/';
            $this->data['display'] = PwaDisplayMode::STANDALONE->value;
            $this->data['dir'] = PwaTextDirection::AUTO->value;
            $this->data['lang'] = 'en-US';
            $this->data['icons'] = [];
            $this->data['shortcuts'] = [];
            $this->data['screenshots'] = [];
            $this->data['categories'] = [];
            $this->data['protocol_handlers'] = [];
            $this->data['related_applications'] = [];
        }

        /**
         * Sets the windowing behavior.
         * @param PwaDisplayMode $mode The primary display mode.
         * @param PwaDisplayOverride[] $overrides Advanced fallback chain for windowing (e.g., Tabbed, Overlay).
         */
        public function setDisplay(PwaDisplayMode $mode, array $overrides = []): self
        {
            $this->data['display'] = $mode->value;
            if (!empty($overrides)) {
                $this->data['display_override'] = array_map(fn(PwaDisplayOverride $do) => $do->value, $overrides);
            }
            return $this;
        }

        /** @param string $language ISO 639-1 language code (e.g., "en-GB") */
        public function setLanguage(string $language): self
        {
            $this->data['lang'] = $language;
            return $this;
        }

        /** @param PwaTextDirection $dir The reading direction (Left-to-Right or Right-to-Left) */
        public function setTextDirection(PwaTextDirection $dir): self
        {
            $this->data['dir'] = $dir->value;
            return $this;
        }

        /** @param PwaOrientation $orientation Locks the app to a specific rotation (not recommended for all apps) */
        public function setOrientation(PwaOrientation $orientation): self
        {
            $this->data['orientation'] = $orientation->value;
            return $this;
        }

        /** @param string $color The color of the OS title bar and task switcher */
        public function setThemeColor(string $color): self
        {
            $this->data['theme_color'] = $color;
            return $this;
        }

        /** @param string $color The background color shown on the splash screen before CSS loads */
        public function setBackgroundColor(string $color): self
        {
            $this->data['background_color'] = $color;
            return $this;
        }

        /** @param string $desc A brief summary of what the app does */
        public function setDescription(string $desc): self
        {
            $this->data['description'] = $desc;
            return $this;
        }

        /** @param string $scope The URL prefix that the browser considers "inside" the app */
        public function setScope(string $scope): self
        {
            $this->data['start_url'] = $scope;
            $this->data['scope'] = $scope;
            return $this;
        }

        /**
         * Defines the genre/category of the app for store listing purposes.
         * @param PwaCategory|string $categories Variadic list of category tokens.
         */
        public function setCategories(PwaCategory|string ...$categories): self
        {
            $this->data['categories'] = array_map(fn($c) => $c instanceof PwaCategory ? $c->value : $c, $categories);
            return $this;
        }

        /** @param string $id The certificate ID obtained from the IARC rating system */
        public function setIarcRatingId(string $id): self
        {
            $this->data['iarc_rating_id'] = $id;
            return $this;
        }

        /**
         * Appends icons to the manifest. At least one 192x192 and one 512x512 are required.
         * @param PwaIcon $icons One or more PwaIcon objects.
         */
        public function addIcon(PwaIcon ...$icons): self
        {
            foreach ($icons as $icon) {
                $this->data['icons'][] = $icon->toArray();
            }
            return $this;
        }

        /**
         * Adds an app screenshot used in the "Rich Install" UI.
         * @param string $src URL to the image.
         * @param PwaDimension $sizes Dimensions of the image.
         * @param PwaFormFactor $formFactor Specifies if this is for 'wide' (desktop) or 'narrow' (mobile).
         * @param string|null $label Accessibility label describing the screenshot.
         */
        public function addScreenshot(string $src, PwaDimension $sizes, PwaFormFactor $formFactor, ?string $label = null): self
        {
            $screenshot = [
                'src' => $src,
                'sizes' => $sizes->asString(),
                'type' => MediaType::fromFilename($src),
                'form_factor' => $formFactor->value
            ];
            if ($label) {
                $screenshot['label'] = $label;
            }
            $this->data['screenshots'][] = $screenshot;
            return $this;
        }

        /**
         * Defines "Quick Actions" available via the app icon context menu.
         * @param string $appName Name of the action.
         * @param URI $url URL to navigate to.
         * @param string|null $appShortName Shorter version of the action name.
         * @param string|null $description Purpose of the shortcut.
         * @param PwaIcon $icons Specific icons for this shortcut (e.g., a "Plus" for "Compose").
         */
        public function addShortcut(string $appName, URI $url, ?string $appShortName = null, ?string $description = null, PwaIcon ...$icons): self
        {
            $shortcut = ['name' => $appName, 'url' => $url];
            if ($appShortName) {
                $shortcut['short_name'] = $appShortName;
            }
            if ($description) {
                $shortcut['description'] = $description;
            }
            if (!empty($icons)) {
                $shortcut['icons'] = array_map(fn(PwaIcon $i) => $i->toArray(), $icons);
            }
            $this->data['shortcuts'][] = $shortcut;
            return $this;
        }

        /**
         * Registers the app to handle custom URL schemes (e.g., web+messenger://...).
         * @param string $protocol The scheme (must start with web+).
         * @param string $url The endpoint that handles the request, including %s for the URI payload.
         */
        public function addProtocolHandler(string $protocol, string $url): self
        {
            if (!str_contains($url, '%s')) {
                throw new \InvalidArgumentException("Protocol handler URL must contain '%s' placeholder.");
            }
            $this->data['protocol_handlers'][] = [
                'protocol' => $protocol,
                'url' => $url
            ];
            return $this;
        }

        /**
         * Tells the browser to suggest a native app instead of the PWA if available.
         * @param bool $prefer Whether to prioritize the native app.
         * @param PwaRelatedApplication ...$apps List of platform-specific app store entries.
         */
        public function setRelatedApplications(bool $prefer, PwaRelatedApplication ...$apps): self
        {
            $this->data['prefer_related_applications'] = $prefer;
            $this->data['related_applications'] = array_map(fn(PwaRelatedApplication $a) => $a->toArray(), $apps);
            return $this;
        }

        /**
         * Supports non-standard or future manifest properties.
         * @param string $key The property name.
         * @param mixed $value The property value.
         */
        public function setExtra(string $key, mixed $value): self
        {
            $this->data[$key] = $value;
            return $this;
        }

        /**
         * Filters the data and returns a ReadableMap for final JSON encoding.
         * * Removes null/empty values to keep the manifest file concise while
         * preserving valid falsy values (like false or 0).
         */
        public function build(): ReadableMap
        {
            $data = array_filter($this->data, fn($value) => $value !== null && $value !== []);
            return new ReadableMap($data);
        }
    }

}
