<?php
switch($action) {
  case "":
    show_index();
    break;
  default:
    exit("Unknown action -> $action");
}

function show_index() {
  render("index");
}

?>