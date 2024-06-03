<?php

/**
 * Description of Template
 * @author coder
 *
 * Created on: Feb 19, 2024 at 1:07:12 PM
 */

namespace gui\v1 {

    use shani\engine\http\App;

    final class Template implements \gui\GUI
    {

        private const NAVBAR_TYPE = ['content-navbar-type-1', 'content-navbar-type-2'];

        private App $app;
        private bool $showMenu = false;
        private mixed $data, $state;
        private ?array $scripts, $styles;
        private ?string $title, $details, $icon, $view, $bottomType, $topType;

        public function __construct(App &$app)
        {
            $this->scripts = $this->styles = $this->data = $this->state = [];
            $this->view = $this->topType = $this->bottomType = null;
            $this->title = $this->details = $this->icon = null;
            $this->app = $app;
        }

        public function icon(string $path, string $mime): self
        {
            $this->icon = '<link rel="icon" href="' . $path . '" type="' . $mime . '"/>';
            return $this;
        }

        public function description(string $content): self
        {
            $this->details = '<meta name="description" content="' . $content . '"/>';
            return $this;
        }

        public function title(string $str): self
        {
            $this->title = $str;
            return $this;
        }

        public function scripts($srcs): self
        {
            self::createHeader($this->scripts, $srcs);
            return $this;
        }

        public function html(string $path): string
        {
            return \shani\engine\core\Path::GUI . '/v1/html' . $path . '.php';
        }

        public function css($hrefs): self
        {
            self::createHeader($this->styles, $hrefs);
            return $this;
        }

        private static function createHeader(array &$head, $urls): void
        {
            if (!is_array($urls)) {
                $head[$urls] = null;
                return;
            }
            foreach ($urls as $url => $attr) {
                $head[$url] = null;
                foreach ($attr as $key => $val) {
                    $head[$url] .= is_int($key) ? $val . ' ' : $key . '="' . $val . '" ';
                }
            }
        }

        public function render(mixed $data, mixed $state): void
        {
            $this->data = $data;
            $this->state = $state;
            $this->view ??= $this->app->view();
            if ($this->app->request()->isAjax()) {
                self::load($this->view, $this->app);
            } else {
                self::load($this->html('/main'), $this->app);
            }
        }

        public function view(string $path = null): self
        {
            if ($path === null) {
                self::load($this->view, $this->app);
            } else {
                $this->view = $path;
            }
            return $this;
        }

        public function data(): mixed
        {
            return $this->data;
        }

        public function state(): mixed
        {
            return $this->state;
        }

        public function bottom(string $path = null, int $type = 2): self
        {
            if ($path === null) {
                self::load($this->bottom ?? $this->html('/navbar'), $this->app);
            } else {
                $this->bottom = $path;
                $this->bottomType = self::NAVBAR_TYPE[$type];
            }
            return $this;
        }

        public function top(string $path = null, int $type = 2): self
        {
            if ($path === null) {
                self::load($this->top ?? $this->html('/navbar'), $this->app);
            } else {
                $this->top = $path;
                $this->topType = self::NAVBAR_TYPE[$type];
            }
            return $this;
        }

        public function menu(string $path = null): self
        {
            if ($path === null) {
                self::load($this->menu ?? $this->html('/menu'), $this->app);
            } else {
                $this->showMenu = true;
                $this->menu = $path;
            }
            return $this;
        }

        public function bottomType(): ?string
        {
            return $this->bottomType;
        }

        public function topType(): ?string
        {
            return $this->topType;
        }

        public function showMenu(): bool
        {
            return $this->showMenu;
        }

        public function head(): string
        {
            $head = $this->icon;
            foreach ($this->scripts as $url => $attr) {
                $head .= '<script ' . $attr . ' src="' . $this->app->asset()->url($url) . '"></script>';
            }
            foreach ($this->styles as $url => $attr) {
                $head .= '<link ' . $attr . ' rel="stylesheet" href="' . $this->app->asset()->url($url) . '"/>';
            }
            return $head . '<title>' . ($this->title ?? $this->app->config()->appName()) . '</title>';
        }

        public function breadcrumb(): ?string
        {
            $rs = $this->app->request()->resource();
            $bc = $this->app->module() . $this->app->config()->breadcrumbDir();
            $str = self::loadFile($bc . $this->app->request()->module());
            $str .= self::loadFile($bc . $rs . $rs);
            $str .= self::loadFile($bc . $rs . $this->app->config()->breadcrumbMethodsDir() . $this->app->request()->callback());
            return $str;
        }

        private static function load(string $_loadedFile, App &$app): void
        {
            require $_loadedFile;
        }

        private static function loadFile($file): ?string
        {
            if (is_file($file . '.php')) {
                ob_start();
                require $file . '.php';
                return '<li>' . ob_get_clean() . '</li>';
            }
            return null;
        }
    }

}