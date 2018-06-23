jQuery(document).ready(function($){
  $("#rateYo").rateYo()
  .on("rateyo.set", function (e, data) {
    var post_id = $(this).data('post-id');
    var data = {
      'action': 'rate',
      'post_id': post_id,
      'rating': data.rating
    };
    jQuery.post(rating_ajax_object.ajax_url, data, function(response) {
      if ( response.status == 'success' ) {
        $("#rateYo").rateYo("option", "readOnly", true);
      } else {
        $("#rateYo").rateYo("option", "readOnly", false);
      }
    });
  });
});