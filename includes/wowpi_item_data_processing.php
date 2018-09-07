<?php
function wowpi_get_item($item_id, $region = null, $locale = null)
{
  global $wowpi_options;
  $returned = get_option('wowpi_items');
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
  
  
  if(($returned==false) || !isset($returned['data'][$item_id]) || (isset($returned['last_update']) && (intval($returned['last_update'])+$caching)<current_time('timestamp')))
  {
    $wowpi_key = $wowpi_options['api_key'];
    
    $service_url = 'https://'.$region.'.api.battle.net/wow/item/'.$item_id.'?locale='.$locale.'&apikey='.$wowpi_key;
    
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
        'quality' => $curl_response->quality
      );
    }
    update_option('wowpi_items', $returned);
  }
  $returned = get_option('wowpi_items');
  return $returned['data'][$item_id];  
}


function wowpi_get_artifact($artifact_id, $region = null, $locale = null)
{
  global $wowpi_options;
  $returned = get_option('wowpi_artifact_weapons');
  
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
  
  
  if(($returned==false) || !isset($returned['data'][$artifact_id]) || (isset($returned['last_update']) && (intval($returned['last_update'])+(14*24*60*60))<current_time('timestamp')))
  {
    $wowpi_key = $wowpi_options['api_key'];
    
    $service_url = 'https://'.$region.'.api.battle.net/wow/item/'.$artifact_id.'?locale='.$locale.'&apikey='.$wowpi_key;
    
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
        'bonus_stats' => $curl_response->bonusStats,
        'traits' => $curl_response->itemSpells,
        'item_class' => $curl_response->itemClass,
        'item_sub_class' => $curl_response->itemSubClass,
        'weapon_info' => $curl_response->weaponInfo,
        'sockets' => $curl_response->socketInfo,
        'artifact_id' => $curl_response->artifactId
      );
    }
    update_option('wowpi_artifact_weapons', $returned);
  }
  $returned = get_option('wowpi_artifact_weapons');
  return $returned['data'][$artifact_id];
}