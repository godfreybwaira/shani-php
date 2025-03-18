<?php

/**
 * Event Listener class that provide capability for creation of user defined
 * events and execute callbacks asynchronously.
 * @author coder
 *
 * Created on: Mar 5, 2024 at 10:22:08 AM
 */

namespace lib {

    final class Event implements \shani\contracts\Handler
    {

        private $onDone = null;
        private array $callbacks = [], $events;
        private static \shani\contracts\Event $handler;

        public function __construct(array $supportedEvents = [])
        {
            $this->events = $supportedEvents;
        }

        public static function setHandler($handler): void
        {
            self::$handler = $handler;
        }

        /**
         * Check if the event is supported (registered).
         * @param string $event Event name to check
         * @return bool True on success, false otherwise
         */
        private function supported(string $event): bool
        {
            return empty($this->events) || in_array($event, $this->events);
        }

        /**
         * Register an event to be fired asynchronous
         * @param string $event Event to register
         * @param callable $callback A callback to execute during the event execution.
         * @return self
         */
        public function on(string $event, callable $callback): self
        {
            if ($this->supported($event)) {
                $this->callbacks[$event][] = $callback;
            }
            return $this;
        }

        /**
         * Register an event to be fired asynchronous. Once the event is triggered, it will be
         * removed.
         * @param string $event Event to register
         * @param callable $callback A callback to execute during the event execution.
         * @return self
         */
        public function once(string $event, callable $callback): self
        {
            if (empty($this->callbacks[$event]) && $this->supported($event)) {
                $this->callbacks[$event] = $callback;
            }
            return $this;
        }

        /**
         * Remove/Unregister registered user defined event
         * @param string $event Event to unregister
         * @return self
         */
        public function off(string $event): self
        {
            unset($this->callbacks[$event]);
            return $this;
        }

        /**
         * Register a callback function to be triggered when an event is finished
         * @param callable $cb A callback function
         * @return self
         */
        public function done(callable $cb): self
        {
            $this->onDone = $cb;
            return $this;
        }

        /**
         * Execute callback function when the event is finished.
         * @param string $event Event that was previously executed
         * @param callable|null $cb A callback function to execute
         * @param type $data Data to pass on callback along with the event
         * @return void
         */
        private static function finish(string $event, ?callable $cb, $data): void
        {
            if ($cb !== null) {
                $cb($event, $data);
            }
        }

        /**
         * Check whether an event is registered
         * @param string $event Event to check
         * @return bool True on success, false otherwise
         */
        public function listening(string $event): bool
        {
            return !empty($this->callbacks[$event]);
        }

        /**
         * Trigger user defined event
         * @param string $event Event to trigger
         * @param type $params Parameters to be passed on callback function
         * @return self
         */
        public function trigger(string $event, ...$params): self
        {
            $cb = $this->callbacks[$event] ?? null;
            if (is_array($cb)) {
                self::$handler->dispatch($cb, function ($data) use (&$event) {
                    self::finish($event, $this->onDone, $data);
                }, $params);
            } elseif ($cb !== null) {
                $this->off($event);
                Concurrency::async(fn() => self::finish($event, $this->onDone, $cb(...$params)));
            }
            return $this;
        }
    }

}
