function switchEditTab(button)
{
    var id = $(button).data('id');
    if (id == null) {
        return;
    }

    $('.editor .body').hide();
    $('.editor .header button').removeClass('active');
    $('.editor .body[data-tab-id=' + id + ']').show();
    $(button).addClass('active');
}

function executeMassAction(button)
{
    var buttonData = $(button).data();
    if (buttonData.url == undefined) {
        return;
    }

    if (buttonData.confirm != undefined) {
        if (!confirm(buttonData.confirm)) {
            return;
        }
    }

    var checkboxData = [];
    $("input.row_selector:checked").each(function() {
        var data = $(this).data();
        checkboxData.push(data);
    });

    $('<form>', {
        "html": '<input type="hidden" name="keys" value="' + encodeURIComponent(JSON.stringify(checkboxData)) + '" />',
        "action": buttonData.url,
        "method": "post"
    }).appendTo(document.body).submit();
}

function select_nav_element(id)
{
    var element = null;
    $('div.nav ul li').each(function() {
        if ($(this).data('id') == id) {
            element = $(this);
        }
    });
    if (element == null) {
        return;
    }

    $('div.nav ul').hide();
    $('div.nav ul li a').removeClass('active');
    $('div.nav ul[data-parent-id=0]').show();
    var treeList = element.data('tree');
    for (var i = 0; i < treeList.length; i++) {
        var id = treeList[i];
        $('div.nav ul[data-parent-id='+id+']').show();
        $('div.nav ul li[data-id='+id+'] a').addClass('active');
    }
}

function select_nav_element_by_name(name)
{
    var element = null;
    $('div.nav ul li').each(function() {
        if ($(this).data('name') == name) {
            element = $(this);
        }
    });
    if (element == null) {
        return;
    }

    select_nav_element(element.data('id'));
}

$(document).ready(function() {
    $('table.list thead th input[type=checkbox]').change(function() {
        if ($(this).attr('checked') === 'checked') {
            $('table.list tbody td input[type=checkbox]').attr('checked', 'checked');
        } else {
            $('table.list tbody td input[type=checkbox]').attr('checked', null);
        }
    });

    $('table.list tfoot button').click(function() {
        executeMassAction(this);
    });

    $('.tooltip').tooltip({
        content: function() {
            return $(this).data('text');
        },
        items: ".tooltip",
        show: true,
        hide: true
    });
    $('.tooltip').each(function() {
        $(this).tooltip("option", "tooltipClass", $(this).data('class'));
    });

    if (typeof sortingDef !== 'undefined' && sortingDef !== null) {
        var fixHelper = function(e, ui) {
            ui.children().each(function() {
                $(this).width($(this).width());
            });
            return ui;
        };

        $('table.list tbody').sortable(
        {
            helper: fixHelper,
            cursor: "move",
            containment: $('table.list'),
            axis: "y",
            opacity: 0.75,
            revert: true,
            update: function(event, ui) {
                var itemList = $(this).sortable('toArray', {attribute: 'data-key'});
                var data = {
                    orderColumn: sortingDef.orderColumn,
                    groupColumns: sortingDef.groupColumns,
                    itemList: itemList
                };
                console.log(data);
                $.ajax(
                {
                    url: sortingDef.url,
                    type: "post",
                    data: data
                });
            }
        });
    }
});
