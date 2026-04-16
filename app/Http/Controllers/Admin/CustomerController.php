<?php

namespace App\Http\Controllers\Admin;

use App\Enums\UserRole;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\UpdateCustomerRequest;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CustomerController extends Controller
{
    public function index(Request $request): View
    {
        $query = User::query()
            ->where('role', 'customer')
            ->withCount('orders')
            ->latest();

        if ($request->boolean('with_trashed')) {
            $query->withTrashed();
        }

        if ($request->filled('status')) {
            $query->where('status', $request->string('status')->value());
        }

        if ($request->filled('search')) {
            $search = trim($request->string('search')->value());

            $query->where(function ($q) use ($search): void {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%");
            });
        }

        $customers = $query->paginate(20)->withQueryString();

        return view('admin.customers.index', [
            'customers' => $customers,
            'filters' => [
                'search' => $request->string('search')->value(),
                'status' => $request->string('status')->value(),
                'with_trashed' => $request->boolean('with_trashed'),
            ],
        ]);
    }

    public function show(User $customer): View
    {
        abort_unless($customer->role === UserRole::Customer, 404);

        $customer->load([
            'orders' => fn ($query) => $query->latest(),
        ]);

        return view('admin.customers.show', [
            'customer' => $customer,
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

        $customer->update($request->validated());

        return redirect()
            ->route('admin.customers.index')
            ->with('success', 'Покупатель обновлён.');
    }

    public function destroy(User $customer): RedirectResponse
    {
        abort_unless($customer->role === UserRole::Customer, 404);

        $customer->delete();

        return redirect()
            ->route('admin.customers.index')
            ->with('success', 'Покупатель удалён.');
    }

    public function restore(int $customer): RedirectResponse
    {
        $customerModel = User::onlyTrashed()
            ->where('role', 'customer')
            ->findOrFail($customer);

        $customerModel->restore();

        return redirect()
            ->route('admin.customers.index')
            ->with('success', 'Покупатель восстановлен.');
    }
}
