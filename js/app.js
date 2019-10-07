$(document).ready(function() {
  $('.dropdown-trigger').dropdown();
  $('.tabs').tabs();
});

window.vue = new Vue({
  el: '#app',
  data: {
    serverName: 'InfinityOrbit',
    shipName: 'Legionary'
  }
});
