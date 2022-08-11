<template>
  <div class="vs-row" :class="treeId" style="display: flex; width: 100%;height: 100%;">
      <div id="htmlTemplate" style="display:none">
          <li :id=" treeId ? 'nodeContent-' + treeId : 'nodeContent'" class="tree-node" :class="treeId">
              <div class="tree-content" style="padding-left: 0px;">
                  <i class="tree-arrow ltr"></i>
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
        <div role="tree" class="w-full">
        <ul class="tree-root" :id="treeId">

        </ul>
      </div>
    </vs-row>
  </div>
</template>
<script>
import { mapState, mapActions } from "vuex";
import config from "../../app.config";
import Axios from "axios";
export default {
  props: {
    opened: {type: Boolean, default: true},
    treeId: { type: String, default: '' },
  },
  data: () => ({
    nodeIndex: 0,
    numberInitItem: 20,
    numberNextItem: 10,
    isExpanded: false,
    treeData: [],
    company: [],
    finishedDate:'',
  }),
  methods: {
      ...mapActions({
          getMyFolders: "circulars/getMyFolders",
          getInfoByHash: "user/getInfoByHash",
      }),
    renderNode(node, padding, nodeId, parentNum, nodeOrder, hidden) {
      if(!node) return;
      this.nodeIndex++;
      let selectId = '#' + (this.treeId ? 'nodeContent-' + this.treeId : 'nodeContent')
      const nodeElement = $(selectId).clone();
      nodeElement.removeAttr('id');
      nodeElement.attr('parent-node', `node-${parentNum}`);
      nodeElement.attr('node-order', nodeOrder);
      nodeElement.addClass('node-'+nodeId);
      nodeElement.attr('node-id','node-'+nodeId);
      nodeElement.attr('data-id', node.id);
      if(hidden) nodeElement.addClass('hidden');
      nodeElement.find('.tree-content').css('padding-left', padding + 'px');
      if(Object.prototype.hasOwnProperty.call(node.data, "isGroup") && node.data.isGroup) {
          if(this.isExpanded)
              nodeElement.addClass('has-child expanded');
          else
              nodeElement.addClass('has-child');
        nodeElement.find('.tree-text').html('<i class="far fa-folder"></i> '+ node.text)
      }else {
        nodeElement.find('.tree-text').html(`
            <span><i class="fas fa-user"></i></span>
            <span style="width: 200px">${node.text}</span>
        `);
      }

      if(Object.prototype.hasOwnProperty.call(node, "children") && node.children && node.children.length > 0) {
          let newDepartmentsNode = [];
          node.children=node.children.map(_=>{
              if(_.data && Object.prototype.hasOwnProperty.call(_.data, "isGroup") && _.data.isGroup){
                  newDepartmentsNode.push(_);
              }
              return _
          }).filter(_=>{
              return _!=null
          })
          if(this.isExpanded)
              nodeElement.find('.tree-content .tree-arrow').addClass('has-child expanded');
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
              nodeElement.find('> .tree-children').append('<li class="tree-node" :class="'+ this.treeId + '"><a href="#" node-parent="'+`node-${nodeId}`+'" data-padding="'+(parseInt(padding)+24)+'" data-order="' + this.numberInitItem + '" class="load-more text-center">もっと見る</a></li>')
            }
            this.nodeIndex = this.nodeIndex + (node.children.length - this.numberNextItem);
            break;
          }
        }
          for(let sonNode of newDepartmentsNode) {
              index++;
              nodeElement.find('> .tree-children').append(this.renderNode(sonNode,  parseInt(padding) + 24, +`${nodeId}${index}`, nodeId, index));
          }
      }
      return nodeElement;
    },
    async loadTree(treeData) {
      this.nodeIndex = 0;
        $('#' + this.treeId).html('');
        //$('.tree-root').html('');
        let index = 0;
        let html = '';
        for(let node of treeData) {
          index++;
          await this.$forceNextTick();
          html += this.renderNode(node, 0, index, 0, index).prop('outerHTML');
        }
        //document.querySelector('.tree-root').innerHTML = html;
        document.querySelector('#' + this.treeId).innerHTML = html;
    },
    async searchTreeData(){
        const mapfunc = (item) => {
            let newItem = {};
            if(!item) return null;
            if(!Object.prototype.hasOwnProperty.call(item, "parent_id")) {
                newItem = {text: item.folder_name, data: item};
                return newItem;
            }else {
                let children = [];
                if(Object.prototype.hasOwnProperty.call(item, "children")) {
                    if(item.children)
                        children.push(...item.children.map(mapfunc));
                }
                newItem.id = item.id;
                newItem.text =  item.folder_name;
                newItem.children =  children;
                newItem.data =  {isGroup: true};
                return newItem;
            }
        };
        let myFolders = await this.getMyFolders();
        myFolders = myFolders.map(mapfunc);

        let arrAddressTree = [
            {id:0, text:this.company.company_name, children: myFolders, data: {isGroup: true}},
        ];

        return arrAddressTree;
    },
    async onSearch() {
      this.isExpanded = false;
      const treeData = await this.searchTreeData();
      await this.loadTree(treeData);
    }
  },
    async mounted() {
      if(this.$store.state.home.usingPublicHash){
        const userHashInfoPromise = this.getInfoByHash();
        userHashInfoPromise.then((item) => {
          this.finishedDate = item.finishedDate;
        })
      }
      this.company = this.$store.state.groupware.myCompany  ? this.$store.state.groupware.myCompany :[]
      if(!this.company || ((new Date().getTime()) -  this.company.currentTime > 600000 )){
        this.company = await Axios.get(`${config.BASE_API_URL}${this.$store.state.home.usingPublicHash ? '/public': ''}/setting/getMyCompany`, this.$store.state.home.usingPublicHash ? {data: {usingHash: true, finishedDate: this.finishedDate}}:{})
            .then(response => {
              return response.data ? response.data.data : [];
            })
            .catch(error => {
              return [];
            });
      }
      await this.onSearch();

    const $this = this;
    $(document).off('click', '.tree-arrow').on('click', '.tree-arrow', function(e) {
      const nodeElement = $(e.target).closest('.tree-node');
      if(!nodeElement.hasClass('expanded')) {
        nodeElement.addClass('expanded');
        nodeElement.find('> .tree-children').removeClass('hidden');
        nodeElement.find('> .tree-content .tree-arrow').addClass('expanded');
      }else {
        nodeElement.removeClass('expanded');
        nodeElement.find('> .tree-children').addClass('hidden');
        nodeElement.find('> .tree-content .tree-arrow').removeClass('expanded');
      }
    });
      let selectClass = '.' + this.treeId;
        $(document).off('click', selectClass).on('click', selectClass, function(e) {
            const nodeElement = $(e.target).closest('.tree-node' + selectClass);
            $('.tree-node' + selectClass).removeClass('selected');
            nodeElement.addClass('selected');
            const folderId = nodeElement.attr('data-id');
            $this.$emit('onNodeClick', folderId);
        });
  }
}
</script>

<style>
  .tree_dialog {
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
</style>
