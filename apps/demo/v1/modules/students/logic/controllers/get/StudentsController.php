<?php

/**
 * Description of StudentController
 * @author coder
 *
 * Created on: Feb 13, 2024 at 1:50:50 PM
 */

namespace apps\demo\v1\modules\students\logic\controllers\get {

    use apps\demo\v1\modules\students\data\dto\StudentDto;
    use apps\demo\v1\modules\students\data\dto\StudentListDto;
    use apps\demo\v1\modules\students\logic\services\StudentService;
    use features\attributes\security\AuthenticationCheck;
    use features\attributes\security\PermissionCheck;
    use features\smtp\SMTPClient;
    use features\utils\File;
    use shani\http\HttpResponse;
    use shani\launcher\App;

    final class StudentsController
    {

        private readonly App $app;
        private readonly StudentService $service;

        public function __construct(App $app)
        {
            $this->app = $app;
            $this->service = StudentService::getObject($app->config->getDatabase());
        }

        public function index(): HttpResponse
        {
            $students = $this->service->getAll();
            $dtos = new StudentListDto();
            foreach ($students as $student) {
                $dtos->put(StudentDto::toDto($student));
            }
            $cart = $this->app->session->container('user');
            if (!$cart->isEmpty()) {
                return HttpResponse::withBody($dtos);
            }
            $cart->add($dtos);
            return HttpResponse::withBody('Cart is empty. Come back next time.');
        }

        /**
         * My good function.
         * Returns nothing
         */
        public function one(): ?HttpResponse
        {
            $id = (int) $this->app->request->params(3);
            $student = $this->service->getById($id);
            return $student !== null ? HttpResponse::withBody(StudentDto::toDto($student)) : null;
        }

        #[PermissionCheck(exempted: true)]
        #[AuthenticationCheck(exempted: true)]
        public function mail(): ?HttpResponse
        {
            $storage = SHANI_SERVER_ROOT . '/apps/demo/v1/modules/students/logic/controllers/get';
            $mail = new SMTPClient('localhost:1025');
            $path = new File($storage . '/picha.png');
            $pdf = new File($storage . '/in.pdf');
            $file = new File($storage . '/file.txt');
            $chi = new File($storage . '/chi.webp');
            $tmpl = $storage . '/tmpl.php';
            $mail->from('mia@mail.com', 'FromMia')->to('wendi@mail.ca', 'ToWendy')->attachments($file)
                    ->bcc('bcc@email.ca')
                    ->bcc('bcc2@email.ca')
                    ->cc('cc1@email.ca')
                    ->cc('cc2@mail.ca', 'My new CC name')->setBody($tmpl, [
                'title' => 'Heloooo', 'name' => "goddy"
            ]);
            $mail->subject('testing...')->send();
            return null;
        }
    }

}
