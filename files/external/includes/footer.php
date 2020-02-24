<footer class="page-footer grey darken-4">
  <div class="footer-copyright">
    <div class="container">
    © <?php echo date('Y'); ?> <?php echo SERVER_NAME; ?> | Online: <?php echo Socket::Get('OnlineCount', array('Return' => 0)); ?>
    <div class="right">
      <a class="grey-text text-lighten-4" href="https://darkorbit.com/" target="_blank">DarkOrbit</a>
      |
      <a class="grey-text text-lighten-4" href="https://discord.gg/RwytCms" target="_blank">Discord</a>
      |
      <a class="grey-text text-lighten-4" href="https://elitepvpers.com/" target="_blank">Elitepvpers</a>
    </div>
    </div>
  </div>
</footer>

</div>
<script type="text/javascript" src="<?php echo DOMAIN; ?>js/jquery-3.4.1.min.js"></script>
<script type="text/javascript" src="<?php echo DOMAIN; ?>js/materialize.min.js"></script>
<script type="text/javascript" src="<?php echo DOMAIN; ?>js/main.js"></script>

<?php if (!Functions::IsLoggedIn() && $page[0] === 'index') { ?>
<script type="text/javascript">
  $('#modal #agree').click(function() {
    $('#register input[name=agreement]').prop('checked', true);
  });

  $('#register').submit(function(e) {
    e.preventDefault();

    var form = $(this);

    if ($('#register input[name=agreement]').prop('checked')) {
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

        if (json.status) {
          location.reload();
        } else if (json.message != '') {
          var html = '<span>'+ json.message +'</span>';

          if (json.toastAction != '') {
            html += json.toastAction;
          }

          M.toast({html: html});
        }
      }
    });
  });

  $('body').on('click', '#send-link-again', function() {
    $.ajax({
      type: 'POST',
      url: '<?php echo DOMAIN; ?>api/',
      data: { action: 'send_link_again', username: $('#l-username').val() },
      success: function(response) {
        var json = jQuery.parseJSON(response);

        if (json.message != '') {
          M.toast({html: '<span>'+ json.message +'</span>'});
        }
      }
    });
  });
</script>
<?php } ?>

<?php if (Functions::IsLoggedIn() && $page[0] === 'company_select' && (isset($player) && $player['factionId'] == 0)) { ?>
<script type="text/javascript">
  $('.company').click(function() {
    var company = $(this).attr('class').split(' ')[1];

    $.ajax({
      type: 'POST',
      url: '<?php echo DOMAIN; ?>api/',
      data: { action: 'company_select', company: company },
      success: function(response) {
        var json = jQuery.parseJSON(response);

        if (json.status) {
          location.reload();
        } else if (json.message != '') {
          M.toast({html: '<span>'+ json.message +'</span>'});
        }
      }
    });
  });
</script>
<?php } ?>

<?php if (Functions::IsLoggedIn() && $page[0] === 'ships') { ?>
<script type="text/javascript">
  $('.ship').click(function() {
    var ship = $(this).attr('id');

    $.ajax({
      type: 'POST',
      url: '<?php echo DOMAIN; ?>api/',
      data: { action: 'change_ship', ship: ship },
      success: function(response) {
        var json = jQuery.parseJSON(response);

        if (json.status) {
          $(location).attr('href', "<?php echo DOMAIN;?>equipment");
        } else if (json.message != '') {
          M.toast({html: '<span>'+ json.message +'</span>'});
        }
      }
    });
  });
</script>
<?php } ?>

