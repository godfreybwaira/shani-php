<?php

/**
 * Description of Settings
 * @author coder
 *
 * Created on: Feb 18, 2024 at 2:20:26 PM
 */

namespace apps\demo\v1\config {

    use features\assets\StaticAssetServers;
    use features\oauth2\Oauth2Repository;
    use features\persistence\DatabaseDriver;
    use features\persistence\DatabaseInterface;
    use features\persistence\SQLDatabase;
    use shani\config\AuthenticationConfig;
    use shani\config\CsrfConfig;
    use shani\config\PathConfig;
    use shani\config\SessionConfig;
    use shani\contracts\BasicConfiguration;
    use shani\launcher\App;

    final class Settings extends BasicConfiguration
    {

        private readonly PathConfig $pathConfig;

        public function __construct(App $app)
        {
            parent::__construct($app);
        }

        public function csrfConfig(): CsrfConfig
        {
            return $this->csrfConfig ??= new CsrfConfig(enabled: false);
        }

        #[\Override]
        public function pathConfig(): PathConfig
        {
            return $this->pathConfig ??= new PathConfig(
                    mapper: $this->app->preference->mapper,
                    versionNumber: $this->app->preference->versionNumber,
                    homePath: '/components'
            );
        }

        #[\Override]
        public function isAsync(): bool
        {
            return $this->app->request->header()->getOne('X-Request-Mode') === 'async';
        }

        public function guestResources(): array
        {
            return [];
        }

        public function getDatabase(): ?DatabaseInterface
        {
            return new SQLDatabase(DatabaseDriver::MYSQL, 'test', 'localhost', 3306, 'testuser', 'test123');
        }

        public function getOauth2Repository(): Oauth2Repository
        {
            return new Oauth2Client();
        }

        public function getStaticAssetServer(): StaticAssetServers
        {
            return StaticAssetServers::SHANI;
        }

        public function authenticationConfig(): AuthenticationConfig
        {
            return $this->authenticationConfig ??= new AuthenticationConfig(authenticationStrategies: [
                new auth\PasswordAuthenticator($this->app),
                new auth\JwtAuthenticator($this->app),
                    ], skipAuthentication: false);
        }

        public function sessionConfig(): SessionConfig
        {
            return $this->sessionConfig ??= new SessionConfig(connection: new \features\session\dto\RedisConnectionDto('localhost', 6379));
        }
    }

}
