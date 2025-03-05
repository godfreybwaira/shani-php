<?php

/**
 * Description of Response
 * @author coder
 *
 * Created on: Feb 11, 2024 at 6:50:02 PM
 */

namespace shani\engine\http {

    use library\HttpStatus;
    use shani\contracts\ResponseDto;
    use shani\contracts\ResponseWriter;

    final class Response
    {

        private readonly App $app;
        private HttpStatus $httpStatus;
        private array $headers, $cookies;
        private ResponseWriter $res;

        public function __construct(App &$app, ResponseWriter &$res)
        {
            $this->headers = ['x-content-type-options' => 'nosniff'];
            $this->httpStatus = HttpStatus::OK;
            $this->cookies = [];
            $this->res = $res;
            $this->app = $app;
        }

        private function write(?string $content): self
        {
            if ($content === null || $content === '') {
                return $this->setHeaders('content-length', 0)->close();
            }
            $encoding = $this->app->request()->headers('accept-encoding');
            $ratio = $this->app->config()->compressionLevel();
            $minSize = $this->app->config()->compressionMinSize();
            if ($encoding === null || $ratio === 0 || mb_strlen($content) < $minSize) {
                return $this->finish($content);
            } elseif (str_contains($encoding, 'gzip')) {
                return $this->setHeaders('content-encoding', 'gzip')->finish(gzencode($content, $ratio));
            } elseif (str_contains($encoding, 'deflate')) {
                return $this->setHeaders('content-encoding', 'deflate')->finish(gzdeflate($content, $ratio));
            }
            return $this->finish($content);
        }

        /**
         * Send HTTP response body, leaving connection open. Ideal when wanting
         * to send data in chunks. Remember to close the connection when done.
         * @param ResponseDto $dto Data object to send
         * @param string|null $type Type to send
         * @return self
         * @see self::close(), self::useBuffer()
         */
        public function send(ResponseDto $dto, ?string $type = null): self
        {
            $datatype = $type ?? $this->type();
            switch ($datatype) {
                case'json':
                    return $this->sendAsJson($dto);
                case'xml':
                    return $this->sendAsXml($dto);
                case'csv':
                    return $this->sendAsCsv($dto);
                case'yaml':
                case'yml':
                    return $this->sendAsYaml($dto);
                case'sse':
                case'event-stream':
                    return $this->sendAsSse(json_encode($dto->asMap()));
                case'js':
                case'jsonp':
                    return $this->sendAsJsonp($dto, $this->app->request()->query('callback') ?? 'callback');
                default :
                    return $this->plainText(serialize($dto->asMap()), \library\MediaType::BIN);
            }
        }

        private function plainText(?string $content, string $type): self
        {
            $this->setHeaders('content-type', $type);
            return $this->write($content);
        }

        /**
         * Send HTTP response body as HTML
         * @param string $html HTML string to send
         * @return self
         */
        public function sendAsHtml(string $html): self
        {
            return $this->plainText($html, \library\MediaType::TEXT_HTML);
        }

        /**
         * Send HTTP response body as Server sent event
         * @param string $content Data to send
         * @return self
         */
        public function sendAsSse(string $content, string $event = 'message'): self
        {
            $this->setHeaders('cache-control', 'no-cache');
            $evt = 'id:id' . hrtime(true) . PHP_EOL;
            $evt .= 'event:' . $event . PHP_EOL;
            $evt .= 'data:' . $content . PHP_EOL;
            $evt .= PHP_EOL;
            return $this->plainText($evt, 'text/event-stream', null);
        }

        /**
         * Send HTTP response body as JSON
         * @param ResponseDto $dto Data object to send
         * @return self
         */
        public function sendAsJson(ResponseDto $dto): self
        {
            return $this->plainText(json_encode($dto->asMap()), \library\MediaType::JSON);
        }

        /**
         * Send HTTP response body as JSON with padding
         * @param ResponseDto $dto Data object to send
         * @param string $callback JavaScript callback function
         * @return self
         */
        public function sendAsJsonp(ResponseDto $dto, string $callback): self
        {
            $this->setHeaders('content-type', \library\MediaType::JS);
            return $this->write($callback . '(' . json_encode($dto->asMap()) . ');');
        }

