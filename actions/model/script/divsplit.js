let panel1 = null;
let panel_left = 0;
let panel_top = 0;
let orientation = 'vertical';

function splitterMouseDown(elm, divid, options) {

    panel1 = document.getElementById(divid);

    panel_left = -1;
    panel_top = -1;
    orientation = 'vertical';

    if ($("#" + elm.id).hasClass("row")) {
        orientation = 'horizontal';
        panel_top = parseFloat(panel1.offsetTop);
    }

    // install the mousup event canceller to the window
    window.addEventListener('mouseup', splitterMouseUp, false);
    window.addEventListener('mousemove', splitterMouseMove, true);
}

function splitterMouseMove(e) {
    let minposv = 50;
    let minposh = 15;

    if (orientation === 'horizontal') {
        if (panel1) {
            let yoffs = parseFloat(e.clientY);

            yoffs = yoffs - panel_top;
            yoffs = Math.max(yoffs, minposh);

            panel1.style.height = (yoffs) + 'px';
        }
    }
    else {
        //default = vertical
        if (panel1) {
            let xoffs = parseFloat(e.clientX);
            if (panel_left < 0) {
                panel_left = xoffs - parseFloat(panel1.offsetWidth);
            }


            xoffs = xoffs - panel_left;
            xoffs = Math.max(xoffs, minposv);

            panel1.style.width = (xoffs) + 'px';
        }
    }
}

function splitterMouseUp(e) {
    window.removeEventListener('mousemove', splitterMouseMove, true);
    window.removeEventListener('mouseup', splitterMouseUp, true);
}
