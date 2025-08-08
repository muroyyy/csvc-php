<?php
<?php
  error_log('Retrieving settings');
  require 'aws.phar';

  $az = file_get_contents('http://169.254.169.254/latest/meta-data/placement/availability-zone');
  $region = substr($az, 0, -1);
  $ssm_client = new Aws\Ssm\SsmClient([
     'version' => 'latest',
     'region'  => $region
  ]);

  try {
    // Retrieve the JSON parameter from Parameter Store
    $result = $ssm_client->getParameter([
      'Name' => '/test',
      'WithDecryption' => true
    ]);

    // Decode the JSON value
    $json = json_decode($result['Parameter']['Value'], true);

    $ep = $json['rds_endpoint'];
    $db = $json['db_name'];
    $un = $json['username'];
    $pw = $json['password'];
  }
  catch (Exception $e) {
    $ep = '';
    $db = '';
    $un = '';
    $pw = '';
  }
?>