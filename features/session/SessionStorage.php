<?php

/**
 * Description of SessionStorage
 * @author goddy
 *
 * Created on: Apr 17, 2026 at 9:35:22 AM
 */

namespace features\session {

    use features\storage\InMemoryDataStorage;
    use features\storage\StorageInterface;
    use shani\launcher\App;

    final class SessionStorage
    {

        public static function getStorage(App $app, ?SessionConnectionInterface $conn): StorageInterface
        {
            if ($conn === null) {
                return new InMemoryDataStorage('4b6f96760ca17d1');
            }
            return new StatefulSessionStorage($app, $conn);
        }
    }

}
