<?php

/**
 * Description of Directory
 * @author coder
 *
 * Created on: Feb 12, 2024 at 7:45:54 PM
 */

namespace shani\engine\core {

    interface Directory
    {

        public const APP = '/app';
        public const ASSET = '/asset';
        public const ASSET_PUBLIC = '/public';
        public const ASSET_PRIVATE = '/private';
        public const ASSET_STORAGE = '/storage';
        public const GUI = '/gui';
        public const ASSET_FONTS = '/fonts';
        public const ASSET_CSS = '/css';
        public const ASSET_JS = '/js';
    }

}
