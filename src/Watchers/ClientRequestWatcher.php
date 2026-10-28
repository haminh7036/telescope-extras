<?php

namespace Haminh7036\TelescopeExtras\Watchers;

use Illuminate\Http\Client\Request;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Str;
use Laravel\Telescope\Telescope;
use Laravel\Telescope\Watchers\ClientRequestWatcher as BaseClientRequestWatcher;

class ClientRequestWatcher extends BaseClientRequestWatcher
{
    /**
     * Format the given response object.
     *
     * @see https://github.com/laravel/telescope/pull/1758
     *
     * @param  \Illuminate\Http\Client\Response  $response
     * @return array|string
     */
    protected function response(Response $response)
    {
        $stream = $response->toPsrResponse()->getBody();

        if (! $stream->isSeekable()) {
            return 'Stream Response';
        }

        $content = $response->body();

        $stream->rewind();

        if (is_string($content)) {
            if (is_array(json_decode($content, true)) &&
                json_last_error() === JSON_ERROR_NONE) {
                return $this->contentWithinLimits($content)
                    ? $this->hideParameters(json_decode($content, true), Telescope::$hiddenResponseParameters)
                    : 'Purged By Telescope';
            }

            if ($xml = $this->parseXml($content)) {
                return $this->contentWithinLimits($content)
                    ? $this->hideParameters($xml, Telescope::$hiddenResponseParameters)
                    : 'Purged By Telescope';
            }

            if (Str::startsWith(strtolower($response->header('Content-Type') ?? ''), 'text/plain')) {
                return $this->contentWithinLimits($content) ? $content : 'Purged By Telescope';
            }
        }

        if ($response->redirect()) {
            return 'Redirected to '.$response->header('Location');
        }

        if (empty($content)) {
            return 'Empty Response';
        }

        return 'HTML Response';
    }

    /**
     * Extract the input from the given request.
     *
     * @param  \Illuminate\Http\Client\Request  $request
     * @return array
     */
    protected function input(Request $request)
    {
        if (! $request->isMultipart()) {
            if ($xml = $this->parseXml($request->body())) {
                return $xml;
            }

            return $request->data();
        }

        return parent::input($request);
    }

    /**
     * Parse XML content into an array.
     *
     * @param  string|null  $content
     * @return array|null
     */
    protected function parseXml($content)
    {
        if (! is_string($content) || trim($content) === '') {
            return null;
        }

        $previous = libxml_use_internal_errors(true);

        try {
            $xml = simplexml_load_string($content, 'SimpleXMLElement', LIBXML_NOCDATA);

            if ($xml === false) {
                return null;
            }

            $array = json_decode(json_encode($xml), true);

            return is_array($array) ? $array : null;
        } finally {
            libxml_clear_errors();
            libxml_use_internal_errors($previous);
        }
    }
}
