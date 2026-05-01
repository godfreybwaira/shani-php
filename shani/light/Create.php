<?php

/**
 * Description of Create
 * @author goddy
 *
 * Created on: May 1, 2026 at 9:34:23 AM
 */

namespace shani\light {

    use shani\launcher\Framework;

    final class Create
    {

        private const ASSETS = Framework::DIR_SHANI . '/light/assets';

        public static function project(string $params): void
        {
            $project = new subcommands\Project($params, self::ASSETS);
            $project->create();
        }

        public static function module(string $params): void
        {

        }

        public static function controller(string $params): void
        {

        }
    }

}
