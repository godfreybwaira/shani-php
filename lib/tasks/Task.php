<?php

/**
 * A custom task scheduler API
 * @author coder
 *
 * Created on: Mar 5, 2024 at 10:22:08 AM
 */

namespace lib\tasks {

    use lib\Concurrency;
    use lib\Duration;
    use lib\Event;

    final class Task
    {

        private \Exception $exception;
        private readonly Event $listener;
        private bool $paused = false, $cancelled = false;
        private int $frequency = 1, $steps = 1, $repeat = 0;

        public function __construct()
        {
            $this->listener = new Event(array_map(fn(TaskEvent $e) => $e->name, TaskEvent::cases()));
        }

        /**
         * Set start time on when to execute a task
         * @param \DateTimeInterface $duration Duration from now
         * @return self
         */
        public function startAt(\DateTimeInterface $duration): self
        {
            return $this->startAfter($duration);
        }

        /**
         * Set handler for given task event
         * @param TaskEvent $event Event name.
         * @param callable $callback Function to execute when event is triggered.
         * @return self
         */
        public function on(TaskEvent $event, callable $callback): self
        {
            $this->listener->on($event->name, $callback);
            return $this;
        }

        /**
         * Execute a task immediately.
         * @return self
         */
        public function startNow(): self
        {
            return $this->startAfter(Duration::of(0, Duration::SECONDS));
        }

        /**
         * Execute a task after a given seconds from now
         * @param \DateTimeInterface $duration
         * @return self
         */
        public function startAfter(\DateTimeInterface $duration): self
        {
            $seconds = $duration->getTimestamp() - time();
            Concurrency::thread(function ()use (&$seconds) {
                $this->listener->trigger(TaskEvent::START->name);
                $counter = 0;
                do {
                    try {
                        Concurrency::sleep($seconds);
                        $this->execTask($counter)->handleRepetition($counter);
                    } catch (\Exception $exc) {
                        $this->exception = $exc;
                        $this->listener->trigger(TaskEvent::ERROR->name);
                    }
                } while (!$this->cancelled && ($this->repeated() || $counter < $this->frequency));
            });
            return $this;
        }

        private function execTask(int &$counter): self
        {
            $diff = 0;
            while ($this->running() && $counter++ < $this->frequency) {
                if ($diff < $this->steps) {
                    Concurrency::sleep(floor($this->steps - $diff));
                }
                $start = time();
                $this->listener->trigger(TaskEvent::RUNNING->name);
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
                    $this->listener->trigger(TaskEvent::REPEAT->name);
                    $counter = 0;
                } else {
                    $this->listener->trigger(TaskEvent::COMPLETE->name);
                }
            }
        }

        /**
         * Set number of steps to pause execution of a task before the next execution.
         * @param DateTimeInterface $duration Duration to pause the execution of a task.
         * @return self
         */
        public function steps(\DateTimeInterface $duration): self
        {
            $this->steps = $duration->getTimestamp() - time();
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
            return $this->listener->trigger(TaskEvent::PAUSE->name);
        }

        /**
         * Resume execution of a task
         * @return self
         */
        public function resume(): self
        {
            $this->paused = false;
            return $this->listener->trigger(TaskEvent::RESUME->name);
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
            $this->listener->trigger(TaskEvent::CANCEL->name);
        }

        /**
         * Get any exception occurred during task execution
         * @return \Exception
         */
        public function getException(): \Exception
        {
            return $this->exception;
        }
    }

}
