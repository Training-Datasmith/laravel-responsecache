<?php

declare (strict_types=1);
namespace Spatie\Response_Cache\Commands;

use Illuminate\Console\Command;
use Spatie\Response_Cache\Facades\Response_Cache;
class Clear_Command extends Command
{
    protected $signature = 'responsecache:clear {--url=}';
    protected $description = 'Clear the response cache';
    public function handle(): void
    {
        $this->clear();
        $this->info('Response cache cleared!');
    }
    protected function clear()
    {
        if ($url = $this->option('url')) {
            return (new Response_Cache())->forget($url);
        }
        (new Response_Cache())->clear();
    }
}