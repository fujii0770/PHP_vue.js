<li class="tree-node {{ $itemFolder->id }}">
    <div class="name " data-id="{{ $itemFolder->id }}" data-longTermFolder="{{ $itemFolder->name }}" data-parent="{{ $itemFolder->parent_id }}" ng-class="{selected: selectedID == {{ $itemFolder->id }}}" ng-click="selectRow({{ $itemFolder->id }})">
        <span class="arrow">
             @isset($itemFolder->data_child)
                <i class="fas fa-caret-down icon icon-down"></i> <i class="fas fa-caret-right icon icon-right"></i>
             @endisset
        </span>
        <i class="far fa-folder"></i>
        {{ $itemFolder->name }}
    </div>

    <ul class="items">
        @isset($itemFolder->data_child)
            @foreach ($itemFolder->data_child as $_itemFolder)
                @include('LongTerm.longTermFolder.folder_tree_node',['itemFolder' => $_itemFolder])
            @endforeach
        @endisset
    </ul>
</li>

