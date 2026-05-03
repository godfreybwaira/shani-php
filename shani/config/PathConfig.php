<?php

/**
 * Description of PathConfig
 * @author goddy
 *
 * Created on: Apr 24, 2026 at 1:26:35 PM
 */

namespace shani\config {

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
        public readonly string $enums;

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
         * Private storage directory for static contents. Private assets
         * can be accessed by asset owner, group or anyone if it is the group
         * asset and no group is provided.
         *
         * @var string
         */
        public readonly string $privateBucket;

        /**
         * Public storage directory for static contents. Public assets is
         * accessible by everyone.
         *
         * @var string
         */
        public readonly string $publicBucket;

        /**
         * Constructor for PathConfig.
         *
         * Initializes application path configuration with defaults if none are provided.
         *
         * @param string $root
         *     Application root directory.
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
         * @param string $enums
         *     Directory for ENUMS inside modules. Defaults to '/data/enums'.
         *
         * @param string $modules
         *     Directory for application modules. Defaults to '/modules'.
         *
         * @param string $views
         *     Directory for views. Defaults to '/presentation/views'.
         *
         * @param string $languages
         *     Directory for language files. Defaults to '/presentation/lang'.
         *
         * @param string $privateBucket
         *     Private storage directory. Defaults to '/0pv'.
         *
         * @param string $publicBucket
         *     Public storage directory. Defaults to '/1pb'.
         */
        public function __construct(
                string $root,
                string $homePath,
                string $controllers = '/logic/controllers',
                string $services = '/logic/services',
                string $dto = '/data/dto',
                string $entities = '/data/entities',
                string $enums = '/data/enums',
                string $modules = '/modules',
                string $views = '/presentation/views',
                string $languages = '/presentation/lang',
                string $privateBucket = '/0pv',
                string $publicBucket = '/1pb'
        )
        {
            $this->root = $root;
            $this->homePath = $homePath;
            $this->controllers = $controllers;
            $this->services = $services;
            $this->modules = $modules;
            $this->views = $views;
            $this->languages = $languages;
            $this->storage = $root . '/.bucket';
            $this->privateBucket = $privateBucket;
            $this->publicBucket = $publicBucket;
            $this->dto = $dto;
            $this->entities = $entities;
            $this->enums = $enums;
        }
    }

}
