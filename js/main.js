$(document).ready(function() {
  $('.dropdown-trigger').dropdown({ hover: true, constrainWidth: false });
  $('.tabs').tabs();
  $('.modal').modal();
  $('select').formSelect();
  $('.tooltipped').tooltip({ html: true });
});
