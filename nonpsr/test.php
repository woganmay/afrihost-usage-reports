<?php

require_once 'functions.php';

$tests = array(
  "B,B",
  "MB,MB",
  "GB,GB",
  "TB,TB",
  "B,MB",
  "B,GB",
  "B,TB",
  "MB,B",
  "MB,GB",
  "MB,TB",
  "GB,B",
  "GB,MB",
  "GB,TB",
  "TB,B",
  "TB,MB",
  "TB,GB"
);

foreach($tests as $test)
{
  $p = explode(",", $test);
  echo "From $p[0] => $p[1]: " . get_conversion_rate($p[0], $p[1]) . "\n";
}
