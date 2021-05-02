<?php 

// function execute curl request , and get result from github 
 function curlRequest($q='')
{
                $url = "https://api.github.com/search/repositories?q=".$q ;
                $ch = curl_init();
                curl_setopt($ch, CURLOPT_URL, $url);
                curl_setopt($ch, CURLOPT_HTTPGET, 1);
                curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
                curl_setopt( $ch, CURLOPT_USERAGENT, "Mozilla/5.0 (Windows NT 6.1; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/90.0.4430.85 Safari/537.36" );	

                $result = curl_exec($ch);
             	$json_result =  json_decode($result,true);   
             	curl_close($ch);  
             	return $json_result;           
}

            //get 1 month from NOW 
            $date = new DateTime('NOW');
            $date->modify('-1 month');
            $gitdate = $date->format('Y-m-d');
            
            //get 100 popular github's repositories created at least 1 month ago 
            $json_result = curlRequest("created:>".$gitdate."&sort=stars&order=desc&per_page=100");
            
         
            $languages = [];
             
             if(isset($json_result ['items'])){

                 //build array of languages
                 foreach ($json_result ['items'] as  $item) {
                 	if($item ['language']){
                 		array_push($languages,$item ['language']);
                 	}
                 	
                 }
                 $langs = array();
                 // eliminate the duplicated elements
                 $languages= array_unique($languages);
                 
                 //build object json to return 
                 foreach ($languages as $key => $value) {

                 	//get repos for each language
                 	$langs[$key]['languages'] = $value;
	                $json_result = curlRequest('languages:'.$value);
	                
	                //if curl request had results
	                 if(isset($json_result['items'])){
		                $langs[$key]['total_count'] = $json_result['total_count'];
	                    $langs[$key]['repos'] = array();
                        //get list repos url for each language
		                foreach ($json_result['items'] as $index => $repos) {

		                	array_push($langs[$key]['repos'], $json_result['items'][$index]['html_url']);
		                }	                 	
		            }else{
		            	//no results returned
		            	//echo "no result from github for ".$value."\n";
		            	$langs[$key]['total_count'] = null;
		            	$langs[$key]['repos'] = null;
		            }
		            

	     
                 }

                 header('Content-Type: application/json');
				 echo json_encode($langs); 
                
             } else {
             	echo 'no result from github';
             }

 ?>