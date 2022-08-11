<template>
    <div ref="wrap" style="position: relative">
        <img v-if="!useKonva"
         :src="imageUrl" alt="a4" style="width: 100%">
        <v-stage v-else
                 @touchstart="touchEditorClick" @touchend="confirmTouchEditor" @touchmove="moveToChangePage"
                 ref="stage"
                 :config="config"
                 :draggable="isDraggable"
                 @dragstart="handleDragstart"
                 @dragend="handleDragend"
                 :key="page.no + isConfidential">
            <v-layer ref="layer">
                <v-image :config="{
                    image: createImageFromUrl(imageUrl),
                    id: 1,
                    draggable: false,
                    width: config.width,
                    height: config.height,
                    type: 'Image',
                  }">
                </v-image>
            </v-layer>
        </v-stage>
        <!--<div class="text-options" v-if="text" :id="'text-' + text.index" :style="`top:${(text.y * ($store.state.home.fileSelected.zoom / 100) * realScale) - 35}px;left:${((text.x + text.width) * ($store.state.home.fileSelected.zoom / 100) * realScale)  - 45}px`" :key="textIdSelected">-->
            <!--<vs-row vs-type="flex" vs-align="center" vs-justify="center">-->
                <!--&lt;!&ndash;<v-popover class="button config" placement="top"&ndash;&gt;-->
                           <!--&lt;!&ndash;trigger="manual"&ndash;&gt;-->
                           <!--&lt;!&ndash;:open="openPopover"&ndash;&gt;-->
                           <!--&lt;!&ndash;offset="16"&ndash;&gt;-->
                           <!--&lt;!&ndash;:auto-hide="false" >&ndash;&gt;-->
                    <!--&lt;!&ndash;<div class="tooltip-target" v-on:click="openPopover = !openPopover">&ndash;&gt;-->
                        <!--&lt;!&ndash;<i class="fas fa-cog"> </i>&ndash;&gt;-->
                    <!--&lt;!&ndash;</div>&ndash;&gt;-->
                    <!--&lt;!&ndash;<vs-row style="position: relative" slot="popover">&ndash;&gt;-->
                        <!--&lt;!&ndash;<vs-col vs-type="flex" vs-w="8.5" style="border: 1px solid #0984e3">&ndash;&gt;-->
                            <!--&lt;!&ndash;<vs-dropdown class="dropdown" style="display: flex;width: 100%" vs-custom-content vs-trigger-click>&ndash;&gt;-->
                                <!--&lt;!&ndash;<a class="a-icon" href.prevent>&ndash;&gt;-->
                                    <!--&lt;!&ndash;{{getFontName(text.fontFamily)}}&ndash;&gt;-->
                                    <!--&lt;!&ndash;<vs-icon class="" icon="expand_more"></vs-icon>&ndash;&gt;-->
                                <!--&lt;!&ndash;</a>&ndash;&gt;-->

                                <!--&lt;!&ndash;<vs-dropdown-menu >&ndash;&gt;-->
                                    <!--&lt;!&ndash;<vs-dropdown-item v-on:click="onTextChangeFont(text,'MS Mincho')">&ndash;&gt;-->
                                        <!--&lt;!&ndash;ＭＳ 明朝&ndash;&gt;-->
                                    <!--&lt;!&ndash;</vs-dropdown-item>&ndash;&gt;-->
                                    <!--&lt;!&ndash;<vs-dropdown-item v-on:click="onTextChangeFont(text,'MS Gothic')">&ndash;&gt;-->
                                        <!--&lt;!&ndash;ＭＳ ゴシック&ndash;&gt;-->
                                    <!--&lt;!&ndash;</vs-dropdown-item>&ndash;&gt;-->
                                    <!--&lt;!&ndash;<vs-dropdown-item v-on:click="onTextChangeFont(text,'Meiryo')">&ndash;&gt;-->
                                        <!--&lt;!&ndash;メイリオ&ndash;&gt;-->
                                    <!--&lt;!&ndash;</vs-dropdown-item>&ndash;&gt;-->
                                <!--&lt;!&ndash;</vs-dropdown-menu>&ndash;&gt;-->
                            <!--&lt;!&ndash;</vs-dropdown>&ndash;&gt;-->
                        <!--&lt;!&ndash;</vs-col>&ndash;&gt;-->
                        <!--&lt;!&ndash;<vs-col vs-type="flex" vs-w="3" style="margin-left: 1px ; border: 1px solid #0984e3">&ndash;&gt;-->
                            <!--&lt;!&ndash;<vs-dropdown class="dropdown" style="display: flex;width: 100%" vs-custom-content vs-trigger-click>&ndash;&gt;-->
                                <!--&lt;!&ndash;<a class="a-icon" href.prevent>&ndash;&gt;-->
                                    <!--&lt;!&ndash;{{Math.round(text.fontSize/(1.333333333*($store.state.home.fileSelected.zoom / 100)))}}&ndash;&gt;-->
                                    <!--&lt;!&ndash;<vs-icon class="" icon="expand_more"></vs-icon>&ndash;&gt;-->
                                <!--&lt;!&ndash;</a>&ndash;&gt;-->
                                <!--&lt;!&ndash;<vs-dropdown-menu>&ndash;&gt;-->
                                    <!--&lt;!&ndash;<vs-dropdown-item v-on:click="onTextChangeFontSize(text, fontSize)" v-for="(fontSize,index) in fontSizeOptions" :key="index">&ndash;&gt;-->
                                        <!--&lt;!&ndash;{{fontSize}}&ndash;&gt;-->
                                    <!--&lt;!&ndash;</vs-dropdown-item>&ndash;&gt;-->
                                <!--&lt;!&ndash;</vs-dropdown-menu>&ndash;&gt;-->
                            <!--&lt;!&ndash;</vs-dropdown>&ndash;&gt;-->
                        <!--&lt;!&ndash;</vs-col>&ndash;&gt;-->
                    <!--&lt;!&ndash;</vs-row>&ndash;&gt;-->
                <!--&lt;!&ndash;</v-popover>&ndash;&gt;-->
                <!--<a class="button delete delete-text" v-on:click="onDeleteTextClick(text)"><i class="fas fa-times"> </i></a>-->
            <!--</vs-row>-->
        <!--</div>-->
        <div class="stamps-confirm-modal" style="display: none;">
            <vs-row vs-type="flex" vs-align="center" vs-justify="center">
                <vs-button class="square mr-0 bg-primary" type="filled" v-on:click="cancelStamp"><div><img :src="require('@assets/images/mobile/cancle_white.svg')"></div> キャンセル</vs-button>
                <vs-button class="square mr-0 bg-primary" type="filled" v-on:click="confirmStamp"><div><img :src="require('@assets/images/mobile/confirm_white.svg')"></div> 決定</vs-button>
            </vs-row>
        </div>
    </div>
