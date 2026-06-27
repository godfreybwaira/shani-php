<?php

/**
 * Description of StudentController
 * @author coder
 *
 * @since Feb 13, 2024 at 1:50:50 PM
 */

namespace apps\demo\v1\modules\students\logic\controllers\get {

    use apps\demo\v1\modules\students\data\dto\StudentDto;
    use apps\demo\v1\modules\students\data\dto\StudentListDto;
    use apps\demo\v1\modules\students\logic\services\StudentService;
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

        public function index(): mixed
        {
            $students = $this->service->getAll();
            $dtos = new StudentListDto();
            foreach ($students as $student) {
                $dtos->put(StudentDto::toDto($student));
            }
            $cart = $this->app->session->container('user');
            if (!$cart->isEmpty()) {
                return $dtos;
            }
            $cart->add($dtos);
            return 'Cart is empty. Come back next time.';
        }

        public function one(): ?StudentDto
        {
            $id = (int) $this->app->request->params(3);
            $student = $this->service->getById($id);
            return $student !== null ? StudentDto::toDto($student) : null;
        }
    }

}
