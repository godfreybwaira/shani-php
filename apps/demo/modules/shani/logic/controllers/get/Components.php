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

        public function index(): void
        {
            $builder = new WebUIBuilder();
            $builder->description('Shani web framework')
                    ->title('Home Page II')
                    ->view('/body');
            $this->app->writer->send($builder);
        }

        public function all(): void
        {
            $this->app->writer->send(new WebUIBuilder());
        }

        public function inputs(): void
        {
            $this->app->writer->send(new WebUIBuilder());
        }

        public function containers(): void
        {
            $this->app->writer->send(new WebUIBuilder());
        }

        public function modals(): void
        {
            $builder = new WebUIBuilder();
            $builder->attr()->addIfAbsent('type', $this->app->request->query->getOne('type'));
            $this->app->writer->send($builder);
        }

        public function toaster(): void
        {
            $this->app->writer->send(new WebUIBuilder());
        }

        public function loader(): void
        {
            $builder = new WebUIBuilder();
            $builder->attr()->addIfAbsent('type', $this->app->request->query->getOne('type'));
            $this->app->writer->send($builder);
        }

        public function timeline(): void
        {
            $this->app->writer->send(new WebUIBuilder());
        }

        public function shani(): void
        {
            $this->app->writer->send(new WebUIBuilder());
        }

        public function redirect(): void
        {
            $this->app->writer->send(new WebUIBuilder());
        }

        public function generator(): void
        {
            sleep(2);
            $doc = new Documentation($this->app);
            $this->app->writer->send($doc);
        }

        public function card(): void
        {
            sleep(1);
            $this->app->writer->send(new WebUIBuilder());
        }
    }

}
