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

// Now get the data for each $packages
$output = array();
foreach($packages as $package)
{

  $return = array(
    "Timestamp" => date("Y-m-d H:i:s"),
    "Package"  => $package
  );

  curl_setopt($ch, CURLOPT_URL, "https://clientzone.afrihost.com/en/my-connectivity/account-detail/$package~1");
  $data = curl_exec($ch);

  // Now parse for bandwidth totals and usage
  $dom->loadHTML($tidy->repairString($data));
  $xpath = new DOMXPath($dom);

  foreach ($xpath->query("//dl") as $element)
  {
    foreach (explode("\n", $element->nodeValue) as $line)
    {
      if (strpos($line, ":") !== false)
      {
        // Line
        $parts = explode(":", $line);
        $return[ trim($parts[0]) ] = trim($parts[1]);
      }
    }
  }

  // Is it B MB or GB or TB
  $total_unit = trim(preg_replace('/\d|\.|\s/', '', $return["Total Account Data"]));
  $usage_unit = trim(preg_replace('/\d|\.|\s/', '', $return["Data Used"]));

  $total_number = trim(str_replace($total_unit, "", $return["Total Account Data"]));
  $usage_number = trim(str_replace($usage_unit, "", $return["Data Used"]));

  // Convert usage unit to the total unit
  $conversion_rate = get_conversion_rate($usage_unit, $total_unit);

  // The total will always be a higher unit than the usage
  $usage_converted = $usage_number * $conversion_rate;

  $return["Data Remaining"] = sprintf("%s %s", $total_number-$usage_converted, $total_unit);

  $output[] = $return;

}

// Pull headers from the first $output;

$handle = fopen("output_".time().".csv", "w+");
fputcsv($handle, array("Timestamp", "Package", "Total Account Data", "Data Used", "Average Daily Usage", "Account Status", "Latest IP Address", "Last Connection From", "Data Remaining"));

foreach($output as $row) fputcsv($handle, $row);

fclose($handle);
