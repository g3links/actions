function initListeners()
{
    window.addEventListener('message', function (event) {
        if (event.data.logictype === 'main')
            ctrlMain(event);

        if (event.data.logictype === 'open')
            ctrlFrames(event);

        if (event.data.logictype === 'refresh')
            ctrlRefresh(event);

        if (event.data.logictype === 'close')
            ctrlClose(event);

        if (event.data.logictype === 'closewindow')
            ctrlCloseWindow(event);

        if (event.data.logictype === 'hide')
            ctrlHideElement(event);

        if (event.data.logictype === 'function')
            ctrlFunction(event);

        if (event.data.logictype === 'mssg')
            ctrlMessages(event);
    });

    // before leaving the app (e.g.: unregister user)
    window.addEventListener("beforeunload", function (e) {
        console.log("leaving app");
    });

}

async function ctrlMessages(event)
{
    if (event.data.logictype !== 'mssg')
        return false;

    if (!event.data.message)
        return false;

    if (!event.data.pagepath)
        return false;

    let response = await fetch(event.data.pagepath);
    $("#message").html(await response.text());
    opennotification(event.data.message, event.data.autoclose);
}

function ctrlMain(event)
{
    if (event.data.logictype !== 'main')
        return false;

    const g3menu = "g3menu";

    if (String(event.data.sourcelink).trim() === '') {
        document.getElementById(g3menu).contentWindow.location.reload();
    } else
    {
        //reload main menu
        let projectPanel = document.getElementById(g3menu);
        if (projectPanel) {
            projectPanel.setAttribute("class", "mainmenu");
            projectPanel.src = event.data.sourcelink;
        }
    }
    console.log("refresh main");
}

function ctrlRefresh(event)
{
    if (event.data.logictype !== 'refresh')
        return false;

    if (!event.data.tabid || String(event.data.tabid).trim() === '')
        event.data.tabid = 0;

    let elementID = event.data.hostname + event.data.iframename + event.data.tabid;
    let eleExist = document.getElementById("con-" + elementID);

    // form exist
    if (!eleExist)
        return;

    console.log("refesh: " + elementID + ", " + event.data.sourcelink);
    eleExist.src = event.data.sourcelink;

    if (event.data.headertitle) {
        let ele = document.getElementById("headerTitle" + elementID);
        if (ele)
            ele.innerHTML = event.data.headertitle;
    }

    if (event.data.windowicon && String(event.data.windowicon).trim() !== '') {
        let eleicon = document.getElementById("w" + elementID);
        if (eleicon)
            eleicon.setAttribute("class", event.data.windowicon);
    }
}

function ctrlHideElement(event)
{
    if (event.data.logictype !== 'hide')
        return false;

    let elem = document.getElementById(event.data.elementid);
    if (!elem)
        return;

    elem.src = "";
    $("#" + event.data.elementid).hide();

    console.log("hide element: " + event.data.elementid);
}

function ctrlClose(event)
{
    if (event.data.logictype !== 'close')
        return false;

    if (!event.data.tabid || String(event.data.tabid).trim() === '')
        event.data.tabid = 0;

    removeTab(event.data.hostname + event.data.tabid);
}

function ctrlCloseWindow(event)
{
    if (event.data.logictype !== 'closewindow')
        return false;

    if (!event.data.tabid || String(event.data.tabid).trim() === '')
        event.data.tabid = 0;

    //close a frame
    removeFrame(event.data.hostname, event.data.iframename, event.data.tabid);
}

