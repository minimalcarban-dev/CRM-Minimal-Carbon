<div class="attribute-workspace" data-mode="{{ $mode }}" data-module="{{ $module['key'] }}">
    @if ($mode === 'list')
        @include('attributes.partials.workspace-list', [
            'module' => $module,
            'items' => $items,
            'search' => $search,
            'expandedItemId' => $expandedItemId,
            'totalCount' => $totalCount,
            'activeCount' => $activeCount,
            'inactiveCount' => $inactiveCount,
            'canCreate' => $canCreate,
            'canEdit' => $canEdit,
            'canDelete' => $canDelete,
            'canView' => $canView,
        ])
    @else
        @include('attributes.partials.workspace-form', [
            'module' => $module,
            'mode' => $mode,
            'item' => $item,
            'formAction' => $formAction,
            'formMethod' => $formMethod,
            'submitLabel' => $submitLabel,
            'headingLabel' => $headingLabel,
            'descriptionLabel' => $descriptionLabel,
            'backToListUrl' => $backToListUrl,
        ])
    @endif
</div>
