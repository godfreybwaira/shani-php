<?php

namespace apps\shop\v1\config {

    use shani\config\PathConfig;
    use shani\contracts\BasicConfiguration;
    use shani\launcher\App;

    final class Settings extends BasicConfiguration
    {

        private readonly PathConfig $pathConfig;

        public function __construct(App $app)
        {
            parent::__construct($app);
        }

        #[\Override]
        public function pathConfig(): PathConfig
        {
            return $this->pathConfig ??= new PathConfig(
                    mapper: $this->app->preference->mapper,
                    versionNumber: $this->app->preference->versionNumber,
                    homePath: '/users'
            );
        }

        #[\Override]
        public function isAsync(): bool
        {
            return $this->app->request->header()->getOne('X-Request-Mode') === 'async';
        }
    }

}

