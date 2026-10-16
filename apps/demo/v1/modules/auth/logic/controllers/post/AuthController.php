<?php

/**
 * Description of AuthController
 * @author goddy
 *
 * @since Mar 18, 2026 at 10:52:52 AM
 */

namespace apps\demo\v1\modules\auth\logic\controllers\post {

    use features\attributes\security\AuthenticationCheck;
    use features\attributes\security\CsrfCheck;
    use features\attributes\security\PermissionCheck;
    use gui\WebUIBuilder;
    use shani\launcher\App;

    #[AuthenticationCheck(exempted: true)]
    #[PermissionCheck(exempted: true)]
    #[CsrfCheck(exempted: true)]
    final class AuthController
    {

        private readonly App $app;

        public function __construct(App $app)
        {
            $this->app = $app;
        }

        public function login()
        {
            $result = $this->app->auth->login();
            if ($result === null) {
                $builder = new WebUIBuilder();
                $builder->description('Shani web framework')->title('Login');
                $builder->attr->addOne('error', 'Invalid credentials provided. Please retry.');
                return $builder;
            }
            $this->app->response->redirect($this->app->config->pathConfig()->homePath);
        }

        public function logout(): string
        {
            return $this->app->auth->logout() ? 'Success' : 'Failed';
        }
    }

}
