<?php

namespace App\Providers;

use App\Models\Invoice;
use App\Models\InvoiceItem;
use App\Models\Lead;
use App\Models\Post;
use App\Models\Product;
use App\Models\Quote;
use App\Models\QuoteItem;
use App\Policies\InvoiceItemPolicy;
use App\Policies\InvoicePolicy;
use App\Policies\LeadPolicy;
use App\Policies\PostPolicy;
use App\Policies\ProductPolicy;
use App\Policies\QuoteItemPolicy;
use App\Policies\QuotePolicy;
use Carbon\CarbonImmutable;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Date;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\ServiceProvider;
use Illuminate\Validation\Rules\Password;

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
        $this->configureDefaults();
        $this->registerPolicies();
        $this->configureRateLimiters();

        \App\Models\InvoiceItem::observe(\App\Observers\InvoiceItemObserver::class);
        \App\Models\QuoteItem::observe(\App\Observers\QuoteItemObserver::class);
        \App\Models\Media::observe(\App\Observers\MediaObserver::class);
    }

    /**
     * Explicitly register all Eloquent model policies.
     *
     * Do NOT rely solely on Laravel's naming-convention auto-discovery.
     * Explicit registration is the contract that guarantees authorization
     * works even if a model or policy is ever renamed or moved.
     */
    protected function registerPolicies(): void
    {
        Gate::policy(Lead::class,        LeadPolicy::class);
        Gate::policy(Quote::class,       QuotePolicy::class);
        Gate::policy(QuoteItem::class,   QuoteItemPolicy::class);
        Gate::policy(Invoice::class,     InvoicePolicy::class);
        Gate::policy(InvoiceItem::class, InvoiceItemPolicy::class);
        Gate::policy(Product::class,     ProductPolicy::class);
        Gate::policy(Post::class,        PostPolicy::class);
    }

    /**
     * Register named rate limiters for public-facing API surfaces.
     *
     * Using named limiters (instead of the generic throttle:N,1 middleware)
     * provides: proper Retry-After headers, granular observability, and
     * the ability to tweak limits without touching route definitions.
     */
    protected function configureRateLimiters(): void
    {
        // Stripe only sends 1 webhook event at a time per endpoint.
        // 30 req/min per IP is generous for legitimate Stripe traffic
        // but will instantly block any bot or replay-attack flood.
        RateLimiter::for('stripe-webhooks', function (Request $request) {
            return Limit::perMinute(30)->by($request->ip())->response(function () {
                return response()->json(
                    ['error' => 'Too many webhook requests. Slow down.'],
                    429
                );
            });
        });

        // The public invoice payment portal should not be hammerable.
        // 10 attempts per minute per IP prevents ULID enumeration attacks.
        RateLimiter::for('invoice-portal', function (Request $request) {
            return Limit::perMinute(10)->by($request->ip());
        });
    }

    /**
     * Configure default behaviors for production-ready applications.
     */
    protected function configureDefaults(): void
    {
        Date::use(CarbonImmutable::class);

        DB::prohibitDestructiveCommands(
            app()->isProduction(),
        );

        Password::defaults(fn (): ?Password => app()->isProduction()
            ? Password::min(12)
                ->mixedCase()
                ->letters()
                ->numbers()
                ->symbols()
                ->uncompromised()
            : null,
        );
    }
}

