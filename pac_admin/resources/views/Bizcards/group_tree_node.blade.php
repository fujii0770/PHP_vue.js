<li class="tree-node">
     <div class="name">
        <span class="arrow">
                <i class="fas fa-caret-down icon icon-down"></i> <i class="fas fa-caret-right icon icon-right"></i> 
        </span>
        <label ng-click="selectGroupItem('{{ $listGroupTree->id }}')">
            <input type="checkbox" id="{{ $listGroupTree->id }}" data-parent="{{ $listGroupTree->parent_id }}">
            {{ $listGroupTree->name }}
        </label>
    </div>

        <ul class="items">
            @isset($listGroupTree->data_child)
            @foreach ($listGroupTree->data_child as $_itemGroup)
                @include('Bizcards.group_tree_node',['listGroupTree' => $_itemGroup])
            @endforeach
            @endisset
        </ul>
</li>
