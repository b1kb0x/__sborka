<?php

namespace App\Http\Controllers\Admin;

use App\Enums\UserRole;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\UpdateCustomerRequest;
use App\Models\User;
use App\Services\SettingsService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CustomerController extends Controller
{
    public function index(Request $request, SettingsService $settings): View
    {
        $query = User::query()
            ->where('role', 'customer')
            ->withCount('orders')
            ->withMax('orders as last_order_at', 'created_at')
            ->latest();

        if ($request->filled('status')) {
            $query->where('status', $request->string('status')->value());
        }

        if ($request->filled('search')) {
            $search = trim($request->string('search')->value());
            $normalizedPhone = preg_replace('/\D+/', '', $search);

            $query->where(function ($q) use ($search, $normalizedPhone): void {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%");

                if ($normalizedPhone !== '') {
                    $q->orWhereRaw(
                        "REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(phone, '+', ''), ' ', ''), '-', ''), '(', ''), ')', '') LIKE ?",
                        ["%{$normalizedPhone}%"]
                    );
                }
            });
        }

        $customers = $query
            ->paginate($settings->adminCustomersPerPage())
            ->withQueryString();

        return view('admin.customers.index', [
            'customers' => $customers,
            'filters' => [
                'search' => $request->string('search')->value(),
                'status' => $request->string('status')->value(),
            ],
        ]);
    }

    public function edit(User $customer): View
    {
        abort_unless($customer->role === UserRole::Customer, 404);

        return view('admin.customers.edit', [
            'customer' => $customer,
        ]);
    }

    public function update(UpdateCustomerRequest $request, User $customer): RedirectResponse
    {
        abort_unless($customer->role === UserRole::Customer, 404);

        $validated = $request->validated();
        $validated['name'] = $this->buildCustomerName(
            $validated['first_name'] ?? '',
            $validated['last_name'] ?? '',
            $customer
        );

        $customer->update($validated);

        return redirect()
            ->route('admin.customers.edit', $customer)
            ->with('success', 'Покупатель обновлён.');
    }

    protected function buildCustomerName(string $firstName, string $lastName, User $customer): string
    {
        $name = trim($firstName.' '.$lastName);

        if ($name !== '') {
            return $name;
        }

        return $customer->name !== '' ? $customer->name : $customer->email;
    }
}
