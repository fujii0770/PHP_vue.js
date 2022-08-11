<li class="tree-node">
    <div class="name " data-id="{{ $itemDepartment->id }}" data-sort="{{ $itemDepartment->display_no }}" data-department="{{ $itemDepartment->name }}" data-parent="{{ $itemDepartment->parent_id }}" ng-class="{selected: selectedID == {{ $itemDepartment->id }}}" ng-click="selectRow({{ $itemDepartment->id }})">
        <span class="arrow">
            
                <i class="fas fa-caret-down icon icon-down"></i> <i class="fas fa-caret-right icon icon-right"></i> 
             
        </span>
        <i class="fas fa-folder-open"></i> 
        {{ $itemDepartment->name }}
    </div>                                    

        <ul class="items">
            @isset($itemDepartment->data_child)
            @foreach ($itemDepartment->data_child as $_itemDepartment)
                @include('DepartmentTitle.department_tree_node',['itemDepartment' => $_itemDepartment])
            @endforeach
            @endisset
        </ul>
</li>
<style>
    .hide{
        display: none!important;
    }
</style>
