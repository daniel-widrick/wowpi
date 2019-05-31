<?php

function wowpi_get_tooltip($id,$type='spell',$content = '', $advanced = array(),$append = '')
{
  global $wowpi_options;
  
  //let's be kind to other languages and locales
  $locale = $wowpi_options['locale'];
  $locale_arr = explode('_',$locale);
  $tooltip_language = $locale_arr[0];
  
  $tooltip_url = $wowpi_options['tooltips'];
  
  $output = '<a href="';
  if(strpos($tooltip_url,'wowdb')!==false)
  {
    $output .= $tooltip_url.$type.'s/'.$id.'?';
    if(isset($advanced['pcs']))
    {
      $output .= 'setPieces='.str_replace(':',',',$advanced['pcs']).'&';
    }
    if(isset($advanced['bonus']))
    {
      $output .= 'bonusIDs='.str_replace(':',',',$advanced['bonus']).'&';
    }

    $output .= '"';
  }
  elseif(strpos($tooltip_url,'wowhead')!==false)
  {
    $output .= $tooltip_url.$type.'='.$id;
    if(isset($advanced['bonus'])) {
        $output .= '&amp;bonus='.$advanced['bonus'];
    }
    if(isset($advanced['itemLevel'])) {
        $output .= '&amp;ilvl='.$advanced['itemLevel'];
        unset($advanced['itemLevel']);
    }


    $output .= '"';
    $output .= ' rel="';
    foreach($advanced as $key => $value)
    {
      $output .= '&amp;'.$key.'='.$value;
    }
    if(!isset($advanced['domain']))
    {
      //$output .= '&amp;domain=de';
      $output .= '&amp;domain='.$tooltip_language;
    }
    $output .= '"';
  }
  else
  {
    $output .= '#"';
  }
  $output .= ' '.$append.' target="_blank">';
  $output .= $content;
  $output .= '</a>';
  return $output;
}

// retrieve data from the battle.net server
function wowpi_get_curl($api, $name, $field, $realm = null, $region = null, $locale = null)
{
  global $wowpi_options;
  $wowpi_key = $wowpi_options['api_key'];
  
  //get the realm
  if(!isset($realm))
  {
    $realm = (isset($wowpi_options['realm']) && strlen($wowpi_options['realm'])>0) ? $wowpi_options['realm'] : $realm;
  }

  //get the region
  if(!isset($region))
  {
    $region = (isset($wowpi_options['region']) && strlen($wowpi_options['region'])>0) ? $wowpi_options['region'] : $region;
  }

  //get the language
  if(!isset($locale))
  {
    $locale = (isset($wowpi_options['locale']) && strlen($wowpi_options['locale'])>0) ? $wowpi_options['locale'] : $locale;
    $locale = rawurlencode($locale);
  }
  
  //create the service url
  
  $service_url = 'https://'.$region.'.api.blizzard.com/wow/'.$api.'/'.trim(str_replace(array(' ','\''),array('%20','%27'),$realm)).'/'.rawurlencode($name).'?';
  $service_url .= ($field=='profile') ? '' : 'fields='.$field.'&';
  $service_url .= 'locale='.$locale.'&access_token='.wowpi_getToken();
  
  // the curl thingy
  $curl_response = wowpi_retrieve_data($service_url);
  $decoded = json_decode($curl_response);
  if (isset($decoded->response->status) && $decoded->response->status == 'ERROR') {
    die('error occured: ' . $decoded->response->errormessage);
  }
  else
  {
    return $decoded;
  }
}

function wowpi_retrieve_data($service_url)
{
//Hack-a-cache
$cacheURL = explode('access_token',$service_url,2)[0];
$cacheURLHash = md5($cacheURL);
$cacheURLResult = wowpi_widrick_url_cache_get($cacheURLHash);
if($cacheURLResult !== false)
{
	//var_dump($service_url);
	echo '<!-- cacheHit: ' . $service_url . '-->';
	return $cacheURLResult;
}

//Start widrick fix for broken wordpress curl
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $service_url);
//curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
ob_Start();
$result = curl_exec($ch);
$response = ob_get_contents();
ob_end_clean();

if(curl_getinfo($ch, CURLINFO_HTTP_CODE) !== 200)
{
	var_dump($service_url);
	var_dump(curl_getinfo($ch, CURLINFO_HTTP_CODE));
	var_dump(debug_backtrace());
	return false;
}
if($result === false)
{
	echo "CURL DIED!";
	return false;
}
wowpi_widrick_url_cache_save($cacheURLHash,$response);
return $response;
//End widrick fix for broken wordpress curl

}



function get_http_response_code($url) {
    $headers = get_headers($url);
    return substr($headers[0], 9, 3);
}
