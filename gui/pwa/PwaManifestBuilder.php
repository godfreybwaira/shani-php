<?php

/**
 * Description of newPHPClass
 * @author goddy
 *
 * Created on: Apr 8, 2026 at 1:27:25 PM
 */

namespace gui\pwa {

    use lib\ds\map\ReadableMap;
    use lib\MediaType;
    use lib\URI;

    final class PwaManifestBuilder
    {

        private array $data = [];

        public function __construct(string $appName, string $appShortName, URI $appId)
        {
            $this->data['name'] = $appName;
            $this->data['short_name'] = $appShortName;
            $this->data['id'] = $appId->hostname();
            $this->data['start_url'] = '/';
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

        public function setDisplay(PwaDisplayMode $mode, array $overrides = []): self
        {
            $this->data['display'] = $mode->value;
            if (!empty($overrides)) {
                $this->data['display_override'] = array_map(fn(PwaDisplayOverride $do) => $do->value, $overrides);
            }
            return $this;
        }

        public function setLanguage(string $language): self
        {
            $this->data['lang'] = $language;
            return $this;
        }

        public function setTextDirection(PwaTextDirection $dir): self
        {
            $this->data['dir'] = $dir->value;
            return $this;
        }

        public function setOrientation(PwaOrientation $orientation): self
        {
            $this->data['orientation'] = $orientation->value;
            return $this;
        }

        public function setThemeColor(string $color): self
        {
            $this->data['theme_color'] = $color;
            return $this;
        }

        public function setBackgroundColor(string $color): self
        {
            $this->data['background_color'] = $color;
            return $this;
        }

        public function setDescription(string $desc): self
        {
            $this->data['description'] = $desc;
            return $this;
        }

        public function setScope(string $scope): self
        {
            $this->data['scope'] = $scope;
            return $this;
        }

        public function setCategories(PwaCategory|string ...$categories): self
        {
            $this->data['categories'] = array_map(fn($c) => $c instanceof PwaCategory ? $c->value : $c, $categories);
            return $this;
        }

        public function setIarcRatingId(string $id): self
        {
            $this->data['iarc_rating_id'] = $id;
            return $this;
        }

        public function addIcon(PwaIcon ...$icons): self
        {
            foreach ($icons as $icon) {
                $this->data['icons'][] = $icon->toArray();
            }
            return $this;
        }

        public function addScreenshot(string $src, PwaDimension $sizes, ?string $label = null, PwaFormFactor $formFactor = PwaFormFactor::NARROW): self
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

        public function addShortcut(string $appName, URI $url, ?string $appShortName = null, ?string $description = null, array $icons = []): self
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

        public function addProtocolHandler(string $protocol, string $url): self
        {
            $this->data['protocol_handlers'][] = [
                'protocol' => $protocol,
                'url' => $url
            ];
            return $this;
        }

        public function setRelatedApplications(bool $prefer, PwaRelatedApplication ...$apps): self
        {
            $this->data['prefer_related_applications'] = $prefer;
            $this->data['related_applications'] = array_map(fn(PwaRelatedApplication $a) => $a->toArray(), $apps);
            return $this;
        }

        public function setExtra(string $key, mixed $value): self
        {
            $this->data[$key] = $value;
            return $this;
        }

        public function build(): ReadableMap
        {
            $data = array_filter($this->data, fn($value) => !empty($value) || $value === false || $value === 0);
            return new ReadableMap($data);
        }
    }

}
