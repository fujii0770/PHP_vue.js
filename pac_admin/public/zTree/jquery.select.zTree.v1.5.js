;(function ($, window, document, undefined) {
    var cacheName = "selectZTreeObj";

    var tools = {
        uuid: function () {
            var S4 = function () {
                return (((1 + Math.random()) * 0x10000) | 0).toString(16).substring(1);
            };
            //+ S4() + S4() + S4()
            return (S4() + S4() + S4()).toLowerCase();
        },
        isEmpty: function (str) {
            return typeof (str) == "undefined" || str == null | str == "";
        },
        isInclude: function (name) {
            var js = /js$/i.test(name);
            var es = document.getElementsByTagName(js ? 'script' : 'link');
            for (var i = 0; i < es.length; i++)
                if (es[i][js ? 'src' : 'href'].indexOf(name) != -1) return true;
            return false;
        }
    };

    var SelectZTree = function (ele, option) {
        var self = this, _option = $.extend(true, {
            showSearch: true,
            closeOnSelect: true,
            selectLevel: 0,
            initValue: true,
            placeholder: '選択してください',
            key: {
                idKey: "id",
                pIdKey: "pId",
                rootPId: 0,
                name: "name"
            },
            data: [],
        }, option);

        self.id = tools.uuid();
        self.$ele = ele.addClass("select_ztree_hidden");
        self.$select = $("<div class=\"select_ztree\"><div class=\"select_ztree_btn\"><input readonly class=\"select-value\" placeholder='" +
            _option.placeholder + "'/><span class=\"tag\"><b></b></span></div></div>").insertAfter(ele);
        self.$select.hover(function (e) {
            self._flag = true;
        }, function (e) {
            self._flag = false;
        }).find(".select_ztree_btn").on('click', function (e) {
            self.show(!self.$select.find(self.$container).length);
        });
        self.$container = $("<div class=\"select_ztree_container\" >" +
            "<div class=\"select_ztree_search\" ><input autocomplete=\"off\"/></div>" +
            "<div class=\"select_ztree_list\" ><ul class=\"ztree\" id=\"" + self.id + "\"></ul></div></div>");

        self.option = _option;
    };

    SelectZTree.prototype = {
        initEvent: function (option) {
            var self = this, option = $.extend(true, self.option, option), key = option.key;

            if (option.placeholder) {
                this.$select.find(".select_ztree_btn input").attr("placeholder", option.placeholder);
            }
            if (!option.showSearch) {
                this.$container.find(".select_ztree_search").addClass("hide");
            }
            if (!!option.width) {
                this.$select.css("width", option.width);
            }
            if (!option.data.length) {
                self.domToData(key.rootPId, self.$ele.children());
            }

            self.groupOfData();
            self.renderEle();

            if (!tools.isEmpty(option.initValue)) {
                self.setDefault();
            }
            self.doEvent(option.onReady, option.def);
            self.option = option;
        },
        domToData: function (pId, $children) {
            var self = this, option = self.option, key = option.key;
            var createItem = function (id, pId, name) {
                var item = {};
                item[key.idKey] = id;
                item[key.pIdKey] = pId;
                item[key.name] = name;
                return item;
            }
            for (var i = 0, len = $children.length; i < len; i++) {
                var $dom = $children.eq(i);
                if ($dom.get(0).tagName.toLowerCase() == "option") {
                    option.data.push(createItem($dom.val(), pId, $dom.text()));
                } else {
                    var uuid = tools.uuid();
                    option.data.push(createItem(uuid, pId, $dom.attr("label")));
                    self.domToData(uuid, $dom.children());
                }
            }
        },
        groupOfData: function () {
            var self = this, option = self.option, data = option.data, key = option.key, group = {};
            for (var i = 0, len = data.length; i < len; i++) {
                var pid = data[i][key.pIdKey];
                if (!group[pid]) {
                    group[pid] = [];
                }
                group[pid].push(data[i]);
            }
            option._group = group;
        },
        renderEle: function () {
            var self = this, key = self.option.key, data = self.option.data, txt = "";
            for (var i = 0, len = data.length; i < len; i++) {
                txt += "<option value='" + data[i][key.idKey] + "' data-" + key.pIdKey + "='" + data[i][key.pIdKey] + "'>" + data[i].name + "</option>";
            }
            self.$ele.empty().append(txt);
        },
        setDefault: function () {
            var self = this, option = self.option, group = option._group, initVal = option.initValue, key = option.key,
                level = 0;
            if (initVal == true) {
                var getLastItem = function (children) {
                    if (option.selectLevel == level++) {
                        return children[0];
                    }
                    var childId = children[0][key.idKey];
                    if (group[childId]) {
                        return getLastItem(group[childId]);
                    } else {
                        return children[0];
                    }
                };
                option.def = getLastItem(group[key.rootPId]);
            } else {
                option.def = self.getItem(initVal);
            }
            self.doChange(option.def[key.idKey]);
        },
        show: function (flag) {
            var self = this, option = self.option;

            if (flag) {
                self.doEvent(option.onOpen);

                self.$container.appendTo(self.$select)
                    .find("input").off("blur")
                    .on("blur", function (e) {
                        if (self._flag) {
                            $(this).get(0).focus();
                        } else {
                            self.show(false);
                        }
                    }).get(0).focus();

                self.initZTree();
                self.$select.find("b").addClass("selected");
                self.$container.css("minWidth", self.$select.outerWidth() || "100%");
            } else {
                self.$container.remove().find("input").val("");
                self.$select.find("b").removeClass("selected");

                self.doEvent(option.onClose);
            }
        },
        initZTree: function () {
            var self = this, option = self.option, key = option.key;

            var beforeClick = function (treeId, treeNode) {
                var level = option.selectLevel;
                if (((level == -1 && !option._group[treeNode[key.idKey]]) || (level != -1 && level <= treeNode.level)) && treeNode.type) {
                    return true;
                } else {
                    var zTree = $.fn.zTree.getZTreeObj(self.id);
                    //展开/折叠节点
                    zTree.expandNode(treeNode);
                    return false;
                }
            }

            var click = function (e, treeId, treeNode) {
                e.stopPropagation();
                self.doChange(treeNode[key.idKey]);
                var cos = option.closeOnSelect;
                if (typeof cos == "undefined" || cos) {
                    self.show(false);
                }
            }

            var setting = {
                view: {
                    showLine: false,
                    showIcon: true,
                    selectedMulti: false,
                    dblClickExpand: false
                },
                data: {
                    key: {
                        title: "title"
                    },
                    simpleData: {
                        enable: true,
                        idKey: key.idKey,
                        pIdKey: key.pIdKey,
                        rootPId: key.rootPId,
                        icon: key.icon
                    }
                },
                callback: {
                    onClick: click,
                    beforeClick: beforeClick
                }
            };
            $.fn.zTree.init($("#" + self.id), setting, option.data.map(function (item) {
                item[setting.data.key.title] = item["name"];
                return item;
            }));
            if (!!option.showSearch) {
                self.fuzzySearch(self.id, self.$container.find("input"), false, true);
            }
        },
        setVal: function (item) {
            var self = this, key = self.option.key;
            if (!tools.isEmpty(item)) {
                self.doChange(typeof item == "object" ? item[key.idKey] : item);
            }
        },
        getVal: function () {
            return this.getItem(this.$ele.val());
        },
        doChange: function (KeyVal) {
            var self = this, option = self.option, key = option.key;
            var data = (self.getItem(KeyVal)) || {};
            self.$select.find("input").val(data[key.name]);
            self.doEvent(option.onSelected, data);
            self.$ele.val(data[key.idKey]).trigger("change", data);
        },
        getItem: function (KeyVal) {
            var self = this, key = self.option.key, data = self.option.data;
            //为兼容IE
            for (var i = 0, len = data.length; i < len; i++) {
                if (KeyVal == data[i][key.idKey]) {
                    return data[i];
                }
            }
            return null;
            // return self.option.data.find(function (item) {
            //     return KeyVal == item[key.idKey];
            // });
        },
        doEvent: function (callback, value) {
            var $ele = this.$ele;
            if (typeof callback == "function") {
                try {
                    callback.call($ele, $ele, value);
                } catch (e) {
                    console.error(e);
                }
            }
        },
        fuzzySearch: function (zTreeId, searchField, isHighLight, isExpand) {
            var zTreeObj = $.fn.zTree.getZTreeObj(zTreeId);
            if (!zTreeObj) {
                alert("fail to get ztree object");
            }
            // get the key of the node name
            var nameKey = zTreeObj.setting.data.key.name;
            var oldNameKey = "oldname";
            isHighLight = isHighLight === false ? false : true;
            isExpand = isExpand ? true : false;
            zTreeObj.setting.view.nameIsHTML = isHighLight;

            var metaChar = '[\\[\\]\\\\\^\\$\\.\\|\\?\\*\\+\\(\\)]';
            var rexMeta = new RegExp(metaChar, 'gi');


            function ztreeFilter(zTreeObj, _keywords, callBackFunc) {
                if (!_keywords) {
                    _keywords = ''; //default blank for _keywords
                }

                // function to find the matching node
                function filterFunc(node) {
                    if (node && node[oldNameKey] && node[oldNameKey].length > 0) {
                        node[nameKey] = node[oldNameKey]; //recover oldname of the node if exist
                    }
                    zTreeObj.updateNode(node); //update node to for modifications take effect
                    if (_keywords.length == 0) {
                        //return true to show all nodes if the keyword is blank
                        zTreeObj.showNode(node);
                        zTreeObj.expandNode(node, isExpand);
                        return true;
                    }
                    //transform node name and keywords to lowercase
                    if (node[nameKey] && node[nameKey].toLowerCase().indexOf(_keywords.toLowerCase()) != -1) {
                        if (isHighLight) { //highlight process
                            //a new variable 'newKeywords' created to store the keywords information
                            //keep the parameter '_keywords' as initial and it will be used in next node
                            //process the meta characters in _keywords thus the RegExp can be correctly used in str.replace
                            var newKeywords = _keywords.replace(rexMeta, function (matchStr) {
                                //add escape character before meta characters
                                return '\\' + matchStr;
                            });
                            node[oldNameKey] = node[nameKey]; //store the old name
                            var rexGlobal = new RegExp(newKeywords, 'gi');//'g' for global,'i' for ignore case
                            //use replace(RegExp,replacement) since replace(/substr/g,replacement) cannot be used here
                            node[nameKey] = node[oldNameKey].replace(rexGlobal, function (originalText) {
                                //highlight the matching words in node name
                                var highLightText =
                                    '<span style="color: whitesmoke;background-color: darkred;">'
                                    + originalText
                                    + '</span>';
                                return highLightText;
                            });

                            zTreeObj.updateNode(node); //update node for modifications take effect
                        }
                        zTreeObj.showNode(node);//show node with matching keywords
                        return true; //return true and show this node
                    }

                    zTreeObj.hideNode(node); // hide node that not matched
                    return false; //return false for node not matched
                }

                var nodesShow = zTreeObj.getNodesByFilter(filterFunc); //get all nodes that would be shown
                processShowNodes(nodesShow, _keywords);//nodes should be reprocessed to show correctly
            }


            function processShowNodes(nodesShow, _keywords) {
                if (nodesShow && nodesShow.length > 0) {
                    //process the ancient nodes if _keywords is not blank
                    if (_keywords.length > 0) {
                        $.each(nodesShow, function (n, obj) {
                            var pathOfOne = obj.getPath();//get all the ancient nodes including current node
                            if (pathOfOne && pathOfOne.length > 0) {
                                //i < pathOfOne.length-1 process every node in path except self
                                for (var i = 0; i < pathOfOne.length - 1; i++) {
                                    zTreeObj.showNode(pathOfOne[i]); //show node
                                    zTreeObj.expandNode(pathOfOne[i], true); //expand node
                                }
                            }
                        });
                    } else { //show all nodes when _keywords is blank and expand the root nodes
                        var rootNodes = zTreeObj.getNodesByParam('level', '0');//get all root nodes
                        $.each(rootNodes, function (n, obj) {
                            zTreeObj.expandNode(obj, true); //expand all root nodes
                        });
                    }
                }
            }

            $(searchField).bind('input propertychange', function () {
                var _keywords = $(this).val();
                searchNodeLazy(_keywords); //call lazy load
            });

            var timeoutId = null;
            var lastKeyword = '';

            function searchNodeLazy(_keywords) {
                if (timeoutId) {
                    //clear pending task
                    clearTimeout(timeoutId);
                }
                timeoutId = setTimeout(function () {
                    if (lastKeyword === _keywords) {
                        return;
                    }
                    ztreeFilter(zTreeObj, _keywords); //lazy load ztreeFilter function
                    // $(searchField).focus();//focus input field again after filtering
                    lastKeyword = _keywords;
                }, 500);
            }
        }
    };

    $.fn.selectZTree = function (option) {

        if (!$.fn.zTree) {
            throw Error("jqueryを導入してください。ztree.core.js、これはzTreeのコアコードです");
        }

        if (option && option.showSearch) {
            var fun = $.fn.zTree._z.view.clearOldFirstNode;
            if (typeof fun != "function") {
                throw Error("jqueryを導入してください。ztree.exhide.js、これはzTreeのあいまいな検索拡張コードです");
            }
        }

        if (!tools.isInclude("zTreeStyle.css")) {
            console.error("スタイルファイルzTreeStyle.css未導入");
        }

        this.each(function () {
            var $this = $(this), obj = $this.data(cacheName);
            if (!obj) {
                obj = new SelectZTree($this, option);
                $this.data(cacheName, obj);
            }
            obj.initEvent(option);
        });
        return this;
    };

    $.fn.selectZTreeSet = function (item) {
        this.each(function () {
            var obj = $(this).data(cacheName);
            if (obj) {
                obj.setVal(item);
            }
        });
        return this;
    };

    $.fn.selectZTreeGet = function () {
        var arrItem = [];
        this.each(function () {
            var obj = $(this).data(cacheName);
            if (obj) {
                arrItem.push(obj.getVal());
            }
        });
        return arrItem.length == 1 ? arrItem[0] : arrItem;
    };

})(jQuery, window, document);
