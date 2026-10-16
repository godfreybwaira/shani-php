<?php

/**
 * Description of AuthController
 * @author goddy
 *
 * @since Mar 18, 2026 at 10:52:52 AM
 */

namespace apps\demo\v1\modules\auth\logic\controllers\get {

    use features\attributes\security\AuthenticationCheck;
    use features\attributes\security\PermissionCheck;
    use gui\WebUIBuilder;
    use shani\launcher\App;

    #[AuthenticationCheck(exempted: true)]
    #[PermissionCheck(exempted: true)]
    final class AuthController
    {

        private readonly App $app;

        public function __construct(App $app)
        {
            $this->app = $app;
        }

        public function login(): WebUIBuilder
        {
            $builder = new WebUIBuilder();
            $builder->description('Shani web framework')->title('Login');
            return $builder;
        }

        public function a()
        {
            $payment = new PaymentCompletedWebhook([
                'tanxId' => 344,
                'status' => 'PAID'
            ]);
            $wh = new \features\webhook\WebhookManager($payment, maxRetries: 2, timeout: 10);
            $wh->post(url: $url, callback: fn(\shani\http\enums\HttpStatus $status) => $status === \shani\http\enums\HttpStatus::OK);
        }
    }

}
