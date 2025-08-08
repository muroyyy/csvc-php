<?php
  # Retrieve settings from Parameter Store
  error_log('Retrieving settings');
  require 'aws.phar';
  
  $az = file_get_contents('http://169.254.169.254/latest/meta-data/placement/availability-zone');
  $region = substr($az, 0, -1);
  $ssm_client = new Aws\Ssm\SsmClient([
     'version' => 'latest',
     'region'  => $region
  ]);
  
  try {
    # Retrieve settings from Parameter Store
    $result = $ssm_client->GetParametersByPath(['Path' => '/msri/', 'WithDecryption' => true]);

    # Extract individual parameters
    foreach($result['Parameters'] as $p) {
        $values[$p['Name']] = $p['Value'];
    }

    $ep = $values['/msri/endpoint'];
    $un = $values['/msri/username'];
    $pw = $values['/msri/password'];
    $db = $values['/msri/database'];
  }
  catch (Exception $e) {
    $ep = '';
    $db = '';
    $un = '';
    $pw = '';
  }

?>