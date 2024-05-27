<?php

/**
 * Description of Response
 * @author coder
 *
 * Created on: Feb 11, 2024 at 6:50:02 PM
 */

namespace shani\engine\http {

    use library\HttpStatus;

    final class Response
    {

        private Request $req;
        private int $statusCode;
        private ?string $type = null;
        private array $headers, $cookies;
        private \shani\adaptor\Response $res;

        private const CHUNK_SIZE = 1048576; //1MB

        public function __construct(Request &$req, \shani\adaptor\Response &$res)
        {
            $this->statusCode = HttpStatus::OK;
            $this->cookies = [];
            $this->req = $req;
            $this->res = $res;
            $this->headers = [
                'x-frame-options' => 'sameorigin',
                'x-content-type-options' => 'nosniff'
            ];
        }

        private function write(?string $content): self
        {
            $this->sendHeaders(['content-length' => $content !== null ? mb_strlen($content) : 0]);
            $this->res->write($this->req->method() !== 'head' ? $content : null);
            return $this;
        }

        public function type(): ?string
        {
            if (!$this->type) {
                if (!empty($this->headers['content-type'])) {
                    $this->type = \library\Mime::explode($this->headers['content-type'])[1] ?? null;
                } else {
                    $path = $this->req->uri()->path();
                    $parts = explode('.', $path);
                    $size = count($parts);
                    if ($size > 1) {
                        $this->type = strtolower($parts[$size - 1]);
                    } else {
                        $this->type = \library\Mime::explode($this->req->headers('accept'))[1] ?? null;
                    }
                }
            }
            return $this->type;
        }

        public function send($data = null, string $encoding = null): self
        {
            if ($data === null || $data === '') {
                return $this->write(null);
            }
            $type = $this->type();
            if ($type !== null) {
                switch ($type) {
                    case'json':
                        return $this->sendJson($data, $encoding);
                    case'xml':
                        return $this->sendXml($data, $encoding);
                    case'csv':
                        return $this->sendCsv($data, $encoding);
                    case'yaml':
                    case'yml':
                        return $this->sendYaml($data, $encoding);
                    case'html':
                        return $this->sendHtml($data, $encoding);
                    case'raw':
                        return $this->plainText($data, 'application/octet-stream', '');
                    case'sse':
                    case'event-stream':
                        return $this->sendSse($data);
                    case'js':
                    case'jsonp':
                        return $this->sendJsonp($data, $this->req->query('callback') ?? 'callback', $encoding);
                }
            }
            return $this->plainText($data, 'text/plain', $encoding);
        }

        private function plainText($data, string $type, ?string $encoding): self
        {
            $this->setHeaders('content-type', $type . ($encoding ??= '; charset=utf-8'));
            if (is_array($data)) {
                return $this->write(serialize($data));
            }
            return $this->write($data);
        }

        public function sendHtml($data, string $encoding = null): self
        {
            return $this->plainText($data, 'text/html', $encoding);
        }

        public function sendSse($data, string $event = 'message'): self
        {
            $this->setHeaders('cache-control', 'no-cache');
            $evt = 'id:idn' . hrtime(true) . PHP_EOL;
            $evt .= 'event:' . $event . PHP_EOL;
            $evt .= 'data:' . $data . PHP_EOL . PHP_EOL;
            return $this->plainText($evt, 'text/event-stream', null);
        }

        public function sendJson($data, string $encoding = null): self
        {
            return $this->plainText(json_encode($data), 'application/json', $encoding);
        }

