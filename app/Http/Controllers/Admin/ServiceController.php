<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreServiceRequest;
use App\Http\Requests\UpdateServiceRequest;
use App\Models\Service;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ServiceController extends Controller
{
    public function index(Request $request): View
    {
        $search = trim((string) $request->query('search'));
        $status = $request->query('status', 'all');

        $services = Service::query()
            ->when($search !== '', function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%");
            })
            ->when($status === 'active', function ($q) {
                $q->where('status', true);
            })
            ->when($status === 'inactive', function ($q) {
                $q->where('status', false);
            })
            ->ordered()
            ->paginate(15)
            ->withQueryString();

        return view('admin.services.index', compact('services', 'search', 'status'));
    }

    public function create(): View
    {
        $nextOrder = (int) Service::max('sort_order') + 1;

        return view('admin.services.create', compact('nextOrder'));
    }

    public function store(StoreServiceRequest $request): RedirectResponse
    {
        Service::create($request->validatedData());

        return redirect()->route('admin.services.index')->with('success', 'Service created successfully.');
    }

    public function edit(Service $service): View
    {
        return view('admin.services.edit', compact('service'));
    }

    public function update(UpdateServiceRequest $request, Service $service): RedirectResponse
    {
        $service->update($request->validatedData());

        return redirect()->route('admin.services.index')->with('success', 'Service updated successfully.');
    }

    public function destroy(Service $service): RedirectResponse
    {
        $service->delete();

        return redirect()->route('admin.services.index')->with('success', 'Service deleted successfully.');
    }

    public function toggleStatus(Service $service): RedirectResponse
    {
        $service->update(['status' => ! $service->status]);

        return redirect()->route('admin.services.index')->with('success', 'Service status updated successfully.');
    }

    public function moveUp(Service $service): RedirectResponse
    {
        $this->swapOrder($service, -1);

        return redirect()->route('admin.services.index')->with('success', 'Service moved up.');
    }

    public function moveDown(Service $service): RedirectResponse
    {
        $this->swapOrder($service, 1);

        return redirect()->route('admin.services.index')->with('success', 'Service moved down.');
    }

    /**
     * Swap the sort_order with the adjacent service above (up) or below (down).
     */
    protected function swapOrder(Service $service, int $direction): void
    {
        $neighbor = $direction < 0
            // Move up: swap with the previous (lower sort_order) item.
            ? Service::where('sort_order', '<', $service->sort_order)->orderByDesc('sort_order')->first()
            // Move down: swap with the next (higher sort_order) item.
            : Service::where('sort_order', '>', $service->sort_order)->orderBy('sort_order')->first();

        if ($neighbor) {
            $current = $service->sort_order;
            $service->update(['sort_order' => $neighbor->sort_order]);
            $neighbor->update(['sort_order' => $current]);
        }
    }
}
