<?php

namespace Haminh7036\TelescopeExtras\Tests;

use GuzzleHttp\Psr7\Request as PsrRequest;
use GuzzleHttp\Psr7\Response as PsrResponse;
use Haminh7036\TelescopeExtras\Watchers\ClientRequestWatcher;
use Illuminate\Http\Client\Request;
use Illuminate\Http\Client\Response;
use Laravel\Telescope\Telescope;

class ClientRequestWatcherTest extends TestCase
{
    protected function tearDown(): void
    {
        Telescope::$hiddenResponseParameters = [];
        Telescope::$hiddenRequestParameters = [];

        parent::tearDown();
    }

    public function test_xml_response_is_parsed_into_array()
    {
        $xml = '<?xml version="1.0" encoding="UTF-8"?><response><message>success</message><status>200</status></response>';
        $response = new Response(new PsrResponse(200, ['Content-Type' => 'application/xml'], $xml));

        $watcher = new TestableClientRequestWatcher;

        $this->assertSame([
            'message' => 'success',
            'status' => '200',
        ], $watcher->formatResponse($response));
    }

    public function test_xml_request_payload_is_parsed_into_array()
    {
        $xml = '<?xml version="1.0"?><request><user>taylor</user><action>login</action></request>';
        $request = new Request(new PsrRequest('POST', 'https://example.com/api', ['Content-Type' => 'application/xml'], $xml));

        $watcher = new TestableClientRequestWatcher;

        $this->assertSame([
            'user' => 'taylor',
            'action' => 'login',
        ], $watcher->formatInput($request));
    }

    public function test_large_xml_response_is_purged()
    {
        $xml = '<response><data>'.str_repeat('x', 70000).'</data></response>';
        $response = new Response(new PsrResponse(200, ['Content-Type' => 'application/xml'], $xml));

        $watcher = new TestableClientRequestWatcher;

        $this->assertSame('Purged By Telescope', $watcher->formatResponse($response));
    }

    public function test_hidden_parameters_are_masked_in_xml_response()
    {
        Telescope::hideResponseParameters(['token', 'password']);

        $xml = '<response><token>super-secret</token><status>ok</status></response>';
        $response = new Response(new PsrResponse(200, ['Content-Type' => 'application/xml'], $xml));

        $watcher = new TestableClientRequestWatcher;

        $this->assertSame([
            'token' => '********',
            'status' => 'ok',
        ], $watcher->formatResponse($response));
    }

    public function test_json_and_html_responses_still_handled_correctly()
    {
        $watcher = new TestableClientRequestWatcher;

        $json = new Response(new PsrResponse(200, ['Content-Type' => 'application/json'], '{"foo":"bar"}'));
        $this->assertSame(['foo' => 'bar'], $watcher->formatResponse($json));

        $html = new Response(new PsrResponse(200, ['Content-Type' => 'text/html'], '<!DOCTYPE html><html><body>hello<br>world</body></html>'));
        $this->assertSame('HTML Response', $watcher->formatResponse($html));
    }
}

class TestableClientRequestWatcher extends ClientRequestWatcher
{
    public function formatResponse(Response $response)
    {
        return $this->response($response);
    }

    public function formatInput(Request $request)
    {
        return $this->input($request);
    }
}
