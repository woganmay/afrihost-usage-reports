<?php

function get_conversion_rate($from, $to)
{
  if ($from == $to) return 1;

  $map = array(
    "B" => 1,
    "MB" => 1024,
    "GB" => 1024*1024,
    "TB" => 1024*1024*1024
  );

  return $map[$from] / $map[$to];

}
