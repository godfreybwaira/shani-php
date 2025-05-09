<?php

/**
 * Description of StudentController
 * @author coder
 *
 * Created on: Feb 13, 2024 at 1:50:50 PM
 */

namespace apps\demo\modules\schools\logic\controllers\get {

    use apps\demo\modules\schools\data\dto\StudentDto;
    use apps\demo\modules\schools\logic\services\StudentService;
    use shani\http\App;

    final class Students
    {

        private readonly App $app;
        private readonly StudentService $service;

        public function __construct(App &$app)
        {
            $this->app = $app;
            $this->service = StudentService::getObject($app->config->database());
        }

        public function index()
        {
            $students = $this->service->getAll();
            $dtos = [];
            foreach ($students as $student) {
                $dtos[] = StudentDto::toDto($student);
            }
            $this->app->render($dtos);
        }

        public function one()
        {
            $id = (int) $this->app->request->params(3);
            $student = $this->service->getById($id);
            $dto = $student !== null ? StudentDto::toDto($student) : null;
            $this->app->render($dto);
        }
    }

}
