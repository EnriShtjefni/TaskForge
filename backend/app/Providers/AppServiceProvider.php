<?php

namespace App\Providers;

use App\Models\Project;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Broadcast;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        RateLimiter::for('api', function (Request $request) {
            return Limit::perMinute(60)->by(
                $request->user()?->id ?: $request->ip()
            );
        });

        Broadcast::channel('project.{projectId}', function ($user, $projectId) {
            return Project::find($projectId)?->users()->where('users.id', $user->id)->exists();
        });

        Broadcast::routes(['middleware' => ['web', 'auth:sanctum']]);
    }
}