        /**
         * Send HTTP response body as XML
         * @param ResponseDto $dto Data object to send
         * @return self
         */
        public function sendAsXml(ResponseDto $dto): self
        {
            $xml = \library\DataConvertor::array2xml($dto->asMap());
            return $this->plainText($xml, \library\MediaType::XML);
        }

        /**
         * Send HTTP response body as CSV
         * @param ResponseDto $dto Data object to send
         * @param string $separator data separator
         * @return self
         */
        public function sendAsCsv(ResponseDto $dto, string $separator = ','): self
        {
            $csv = \library\DataConvertor::array2csv($dto->asMap(), $separator);
            return $this->plainText($csv, \library\MediaType::TEXT_CSV);
        }

        /**
         * Send HTTP response body as YAML
         * @param ResponseDto $dto Data object to send
         * @return self
         */
        public function sendAsYaml(ResponseDto $dto): self
        {
            return $this->plainText(yaml_emit($dto->asMap()), \library\MediaType::TEXT_YAML);
        }

        /**
         * Output a file to user agent
         * @param string $filename filename to send
         * @return self
         */
        public function saveAs(string $filename = null): self
        {
            $name = $filename ? '; filename="' . $filename . '"' : '';
            return $this->setHeaders('content-disposition', 'attachment' . $name);
        }

        /**
         * Send HTTP response headers
         * @param array|null $headers Headers to send
         * @return self
         */
        private function sendHeaders(?array $headers = null): self
        {
            if ($headers !== null) {
                $this->setHeaders($headers);
            }
            if (count($this->cookies) > 0) {
                $this->setHeaders('set-cookie', implode(',', $this->cookies));
            }
            $this->setHeaders('server', \shani\engine\core\Framework::NAME);
            $this->res->sendHeaders($this->headers);
            return $this;
        }

        /**
         * Set HTTP response headers
         * @param string|array $headers header to send, if it is string then value must be
         * provided, else it must be an array of key-value pair where key is the
         * header name and value is header value.
         * @param string|null $val header value
         * @return self
         */
        public function setHeaders(string|array $headers, ?string $val = null): self
        {
            if (is_array($headers)) {
                foreach ($headers as $key => $value) {
                    $this->headers[trim(strtolower($key))] = $value;
                }
            } else {
                $this->headers[trim(strtolower($headers))] = $val;
            }
            return $this;
        }

        /**
         * Get HTTP response headers
         * @param string|array $names header name
         * @param bool $selected If set to true, only the selected values will be returned.
         * @return type
         */
        public function headers(string|array $names = null, bool $selected = true)
        {
            return \library\Map::get($this->headers, $names, $selected);
        }

        /**
         * Get HTTP cookie object(s)
         * @param string $name named key
         * @param bool $selected If set to true, only the selected values will be returned.
         * @return \library\Cookie|null
         */
        public function cookie(string $name): ?\library\Cookie
        {
            return $this->cookies[$name] ?? null;
        }

        /**
         * Send HTTP response redirect
         * @param string $url new destination
         * @param HttpStatus $status HTTP status code, default is 302
         * @return self
         */
        public function redirect(string $url, HttpStatus $status = HttpStatus::FOUND): self
        {
            $this->res->redirect($url, $status->value);
            return $this;
        }

        /**
         * Send HTTP response redirect using a given HTTP referrer, if no referrer given
         * false is returned and redirection fails
         * @param HttpStatus $status HTTP status code, default is 302
         * @return bool
         */
        public function redirectBack(HttpStatus $status = HttpStatus::FOUND): bool
        {
            $url = $this->app->request()->headers('referer');
            if ($url !== null) {
                $this->res->redirect($url, $status->value);
                return true;
            }
            return false;
        }

        /**
         * Set HTTP response status code
         * @param HttpStatus $status HTTP status
         * @param string $message HTTP status message, if not provided then default
         * HTTP message will be used.
         * @return self
         */
        public function setStatus(HttpStatus $status, string $message = null): self
        {
            $this->httpStatus = $status;
            $this->res->setStatus($status->value, $message ?? $status->getMessage());
            return $this;
        }

