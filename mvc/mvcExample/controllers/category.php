<?php
switch($action) {
  case "":
  case "list":
    show_list();
    break;
  case "new":
    show_new();
    break;
  case "add":
    add_cat();
    break;
  case "del":
    rm_cat($id);
  default:
    exit("Unknown action -> $action");
}

function show_list() {
  $cats = get_cats();
  render("list", $cats);
}

function show_new() {
  render("new");
}

function add_cat() {
  global $PATH;
  
  $name = $_POST['name'];
  $result = insert_cat($name);

  header("location: ${PATH}/category/list/");
}

function rm_cat($id) {
  global $PATH;

  $result = del_cat($id);

  header("location: $PATH/category/list/");
}
?>