<?php

/**
 * Description of Oauth2Controller
 * @author goddy
 *
 * @since Mar 18, 2026 at 10:52:52 AM
 */

namespace apps\demo\v1\modules\oauth2\logic\controllers\get {

    use features\attributes\security\AuthenticationCheck;
    use features\attributes\security\PermissionCheck;
    use gui\WebUIBuilder;
    use shani\launcher\App;

    #[AuthenticationCheck(exempted: true)]
    #[PermissionCheck(exempted: true)]
    final class Oauth2Controller
    {

        private readonly App $app;

        public function __construct(App $app)
        {
            $this->app = $app;
        }

        public function authorize(): WebUIBuilder
        {
            $builder = new WebUIBuilder();
            $builder->description('Shani web framework')
                    ->title('Oauth 2.0 implementation')
            ->attr->addOne('url', $this->app->request->uri);
            return $builder;
        }

        public function device(): WebUIBuilder
        {
            $builder = new WebUIBuilder();
            $builder->description('Shani web framework')
                    ->title('Oauth 2.0 implementation')
            ->attr->addOne('url', $this->app->request->uri);
            return $builder;
        }

        public function logout(): string
        {
            return $this->app->auth->logout() ? 'Success' : 'Failed';
        }
    }

}
