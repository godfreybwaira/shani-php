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
            $db = $this->app->config->getDatabase();
            $filter1 = new \features\persistence\sql\SQLFilter();
            $filter2 = new \features\persistence\sql\SQLFilter();
            $filter3 = new \features\persistence\sql\SQLFilter(\features\persistence\FilterType::HAVING);
            $filter1->eq('region', 'mwanza')->notBtw('price', 200, 400);
            $filter2->eq('user', 'user1')->gt('age', 20)->gte('yod', 2000)->btw('date', 'd1', 'd2');
            $filter3->gt('customer_id', 90)->neq('region', 'dar');
            $filter4 = $filter1->or($filter2);
            $sql5 = $db->aggregate('sales')->count('sales_amount', $filter4)
                    ->groupBy('customer_id', false)
                    ->groupBy('region', true)
                    ->having($filter3);
            $sql = $db->aggregate('sales')->sumOf('sales_amount');
            $sql2 = $db->aggregate('sales')->avgOf('sales_amount');
            $sql3 = $db->aggregate('sales')->maxOf('sales_amount');
            $sql4 = $db->aggregate('sales')->minOf('sales_amount');
            return HttpResponse::withBody($sql . PHP_EOL . $sql2 . PHP_EOL . $sql3 . PHP_EOL . $sql4 . PHP_EOL . $sql5);
//            $students = $this->service->getAll();
//            $dtos = new StudentListDto();
//            foreach ($students as $student) {
//                $dtos->put(StudentDto::toDto($student));
//            }
//            $cart = $this->app->session->container('user');
//            if (!$cart->isEmpty()) {
//                return HttpResponse::withBody($dtos);
//            }
//            $cart->add($dtos);
//            return HttpResponse::withBody('Cart is empty. Come back next time.');
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
