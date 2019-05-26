<h2>Analisa Gambar dengan Azure Computer Vision</h2>
<hr />
<p>Pilih gambar yang akan dianalisa</p>

<form method="post" action="fileAnalyzer.php?Upload&containerName=gungzimage" enctype="multipart/form-data">
    <input type="file" name="fileToUpload" id="fileToUpload" />
    <button type="submit" style="background-color: #4682B4; color: white">Upload</button>
</form>

<?php
/**----------------------------------------------------------------------------------
* Microsoft Developer & Platform Evangelism
*
* Copyright (c) Microsoft Corporation. All rights reserved.
*
* THIS CODE AND INFORMATION ARE PROVIDED "AS IS" WITHOUT WARRANTY OF ANY KIND, 
* EITHER EXPRESSED OR IMPLIED, INCLUDING BUT NOT LIMITED TO THE IMPLIED WARRANTIES 
* OF MERCHANTABILITY AND/OR FITNESS FOR A PARTICULAR PURPOSE.
*----------------------------------------------------------------------------------
* The example companies, organizations, products, domain names,
* e-mail addresses, logos, people, places, and events depicted
* herein are fictitious.  No association with any real company,
* organization, product, domain name, email address, logo, person,
* places, or events is intended or should be inferred.
*----------------------------------------------------------------------------------
**/

/** -------------------------------------------------------------
# Azure Storage Blob Sample - Demonstrate how to use the Blob Storage service. 
# Blob storage stores unstructured data such as text, binary data, documents or media files. 
# Blobs can be accessed from anywhere in the world via HTTP or HTTPS. 
#
# Documentation References: 
#  - Associated Article - https://docs.microsoft.com/en-us/azure/storage/blobs/storage-quickstart-blobs-php 
#  - What is a Storage Account - http://azure.microsoft.com/en-us/documentation/articles/storage-whatis-account/ 
#  - Getting Started with Blobs - https://azure.microsoft.com/en-us/documentation/articles/storage-php-how-to-use-blobs/
#  - Blob Service Concepts - http://msdn.microsoft.com/en-us/library/dd179376.aspx 
#  - Blob Service REST API - http://msdn.microsoft.com/en-us/library/dd135733.aspx 
#  - Blob Service PHP API - https://github.com/Azure/azure-storage-php
#  - Storage Emulator - http://azure.microsoft.com/en-us/documentation/articles/storage-use-emulator/ 
#
**/

require_once 'vendor/autoload.php';
require_once "./random_string.php";

use MicrosoftAzure\Storage\Blob\BlobRestProxy;
use MicrosoftAzure\Storage\Common\Exceptions\ServiceException;
use MicrosoftAzure\Storage\Blob\Models\ListBlobsOptions;
use MicrosoftAzure\Storage\Blob\Models\CreateContainerOptions;
use MicrosoftAzure\Storage\Blob\Models\PublicAccessType;

$connectionString = "DefaultEndpointsProtocol=https;AccountName=dicodingwebappgungz;AccountKey=cvtCEklkyNpjWsVgiHGDibuV8nLiTRODa+ZUL8wFYL/nkgj++ciwIp57OLYzCOvPoaDs1vvQ80rl5QERjXsUyA==";

// Create blob client.
$blobClient = BlobRestProxy::createBlobService($connectionString);
$restClient = new GuzzleHttp\Client();

$visionSubscriptionKey = "8e15e847cf0e4cebb0ec0225c9e6226c";
// You must use the same Azure region in your REST API method as you used to
// get your subscription keys. For example, if you got your subscription keys
// from the West US region, replace "westcentralus" in the URL
// below with "westus".
//
// Free trial subscription keys are generated in the "westus" region.
// If you use a free trial subscription key, you shouldn't need to change
// this region.
$visionUriBase =
    "https://westeurope.api.cognitive.microsoft.com/vision/v2.0/analyze";
$visionUri = $visionUriBase . "?visualFeatures=Description&details=&language=en";

$headers = array(
    // Request headers
    'Content-Type' => 'application/json',
    'Ocp-Apim-Subscription-Key' => $visionSubscriptionKey,
);

$storageAccount = "dicodingwebappgungz";

if (isset($_GET["Upload"])) {
    // Create container options object.
    $createContainerOptions = new CreateContainerOptions();

    $containerName = $_GET["containerName"];

    try {
        $fileToUpload = $_FILES['fileToUpload']['tmp_name'];
        $fileName = basename($_FILES["fileToUpload"]["name"]);

        $myfile = fopen($fileToUpload, "r") or die("Unable to open file!");
        fclose($myfile);

        $content = fopen($fileToUpload, "r");

        //Upload blob
        $blobClient->createBlockBlob($containerName, $fileName, $content);

        $blobUrl = "https://" . $storageAccount . ".blob.core.windows.net/" . $containerName . "/" . $fileName;

        echo "<br />";
        echo "<img src=\"" . $blobUrl . "\" />";
        echo "<br /> <br/>";

        $body =  "{\"url\":\"" . $blobUrl . "\"}";

        $response = $restClient->request('POST', $visionUri, ['headers' => $headers, 'body' => $body]);
        $res = json_decode($response->getBody());
        echo "<div style=\"font-family: Arial\">Analysis Result: " . $res->{'description'}->{'captions'}[0]->{'text'} . "</span>";
    }
    catch(ServiceException $e){
        // Handle exception based on error codes and messages.
        // Error codes and messages are here:
        // http://msdn.microsoft.com/library/azure/dd179439.aspx
        $code = $e->getCode();
        $error_message = $e->getMessage();
        echo $code.": ".$error_message."<br />";
    }
    catch(InvalidArgumentTypeException $e){
        // Handle exception based on error codes and messages.
        // Error codes and messages are here:
        // http://msdn.microsoft.com/library/azure/dd179439.aspx
        $code = $e->getCode();
        $error_message = $e->getMessage();
        echo $code.": ".$error_message."<br />";
    }
}

?>