<h2>List</h2>
<ul>
  <? foreach($data as $cat): ?>
  <li>
    [ <a href="<?= $PATH ?>/category/del/<?= $cat['id'] ?>">del</a> ]
    <strong><?= $cat['name'] ?></strong>
  </li>
  <? endforeach; ?>
</ul>

<br>
<a href="<?= $PATH ?>/category/new/">New Category</a>
