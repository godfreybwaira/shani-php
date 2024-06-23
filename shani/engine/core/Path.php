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

        public const DIR_APPS = '/apps';
        public const DIR_GUI = '/gui';
        public const APPS = SERVER_ROOT . self::DIR_APPS;
        public const GUI = SERVER_ROOT . self::DIR_GUI;
    }

}