</template>

<script>
    import { mapState, mapActions } from "vuex";
    //import Konva from "konva/konva";

export default {
  name: 'pdf-page-editor-mobile',
  props: {
    config              : { type: Object},
    page                : { type: Object},
    imageUrl            : { type: String},
    selected            : { type: Boolean },
    isPublic            : { type: Boolean, default: false},
    enable              : { type: Boolean, default: true},
    deleteFlg           : { type: Boolean, default: false},
    deleteWatermark     : { type: String, default: ''},
    isExternal  : { type: Boolean, default: false},
    showEdit  : { type: Boolean, default: true},
    stamps              : { type: Array},
  },
  data() {
    return {
      openPopover: false,
      selectedId: 0,
      stampIndex: 0,
      textIndex: 2,
      font: '0',
      textIdSelected: 0,
      fontSizeOptions: [8,9,10,11,12,14,16,18,20,22,24,26,28,36,42,72],
      realScale: 0,
      maskBase64: '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',
      maskBase64Landscape: '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',
      confirmIndex: 0,
      confirmFlag: false,
      startX: 0,
      moveEndX: 0,
      dragStartPonitX: 0,
      dragStartPonitY: 0,
      pinchInOutX0 : 0,
      pinchInOutX1 : 0,
      pinchInOutY0 : 0,
      pinchInOutY1 : 0,
      isDraggable: true,            // draggableの値
      pdfShiftDistanceX: 0,     // draggableで移動したPDFズレ X軸
      pdfShiftDistanceY: 0,     // draggableで移動したPDFズレ Y軸
        selectedStamp: {},
      defaultFontColor:"#000000",
    }
  },
  computed: {
    ...mapState({
      // stamps: state => state.home.stamps,
      stampSelected: state => state.home.stampSelected,
      isTextSelected: state => state.home.textSelected,
      fileSelected: state => state.home.fileSelected,
    }),
    zoomChanged: function () {
      if(this.$store.fileSelected == null) {
        return -1;
      }
      return this.$store.fileSelected.zoom;
    },
    isConfidential: function() {
      if(this.$store.state.home.fileSelected == null) {
        return false;
      }
      return this.$store.state.home.fileSelected.confidential_flg;
    },
    isOverlayHidden:function(){
      if(this.$store.state.home.fileSelected == null) {
        return false;
      }
      return this.$store.state.home.fileSelected.overlay_hidden_flg;

    },
    text: function() {
      return this.page.texts.find(item => item.index === this.textIdSelected);
    },
    useKonva() {
      const page = this.page;
      return this.selected || page.stamps.length > 0 || page.texts.length > 0;
    }
  },
  methods: {
    ...mapActions({
      addFileStamp: "home/addFileStamp",
      updateFileStamp: "home/updateFileStamp",
      deleteFileStamp: "home/deleteFileStamp",
      addFileText: "home/addFileText",
      updateFileText: "home/updateFileText",
      deleteFileText: "home/deleteFileText",
      undoAction: "home/undoAction",
    }),
    createImageFromUrl: function(url) {
      const image = new Image();
      image.src = url;
      return image;
    },
    createImage: function(base64) { 
      if(!base64) {
        return new Image();
      }
      return this.createImageFromUrl(`data:image/png;base64,${base64}`);
    },
    createStampImage: function(stampId) {
      if(!stampId) {
        return new Image();
      }
      const stamp = this.stamps.find(item=> item.id === stampId);
      let image = new Image();
      if(stamp) image.src = `data:image/png;base64,${stamp.url}`;
      return image;
    },
    touchEditorClick: function(e){
        if(e.target.attrs.type == "Stamp"){
            this.selectedStamp = this.page.stamps.find(s => s.index == e.target.attrs.id);
        }
        this.confirmFlag = false;
        this.startX = 0;
        this.moveEndX = 0;

        // 二本指タッチ時、ドラッグフラグをfalseにする
        if (e.evt.touches.length === 2 || this.isDraggable === false){
            this.isDraggable = false;
            return;
        }
        // 一本指タッチ時、ドラッグ距離を計算する
        if (e.evt.touches.length === 1){
            this.dragStartPonitX = e.evt.touches[0].clientX;
            this.dragStartPonitY = e.evt.touches[0].clientY;
        }

        if(!this.enable) return;
        if(!this.showEdit){
            // get start x
            this.startX = e.evt.touches[0].pageX;
            return;
        }
        if(this.isPublic && this.selectedId === 0) {
            this.stampIndex++;
            const callback = () => {
                this.selectedId = this.stampIndex;
                this.loadStamps();
            }
            this.$emit('generateStamp',{event: e, page: this.page, realScale: this.realScale, stampIndex: this.stampIndex, selectedId: this.selectedId, callback:callback});
            return;
        }

        let stage = this.$refs.stage.getStage();
        let layer = stage.children[0];

        const scale = this.$store.state.home.fileSelected.zoom / 100;
        if(this.isTextSelected) {
            const targetId = e.target.attrs.id;
            if (e.target.attrs.type === 'Text') {
                this.textIdSelected = targetId;
                return;
            }
            if(this.textIdSelected > 0) {
                this.textIdSelected = 0;
                return;
            }
        }
    },
    confirmTouchEditor: function(e) {
        if(e.target.attrs.type == "Stamp"){
            const scale = this.$store.state.home.fileSelected.zoom / 100;
            if(scale < 1){
                let canvas = this.$el.querySelector(`canvas`);
                let canvasClientWidth = canvas.clientWidth;
                let canvasClientHeight = canvas.clientHeight;
                let mobileWidth = document.getElementsByClassName('router-view')[0].clientWidth;
                let navHeight = document.getElementsByClassName('sp-navi')[0].clientHeight;
                //let scrollTop = document.documentElement.scrollTop || document.body.scrollTop;
                const maxPageX = canvasClientWidth * scale + (mobileWidth - canvasClientWidth) / 2 - 10;
                const maxPageY = canvasClientHeight * scale + navHeight + 20;
                //const maxPageY = canvasClientHeight * scale + navHeight + 150;
                if(e.evt.changedTouches[0].pageX > maxPageX || e.evt.changedTouches[0].pageY > maxPageY){
                    this.updateFileStamp({stamp:{
                            ...this.selectedStamp
                        }, pageno: this.page.no});
                    this.loadStamps();
                }
            }
        }
        // ドラッグ距離の計算
        const distance = this.getDistance(e.evt.changedTouches[0].clientX, e.evt.changedTouches[0].clientY, this.dragStartPonitX, this.dragStartPonitY)

        // 初期化
        this.dragStartPonitX = null;
        this.dragStartPonitY = null;
        this.pinchInOutX0 = null;
        this.pinchInOutX1 = null;
        this.pinchInOutY0 = null;
        this.pinchInOutY1 = null;

        // 編集時でタッチ終了時にドラッグフラグを立てる
        /*if (this.showEdit){
            var ele = document.getElementsByClassName('stamps-confirm-modal');
            // 決定、キャンセルモーダルが表示されていない場合のみドラッグフラグを立てる
            if (ele[0].outerHTML.indexOf('display: none;') !== -1){
                this.isDraggable = true;
            }
        }*/

        /*if(!this.isTextSelected && !this.isExternal && this.showEdit) {
            if(e.evt.changedTouches.length === 1 && distance < 5){
                if (typeof this.pdfShiftDistanceX === "undefined"){
                    this.pdfShiftDistanceX = 0;
                    this.pdfShiftDistanceY = 0;
                }

                $(".stamps-confirm-modal").show();
                this.isDraggable = false;
                this.confirmIndex = e.target.id();
            }
        }*/
        var ele = document.getElementsByClassName('stamps-confirm-modal');
        // 決定、キャンセルモーダルが表示されていない場合のみドラッグフラグを立てる
        if (ele[0].outerHTML.indexOf('display: none;') !== -1){
            this.isDraggable = true;
        }

        if(!this.isTextSelected && !this.isExternal) {
            if(e.evt.changedTouches.length === 1 && distance < 5 && this.showEdit){
                if (typeof this.pdfShiftDistanceX === "undefined"){
                    this.pdfShiftDistanceX = 0;
                    this.pdfShiftDistanceY = 0;
                }

                $(".stamps-confirm-modal").show();
                this.isDraggable = false;
                this.confirmIndex = e.target.id();
            }
        }
        if(!this.isTextSelected){
            // 再度タップでも捺印
            if (this.confirmFlag) {
                this.confirmStamp();
            }
        }
        if(!this.showEdit){
            // 1 not touchmove 2 android startX not equals moveEndX
            if(this.moveEndX !== 0 && this.moveEndX !== this.startX){
                if(this.moveEndX > this.startX){
                    this.$emit('prev');
                }else if(this.moveEndX < this.startX){
                    this.$emit('next');
                }
            }else{
                this.$emit('func');
                return;
            }
        }

        // ドラッグしていない場合
        if (distance < 5){
            // スタンプ選択時、かつドラッグしていない場合、スタンプを押下する
            if(!this.isTextSelected) {
                const scale = this.$store.state.home.fileSelected.zoom / 100;
                let canvas = this.$el.querySelector(`canvas`);
                let canvasClientWidth = canvas.clientWidth;
                let canvasClientHeight = canvas.clientHeight;
                let navHeight = document.getElementsByClassName('sp-navi')[0].clientHeight;
                let mobileWidth = document.getElementsByClassName('router-view')[0].clientWidth;
                //let stampWidth = this.stampSelected.width * scale;
                if(scale < 1 && e.evt.changedTouches[0].pageX > (canvasClientWidth * scale + (mobileWidth - canvasClientWidth) / 2) - 10
                    || e.evt.changedTouches[0].pageY > canvasClientHeight * scale + navHeight + 20){
                    //|| e.evt.changedTouches[0].pageY > canvasClientHeight * scale + navHeight + 150){
                    this.isTextSelected = true;
                    $(".stamps-confirm-modal").hide();
                    return false;
                }
                let y = e.evt.changedTouches[0].clientY;
                // set stamps-confirm-modal position
                if(window.innerHeight - y < 190){
                    $(".stamps-confirm-modal").css("bottom",190 + this.stampSelected.width * this.realScale);
                }else{
                    $(".stamps-confirm-modal").css("bottom",120);
                }
                if(this.selectedId > 0) {
                    if(e.target.id() === this.selectedId) {
                        // 再度タップでも捺印
                        this.confirmFlag = true;
                    }
                    return;
                }
                this.stampIndex++;
                let topDistance = document.getElementsByClassName('sp-navi')[0].offsetHeight + 20;
                //let topDistance = document.getElementsByClassName('sp-navi')[0].offsetHeight + 140;
                //const stamp = {id: this.stampSelected.id,url:this.stampSelected.url, width: this.stampSelected.width, height: this.stampSelected.height, x:e.evt.offsetX, y:e.evt.offsetY};
                const stampWithoutUrl = {
                    index: this.stampIndex,
                    id: this.stampSelected.id,
                    width: this.stampSelected.width,
                    height: this.stampSelected.height,
                    x: ((e.evt.changedTouches[0].pageX - (this.stampSelected.width * scale) / 2) / scale) / this.realScale - this.pdfShiftDistanceX,
                    //y: ((e.evt.changedTouches[0].pageY - (this.stampSelected.height * scale) / 2 - 220) / scale) / this.realScale - this.pdfShiftDistanceY,
                    y: ((e.evt.changedTouches[0].pageY - (this.stampSelected.height * scale) / 2 - topDistance) / scale) / this.realScale - this.pdfShiftDistanceY,
                    scaleX: 1,
                    scaleY: 1,
                    rotation: 0,
                    selected: true,
                    clientY: e.evt.changedTouches[0].clientY,
                };
                //thisq.page.stamps.push(stamp);
                this.addFileStamp({stamp: stampWithoutUrl, pageno: this.page.no});
                this.selectedId = this.stampIndex;

                //Draw new stamp with selected
                
                let stage = this.$refs.stage.getStage();
                let layer = stage.children[0];
                let groups = layer.find('Group').toArray();
                let group = groups[0];

                let stampNode = new Konva.Image({
                    image: this.createStampImage(stampWithoutUrl.id),
                    id: stampWithoutUrl.index,
                    draggable: stampWithoutUrl.selected,
                    width: stampWithoutUrl.width * this.realScale,
                    height: stampWithoutUrl.height * this.realScale,
                    x: stampWithoutUrl.x * this.realScale,
                    y: stampWithoutUrl.y * this.realScale,
                    scaleX: stampWithoutUrl.scaleX,
                    scaleY: stampWithoutUrl.scaleY,
                    rotation: stampWithoutUrl.rotation,
                    stroke: '#0984e3',
                    strokeWidth: (stampWithoutUrl.selected ? 2: 0),
                    type: 'Stamp',
                    clientY: stampWithoutUrl.clientY,
                });

                group.add(stampNode);
                layer.draw();
            } else if (this.isTextSelected){
                let stage = this.$refs.stage.getStage();
                let layer = stage.children[0];
                const scale = this.$store.state.home.fileSelected.zoom / 100;
                const targetId = e.target.attrs.id;
                if (e.target.attrs.type === 'Text') {
                    this.textIdSelected = targetId;
                    return;
                }
                if(this.textIdSelected > 0) {
                    this.textIdSelected = 0;
                    return;
                }
                this.textIndex++;
                this.textIdSelected = this.textIndex;

                let textNode = new Konva.Text({
                    id: this.textIndex,
                    text: '',
                    x: e.evt.changedTouches[0].pageX / scale - 20 - this.pdfShiftDistanceX,
                    y: e.evt.changedTouches[0].pageY / scale - 90 - this.pdfShiftDistanceY,
                    //y: e.evt.changedTouches[0].pageY / scale - 220 - this.pdfShiftDistanceY,PAC_5-1922テキストエリア
                    fontSize: Math.round(20*1.333333333*scale* this.realScale),
                    fontFamily: 'shnmin',
                    fontColor: '#000000',
                    draggable: true,
                    width: 50,
                    type: 'Text'
                });
                textNode.on('mouseover', function () {
                    stage.container().style.cursor = 'move';
                });

                textNode.on('mouseout', function () {
                    stage.container().style.cursor = 'default';
                });

                textNode.on('transform', function () {
                    // reset scale, so only with is changing by transformer
                    textNode.setAttrs({
                        width: textNode.width() * textNode.scaleX(),
                        scaleX: 1
                    });
                });
                const text = {
                    index: textNode.id(),
                    text: textNode.text(),
                    x: textNode.x() / this.realScale,
                    y: textNode.y() / this.realScale,
                    fontSize: textNode.fontSize() / this.realScale,
                    fontFamily: textNode.fontFamily(),
                    width: textNode.width() / this.realScale,
                    scaleX: textNode.scaleX(),
                    scaleY: textNode.scaleY(),
                    rotation: textNode.rotation()
                };
                this.addFileText({text: text, pageno: this.page.no});
                this.onTextDblClick(stage,textNode, layer, true);
                this.$emit('hideBtn');
            }
        }
    },
    moveToChangePage:function(e){
        // 二本指の場合、ピンチアウト、ピンチイン
        if (e.evt.touches.length === 2) {
            if (this.pinchInOutX0 == null){
                this.pinchInOutX0 = e.evt.touches[0].pageX;
            }
            if (this.pinchInOutX1 == null){
                this.pinchInOutX1 = e.evt.touches[1].pageX;
            }
            if (this.pinchInOutY0 == null){
                this.pinchInOutY0 = e.evt.touches[0].pageY;
            }
            if (this.pinchInOutY1 == null){
                this.pinchInOutY1 = e.evt.touches[1].pageY;
            }

            if(this.getDistance(e.evt.touches[0].pageX, e.evt.touches[0].pageY, e.evt.touches[1].pageX, e.evt.touches[1].pageY) < this.getDistance(this.pinchInOutX0, this.pinchInOutY0, this.pinchInOutX1 , this.pinchInOutY1)){
                if(this.$store.state.home.fileSelected.zoom - 8 < 75){
                    this.$store.state.home.fileSelected.zoom = 75;
                }else{
                    this.$store.state.home.fileSelected.zoom = this.$store.state.home.fileSelected.zoom - 10;
                }
            }else{
                if(this.$store.state.home.fileSelected.zoom + 8 > 200){
                    this.$store.state.home.fileSelected.zoom = 200;
                }else{
                    this.$store.state.home.fileSelected.zoom = this.$store.state.home.fileSelected.zoom + 10;
                }
            }
        }
        /*if(!this.showEdit){
            this.moveEndX = e.evt.touches[0].pageX;
        }
        // 編集モード時
        else{
            // 二本指の場合、ピンチアウト、ピンチイン
            if (e.evt.touches.length === 2) {
                if (this.pinchInOutX0 == null){
                    this.pinchInOutX0 = e.evt.touches[0].pageX;
                }
                if (this.pinchInOutX1 == null){
                    this.pinchInOutX1 = e.evt.touches[1].pageX;
                }
                if (this.pinchInOutY0 == null){
                    this.pinchInOutY0 = e.evt.touches[0].pageY;
                }
                if (this.pinchInOutY1 == null){
                    this.pinchInOutY1 = e.evt.touches[1].pageY;
                }

                if(this.getDistance(e.evt.touches[0].pageX, e.evt.touches[0].pageY, e.evt.touches[1].pageX, e.evt.touches[1].pageY) < this.getDistance(this.pinchInOutX0, this.pinchInOutY0, this.pinchInOutX1 , this.pinchInOutY1)){
                    if(this.$store.state.home.fileSelected.zoom - 8 < 75){
                        this.$store.state.home.fileSelected.zoom = 75;
                    }else{
                        this.$store.state.home.fileSelected.zoom = this.$store.state.home.fileSelected.zoom - 10;
                    }
                }else{
                    if(this.$store.state.home.fileSelected.zoom + 8 > 200){
                        this.$store.state.home.fileSelected.zoom = 200;
                    }else{
                        this.$store.state.home.fileSelected.zoom = this.$store.state.home.fileSelected.zoom + 10;
                    }
                }
            }
        }*/
    },
    // 距離計算
    getDistance: function(p1x, p1y, p2x, p2y) {
        var x = p2x - p1x,
            y = p2y - p1y;

        return Math.sqrt((x * x) + (y * y));
    },
    cancelStamp: function(){
        $(".stamps-confirm-modal").hide();
        this.isDraggable = true;
        this.undoAction();
        this.updateFileStamp({stamp: {index:this.selectedId, selected: false}, pageno: this.page.no});
        this.selectedId = 0;
        this.loadStamps();
        return;
    },
    confirmStamp: function(){
        $(".stamps-confirm-modal").hide();
        this.isDraggable = true;
        this.updateFileStamp({stamp: {index:this.selectedId, selected: false}, pageno: this.page.no});
        this.selectedId = 0;
        this.loadStamps();
        return;
    },
    onStampClick: function(e) {
        //this.selectedId = e.currentTarget.attrs.id;
        //this.$refs.stage.draw();

    },
    handleDragstart(e) {
        this.confirmFlag = false;
    },
    handleDragend(e) {
        // ドラッグした距離を格納
        this.pdfShiftDistanceX = e.currentTarget.attrs.x;
        this.pdfShiftDistanceY = e.currentTarget.attrs.y;

        if(e.target.attrs.type === 'Stamp' && e.evt) {
            this.doUpdateStamp(e);
        }
        else if(e.target.attrs.type === 'Text') {
            this.doUpdateText(e.target);
            let stage = this.$refs.stage.getStage();
            let layer = stage.children[0];
            setTimeout(()=> {
                this.onTextDblClick(stage,e.target, layer, false);
                this.$emit('hideBtn');
            },100);

        }
        // 以下でPDFドラッグ時の位置調整を行う　(draggble=trueの場合の処理)
        else{
            let stage = this.$refs.stage.getStage();
            const layer = stage.children[0];
            const scale = this.$store.state.home.fileSelected.zoom / 100;

            // X軸の位置調整
            if (this.pdfShiftDistanceX >= 0 || scale < 1){
                this.pdfShiftDistanceX = 0;
            }
            else if (scale >= 1 && this.config.width * scale + this.pdfShiftDistanceX < this.config.width){
                this.pdfShiftDistanceX = this.config.width - this.config.width * scale;
            }

            // Y軸の位置調整
            if (this.pdfShiftDistanceY >= 0 || scale < 1){
                this.pdfShiftDistanceY  = 0;
            }
            else if (scale >= 1 && this.config.height * scale + this.pdfShiftDistanceY < this.config.height){
                this.pdfShiftDistanceY = this.config.height - this.config.height * scale;
            }

            stage.x(this.pdfShiftDistanceX);
            stage.y(this.pdfShiftDistanceY);
            layer.draw();
        }
    },
    doUpdateText: function(textNode) {
        const text = {
            index: textNode.id(),
            text: textNode.text(),
            x: textNode.x() / this.realScale,
            y: textNode.y() / this.realScale,
            fontSize: textNode.fontSize() / this.realScale,
            fontFamily: textNode.fontFamily(),
            width: textNode.width() / this.realScale,
            scaleX: textNode.scaleX(),
            scaleY: textNode.scaleY(),
            rotation: textNode.rotation()
        };

        this.updateFileText({text: text, pageno: this.page.no});
    },
    doUpdateStamp: function(e) {
        const stamp = {
            index: e.target.attrs.id,
            x: e.target.x() / this.realScale,
            y: e.target.y() / this.realScale,
            width: e.target.width() / this.realScale,
            height: e.target.height() / this.realScale,
            scaleX: e.target.scaleX(),
            scaleY: e.target.scaleY(),
            rotation: e.target.rotation(),
            selected: true,
        };
        this.updateFileStamp({stamp:stamp, pageno: this.page.no});
    },
    onStageClick: function (e) {

    },
    loadTexts: function (draggable) {
        if(!this.$refs.stage) {
            return;
        }

        let stage = this.$refs.stage.getStage();
        let layer = stage.children[0];
        this.textIndex = 2;
        layer.find('Text').destroy();
        for(let text of this.page.texts) {
            this.textIndex = text.index;
            let textNode = new Konva.Text({
                id: this.textIndex,
                text: text.text,
                x: text.x * this.realScale,
                y: text.y * this.realScale,
                fontSize: text.fontSize* this.realScale,
                fontFamily: text.fontFamily,
                draggable: draggable,
                width: text.width * this.realScale,
                scaleX: text.scaleX,
                scaleY: text.scaleY,
                rotation: text.rotation,
                type: 'Text'
            });

            //textNode.zIndex(-1);
            if(textNode.attrs.id === this.textIdSelected) {
                textNode.hide();
            }

            layer.add(textNode);

            textNode.on('dragstart', (e) => {
                this.textIdSelected = 0;
            });

            textNode.on('dragend', (e) => {
                this.textIdSelected = e.target.id();
            });

            textNode.on('mouseover', function () {
                stage.container().style.cursor = 'move';
            });

            textNode.on('mouseout', function () {
                stage.container().style.cursor = 'default';
            });

            textNode.on('transform', function () {
                // reset scale, so only with is changing by transformer
                textNode.setAttrs({
                    width: textNode.width() * textNode.scaleX(),
                    scaleX: 1
                });
            });

            layer.draw();

            textNode.on('click', () => {

                this.onTextDblClick(stage,textNode, layer, false);
            });
            const $this = this;
            textNode.on('transformend', function (e) {
                $this.doUpdateText(e.target);
            });
        }
    },
    loadStamps: function () {
        if(!this.$refs.stage) {
            return;
        }
        let stage = this.$refs.stage.getStage();
        let layer = stage.children[0];
        layer.find('Group').destroy();
        const group = new Konva.Group();
        layer.add(group);

        if (typeof this.pdfShiftDistanceX === "undefined"){
            this.pdfShiftDistanceX = 0;
            this.pdfShiftDistanceY = 0;
        }

        ///const mask = group.find('Image').toArray().find(item => item.attrs.type === 'Mask');
        //if(mask) mask.destroy();
        for(let stamp of this.page.stamps) {
            let stampNode = new Konva.Image({
                image: this.createStampImage(stamp.id),
                id: stamp.index,
                draggable: stamp.selected,
                width: stamp.width * this.realScale,
                height: stamp.height * this.realScale,
                x: stamp.x * this.realScale,
                y: stamp.y * this.realScale,
                scaleX: stamp.scaleX,
                scaleY: stamp.scaleY,
                rotation: stamp.rotation,
                stroke: '#0984e3',
                strokeWidth: (stamp.selected ? 2: 0),
                type: 'Stamp'
            });

            group.add(stampNode);
        }
        if(this.isConfidential && !this.isOverlayHidden) {
            const image1 = new Konva.Image({
                image: this.createImage((this.config.width < this.config.height ? this.maskBase64 : this.maskBase64Landscape)),
                id: 0,
                draggable: false,
                width: this.config.width,
                height: this.config.height,
                type: 'Mask',
            });

            group.add(image1);
        }
        if(this.deleteFlg && this.deleteWatermark) {
            const image1 = new Konva.Image({
                image: this.createImage(this.deleteWatermark),
                id: 0,
                draggable: false,
                width: this.config.width,
                height: this.config.height,
                type: 'Mask',
            });

            group.add(image1);
        }

        //layer.add(group);
        layer.draw();
        setTimeout(()=> {
            //layer.draw();
        },100);

    },
    onStampTransform: function (e) {
        this.doUpdateStamp(e);

    },
    reloadStage:function() {
        //this.realScale = this.config.width / this.page.width;
        if(!this.$refs.stage) {
            return;
        }
        let stage = this.$refs.stage.getStage();
        const scale = this.$store.state.home.fileSelected.zoom / 100;

        stage.width(this.config.width);
        stage.height(this.config.height);
        stage.scale({ x: scale, y: scale });
        stage.draw();
    },
    onTextDblClick: function(stage,textNode,layer,isAdd) {
        if(!this.isTextSelected) {
            return;
        }

        const $this = this;
        const scale = this.$store.state.home.fileSelected.zoom / 100;
        // hide text node and transformer:
        stage.find(node => node.attrs.type === 'Text' && (node.attrs.id === textNode.attrs.id || node.attrs.id === this.textIdSelected)).hide();
        layer.draw();

        // create textarea over canvas with absolute position
        // first we need to find position for textarea
        // how to find it?

        // at first lets find position of text node relative to the stage:
        let textPosition = textNode.absolutePosition();

        // then lets find position of stage container on the page:
        let stageBox = stage.container().getBoundingClientRect();

        // so position of textarea will be the sum of positions above:
        let areaPosition = {
            /*x: stageBox.left + textPosition.x,
            y: stageBox.top + textPosition.y*/
            x: textPosition.x,
            y: textPosition.y
        };

        // create textarea and style it
        let textarea = document.createElement('textarea');
        //document.body.appendChild(textarea);
        this.$refs.wrap.appendChild(textarea)

        // apply many styles to match text on canvas as close as possible
        // remember that text rendering on canvas and on the textarea can be different
        // and sometimes it is hard to make it 100% the same. But we will try...
        textarea.value = textNode.text();
        textarea.placeholder = 'あa';
        textarea.rows = 1;
        textarea.wrap = 'off';
        textarea.style.position = 'absolute';
        textarea.style.top = (areaPosition.y - 5 + this.pdfShiftDistanceY) * scale + 'px';
        textarea.style.left = (areaPosition.x - 5 + this.pdfShiftDistanceX) * scale + 'px';
        textarea.style.width = textNode.width() - textNode.padding() * 2 + 'px';
        textarea.style.height = textNode.height() - textNode.padding() + 5 + 'px';
        textarea.style.fontSize = '16px';
        textarea.style.border = 'none';
        textarea.style.margin = '0px';
        textarea.style.overflow = 'hidden';
        textarea.style.background = 'none';
        textarea.style.outline = 'none';
        textarea.style.resize = 'none';
        textarea.style.lineHeight = textNode.lineHeight();
        textarea.style.fontFamily = textNode.fontFamily();
        textarea.style.transformOrigin = 'left top';
        textarea.style.textAlign = textNode.align();
        textarea.style.color = textNode.fill();
        textarea.style.padding = '5px';
        textarea.style.border = '2px solid #0984e3';
        textarea.style.whiteSpace = 'pre';
        textarea.style.overflowWrap = 'anywhere';
        let rotation = textNode.rotation();
        let transform = '';
        if (rotation) {
            transform += 'rotateZ(' + rotation + 'deg)';
        }

        let px = 0;
        // also we need to slightly move textarea on firefox
        // because it jumps a bit
        let isFirefox =
            navigator.userAgent.toLowerCase().indexOf('firefox') > -1;
        if (isFirefox) {
            px += 2 + Math.round(textNode.fontSize() / 20);
        }
        transform += 'translateY(-' + px + 'px)';

        textarea.style.transform = transform;

        // reset height
        textarea.style.height = 'auto';
        // after browsers resized it we can set actual value
        textarea.style.height = textarea.scrollHeight + 3 + 'px';

        textarea.focus();

        function removeTextarea() {
            //$this.textIdSelected = 0;
            textarea.parentNode.removeChild(textarea);
            window.removeEventListener('touchstart', handleOutsideClick);
            stage.find(node => node.attrs.type === 'Text' && node.attrs.id === textNode.attrs.id).show();
            layer.draw();
            $this.$emit('showBtn');
        }

        function setTextareaWidth(newWidth) {
            if (!newWidth) {
                // set width for placeholder
                newWidth = textNode.placeholder.length * textNode.fontSize();
            }
            // some extra fixes on different browsers
            let isSafari = /^((?!chrome|android).)*safari/i.test(
                navigator.userAgent
            );
            let isFirefox =
                navigator.userAgent.toLowerCase().indexOf('firefox') > -1;
            if (isSafari || isFirefox) {
                newWidth = Math.ceil(newWidth);
            }

            let isEdge =
                document.documentMode || /Edge/.test(navigator.userAgent);
            if (isEdge) {
                newWidth += 1;
            }
            textarea.style.width = newWidth + 'px';
        }

        textarea.addEventListener('focus', function (e) {
            $this.openPopover = false;
        });

        textarea.addEventListener('keydown', function (e) {
            let scale = textNode.getAbsoluteScale().x;
            $(this).width(10).height(10).width(this.scrollWidth).height(this.scrollHeight);
            let inputWidth = this.scrollWidth;
            textNode.width((inputWidth + 30 ));
            setTextareaWidth((inputWidth + 30 ) * scale);
            //const optionsElement = document.getElementById(`text-${textNode.attrs.id}`);
            //optionsElement.style.left = `${textNode.x() + ((inputWidth + 30) * scale) - 45}px`;
        });

        function handleOutsideClick(e) {
            if (e.target !== textarea) {
                textNode.text(textarea.value);
                if(e.target.className === 'vs-con-dropdown parent-dropdown button config') {
                    const text = stage.find(node => node.attrs.type === 'Text' && node.attrs.id === textNode.attrs.id);
                    text.text(textarea.value);
                    text.width(textNode.width());
                    layer.draw();
                }
                if(isAdd) {
                    const text = $this.page.texts.find(item => item.index === textNode.id());
                    if (text){
                        textNode.fontFamily(text.fontFamily);
                        textNode.fontSize(text.fontSize*$this.realScale);

                        $this.applyText(textNode);
                    }
                    if(!textarea.value && !$(e.target).hasClass('vs-con-dropdown parent-dropdown dropdown') && !$(e.target).hasClass('vs-dropdown--item-link')
                        && !$(e.target).hasClass('tooltip-target')&& $(e.target).closest('.config').length <= 0 ) {
                        if(e.target.className !== 'button delete delete-text') $this.deleteFileText({text: text, pageno: $this.page.no});
                    }
                }
                if(e.target.className !== 'vs-con-dropdown parent-dropdown dropdown' && e.target.className !== 'vs-dropdown--item-link'
                    && $(e.target).closest('.config').length <= 0 ) {
                    if($(e.target).closest('.vs-dropdown--item').length <= 0) $this.doUpdateText(textNode)
                    removeTextarea();
                    $this.openPopover = false;
                }
            }
        }

        setTimeout(() => {
            window.addEventListener('touchstart', handleOutsideClick);
        });
    },
    onTextChangeFont(text, fontFamily) {
        const stage = this.$refs.stage.getStage();
        const layer = stage.children[0];
        if(!stage) {
            return;
        }

        const nodeText = layer.find('Text').toArray().find(item => item.attrs.id == text.index);
        if(!nodeText) {
            return;
        }
        nodeText.fontFamily(fontFamily);
        layer.draw();

        this.applyText(nodeText);
    },
    onTextChangeFontSize(text, fontSize) {
        const stage = this.$refs.stage.getStage();
        const layer = stage.children[0];
        if(!stage) {
            return;
        }
        const nodeText = layer.find('Text').toArray().find(item => item.attrs.id == text.index);
        if(!nodeText) {
            return;
        }
        nodeText.fontSize(Math.round(fontSize*1.333333333*(this.$store.state.home.fileSelected.zoom / 100) * this.realScale));
        layer.draw();

        this.applyText(nodeText);
    },
    applyText(nodeText){
        const fakeEl = $('<span>').hide().appendTo(document.body);
        let html = nodeText.text();
        html = html.replace(/[\r\n]/g, "<br />");
        fakeEl.html(html).css('font-size', nodeText.fontSize());
        fakeEl.css('font-family', nodeText.fontFamily());
        const width = fakeEl.width();
        nodeText.width(width + 30);
        nodeText.height(fakeEl.height());

        this.doUpdateText(nodeText);
        fakeEl.remove();
    },
    getFontName(fontFamily) {
        let fontName = '';
        if(fontFamily === 'shnmin') {
            fontName = '明朝体';
        }else if(fontFamily === 'shnkgo') {
            fontName = '角ゴシック体';
        }else if(fontFamily === 'shgyo') {
            fontName = '行書体';
        }
        return fontName;
    },
    onDeleteTextClick: function (text) {
        const stage = this.$refs.stage.getStage();
        const layer = stage.children[0];
        if(!stage) {
            return;
        }
        const nodeText = layer.find('Text').toArray().find(item => item.attrs.id == text.index);
        if(!nodeText) {
            return;
        }
        nodeText.destroy();
        layer.draw();
        this.textIdSelected = 0;
        this.deleteFileText({text: text, pageno: this.page.no});
    },
    onWindowClick: function(e) {
        if(!this.isTextSelected && this.selectedId && $(e.target).prop("tagName").toUpperCase() !== 'CANVAS' && !this.isPublic) {
            if($(e.target).closest('.submit-circular').length > 0) return;
            if($(e.target).closest('.submit-circular2').length > 0) return;
            $(".stamps-confirm-modal").hide();
            this.isDraggable = true;
            this.undoAction();
            this.selectedId = 0;
            this.textIdSelected = 0;
        }
    },
    exportToImage: function() {
        const stage = this.$refs.stage.getStage();
        if(stage) {
            return stage.toDataURL();
        }
        return null;
    }

    },
    mounted() {
        this.stampIndex = this.page.stamps.length + 1;
        this.loadTexts(this.isTextSelected);
        this.loadStamps();
        this.reloadStage();
    },
    created() {
        this.realScale = this.config.width / this.page.width;
        window.addEventListener('click', this.onWindowClick);
    },
    updated() {
        this.reloadStage();
        this.loadStamps();
    },
    watch: {
        "$store.state.home.fileSelected.zoom": function (newVal, oldVal) {
            this.reloadStage();
        },
        "$store.state.home.fileSelected.confidential_flg": function (newVal, oldVal) {
            this.loadStamps();
        },
        "$store.state.home.fileSelected.overlay_hidden_flg": function (newVal, oldVal) {
            this.loadStamps();
        },
        "isTextSelected": function (newVal, oldVal) {
            if(this.selectedId > 0) {
                this.undoAction();
                this.selectedId = 0;
            }

            this.textIdSelected = 0;
            this.loadTexts(newVal);
        },
        "page.texts": {
            handler: function (val, oldVal) {
                this.loadTexts(this.isTextSelected);
            },
            deep: true
        },
        "page.stamps": function (val, oldVal) {
            //this.loadStamps();
            if(val.length === 0) {
                const stage = this.$refs.stage.getStage();
                const layer = stage.children[0];
                if(!stage) {
                    return;
                }
                layer.draw();
            }
        },
        "page.stamps.length": function (val, oldVal) {
            if(val < oldVal) {
                this.loadStamps();
            }
        },
        "config.width": function (newVal, oldVal) {
            this.realScale = this.config.width / this.page.width;
            this.loadTexts(this.isTextSelected);
            this.loadStamps();
        },
        "showEdit": function(newVal, oldVal) {
            // 編集モード終了時、PDFを初期化
            this.isDraggable = true;
            /*if (!this.showEdit){
                let stage = this.$refs.stage.getStage();
                stage.x(0);
                stage.y(0);
                this.$store.state.home.fileSelected.zoom = 100;
                this.isDraggable = false;
                this.pdfShiftDistanceX = 0;
                this.pdfShiftDistanceY = 0;
            }
            // 編集モード時、PDFをドラッグ可能に
            else{
                this.isDraggable = true;
            }*/
        }
    }
}

</script>