<?php

/**
 * Description of StudentController
 * @author coder
 *
 * Created on: Feb 13, 2024 at 1:50:50 PM
 */

namespace apps\demo\modules\schools\logic\controllers\post {

    use apps\demo\modules\schools\data\dto\StudentDto;
    use apps\demo\modules\schools\logic\services\StudentService;
    use features\exceptions\CustomException;
    use features\utils\File;
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

        public function index(): HttpResponse
        {
            $data = $this->app->request->body()->getAll(['firstName', 'id', 'lastName', 'age', 'subjects']);
            $dto = StudentDto::fromArray($data);
            $student = $this->service->save(StudentDto::toEntity($dto));
            if ($student === null) {
                throw CustomException::serverError($this->app, 'Could not save student');
            }
            return HttpResponse::withBody(StudentDto::toDto($student));
        }

        public function upload(): ?HttpResponse
        {
            $file = $this->app->request->file('f1');
            $path = $this->app->storage->save($file);
            $copy = new File($this->app->storage->pathTo($path));
            $s0 = $this->app->storage->share2group($copy);
            $s1 = $this->app->storage->share2group($copy, 'grp001');
            $s2 = $this->app->storage->share2other($copy, 'user02222');
            return HttpResponse::withBody(new \features\ds\map\ReadableMap([
                                'pa' => $this->app->storage->uri($path)->asString(),
                                's0' => $this->app->storage->uri($s0)->asString(),
                                's1' => $this->app->storage->uri($s1)->asString(),
                                's2' => $this->app->storage->uri($s2)->asString(),
            ]));
        }
    }

}
