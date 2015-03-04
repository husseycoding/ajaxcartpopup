var cartpopup = Class.create({
    afterInit: function() {
        if (this.ajaxenabled) {
            if (this.product) {
                this.observeSubmit();
            } else {
                this.collectCategoryRequests();
            }
        }
        if ($("header-cart")) {
            $("header-cart").remove();
        }
        this.positionNoticeStart();
        if (this.showpopup) {
            this.mouseclose = false;
            this.displayPopup();
            this.addCloseListener.bind(this).delay(this.slidespeed);
            this.addInputListener.bind(this).delay(this.slidespeed);
        } else if (!this.emptycart) {
            this.initPopup();
        }
        this.disableCartLink();
    },
    getCartElement: function() {
        if ($$("a.skip-cart")[0]) {
            return $$("a.skip-cart")[0];
        } else if ($$("a.top-link-cart")[0]) {
            return $$("a.top-link-cart")[0];
        }
    },
    initPopup: function() {
        $("cartpopup_overlay").hide();
        this.mouseclose = true;
        this.positionPopupStart();
        this.mouseDisplayPopup();
        this.addCancelAutoCloseListener();
        this.addInputListener.bind(this).delay(this.slidespeed);
        this.popupshowing = false;
    },
    disablePopup: function() {
        this.emptycart = true;
        this.mouseclose = false;
        document.stopObserving("click", this.documenthandler);
        $("cartpopup").stopObserving("mouseout", this.hidehandler);
        this.getCartElement().stopObserving(this.getOpenMethod(), this.popuphandler);
        if (this.popupshowing) {
            Effect.SlideUp("cartpopup", {duration: this.slidespeed});
            this.popupshowing = false;
        }
    },
    observeSubmit: function() {
        this.submiturl = $("product_addtocart_form").readAttribute("action");
        $("product_addtocart_form").writeAttribute("action", "javascript:thiscartpopup.submitAction()");
    },
    submitAction: function() {
        var formdata = $("product_addtocart_form").serialize(true);
        var id = false;
        this.addToCart(id, formdata, this.submiturl)
    },
    updateQuantityAction: function() {
        var formdata = $("cartpopup_form").serialize(true);
        this.updateQuantity(formdata, this.updateurl);
    },
    collectCategoryRequests: function() {
        this.requests = {};
        $$("button.btn-cart").each(function(e) {
            if (!e.up("div.block-reorder")) {
                var request = e.readAttribute("onclick");
                if (request.indexOf("/product/") > 0) {
                    var id = request.substring(request.indexOf("/product/")).split("/");
                    id = id[2];
                    e.addClassName("ajaxprodid-" + id);
                    request = request.replace(/^setlocation\(['"]{1}/i, "").replace(/['"]{1}\)$/, "");
                    this.requests[id] = request;
                    e.writeAttribute("onclick", "");

                    e.observe("click", function(el) {
                        var target = el.target;
                        if (target.tagName.toLowerCase() != "button") {
                            target = target.up("button.btn-cart");
                        }
                        var id = target.className;
                        if (id.indexOf("ajaxprodid-")) {
                            id = id.substring(id.indexOf("ajaxprodid-"));
                            id = id.split(" ").shift();
                            id = id.split("-").pop();
                            this.addToCart(id);
                        }
                    }.bind(this));
                }
            }
        }.bind(this));
    },
    addToCart: function(id, formdata, submiturl) {
        $("ajaxnotice_working").show();
        $("ajaxnotice_result").hide();
        this.positionNotice();
        $("ajaxnotice").show();
        if (formdata) {
            formdata.ajaxcartpopup = true;
            formdata.isproductpage = true;
            var parameters = formdata;
        } else {
            var parameters = {ajaxcartpopup : true, imagedetail : true};
        }
        if (submiturl) {
            var url = submiturl;
        } else {
            var url = this.requests[id];
        }
        new Ajax.Request(url, {
            parameters: parameters,
            onSuccess: function(response) {
                var contentarray = response.responseText.evalJSON();
                var result = contentarray.result;
                if (result == "success" || (!result && this.product)) {
                    var message = contentarray.message;
                    var linktext = contentarray.linktext;
                    var popuphtml = contentarray.popuphtml;
                    var itemid = contentarray.itemid;
                    var deleteurl = contentarray.deleteurl;
                    if (contentarray.imageurl) {
                        this.imageurl = contentarray.imageurl;
                    }
                    if (contentarray.productname) {
                        this.productname = contentarray.productname;
                    }
                    var image = this.getProductImage();
                    if (this.showonadd) {
                        $("ajaxnotice").hide();
                        this.displayPopup();
                    } else {
                        var notice = "";
                        if (image) {
                            notice += "<div class=\"ajaxnotice_image\">" + image + "</div>";
                        }
                        notice += "<div class=\"ajaxnotice_content\">";
                        if (this.backurl && this.backname) {
                            notice += "<a class=\"ajaxnotice_back\" href=\"" + this.backurl + "\">< Back to " + this.backname + "</a>";
                        }
                        notice += "<a class=\"ajaxnotice_close\" href=\"javascript:void(null)\" onclick=\"thiscartpopup.hideNotice()\">CLOSE</a>";
                        notice += "<div class=\"ajaxnotice_clearer\"></div>";
                        if (!result && this.product) {
                            var errorclass = " ajaxaddfailed";
                        } else {
                            var errorclass = "";
                        }
                        if (this.cartbutton || this.checkoutbutton) {
                            notice += "<div class=\"ajaxnotice_message" + errorclass + "\" style=\"margin-bottom:35px\">" + message + "</div>";
                            notice += "<div class=\"ajaxnotice_buttons\">";
                            if (this.cartbutton) {
                                notice += "<a href=\"" + this.carturl + "\">";
                                notice += "<div class=\"ajaxnotice_cart\"><img src=\"" + this.cartbutton + "\" alt=\"\" \\><div>" + this.carttext + "</div></div>";
                                notice += "</a>";
                            }
                            if (this.checkoutbutton) {
                                notice += "<a href=\"" + this.checkouturl + "\">";
                                notice += "<div class=\"ajaxnotice_checkout\"><img src=\"" + this.checkoutbutton + "\" alt=\"\" \\><div>" + this.checkouttext + "</div></div>";
                                notice += "</a>";
                            }
                            notice += "<div class=\"ajaxnotice_clearer\"></div>";
                            notice += "</div>";
                        } else {
                            notice += "<div class=\"ajaxnotice_message" + errorclass + "\">" + message + "</div>";
                        }
                        notice += "<div class=\"ajaxnotice_clearer\"></div>";
                        notice += "</div>";
                        $("ajaxnotice").hide();
                        $("ajaxnotice_working").hide();
                        $("ajaxnotice_result").update(notice);
                        $("ajaxnotice_result").show();
                        this.positionNotice();
                        $("ajaxnotice").show();
                    }
                    
                    if (result == "success") {
                        this.updatePopup(linktext, popuphtml);
                        if (itemid && deleteurl) {
                            this.deleteurls[itemid] = deleteurl;
                        }
                        if (this.emptycart) {
                            this.emptycart = false;
                            this.initPopup();
                        }
                    }
                } else {
                    setLocation(result);
                }
                if (this.product) {
                    $$("button.btn-cart").each(function(e) {
                        e.disabled = false;
                    });
                }
            }.bind(this)
        });
    },
    removeFromCart: function(id) {
        $("cartpopup_overlay").show();
        var url = this.deleteurls[id];
        var parameters = {ajaxcartpopup : true};
        new Ajax.Request(url, {
            parameters: parameters,
            onSuccess: function(response) {
                var contentarray = response.responseText.evalJSON();
                var result = contentarray.result;
                if (result == "success") {
                    var emptycart = contentarray.emptycart;
                    var linktext = contentarray.linktext;
                    var popuphtml = contentarray.popuphtml;
                    if (emptycart) {
                        this.disablePopup();
                        this.updatePopup.bind(this).delay(this.slidespeed, linktext, popuphtml, true);
                    } else {
                        this.updatePopup(linktext, popuphtml);
                    }
                } else {
                    setLocation(result);
                }
            }.bind(this)
        });
    },
    updateQuantity: function(formdata, updateurl) {
        $("cartpopup_overlay").show();
        if (formdata) {
            formdata.ajaxcartpopup = true;
            formdata.ajaxupdatequantity = true;
            formdata.update_cart_action = "update_qty";
            var parameters = formdata;
        }
        if (updateurl) {
            var url = updateurl;
        }
        new Ajax.Request(url, {
            parameters: parameters,
            onSuccess: function(response) {
                var contentarray = response.responseText.evalJSON();
                var result = contentarray.result;
                if (result == "success") {
                    var emptycart = contentarray.emptycart;
                    var linktext = contentarray.linktext;
                    var popuphtml = contentarray.popuphtml;
                    if (emptycart) {
                        this.disablePopup();
                        this.updatePopup.bind(this).delay(this.slidespeed, linktext, popuphtml, true);
                    } else {
                        this.updatePopup(linktext, popuphtml);
                    }
                } else {
                    setLocation(result);
                }
            }.bind(this)
        });
    },
    updatePopup: function(linktext, popuphtml, removecount) {
        if (linktext) {
            var link = this.getCartElement();
            if (link.readAttribute("title")) {
                link.writeAttribute("title", linktext.stripScripts().stripTags());
            }
            link.update(linktext);
            if (removecount) {
                this.removeCount();
            }
        }
        if (popuphtml) {
            $("cartpopup_slidecontainer").update(popuphtml);
        }
        $("cartpopup_overlay").hide();
        this.addInputListener();
    },
    getProductImage: function() {
        if (this.imageurl && this.productname) {
            var tag = "<img src=\"" + this.imageurl + "\" alt=\"" + this.productname + "\" title=\"" + this.productname + "\" \\>";
            return tag;
        } else {
            return false;
        }
    },
    addCancelAutoCloseListener: function() {
        this.autoclosehandler =  this.autoCloseHandler.bind(this);
        $("cartpopup").observe("mouseover", this.autoclosehandler);
    },
    autoCloseHandler: function(e) {
        if (Position.within($("cartpopup"), Event.pointerX(e), Event.pointerY(e))) {
            this.cancelPopupAutoClose();
        }
    },
    startPopupAutoClose: function() {
        if (this.autoclosetime) {
            this.autoclose = this.hidePopup.bind(this).delay(this.autoclosetime);
        }
    },
    cancelPopupAutoClose: function() {
        if (this.autoclose) {
            window.clearTimeout(this.autoclose);
            this.autoclose = false;
        }
    },
    displayPopup: function() {
        this.startPopupAutoClose();
        $("cartpopup_overlay").hide();
        if (!this.mouseclose) {
            this.positionPopupStart();
            Effect.SlideDown("cartpopup", {duration: this.slidespeed});
        } else {
            this.getCartElement().stopObserving(this.getOpenMethod(), this.popuphandler);
            Effect.SlideDown("cartpopup", {duration: this.slidespeed});
            this.mouseHidePopup.bind(this).delay(this.slidespeed);
            this.addCloseListener.bind(this).delay(this.slidespeed);
        }
        this.popupshowing = true;
    },
    positionPopupStart: function() {
        $("cartpopup").hide();
        this.positionPopup();
    },
    positionPopup: function() {
        var position = this.getCartElement().viewportOffset();
        var top = position.top;
        var left = position.left
        var size = this.getCartElement().getDimensions();
        var height = size.height;
        var width = size.width;
        var posleft = left + (width / 2) - ($("cartpopup").getWidth() / 2);
        var mainleft = $$("div.main")[0].viewportOffset();
        mainleft = mainleft.left;
        var mainright = mainleft + $$("div.main")[0].getWidth();
        if (posleft < mainleft) {
            posleft = mainleft;
        } else if ((posleft + $("cartpopup").getWidth()) > mainright) {
            var diff = (posleft + $("cartpopup").getWidth()) - mainright;
            posleft = posleft - diff;
        }
        var scroll = document.viewport.getScrollOffsets();
        var postop = top + height + scroll.top;
        $("cartpopup").setStyle({
            top: postop + "px",
            left: posleft + "px"
        });
    },
    positionNoticeStart: function() {
        $("ajaxnotice").hide();
        this.positionNotice();
    },
    positionNotice: function() {
        var viewportdimensions = document.viewport.getDimensions();
        var noticedimensions = $("ajaxnotice").getDimensions();
        $("ajaxnotice").style.left = (viewportdimensions.width / 2) - (noticedimensions.width / 2) + "px";
        $("ajaxnotice").style.top = (viewportdimensions.height / 2) - (noticedimensions.height / 2) + "px";
    },
    addCloseListener: function() {
        this.documenthandler =  this.documentHandler.bind(this);
        document.observe("click", this.documenthandler);
    },
    documentHandler: function(e) {
        if (!e.target.up("div#cartpopup")) {
            this.cancelPopupAutoClose();
            this.hidePopup();
        }
    },
    addInputListener: function() {
        $$("#cartpopup_slidecontainer input").each(function(e) {
            e.observe("keypress", function(e) {
                if (e.keyCode == Event.KEY_RETURN) {
                    $("cartpopup_form").submit();
                    Event.stop(e);
                }
            });
        });
    },
    hidePopup: function() {
        if (!this.mouseclose) {
            this.mouseclose = true;
            document.stopObserving("click", this.documenthandler);
            Effect.SlideUp("cartpopup", {duration: this.slidespeed});
            this.mouseDisplayPopup.bind(this).delay(this.slidespeed);
        } else {
            document.stopObserving("click", this.documenthandler);
            $("cartpopup").stopObserving("mouseout", this.hidehandler);
            Effect.SlideUp("cartpopup", {duration: this.slidespeed});
            this.mouseDisplayPopup.bind(this).delay(this.slidespeed);
        }
        this.popupshowing = false;
    },
    hideNotice: function() {
        $("ajaxnotice").hide();
    },
    mouseDisplayPopup: function() {
        this.popuphandler =  this.mousePopupHandler.bind(this);
        this.getCartElement().observe(this.getOpenMethod(), this.popuphandler);
    },
    mousePopupHandler: function(e) {
        this.displayPopup();
    },
    mouseHidePopup: function() {
        this.hidehandler =  this.mouseHideHandler.bind(this);
        $("cartpopup").observe("mouseout", this.hidehandler);
    },
    mouseHideHandler: function(e) {
        if (!Position.within($("cartpopup"), Event.pointerX(e), Event.pointerY(e))) {
            this.hidePopup();
        }
    },
    positionAll: function() {
        this.positionNotice();
        this.positionPopup();
    },
    getOpenMethod: function() {
        if (this.clickopen) {
            return "click";
        }
        return "mouseover";
    },
    disableCartLink: function() {
        if (this.clickopen) {
            this.linkhandler =  this.cartLinkHandler.bind(this);
            this.getCartElement().observe(this.getOpenMethod(), this.linkhandler);
        }
    },
    cartLinkHandler: function(e) {
        if (!this.emptycart) {
            Event.stop(e);
        }
    },
    removeCount: function(e) {
        if (this.getCartElement().down("span.count")) {
            this.getCartElement().down("span.count").remove();
        }
    }
});