<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Auth;

class CheckNavPermission
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = Auth::user();
        if (!$user) {
            return $next($request);
        }

        $navKey = $this->getNavKeyForRequest($request);

        if ($navKey && !$user->canAccessNav($navKey)) {
            if ($request->wantsJson() || $request->expectsJson() || $request->is('api/*')) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthorized access to this section.'
                ], 403);
            }

            return redirect()->to($user->getFirstAvailableNavUrl());
        }

        return $next($request);
    }

    private function getNavKeyForRequest(Request $request): ?string
    {
        $routeName = $request->route() ? $request->route()->getName() : null;
        $path = trim($request->path(), '/');

        if ($routeName) {
            if ($routeName === 'dashboard' || str_starts_with($routeName, 'dashboard.')) {
                return 'dashboard';
            }
            if ($routeName === 'billing' || str_starts_with($routeName, 'billing.')) {
                return 'billing';
            }
            if ($routeName === 'invoices.store') {
                return 'billing';
            }
            if ($routeName === 'invoices.index' || str_starts_with($routeName, 'invoices.')) {
                return 'invoices';
            }
            if (str_starts_with($routeName, 'delivery-sheets.')) {
                return 'delivery-sheets';
            }
            if (str_starts_with($routeName, 'payments.')) {
                return 'payments';
            }
            if (str_starts_with($routeName, 'customers.') || str_starts_with($routeName, 'api.customers.')) {
                return 'customers';
            }
            if (str_starts_with($routeName, 'products.') || str_starts_with($routeName, 'api.products.')) {
                return 'products';
            }
            if (str_starts_with($routeName, 'product-sales.')) {
                return 'product-sales';
            }
            if (str_starts_with($routeName, 'stock-register.')) {
                return 'stock-register';
            }
            if (str_starts_with($routeName, 'looms.')) {
                return 'looms';
            }
            if (str_starts_with($routeName, 'workers.') || str_starts_with($routeName, 'api.workers.')) {
                return 'workers';
            }
            if (str_starts_with($routeName, 'borrows.')) {
                return 'borrows';
            }
            if (str_starts_with($routeName, 'settings.') || str_starts_with($routeName, 'team-user.')) {
                return 'settings';
            }
        }

        if ($path === '' || $path === 'dashboard' || str_starts_with($path, 'api/dashboard')) {
            return 'dashboard';
        }
        if (str_starts_with($path, 'billing') || str_starts_with($path, 'api/billing')) {
            return 'billing';
        }
        if (str_starts_with($path, 'invoices') || str_starts_with($path, 'api/invoices')) {
            return 'invoices';
        }
        if (str_starts_with($path, 'delivery-sheets') || str_starts_with($path, 'api/delivery-sheets')) {
            return 'delivery-sheets';
        }
        if (str_starts_with($path, 'payments') || str_starts_with($path, 'api/payments')) {
            return 'payments';
        }
        if (str_starts_with($path, 'customers') || str_starts_with($path, 'api/customers')) {
            return 'customers';
        }
        if (str_starts_with($path, 'products') || str_starts_with($path, 'api/products')) {
            return 'products';
        }
        if (str_starts_with($path, 'product-sales')) {
            return 'product-sales';
        }
        if (str_starts_with($path, 'stock-register') || str_starts_with($path, 'api/stock-register')) {
            return 'stock-register';
        }
        if (str_starts_with($path, 'looms') || str_starts_with($path, 'api/looms')) {
            return 'looms';
        }
        if (str_starts_with($path, 'workers') || str_starts_with($path, 'api/workers')) {
            return 'workers';
        }
        if (str_starts_with($path, 'borrows') || str_starts_with($path, 'api/borrows')) {
            return 'borrows';
        }
        if (str_starts_with($path, 'settings') || str_starts_with($path, 'api/settings') || str_starts_with($path, 'api/team-user')) {
            return 'settings';
        }

        return null;
    }
}
