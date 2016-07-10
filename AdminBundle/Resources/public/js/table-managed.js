var TableManaged = {

    // main function to initiate the module
    init: function (id, url, not_sortable_cols, columns, languageUrl, callback) {
        if (!jQuery().dataTable) {
            return;
        }

        $(id).dataTable({
            "bProcessing": true,
            "bServerSide": true,
            "sAjaxSource": url,
            "aoColumnDefs": [{
                "bSortable": false,
                "aTargets": not_sortable_cols
            }],
            "aoColumns": columns,
            "sPaginationType": "bootstrap",
            "oLanguage": {
                "sUrl": languageUrl
            },
            "aaSorting": [[ 0, "desc" ]] // Sort by first column descending
        }).on( 'draw.dt', function () {
            if(isFunction(callback)){
                callback();
            }
            
        });
    },
    
    // main function to initiate the module
    reload: function (id, url, not_sortable_cols, columns, languageUrl) {
        if (!jQuery().dataTable) {
            return;
        }

        $(id).dataTable().fnClearTable();
        $(id).dataTable().fnDestroy();
//        $(id).empty();
        
        $(id).dataTable({
            "bProcessing": true,
            "bServerSide": true,
            "sAjaxSource": url,
            "aoColumnDefs": [{
                "bSortable": false,
                "aTargets": not_sortable_cols
            }],
            "aoColumns": columns,
            "sPaginationType": "bootstrap",
            "oLanguage": {
                "sUrl": languageUrl
            },
            "aaSorting": [[ 0, "desc" ]] // Sort by first column descending
        });
        $(id).attr('style','width:100%');
    }
    
}

function isFunction(functionToCheck) {
 var getType = {};
 return functionToCheck && getType.toString.call(functionToCheck) === '[object Function]';
}