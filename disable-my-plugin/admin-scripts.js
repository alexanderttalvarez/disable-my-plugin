jQuery(document).ready(function($){

  // Calling functions
  add_more_button();
  delete_button();
  typeOfHour();
  checkingForm();

  // It adds a row to the table for a new plugin
  function add_more_button() {

    $('.dmp-add-plugin').on('click',function() {

      // Setting the next position
      if($('.current-pos').length) {
        pos = $('table.dmp-table > tbody > tr:last-child .current-pos').html();
        pos++;
      } else {
        $('table.dmp-table > tbody > tr:last-child').remove();
        pos = 0;
      }

      var data = {
        action: 'disable_my_plugin_row_code_raw',
        security : DMP.security,
        pos: pos
      };

      $.ajax({
            url: DMP.ajaxurl,
            datatype: 'html',
            type: 'post',
            data: data,
            error: function(msg) {
              alert("There was an error adding a new plugin to the list.")
            },
            success: function(msg) {
                $('.dmp-table > tbody').append(msg);
            }
        });
    });

  }

  // Button for deleting the row
  function delete_button() {

    $(document).on('click','.delete-button', function() {

      $(this).closest('tr').remove();
      if(!$('.current-pos').length) {
        $('table.dmp-table > tbody').append("<tr><td colspan='5'>There are no plugins in the list at this moment.</td></tr>");
      }

    });

  }

  // It shows the Time Zone Field depending on the "Type of hour" field selection
  function typeOfHour() {

    $(document).on('change','.radio-hour',function() {

      if( $(this).val() === "server" ) {
        $(this).closest('tr').find('.time_zone').css({'visibility':'hidden'});
      } else {
        $(this).closest('tr').find('.time_zone').css({'visibility':'visible'});
      }

    });

  }

  // It checks all the form data are filled
  function checkingForm() {

    $('form#dmp-form').submit(function() {

      var controller = true;

      $('.start_hour').each(function() {
        if($(this).val() === '') {
          controller = false;
          $(this).css({'background-color':'#f2dede'});
        }
      });

      $('.end_hour').each(function() {
        if($(this).val() === '') {
          controller = false;
          $(this).css({'background-color':'#f2dede'});
        }
      });

      // Checking that no data was empty
      if( controller === false ) {

        $('.dmp-errors').fadeOut("slow",function() {
          $('.dmp-errors-text').empty();
          $('.dmp-errors-text').html("There are empty fields.");
          $('.dmp-errors').fadeIn();
        });

        return false;

      } else {
        $('.dmp-errors').fadeOut("slow");
      }

    });

    $(document).on('change','.start_hour',function() {
      $(this).css({'background-color':'white'});
    });

    $(document).on('change','.end_hour',function() {
      $(this).css({'background-color':'white'});
    });

  }


});
