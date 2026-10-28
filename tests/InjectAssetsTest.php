<?php

namespace Haminh7036\TelescopeExtras\Tests;

class InjectAssetsTest extends TestCase
{
    protected function defineRoutes($router)
    {
        // Stands in for Telescope's mail preview: HTML, same middleware group, other route.
        $router->get('/telescope-extras-other', fn () => '<html><body>mail</body></html>')
            ->middleware('telescope');
    }

    public function test_dashboard_page_gets_the_assets_before_closing_body()
    {
        $content = $this->get('/telescope/requests')->assertOk()->getContent();

        $this->assertStringContainsString('telescope-extras-query-filter', $content);
        $this->assertStringContainsString('telescope-extras-duplicate-query', $content);
        $this->assertLessThan(strripos($content, '</body>'), strpos($content, 'telescope-extras-query-filter'));
    }

    public function test_other_html_responses_in_the_group_are_untouched()
    {
        $this->get('/telescope-extras-other')->assertOk()->assertContent('<html><body>mail</body></html>');
    }
}
