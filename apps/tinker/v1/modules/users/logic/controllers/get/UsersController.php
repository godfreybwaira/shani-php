<?php

namespace apps\tinker\v1\modules\users\logic\controllers\get {

    use features\ds\map\ReadMap;
    use gui\WebUIBuilder;
    use shani\launcher\App;

    final class UsersController
    {

        private readonly App $app;

        public function __construct(App $app)
        {
            $this->app = $app;
        }

        public function index(): WebUIBuilder
        {
            $data = new ReadMap([
                'name' => get_current_user(),
                'title' => 'developer'
            ]);

            $builder = new WebUIBuilder($data);
            $builder->title('Users')
            ->description('Description of Users')
            ->attr->addOne('name', $data->getOne('name'));

            return $builder;
        }
    }

}

