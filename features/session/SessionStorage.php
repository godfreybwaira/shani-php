<?php

/**
 * Description of SessionStorage
 * @author goddy
 *
 * Created on: Apr 17, 2026 at 9:35:22 AM
 */

namespace features\session {

    use shani\launcher\App;

    final class SessionStorage
    {

        public static function getStorage(App $app, ?SessionConnectionInterface $conn): SessionStorageInterface
        {
            if ($conn === null) {
                return new StatelessSessionStorage();
            }
            return new StatefulSessionStorage($app, $conn);
        }
    }

}
