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
    use features\attributes\AuthorizationCheck;
    use features\attributes\CsrfCheck;
    use shani\http\HttpResponse;
    use shani\launcher\App;

    #[CsrfCheck(exempted: true)]
    final class StudentsController
    {

        private readonly App $app;
        private readonly StudentService $service;

        public function __construct(App $app)
        {
            $this->app = $app;
            $this->service = StudentService::getObject($app->config->getDatabase());
        }

        #[AuthorizationCheck(true)]
        #[CsrfCheck(false)]
        public function index(): HttpResponse
        {
            $students = $this->service->getAll();
            $dtos = new StudentListDto();
            foreach ($students as $student) {
                $dtos->put(StudentDto::toDto($student));
            }
            $cart = $this->app->session->cart('user');
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
    }

}
