<template>
  <div class="vs-row" style="display: flex; width: 100%;">
    <div id="htmlTemplate" style="display:none">
      <li id="nodeContent" class="tree-node">
        <div class="tree-content" style="padding-left: 0px;">
          <i class="tree-arrow ltr"></i> <i class="tree-checkbox" :style="mode=='1'?'display:none':''"></i>
          <span tabindex="-1" class="tree-anchor">
              <span class="tree-text">
                <i class="far fa-folder"></i>
                個人
              </span>
          </span>
        </div>
        <ul class="tree-children" :class="isExpanded?'':'hidden'"></ul>
      </li>
    </div>
    <vs-row>
      <vs-col class="mt-4" vs-type="flex" vs-align="center" vs-w="7.5" vs-xs="12">
          <vx-input-group  class="w-full mb-0">
              <vs-input v-model="searchModel" @change="onSearch"/>
              <template slot="append">
                  <div class="append-text btn-addon">
                      <vs-button color="primary" @click="onSearch">
                          <i class="fas fa-search"></i>
                          <div  @click="onSearch" style="opacity: 0;position: fixed;inset: 0px;width: 100%;height: 100%;position: absolute;"></div>
                      </vs-button>
                  </div>
              </template>
          </vx-input-group>
      </vs-col>
      <vs-col class="pr-3 contact_tree_action" vs-type="flex" vs-align="flex-end" vs-justify="flex-end" vs-w="4.5" vs-xs="12" v-show="mode=='0'">
          <vs-button style="padding-left: 1rem;padding-right: 1rem" class="square"  color="primary" type="filled" v-on:click="onTreeAddToStepClick(false)"> 回覧先に追加</vs-button>
          <vs-button v-if="showPlan && notAllSend" style="padding-left: 1rem;padding-right: 1rem" class="square"  color="primary" type="filled" v-on:click="onTreeAddToStepClick(true)"> 合議先に追加</vs-button>
      </vs-col>
    </vs-row>
     <vs-row :class="(mode=='1'?'tree-wrap-screen font-size-screen':'tree-wrap_dialog')">
        <div role="tree" class="w-full tree">
        <ul v-bind:class="number ? ('tree-root-'+ number) : 'tree-root'">

        </ul>
        <!---->
      </div>
    </vs-row>
  </div>
