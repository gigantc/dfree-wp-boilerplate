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


// BROWER DETECTION -----------------------
//mobile detection
var device = navigator.userAgent.toLowerCase();
var mobileos = device.match(/android|webos|iphone|ipad|ipod|blackberry|iemobile|opera mini/i);

//desktop detection
var BrowserDetect = {
    init: function () {
        this.browser = this.searchString(this.dataBrowser) || "Other";
        this.version = this.searchVersion(navigator.userAgent) || this.searchVersion(navigator.appVersion) || "Unknown";
    },
    searchString: function (data) {
        for (var i = 0; i < data.length; i++) {
            var dataString = data[i].string;
            this.versionSearchString = data[i].subString;

            if (dataString.indexOf(data[i].subString) !== -1) {
                return data[i].identity;
            }
        }
    },
    searchVersion: function (dataString) {
        var index = dataString.indexOf(this.versionSearchString);
        if (index === -1) {
            return;
        }

        var rv = dataString.indexOf("rv:");
        if (this.versionSearchString === "Trident" && rv !== -1) {
            return parseFloat(dataString.substring(rv + 3));
        } else {
            return parseFloat(dataString.substring(index + this.versionSearchString.length + 1));
        }
    },
    dataBrowser: [
        {string: navigator.userAgent, subString: "Chrome", identity: "Chrome"},
        {string: navigator.userAgent, subString: "MSIE", identity: "Explorer"},
        {string: navigator.userAgent, subString: "Trident", identity: "Explorer"},
        {string: navigator.userAgent, subString: "Firefox", identity: "Firefox"},
        {string: navigator.userAgent, subString: "Safari", identity: "Safari"},
        {string: navigator.userAgent, subString: "Opera", identity: "Opera"}
    ]
};
BrowserDetect.init();

var thisBrowser = BrowserDetect.browser;

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