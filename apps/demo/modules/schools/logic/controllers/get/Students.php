<?php

/**
 * Description of StudentController
 * @author coder
 *
 * Created on: Feb 13, 2024 at 1:50:50 PM
 */

namespace apps\demo\modules\schools\logic\controllers\get {

    use apps\demo\modules\schools\data\dto\StudentDto;
    use apps\demo\modules\schools\data\dto\StudentListDto;
    use apps\demo\modules\schools\logic\services\StudentService;
    use shani\http\HttpResponse;
    use shani\launcher\App;

    final class Students
    {

        private readonly App $app;
        private readonly StudentService $service;

        public function __construct(App $app)
        {
            $this->app = $app;
            $this->service = StudentService::getObject($app->config->getDatabase());
        }

        public function index(): ?HttpResponse
        {
            $students = $this->service->getAll();
            $dtos = new StudentListDto();
            foreach ($students as $student) {
                $dtos->put(StudentDto::toDto($student));
            }
            $cart = $this->app->session->cart('user');
            if (!$cart->isEmpty()) {
                return new HttpResponse($dtos);
            }
            $cart->add($dtos);
            $this->app->response->setBody('Cart is empty. Come back next time.');
            return null;
        }

        /**
         * My good function.
         * Returns nothing
         */
        public function one(): ?HttpResponse
        {
            $id = (int) $this->app->request->params(3);
            $student = $this->service->getById($id);
            return $student !== null ? new HttpResponse(StudentDto::toDto($student)) : null;
        }
    }

}
