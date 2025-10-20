<?php

/**
 * Description of Components
 * @author coder
 *
 * Created on: Jun 12, 2025 at 1:57:16 PM
 */

namespace apps\demo\modules\shani\logic\controllers\get {

    use gui\WebUIBuilder;
    use shani\documentation\Generator as Documentation;
    use shani\http\App;

    final class Components
    {

        private readonly App $app;

        public function __construct(App &$app)
        {
            $this->app = $app;
        }

        public function index(): WebUIBuilder
        {
            $builder = new WebUIBuilder();
            $builder->description('Shani web framework')
                    ->title('Home Page II')
                    ->view('/body');
            return $builder;
        }

        public function all(): WebUIBuilder
        {
            return new WebUIBuilder();
        }

        public function inputs(): WebUIBuilder
        {
            return new WebUIBuilder();
        }

        public function containers(): WebUIBuilder
        {
            return new WebUIBuilder();
        }

        public function modals(): WebUIBuilder
        {
            $builder = new WebUIBuilder();
            $builder->attr()->addIfAbsent('type', $this->app->request->query->getOne('type'));
            return $builder;
        }

        public function toaster(): WebUIBuilder
        {
            return new WebUIBuilder();
        }

        public function loader(): WebUIBuilder
        {
            $builder = new WebUIBuilder();
            $builder->attr()->addIfAbsent('type', $this->app->request->query->getOne('type'));
            return $builder;
        }

        public function timeline(): WebUIBuilder
        {
            return new WebUIBuilder();
        }

        public function shani(): WebUIBuilder
        {
            return new WebUIBuilder();
        }

        public function redirect(): WebUIBuilder
        {
            return new WebUIBuilder();
        }

        public function generator(): WebUIBuilder
        {
            sleep(2);
            $doc = new Documentation($this->app);
            return new WebUIBuilder($doc);
        }

        public function card(): WebUIBuilder
        {
            sleep(1);
            return new WebUIBuilder();
        }
    }

}