        public function sendJsonp($data, string $callback, string $encoding = null): self
        {
            $this->setHeaders('content-type', 'application/javascript; charset=' . ($encoding ?? 'utf-8'));
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

        public function sendXml($data, string $encoding = null): self
        {
            return $this->plainText(\library\DataConvertor::array2xml($data), 'application/xml', $encoding);
        }

        public function sendCsv($data, string $encoding = null, string $separator = ','): self
        {
            return $this->plainText(\library\DataConvertor::array2csv($data, $separator), 'text/csv', $encoding);
        }

        public function sendYaml($data, string $encoding = null): self
        {
//            return $this->plainText(\library\DataConvertor::php2yaml($data), 'text/yaml', $encoding);
            return $this->plainText(yaml_emit($data), 'text/yaml', $encoding);
        }

        public function saveAs(string $filename = null): self
        {
            $name = $filename ? '; filename="' . $filename . '"' : '';
            return $this->setHeaders('content-disposition', 'attachment' . $name);
        }

        public function sendHeaders(?array $headers = null): self
        {
            if (!$this->res->ended()) {
                if ($headers !== null) {
                    $this->setHeaders($headers);
                }
                if (count($this->cookies) > 0) {
                    $this->setHeaders('set-cookie', implode(',', $this->cookies));
                }
                $this->setHeaders('server', \shani\engine\core\Framework::NAME);
                $this->res->sendHeaders($this->headers);
            }
            return $this;
        }

        public function setHeaders($headers, $val = null): self
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

        public function headers($names = null, bool $selected = true)
        {
            return \library\Map::get($this->headers, $names, $selected);
        }

        public function cookie(string $name): ?\library\HttpCookie
        {
            return $this->cookies[$name] ?? null;
        }

        public function redirect(string $url, int $code = HttpStatus::FOUND): self
        {
            $this->res->redirect($url, $code);
            return $this;
        }

        public function redirectBack(int $code = HttpStatus::FOUND): bool
        {
            $url = $this->req->headers('referer');
            if ($url !== null) {
                $this->res->redirect($url, $code);
                return true;
            }
            return false;
        }

        public function setStatus(int $code, string $message = ''): self
        {
            $this->statusCode = $code;
            $this->res->setStatus($code, $message);
            return $this;
        }

        public function statusCode(): int
        {
            return $this->statusCode;
        }

        private function doStream(string $path, int $start = 0, int $end = null): self
        {
            $size = filesize($path);
            if ($size <= $start || ($end !== null && $start >= $end)) {
                return $this->setStatus(HttpStatus::BAD_REQUEST)->sendHeaders();
            }
            $chunk = min($size, self::CHUNK_SIZE);
            $len = $size - $start;
            if ($end > 0) {
                $len = $chunk = $end - $start + 1;
            }
            $this->sendHeaders([
                'content-length' => $len,
                'content-type' => \library\Mime::fromFilename($path)
            ]);

            if ($this->req->method() !== 'head') {
                $this->res->sendFile($path, $start, $chunk);
            } else {
                $this->res->write();
            }
            return $this;
        }

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

        public function stream(string $filepath, int $chunkSize = null): self
        {
            if (!is_readable($filepath)) {
                return $this->setStatus(HttpStatus::NOT_FOUND)->sendHeaders();
            }
            $file = stat($filepath);
            $range = $this->req->headers('range') ?? '=0-';
            $start = (int) substr($range, strpos($range, '=') + 1, strpos($range, '-'));
            $end = min($start + ($chunkSize ?? self::CHUNK_SIZE), $file['size'] - 1);
            return $this->setHeaders([
                        'content-range' => 'bytes ' . $start . '-' . $end . '/' . $file['size'],
                        'accept-ranges' => 'bytes'
                    ])->setStatus(HttpStatus::PARTIAL_CONTENT)->doStream($filepath, $start, $end);
        }

        public function sendFile(string $path): self
        {
            if (!is_readable($path)) {
                return $this->setStatus(HttpStatus::NOT_FOUND)->sendHeaders();
            }
            $range = $this->req->headers('range');
            $start = $range ? (int) substr($range, strpos($range, '=') + 1, strpos($range, '-')) : 0;
            return $this->setHeaders(['etag' => basename($path)])->doStream($path, $start);
        }

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
