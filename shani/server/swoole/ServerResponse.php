<?php

/**
 * Description of ServerResponse
 * @author coder
 *
 * Created on: Mar 25, 2024 at 1:26:42 PM
 */

namespace shani\server\swoole {

    final class ServerResponse implements \shani\contracts\ServerResponse
    {

        private \Swoole\Http\Response $res;

        public function __construct(\Swoole\Http\Response $res)
        {
            $this->res = $res;
        }

        public function sendHeaders(array $values): self
        {
            if ($this->res->isWritable()) {
                foreach ($values as $key => $value) {
                    $this->res->header($key, $value);
                }
            }
            return $this;
        }

        public function write(?string $content = null): self
        {
            $this->res->write($content);
            return $this;
        }

        public function close(?string $content = null): self
        {
            $this->res->end($content);
            return $this;
        }

        public function redirect(string $url, int $code): self
        {
            $this->res->redirect($url, $code);
            return $this;
        }

        public function setStatus(int $code, string $message = ''): self
        {
            $this->res->status($code, $message);
            return $this;
        }

        public function sendFile(string $path, int $start, int $chunk): self
        {
            $this->res->sendfile($path, $start, $chunk);
            return $this;
        }
    }

}
