<?php

/**
 * Description of Settings
 * @author coder
 *
 * Created on: Feb 18, 2024 at 2:20:26 PM
 */

namespace apps\test\codes\v1\config {

    use shani\engine\core\AutoConfig;
    use shani\engine\middleware\Register;

    final class Settings extends AutoConfig
    {

        public function __construct(\shani\engine\http\App &$app)
        {
            parent::__construct($app);
        }

        public function root(): string
        {
            return App::ROOT_DIR;
        }

        public function fallbackUrl(): string
        {
            return Module::FALLBACK;
        }

        public function middleware(Register &$mw): void
        {
            \apps\test\codes\v1\middleware\Register::exec($this->app, $mw);
        }

        public function assetDir(): ?string
        {
            return App::ASSET_DIR;
        }

        public function homepage(): string
        {
            return $this->app->auth()->verified() ? Module::HOME_AUTH : Module::HOME_GUEST;
        }

        public function signatureSecretKey(): string
        {
            return '';
        }

        public function templateVersion(): string
        {
            return App::TEMPLATE_VERSION;
        }
    }

}
