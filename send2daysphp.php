<?php
function getAccessToken(String $authUrlPara, String $clientIdPara, String $clientSecretPara): String
{
    $authUrl = $authUrlPara;
    $clientId = $clientIdPara;
    $clientSecret = $clientSecretPara;


	$code = htmlspecialchars($_GET["code"]) ;

    $postData = [
    	'grant_type' => 'authorization_code',
    	'code' => $code,
    	'redirect_uri' => 'http://localhost/send2days.php'
	];

    $ch = curl_init($authUrl);

    curl_setopt($ch, CURLOPT_POST, 1);
    curl_setopt($ch, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
    curl_setopt($ch, CURLOPT_USERNAME, $clientId);
    curl_setopt($ch, CURLOPT_PASSWORD, $clientSecret);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $postData);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);

    $tokenResult = curl_exec($ch);
    $resultCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    if ($tokenResult === false || $resultCode !== 200) {
    	echo "Something is wrong with TOKEN ";
    	return false; 

    }

    $tokenObject = json_decode($tokenResult);
    $ready = true ;
   
    return $tokenObject->access_token;
}

function getUserOffer(String $token, String $sellerIdPara): stdClass {
ini_set('max_execution_time', 300); 

    $offset=1;
    $limit=100;
    $tableProducts = array (0);
    $idx = 0;
    for ($il=0;$il<4;$il++) {
        //$userID='44023605'; 
        $uri="https://api.allegro.pl/offers/listing?seller.id=".$sellerIdPara."&offset=".$offset."&limit=".$limit."";  
          //&sort=-startTime - sort by the newest 
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

       // $status = curl_getinfo($curl,CURLINFO_HTTP_CODE);
        $List = json_decode($wynik);
        $lowerprice = 50000;
        //var_dump($List);

        //echo $List->items->regular[0]->name  ; 
        if ($offset==1) {
            echo '<table><thead>';
            echo '<tr class="header"><td>Lista ofert</td><td>Czas dostawy</td></tr>';
        }



        foreach($List->items->regular as $mydata) {  

        	$offerId = $mydata->id ;
          $urik="https://api.allegro.pl/sale/offers/".$offerId."";  
        	$headersk = [
        		'Accept: application/vnd.allegro.public.v1+json',
        		'Content-Type: application/vnd.allegro.public.v1+json',
        		'Authorization: Bearer '.$token.'',
        		'Accept-Language: PL'
      		];
      		$curlk = curl_init($urik);
        	curl_setopt($curlk, CURLOPT_HTTPHEADER, $headersk);
        	curl_setopt($curlk, CURLOPT_RETURNTRANSFER, TRUE);
        	$wynikk = curl_exec($curlk);
        	$Listk = json_decode($wynikk);        

            if ($Listk->delivery->handlingTime!='PT24H') {
        	   echo '<tr><td>'. $mydata->name .'</td>';
                echo '<td>'. $Listk->delivery->handlingTime .'</td>';
                echo '</tr>';
            }
        }
  		

  		foreach($List->items->promoted as $mydata2) {   

			$offerId2 = $mydata2->id ;
          	$urik2="https://api.allegro.pl/sale/offers/".$offerId2."";  
        	$headersk2 = [
        		'Accept: application/vnd.allegro.public.v1+json',
        		'Content-Type: application/vnd.allegro.public.v1+json',
        		'Authorization: Bearer '.$token.'',
        		'Accept-Language: PL'
      		];
      		$curlk2 = curl_init($urik2);
        	curl_setopt($curlk2, CURLOPT_HTTPHEADER, $headersk2);
        	curl_setopt($curlk2, CURLOPT_RETURNTRANSFER, TRUE);
        	$wynikk2 = curl_exec($curlk2);
        	$Listk2 = json_decode($wynikk2);        

            if ($Listk2->delivery->handlingTime!='PT24H') {
        	   echo '<tr><td>'. $mydata2->name .'</td>';
                echo '<td>'. $Listk2->delivery->handlingTime .'</td>';
                echo '</tr>';
            }
        }

           
        
        if ($offset>300){
            echo '</thead></table>';
        }
        $offset=$offset+100; 
    }
    return $List;  

}


function whoLogin() {
	$name = $_POST['who_login'];
	//echo $name;
	if ($name == 'wintel_pl') return 1; 
	if ($name == 'rafmar') return 2; 
	else return -100; 
}


function main()
{	

	if ($code = htmlspecialchars($_GET["code"])!=null) {
		$whoLogin = whoLogin();
	}

	//echo $whoLogin;
	if ($whoLogin == 2) {
		 $token = getAccessToken("https://allegro.pl/auth/oauth/token","xxx","xxx");
		 getUserOffer($token,"xxx");
	}

	else if ($whoLogin == 1) {
		 $token = getAccessToken("https://allegro.pl/auth/oauth/token","xxx","xxx");
		 getUserOffer($token,"xxx");
	}
   
}

@main();