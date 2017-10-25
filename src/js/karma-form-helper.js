/**
 * Description
 */

/*global hoge: true*/

jQuery(document).ready(function ($) {

  'use strict';

  var $select = $('#user_id');
  if ($select.length) {

    $select.select2({
      placeholder: "",
      ajax: {
        url: $select.attr('data-end-point'),
        data: function (params) {
          return {
            s: params.term,
            _wpnonce: $('input[name=_wpnonce]').val()
          };
        },
        processResults: function (data) {
          return {
            results: $.map(data, function (user, index) {
              return {
                id: user.ID,
                text: user.display_name
              };
            })
          };
        }
      }
    });
  }

  var $form = $('#karma-point-editor')
  if ($form.length) {
    $form.submit(function (e) {
      e.preventDefault();
      var query = {
        type: $('#type').val(),
        status: $('#status').val(),
        point: $('#point').val()
      };
      if ($select.length) {
        query.user_id = $select.val();
      }
      var $pointId = $form.find('input[name=point_id]');
      if ( $pointId.length ) {
        query.point_id = $pointId.val();
      }
      $form.addClass('loading');
      $.ajax({
        method: $form.attr('data-method'),
        url: $form.attr('action'),
        beforeSend: function( xhr ){
          xhr.setRequestHeader('X-WP-Nonce', $form.find('input[name=_wpnonce]').val() );
        },
        data: query
      }).done(function(result){
        window.location.href = $form.attr('data-return') + result.point_id;
      }).fail(function(response){
        alert(response.responseJSON.message);
      }).always(function(){
        $form.removeClass('loading');
      });
    });
  }
});
