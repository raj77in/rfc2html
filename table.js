function overlay() {
  el = document.getElementById("overlay");
  el.style.visibility = (el.style.visibility == "visible") ? "hidden" : "visible";
}

(function ($) {
  $.fn.styleTable = function (options) {
    var defaults = {
      css: 'ui-styled-table'
    };
    options = $.extend(defaults, options);

    return this.each(function () {
      $this = $(this);
      $this.addClass(options.css);

      $this.on('mouseover mouseout', 'tbody tr', function (event) {
        $(this).children().toggleClass("ui-state-hover",
            event.type == 'mouseover');
      });

      $this.find("th").addClass("ui-state-default");
      $this.find("td").addClass("ui-widget-content");
      $this.find("tr:last-child").addClass("last-child");
      $this.find( "tr:odd" ).css( "background-color", "#bbbbff" );
    });
  };
})(jQuery);

function removeHighlighting(highlightedElements){
  highlightedElements.each(function(){
    var element = $(this);
    element.replaceWith(element.html());
  })
}

function addHighlighting(element, textToHighlight){
  var text = element.text();
  var highlightedText = '<em>' + textToHighlight + '</em>';
  var newText = text.replace(textToHighlight, highlightedText);

  element.html(newText);
}

$("#search").on("keyup", function() {
  var value = $(this).val();

  removeHighlighting($(".Table tr em"));

  $(".Table tr").each(function(index) {
    if (index !== 0) {
      $row = $(this);

      var $tdElement = $row.find("td:first");
      var id = $tdElement.text();
      var matchedIndex = id.indexOf(value);

      if (matchedIndex != 0) {
        $row.hide();
      }
      else {
        addHighlighting($tdElement, value);
        $row.show();
      }
    }
  });
});


$(document).ready(function () {
  $(".Table").styleTable();
  //$('#Table').DataTable();
//Column Filter::::
var table = $('#Table').DataTable();
 
table.columns().flatten().each( function ( colIdx ) {
    // Create the select list and search operation
    var select = $('<select />')
        .appendTo(
            table.column(colIdx).footer()
        )
        .on( 'change', function () {
            table
                .column( colIdx )
                .search( $(this).val() )
                .draw();
        } );
 
    // Get the search data for the first column and add to the select list
    table
        .column( colIdx )
        .cache( 'search' )
        .sort()
        .unique()
        .each( function ( d ) {
            select.append( $('<option value="'+d+'">'+d+'</option>') );
        } );
} );

});

