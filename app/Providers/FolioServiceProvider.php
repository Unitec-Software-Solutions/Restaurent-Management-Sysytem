<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Laravel\Folio\Folio;

class FolioServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        Folio::path(resource_path('views/pages'))->middleware([
            '*' => [
                //
            ],
        ]);
    }

    protected function getFolioRoutes()
    {
        // Check if Folio is available
        if (!class_exists('Laravel\Folio\FolioManager')) {
            return collect([]);
        }

        try {
            $output = new \Symfony\Component\Console\Output\BufferedOutput();

            \Illuminate\Support\Facades\Artisan::call("folio:list", ["--json" => true], $output);

            $mountPaths = collect(app('Laravel\Folio\FolioManager')->mountPaths());

            return collect(json_decode($output->fetch(), true))->map(fn($route) => $this->getFolioRoute($route, $mountPaths));
        } catch (\Exception | \Throwable $e) {
            return collect([]);
        }
    }

    /**
     * Transform a folio route and mount paths as needed.
     *
     * @param array $route
     * @param \Illuminate\Support\Collection $mountPaths
     * @return array
     */
    protected function getFolioRoute(array $route, $mountPaths)
    {
        // Example: just return the route as-is, or customize as needed
        return $route;
    }
}
