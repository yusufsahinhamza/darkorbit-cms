<footer class="page-footer grey darken-4">
  <div class="footer-copyright">
    <div class="container">
    © <?php echo date('Y'); ?> <?php echo SERVER_NAME; ?>
    <a class="grey-text text-lighten-4 right" href="https://elitepvpers.com/" target="_blank">Elitepvpers</a>
    </div>
  </div>
</footer>

</div>
<script type="text/javascript" src="js/jquery-3.4.1.min.js"></script>
<script type="text/javascript" src="js/materialize.min.js"></script>
<script type="text/javascript" src="js/main.js"></script>

<?php if (!Functions::IsLoggedIn() && isset($page[0]) && $page[0] === 'index') { ?>
<script type="text/javascript">
  $('#modal #agree').click(function() {
    $('#register input[name=agreement]').prop('checked', true);
  });

  $('#register').submit(function(e) {
    e.preventDefault();

    if ($('#register input[name=agreement]').prop('checked')) {
      var form = $(this);

      $.ajax({
        type: 'POST',
        url: '<?php echo DOMAIN; ?>api/',
        data: form.serialize() + '&action=register',
        success: function(response) {
          var json = jQuery.parseJSON(response);

          for (var input in json.inputs) {
            $('#register input[name='+input+'] + label + span').attr('data-error', json.inputs[input].error);
            $('#register input[name='+input+']').removeClass('valid invalid');
            $('#register input[name='+input+']').addClass(json.inputs[input].validate);
          }

          if (json.message != '') {
            M.toast({html: '<span>'+ json.message +'</span>'});
          }
        }
      });
    } else {
      M.toast({html: '<span>Please agree Terms & Conditions in order to register!</span>'});
    }
  });

  $('#login').submit(function(e) {
    e.preventDefault();

    var form = $(this);

    $.ajax({
      type: 'POST',
      url: '<?php echo DOMAIN; ?>api/',
      data: form.serialize() + '&action=login',
      success: function(response) {
        var json = jQuery.parseJSON(response);

        for (var input in json.inputs) {
          $('#login input[name='+input+'] + label + span').attr('data-error', json.inputs[input].error);
          $('#login input[name='+input+']').removeClass('valid invalid');
          $('#login input[name='+input+']').addClass(json.inputs[input].validate);
        }

        if (json.message != '') {
          if (json.message == '1') {
            location.reload();
          } else {
            M.toast({html: '<span>'+ json.message +'</span>'});
          }
        }
      }
    });
  });
</script>
<?php } ?>

<?php if (Functions::IsLoggedIn() && isset($page[0]) && $page[0] === 'company-select') { ?>
<script type="text/javascript">
  $('.company').click(function() {
    var company = $(this).attr('class').split(' ')[1];

    $.ajax({
      type: 'POST',
      url: '<?php echo DOMAIN; ?>api/',
      data: { action: 'company_select', company: company },
      success: function(response) {
        var json = jQuery.parseJSON(response);

        if (json.message != '') {
          if (json.message == '1') {
            location.reload();
          } else {
            M.toast({html: '<span>'+ json.message +'</span>'});
          }
        }
      }
    });
  });
</script>
<?php } ?>

</body>
</html>