        /**
         * Get HTTP response status
         * @return HttpStatus Status code
         */
        public function status(): HttpStatus
        {
            return $this->httpStatus;
        }

        /**
         * Stream a file to a client
         * @param string $path Path to a file to stream
         * @param int $start Start bytes to stream
         * @param int $end End bytes to stream
         * @return self
         */
        private function doStream(string $path, int $start = 0, int $end = null): self
        {
            $size = filesize($path);
            if ($size <= $start || ($end !== null && $start >= $end)) {
                return $this->setStatus(HttpStatus::BAD_REQUEST)->close();
            }
            $chunk = min($size, \shani\engine\core\Definitions::BUFFER_SIZE);
            $length = $size - $start;
            if ($end > 0) {
                $length = $chunk = $end - $start + 1;
            }
            if ($this->app->request()->method() !== 'head') {
                $this->sendHeaders([
                    'content-length' => $length,
                    'content-type' => \library\MediaType::fromFilename($path)
                ])->res->sendFile($path, $start, $chunk);
            } else {
                $this->setHeaders([
                    'content-length' => $length,
                    'content-type' => \library\MediaType::fromFilename($path)
                ])->setStatus(HttpStatus::NO_CONTENT)->close();
            }
            return $this;
        }

        /**
         * Set HTTP response cookie
         * @param \library\Cookie $cookie
         * @return self
         */
        public function setCookie(\library\Cookie $cookie): self
        {
            $this->cookies[$cookie->name()] = $cookie;
            return $this;
        }

        /**
         * The Link header is used to provide relationships between the current
         * document and other resources.
         * @param array $links Associative array where key is the link name and value is the actual link
         * @return self
         */
        public function setLink(array $links): self
        {
            $lnk = null;
            foreach ($links as $name => $link) {
                $lnk .= ',<' . $link . '>; rel="' . $name . '"';
            }
            $this->setHeaders('link', substr($lnk, 1));
            return $this;
        }

        /**
         * Stream  a file as HTTP response
         * @param string $filepath Path to a file to stream
         * @param int|null $chunkSize Number of bytes to stream every turn, default is 1MB
         * @return self
         */
        public function stream(string $filepath, ?int $chunkSize = null): self
        {
            if (!is_file($filepath)) {
                return $this->setStatus(HttpStatus::NOT_FOUND)->close();
            }
            $file = stat($filepath);
            $range = $this->app->request()->headers('range') ?? '=0-';
            $start = (int) substr($range, strpos($range, '=') + 1, strpos($range, '-'));
            $end = min($start + ($chunkSize ?? \shani\engine\core\Definitions::BUFFER_SIZE), $file['size'] - 1);
            $this->setHeaders([
                'content-range' => 'bytes ' . $start . '-' . $end . '/' . $file['size'],
                'accept-ranges' => 'bytes'
            ]);
            return $this->setStatus(HttpStatus::PARTIAL_CONTENT)->doStream($filepath, $start, $end);
        }

        /**
         * Set HTTP cache commands
         * @param array|null $options
         * @param bool $reuse whether to re-use cached content or not.
         * @return self
         */
        public function setCache(?array $options = null, bool $reuse = true): self
        {
            $directives = [];
            $cache = $options ?? [];
            if ($reuse) {
                $directives[] = empty($cache['public']) ? 'no-store' : 'no-cache';
            } else {
                if (!empty($cache['unique'])) {
                    $directives[] = 'private';
                }
                $age = '6m';
                $directives[] = 'max-age=' . (new \DateTime($cache['max_age'] ?? $age))->getTimestamp();
                if (!empty($cache['versioned'])) {
                    $directives[] = 'immutable';
                } else {
                    if (!empty($cache['revalidate'])) {
                        $directives[] = 'stale-while-revalidate=' . (new \DateTime($cache['stale'] ?? $age))->getTimestamp();
                    }
                    if (!empty($cache['etag'])) {
                        $this->setHeaders('etag', $cache['etag']);
                    }
                }
            }
            return $this->setHeaders('cache-control', implode(',', $directives));
        }
    }

}
