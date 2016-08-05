/*

  dFree Boilerplate

  Site Development by Gigantc
  Developer: Daniel Freeman [@dFree]

*/

$(document).ready(function(){ 

// GLOBALS -----------------------
var like = 0,
    icarus = 0;





// BROWER DETECTION -----------------------
//mobile detection
var device = navigator.userAgent.toLowerCase();
var mobileos = device.match(/android|webos|iphone|ipad|ipod|pocket|psp|kindle|avantgo|blazer|midori|tablet|palm|maemo|plucker|phone|blackberry|symbian|iemobile|mobile|zunewp7|windows phone|opera mini/i);

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
$(window).resize(function() {
  windowWidth = $(window).width();
  windowHeight = $(window).height();
});




});//end