<?php

/**
 * Description of Connections
 * @author coder
 *
 * Created on: Feb 19, 2024 at 5:42:34 PM
 */

namespace apps\test\codes\v1\config {

    interface Connections
    {

        public const DB_DEFAULT = [
            'driver' => 'mysql',
            'dbname' => 'db_name',
            'host' => '127.0.0.1',
            'port' => 3306,
            'user' => 'my_user',
            'pass' => '',
            'charset' => 'utf8'
        ];
    }

}
