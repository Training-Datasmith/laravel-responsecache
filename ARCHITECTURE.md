# Architecture: laravel-responsecache

## Purpose
A Laravel middleware package that caches full HTTP responses and serves them from cache on subsequent requests, dramatically reducing database and computation load for cacheable routes.

## Directory Structure
```
src/
  Response_Cache.php                      # Facade-backed service — stores, retrieves, forgets cached responses
  Response_Cache_Repository.php           # Wraps Laravel's cache store with serialization/deserialization
  Middlewares/
    Cache_Response.php                    # Core middleware — checks cache, returns hit or caches miss
    Do_Not_Cache_Response.php             # Middleware to bypass caching for specific routes
    Flexible_Cache_Response.php           # Stale-while-revalidate caching variant
  CacheProfiles/
    Cache_Profile.php                     # Interface — shouldCacheRequest, shouldCacheResponse, cacheNameSuffix
    Base_Cache_Profile.php                # Base with defaults (caches GET 200s, excludes authenticated users)
    Cache_All_Successful_Get_Requests.php # Concrete profile
  Hasher/
    Request_Hasher.php                    # Interface — hash a request to a cache key
    Default_Hasher.php                    # MD5 of full URL + optional query string params
  Serializers/
    Serializer.php                        # Interface — serialize/unserialize Response objects
    Json_Serializer.php                   # Serializes Symfony/Laravel Response as JSON
  Replacers/
    Replacer.php                          # Interface — post-processing on cached response content
    Csrf_Token_Replacer.php               # Replaces placeholder with fresh CSRF token on cache hit
  Attributes/
    Cache.php / No_Cache.php / Flexible_Cache.php  # PHP 8 attributes for per-route cache control
  Events/                                 # Cache hit, miss, clear events
  Enums/Http_Method.php / Response_Type.php
  Commands/Clear_Command.php              # Artisan: response-cache:clear
  Configuration/                          # Typed config value objects
  CacheItemSelector/                      # Fluent builder for targeted cache invalidation
  Response_Cache_Service_Provider.php
```

## Key Design Decisions
- **Middleware-first** — caching is transparent to the application; routes need no modification other than applying the middleware.
- **Profile strategy** — `Cache_Profile` is injected and controls which requests/responses to cache, allowing per-app customisation (e.g., only cache for guests, only cache 200s).
- **Replacers** — post-cache content substitution (e.g., CSRF token injection) allows caching of pages that contain per-user elements, as long as those elements can be replaced server-side.
- **PHP attributes** — `#[Cache]`, `#[NoCache]`, and `#[FlexibleCache]` attributes on controller actions provide per-action cache control without route-level middleware configuration.
- **Flexible cache (SWR)** — `Flexible_Cache_Response` implements stale-while-revalidate: serve the cached response immediately while triggering an async background regeneration.

## Extension Points
- Implement `Cache_Profile` to control cache eligibility based on request/response properties.
- Implement `Request_Hasher` to change how cache keys are computed (e.g., include auth state).
- Implement `Replacer` to perform custom content substitution on cache hits.
- Use `CacheItemSelector` for targeted cache invalidation by URL or tag.

## Dependency Flow
```
HTTP Request
  └─ Cache_Response middleware
       ├─ Cache_Profile::shouldCacheRequest() → skip if false
       ├─ Default_Hasher::getHashFor($request) → cache key
       ├─ Cache HIT → Response_Cache_Repository::getCachedResponseFor()
       │    └─ Replacer[] (e.g., CSRF token) → return cached response
       └─ Cache MISS → forward to application → Response
            └─ Cache_Profile::shouldCacheResponse() → store if true
                 └─ Serializer::serialize(Response) → cache store
```
