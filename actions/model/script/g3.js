const g3 = {
//    proj_selectLogout(url) {
//        window.top.location.href = url;
//    },
    Action(framesetup)
    {
        let data = JSON.parse(framesetup);
        g3.handleFrames('inline', data.tabname, data.appFramename, data.apppath, data.appwidth, data.title, data.tabtitle, data.icon);
    },
    getHost(olink) {
        let h = document.createElement("a");
        h.href = olink;
        let hostname = h.hostname.replace(".", "");
        return hostname + h.port;
    },
    MainRefresh(url = '')
    {
//    let triggerMessageTo = window.location.protocol + "//" + window.location.host + "/";
        window.parent.postMessage(
                {
                    logictype: 'main',
                    sourcelink: url
//            }, triggerMessageTo);
                }, "*");
    },
    handleFrames(displaytype, tabid, frameid, sourcelink, framewidth, title, tabtitle, windowicon, isrefresh) {
        if (g3.getHost(window.location.href) != g3.getHost(sourcelink)) {
            // get project key
            let key = "idproject";
            let query_string = sourcelink.split("?");
            if (query_string[1]) {
                let string_values = query_string[1].split("&");
                let req_value = "";
                for (i = 0; i < string_values.length; i++)
                {
                    if (string_values[i].match(key))
                        req_value = string_values[i].split('=')[1];
                }
                tabid = req_value;
                console.log(title, tabtitle);
            }
        }
//    let triggerMessageTo = window.location.protocol + "//" + window.location.host + "/";
        window.parent.postMessage(
                {
                    logictype: 'open',
                    tabid: tabid,
                    tabtitle: tabtitle,
                    iframename: frameid,
                    sourcelink: sourcelink,
                    framewidth: parseInt(framewidth),
                    isrefresh: isrefresh,
                    displaytype: displaytype,
                    headertitle: title,
                    windowicon: windowicon,
//                hostname: g3.getHost(window.location.href)
                    hostname: g3.getHost(sourcelink)
//            }, triggerMessageTo);
                }, "*");
    },
    ActionRefresh(idproject, framename, url, title, windowicon)
    {
//    let triggerMessageTo = window.location.protocol + "//" + window.location.host + "/";
        window.parent.postMessage(
                {
                    logictype: 'refresh',
                    tabid: idproject,
                    iframename: framename,
                    sourcelink: url,
                    headertitle: title,
                    windowicon: windowicon,
//                hostname: g3.getHost(window.location.href)
                    hostname: g3.getHost(url)
//            }, triggerMessageTo);
                }, "*");
    },
    ActionHideElement(elementid)
    {
//    let triggerMessageTo = window.location.protocol + "//" + window.location.host + "/";
        window.parent.postMessage(
                {
                    elementid: elementid,
                    logictype: 'hide'
//            }, triggerMessageTo);
                }, "*");
    },
    ActionClose(idproject)
    {
//    let triggerMessageTo = window.location.protocol + "//" + window.location.host + "/";
        window.parent.postMessage(
                {
                    logictype: 'close',
                    tabid: idproject,
                    hostname: g3.getHost(window.location.href)
//            }, triggerMessageTo);
                }, "*");
    },
    ActionCloseWindow(idproject, framename)
    {
//    let triggerMessageTo = window.location.protocol + "//" + window.location.host + "/";
        window.parent.postMessage(
                {
                    logictype: 'closewindow',
                    tabid: idproject,
                    iframename: framename,
                    hostname: g3.getHost(window.location.href)
//            }, triggerMessageTo);
                }, "*");
    },
// display message in the main page 
    Message(pagepath, message, autoclose)
    {
//    let triggerMessageTo = window.location.protocol + "//" + window.location.host + "/";
        window.parent.postMessage(
                {
                    logictype: 'mssg',
                    message: message,
                    pagepath: pagepath,
                    autoclose: autoclose
//            }, triggerMessageTo);
                }, "*");
    },
    ActionWindow(idproject, framename, pagewidth, url, title, tabtitle, windowicon)
    {
        g3.handleFrames('inline', idproject, framename, url, pagewidth, title, tabtitle, windowicon);
    },
    ActionFloat(framesetup)
    {
        let data = JSON.parse(framesetup);
        g3.handleFrames('float', data.tabname, data.appFramename, data.apppath, data.appwidth, data.title, '', data.icon);
    },
    ActionFloatWindow(idproject, framename, pagewidth, url, title, windowicon)
    {
        g3.handleFrames('float', idproject, framename, url, pagewidth, title, '', windowicon);
    },
    async MessageById(pagepath, elementid, message, autoclose)
    {
        let response = await fetch(pagepath);
        $("#" + elementid).html(await response.text());
        opennotification(message, autoclose);
    }
};


// tools for modal form
function getSpan() {
    if (modaluser) {
        modaluser.style.display = "none";
    }
}
function closeForm() {
    $("#submitpnl").hide();
}
//*********************************************

//function initViewElement(ele) {
//    setTimeout(function () {
//        ele.slideDown("slow");
//    }, 100);

//}

function isNumeric(evt) {
    let theEvent = evt || window.event;
    let key = theEvent.keyCode || theEvent.which;
    key = String.fromCharCode(key);
    let regex = /[0-9]|\./;
    if (!regex.test(key)) {
        theEvent.returnValue = false;
        if (theEvent.preventDefault)
            theEvent.preventDefault();
    }
}

function adjustNoteTextHeight(el) {
    if (el.scrollHeight > el.clientHeight) {
        el.style.height = (el.scrollHeight + 5) + "px";
    }
}

function initDatepicker() {
    $('.datepicker')
            .attr('readonly', 'readonly')
            .datepicker({
                dateFormat: 'yy-m-d'
            });
}
