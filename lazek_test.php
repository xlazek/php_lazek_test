<?php

$accessToken = "eyJ0eXAiOiJKV1QiLCJhbGciOiJSUzI1NiJ9.eyJhdWQiOiJhMzg0Mzc0 ...";

// Funkce pro vytvoření produktu
function createProduct()
{
    global $accessToken;
    $ch = curl_init();

    curl_setopt($ch, CURLOPT_URL, "https://private-anon-914f3ce1e3-authenticawms.apiary-mock.com/api/shop/shop/product");
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
    curl_setopt($ch, CURLOPT_HEADER, FALSE);
    curl_setopt($ch, CURLOPT_POST, TRUE);
    curl_setopt($ch, CURLOPT_POSTFIELDS, "{
      \"name\": \"TEST\",
      \"declarationName\": \"TEST\",
      \"description\": null,
      \"note\": null,
      \"length\": 7.77,
      \"width\": 7.77,
      \"height\": 7.77,
      \"weight\": 7.77,
      \"hsCode\": \"1234\",
      \"fdaCode\": null,
      \"hasLot\": false,
      \"hasImei\": false,
      \"hasLotExpiration\": false,
      \"inspectionMethod\": null,
      \"skus\": [
        \"sku-3079\"
      ],
      \"barcodes\": [
        \"5103092117769\"
      ]
    }");
    curl_setopt($ch, CURLOPT_HTTPHEADER, array(
        "Content-Type: application/json",
        "Authorization: Bearer $accessToken"
    ));

    $response = curl_exec($ch);

    if ($response === false) {
        echo 'Chyba curl: ' . curl_error($ch);
    } else {
        echo 'Úspěšně odesláno!';
    }

    curl_close($ch);

    // Dekódujte odpověď JSON do pole PHP
    return json_decode($response, true);
}

// Funkce pro vytvoření objednávky s daným produktem
function createOrder($product)
{
    global $accessToken;
    $url = "https://private-anon-914f3ce1e3-authenticawms.apiary-mock.com/api/shop/shop/order";
    $ch = curl_init();

    $new_order = array(
        "externalId" => "external-ccx",
        "carrierId" => 8,
        "branchId" => null,
        "price" => "69.00",
        "priceCurrency" => "CZK",
        "cod" => false,
        "codValue" => null,
        "codValueCurrency" => null,
        "vs" => null,
        "companyName" => "Daniel",
        "firstName" => null,
        "lastName" => null,
        "addressLine1" => "Marešova 14",
        "addressLine2" => "2nd floor",
        "addressLine3" => null,
        "city" => "Brno",
        "zip" => "602 00",
        "country" => "CZ",
        "state" => null,
        "phone" => "+420777777777",
        "email" => "lazekdaniel2000@gmail.com",
        "processingDate" => "2024-05-13",
        "items" => array(
            array(
                "productId" => $product["id"],
                "amount" => 7
            )
        )
    );

    curl_setopt($ch, CURLOPT_HTTPHEADER, array(
        "Authorization: Bearer $accessToken",
        "Content-Type: application/json"
    ));

    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($new_order));
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
    curl_setopt($ch, CURLOPT_HEADER, FALSE);

    $response = curl_exec($ch);

    if ($response === false) {
        echo 'Chyba curl: ' . curl_error($ch);
    } else {
        echo 'Úspěšně odesláno!';
    }

    var_dump($response);

    curl_close($ch);
}

// Funkce pro obnovení přístupového tokenu
function refreshAccessToken()
{
    $ch = curl_init();

    curl_setopt($ch, CURLOPT_URL, "https://private-anon-914f3ce1e3-authenticawms.apiary-mock.com/api/token");
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
    curl_setopt($ch, CURLOPT_HEADER, FALSE);
    curl_setopt($ch, CURLOPT_POST, TRUE);
    curl_setopt($ch, CURLOPT_POSTFIELDS, '{
      "grant_type": "refresh_token",
      "refresh_token": "def502001709892ea43ab6a65d55bf5c4f08bfca04fffd03a62ff8ca4 ...",
      "client_id": "fa45064cf13fb3ef62b11fcd20bb5cba",
      "client_secret": "f0413d4eaa4a42cf85f14ad5018cc0e3207b6d6dc4cf56c0e2ef6ae2b8a008ae633626422ed9f922877695bc20324412ba943f9684941997e2bc127f558aecaf",
      "scope": "default api"
    }');
    curl_setopt($ch, CURLOPT_HTTPHEADER, array(
        "Content-Type: application/json"
    ));

    $response = curl_exec($ch);
    curl_close($ch);

    return json_decode($response, true);
}

// Vytvoření produktu
$newProduct = createProduct();
var_dump($newProduct);

// Vytvoření objednávky s daným produktem
if ($newProduct !== null && isset($newProduct['id'])) {
    createOrder($newProduct);
} else {
    echo "Chyba při vytváření produktu.";
}

// Vytvoření nového tokenu pokud je starý prošlý
if ($newProduct === null && isset($newProduct['error']) && $newProduct['error'] === 'invalid_token') {
    $refreshedToken = refreshAccessToken();
    if ($refreshedToken !== null && isset($refreshedToken['access_token'])) {
        $accessToken = $refreshedToken['access_token'];
        // Znovu vytvoření produktu
        $newProduct = createProduct();
        if ($newProduct !== null && isset($newProduct['id'])) {
            createOrder($newProduct);
        } else {
            echo "Chyba při vytváření produktu.";
        }
    } else {
        echo "Chyba při obnově přístupového tokenu.";
    }
}
?>
