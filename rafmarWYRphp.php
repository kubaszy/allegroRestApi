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
    $getCategoriesUrl = "https://api.allegro.pl/sale/categories";

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
ini_set('max_execution_time', 300); 
    $ProductArray = [
        'rowenta','philips','xblitz','caferomantica','kenwood','hoover','roomba','ilife','yi','karcher','delonghi','mio','krups','Beko','tefal','zelmer','siemens','sandisk','bosch','saeco','inlife','sharp','reinston','navitel','eldom','sage','braun','remington','kingston','huawei','logitech','braava'
    ];
    $offset=1;
    $limit=100;
    for ($il=0;$il<4;$il++) {
        //$userID='xxx'; 
        $uri="https://api.allegro.pl/offers/listing?seller.id=xxx&offset=".$offset."&limit=".$limit."";  
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
            echo '<tr class="header"><td>Lista ofert WYRÓŻNIONYCH</td><td>Nasza Cena</td><td>Najniższa Cena</td><td>Szukana fraza</td></tr>';
        }
        foreach($List->items->promoted as $mydata)
        {
            
            $modelname=$mydata->name ;
            $tablica = explode(" ", $mydata->name);
            $counter = 0;
            $best_price = 0 ;
            $i=0;
            $lowerprice=0;

            foreach($ProductArray as $myproduct) {
                $i=0;
                foreach ($tablica as $mytxt) {
                    $mytxt = strtolower($mytxt);
                    $myproduct = strtolower($myproduct);
                    $i=$i+1;
                    if ($mytxt===$myproduct) {

                        $new_product_name = $mytxt."+".$tablica[$i];
                        $lookingfor = $mytxt." ".$tablica[$i];
                        //$new_product_name = str_replace('/', '%2F', $new_product_name);
                        //echo next($tablica) . '<br/>';
                        //echo next($tablica) . '<br/>' ;

                        $uri2="https://api.allegro.pl/offers/listing?phrase=".$new_product_name."&include=FILTERS&searchMode=REGULAR&option=VAT_INVOICE&parameter.11323=1&fallback=false";
                        $headers2 = [
                          'Accept: application/vnd.allegro.public.v1+json',
                          'Content-Type: application/vnd.allegro.public.v1+json',
                          'Authorization: Bearer '.$token.'',
                          'Accept-Language: PL'
                        ];

                        $curl2 = curl_init($uri2);
                        curl_setopt($curl2, CURLOPT_HTTPHEADER, $headers2);
                        curl_setopt($curl2, CURLOPT_RETURNTRANSFER, TRUE);
                        $wynik2 = curl_exec($curl2);

                        $List2 = json_decode($wynik2);
                        
                        $lowerprice = (($mydata->sellingMode->price->amount)+($mydata->delivery->lowestPrice->amount));
                        $pricecompetition = 10000; 

                        foreach($List2->items->regular as $mydata2) {
                            
                            
                            // if ($pricecompetition>$mydata2->sellingMode->price->amount+$mydata2->delivery->lowestPrice->amount) {
                            //    // if ($mydata2->seller->id!=xxx) {
                            //          echo $mydata->seller->id;
                            //          echo "\n";
                            //    // } 
                            // }

                            if ($lowerprice>($mydata2->sellingMode->price->amount+$mydata2->delivery->lowestPrice->amount)) {
                                //OTHER IFS
                                if (($mydata->category->id==$mydata2->category->id)&&($mydata2->sellingMode->format=="BUY_NOW")) {
                                    $best_price = 1 ;
                                    $lowerprice = (($mydata2->sellingMode->price->amount)+($mydata2->delivery->lowestPrice->amount));
                                }

                                
                            }
                        }


                        foreach($List2->items->promoted as $mydata2) {


                            if (($lowerprice)>($mydata2->sellingMode->price->amount+$mydata2->delivery->lowestPrice->amount)) {
                                //OTHER IFS
                                if (($mydata->category->id==$mydata2->category->id)&&($mydata2->sellingMode->format=="BUY_NOW")) {
                                    $best_price = 1 ;
                                    $lowerprice = (($mydata2->sellingMode->price->amount)+($mydata2->delivery->lowestPrice->amount));
                                }

                                
                            }
                        }

                        //echo "NAJNIZSZA CENA: ".$lowerprice."<br/>" ;
                        //var_dump($wynik2);

                        $counter++; 

                    }
                }
                
            }
            if ($counter==0) $lowerprice = 0; 
            echo '<tr><td>'. $mydata->name .'</td>';
            echo '<td>'.($mydata->sellingMode->price->amount+$mydata->delivery->lowestPrice->amount).'</td>';
            if ($counter==0) {
                echo '<td class="yellow">Nie znaleziono</td>';
                echo '<td>-</td></tr>';
            }
            else{ 
                if ($best_price==0) {
                    echo '<td class="green">'.$lowerprice.'</td>';
                }
                else {
                    echo '<td class="red">'.$lowerprice.'</td>';
                }
                echo '<td>'.$lookingfor.'</td></tr>';
            }
            $couter = 0;
            $best_price = 0 ;


        
        }
        if ($offset>300){
            echo '</thead></table>';
        }
        $offset=$offset+100; 
    }
    return $List;  

}


function main()
{
    $token = getAccessToken();
    //var_dump(getMainCategories($token));
   // var_dump(getUserOffer($token));
   // $curl -i https://api.allegro.pl
    //getMyItems($token);
    getUserOffer($token);
}

@main();
