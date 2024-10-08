<?php

/**
 * Description of Response
 * @author coder
 *
 * Created on: Feb 11, 2024 at 6:50:02 PM
 */

namespace shani\engine\http {

    use library\HttpStatus;
    use shani\contracts\ServerResponse;

    final class Response
    {

        private App $app;
        private int $statusCode;
        private ?string $datatype = null;
        private array $headers, $cookies;
        private ServerResponse $res;
        private bool $buffer = false;

        public function __construct(App &$app, ServerResponse &$res)
        {
            $this->statusCode = HttpStatus::OK;
            $this->cookies = [];
            $this->res = $res;
            $this->app = $app;
            $this->headers = ['x-content-type-options' => 'nosniff'];
        }

        /**
         * Set output buffer on so that output can be sent in chunks without closing connection.
         * @param bool $use Buffer value. If true, then the buffer will be on,
         * If false, then connection will be closed and no output can be sent.
         * @return self
         */
        public function useBuffer(bool $use = true): self
        {
            $this->buffer = $use;
            if (!$use) {
                $this->res->close();
            }
            return $this;
        }

        private function write(?string $content = null): self
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

        private function finish(string $content): self
        {
            if ($this->app->request()->method() !== 'head') {
                if (!$this->buffer) {
                    return $this->setHeaders('content-length', mb_strlen($content))->close($content);
                }
                $this->sendHeaders()->res->write($content);
                return $this;
            }
            $this->setStatus(HttpStatus::NO_CONTENT);
            return $this->setHeaders('content-length', mb_strlen($content))->close();
        }

        /**
         * Get HTTP response data type
         * @return string|null HTTP response data type
         */
        public function type(): ?string
        {
            if ($this->datatype === null) {
                if (!empty($this->headers['content-type'])) {
                    $this->datatype = \library\Mime::explode($this->headers['content-type'])[1] ?? null;
                } else {
                    $path = $this->app->request()->uri()->path();
                    $parts = explode('.', $path);
                    $size = count($parts);
                    if ($size > 1) {
                        $this->datatype = strtolower($parts[$size - 1]);
                    } else {
                        $this->datatype = \library\Mime::explode($this->app->request()->headers('accept'))[1] ?? null;
                    }
                }
            }
            return $this->datatype;
        }

        /**
         * Filter HTTP response body before sending using user preferences
         * @param array $data Data to filter
         * @param array|null $availableColumns Allowed columns to be send to user response.
         * Use this parameter to filter out columns you don't want to send to user.
         * @param array|null $filters User filters supplied via HTTP query string.
         * The values of query string must match data columns and values MUST be present
         * @return self
         */
        public function sendFilter(array $data, ?array $availableColumns = null, ?array $filters = null): self
        {
            $values = \library\Map::filter($data, $filters ?? $this->app->request()->query());
            if (empty($availableColumns)) {
                return $this->send($values);
            }
            $userColumns = $this->app->request()->columns($availableColumns);
            return $this->send(\library\Map::getAll($values, $userColumns));
        }

        /**
         * Send HTTP response body, leaving connection open. Ideal when wanting
         * to send data in chunks. Remember to close the connection when done.
         * @param type $data Data to send as response body.
         * @return self
         * @see self::close()
         */
        public function send($data = null): self
        {
            $type = $this->type();
            if ($type !== null) {
                switch ($type) {
                    case'json':
                        return $this->sendAsJson($data);
                    case'xml':
                        return $this->sendAsXml($data);
                    case'csv':
                        return $this->sendAsCsv($data);
                    case'yaml':
                    case'yml':
                        return $this->sendAsYaml($data);
                    case'html':
                    case'htm':
                        return $this->sendAsHtml($data);
                    case'raw':
                        return $this->plainText($data, 'application/octet-stream');
                    case'sse':
                    case'event-stream':
                        return $this->sendAsSse($data);
                    case'js':
                    case'jsonp':
                        return $this->sendAsJsonp($data, $this->app->request()->query('callback') ?? 'callback');
                }
            }
            return $this->plainText($data, 'text/plain; charset=utf-8');
        }

        private function plainText($data, string $type): self
        {
            $this->setHeaders('content-type', $type);
            if (is_array($data)) {
                return $this->write(serialize($data));
            }
            return $this->write($data);
        }

        /**
         * Send HTTP response body as HTML
         * @param type $data Data to send
         * @return self
         */
        public function sendAsHtml($data): self
        {
            return $this->plainText($data, 'text/html; charset=utf-8');
        }

        /**
         * Send HTTP response body as Server sent event
         * @param type $data Data to send
         * @return self
         */
        public function sendAsSse($data, string $event = 'message'): self
        {
            $this->setHeaders('cache-control', 'no-cache');
            $evt = 'id:id' . hrtime(true) . PHP_EOL;
            $evt .= 'event:' . $event . PHP_EOL;
            $evt .= 'data:' . (is_array($data) ? serialize($data) : $data);
            $evt .= PHP_EOL . PHP_EOL;
            return $this->plainText($evt, 'text/event-stream', null);
        }

