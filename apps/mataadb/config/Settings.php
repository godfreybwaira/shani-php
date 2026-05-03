<?php

namespace apps\mataadb\config {

    use shani\config\PathConfig;
    use shani\contracts\BasicConfiguration;
    use shani\launcher\App;
    use shani\launcher\Framework;

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
            return $this->pathConfig ??= new PathConfig(root: Framework::DIR_APPS . '/mataadb', homePath: '/users/0/account/0/index');
        }

        #[\Override]
        public function isAsync(): bool
        {
            return $this->app->request->header()->getOne('X-Request-Mode') === 'async';
        }
    }

}

