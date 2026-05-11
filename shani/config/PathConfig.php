<?php

/**
 * Description of PathConfig
 * @author goddy
 *
 * Created on: Apr 24, 2026 at 1:26:35 PM
 */

namespace shani\config {

    use shani\launcher\Framework;
    use shani\utils\VirtualHostMapper;

    /**
     * Defines default directory path configurations for an application.
     *
     * This class centralizes configuration of important application paths,
     * including root, modules, controllers, views, language files, and storage.
     * It ensures consistent directory structure and provides sensible defaults
     * for storage locations.
     *
     * Key features:
     * - Root directory of the application
     * - Default home path for requests to '/'
     * - Controllers directory (for HTTP method-based controllers)
     * - Modules directory
     * - Views directory
     * - Languages directory
     * - Storage directories (general, protected, public)
     *
     */
    final class PathConfig
    {

        /**
         * Application root directory.
         *
         * @var string
         */
        public readonly string $root;

        /**
         * Default URI path to a home page if '/' is provided during HTTP request.
         *
         * @var string
         */
        public readonly string $homePath;

        /**
         * The directory inside application module where module controllers reside.
         * This is where GET, POST, PUT, DELETE, or other HTTP method directories
         * must be created (in lowercase).
         *
         * @var string
         */
        public readonly string $controllers;

        /**
         * The directory inside application module where module services reside.
         *
         * @var string
         */
        public readonly string $services;

        /**
         * The directory inside application module where DTO reside.
         *
         * @var string
         */
        public readonly string $dto;

        /**
         * The directory inside application module where Entities (models) reside.
         *
         * @var string
         */
        public readonly string $entities;

        /**
         * The directory inside application module where ENUMS reside.
         *
         * @var string
         */
        public readonly string $values;

        /**
         * Application modules directory.
         *
         * @var string
         */
        public readonly string $modules;

        /**
         * User-defined view directory.
         *
         * @var string
         */
        public readonly string $views;

        /**
         * User language directory. Each module contains its own language files here.
         *
         * @var string
         */
        public readonly string $languages;

        /**
         * Application storage directory for static files.
         * Must be accessible and writable by the web server.
         *
         * @var string
         */
        public readonly string $storage;

        /**
         * Constructor for PathConfig.
         *
         * Initializes application path configuration with defaults if none are provided.
         *
         * @param VirtualHostMapper $mapper
         *     Virtual host configuration.
         *
         * @param string $versionNumber
         *     Requested version number
         *
         * @param string $homePath
         *     Default URI path to a home page if '/' is requested.
         *
         * @param string $controllers
         *     Directory for controllers inside modules. Defaults to '/logic/controllers'.
         *
         * @param string $services
         *     Directory for services inside modules. Defaults to '/logic/services'.
         *
         * @param string $dto
         *     Directory for DTO inside modules. Defaults to '/data/dto'.
         *
         * @param string $entities
         *     Directory for entities (models) inside modules. Defaults to '/data/entities'.
         *
         * @param string $values
         *     Directory for Value objects inside modules. Defaults to '/data/values'.
         *
         * @param string $modules
         *     Directory for application modules. Defaults to '/modules'.
         *
         * @param string $views
         *     Directory for views. Defaults to '/presentation/views'.
         *
         * @param string $languages
         *     Directory for language files. Defaults to '/presentation/lang'.
         */
        public function __construct(
                VirtualHostMapper $mapper,
                string $versionNumber,
                string $homePath,
                string $controllers = '/logic/controllers',
                string $services = '/logic/services',
                string $dto = '/data/dto',
                string $entities = '/data/entities',
                string $values = '/data/values',
                string $modules = '/modules',
                string $views = '/presentation/views',
                string $languages = '/presentation/lang',
        )
        {
            $projectRoot = Framework::DIR_APPS . '/' . $mapper->projectName;
            $this->root = $projectRoot . '/' . $mapper->getVersionName($versionNumber);
            $this->homePath = $homePath;
            $this->controllers = $controllers;
            $this->services = $services;
            $this->modules = $modules;
            $this->views = $views;
            $this->languages = $languages;
            $this->storage = $projectRoot . $mapper->appStorage;
            $this->dto = $dto;
            $this->entities = $entities;
            $this->values = $values;
        }
    }

}
