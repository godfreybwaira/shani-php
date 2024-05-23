<?php

/**
 * Description of Profile
 * @author coder
 *
 * Created on: Feb 13, 2024 at 1:50:50 PM
 */

namespace app\test\v1\modules\users\src\get {

    final class Profile
    {

        private \shani\engine\http\App $app;

        public function __construct(\shani\engine\http\App $app)
        {
            $this->app = $app;
        }

        public function activity()
        {
            $this->app->logger()->debug('debugging...');
            $this->app->render('from Shani');
        }

        public function sample()
        {
            $data = [
                ['sn' => 1, 'name' => 'goddy', 'id' => 12, 'age' => 93],
                ['sn' => 2, 'name' => 'Mika', 'id' => 33, 'age' => 10],
                ['sn' => 3, 'name' => 'john', 'id' => 119, 'age' => 393],
                ['sn' => 4, 'name' => 'Miska', 'id' => 90, 'age' => 10],
                ['sn' => 5, 'name' => 'Run', 'id' => 192, 'age' => 0],
                ['sn' => 5, 'name' => 'mika', 'id' => 71, 'age' => 93],
                ['sn' => 6, 'name' => 'Rash', 'id' => 33, 'age' => 81]
            ];
            $this->app->response()->send($data);
        }

        public function data()
        {

        }
    }

}