//tabid: #=idproject, 0=system 
//tabtitle: display name 
//frameid: name of the iframe, 
//framewidth: width of the iframe
//sourcelink: url source (when no link frame will be closed)
//windowicon: icon class name
function ctrlFrames(event)
{
    if (event.data.logictype !== 'open')
        return false;

    const g3menu = "g3menu";

    if (!event.data.sourcelink || String(event.data.sourcelink).trim() === '')
        return false;

    let headerTitle = '';
    if (event.data.headertitle)
        headerTitle = event.data.headertitle;

// setup apps
    if (!event.data.tabtitle)
        event.data.tabtitle = event.data.tabid;

    if (!event.data.tabid || String(event.data.tabid).trim() === '')
        event.data.tabid = 0;

    let windowicon = "";
    if (event.data.windowicon && String(event.data.windowicon).trim() !== '')
        windowicon = event.data.windowicon;

    let selectedFrame = null;

    if (event.data.displaytype === 'inline') {
        selectedFrame = document.getElementById('snap');
        if (event.data.tabid !== 0)
            selectedFrame = addTab(event.data.hostname, event.data.tabid, event.data.tabtitle);
    } else {
        //remove specific form
        removeFrame(event.data.hostname, event.data.iframename, event.data.tabid);
        $('#floatform').show();
        selectedFrame = document.getElementById('floatform');
    }

    let elementID = event.data.hostname + event.data.iframename + event.data.tabid;
    let eleExist = document.getElementById(elementID);

    // create new form 
    if (!eleExist)
    {
        //default width 
        let valframewidth = "100%";
        //format width for g3 frames
        if (event.data.framewidth !== 0) {
            valframewidth = (event.data.framewidth - 4) + "px";
        } else {
            let board = document.getElementById(g3menu);
            if (board) {
                //resize frame: board + splitters
                valframewidth = (window.innerWidth - 220 - 5);
                //set a minimun
                if (valframewidth < 650)
                {
                    valframewidth = 650;
                }
                valframewidth = valframewidth + "px";
            }
        }

        //create host frame
        let ifrdiv = document.createElement('div');
        ifrdiv.id = elementID;
        ifrdiv.setAttribute("class", "mainmenucolor");
        ifrdiv.style = "width: " + valframewidth + ";";
        addControles(ifrdiv, event.data.hostname, event.data.tabid, event.data.iframename, event.data.displaytype, headerTitle, windowicon);

        let ifr = document.createElement('iframe');
        ifr.id = "con-" + elementID;
        ifr.name = event.data.iframename + event.data.tabid;
        ifr.setAttribute("class", "g3actionframe");
        ifrdiv.appendChild(ifr);

        console.log("open: " + elementID + ", " + event.data.sourcelink);

        // no container found, just add
        if (!selectedFrame) {
            document.body.appendChild(ifr);
        } else {
            selectedFrame.appendChild(ifrdiv);

            if (event.data.displaytype === 'inline') {
                // add splitter
                let sfr = document.createElement('div');
                sfr.id = "split" + elementID;
                sfr.setAttribute("class", "splitter column");
                sfr.setAttribute("onmousedown", "splitterMouseDown(this, '" + elementID + "');");
                selectedFrame.appendChild(sfr);
            }
        }

        ifr.src = event.data.sourcelink;
        ifr.scrollIntoView({block: "end"});
    }

    // form exist
    if (eleExist) {
        let frm = document.getElementById("con-" + elementID);
        if (frm) {
            console.log("refresh: " + elementID + ", " + event.data.sourcelink);
            frm.src = event.data.sourcelink;
        }

//        eleExist.addEventListener('load', function () {
        let ele = document.getElementById("headerTitle" + elementID);
        if (ele)
            ele.innerHTML = headerTitle;

        let eleicon = document.getElementById("w" + elementID);
        if (eleicon)
            eleicon.setAttribute("class", windowicon + " floatLeft");
    }
}

function resizewindow(elementName) {
    let minheight = 40;
    let minimizeto = '35px';
    let maximizeto = '70%';

    let parentEle = parent.$('#' + elementName);
    if (!parentEle)
        return;

    let minimize = true;

    let panelHeight = parseInt(parent.$('#' + elementName).height());
    if (panelHeight > minheight) {
        parent.$('#' + elementName).css('height', minimizeto);
    } else {
        minimize = false;
        parent.$('#' + elementName).css('height', '100%');

    }

    if (minimize) {
        let pnlFloat = parent.document.getElementById("floatform");
        let floatPanels = pnlFloat.childNodes;
        $.each(floatPanels, function (index, value) {
            let panelHeight = parseInt(parent.$("#" + value.id).height());
            if (panelHeight > minheight) {
                minimize = false;
            }
        });
    }

    if (minimize) {
        parent.$('#floatform').css('height', minimizeto);
    } else {
        parent.$('#floatform').css('height', maximizeto);
    }
}

function refreshFrame(framename)
{
    let elem = document.getElementById(framename);
    if (elem)
        document.getElementById(framename).src = document.getElementById(framename).src;
}

function removeTab(elementID)
{
    //close tab
    let tablinkExist = document.getElementById("tablink" + elementID);
    if (tablinkExist)
        tablinkExist.parentNode.removeChild(tablinkExist);

    let tabExist = document.getElementById(elementID);
    if (tabExist) {
        tabExist.parentNode.removeChild(tabExist);
        console.log("closed tab: " + elementID);
        ShowNextActivePanel();
    }
}

function removeFrame(hostname, iframename, itabid)
{
    let todelete = hostname + iframename + itabid;
    let row = document.getElementById(todelete);
    if (!row)
        return;

    let board = row.parentNode;
    if (!board)
        return;

    board.removeChild(row);
    let split = document.getElementById('split' + todelete);
    if (split)
        board.removeChild(split);

    console.log("closed: " + todelete);

    //float frames: do not remove, just hide them
    if (board.id === 'floatform') {
        let floatform = document.getElementById('floatform').childElementCount;
        if (floatform === 0)
            $('#floatform').hide();
    }

    if (board.children.length !== 0)
        return;

    // delete tab if empty
    let tablink = document.getElementById('tablink' + hostname + itabid);
    if (tablink) {
        tablink.parentNode.removeChild(tablink);
        board.parentNode.removeChild(board);
        console.log("closed tab: " + hostname + itabid);
        ShowNextActivePanel();
    }
}

