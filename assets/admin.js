jQuery(function($){
  // Quick test when bulk page loads:
  if (typeof kdr_iaa_ajax !== 'undefined') {
    // Uncomment to test scan:
   
    $.post(kdr_iaa_ajax.ajax_url, {
      action: 'kdr_iaa_scan_missing',
      nonce: kdr_iaa_ajax.nonce
    }).done(function(res){
      console.log('Scan result', res);
    });
   
  }
});
