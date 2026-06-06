<?php

/**
 * Description of StudentController
 * @author coder
 *
 * @since Feb 13, 2024 at 1:50:50 PM
 */

namespace apps\demo\v1\modules\students\logic\controllers\get {

    use apps\demo\v1\modules\students\data\dto\SalesDto;
    use apps\demo\v1\modules\students\data\dto\StudentDto;
    use apps\demo\v1\modules\students\logic\services\StudentService;
    use features\persistence\FilterType;
    use features\persistence\sql\SQLFilter;
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
            $sale1 = new SalesDto(1, new \DateTimeImmutable('2011-01-12'), 'Kakara Chalya', 'Miwa', 2, 200);
            $sale2 = new SalesDto(2, new \DateTimeImmutable('2011-01-14'), 'Kakara Chalya', 'Mchele', 2, 1200);
            $sale3 = new SalesDto(3, new \DateTimeImmutable('2011-01-14'), 'John Kanjunju', 'Mchele', 12, 1200);
            $sale4 = new SalesDto(4, new \DateTimeImmutable('2011-02-10'), 'Warioba Kanjunju', 'Viazi', 6, 1400);
            $sale5 = new SalesDto(5, new \DateTimeImmutable('2011-02-10'), 'Warioba Kanjunju', 'Mkate', 1, 2000);
            $sale6 = new SalesDto(6, new \DateTimeImmutable('2011-02-12'), 'Warioba Kanjunju', 'Sukari', 1, 1000);
            $sale7 = new SalesDto(7, new \DateTimeImmutable('2011-02-13'), 'Godfrey', 'Sukari', 5, 1000);
//            $db->delete('sales', (new SQLFilter())->gt('id', 0));
//            $db->insertAll('sales', $sale1, $sale2, $sale3, $sale4, $sale5, $sale6, $sale7);
//            'sales', (new SQLFilter())->eq('product', 'Miwa')
            $result = $db->aggregate('sales')->sumOf('price')->groupBy('product')->run();
            return HttpResponse::withBody($result);
//            $filter1 = new \features\persistence\sql\SQLFilter();
//            $filter2 = new \features\persistence\sql\SQLFilter();
//            $filter3 = new \features\persistence\sql\SQLFilter(\features\persistence\FilterType::HAVING);
//            $filter1->eq('region', 'mwanza')->notBtw('price', 200, 400);
//            $filter2->eq('user', 'user1')->gt('age', 20)->gte('yod', 2000)->btw('date', 'd1', 'd2');
//            $filter3->gt('customer_id', 90)->neq('region', 'dar');
//            $filter4 = $filter1->or($filter2);
//            $sql5 = $db->aggregate('sales')->count('sales_amount', $filter4)
//                    ->groupBy('customer_id', false)
//                    ->groupBy('region', true)
//                    ->having($filter3);
//            $sql = $db->aggregate('sales')->sumOf('sales_amount');
//            $sql2 = $db->aggregate('sales')->avgOf('sales_amount');
//            $sql3 = $db->aggregate('sales')->maxOf('sales_amount');
//            $sql4 = $db->aggregate('sales')->minOf('sales_amount');
//            return HttpResponse::withBody($sql . PHP_EOL . $sql2 . PHP_EOL . $sql3 . PHP_EOL . $sql4 . PHP_EOL . $sql5);
//            /////////////////////////////////
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
