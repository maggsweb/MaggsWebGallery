$(document).ready(function () {

//            $(window).scroll(function() {
//                if ($(this).scrollTop()>50) {
//                    $('#mini-directory-nav').fadeIn();
//                    $('#directory-nav').fadeOut();
//                } else {
//                  $('#mini-directory-nav').fadeOut();
//                  $('#directory-nav').fadeIn();
//                }
//            });

    $('.image').on('click', function () {
        $image = $(this).attr('data-trigger');
        $('#image' + $image).trigger('click');
    });

    $( '.minidirimage' ).hover(
        function() {
            $('#currentdirname').text($(this).attr('data-title'));
        }, function() {
            $('#currentdirname').text($('#currentdirname').attr('data-current'));
        }
    );

});
