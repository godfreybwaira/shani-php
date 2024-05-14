<?php

/**
 * Description of Path
 * @author coder
 *
 * Created on: Feb 13, 2024 at 12:12:54 PM
 */

namespace shani\engine\core {

    interface Path
    {

        public const APP = SERVER_ROOT . Directory::APP;
        public const ASSET = SERVER_ROOT . Directory::ASSET;
        public const ASSET_PUBLIC = self::ASSET . Directory::ASSET_PUBLIC;
        public const ASSET_PRIVATE = self::ASSET . Directory::ASSET_PRIVATE;
        public const ASSET_STORAGE = self::ASSET_PUBLIC . Directory::ASSET_STORAGE;
        public const GUI = SERVER_ROOT . Directory::GUI;
        public const ASSET_FONTS = self::ASSET_PUBLIC . Directory::ASSET_FONTS;
        public const ASSET_CSS = self::ASSET_PUBLIC . Directory::ASSET_CSS;
        public const ASSET_JS = self::ASSET_PUBLIC . Directory::ASSET_JS;
    }

}