<?php if (Functions::IsLoggedIn() && $page[0] === 'skill_tree') { ?>
<script type="text/javascript">
  $('#exchangeLogdisks').click(function() {
    var button = $(this);

    if (!button.is(':disabled')) {
      $.ajax({
        type: 'POST',
        url: '<?php echo DOMAIN; ?>api/',
        data: { action: 'exchange_logdisks' },
        success: function(response) {
          var json = jQuery.parseJSON(response);

          if (json.newStatus) {
            $('#logdisks').text(json.newStatus.logdisks);
            $('#requiredLogdisks').text(json.newStatus.requiredLogdisks);
            $('#researchPoints').text(json.newStatus.researchPoints);

            if (json.newStatus.logdisks < json.newStatus.requiredLogdisks || json.newStatus.researchPointsMaxed) {
              button.attr('disabled', true);
            }

            $('.skill').children().removeClass('noCursor');
          }

          if (json.message != '') {
            M.toast({html: '<span>'+ json.message +'</span>'});
          }
        }
      });
    }
  });

  $('#resetSkills').click(function() {
    var button = $(this);

    if (button.is(':visible')) {
      $.ajax({
        type: 'POST',
        url: '<?php echo DOMAIN; ?>api/',
        data: { action: 'reset_skills' },
        success: function(response) {
          var json = jQuery.parseJSON(response);

          if (json.status) {
            location.reload();
          } else if (json.message != '') {
            M.toast({html: '<span>'+ json.message +'</span>'});
          }
        }
      });
    }
  });

  $('.skill').click(function() {
    if (parseInt($('#researchPoints').text()) >= 1 && parseInt($(this).find('.currentLevel').text()) != parseInt($(this).find('.maxLevel').text()) && !$(this).children().hasClass('skill_effect_inactive')) {
      var skill = $(this).attr('id');

      $.ajax({
        type: 'POST',
        url: '<?php echo DOMAIN; ?>api/',
        data: { action: 'use_researchPoints', skill: skill },
        success: function(response) {
          var json = jQuery.parseJSON(response);

          if (json.newStatus) {
            $('#'+ skill +'').find('.currentLevel').text(json.newStatus.currentLevel);
            $('#'+ skill +'').attr('data-tooltip', json.newStatus.tooltip);
            $('.tooltipped').tooltip({ html: true });

            $('#researchPoints').text(json.newStatus.researchPoints);
            $('#usedResearchPoints').text(json.newStatus.usedResearchPoints);

            if (json.newStatus.isMaxed) {
              $('#'+ skill +'').find('.skillPoints').removeClass('skilltree_font_fail_skillPoints').addClass('skilltree_font_ismax');

              if (json.newStatus.nextSkill) {
                $('#'+ json.newStatus.nextSkill +'').children().removeClass('skill_effect_inactive').addClass('skill_effect');
              }
            }

            if (json.newStatus.researchPoints <= 0) {
              $('.skill').children().addClass('noCursor');
            }

            if (!$('.modal-trigger').is(':visible')) {
              $('.modal-trigger').css({display: 'inline-block'});
            }
          }

          if (json.message != '') {
            M.toast({html: '<span>'+ json.message +'</span>'});
          }
        }
      });
    }
  });
</script>
<?php } ?>

<?php if (Functions::IsLoggedIn() && $page[0] === 'shop') { ?>
<script type="text/javascript">
  var currentItemId = -1;
  var currentItemName = "%item_name%";
  var currentItemPrice = "%item_price%";

  $('.buy').click(function() {
    var itemId = $(this).data('item-id');

    if (currentItemId != itemId) {
      currentItemId = itemId;

      var itemName = $('#item-'+ currentItemId +'').find('.card-title').text();
      var itemPrice = $('#item-'+ currentItemId +'').find('.card-content p').text();

      $('#modal p').text($('#modal p').text().replace(currentItemName, itemName)).text($('#modal p').text().replace(currentItemPrice, itemPrice));

      currentItemName = itemName;
      currentItemPrice = itemPrice;
    }
  });

  $('#confirm-buy').click(function() {
    if (currentItemId != -1) {

      var amountInput = $('#item-'+ currentItemId +'').find('input[name=amount]').val();
      var amount = amountInput ? amountInput: 0;

      $.ajax({
        url: '<?php echo DOMAIN; ?>api/',
        data: { action: 'buy', itemId: currentItemId, amount: amount },
        type: 'POST',
        success:function(response) {
          var json = jQuery.parseJSON(response);

          if (json.newStatus) {
            $('#data #uridium').text(json.newStatus.uridium);
            $('#data #credits').text(json.newStatus.credits);
          }

          if (json.message != '') {
            M.toast({html: '<span>'+ json.message +'</span>'});
          }
        }
      });
    }
  });

  $('input[name=amount]').on('keyup keypress keydown click', function() {
    var price = $(this).prev().val();

    if ($.isNumeric(price) && $.isNumeric($(this).val())) {
      if (parseInt($(this).val()) <= 0) {
        $(this).val('1');
      }

      var sum = (parseInt(price) * parseInt($(this).val())).toString().replace(/(\d)(?=(\d{3})+(?!\d))/g, '$1.');
      $(this).parent().prev().find('.price').text(sum);
    }
  });
</script>
<?php } ?>

