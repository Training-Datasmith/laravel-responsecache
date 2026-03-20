<?php

declare (strict_types=1);
namespace Spatie\Response_Cache\Replacers;

use Symfony\Component\Http_Foundation\Response;
class Csrf_Token_Replacer implements Replacer
{
    protected string $replacement_string = '<laravel-responsecache-csrf-token-here>';
    public function prepare_response_to_cache(Response $response): void
    {
        $content = $response->get_content();
        if (!$content) {
            return;
        }
        $csrf_token = csrf_token();
        if (!$csrf_token) {
            return;
        }
        if (!str_contains($content, $csrf_token)) {
            return;
        }
        $response->set_content(str_replace($csrf_token, $this->replacement_string, $content));
    }
    public function replace_in_cached_response(Response $response): void
    {
        $content = $response->get_content();
        if (!$content || !str_contains($content, $this->replacement_string)) {
            return;
        }
        $csrf_token = csrf_token();
        if (!$csrf_token) {
            return;
        }
        $response->set_content(str_replace($this->replacement_string, $csrf_token, $content));
    }
}