function ShowNextActivePanel()
{
    let panelElement = document.getElementById("panels");
    let tabPanels = panelElement.childNodes;
    $.each(tabPanels, function (index, value) {
        let viewPanel = document.getElementById("" + value.id)
        if (viewPanel) {
            selectMasterTab(value.id);
            return false;
        }
    });
}

function selectMasterTab(itabid)
{
    if (itabid === '')
        return false;

    let tabExist = document.getElementById("" + itabid);
    if (!tabExist)
        return false;

    let panelElement = document.getElementById("panels");
    let tabElement = document.getElementById("tabs");

    // hide all panels
    let tabPanels = panelElement.childNodes;
    $.each(tabPanels, function (index, value) {
        $("#" + value.id).hide();
    });
    // highlight selected tab
    let tablinkPanels = tabElement.childNodes;
    $.each(tablinkPanels, function (index, value) {
        window.document.getElementById(value.id).classList.remove("linkTabSelected");
        window.document.getElementById(value.id).classList.add("linkTab");
    });

    // tab exist, acive view
    window.document.getElementById("tablink" + itabid).classList.remove("linkTab");
    window.document.getElementById("tablink" + itabid).classList.add("linkTabSelected");

    $("#" + tabExist.id).show();
    return tabExist;
}

function addTab(hostname, itabid, tabname)
{
    let tabExist = document.getElementById(hostname + itabid);

    // tab exist, acive view
    if (tabExist)
        return selectMasterTab(hostname + itabid);

    let tabs = document.getElementById("tabs");
    let panelElement = document.getElementById("panels");

    // create tab
    let dfr = document.createElement('div');
    dfr.id = "tablink" + hostname + itabid;
    dfr.setAttribute("onclick", "selectMasterTab('" + hostname + itabid + "');");

    let afr = document.createElement('label');
    afr.innerHTML = tabname;

    let imfr = document.createElement('span');
    imfr.setAttribute("class", "imgBtn imgHide marginsmall");
    imfr.setAttribute("onclick", "removeTab('" + hostname + itabid + "');");

    dfr.appendChild(afr);
    dfr.appendChild(imfr);

    tabs.appendChild(dfr);

    let ifr = document.createElement('div');
    ifr.id = hostname + itabid;
    ifr.setAttribute("class", "container");

    panelElement.appendChild(ifr);
    console.log("new tab: " + hostname + itabid);
    return selectMasterTab(hostname + itabid);
}

function addControles(ifr, hostname, itabid, iframename, displaytype, headerTitle, windowIcon)
{
    //close btn
    let imgClose = document.createElement('span');
    imgClose.setAttribute("class", "imgBtn imgClose");
    imgClose.setAttribute("onclick", "removeFrame('" + hostname + "','" + iframename + "','" + itabid + "');");

    //refresh btn
    let imgRefresh = document.createElement('span');
    imgRefresh.setAttribute("class", "imgBtn imgRefresh");
    imgRefresh.setAttribute("id", "reload");

    imgRefresh.setAttribute("onclick", "refreshFrame('con-" + hostname + iframename + itabid + "');");

    let imgdiv = document.createElement('span');
    imgdiv.setAttribute("class", "floatRight");

    if (displaytype === 'float') {
        //add resize btn
        let imgRz = document.createElement('span');
        imgRz.setAttribute("class", "imgBtn imgMinimize");
        imgRz.setAttribute("onclick", "resizewindow('" + hostname + iframename + itabid + "');");

        imgdiv.appendChild(imgRz);
    }

    imgdiv.appendChild(imgRefresh);
    imgdiv.appendChild(imgClose);

    let divHeader = document.createElement('div');

    // form label
    let title = document.createElement('label');
    title.id = "headerTitle" + hostname + iframename + itabid;
    title.innerHTML = headerTitle;
    title.setAttribute("class", "txtDark marginleft");

    //window icon
    let imgIcon = document.createElement('span');
    if (windowIcon !== '')
        imgIcon.setAttribute("class", windowIcon + " floatLeft");

    imgIcon.setAttribute("id", "w" + hostname + iframename + itabid);
    divHeader.appendChild(imgIcon);

    divHeader.appendChild(title);
    divHeader.appendChild(imgdiv);
    ifr.appendChild(divHeader);
}
