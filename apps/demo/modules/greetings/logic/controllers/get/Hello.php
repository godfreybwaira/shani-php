<?php

/**
 * Description of Hello
 * @author coder
 *
 * Created on: Feb 13, 2024 at 1:50:50 PM
 */

namespace apps\demo\modules\greetings\logic\controllers\get {

    use apps\demo\modules\greetings\data\dto\Student;
    use apps\demo\modules\greetings\data\dto\Subject;
    use lib\client\HttpClient;
    use lib\http\HttpHeader;
    use lib\http\ResponseEntity;
    use lib\MediaType;
    use shani\http\App;

    final class Hello
    {

        private readonly App $app;

        public function __construct(App &$app)
        {
            $this->app = $app;
        }

        /**
         * Display greetings from Shani.
         */
        public function world()
        {
            //using global style
//            $this->app->ui()->style('/css/styles.css');
            $this->app->ui()->script('/js/shani-ob-2.0.js');
            $this->app->ui()->script('/js/shani-plugins.js');
//            passing data to current view (greetings/presentation/views/hello/world.php)
            $student = new Student('Godfrey', 'Bwaira', 20);
            $student->addSubject(new Subject('English', 'A', 89))
                    ->addSubject(new Subject('Kiswahili', 'B', 79))
                    ->addSubject(new Subject('Maths', 'C', 70.6));
            $this->app->render($student->jsonSerialize());
        }

        public function test()
        {
            $header = new HttpHeader();
            $header->addOne(HttpHeader::ACCEPT_VERSION, 'api');
            $header->addOne(HttpHeader::ACCEPT, $this->app->request->header()->getOne(HttpHeader::ACCEPT));
            $uri = new \lib\URI('http://localhost:8008/greetings/0/hello/0/test');
            $client = new HttpClient($uri);
            $client->setBody((new Subject('Mathemtics', 'D', 70.6))->jsonSerialize());
            $client->setHeader($header);
            $file = '/home/coder/Pictures/abc.png';
            $client->signature('abc');
            $client->post('/greetings/0/hello/0/test', function (ResponseEntity $response) {
                print_r($response->request->header()->toArray());
                $this->app->response->setStatus($response->status())->setBody($response->body());
                $this->app->send();
            });
        }

        public function abc()
        {
            $file = '/home/coder/Pictures/Screenshot from 2022-11-05 18-34-36.png';
            $this->app->stream($file);
        }
    }

}
