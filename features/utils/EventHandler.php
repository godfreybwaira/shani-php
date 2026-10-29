<?php

/**
 * Event handler class that provide capability for creation of user defined
 * events and execute callbacks asynchronously.
 * @author coder
 *
 * @since Mar 5, 2024 at 10:22:08 AM
 */

namespace features\utils {

    final class EventHandler
    {

        private ?\Closure $onDone = null;
        private array $callbacks = [];
        private readonly array $events;

        public function __construct(array $supportedEvents = [])
        {
            $this->events = $supportedEvents;
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
         * @param \Closure $callback A callback to execute during the event execution.
         * @return self
         */
        public function on(string $event, \Closure $callback): self
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
         * @param \Closure $callback A callback to execute during the event execution.
         * @return self
         */
        public function once(string $event, \Closure $callback): self
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
         * @param \Closure $callback A callback function
         * @return self
         */
        public function done(\Closure $callback): self
        {
            $this->onDone = $callback;
            return $this;
        }

        /**
         * Execute callback function when the event is finished.
         * @param \Closure|null $cb A callback function to execute
         * @return void
         */
        private static function finish(?\Closure $cb): void
        {
            if ($cb !== null) {
                $cb();
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
            $handlers = $this->callbacks[$event] ?? null;
            if (is_array($handlers)) {
                foreach ($handlers as $cb) {
                    $cb(...$params);
                    self::finish($this->onDone);
                }
            } elseif ($handlers !== null) {
                $cb(...$params);
                self::finish($this->onDone);
                $this->off($event);
            }
            return $this;
        }

        /**
         * Get a list of all events registered to this event handler
         * @return array|null A list of all events registered, or null if no events registered.
         */
        public function getEvents(): ?array
        {
            return empty($this->callbacks) ? null : array_keys($this->callbacks);
        }
    }

}
