<?php

/**
 * Description of ServerRequest
 * @author coder
 *
 * Created on: Mar 25, 2024 at 12:36:29 PM
 */

namespace shani\server\swoole {

    final class ServerRequest implements \shani\contracts\ServerRequest
    {

        private \library\URI $uri;
        private \Swoole\Http\Request $req;
        private string $method;

        public function __construct(\Swoole\Http\Request $req, \library\URI &$uri)
        {
            $this->method = strtolower($req->server['request_method']);
            $this->uri = $uri;
            $this->req = $req;
        }

        public function raw(): ?string
        {
            return $this->req->rawcontent();
        }

        public function ip(): string
        {
            return $this->req->server['remote_addr'];
        }

        public function protocol(): string
        {
            return $this->req->server['server_protocol'];
        }

        public function time(): int
        {
            return $this->req->server['request_time'];
        }

        public function method(): string
        {
            return $this->method;
        }

        public function uri(): \library\URI
        {
            return $this->uri;
        }

        public function cookies(): ?array
        {
            return $this->req->cookie;
        }

        public function files(): ?array
        {
            return $this->req->files;
        }

        public function headers(): ?array
        {
            return $this->req->header;
        }

        public function post(): ?array
        {
            return $this->req->post;
        }

        public function get(): ?array
        {
            return $this->req->get;
        }
    }

}
