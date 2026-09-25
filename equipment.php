<?php
if(isset($_GET['create'])){require __DIR__.'/app/equipment-register.php';exit;}
if(!isset($_GET['id'])){require __DIR__.'/app/equipment-list.php';exit;}
$module='equipment';require __DIR__.'/app/operations-page.php';
