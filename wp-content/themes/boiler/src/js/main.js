/*

  dFree Boilerplate

  Site Development by Badhawk Workshop
  Developer: Daniel Freeman [@dFree]

*/

document.addEventListener("DOMContentLoaded", function(){




// WINDOW RESIZE FUNCTIONS -----------------------
let windowWidth = window.outerWidth,
    windowHeight = window.outerHeight;
    
window.addEventListener("resize", function(){
  windowWidth = window.outerWidth;
  windowHeight = window.outerHeight;
});


//returns the direct path to the theme
const getHomeUrl = () => {
  const href = window.location.hostname;
  console.log("getting home url");
  if(href == "boiler.test"){
    const index = ('http://' + href + '/wp-content/themes/boiler');
  } else {
    const index = ('https://' + href + '/wp-content/themes/boiler');
  }
  return index;
}


// badhawk tag!!
console.log('-=   built with ♥ by badhawkworkshop.com   =-');




});//end