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
    	'redirect_uri' => 'http://localhost/kodyean.php'
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

function getUserOffer(String $token, String $sellerIdPara, array $allegroProducts): stdClass {
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
            echo '<tr class="header"><td>Lista ofert</td><td>Nasz EAN</td><td>Nasza Cena</td><td>Cena Allegro</td><td>Prowizja Promocyjna</td><td>Min. sztuk</td></tr>';
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


        	echo '<tr><td>'. $mydata->name .'</td>';

        	if ($Listk->ean!=null) {
        		$tableProducts[$idx] = $Listk->ean;
        		$tableProducts[$idx+1] = $mydata->sellingMode->price->amount;
        		$idx = $idx+4;
        		$prod=0;
        		$succes = 0 ;
        		$currentPricee = 0;
        		foreach ($allegroProducts as $key => $allegroProduct) {
        			$prod = $prod+1;
        			if ($allegroProducts[$key]==$Listk->ean) {
        				$currentPricee = $allegroProducts[$key+1] ;
                        $commission_Allegro =  $allegroProducts[$key+2]*100 ;
                        $pieces =  $allegroProducts[$key+3] ;
        				if ($mydata->sellingMode->price->amount<$allegroProducts[$key+1])
        					$succes = 1;
        				else if ($mydata->sellingMode->price->amount-20<$allegroProducts[$key+1]){
        					$succes = 2;
        				}
        				else $succes = 3;
        			}
        		}
        		if ($succes==1) {
        			 echo '<td style="background-color:green">'.$Listk->ean.'</td>';
        			 echo '<td style="background-color:green">'.$mydata->sellingMode->price->amount.'</td>';
        			 echo '<td style="background-color:green">'.$currentPricee.'</td>';
                     echo '<td style="background-color:green">'.$commission_Allegro.'%</td>';
                     echo '<td style="background-color:green">'.$pieces.'</td>';
        		}
        		else if ($succes==2) {
        			echo '<td style="background-color:yellow">'.$Listk->ean.'</td>';
        			echo '<td style="background-color:yellow">'.$mydata->sellingMode->price->amount.'</td>';
        			echo '<td style="background-color:yellow">'.$currentPricee.'</td>';
                    echo '<td style="background-color:yellow">'.$commission_Allegro.'%</td>';
                    echo '<td style="background-color:yellow">'.$pieces.'</td>';
        		} 
        		else if ($succes==3) {
        			echo '<td style="background-color:pink">'.$Listk->ean.'</td>';
        			echo '<td style="background-color:pink">'.$mydata->sellingMode->price->amount.'</td>';
        			echo '<td style="background-color:pink">'.$currentPricee.'</td>';
                    echo '<td style="background-color:pink">'.$commission_Allegro.'%</td>';
                    echo '<td style="background-color:pink">'.$pieces.'</td>';
        		} 
        		else {
        			echo '<td>'.$Listk->ean.'</td>';
        			echo '<td>'.$mydata->sellingMode->price->amount.'</td>';
        			echo '<td>Brak</td>';
                    echo '<td>Brak</td>';
                    echo '<td>Brak</td>';
        		}
        	}
        	else {
        		echo '<td>Brak EAN</td>';
        		echo '<td>'.$mydata->sellingMode->price->amount.'</td>';
        		echo '<td>Brak</td>';
                echo '<td>Brak</td>';
                echo '<td>Brak</td>';
        	}
        	
            echo '</tr>';
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

        	echo '<tr><td>'. $mydata2->name .'</td>';

        	if ($Listk2->ean!=null) {
        		$tableProducts[$idx] = $Listk2->ean;
        		$tableProducts[$idx+1] = $mydata2->sellingMode->price->amount;
        		$idx = $idx+4;
        		$prod=0;
        		$succes = 0 ;
        		$currentPrice=0;
        		foreach ($allegroProducts as $key => $allegroProduct) {
        			$prod = $prod+1;
        			if ($allegroProducts[$key]==$Listk2->ean) {
        				$currentPrice = $allegroProducts[$key+1] ;
                        $commission_Allegro =  $allegroProducts[$key+2]*100 ;
                        $pieces =  $allegroProducts[$key+3] ;
        				if ($mydata2->sellingMode->price->amount<$allegroProducts[$key+1])
        					$succes = 1;
        				else if ($mydata2->sellingMode->price->amount-20<$allegroProducts[$key+1]){
        					$succes = 2;
        				}
        				else $succes = 3;
        			}
        		}
        		if ($succes==1) {
        			 echo '<td style="background-color:green">'.$Listk2->ean.'</td>';
        			 echo '<td style="background-color:green">'.$mydata2->sellingMode->price->amount.'</td>';
        			 echo '<td style="background-color:green">'.$currentPrice.'</td>';
                     echo '<td style="background-color:green">'.$commission_Allegro.'%</td>';
                     echo '<td style="background-color:green">'.$pieces.'</td>';
        		}
        		else if ($succes==2) {
        			echo '<td style="background-color:yellow">'.$Listk2->ean.'</td>';
        			echo '<td style="background-color:yellow">'.$mydata2->sellingMode->price->amount.'</td>';
        			echo '<td style="background-color:yellow">'.$currentPrice.'</td>';
                    echo '<td style="background-color:yellow">'.$commission_Allegro.'%</td>';
                    echo '<td style="background-color:yellow">'.$pieces.'</td>';
        		} 
        		else if ($succes==3) {
        			echo '<td style="background-color:pink">'.$Listk2->ean.'</td>';
        			echo '<td style="background-color:pink">'.$mydata2->sellingMode->price->amount.'</td>';
        			echo '<td style="background-color:pink">'.$currentPrice.'</td>'; 
                    echo '<td style="background-color:pink">'.$commission_Allegro.'%</td>';   
                    echo '<td style="background-color:pink">'.$pieces.'</td>';    			
        		} 
        		else {
        			echo '<td>'.$Listk2->ean.'</td>';
        			echo '<td>'.$mydata2->sellingMode->price->amount.'</td>';
        			echo '<td>Brak</td>';
                    echo '<td>Brak</td>';
                    echo '<td>Brak</td>';
        		}
        	}
        	else {
        		echo '<td>Brak EAN</td>';
        		echo '<td>'.$mydata2->sellingMode->price->amount.'</td>';
        		echo '<td>Brak</td>';
                echo '<td>Brak</td>';
                echo '<td>Brak</td>';
        	}
            echo '</tr>';
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

function getAllegroProducts() {
	require_once "Classes/PHPExcel.php";
		//$tmpfname = "test.xlsx";
		$url = "https://assets.allegrostatic.com/popart-attachments/att-6062aca4-7617-4620-bc55-d187547d1e62";
		$filecontent = file_get_contents($url);
		$tmpfname = tempnam(sys_get_temp_dir(),"tmpxls");
		file_put_contents($tmpfname,$filecontent);
		
		$excelReader = PHPExcel_IOFactory::createReaderForFile($tmpfname);
		$excelObj = $excelReader->load($tmpfname);
		$worksheet = $excelObj->getSheet(0);
		$lastRow = $worksheet->getHighestRow();
		$allegroProducts = array (0);
		$idx=0;
		
		
		for ($row = 1; $row <= $lastRow; $row++) {
			 $allegroProducts[$idx] = $worksheet->getCell('D'.$row)->getValue();
			 $allegroProducts[$idx+1] = $worksheet->getCell('E'.$row)->getValue();
             $allegroProducts[$idx+2] = $worksheet->getCell('G'.$row)->getValue();
             $allegroProducts[$idx+3] = $worksheet->getCell('F'.$row)->getValue();
			 $idx = $idx+4 ;
		}	
		//var_dump($allegroProducts);
		return $allegroProducts;
}

function main()
{	

	if ($code = htmlspecialchars($_GET["code"])!=null) {
		$whoLogin = whoLogin();
		$allegroProducts = getAllegroProducts() ; 
	}

	//echo $whoLogin;
	if ($whoLogin == 2) {
		 $token = getAccessToken("https://allegro.pl/auth/oauth/token","xxx","xxx");
		 getUserOffer($token,"xxx",$allegroProducts);
	}

	else if ($whoLogin == 1) {
		 $token = getAccessToken("https://allegro.pl/auth/oauth/token","xxx","xxx");
		 getUserOffer($token,"xxx",$allegroProducts);
	}
   
}

@main();