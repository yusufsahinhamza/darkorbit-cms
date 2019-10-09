<footer class="page-footer grey darken-4">
  <div class="footer-copyright">
    <div class="container">
    © <?php echo date('Y'); ?> <?php echo SERVER_NAME; ?>
    <a class="grey-text text-lighten-4 right" href="https://elitepvpers.com/" target="_blank">Elitepvpers</a>
    </div>
  </div>
</footer>

</div>
<script type="text/javascript" src="<?php echo DOMAIN; ?>js/jquery-3.4.1.min.js"></script>
<script type="text/javascript" src="<?php echo DOMAIN; ?>js/materialize.min.js"></script>
<script type="text/javascript" src="<?php echo DOMAIN; ?>js/main.js"></script>

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

<?php if (Functions::IsLoggedIn() && isset($page[1]) && $page[1] === 'join') { ?>
<script type="text/javascript">
  $('input[name=search_clan]').on('keyup keypress keydown click', function(e) {
    if ($('input[name=search_clan]').val() != '') {
      $.ajax({
        url: '<?php echo DOMAIN; ?>api/',
        data: { action: 'search_clan', keywords: $('input[name=search_clan]').val() },
        type: 'POST',
        success:function(response) {
          $('#clan-list tbody').html('');

          var json = jQuery.parseJSON(response);
          for (var index in json) {
            $('#clan-list tbody').append('
              <tr>
                <td><a href="<?php echo DOMAIN; ?>clan/clan-details/'+json[index].id+'">['+ json[index].tag +'] '+json[index].name+'</a></td>
                <td>'+ json[index].members +'</td>
                <td>'+ json[index].rank +'</td>
                <td>'+ json[index].rankPoints +'</td>
              </tr>');
          }
        }
      });
    }
  });
</script>
<?php } ?>

<?php if (Functions::IsLoggedIn() && isset($page[1], $clanId, $clan) && $page[1] === 'clan_details' && $clan !== NULL) { ?>
<script type="text/javascript">
  $('#send_clan_application').submit(function(e) {
    e.preventDefault();

    var form = $(this);

    $.ajax({
      url: '<?php echo DOMAIN; ?>api/',
      data: form.serialize() + '&action=send_clan_application',
      type: 'POST',
      success:function(response) {
        var json = jQuery.parseJSON(response);

        if (json.status) {
          $('#send_clan_application textarea[name=text]').val('').attr('placeholder', 'Your application to this Clan is pending.').attr('disabled', true);
          $('#send_clan_application button').addClass('disabled');
        }

        if (json.message != '') {
          M.toast({html: '<span>'+ json.message +'</span>'});
        }
      }
    });
  });
</script>
<?php } ?>

<?php if (Functions::IsLoggedIn() && isset($page[0]) && $page[0] === 'equipment') { ?>
  <script type="text/javascript" src="<?php echo DOMAIN; ?>js/darkorbit/jquery.flashembed.js"></script>
  <script type='text/javascript'>
      function onFailFlashembed() {
        var html = '';

        html += '<div id="flashFail">';
        html += '<div class="flashFailHead">Get the Adobe Flash Player</div>';
        html += '<div class="flashFailHeadText">';
        html += 'In order to play <?php echo SERVER_NAME; ?>, you need the latest version of Flash Player. Just install it to start playing!';
        html += '<div class="flashFailHeadLink">';
        html += 'Download the Flash Player here for free: <a href=\"http://www.adobe.com/go/getflashplayer\" style=\"text-decoration: underline; color:#A0A0A0;\">Download Flash Player<\/a>';
        html += '</div>';
        html += '</div>';
        html += '</div>';

        jQuery('#equipment_container').html(html);
      }

      function expressInstallCallback(info) {
        onFailFlashembed();
      }

      jQuery(document).ready(
          function(){
              flashembed("equipment_container", {
                "onFail": onFailFlashembed,
                "src": "<?php echo DOMAIN; ?>swf_global/inventory/inventory.swf",
                "version": [10,0],
                "expressInstall": "<?php echo DOMAIN; ?>swf_global/expressInstall.swf",
                "onFail": function(){ onFailFlashembed(); },
                "width": 770,
                "height": 395,
                "id": "inventory",
                "wmode": "transparent"
              },
              {"cdn": "<?php echo DOMAIN; ?>",
              "nosid": "1",
              "navPoint": "2",
              "eventItemEnabled": "",
              "supporturl": "",
              "serverdesc": "",
              "server_code": "1",
              "jackpot": "0 EUR",
              "uridium_highlighted": "",
              "lang": "en",
              "sid": "<?php echo $player['sessionId']; ?>",
              "locale_hash": "",
              "dynamicHost": "<?php echo $_SERVER['SERVER_NAME']; ?>",
              "menu_layout_config_hash": "",
              "assets_config_hash": "",
              "items_config_hash": "",
              "useDeviceFonts": "0"});
          }
      );
  </script>
<?php } ?>

</body>
</html>
