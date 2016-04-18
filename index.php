<!DOCTYPE html>
<html>
<!-- Read from bound VCAP_CREDENTIALS -->
<?php

// use BlueMix VCAP_SERVICES environment 
if ($services = getenv("VCAP_SERVICES")) {
  $services_json = json_decode($services, true);
  $authUrl = $services_json["Object-Storage"][0]["credentials"]["auth_url"] . "/v3";
  $region = $services_json["Object-Storage"][0]["credentials"]["region"];
  $userId = $services_json["Object-Storage"][0]["credentials"]["userId"];
  $password = $services_json["Object-Storage"][0]["credentials"]["password"];
  $projectId = $services_json["Object-Storage"][0]["credentials"]["projectId"];
  //echo "Object Storage Credentials: " . $authUrl . " " . $region . " " . $userId . " " . $password . " " . $projectId . "\n";
} else {
  throw new Exception('Not in Bluemix environment');
}

?>
<!-- Get Keystone Token -->
<?php
require 'vendor/autoload.php';
$openstack = new OpenStack\OpenStack([
    'authUrl' => $authUrl,
    'region'  => $region,
    'user'    => [
        'id'       => $userId,
        'password' => $password
    ],
    'scope' => [
        'project' => ['id' => $projectId]
    ]
]);

$identity = $openstack->identityV3();
$token = $identity->generateToken([
    'user' => [
        'id'       => $userId,
        'password' => $password
    ]
]);

$tokenId = $token->getId();

//echo "My Keystone token: " . $tokenId;
?>
<!-- Create a container and upload a file -->

<?php

$containerName = 'MyNewContainer';
$objectName = 'MyFile.txt';
$objectContent = 'MyFile.txt';


$service = $openstack->objectStoreV1();

$container = $service->createContainer([
    'name' => $containerName
]);


//Upload file

$options = [
    'name'    => $objectName,
    'content' => $objectContent,
];

$object = $openstack->objectStoreV1()
                    ->getContainer($containerName)
                    ->createObject($options);

//echo "Check the bound instance of Object Storage to view these two items in the file browser."
?>

<!-- Display Information -->

<head>
	<title>PHP & Object Storage</title>
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
	<link rel="stylesheet" href="style.css" />
</head>
<body>
	<table>
		<tr>
			<td style='width: 30%;'><img class = 'newappIcon' src='images/objectStoreIcon50.png'>
			</td>
			<td>
				<h1 id = "message">PHP application using bound Object Storage Instance...</h1>
				<h3>Reading in VCAP Credentials from bound Object Storage Instance...</h2>
				<p class='description'><?php echo "Credentials from bound Object Storage: "; ?></p>
				<ul>
					<li><?php echo "Auth URL: " . $authUrl; ?></li>
					<li><?php echo "Region: " . $region; ?></li>
					<li><?php echo "User Id: " . $userId; ?></li>
					<li><?php echo "Password: " . $password; ?></li>
					<li><?php echo "Project Id: " . $projectId; ?></li>
				</ul>
				<h3>Getting Keystone Token...</h3>
				<p><?php echo "My Keystone token: " . $tokenId; ?></p>
				<h3>Creating 'MyNewContainer' and uploading "MyFile.txt"...</h3>
				<p>Check the bound instance of Object Storage to view these two items in the file browser.</p>
		    </td>
	</table>
	<br />
	<br />
</body>
</html>
