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
         * Private storage directory for static contents.
         * Accessible only by authenticated users.
         *
         * @var string
         */
        public readonly string $privateBucket;

        /**
         * Public storage directory for static contents.
         * Accessible by everyone.
         *
         * @var string
         */
        public readonly string $publicBucket;

        /**
         * Protected storage directory for static contents.
         * Accessible by everyone.
         *
         * @var string
         */
        public readonly string $protectedBucket;

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
         * @param string $protectedBucket
         *     Private storage directory. Defaults to '/1pr'.
         *
         * @param string $publicBucket
         *     Public storage directory. Defaults to '/2pb'.
         */
        public function __construct(
                string $root,
                string $homePath,
                string $controllers = '/logic/controllers',
                string $modules = '/modules',
                string $views = '/presentation/views',
                string $languages = '/presentation/lang',
                string $privateBucket = '/0pv',
                string $protectedBucket = '/1pr',
                string $publicBucket = '/2pb'
        )
        {
            $this->root = $root;
            $this->homePath = $homePath;
            $this->controllers = $controllers;
            $this->modules = $modules;
            $this->views = $views;
            $this->languages = $languages;
            $this->storage = $root . '/.bucket';
            $this->privateBucket = $privateBucket;
            $this->publicBucket = $publicBucket;
            $this->protectedBucket = $protectedBucket;
        }
    }

}
