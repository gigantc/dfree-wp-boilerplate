/*

  dFree Boilerplate

  Site Development by Gigantc
  Developer: Daniel Freeman [@dFree]

*/

$(document).ready(function(){ 

// GLOBALS -----------------------
var like = 0,
    icarus = 0;




// WINDOW RESIZE FUNCTIONS -----------------------
var windowWidth = window.outerWidth,
    windowHeight = window.outerHeight;
window.addEventListener("resize", function(){
  windowWidth = window.outerWidth;
  windowHeight = window.outerHeight;
});


//returns the direct path to the theme
function getHomeUrl() {
  var href = window.location.hostname;
  if(href == "boiler.test"){
    var index = ('http://' + href + '/wp-content/themes/boiler');
  } else {
    var index = ('https://' + href + '/wp-content/themes/boiler');
  }
  return index;
}



// badhawk tag!!
console.log('--------------------------------------');
console.log('-=   built by badhawkworkshop.com   =-');
console.log('--------------------------------------');




});//end