var cartpopup = Class.create({
    afterInit: function() {
        $$('body')[0].insert($("cartpopup"));
        this.cartelement = $$("a.top-link-cart")[0];
        if (this.ajaxenabled) {
            if (this.category) {
                this.collectCategoryRequests();
            } else if (this.product) {
                this.observeSubmit();
            }
        }
        this.positionNoticeStart();
        if (this.showpopup) {
            this.mouseclose = false;
            this.displayPopup();
            this.addCloseListener.bind(this).delay(this.slidespeed);
            this.addInputListener.bind(this).delay(this.slidespeed);
        } else if (this.notempty) {
            this.initPopup();
        }
    },
    initPopup: function() {
        this.mouseclose = true;
        this.positionPopupStart();
        this.mouseDisplayPopup();
        this.addInputListener.bind(this).delay(this.slidespeed);
    },
    observeSubmit: function() {
        this.submitUrl = $("product_addtocart_form").readAttribute("action");
        $("product_addtocart_form").writeAttribute("action", "javascript:thiscartpopup.submitAction()");
    },
    submitAction: function() {
        var formdata = $("product_addtocart_form").serialize(true);
        var id = false;
        this.addToCart(id, formdata, this.submitUrl)
    },
    collectCategoryRequests: function() {
        this.requests = {};
        $$("button.btn-cart").each(function(e) {
            var request = e.readAttribute("onclick");
            if (request.indexOf("/product/") > 0) {
                var id = request.substring(request.indexOf("/product/")).split("/");
                id = id[2];
                e.addClassName("ajaxprodid-" + id);
                request = request.replace(/^setlocation\(['"]{1}/i, "").replace(/['"]{1}\)$/, "");
                this.requests[id] = request;
                e.writeAttribute("onclick", "");
                
                e.observe("click", function(ev) {
                    var target = ev.target;
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
                }.bindAsEventListener(this, e));
            }
        }.bind(this));
    },
    addToCart: function(id, formdata, submiturl) {
        $("ajaxnotice_working").show();
        $("ajaxnotice_result").hide();
        this.positionNotice();
        $("ajaxnotice").show();
        if (formdata) {
            var parameters = formdata;
            formdata.ajaxcartpopup = true;
            formdata.isproductpage = true;
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
                    if (contentarray.imageurl) {
                        this.imageurl = contentarray.imageurl;
                    }
                    if (contentarray.productname) {
                        this.productname = contentarray.productname;
                    }
                    var image = this.getProductImage();
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
                            notice += "<div class=\"ajaxnotice_cart\"><img src=\"" + this.cartbutton + "\" alt=\"\" \\></div>";
                            notice += "</a>";
                        }
                        if (this.checkoutbutton) {
                            notice += "<a href=\"" + this.checkouturl + "\">";
                            notice += "<div class=\"ajaxnotice_checkout\"><img src=\"" + this.checkoutbutton + "\" alt=\"\" \\></div>";
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
                    
                    if (result == "success") {
                        if (linktext) {
                            var link = $$("a.top-link-cart")[0];
                            link.writeAttribute("title", linktext);
                            link.innerHTML = linktext;
                        }
                        if (popuphtml) {
                            $("cartpopup_slidecontainer").innerHTML = popuphtml;
                        }
                        if (!this.notempty) {
                            this.notempty = true;
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
    getProductImage: function() {
        if (this.imageurl && this.productname) {
            var tag = "<img src=\"" + this.imageurl + "\" alt=\"" + this.productname + "\" title=\"" + this.productname + "\" \\>";
            return tag;
        } else {
            return false;
        }
    },
    displayPopup: function() {
        if (!this.mouseclose) {
            this.positionPopupStart();
            Effect.SlideDown("cartpopup", {duration: this.slidespeed});
        } else {
            this.cartelement.stopObserving("mouseover");
            Effect.SlideDown("cartpopup", {duration: this.slidespeed});
            this.mouseHidePopup.bind(this).delay(this.slidespeed);
            this.addCloseListener.bind(this).delay(this.slidespeed);
        }
    },
    positionPopupStart: function() {
        $("cartpopup").hide();
        this.positionPopup();
    },
    positionPopup: function() {
        var position = this.cartelement.viewportOffset();
        var top = position.top;
        var left = position.left
        var size = this.cartelement.getDimensions();
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
        var postop = top + height;
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
        var noticedimensions = $('ajaxnotice').getDimensions();
        $('ajaxnotice').style.left = (viewportdimensions.width / 2) - (noticedimensions.width / 2) + "px";
        $('ajaxnotice').style.top = (viewportdimensions.height / 2) - (noticedimensions.height / 2) + "px";
    },
    addCloseListener: function() {
        document.observe("click", function(e) {
            if (!e.target.up("div#cartpopup")) {
                this.hidePopup();
            }
        }.bindAsEventListener(this));
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
            document.stopObserving("click");
            Effect.SlideUp("cartpopup", {duration: this.slidespeed});
            this.mouseDisplayPopup.bind(this).delay(this.slidespeed);
        } else {
            document.stopObserving("click");
            $("cartpopup").stopObserving("mouseout");
            Effect.SlideUp("cartpopup", {duration: this.slidespeed});
            this.mouseDisplayPopup.bind(this).delay(this.slidespeed);
        }
    },
    hideNotice: function() {
        $("ajaxnotice").hide();
    },
    mouseDisplayPopup: function() {
        this.cartelement.observe("mouseover", function(e) {
            this.displayPopup();
        }.bindAsEventListener(this));
    },
    mouseHidePopup: function() {
        $("cartpopup").observe("mouseout", function(e) {
            if (!Position.within($("cartpopup"), Event.pointerX(e), Event.pointerY(e))) {
                this.hidePopup();
            }
        }.bindAsEventListener(this));
    }
});

document.observe("dom:loaded", function() {
    if (typeof(thiscartpopup) == "object") {
        thiscartpopup.afterInit();
    }
});

Event.observe(window, "resize", function() {
    if (typeof(thiscartpopup) == "object") {
        thiscartpopup.positionNotice();
    }
});