        /**
         * Send HTTP response body as JSON
         * @param type $data Data to send
         * @return self
         */
        public function sendAsJson($data): self
        {
            return $this->plainText($data !== null ? json_encode($data) : null, 'application/json; charset=utf-8');
        }

        /**
         * Send HTTP response body as JSON with padding
         * @param type $data Data to send
         * @param string $callback JavaScript callback function
         * @return self
         */
        public function sendAsJsonp($data, string $callback): self
        {
            $this->setHeaders('content-type', 'application/javascript; charset=utf-8');
            $type = gettype($data);
            if ($type === 'array') {
                return $this->write($callback . '(' . json_encode($data) . ');');
            }
            if ($type === 'string') {
                return $this->write($callback . '("' . $data . '");');
            }
            if ($type === 'boolean') {
                return $this->write($callback . '(' . ($data ? 'true' : 'false') . ');');
            }
            if ($type === 'NULL') {
                return $this->write($callback . '(null);');
            }
            if ($type === 'double' || $type === 'integer') {
                return $this->write($callback . '(' . $data . ');');
            }
            return $this->write($callback . '();');
        }

        /**
         * Send HTTP response body as XML
         * @param type $data Data to send
         * @return self
         */
        public function sendAsXml($data): self
        {
            return $this->plainText(\library\DataConvertor::array2xml($data), 'application/xml; charset=utf-8');
        }

        /**
         * Send HTTP response body as CSV
         * @param type $data Data to send
         * @param string $separator data separator
         * @return self
         */
        public function sendAsCsv($data, string $separator = ','): self
        {
            return $this->plainText(\library\DataConvertor::array2csv($data, $separator), 'text/csv; charset=utf-8');
        }

        /**
         * Send HTTP response body as YAML
         * @param type $data Data to send
         * @return self
         */
        public function sendAsYaml($data): self
        {
//            return $this->plainText(\library\DataConvertor::array2yaml($data), 'text/yaml');
            return $this->plainText(yaml_emit($data), 'text/yaml; charset=utf-8');
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
         * @return \library\HttpCookie|null
         */
        public function cookie(string $name): ?\library\HttpCookie
        {
            return $this->cookies[$name] ?? null;
        }

        /**
         * Send HTTP response redirect
         * @param string $url new destination
         * @param int $code HTTP status code, default is 302
         * @return self
         */
        public function redirect(string $url, int $code = HttpStatus::FOUND): self
        {
            $this->res->redirect($url, $code);
            return $this;
        }

        /**
         * Send HTTP response redirect using a given HTTP referrer, if no referrer given
         * false is returned and redirection fails
         * @param int $code HTTP status code, default is 302
         * @return bool
         */
        public function redirectBack(int $code = HttpStatus::FOUND): bool
        {
            $url = $this->app->request()->headers('referer');
            if ($url !== null) {
                $this->res->redirect($url, $code);
                return true;
            }
            return false;
        }

        /**
         * Set HTTP response status code
         * @param int $code HTTP status code
         * @param string $message HTTP status message
         * @return self
         */
        public function setStatus(int $code, string $message = ''): self
        {
            $this->statusCode = $code;
            $this->res->setStatus($code, $message);
            return $this;
        }

        /**
         * Get HTTP response status code
         * @return int Status code
         */
        public function statusCode(): int
        {
            return $this->statusCode;
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
                    'content-type' => \library\Mime::fromFilename($path)
                ])->res->sendFile($path, $start, $chunk);
            } else {
                $this->setHeaders([
                    'content-length' => $length,
                    'content-type' => \library\Mime::fromFilename($path)
                ])->setStatus(HttpStatus::NO_CONTENT)->close();
            }
            return $this;
        }

        /**
         * Set HTTP response cookie
         * @param \library\HttpCookie $cookie
         * @return self
         */
        public function setCookie(\library\HttpCookie $cookie): self
        {
            $this->cookies[$cookie->name()] = $cookie;
            return $this;
        }

        public function setLink($links, $name = null): self
        {
            if (is_array($links)) {
                $lnk = [];
                foreach ($links as $key => $value) {
                    $lnk[] = '<' . $value . '>; rel="' . $key . '"';
                }
                $this->setHeaders('link', implode(',', $lnk));
            } else {
                $this->setHeaders('link', '<' . $name . '>; rel="' . $links . '"');
            }
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
            ])->setStatus(HttpStatus::PARTIAL_CONTENT);
            return $this->doStream($filepath, $start, $end);
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

        /**
         * Send output and close connection
         * @param string|null $content Output to send
         * @return self
         */
        private function close(?string $content = null): self
        {
            $this->sendHeaders()->res->close($content);
            return $this;
        }
    }

}
