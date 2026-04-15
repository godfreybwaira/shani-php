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
    use shani\launcher\App;

    final class Students
    {

        private readonly App $app;
        private readonly StudentService $service;

        public function __construct(App $app)
        {
            $this->app = $app;
            $this->service = StudentService::getObject($app->config->database());
        }

        public function index(): void
        {
            $students = $this->service->getAll();
            $dtos = new StudentListDto();
            foreach ($students as $student) {
                $dtos->put(StudentDto::toDto($student));
            }
            $cart = $this->app->session->cart('user');
            if ($cart->isEmpty()) {
                $cart->addJson($dtos);
                $this->app->response->setBody('Cart is empty. Come back next time.');
                $this->app->writer->send();
            } else {
                $this->app->writer->send($this->app->framework->config);
            }
        }

        /**
         * My good function.
         * Returns nothing
         */
        public function one(): void
        {
            $id = (int) $this->app->request->params(3);
            $student = $this->service->getById($id);
            $dto = $student !== null ? StudentDto::toDto($student) : null;
            $this->app->writer->send($dto);
        }
    }

}