</template>
<script>
export default {
  props: {
    treeOptions: {type: Object, default: () => {}},
    treeData: {type: Array, default: () => []},
    opened: {type: Boolean, default: false},
    editorShowFlg:{type: Boolean, default: true},
    showPlan: {type: Boolean, default: false},
    // PAC_5-2353
    notAllSend: {type: Boolean, default: true},
    number: {type: String, default: ''},
    // mode=0 model mode=1 screen
    mode: {type: String, default: '0'},
  },
  data: () => ({
    nodeIndex: 0,
    numberInitItem: 20,
    numberNextItem: 10,
    selected: [],
    oldSearchModel: '',
    searchModel: '',
    isExpanded: false,
  }),
  methods: {
    renderNode(node, padding, nodeId, parentNum, nodeOrder, hidden) {
      if(!node) return;
      this.nodeIndex++;
      const nodeElement = $('#nodeContent').clone();
      nodeElement.removeAttr('id');
      nodeElement.attr('parent-node', `node-${parentNum}`);
      nodeElement.attr('node-order', nodeOrder);
      nodeElement.addClass('node-'+nodeId);
      nodeElement.attr('node-id','node-'+nodeId);
      nodeElement.find('.tree-checkbox').addClass('tree-checkbox'+ (this.number ? ('-' + this.number) : '-0'));
      nodeElement.find('.tree-children').removeClass('hidden').addClass(this.isExpanded ? '' : 'hidden');
      if(hidden) nodeElement.addClass('hidden');
      nodeElement.find('.tree-content').css('padding-left', padding + 'px');
      if(Object.prototype.hasOwnProperty.call(node.data, "isGroup") && node.data.isGroup) {
          if(this.isExpanded)nodeElement.addClass('has-child expanded');
          else
              nodeElement.addClass('has-child');
        nodeElement.find('.tree-text').html('<i class="far fa-folder"></i> '+ node.text)
      }else {
        let checkbox_id = '.tree-checkbox'+ (this.number ? ('-' + this.number) : '-0');
        let position_name = node.data.position_name?node.data.position_name:'';
        let phone_number = node.data.phone_number?node.data.phone_number:'';
        nodeElement.find(checkbox_id).attr('data-user', JSON.stringify(node.data));
        nodeElement.find('.tree-text').attr('data-id', node.data.id);
        nodeElement.find('.tree-text').html(`
            <span><i class="fas fa-user"></i></span>
            <span style="width: 200px">${node.data.family_name + node.data.given_name}</span>
            <span style="width: 250px;word-break: break-word;">${node.data.email}</span>
            ${this.mode=='1'?'<span style="width: 150px">'+position_name+'</span>':''}
            ${this.mode=='1'?'<span style="width: 150px">'+phone_number+'</span>':''}
            ${(node.data.state==1 && this.editorShowFlg)&&this.mode=='0' ? '<span class="buttons"><button type="button" name="button" class="vs-component vs-button square mr-0 vs-button-primary vs-button-filled node-action-button" data-id="'+node.data.id+'"><span class="vs-button-backgroundx vs-button--background" style="opacity: 1; left: 20px; top: 20px; width: 0px; height: 0px; transition: width 0.3s ease 0s, height 0.3s ease 0s, opacity 0.3s ease 0s;"></span><!----><span class="vs-button-text vs-button--text"> 編集</span><span class="vs-button-linex" style="top: auto; bottom: -2px; left: 50%; transform: translate(-50%);"></span></button></span>':''}
        `);
      }

      if(Object.prototype.hasOwnProperty.call(node, "children") && node.children && node.children.length > 0) {
          let newDepartmentsNode = [];
          node.children=node.children.map(_=>{
              if(_.data && Object.prototype.hasOwnProperty.call(_.data, "isGroup") && _.data.isGroup){
                  newDepartmentsNode.push(_);
                  //return null
              }
              return _
          }).filter(_=>{
              return _!=null
          })
          if(this.isExpanded)nodeElement.find('.tree-content .tree-arrow').addClass('has-child expanded');
          else
              nodeElement.find('.tree-content .tree-arrow').addClass('has-child');
        nodeElement.attr('child-total', node.children.length);
        let boolFlg = false;
        if(node.children.length - newDepartmentsNode.length  > this.numberInitItem) {
            boolFlg = true;
          nodeElement.attr('children', JSON.stringify(node.children));
        }
        let index = 0;
        for(let childNode of node.children) {
            if(Object.prototype.hasOwnProperty.call(childNode.data, "isGroup") && node.data.isGroup){
                continue;
            }
          index++;
          nodeElement.find('> .tree-children').append(this.renderNode(childNode,  parseInt(padding) + 24, +`${nodeId}${index}`, nodeId, index));
          if(index >= this.numberInitItem) {
            if(index === this.numberInitItem && boolFlg) {
              nodeElement.find('> .tree-children').append('<li class="tree-node"><a href="#" node-parent="'+`node-${nodeId}`+'" data-padding="'+(parseInt(padding)+24)+'" data-order="' + this.numberInitItem + '" class="load-more text-center '+ `this.mode=='1'?'font-size-screen':''` +'">もっと見る</a></li>')
            }
            this.nodeIndex = this.nodeIndex + (node.children.length - this.numberNextItem);
            break;
          }
        }
          // PAC_5-2281 上位部署に紐づいているユーザーが多いとき、子部署が見えにくくなる
          for(let sonNode of newDepartmentsNode) {
              index++;
              nodeElement.find('> .tree-children').append(this.renderNode(sonNode,  parseInt(padding) + 24, +`${nodeId}${index}`, nodeId, index));
          }

      }
      return nodeElement;
    },
    async loadTree(treeData) {
      this.nodeIndex = 0;
      let element = '.tree-root' + (this.number ? ('-' + this.number) : '');
        $(element).html('');
        let index = 0;
        let html = '';
        //this.$store.dispatch('updateLoading', true);
        for(let node of treeData) {
          index++;
          await this.$forceNextTick();
          html += this.renderNode(node, 0, index, 0, index).prop('outerHTML');
        }
         document.querySelector(element).innerHTML = html;
      //this.$store.dispatch('updateLoading', false);
    },
    onTreeAddToStepClick(isPlan) {
      let checkbox_id = '.tree-checkbox' + (this.number ? ('-' + this.number) : '-0' ) + '.checked';
      let selected = $(checkbox_id).map(function(){return $(this).attr('data-user')}).get().filter(item => item).map(item => JSON.parse(item));
      selected = selected.map(item=>{
            item.is_plan=0
            if (isPlan){
                item.is_plan=1
            }
            return item
        })
      this.$emit('onTreeAddToStepClick', selected);
      this.searchModel = '';
      this.oldSearchModel = this.searchModel;
    },
    searchTreeData(keyword){
      if(!keyword) return this.treeData;
      const newData =[];
      const checkHasFunc = (arrays,_keyword) => {
        return arrays.some(item => {
          if(Object.prototype.hasOwnProperty.call(item.data, "isGroup") && item.data.isGroup) {
            return item.text.includes(_keyword) || checkHasFunc(item.children, _keyword);
          }else {
            return (item.data.family_name + item.data.given_name + item.data.email).includes(_keyword);
          }
        })
      }
      const filterFunc = (item) => {
        if(Object.prototype.hasOwnProperty.call(item.data, "isGroup") && item.data.isGroup) {
          return item.text.includes(keyword) || checkHasFunc(item.children, keyword);
        }else {
          return (item.data.family_name + item.data.given_name + item.data.email).includes(keyword);
        }
      }

      const mapFunc = (item) => {
        if(Object.prototype.hasOwnProperty.call(item.data, "isGroup") && item.data.isGroup) {
          const newItem = Object.assign({}, item);
          newItem.children = newItem.children.filter(filterFunc).map(mapFunc);
          return newItem;
        }else {
          return Object.assign({}, item);
        }
      }

      return this.treeData.filter(filterFunc).map(mapFunc);
    },
    async onSearch() {
      this.isExpanded = this.searchModel ? true : false;
      if(this.oldSearchModel === this.searchModel) return;
      this.oldSearchModel = this.searchModel;
      let keyWord = this.searchModel.replace(/(\s*)|(\s*$)/g, '')
      const treeData = this.searchTreeData(keyWord);
      await this.loadTree(treeData);
    }
  },
  mounted() {
    const $this = this;
    let checkbox_id = '.tree-checkbox' + (this.number ? ('-' + this.number) : '-0');
    $(document).off('click', checkbox_id).on('click', checkbox_id, function(e) {
      $(this).toggleClass('checked');
      const checked = $(this).hasClass('checked');
      if(checked){
          // fix PAC_5-948 【速度改善】アドレス帳で指定した宛先の反映速度改善①
          if (this.parentNode && this.parentNode.parentNode){
              var fourChildNode = this.parentNode.parentNode.querySelectorAll(checkbox_id);
              for (let i = 0; i < fourChildNode.length; i++) {
                  fourChildNode[i].classList.add("checked");
              }
          }
        //  $(this).closest('.tree-node').find('.tree-children .tree-checkbox').addClass('checked');
      }
      else {
          if (this.parentNode && this.parentNode.parentNode ) {
              var fourChildNode = this.parentNode.parentNode.querySelectorAll(checkbox_id);
              for (let i = 0; i < fourChildNode.length; i++) {
                  fourChildNode[i].classList.remove("checked");
              }
          }
      //  $(this).closest('.tree-node').find('.tree-children .tree-checkbox').removeClass('checked');
      }
    });
    $(document).off('click', '.tree-anchor, .tree-arrow').on('click', '.tree-anchor, .tree-arrow', function(e) {
      const nodeElement = $(e.target).closest('.tree-node');
      $('.tree-node').removeClass('selected');
      if(!nodeElement.hasClass('expanded')) {
        nodeElement.addClass('expanded');
        nodeElement.addClass('selected');
        nodeElement.find('> .tree-children').removeClass('hidden');
        nodeElement.find('> .tree-content .tree-arrow').addClass('expanded');
      }else {
        nodeElement.removeClass('expanded');
        nodeElement.removeClass('selected');
        nodeElement.find('> .tree-children').addClass('hidden');
        nodeElement.find('> .tree-content .tree-arrow').removeClass('expanded');
      }
    });

    $(document).on('click','.tree-node button.node-action-button', async function(e) {
      const nodeUserId = $(this).attr('data-id');
      $this.$emit('onNodeClick', nodeUserId);
    });

    // 利用者名簿(mode=1)場合、ダブルクリック
    if($this.mode=='1'){
      $(document).on('dblclick','.tree-text', async function(e) {
        const nodeUserId = $(this).attr('data-id');
        $this.$emit('onNodeClick', nodeUserId);
      });
    }
    // クロージャーのスコープに合うようにeditorShowFlgを宣言し直す
    const editorShowFlg = this.editorShowFlg
    $(document).on('click','.tree-node a.load-more', async function(e) {
      const targetElement = this;
      let nodeOrder = +$(targetElement).attr('data-order');
      if($(targetElement).is(':last-child') && +$(targetElement).closest('.tree-node.has-child').attr('child-total') > $this.numberInitItem) {
        const parentId = $(targetElement).closest('.tree-node.has-child').attr('node-id').split('-').pop();
        const padding = $(targetElement).attr('data-padding');
        let parentClass = '.tree-node.'+$(targetElement).attr('node-parent');
        //parent.find('.tree-children .tree-node:first-child').remove();
          parentClass += '.has-child';
        const children = JSON.parse($(parentClass).attr('children') || '[]');
        let childrenCopy =children.map(_=>{
              if(_.data && Object.prototype.hasOwnProperty.call(_.data, "isGroup") && _.data.isGroup){
                  return null
              }
              return _
          }).filter(_=>{
              return _!=null
          })
        const items = childrenCopy.slice(nodeOrder, nodeOrder + $this.numberNextItem);

        $this.$store.dispatch('updateLoading', true);
        $(targetElement).remove();
        //const checked = $(parentClass).find('.tree-checkbox').hasClass('checked');
        const checked = false;
        let html = '';
        let number = $this.number;
        let mode = $this.mode;
        for(let item of items) {
          if(!item){
            continue;
          }
          nodeOrder++;
          await $this.$forceNextTick();
          if(item.data && Object.prototype.hasOwnProperty.call(item.data, "isGroup") && item.data.isGroup) {
            //html += $this.renderNode(item, padding, +`${parentId}${nodeOrder}`, parentId, nodeOrder).prop('outerHTML');
          }else {
            // this.editorShowFlagとなっていたので、スコープが違い常にundefinedになるので、宣言しなおした変数を利用
            let position_name = item.data.position_name?item.data.position_name:'';
            let phone_number = item.data.phone_number?item.data.phone_number:'';
            html += `
            <li class="tree-node node-${parentId}${nodeOrder}" parent-node="${parentClass}" node-order="${nodeOrder}" node-id="node-${parentId}${nodeOrder}">
                <div class="tree-content" style="padding-left: ${padding}px;">
                    <i class="tree-arrow ltr"></i> <i class="tree-checkbox ${number ? ('tree-checkbox' + '-' + number) : 'tree-checkbox-0'} ${checked ? 'checked': ''}" data-user='${JSON.stringify(item.data)}' style="${mode=='1' ? 'display:none' : ''}"></i>
                    <span tabindex="-1" class="tree-anchor">
                        <span class="tree-text" data-id="${item.data.id}">
                            <span>
                                <i class="fas fa-user"></i>
                            </span>
                            <span style="width:200px">${item.data.family_name + item.data.given_name}</span>
                            <span style="width:250px;word-break: break-word;">${item.data.email}</span>
                            ${mode=='1'?'<span style="width: 150px">'+position_name+'</span>':''}
                            ${mode=='1'?'<span style="width: 150px">'+phone_number+'</span>':''}
                            ${(item.data.state==1 && editorShowFlg)&&mode=='0' ? '<span class="buttons"><button type="button" name="button" class="vs-component vs-button square mr-0 vs-button-primary vs-button-filled node-action-button" data-id="'+item.data.id+'"><span class="vs-button-backgroundx vs-button--background" style="opacity: 1; left: 20px; top: 20px; width: 0px; height: 0px; transition: width 0.3s ease 0s, height 0.3s ease 0s, opacity 0.3s ease 0s;"></span><!----><span class="vs-button-text vs-button--text"> 編集</span><span class="vs-button-linex" style="top: auto; bottom: -2px; left: 50%; transform: translate(-50%);"></span></button></span>':''}
                        </span>
                    </span>
                </div>
            </li>
            `;
          }
        }
        if($(parentClass + ' .tree-children').find('.has-child').length <= 0){
            document.querySelector(parentClass + ' .tree-children').innerHTML += html;
            if(nodeOrder < childrenCopy.length) $(parentClass + '> .tree-children').append('<li class="tree-node"><a href="#" node-parent="'+`node-${parentId}`+'" data-padding="'+padding+'" data-order="'+nodeOrder+'" class="load-more text-center '+`mode=='1'?'font-size-screen':''`+'">もっと見る</a></li>')

        }else{
            $(parentClass + ' .tree-children').find('.has-child').first().before(html);
            if(nodeOrder < childrenCopy.length) $(parentClass + ' .tree-children').find('.has-child').first().before('<li class="tree-node"><a href="#" node-parent="'+`node-${parentId}`+'" data-padding="'+padding+'" data-order="'+nodeOrder+'" class="load-more text-center '+`mode=='1'?'font-size-screen':''`+'">もっと見る</a></li>')

        }
        //$(parentClass + ' .tree-children').append(html);
        $this.$store.dispatch('updateLoading', false);
      }
    });
  },
  watch: {
    opened: async function(val) {
      if(val) {
        this.oldSearchModel = '';
        this.isExpanded = false;
        if(this.searchModel){
            await this.onSearch();
            return ;
        }
        await this.loadTree(this.treeData);
      }
    },

  }
}
</script>

