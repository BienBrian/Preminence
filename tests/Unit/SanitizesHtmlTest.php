<?php

namespace Tests\Unit;

use Tests\TestCase;

class SanitizesHtmlTest extends TestCase
{
    use \App\Traits\SanitizesHtml;

    public function test_purify_strips_script_tags(): void
    {
        $dirty = '<p>Hello</p><script>alert("xss")</script>';
        $clean = $this->purify($dirty);

        $this->assertStringNotContainsString('<script>', $clean);
        $this->assertStringContainsString('<p>Hello</p>', $clean);
    }

    public function test_purify_allows_basic_html(): void
    {
        $html = '<p>Hello <strong>World</strong></p>';
        $clean = $this->purify($html);

        $this->assertStringContainsString('<p>', $clean);
        $this->assertStringContainsString('<strong>', $clean);
    }

    public function test_purify_strips_onclick_attributes(): void
    {
        $dirty = '<p onclick="alert(1)">Click me</p>';
        $clean = $this->purify($dirty);

        $this->assertStringNotContainsString('onclick', $clean);
    }

    public function test_purify_allows_links_with_href(): void
    {
        $html = '<a href="https://example.com" title="Example">Link</a>';
        $clean = $this->purify($html);

        $this->assertStringContainsString('href="https://example.com"', $clean);
    }

    public function test_purify_strips_iframe_tags(): void
    {
        $dirty = '<iframe src="https://evil.com"></iframe><p>Safe</p>';
        $clean = $this->purify($dirty);

        $this->assertStringNotContainsString('<iframe', $clean);
        $this->assertStringContainsString('Safe', $clean);
    }

    public function test_purify_allows_lists(): void
    {
        $html = '<ul><li>Item 1</li><li>Item 2</li></ul>';
        $clean = $this->purify($html);

        $this->assertStringContainsString('<ul>', $clean);
        $this->assertStringContainsString('<li>', $clean);
    }

    public function test_purify_handles_empty_string(): void
    {
        $clean = $this->purify('');
        $this->assertEquals('', $clean);
    }

    public function test_purify_allows_images_with_src(): void
    {
        $html = '<img src="https://example.com/img.jpg" alt="Photo">';
        $clean = $this->purify($html);

        $this->assertStringContainsString('src="https://example.com/img.jpg"', $clean);
        $this->assertStringContainsString('alt="Photo"', $clean);
    }
}