<?php if (Functions::IsLoggedIn() && isset($page[1]) && $page[1] === 'join') { ?>
<script type="text/javascript">
  <?php if (count($array) >= 1) { ?>

  var currentWpClanName = '%clan_name%';
  var currentWpClanId = 0;

  $('.withdraw-pending').click(function() {
    var clanId = $(this).data('clan-id');

    if (currentWpClanId != clanId) {
      var name = $(this).data('clan-name');

      $('#modal p').text($('#modal p').text().replace(currentWpClanName, name));

      currentWpClanId = clanId;
      currentWpClanName = name;
    }
  });

  $('#withdraw').click(function() {
    if (currentWpClanId != 0) {
      var table = $('#open-clan-applications');

      $.ajax({
        type: 'POST',
        url: '<?php echo DOMAIN; ?>api/',
        data: { action: 'withdraw_pending_application', clanId: currentWpClanId },
        success: function(response) {
          var json = jQuery.parseJSON(response);

          if (json.status) {
            if (table.find('tbody tr').length <= 1) {
              table.prev().remove();
              table.remove();
            } else {
              table.find('#pending-application-'+ currentWpClanId +'').remove();
            }
          }

          if (json.message != '') {
            M.toast({html: '<span>'+ json.message +'</span>'});
          }
        }
      });
    }
  });

  <?php } ?>

  $('input[name=search_clan]').on('keyup keypress keydown click', function() {
    var value = $(this).val();

    if (value != '') {
      $.ajax({
        url: '<?php echo DOMAIN; ?>api/',
        data: { action: 'search_clan', keywords: value },
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

<?php if (Functions::IsLoggedIn() && isset($page[1], $clan) && $page[1] === 'diplomacy' && $clan !== NULL) { ?>
<script type="text/javascript">
$('input[name=keywords]').on('keyup keypress keydown click', function() {
  var value = $(this).val();

  if (value != '') {
    $.ajax({
      url: '<?php echo DOMAIN; ?>api/',
      data: { action: 'diplomacy_search_clan', keywords: value },
      type: 'POST',
      success:function(response) {
        $('#dropdown3').html('');
        $('#dropdown3').css({display: 'block', opacity: 1});

        var json = jQuery.parseJSON(response);
        for (var index in json) {
          $('#dropdown3').append('
            <li>
              <a href="javascript:void(0)" data-clan-id="'+ json[index].id +'">['+ json[index].tag +'] '+ json[index].name +'</a>
            </li>');
        }
      }
    });
  } else {
    $('#dropdown3').css({display: 'none', opacity: 0});
    $('input[name=keywords]').val('');
    $('input[name=clanId]').val(0);
  }
});

$('#request_diplomacy').submit(function(e) {
  e.preventDefault();

  var form = $(this);

  $.ajax({
    url: '<?php echo DOMAIN; ?>api/',
    data: form.serialize() + '&action=request_diplomacy',
    type: 'POST',
    success:function(response) {
      var json = jQuery.parseJSON(response);

      if (json.request) {
        $('#open_applications_button').css({display: 'inline-block'});

        $('#pending-requests tbody').append('
          <tr id="pending-request-'+ json.request.id +'">
            <td>'+ json.request.date +'</td>
            <td>'+ json.request.clan.name +'</td>
            <td>'+ json.request.form +'</td>
            <td>Waiting...</td>
            <td><button data-request-id="'+ json.request.id +'" class="cancel-request btn grey darken-1 waves-effect waves-light col s12">CANCEL</button></td>
          </tr>');
      }

      if (json.declared) {
        $('#clan-diplomacy tbody').append('
          <tr id="diplomacy-'+ json.declared.id +'">
            <td>'+ json.declared.clan.name +'</td>
            <td>'+ json.declared.form +'</td>
            <td>'+ json.declared.date +'</td>
            <td><button data-clan-id="'+ json.declared.clan.id +'" data-diplomacy-clan-name="'+ json.declared.clan.name +'" data-diplomacy-form="End War" class="end-war btn grey darken-1 waves-effect waves-light col s12 modal-trigger" href="#modal2">CANCEL</button></td>
          </tr>');
      }

      if (json.message != '') {
        M.toast({html: '<span>'+ json.message +'</span>'});
      }
    }
  });
});

$('body').on('click', '.cancel-request', function() {
  var requestId = $(this).data('request-id');

  $.ajax({
    type: 'POST',
    url: '<?php echo DOMAIN; ?>api/',
    data: { action: 'cancel_diplomacy_request', requestId: requestId },
    success: function(response) {
      var json = jQuery.parseJSON(response);

      if (json.status) {
        $('#pending-requests').find('#pending-request-'+ requestId +'').remove();

        if ($('#pending-requests').length <= 1) {
          $('#modal').modal('close');
          $('#open_applications_button').css({display: 'none'});
        }
      }

      if (json.message != '') {
        M.toast({html: '<span>'+ json.message +'</span>'});
      }
    }
  });
});

$('body').on('click', '.end-diplomacy', function() {
  var diplomacyId = $(this).data('diplomacy-id');

  $.ajax({
    type: 'POST',
    url: '<?php echo DOMAIN; ?>api/',
    data: { action: 'end_diplomacy', diplomacyId: diplomacyId },
    success: function(response) {
      var json = jQuery.parseJSON(response);

      if (json.status) {
        $('#diplomacy-'+ diplomacyId +'').remove();
      }

      if (json.message != '') {
        M.toast({html: '<span>'+ json.message +'</span>'});
      }
    }
  });
});

$('body').on('click', '#dropdown3 li a', function() {
  $('#dropdown3').css({display: 'none', opacity: 0});
  $('input[name=keywords]').val($(this).text());
  $('input[name=clanId]').val($(this).data('clan-id'));
});

var currentDrClanName = '%clan_name%';
var currentDrForm = '%form%';
var currentDrId = 0;

$('.view-request').click(function() {
  var requestId = $(this).data('request-id');

  if (currentDrId != requestId) {
    var name = $(this).data('request-clan-name');
    var form = $(this).data('request-form');

    $('#modal1 h6').text($('#modal1 h6').text().replace(currentDrClanName, name));
    $('#modal1 p').text($('#modal1 p').text().replace(currentDrForm, form));

    currentDrId = requestId;
    currentDrClanName = name;
    currentDrForm = form;
  }
});

$('#decline').click(function() {
  if (currentDrId != 0) {
    $.ajax({
      type: 'POST',
      url: '<?php echo DOMAIN; ?>api/',
      data: { action: 'decline_diplomacy_request', requestId: currentDrId },
      success: function(response) {
        var json = jQuery.parseJSON(response);

        if (json.status) {
          $('#request-'+ currentDrId +'').remove();
        }

        if (json.message != '') {
          M.toast({html: '<span>'+ json.message +'</span>'});
        }
      }
    });
  }
});

$('#accept').click(function() {
  if (currentDrId != 0) {
    $.ajax({
      type: 'POST',
      url: '<?php echo DOMAIN; ?>api/',
      data: { action: 'accept_diplomacy_request', requestId: currentDrId },
      success: function(response) {
        var json = jQuery.parseJSON(response);

        if (json.status) {
          $('#request-'+ currentDrId +'').remove();
        }

        if (json.warEnded) {
          $('#diplomacy-'+ json.warEnded.id +'').remove();
        }

        if (json.acceptedRequest) {
          $('#clan-diplomacy tbody').append('
            <tr id="diplomacy-'+ json.acceptedRequest.id +'">
              <td>'+ json.acceptedRequest.name +'</td>
              <td>'+ json.acceptedRequest.form +'</td>
              <td>'+ json.acceptedRequest.date +'</td>
              <td><button data-diplomacy-id="'+ json.acceptedRequest.id +'" class="end-diplomacy btn grey darken-1 waves-effect waves-light col s12">CANCEL</button></td>
            </tr>');
        }

        if (json.message != '') {
          M.toast({html: '<span>'+ json.message +'</span>'});
        }
      }
    });
  }
});






var currentEwdClanName = '%clan_name%';
var currentEwdForm = '%form%';
var currentEwdClanId = 0;

$('body').on('click', '.end-war', function() {
  var clanId = $(this).data('clan-id');

  if (currentEwdClanId != clanId) {
    var name = $(this).data('diplomacy-clan-name');
    var form = $(this).data('diplomacy-form');

    $('#modal2 h6').text($('#modal2 h6').text().replace(currentEwdClanName, name));
    $('#modal2 p').text($('#modal2 p').text().replace(currentEwdForm, form));

    currentEwdClanId = clanId;
    currentEwdClanName = name;
    currentEwdForm = form;
  }
});

$('#end-war').click(function() {
  if (currentEwdClanId != 0) {
    $.ajax({
      type: 'POST',
      url: '<?php echo DOMAIN; ?>api/',
      data: { action: 'end_war_request', clanId: currentEwdClanId },
      success: function(response) {
        var json = jQuery.parseJSON(response);

        if (json.request) {
          $('#open_applications_button').css({display: 'inline-block'});

          $('#pending-requests tbody').append('
            <tr id="pending-request-'+ json.request.id +'">
              <td>'+ json.request.date +'</td>
              <td>'+ json.request.clan.name +'</td>
              <td>'+ json.request.form +'</td>
              <td>Waiting...</td>
              <td><button data-request-id="'+ json.request.id +'" class="cancel-request btn grey darken-1 waves-effect waves-light col s12">CANCEL</button></td>
            </tr>');
        }

        if (json.message != '') {
          M.toast({html: '<span>'+ json.message +'</span>'});
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

<?php if (Functions::IsLoggedIn() && $page[0] === 'settings') { ?>
<script type="text/javascript">
  $('#change_pilot_name').submit(function(e) {
    e.preventDefault();

    var form = $(this);

    $.ajax({
      url: '<?php echo DOMAIN; ?>api/',
      data: form.serialize() + '&action=change_pilot_name',
      type: 'POST',
      success:function(response) {
        var json = jQuery.parseJSON(response);

        for (var input in json.inputs) {
          $('#change_pilot_name input[name='+input+'] + label + span').attr('data-error', json.inputs[input].error);
          $('#change_pilot_name input[name='+input+']').removeClass('valid invalid');
          $('#change_pilot_name input[name='+input+']').addClass(json.inputs[input].validate);
        }

        if (json.message != '') {
          M.toast({html: '<span>'+ json.message +'</span>'});
        }
      }
    });
  });

  $('input[name=version]').change(function() {
    var version = $(this).prop('checked');
    $.ajax({
      url: '<?php echo DOMAIN; ?>api/',
      data: { action: 'change_version', version: version },
      type: 'POST',
      success:function(response) {
        var json = jQuery.parseJSON(response);

        if (json.message != '') {
          M.toast({html: '<span>'+ json.message +'</span>'});
        }
      }
    });
  });
</script>
<?php } ?>

<?php if (Functions::IsLoggedIn() && isset($page[1]) && $page[1] === 'found') { ?>
<script type="text/javascript">
  $('#found_clan').submit(function(e) {
    e.preventDefault();

    var form = $(this);

    $.ajax({
      url: '<?php echo DOMAIN; ?>api/',
      data: form.serialize() + '&action=found_clan',
      type: 'POST',
      success:function(response) {
        var json = jQuery.parseJSON(response);

        for (var input in json.inputs) {
          var inputType = input !== 'description' ? 'input' : 'textarea';
          $('#found_clan '+inputType+'[name='+input+'] + label + span').attr('data-error', json.inputs[input].error);
          $('#found_clan '+inputType+'[name='+input+']').removeClass('valid invalid');
          $('#found_clan '+inputType+'[name='+input+']').addClass(json.inputs[input].validate);
        }

        if (json.status) {
          location.reload();
        } else if (json.message != '') {
          M.toast({html: '<span>'+ json.message +'</span>'});
        }
      }
    });
  });
</script>
<?php } ?>

<?php if (Functions::IsLoggedIn() && isset($page[1]) && $page[1] === 'company') { ?>
<script type="text/javascript">
  var currentFactionCode = "";
  var currentFactionName = "%faction_name%";

  $('.company').click(function() {
    var factionCode = $(this).attr('class').split(' ')[1];

    if (currentFactionCode != factionCode) {
      var factionName = $(this).data('faction-name');

      $('#modal h6').text($('#modal h6').text().replace(currentFactionName, factionName));

      currentFactionCode = factionCode;
      currentFactionName = factionName;
    }
  });

  $('#confirm-company-change').click(function() {
    if (currentFactionCode != "") {
      $.ajax({
        type: 'POST',
        url: '<?php echo DOMAIN; ?>api/',
        data: { action: 'company_select', company: currentFactionCode },
        success: function(response) {
          var json = jQuery.parseJSON(response);

          if (json.status) {
            location.reload();
          } else if (json.message != '') {
            M.toast({html: '<span>'+ json.message +'</span>'});
          }
        }
      });
    }
  });
</script>
<?php } ?>

<?php if (Functions::IsLoggedIn() && isset($page[1], $clan) && $page[1] === 'members' && $clan !== NULL) { ?>
<script type="text/javascript">
  <?php if ($clan['leaderId'] == $player['userId']) { ?>
  var currentVUserName = '%user_name%';
  var currentVUserText = '%user_text%';
  var currentVUserId = 0;

  $('.view-application').click(function() {
    var userId = $(this).data('user-id');

    if (currentVUserId != userId) {
      var name = $(this).data('user-name');
      var text = $(this).data('user-text');

      $('#modal h6').text($('#modal h6').text().replace(currentVUserName, name));
      $('#modal p').text($('#modal p').text().replace(currentVUserText, text));

      currentVUserId = userId;
      currentVUserName = name;
      currentVUserText = text;
    }
  });

  $('#accept').click(function() {
    if (currentVUserId != 0) {
      $.ajax({
        type: 'POST',
        url: '<?php echo DOMAIN; ?>api/',
        data: { action: 'accept_clan_application', userId: currentVUserId },
        success: function(response) {
          var json = jQuery.parseJSON(response);

          if (json.status) {
            var user = json.acceptedUser;
            $('#members').append('<div class="col s12">
                  <div id="user-'+ user.userId +'" class="card white-text grey darken-3 padding-5">
                    <div class="row">
                      <div class="col s4">
                        <h6>'+ user.pilotName +'</h6>
                        <p>EP: '+ user.experience +'</p>
                        <p>Rank: <img src="<?php echo DOMAIN; ?>img/ranks/rank_'+ user.rank.id +'.png"> '+ user.rank.name +'</p>
                      </div>
                      <div class="col s4">
                        <p>Joined: '+ user.joined_date +'</p>
                        <p>Function: Member</p>
                        <p>Position: </p>
                      </div>
                      <div class="col s4">
                        <p>Company: '+ user.company +'</p>
                        <a data-user-id="'+ user.userId +'" class="dismiss-member btn grey darken-2 waves-effect waves-light s6 modal-trigger" href="#modal1">DISMISS CLAN MEMBER</a>
                      </div>
                    </div>
                  </div>
                </div>');

            if ($('#applications').length <= 1) {
              $('#applications').prev().remove();
              $('#applications').remove();
            } else {
              $('#applications').find('#application-user-'+ currentWpClanId +'').remove();
            }
          }

          if (json.message != '') {
            M.toast({html: '<span>'+ json.message +'</span>'});
          }
        }
      });
    }
  });

  $('#decline').click(function() {
    if (currentVUserId != 0) {
      $.ajax({
        type: 'POST',
        url: '<?php echo DOMAIN; ?>api/',
        data: { action: 'decline_clan_application', userId: currentVUserId },
        success: function(response) {
          var json = jQuery.parseJSON(response);

          if (json.status) {
            if ($('#applications').length <= 1) {
              $('#applications').prev().remove();
              $('#applications').remove();
            } else {
              $('#applications').find('#application-user-'+ currentWpClanId +'').remove();
            }
          }

          if (json.message != '') {
            M.toast({html: '<span>'+ json.message +'</span>'});
          }
        }
      });
    }
  });

  var dismissMemberId = 0;

  $('body').on('click', '.dismiss-member', function() {
    var userId = $(this).data('user-id');

    if (dismissMemberId != userId) {
      dismissMemberId = userId;
    }
  });

  $('#confirm-dismiss-member').click(function() {
    if (dismissMemberId != 0) {
      $.ajax({
        url: '<?php echo DOMAIN; ?>api/',
        data: { action: 'dismiss_clan_member', userId: dismissMemberId },
        type: 'POST',
        success:function(response) {
          var json = jQuery.parseJSON(response);

          if (json.status) {
            $('#user-'+ dismissMemberId +'').parent().remove();
          }

          if (json.message != '') {
            M.toast({html: '<span>'+ json.message +'</span>'});
          }
        }
      });
    }
  });

  $('#confirm-delete-clan').click(function() {
    $.ajax({
      url: '<?php echo DOMAIN; ?>api/',
      data: { action: 'delete_clan' },
      type: 'POST',
      success:function(response) {
        var json = jQuery.parseJSON(response);

        if (json.status) {
          location.reload();
        } else if (json.message != '') {
          M.toast({html: '<span>'+ json.message +'</span>'});
        }
      }
    });
  });

  <?php } else { ?>

  $('#confirm-leave-clan').click(function() {
    $.ajax({
      url: '<?php echo DOMAIN; ?>api/',
      data: { action: 'leave_clan' },
      type: 'POST',
      success:function(response) {
        var json = jQuery.parseJSON(response);

        if (json.status) {
          location.reload();
        } else if (json.message != '') {
          M.toast({html: '<span>'+ json.message +'</span>'});
        }
      }
    });
  });

  <?php } ?>
</script>
<?php } ?>

<?php if (Functions::IsLoggedIn() && $page[0] === 'equipment') { ?>
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
