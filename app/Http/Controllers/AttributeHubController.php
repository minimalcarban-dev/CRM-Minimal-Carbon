<?php

namespace App\Http\Controllers;

use App\Models\ClosureType;
use App\Models\DiamondClarity;
use App\Models\DiamondCut;
use App\Models\MetalType;
use App\Models\RingSize;
use App\Models\SettingType;
use App\Models\StoneColor;
use App\Models\StoneShape;
use App\Models\StoneType;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class AttributeHubController extends Controller
{
    public function index(Request $request)
    {
        $currentAdmin = auth()->guard('admin')->user();

        $modules = collect($this->modules())
            ->filter(function (array $module) use ($currentAdmin) {
                return $currentAdmin && $currentAdmin->canAccessAny($module['access_permissions']);
            })
            ->map(function (array $module) use ($request, $currentAdmin) {
                return $this->enrichModule($module, $request, $currentAdmin);
            })
            ->values();

        $selectedModuleKey = $request->query('module');
        $selectedModule = $modules->firstWhere('key', $selectedModuleKey) ?? $modules->first();
        $workspace = $selectedModule ? $this->buildWorkspace($request, $selectedModule, $request->query('view', 'list')) : null;

        return view('attributes.index', [
            'modules' => $modules,
            'selectedModule' => $selectedModule,
            'workspace' => $workspace,
            'moduleCount' => $modules->count(),
            'totalRecords' => $modules->sum('count'),
        ]);
    }

    public function fragment(Request $request)
    {
        $currentAdmin = auth()->guard('admin')->user();
        $module = $this->moduleByKey((string) $request->query('module'));

        abort_if(!$module, 404);
        abort_unless($currentAdmin && $currentAdmin->canAccessAny($module['access_permissions']), 403);

        $module = $this->enrichModule($module, $request, $currentAdmin);
        $workspace = $this->buildWorkspace($request, $module, $request->query('view', 'list'));

        return view('attributes.partials.workspace', $workspace);
    }

    /**
     * Module registry for the hub.
     *
     * Every module keeps its own table and CRUD routes.
     */
    private function modules(): array
    {
        return [
            [
                'key' => 'metal_types',
                'label' => 'Metal Types',
                'description' => 'Gold, silver, platinum, and other metal masters.',
                'icon' => 'bi-award',
                'model' => MetalType::class,
                'permission_prefix' => 'metal_types',
                'access_permissions' => ['metal_types.view', 'metal_types.create'],
            ],
            [
                'key' => 'setting_types',
                'label' => 'Setting Types',
                'description' => 'Reusable setting masters for jewellery workflows.',
                'icon' => 'bi-gear',
                'model' => SettingType::class,
                'permission_prefix' => 'setting_types',
                'access_permissions' => ['setting_types.view', 'setting_types.create'],
            ],
            [
                'key' => 'closure_types',
                'label' => 'Closure Types',
                'description' => 'Earring closure masters used across design forms.',
                'icon' => 'bi-link-45deg',
                'model' => ClosureType::class,
                'permission_prefix' => 'closure_types',
                'access_permissions' => ['closure_types.view', 'closure_types.create'],
            ],
            [
                'key' => 'ring_sizes',
                'label' => 'Ring Sizes',
                'description' => 'Shared ring size values for order and stock forms.',
                'icon' => 'bi-circle',
                'model' => RingSize::class,
                'permission_prefix' => 'ring_sizes',
                'access_permissions' => ['ring_sizes.view', 'ring_sizes.create'],
            ],
            [
                'key' => 'stone_types',
                'label' => 'Stone Types',
                'description' => 'Core stone type masters used by diamond workflows.',
                'icon' => 'bi-gem',
                'model' => StoneType::class,
                'permission_prefix' => 'stone_types',
                'access_permissions' => ['stone_types.view', 'stone_types.create'],
            ],
            [
                'key' => 'stone_shapes',
                'label' => 'Stone Shapes',
                'description' => 'Shapes used in stone and diamond selection forms.',
                'icon' => 'bi-square',
                'model' => StoneShape::class,
                'permission_prefix' => 'stone_shapes',
                'access_permissions' => ['stone_shapes.view', 'stone_shapes.create'],
            ],
            [
                'key' => 'stone_colors',
                'label' => 'Stone Colors',
                'description' => 'Colour masters for stone and jewellery records.',
                'icon' => 'bi-droplet',
                'model' => StoneColor::class,
                'permission_prefix' => 'stone_colors',
                'access_permissions' => ['stone_colors.view', 'stone_colors.create'],
            ],
            [
                'key' => 'diamond_clarities',
                'label' => 'Diamond Clarities',
                'description' => 'Clarity grades shared by diamond-related screens.',
                'icon' => 'bi-card-list',
                'model' => DiamondClarity::class,
                'permission_prefix' => 'diamond_clarities',
                'access_permissions' => ['diamond_clarities.view', 'diamond_clarities.create'],
            ],
            [
                'key' => 'diamond_cuts',
                'label' => 'Diamond Cuts',
                'description' => 'Cut masters used across diamond and order forms.',
                'icon' => 'bi-scissors',
                'model' => DiamondCut::class,
                'permission_prefix' => 'diamond_cuts',
                'access_permissions' => ['diamond_cuts.view', 'diamond_cuts.create'],
            ],
        ];
    }

    private function moduleByKey(?string $key): ?array
    {
        if (!$key) {
            return null;
        }

        foreach ($this->modules() as $module) {
            if ($module['key'] === $key) {
                return $module;
            }
        }

        return null;
    }

    private function enrichModule(array $module, Request $request, $currentAdmin): array
    {
        $modelClass = $module['model'];

        $module['count'] = (int) $modelClass::count();
        $module['can_view'] = $currentAdmin?->canAccessAny([$module['permission_prefix'] . '.view']) ?? false;
        $module['can_create'] = $currentAdmin?->canAccessAny([$module['permission_prefix'] . '.create']) ?? false;
        $module['can_edit'] = $currentAdmin?->canAccessAny([$module['permission_prefix'] . '.edit']) ?? false;
        $module['can_delete'] = $currentAdmin?->canAccessAny([$module['permission_prefix'] . '.delete']) ?? false;
        $module['is_selected'] = $request->query('module') === $module['key'];
        $module['singular_label'] = Str::singular($module['label']);
        $module['index_url'] = route('attributes.index', ['module' => $module['key']]);
        $module['fragment_url'] = route('attributes.fragment', ['module' => $module['key'], 'view' => 'list']);
        $module['create_url'] = route($module['permission_prefix'] . '.create');
        $module['edit_route_name'] = $module['permission_prefix'] . '.edit';
        $module['store_route_name'] = $module['permission_prefix'] . '.store';
        $module['update_route_name'] = $module['permission_prefix'] . '.update';
        $module['destroy_route_name'] = $module['permission_prefix'] . '.destroy';

        return $module;
    }

    private function buildWorkspace(Request $request, array $module, string $view): array
    {
        $mode = in_array($view, ['create', 'edit'], true) ? $view : 'list';
        $search = trim((string) $request->query('search', ''));
        $expandedItemId = $request->filled('detail') ? (int) $request->query('detail') : null;
        $item = null;
        $items = null;
        $baseQuery = $module['model']::query();

        if ($search !== '') {
            $baseQuery->where('name', 'like', '%' . $search . '%');
        }

        $totalCount = (clone $baseQuery)->count();
        $activeCount = (clone $baseQuery)->where('is_active', true)->count();
        $inactiveCount = max($totalCount - $activeCount, 0);

        if ($mode === 'list') {
            $items = $baseQuery->orderByDesc('id')->paginate(15)->withQueryString();
        } elseif ($mode === 'edit') {
            $id = (int) $request->query('id');
            abort_if(!$id, 404);
            $item = $module['model']::findOrFail($id);
        }

        $formAction = $mode === 'edit'
            ? route($module['update_route_name'], $item->id ?? 0)
            : route($module['store_route_name']);

        return [
            'module' => $module,
            'mode' => $mode,
            'search' => $search,
            'expandedItemId' => $expandedItemId,
            'items' => $items,
            'item' => $item,
            'totalCount' => $totalCount,
            'activeCount' => $activeCount,
            'inactiveCount' => $inactiveCount,
            'formAction' => $formAction,
            'formMethod' => $mode === 'edit' ? 'PUT' : 'POST',
            'submitLabel' => $mode === 'edit'
                ? 'Update ' . $module['singular_label']
                : 'Create ' . $module['singular_label'],
            'headingLabel' => $mode === 'edit'
                ? 'Edit ' . $module['singular_label']
                : 'Add New ' . $module['singular_label'],
            'descriptionLabel' => $mode === 'edit'
                ? 'Update the selected attribute master without leaving the hub.'
                : 'Create a new reusable attribute master inside the hub.',
            'backToListUrl' => $module['fragment_url'],
            'canCreate' => $module['can_create'],
            'canEdit' => $module['can_edit'],
            'canDelete' => $module['can_delete'],
            'canView' => $module['can_view'],
        ];
    }
}
