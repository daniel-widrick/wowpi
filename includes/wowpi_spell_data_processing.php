<?php
function wowpi_get_spell($spell_id, $region = null, $locale = null)
{
  global $wowpi_options;
  $returned = get_option('wowpi_spells');
  $caching = intval($wowpi_options['caching']);
  
  if(!isset($region))
  {
    $region = (isset($wowpi_options['region']) && strlen($wowpi_options['region'])>0) ? $wowpi_options['region'] : $region;
  }
  
  // if no locale was given we get the default locale
  if(!isset($locale))
  {
    $locale = (isset($wowpi_options['locale']) && strlen($wowpi_options['locale'])>0) ? $wowpi_options['locale'] : $locale;
    $locale = rawurlencode($locale);
  }
  
  
  if(($returned==false) || !isset($returned['data'][$spell_id]) || (isset($returned['last_update']) && (intval($returned['last_update'])+$caching)<current_time('timestamp')))
  {
    $wowpi_key = $wowpi_options['api_key'];
    
    $service_url = 'https://'.$region.'.api.battle.net/wow/spell/'.$spell_id.'?locale='.$locale.'&apikey='.$wowpi_key;
    
    $curl_response = wowpi_retrieve_data($service_url);
    
    $curl_response = json_decode($curl_response);
    
    if(isset($curl_response) && !empty($curl_response))
    {
      $last_update = current_time('timestamp');
      $returned['last_update'] = $last_update;      
      $returned['data'][$curl_response->id] = array(
        'id' => $curl_response->id,
        'name' => $curl_response->name,
        'description'=> $curl_response->description,
        'icon' => $curl_response->icon,
        'cast_time' => $curl_response->castTime
      );
    }
    update_option('wowpi_spells', $returned);
  }
  $returned = get_option('wowpi_spells');
  return $returned['data'][$spell_id];  
}