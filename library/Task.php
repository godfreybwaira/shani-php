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

        public function startAt(\DateTimeImmutable $duration): void
        {
            $this->startAfter($duration->getTimestamp() - time());
        }

        public function on(string $event, callable $callback): self
        {
            $this->listener->on($event, $callback);
            return $this;
        }

        public function startNow(): void
        {
            $this->startAfter(0);
        }

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

        public function frequency(int $frequency): self
        {
            $this->frequency = $frequency;
            return $this;
        }

        public function pause(): self
        {
            $this->paused = true;
            return $this->listener->trigger('pause');
        }

        public function resume(): self
        {
            $this->paused = false;
            return $this->listener->trigger('resume');
        }

        public function repeat(int $count = -1): self
        {
            $this->repeat = $count;
            return $this;
        }

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
