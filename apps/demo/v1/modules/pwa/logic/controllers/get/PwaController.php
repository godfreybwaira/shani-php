<?php

/**
 * Description of Sw
 * @author coder
 *
 * @since Jun 12, 2025 at 1:57:16 PM
 */

namespace apps\demo\v1\modules\pwa\logic\controllers\get {

    use features\assets\StaticAssetRequest;
    use features\attributes\security\AuthenticationCheck;
    use features\attributes\security\PermissionCheck;
    use features\ds\map\ReadMap;
    use features\pwa\enums\PwaAppPlatform;
    use features\pwa\enums\PwaCategory;
    use features\pwa\enums\PwaDisplayMode;
    use features\pwa\enums\PwaFormFactor;
    use features\pwa\enums\PwaIconPurpose;
    use features\pwa\enums\PwaOrientation;
    use features\pwa\enums\PwaTextDirection;
    use features\pwa\PwaDimension;
    use features\pwa\PwaIcon;
    use features\pwa\PwaManifestBuilder;
    use features\pwa\PwaRelatedApplication;
    use features\utils\File;
    use shani\http\FileOutput;
    use shani\http\HttpHeader;
    use shani\launcher\App;

    #[AuthenticationCheck(true)]
    #[PermissionCheck(true)]
    final class PwaController
    {

        private readonly App $app;

        public function __construct(App $app)
        {
            $this->app = $app;
        }

        public function manifest(): ReadMap
        {
            $builder = new PwaManifestBuilder('Shani yangu maanani', 'Shani', $this->app->request->uri);
            $dimension = new PwaDimension(1024);
            $anyIcon = new PwaIcon('https://maskable.app/demo/proxx.png', $dimension, PwaIconPurpose::ANY);
            $maskableIcon = new PwaIcon('https://maskable.app/demo/proxx.png', $dimension, PwaIconPurpose::MASKABLE);
            $apps = [
                new PwaRelatedApplication(PwaAppPlatform::PLAY, $this->app->request->uri, 'com.app.id1'),
                new PwaRelatedApplication(PwaAppPlatform::ITUNES, $this->app->request->uri, 'com.app.id2'),
            ];
            $builder->addIcon($anyIcon, $maskableIcon)
                    ->addProtocolHandler('web+myapp', '/app?a=%s')
                    ->addScreenshot('https://maskable.app/demo/proxx.png', $dimension, PwaFormFactor::WIDE)
                    ->addScreenshot('https://maskable.app/demo/proxx.png', $dimension, PwaFormFactor::NARROW)
                    ->setBackgroundColor('#938ca3')
                    ->setCategories(PwaCategory::BUSINESS, PwaCategory::FINANCE, 'other')
                    ->setDescription('My app description goes here.')
                    ->setDisplay(PwaDisplayMode::STANDALONE)
                    ->setIarcRatingId('irc.rating.id')
                    ->setLanguage('sw')
                    ->setOrientation(PwaOrientation::PORTRAIT_PRIMARY)
                    ->setRelatedApplications(true, ...$apps)
                    ->setScope('/')
                    ->setTextDirection(PwaTextDirection::AUTO)
                    ->setThemeColor('#aaccbb');
            return $builder->build();
        }

        public function serviceWorker(): FileOutput
        {
            $file = new File(StaticAssetRequest::assetPath('/js/pwa-sw.js'));
            $this->app->response->header()->addOne(HttpHeader::SERVICE_WORKER_ALLOWED, '/');
            return new FileOutput($file);
        }
    }

}
