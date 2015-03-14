<?php

// Config
libxml_use_internal_errors(false);
date_default_timezone_set("Africa/Johannesburg");
$tidy = new tidy();
require_once 'functions.php';

// Set cookie and GET
$cookie = "clientzone=__________________________; path=/; domain=.clientzone.afrihost.com";

$ch = curl_init("https://clientzone.afrihost.com/en/my-connectivity");
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_VERBOSE, FALSE);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, FALSE);
curl_setopt($ch, CURLOPT_HTTPHEADER, array('Cookie: ' . $cookie));

$html = curl_exec($ch);

// Wrap
// Parse HTML
$dom = new DOMDocument;
$dom->loadHTML($tidy->repairString("<html><head></head><body>$html</body></html>"));

$xpath = new DOMXPath($dom);

// Look for total
$nodes = $dom->getElementsByTagName('dd');

$packages = array();

foreach($nodes as $node)
{
  // If this looks like an email address, it's a login
  if (filter_var($node->textContent, FILTER_VALIDATE_EMAIL)) {
      $packages[] = $node->textContent;
  }
}

$handle = fopen("detail_".time().".csv", "w+");
fputcsv($handle, array("Package", "Date", "Upload (B)", "Download (B)", "Total (B)"));

// Now get the data for each $packages
$output = array();
foreach($packages as $package)
{

  $return = array();

  // Get detailed usage information
  curl_setopt($ch, CURLOPT_URL, "https://clientzone.afrihost.com/en/my-connectivity/bandwidth-graph/$package~1");
  $data = curl_exec($ch);

  // Just look for the one JSON.parse call
  preg_match("/JSON\.parse\(\'(.*)\'\)/", $data, $matches);

  if (count($matches) > 0)
  {
      // Got usage
      $usage = json_decode($matches[1]);

      // Set up the date range. Use from the data, otherwise default
      $date_base = $usage->year . "-" . $usage->month;

      foreach($usage->over_all->days as $index => $day)
      {

        // One new line per day
        $d = $date_base . "-" . str_pad($day, 2, "0", STR_PAD_LEFT);

        fputcsv($handle, array(
          "Package" => $package,
          "Date" => $d,
          "Upload (B)" => $usage->over_all->upload[$index],
          "Download (B)" => $usage->over_all->download[$index],
          "Total (B)" => $usage->over_all->total[$index]
        ));

      }
  }
  else
  {
    echo "Cannot match any usage for $package\n";
  }

}

fclose($handle);
