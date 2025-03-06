<?php

/**
 * Description of HttpCache
 * @author coder
 *
 * Created on: Mar 2, 2025 at 6:05:25 PM
 */

namespace library\http {

    final class HttpCache
    {

        private bool $reuse, $versioned, $accessible, $revalidate, $unique;
        private \DateTime $maxAge;
        private ?\DateTime $stale;
        private ?string $etag;

        public function __construct(bool $reuse = false)
        {
            $this->maxAge = new \DateTime('6m');
            $this->setReuse($reuse);
            $this->stale = null;
            $this->etag = null;
            $this->versioned = false;
            $this->accessible = false;
            $this->revalidate = false;
            $this->unique = false;
        }

        public function setReuse(bool $reuse): self
        {
            $this->reuse = $reuse;
            return $this;
        }

        public function etag(): ?string
        {
            return $this->etag;
        }

        public function setMaxAge(\DateTime $age): self
        {
            $this->maxAge = $age;
            return $this;
        }

        public function setVersioned(bool $versioned): self
        {
            $this->versioned = $versioned;
            return $this;
        }

        public function setEtag(bool $etag): self
        {
            $this->etag = $etag;
            return $this;
        }

        public function setPublic(bool $accessible): self
        {
            $this->accessible = $accessible;
            return $this;
        }

        public function setRevalidate(bool $revalidate): self
        {
            $this->revalidate = $revalidate;
            return $this;
        }

        public function setUnique(bool $unique): self
        {
            $this->unique = $unique;
            return $this;
        }

        public function setStale(\DateTime $stale): self
        {
            $this->stale = $stale;
            return $this;
        }

        public function __toString(): string
        {
            $directives = [];
            if ($this->reuse) {
                $directives[] = $this->accessible ? 'no-cache' : 'no-store';
            } else {
                if ($this->unique) {
                    $directives[] = 'private';
                }
                $directives[] = 'max-age=' . $this->maxAge->getTimestamp();
                if ($this->versioned) {
                    $directives[] = 'immutable';
                } else if ($this->revalidate) {
                    $age = $this->stale ?? $this->maxAge;
                    $directives[] = 'stale-while-revalidate=' . $age->getTimestamp();
                }
            }
            return implode(',', $directives);
        }
    }

}