<style>
  .font-size-screen {
    font-size: 1rem;
  }
  .tree-wrap_dialog {
    max-height: 370px;
    overflow: auto;
  }
  .tree-wrap-screen {
    max-height: calc(100vh - 300px);
    overflow: auto;
  }
  .tree {
    overflow: auto;
  }

  .tree-root,
  .tree-children {
    list-style: none;
    padding: 0;
  }

  .tree > .tree-root,
  .tree > .tree-filter-empty {
    padding: 3px;
    box-sizing: border-box;
  }

  .tree.tree--draggable .tree-node:not(.selected) > .tree-content:hover {
    background: transparent;
  }

  .drag-above,
  .drag-below,
  .drag-on {
    position: relative;
    z-index: 1;
  }

  .drag-on > .tree-content {
    background: #fafcff;
    outline: 1px solid #7baff2;
  }

  .drag-above > .tree-content::before, .drag-below > .tree-content::after {
    display: block;
    content: '';
    position: absolute;
    height: 8px;
    left: 0;
    right: 0;
    z-index: 2;
    box-sizing: border-box;
    background-color: #3367d6;
    border: 3px solid #3367d6;
    background-clip: padding-box;
    border-bottom-color: transparent;
    border-top-color: transparent;
    border-radius: 0;
  }

  .drag-above > .tree-content::before {
    top: 0;
    transform: translateY(-50%);
  }

  .drag-below > .tree-content::after {
    bottom: 0;
    transform: translateY(50%);
  }

  .tree-node {
    white-space: nowrap;
    display: flex;
    flex-direction: column;
    position: relative;
    box-sizing: border-box;
  }

  .tree-content {
    display: flex;
    align-items: center;
    padding: 3px;
    cursor: pointer;
    width: 100%;
    box-sizing: border-box;
  }

  .tree-node:not(.selected) > .tree-content:hover {
    background: #f6f8fb;
  }

  .tree-node.selected > .tree-content {
    background-color: #e7eef7;
  }

  .tree-node.disabled > .tree-content:hover {
    background: inherit;
  }

  .tree-arrow {
    flex-shrink: 0;
    height: 30px;
    cursor: pointer;
    margin-left: 30px;
    width: 0;
  }

  .tree-arrow.has-child {
    margin-left: 0;
    width: 30px;
    position: relative;
  }

  .tree-arrow.has-child:after {
    border: 1.5px solid #494646;
    position: absolute;
    border-left: 0;
    border-top: 0;
    left: 9px;
    top: 50%;
    height: 9px;
    width: 9px;
    transform: rotate(-45deg) translateY(-50%) translateX(0);
    transition: transform .25s;
    transform-origin: center;
  }

  .tree-arrow.has-child.rtl:after {
    border: 1.5px solid #494646;
    position: absolute;
    border-right: 0;
    border-bottom: 0;
    right: 0px;
    top: 50%;
    height: 9px;
    width: 9px;
    transform: rotate(-45deg) translateY(-50%) translateX(0);
    transition: transform .25s;
    transform-origin: center;
  }

  .tree-arrow.expanded.has-child:after {
    transform: rotate(45deg) translateY(-50%) translateX(-5px);
  }

  .tree-checkbox {
    flex-shrink: 0;
    position: relative;
    width: 30px;
    height: 30px;
    box-sizing: border-box;
    border: 1px solid #dadada;
    border-radius: 2px;
    background: #fff;
    transition: border-color .25s, background-color .25s;
  }

  .tree-checkbox:after,
  .tree-arrow:after {
    position: absolute;
    display: block;
    content: "";
  }

  .tree-checkbox.checked,
  .tree-checkbox.indeterminate {
    background-color: #3a99fc;
    border-color: #218eff;
  }

  .tree-checkbox.checked:after {
    box-sizing: content-box;
    border: 1.5px solid #fff; /* probably width would be rounded in most cases */
    border-left: 0;
    border-top: 0;
    left: 9px;
    top: 3px;
    height: 15px;
    width: 8px;
    transform: rotate(45deg) scaleY(0);
    transition: transform .25s;
    transform-origin: center;
  }

  .tree-checkbox.checked:after {
    transform: rotate(45deg) scaleY(1);
  }

  .tree-checkbox.indeterminate:after {
    background-color: #fff;
    top: 50%;
    left: 20%;
    right: 20%;
    height: 2px;
  }

  .tree-anchor {
    flex-grow: 2;
    outline: none;
    display: flex;
    text-decoration: none;
    color: #343434;
    vertical-align: top;
    margin-left: 3px;
    line-height: 24px;
    padding: 3px 6px;
    -webkit-user-select: none;
    -moz-user-select: none;
    -ms-user-select: none;
    user-select: none;
  }

  .tree-node.selected > .tree-content > .tree-anchor {
    outline: none;
  }

  .tree-node.disabled > .tree-content > .tree-anchor {
    color: #989191;
    background: #fff;
    opacity: .6;
    cursor: default;
    outline: none;
  }

  .tree-input {
    display: block;
    width: 100%;
    height: 24px;
    line-height: 24px;
    outline: none;
    border: 1px solid #3498db;
    padding: 0 4px;
  }

  .l-fade-enter-active, .l-fade-leave-active {
    transition: opacity .3s, transform .3s;
    transform: translateX(0);
  }

  .l-fade-enter, .l-fade-leave-to {
    opacity: 0;
    transform: translateX(-2em);
  }


  .tree--small .tree-anchor {
    line-height: 19px;
  }

  .tree--small .tree-checkbox {
    width: 23px;
    height: 23px;
  }

  .tree--small .tree-arrow {
    height: 23px;
  }

  .tree--small .tree-checkbox.checked:after {
    left: 7px;
    top: 3px;
    height: 11px;
    width: 5px;
  }

  .tree-node.has-child.loading > .tree-content > .tree-arrow,
  .tree-node.has-child.loading > .tree-content > .tree-arrow:after {
    border-radius: 50%;
    width: 15px;
    height: 15px;
    border: 0;
  }

  .tree-node.has-child.loading > .tree-content > .tree-arrow {
    font-size: 3px;
    position: relative;
    border-top: 1.1em solid rgba(45,45,45, 0.2);
    border-right: 1.1em solid rgba(45,45,45, 0.2);
    border-bottom: 1.1em solid rgba(45,45,45, 0.2);
    border-left: 1.1em solid #2d2d2d;
    -webkit-transform: translateZ(0);
    -ms-transform: translateZ(0);
    transform: translateZ(0);
    left: 5px;
    -webkit-animation: loading 1.1s infinite linear;
    animation: loading 1.1s infinite linear;
    margin-right: 8px;
  }
  .tree-text > span {
    display: inline-block;
    white-space:normal;
  }

  .tree-text > span.buttons {
    text-align: center;
    width: 90px;
  }

  @-webkit-keyframes loading {
    0% {
      -webkit-transform: rotate(0deg);
      transform: rotate(0deg);
    }
    100% {
      -webkit-transform: rotate(360deg);
      transform: rotate(360deg);
    }
  }
  @keyframes loading {
    0% {
      -webkit-transform: rotate(0deg);
      transform: rotate(0deg);
    }
    100% {
      -webkit-transform: rotate(360deg);
      transform: rotate(360deg);
    }
  }
  .tree-dragnode {
    padding: 10px;
    border: 1px solid #e7eef7;
    position: fixed;
    border-radius: 8px;
    background: #fff;
    transform: translate(-50%, -110%);
    z-index: 10;
  }

  @media( max-width: 600px ){
    .contact_tree_action{
      margin-top: 5px;
      padding-right: 7px !important;
    }

    .contact_tree_action button:last-child{
      margin-right: 0;
    }
  }
</style>
