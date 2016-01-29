<?php
$conn = mysql_connect("localhost", "root", "root");
mysql_select_db("store", $conn);

function get_cats() {
  $result = mysql_query("SELECT * FROM categories");

  $cats = array();
  while($row = mysql_fetch_assoc($result)) {
    $cats[] = $row;
  }

  return $cats;
}

function insert_cat($name) {
  mysql_query("INSERT INTO categories
    (name, created_date, modified_date)
    VALUES ('$name', now(), now())"
  );

  return mysql_insert_id();
}

function del_cat($id) {
  mysql_query("DELETE FROM categories WHERE id = $id");

  return mysql_affected_rows();
}
?>

