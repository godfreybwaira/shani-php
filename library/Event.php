<?php

/**
 * Description of Event
 * @author coder
 *
 * Created on: Mar 5, 2024 at 10:22:08 AM
 */

namespace library {

    final class Event implements \shani\adaptor\Handler
    {

        private $onDone = null;
        private array $callbacks = [], $events;
        private static \shani\adaptor\Event $handler;

        public function __construct(array $supportedEvents = [])
        {
            $this->events = $supportedEvents;
        }

        public static function setHandler($handler): void
        {
            self::$handler = $handler;
        }

        private function supported(string $event): bool
        {
            return empty($this->events) || in_array($event, $this->events);
        }

        public function on(string $event, callable $callback): self
        {
            if ($this->supported($event)) {
                $this->callbacks[$event][] = $callback;
            }
            return $this;
        }

        public function once(string $event, callable $callback): self
        {
            if (empty($this->callbacks[$event]) && $this->supported($event)) {
                $this->callbacks[$event] = $callback;
            }
            return $this;
        }

        public function off(string $event): self
        {
            unset($this->callbacks[$event]);
            return $this;
        }

        public function done(callable $cb): self
        {
            $this->onDone = $cb;
            return $this;
        }

        private static function finish(string $event, ?callable $cb, $data): void
        {
            if ($cb !== null) {
                $cb($event, $data);
            }
        }

        public function listening(string $event): bool
        {
            return !empty($this->callbacks[$event]);
        }

        public function trigger(string $event, ...$params): self
        {
            $cb = $this->callbacks[$event] ?? null;
            if (is_array($cb)) {
                $this->dispatch($event, $cb, $params);
            } elseif ($cb !== null) {
                $this->off($event);
                Concurrency::async(fn() => self::finish($event, $this->onDone, $cb(...$params)));
            }
            return $this;
        }

        private function dispatch(string $event, array $callbacks, ...$params): void
        {
            self::$handler->dispatch($callbacks, function ($data) use (&$event) {
                self::finish($event, $this->onDone, $data);
            }, $params);
        }
    }

}
