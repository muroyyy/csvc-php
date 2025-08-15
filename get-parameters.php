<?php
  // Enable error reporting for debugging (remove or comment out in production)
  ini_set('display_errors', 1);
  ini_set('display_startup_errors', 1);
  error_reporting(E_ALL);

  error_log('Retrieving settings');
  require 'vendor/autoload.php'; // Use Composer autoload

  use Aws\SecretsManager\SecretsManagerClient;
  use Aws\Exception\AwsException;

  $az = file_get_contents('http://169.254.169.254/latest/meta-data/placement/availability-zone');
  $region = 'ap-southeast-1';

  $secretsManagerClient = new SecretsManagerClient([
    'version' => 'latest',
    'region'  => $region
  ]);

  try {
    // Retrieve the secret from Secrets Manager
    $result = $secretsManagerClient->getSecretValue([
      'SecretId' => 'milo-db-credentials'
    ]);
    error_log('Successfully retrieved secret from Secrets Manager.');

    // Decode the JSON secret string
    $json = json_decode($result['SecretString'], true);

    if ($json === null) {
      error_log('Failed to decode JSON from Secrets Manager: ' . json_last_error_msg());
      throw new Exception('JSON decode error: ' . json_last_error_msg());
    }

    $ep = $json['endpoint'];
    $db = $json['database'];
    $un = $json['username'];
    $pw = $json['password'];

    error_log("Database credentials loaded: endpoint=$ep, database=$db, username=$un");

    // Create PDO connection
    try {
      $dsn = "mysql:host=$ep;dbname=$db;charset=utf8mb4";
      $pdo = new PDO($dsn, $un, $pw, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
      ]);
      error_log("PDO connection established successfully.");
    } catch (PDOException $e) {
      error_log("PDO connection failed: " . $e->getMessage());
      throw $e;
    }

  }
  catch (AwsException $e) {
    error_log('AWS Exception when retrieving secret: ' . $e->getAwsErrorMessage());
    $ep = '';
    $db = '';
    $un = '';
    $pw = '';
  }
  catch (Exception $e) {
    error_log('General Exception: ' . $e->getMessage());
    $ep = '';
    $db = '';
    $un = '';
    $pw = '';
  }
?>