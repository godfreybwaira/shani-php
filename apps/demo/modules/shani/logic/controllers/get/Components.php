<?php

/**
 * Description of Components
 * @author coder
 *
 * Created on: Jun 12, 2025 at 1:57:16 PM
 */

namespace apps\demo\modules\shani\logic\controllers\get {

    use gui\UIBuilder;
    use shani\documentation\Generator as Documentation;
    use shani\http\App;

    final class Components
    {

        private readonly App $app;

        public function __construct(App &$app)
        {
            $this->app = $app;
        }

        public function index(): UIBuilder
        {
            $builder = new UIBuilder();
            $builder->description('Shani web framework')
                    ->title('Home Page II')
                    ->view('/body');
            return $builder;
        }

        public function all(): UIBuilder
        {
            return new UIBuilder();
        }

        public function inputs(): UIBuilder
        {
            return new UIBuilder();
        }

        public function containers(): UIBuilder
        {
            return new UIBuilder();
        }

        public function modals(): UIBuilder
        {
            $builder = new UIBuilder();
            $builder->attr()->addIfAbsent('type', $this->app->request->query->getOne('type'));
            return $builder;
        }

        public function toaster(): UIBuilder
        {
            return new UIBuilder();
        }

        public function loader(): UIBuilder
        {
            $builder = new UIBuilder();
            $builder->attr()->addIfAbsent('type', $this->app->request->query->getOne('type'));
            return $builder;
        }

        public function timeline(): UIBuilder
        {
            return new UIBuilder();
        }

        public function shani(): UIBuilder
        {
            return new UIBuilder();
        }

        public function redirect(): UIBuilder
        {
            return new UIBuilder();
        }

        public function generator(): UIBuilder
        {
            sleep(2);
            $doc = new Documentation($this->app);
            return new UIBuilder($doc);
        }

        public function card(): UIBuilder
        {
            sleep(1);
            return new UIBuilder();
        }
    }

}
