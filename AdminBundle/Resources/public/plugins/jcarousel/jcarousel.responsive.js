(function($) {
    $(function() {

        $('.jcarousel').each(function( index ) {
            var jcarousel = $( this );
            jcarousel
                .on('jcarousel:reload jcarousel:create', function () {
                    var width = jcarousel.innerWidth();

                    if (width >= 1024) {
                        width = width / 5;
                    } else if (width <= 1023 && width >= 400) {
                        width = width / 3;
                    } else if (width <= 399 ) {
                        width = (width / 1) - 5;
                    }

                    jcarousel.jcarousel('items').css('width', width + 'px');
                })
                .jcarousel({
                    wrap: 'circular'
                });
            
            $( this ).parent().find('.jcarousel-pagination')
                .on('jcarouselpagination:active', 'a', function() {
                    $(this).addClass('active');
                })
                .on('jcarouselpagination:inactive', 'a', function() {
                    $(this).removeClass('active');
                })
                .on('click', function(e) {
                    e.preventDefault();
                })
                .jcarouselPagination({
                    perPage: 1,
                    item: function(page) {
                        return '<a href="#' + page + '">' + page + '</a>';
                    }
                });
             
          });
        
    });
})(jQuery);