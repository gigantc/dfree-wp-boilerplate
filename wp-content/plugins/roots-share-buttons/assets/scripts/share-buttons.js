jQuery(document).ready(function() {
  jQuery('.entry-share-btns a').on('click', function(e) {
    e.preventDefault();
    var link = jQuery(this).attr('href');
    var width = 840;
    var height = 464;
    var popupName = 'popup_' + width + 'x' + height;
    var left = (screen.width-width) / 2;
    var top = 100;
    var params = 'width=' + width + ',height=' + height + ',location=no,menubar=no,scrollbars=yes,status=no,toolbar=no,left=' + left + ',top=' + top;
    window[popupName] = window.open(link, popupName, params);
    if (window.focus) {
      window[popupName].focus();
    }
    return true;
  });
});
