<?php

/**
 * Description of Sw
 * @author coder
 *
 * Created on: Jun 12, 2025 at 1:57:16 PM
 */

namespace apps\demo\modules\pwa\logic\controllers\get {

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
    use features\ds\map\ReadableMap;
    use shani\http\App;

    final class Manifest
    {

        private readonly App $app;

        public function __construct(App $app)
        {
            $this->app = $app;
        }

        public function index(): ReadableMap
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
    }

}
