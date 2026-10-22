<?php

namespace Modules\Konum\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Modules\Konum\Models\LocationRecord;
use Modules\Konum\Services\KonumFrontendService;
use Yajra\DataTables\Facades\DataTables;

class KonumController extends Controller
{
    public function index(Request $request, KonumFrontendService $locations): View
    {
        $this->authorize('konum.view');
        $filters = $this->filters($request);
        $entity = $filters['entity'] ?? 'cities';
        $ready = $locations->ready();
        $status = DB::table('konum_imports')->where('source_commit', config('konum.source.commit'))->first();
        $labels = KonumFrontendService::LABELS;
        $choices = [];
        if ($ready) {
            foreach (KonumFrontendService::PARENTS as $column => $parent) {
                if ($parent === $entity) {
                    break;
                }
                $query = $locations->query($parent, $filters);
                $preceding = array_key_last($choices);
                $choices[$column] = $preceding === null || ! empty($filters[$preceding])
                    ? $query->orderBy('name')->orderBy('id')->get(['id', 'name'])
                    : collect();
            }
        }

        return view('konum::index', compact('entity', 'filters', 'ready', 'status', 'labels', 'choices'));
    }

    public function data(string $entity, Request $request, KonumFrontendService $locations): JsonResponse
    {
        $this->authorize('konum.view');
        $filters = $this->filters($request);
        $request->validate(['length' => ['sometimes', 'integer', 'min:1', 'max:100'], 'start' => ['sometimes', 'integer', 'min:0']]);
        abort_unless($locations->ready(), 409, 'Konum verisi henüz hazır değil.');
        $query = $locations->query($entity, $filters);

        return DataTables::eloquent($query)
            ->addColumn('location_name', function (LocationRecord $location) use ($entity, $filters): string {
                $entities = array_keys(KonumFrontendService::LABELS);
                $next = $entities[array_search($entity, $entities, true) + 1] ?? null;
                $column = array_search($entity, KonumFrontendService::PARENTS, true);
                $parameters = $next === null ? [] : [...$filters, 'entity' => $next, $column => $location->id];

                return view('konum::partials.name', compact('location', 'next', 'parameters'))->render();
            })
            ->rawColumns(['location_name'])->toJson();
    }

    /** @return array<string, mixed> */
    private function filters(Request $request): array
    {
        return $request->validate([
            'entity' => ['sometimes', Rule::in(array_keys(KonumFrontendService::LABELS))],
            'city_id' => ['nullable', 'integer', 'min:1'],
            'town_id' => ['nullable', 'integer', 'min:1'],
            'district_id' => ['nullable', 'integer', 'min:1'],
            'neighbourhood_id' => ['nullable', 'integer', 'min:1'],
        ]);
    }
}
