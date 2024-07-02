<?php

/**
 * Description of Task
 * @author coder
 *
 * Created on: Mar 5, 2024 at 10:22:08 AM
 */

namespace library {


    final class Task
    {

        private Event $listener;
        private \Exception $exception;
        private bool $paused = false, $cancelled = false;
        private int $frequency = 1, $steps = 1, $repeat = 0;

        public const TIME_SECOND = 1;
        public const TIME_MINUTE = self::TIME_SECOND * 60;
        public const TIME_HOUR = self::TIME_MINUTE * 60;
        public const TIME_DAY = self::TIME_HOUR * 24;
        public const TIME_WEEK = self::TIME_DAY * 7;
        public const EVENTS = ['start', 'running', 'pause', 'resume', 'error', 'complete', 'cancel', 'repeat'];

        public function __construct()
        {
            $this->listener = new Event(self::EVENTS);
        }

        /**
         * Set start time when to execute a task
         * @param \DateTimeImmutable $duration Duration from now
         * @return void
         */
        public function startAt(\DateTimeImmutable $duration): void
        {
            $this->startAfter($duration->getTimestamp() - time());
        }

        /**
         * Set handler for given task
         * @param string $event Event name. Name must be from a list of supported events.
         * @param callable $callback Function to execute when event is triggered.
         * @return self
         */
        public function on(string $event, callable $callback): self
        {
            $this->listener->on($event, $callback);
            return $this;
        }

        /**
         * Execute a task immediately.
         * @return void
         */
        public function startNow(): void
        {
            $this->startAfter(0);
        }

        /**
         * Execute a task after a given seconds from now
         * @param int $seconds
         * @return void
         */
        public function startAfter(int $seconds): void
        {
            Concurrency::async(function ()use (&$seconds) {
                $this->listener->trigger('start');
                $counter = 0;
                do {
                    try {
                        Concurrency::sleep($seconds);
                        $this->execTask($counter)->handleRepetition($counter);
                    } catch (Exception $exc) {
                        $this->exception = $exc;
                        $this->listener->trigger('error');
                    }
                } while (!$this->cancelled && ($this->repeated() || $counter < $this->frequency));
            });
        }

        private function execTask(int &$counter): self
        {
            $diff = 0;
            while ($this->running() && $counter++ < $this->frequency) {
                if ($diff < $this->steps) {
                    Concurrency::sleep(floor($this->steps - $diff));
                }
                $start = time();
                $this->listener->trigger('running');
                $diff = time() - $start;
            }
            return $this;
        }

        private function handleRepetition(int &$counter): void
        {
            if ($counter >= $this->frequency) {
                if ($this->repeated()) {
                    if ($this->repeat > 0) {
                        $this->repeat--;
                    }
                    $this->listener->trigger('repeat');
                    $counter = 0;
                } else {
                    $this->listener->trigger('complete');
                }
            }
        }

        /**
         * Set number of steps to pause execution of a task before the next execution.
         * @param int $seconds Number of seconds to pause.
         * @return self
         */
        public function steps(int $seconds): self
        {
            $this->steps = $seconds;
            return $this;
        }

        private function running(): bool
        {
            return !$this->cancelled && !$this->paused;
        }

        private function repeated(): bool
        {
            return $this->repeat === -1 || $this->repeat > 1;
        }

        /**
         * Set how frequent the task should repeat before it stop. One frequency
         * indicate one circle.
         * @param int $frequency Number of times a task is allowed to repeat before
         * ending.
         * @return self
         */
        public function frequency(int $frequency): self
        {
            $this->frequency = $frequency;
            return $this;
        }

        /**
         * Pause execution of a task
         * @return self
         */
        public function pause(): self
        {
            $this->paused = true;
            return $this->listener->trigger('pause');
        }

        /**
         * Resume execution of a task
         * @return self
         */
        public function resume(): self
        {
            $this->paused = false;
            return $this->listener->trigger('resume');
        }

        /**
         * Set how many a task is to be repeated in each circle or frequency.
         * @param int $count
         * @return self
         */
        public function repeat(int $count = -1): self
        {
            $this->repeat = $count;
            return $this;
        }

        /**
         * Cancel task execution.
         * @return void
         */
        public function cancel(): void
        {
            $this->cancelled = true;
            $this->listener->trigger('cancel');
        }

        public function getException(): \Exception
        {
            return $this->exception;
        }
    }

}
