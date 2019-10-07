<!DOCTYPE html>
<html lang="en" dir="ltr">
  <head>
    <meta charset="utf-8">
    <title>InfinityOrbit 10.0</title>
    <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">
    <link type="text/css" rel="stylesheet" href="css/materialize.min.css"  media="screen,projection"/>
    <link type="text/css" rel="stylesheet" href="css/style.css"/>
  </head>
  <body>
    <div id="app">

      <?php if (Functions::IsLoggedIn()) { ?>
      <ul id="dropdown1" class="dropdown-content">
        <li><a href="#!">Ships</a></li>
        <li><a href="#!">Equipment</a></li>
      </ul>
      <nav>
        <div class="nav-wrapper grey darken-4">
          <div class="container">
          <a href="#!" class="brand-logo">{{serverName}}</a>
          <ul class="right hide-on-med-and-down">
            <li class="grey darken-3"><a href="/asdsad" target="_blank">Start</a></li>
            <li><a class="dropdown-trigger" href="#!" data-target="dropdown1">Hangar<i class="material-icons right">arrow_drop_down</i></a></li>
            <li><a href="/clan">Clan</a></li>
          </ul>
          </div>
        </div>
      </nav>
      <?php } ?>
