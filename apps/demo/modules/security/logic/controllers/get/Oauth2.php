<?php

/**
 * Description of Oauth2
 * @author goddy
 *
 * Created on: Mar 18, 2026 at 10:52:52 AM
 */

namespace apps\demo\modules\security\logic\controllers\get {

    use gui\WebUIBuilder;
    use shani\launcher\App;

    final class Oauth2
    {

        private readonly App $app;

        public function __construct(App $app)
        {
            $this->app = $app;
        }

        public function authorize()
        {
            $builder = new WebUIBuilder();
            $builder->description('Shani web framework')
                    ->title('Oauth 2.0 implementation')
            ->attr->addOne('url', $this->app->request->uri);
            $this->app->writer->send($builder);
        }

        public function device()
        {
            $builder = new WebUIBuilder();
            $builder->description('Shani web framework')
                    ->title('Oauth 2.0 implementation')
            ->attr->addOne('url', $this->app->request->uri);
            $this->app->writer->send($builder);
        }
    }

}
