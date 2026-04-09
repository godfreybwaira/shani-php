<?php

/**
 * Description of Sw
 * @author coder
 *
 * Created on: Jun 12, 2025 at 1:57:16 PM
 */

namespace apps\demo\modules\sw\logic\controllers\get {

    use gui\pwa\PwaAppPlatform;
    use gui\pwa\PwaCategory;
    use gui\pwa\PwaDimension;
    use gui\pwa\PwaDisplayMode;
    use gui\pwa\PwaIcon;
    use gui\pwa\PwaIconPurpose;
    use gui\pwa\PwaManifestBuilder;
    use gui\pwa\PwaOrientation;
    use gui\pwa\PwaRelatedApplication;
    use gui\pwa\PwaTextDirection;
    use gui\WebUI;
    use shani\http\App;

    final class Sw
    {

        private readonly App $app;

        public function __construct(App &$app)
        {
            $this->app = $app;
        }

        public function index(): void
        {
            $file = WebUI::assetPath('/js/pwa-sw.js');
            $this->app->response->setBody(file_get_contents($file));
            $this->app->writer->send();
        }

        public function manifest(): void
        {
            $builder = new PwaManifestBuilder('Shani yangu maanani', 'Shani', $this->app->request->uri);
            $dimension = new PwaDimension(120);
            $icon = new PwaIcon('/img/pic.png', $dimension, [
                PwaIconPurpose::ANY, PwaIconPurpose::MASKABLE
            ]);
            $icon2 = new PwaIcon('/img/pic22.png', $dimension, [
                PwaIconPurpose::ANY
            ]);
            $apps = [
                new PwaRelatedApplication(PwaAppPlatform::PLAY, $this->app->request->uri, 'com.app.id1'),
                new PwaRelatedApplication(PwaAppPlatform::ITUNES, $this->app->request->uri, 'com.app.id2'),
            ];
            $builder->addIcon($icon, $icon2)
                    ->addProtocolHandler('web', $this->app->request->uri->asString())
                    ->addScreenshot('/img/pic2.png', $dimension, 'No label')
                    ->setBackgroundColor('#938ca3')
                    ->setCategories(PwaCategory::BUSINESS, PwaCategory::FINANCE, 'other')
                    ->setDescription('My app description goes here.')
                    ->setDisplay(PwaDisplayMode::STANDALONE)
                    ->setIarcRatingId('irc.rating.id')
                    ->setLanguage('sw')
                    ->setOrientation(PwaOrientation::PORTRAIT_PRIMARY)
                    ->setRelatedApplications(true, ...$apps)
                    ->setScope('App Scope')
                    ->setTextDirection(PwaTextDirection::AUTO)
                    ->setThemeColor('#aaccbb');
            $this->app->writer->send($builder->build());
        }
    }

}
