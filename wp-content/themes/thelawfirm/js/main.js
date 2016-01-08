/*

  KSS Boilerplate

  Site Development by Kitchen Sink Studios, Inc
  Developer: Travis Foshe

*/

$kss = [];

$(document).ready(function(){ 

// GLOBALS -----------------------
$kss.contentWidth = $('section').outerWidth();
$kss.contentHeight = $('section').outerHeight();

// SVG linked-to-inline conversion
$('img.svg').each(function(){
    var $img = $(this);
    var imgID = $img.attr('id');
    var imgClass = $img.attr('class');
    var imgURL = $img.attr('src');

    $.get(imgURL, function(data) {
        // Get the SVG tag, ignore the rest
        var $svg = $(data).find('svg');

        // Add replaced image's ID to the new SVG
        if(typeof imgID !== 'undefined') {
            $svg = $svg.attr('id', imgID);
        }
        // Add replaced image's classes to the new SVG
        if(typeof imgClass !== 'undefined') {
            $svg = $svg.attr('class', imgClass+' replaced-svg');
        }

        // Remove any invalid XML tags as per http://validator.w3.org
        $svg = $svg.removeAttr('xmlns:a');
        
        // Check if the viewport is set, else we gonna set it if we can.
        if(!$svg.attr('viewBox') && $svg.attr('height') && $svg.attr('width')) {
            $svg.attr('viewBox', '0 0 ' + $svg.attr('height') + ' ' + $svg.attr('width'))
        }

        // Replace image with new SVG
        $img.replaceWith($svg);

    }, 'xml');

});


// WINDOW RESIZE FUNCTIONS -----------------------
$(window).on('resize', function() {
  windowWidth = $(window).width();
  windowHeight = $(window).height();
});

$(window).on('scroll', function() {
    // what do
});


$(window).load(function() {
    $('#loader').animate({opacity:0}, 500, function() {
        $(this).hide();
        $('#page').animate({opacity:1}, 500, function() {
        });
    })
});



});