function string_contains(haystack, needle) {
    if (haystack.indexOf(needle) == -1) {
        return false;
    } else {
        return true;
    }
}

function str_replace(search, replace, subject, count) {
    var i = 0,
        j = 0,
        temp = '',
        repl = '',
        sl = 0,
        fl = 0,
        f = [].concat(search),
        r = [].concat(replace),
        s = subject,
        ra = Object.prototype.toString.call(r) === '[object Array]',
        sa = Object.prototype.toString.call(s) === '[object Array]';
    s = [].concat(s);
    if (count) {
        this.window[count] = 0;
    }

    for (i = 0, sl = s.length; i < sl; i++) {
        if (s[i] === '') {
            continue;
        }
        for (j = 0, fl = f.length; j < fl; j++) {
            temp = s[i] + '';
            repl = ra ? (r[j] !== undefined ? r[j] : '') : r[0];
            s[i] = (temp).split(f[j]).join(repl);
            if (count && s[i] !== temp) {
                this.window[count] += (temp.length - s[i].length) / f[j].length;
            }
        }
    }
    return sa ? s : s[0];
}



function htmlspecialchars(string, quote_style, charset, double_encode) {
    var optTemp = 0,
        i = 0,
        noquotes = false;
    if (typeof quote_style === 'undefined' || quote_style === null) {
        quote_style = 2;
    }
    string = string.toString();
    if (double_encode !== false) { // Put this first to avoid double-encoding
        string = string.replace(/&/g, '&amp;');
    }
    string = string.replace(/</g, '&lt;').replace(/>/g, '&gt;');

    var OPTS = {
        'ENT_NOQUOTES': 0,
        'ENT_HTML_QUOTE_SINGLE': 1,
        'ENT_HTML_QUOTE_DOUBLE': 2,
        'ENT_COMPAT': 2,
        'ENT_QUOTES': 3,
        'ENT_IGNORE': 4
    };
    if (quote_style === 0) {
        noquotes = true;
    }
    if (typeof quote_style !== 'number') { // Allow for a single string or an array of string flags
        quote_style = [].concat(quote_style);
        for (i = 0; i < quote_style.length; i++) {
            // Resolve string input to bitwise e.g. 'ENT_IGNORE' becomes 4
            if (OPTS[quote_style[i]] === 0) {
                noquotes = true;
            }
            else if (OPTS[quote_style[i]]) {
                optTemp = optTemp | OPTS[quote_style[i]];
            }
        }
        quote_style = optTemp;
    }
    if (quote_style & OPTS.ENT_HTML_QUOTE_SINGLE) {
        string = string.replace(/'/g, '&#039;');
    }
    if (!noquotes) {
        string = string.replace(/"/g, '&quot;');
    }

    return string;
}


function initDataTable() {
    /*
     var b_destroy = false;
     if (typeof(oTable) != 'undefined') {
     var b_destroy = true;
     }
     */
    $('#clients-assigned-view').dataTable({
        "aaSorting": [
            [7, "asc"]
        ],
        'aoColumnDefs': [
            {'bSortable': false, "aTargets": [0, 1, 2, 3, 5, 6]}
        ],
        //'bDestroy' :b_destroy,
        'fnDrawCallback': function (oSettings) {
            runEditables();
        },
        'bAutoWidth': false,
        'bPaginate': false
        //"sPaginationType": "bootstrap",
    });

    $('#leads-assigned-view').dataTable({
        "aaSorting": [
            [1, "asc"]
        ],
        'fnDrawCallback': function (oSettings) {
            runEditables();
        },
        'bAutoWidth': false,
        'bPaginate': false
    });

};

function addslashes(str) {
    return (str + '').replace(/([\\"'])/g, "\\$1").replace(/\0/g, "\\0");
}

function nl2br(str, is_xhtml) {
    var breakTag = '';
    breakTag = '<br />';
    if (typeof is_xhtml != 'undefined' && !is_xhtml) {
        breakTag = '<br>';
    }
    return (str + '').replace(/([^>]?)\n/g, '$1' + breakTag + '\n');
}

function replaceAndSymbol(str) {
    str = str.replace(/&/, "%26");
    return str;
}

function removeCommas(str) {
    str = str.replace(/\,/g, '');
    return str;
}
function setCaretPosition(elemId, caretPos) {
    var elem = document.getElementById(elemId);

    if (elem != null) {
        if (elem.createTextRange) {
            var range = elem.createTextRange();
            range.move('character', caretPos);
            range.select();
        }
        else {
            if (elem.selectionStart) {
                elem.focus();
                elem.setSelectionRange(caretPos, caretPos);
            }
            else
                elem.focus();
        }
    }
}

function setStartTimeNow(textarea) {
    var d = new Date();
    var currYear = d.getFullYear();
    var currMonth = d.getMonth() + 1;
    var currDate = d.getDate();
    var currHour = d.getHours();
    var currMin = d.getMinutes();
    time = currYear + "-" +
        (currMonth < 10 ? "0" : "") + currMonth + "-" +
        (currDate < 10 ? "0" : "") + currDate + " " +
        (currHour < 10 ? "0" : "") + currHour + ":" +
        (currMin < 10 ? "0" : "") + currMin;

    theInput = document.getElementById(textarea);
    theInput.value = time + " - \n\n" + theInput.value;

    setCaretPosition(textarea, 19);
}

function setCopy(textarea) {
    textarea.rows = 14;
}

function closeCopy(textarea) {
    textarea.rows = 8;
}