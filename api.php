<?php


function getAccessToken(): String
{
    $authUrl = "xxx";
    $clientId = "xxx";
    $clientSecret = "xxx";

    $ch = curl_init($authUrl);

    curl_setopt($ch, CURLOPT_POST, 1);
    curl_setopt($ch, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
    curl_setopt($ch, CURLOPT_USERNAME, $clientId);
    curl_setopt($ch, CURLOPT_PASSWORD, $clientSecret);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);

    $tokenResult = curl_exec($ch);
    $resultCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    if ($tokenResult === false || $resultCode !== 200) {
        exit ("Something went wrong");
    }

    $tokenObject = json_decode($tokenResult);

    return $tokenObject->access_token;
}


function getMainCategories(String $token): stdClass
{
    $getCategoriesUrl = "https://api.allegro.pl.allegrosandbox.pl/sale/categories";

    $ch = curl_init($getCategoriesUrl);

    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
                 "Authorization: Bearer $token",
                 "Accept: application/vnd.allegro.public.v1+json"
    ]);

    $mainCategoriesResult = curl_exec($ch);
    $resultCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    if ($mainCategoriesResult === false || $resultCode !== 200) {
        exit ("Something went wrong");
    }

    $categoriesList = json_decode($mainCategoriesResult);

    return $categoriesList;
}


function getUserOffer(String $token): stdClass {
    //$userID='xxx'; 
    $uri="https://api.allegro.pl.allegrosandbox.pl/offers/listing?seller.id=xxx";
    $headers = [
      'Accept: application/vnd.allegro.public.v1+json',
      'Content-Type: application/vnd.allegro.public.v1+json',
      'Authorization: Bearer '.$token.'',
      'Accept-Language: PL'
    ];

    $curl = curl_init($uri);
    curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, TRUE);
    $wynik = curl_exec($curl);

    $status = curl_getinfo($curl,CURLINFO_HTTP_CODE);
    $List = json_decode($wynik);
    
    //echo $List->items->regular[0]->name  ; 
    echo '<table><thead>';
    foreach($List->items->regular as $mydata)
    {
        echo '<tr><td>'. $mydata->name .'</td>';
        echo '<td>'. $mydata->sellingMode->price->amount .'</td></tr>';
    
    }
    echo '</thead></table>';


    return $List;  
}


function main()
{
    $token = getAccessToken();
    //var_dump(getMainCategories($token));
   // var_dump(getUserOffer($token));
   // $curl -i https://api.allegro.pl
   getUserOffer($token);
}

main();

