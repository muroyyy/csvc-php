<?php
  error_log('Retrieving settings');
  require 'vendor/autoload.php'; // Use Composer autoload

  use Aws\SecretsManager\SecretsManagerClient;
  use Aws\Exception\AwsException;

  $az = file_get_contents('http://169.254.169.254/latest/meta-data/placement/availability-zone');
  $region = substr($az, 0, -1);

  $secretsManagerClient = new SecretsManagerClient([
    'version' => 'latest',
    'region'  => $region
  ]);

  try {
    // Retrieve the secret from Secrets Manager
    $result = $secretsManagerClient->getSecretValue([
      'SecretId' => 'milo-db-credentials'
    ]);

    // Decode the JSON secret string
    $json = json_decode($result['SecretString'], true);

    $ep = $json['endpoint'];
    $db = $json['database'];
    $un = $json['username'];
    $pw = $json['password'];
  }
  catch (AwsException $e) {
    $ep = '';
    $db = '';
    $un = '';
    $pw = '';
  }
?>