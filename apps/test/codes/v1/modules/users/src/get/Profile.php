<?php

/**
 * Description of Profile
 * @author coder
 *
 * Created on: Feb 13, 2024 at 1:50:50 PM
 */

namespace apps\test\codes\v1\modules\users\src\get {

    final class Profile
    {

        private \shani\engine\http\App $app;

        public function __construct(\shani\engine\http\App $app)
        {
            $this->app = $app;
        }

        public function activity()
        {
            $http = new \library\client\HTTP('http://dev.shani.v2.local:8008');
            $http->headers(['accept' => 'application/json']);
            $http->get('/users/0/profile/0/sample', null, function (\library\client\Response $res) {
                $test = new \library\TestCase();
                $length = $res->headers('content-length');
                $result = $test->testIf($res->bodySize())->is($length)->getResult();
                $this->app->response()->send($res->asArray());
            });
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
            $columns = $this->app->request()->columns(['sn', 'age', 'name']);
            $this->app->response()->setStatus(201);
            $this->app->response()->send(\library\Map::getAll($data, $columns));
        }
    }

}
