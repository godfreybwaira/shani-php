<?php

/**
 * Description of Animation
 * @author coder
 *
 * Created on: Apr 21, 2025 at 2:46:42 PM
 */

namespace gui\v2\decoration {

    final class Animation implements Decorator
    {

        private AnimationType $type;
        private readonly float $duration;
        private readonly string $timing;
        private readonly string $name;

        /**
         * Create animation
         * @param float $duration Animation duration
         * @param string $timing Animation timing function
         * @param AnimationType $type Animation type
         */
        public function __construct(AnimationType $type)
        {
            $this->type = $type;
            $this->duration = .2;
            $this->timing = 'ease-in';
            $this->name = Theme::createId();
        }

        /**
         * Get animation keyframes
         */
        public function getKeyframe(): string
        {
            $keyframe = '@keyframes ' . $this->name;
            if ($this->type === AnimationType::SHRINK || $this->type === AnimationType::ZOOM_OUT) {
                return $keyframe . '{100%{opacity:0;transform:' . $this->type->value . ';}}';
            }
            $keyframe .= '{0%{opacity:0;transform:' . $this->type->value . ';}';
            return $keyframe . '100%{opacity:1;transform:none;}}';
        }

        public function getProperty(): ?string
        {
            $animation = 'animation:' . $this->name . ' ' . $this->duration . 's ' . $this->timing . ';';
            if ($this->type === AnimationType::RISE_UP) {
                $animation .= 'transform-origin:bottom left;';
            }
            return $animation;
        }
    }

}