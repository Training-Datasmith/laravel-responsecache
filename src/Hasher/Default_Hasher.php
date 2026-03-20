<?php

declare (strict_types=1);
namespace Spatie\Response_Cache\Hasher;

use Illuminate\Http\Request;
use Spatie\Response_Cache\Cache_Profiles\Cache_Profile;
class Default_Hasher implements Request_Hasher
{
    public function __construct(protected Cache_Profile $cache_profile)
    {
    }
    public function get_hash_for(Request $request): string
    {
        $strings = ['responsecache', $request->get_host(), $this->get_normalized_request_uri($request), $request->get_method(), $this->get_cache_name_suffix($request)];
        return hash('xxh128', implode('-', $strings));
    }
    protected function get_normalized_request_uri(Request $request): string
    {
        $query_string = $this->get_normalized_query_string($request);
        if ($query_string !== '') {
            $query_string = '?' . $query_string;
        }
        return $request->get_base_url() . $request->get_path_info() . $query_string;
    }
    protected function get_normalized_query_string(Request $request): string
    {
        $query_string = $request->get_query_string();
        if ($query_string === null || $query_string === '') {
            return '';
        }
        $ignored_parameters = config('responsecache.ignored_query_parameters', []);
        if (empty($ignored_parameters)) {
            return $query_string;
        }
        parse_str($query_string, $parameters);
        $parameters = array_diff_key($parameters, array_flip($ignored_parameters));
        if (empty($parameters)) {
            return '';
        }
        return http_build_query($parameters);
    }
    protected function get_cache_name_suffix(Request $request): string
    {
        if ($request->attributes->has('responsecache.cacheNameSuffix')) {
            return $request->attributes->get('responsecache.cacheNameSuffix');
        }
        return $this->cache_profile->use_cache_name_suffix($request);
    }
}