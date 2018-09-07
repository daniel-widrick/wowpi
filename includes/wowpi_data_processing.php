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
    $output .= $tooltip_url.$type.'='.$id.'"';
    $output .= ' rel="';
    $output .= $type.'='.$id;
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
  $service_url = 'https://'.$region.'.api.battle.net/wow/'.$api.'/'.trim(str_replace(array(' ','\''),array('%20','%27'),$realm)).'/'.rawurlencode($name).'?';
  $service_url .= ($field=='profile') ? '' : 'fields='.$field.'&';
  $service_url .= 'locale='.$locale.'&apikey='.$wowpi_key;
  
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

function wowpi_general_data($data_requested,$data_type = 'character')
{
  global $wowpi_options;
  $data_requested = filter_var($data_requested, FILTER_SANITIZE_STRING);
  $returned = get_option('wowpi_'.$data_type.'_'.$data_requested);
  if(($returned==false) || (isset($returned['last_update']) && (intval($returned['last_update'])+(62*24*60*60))<current_time('timestamp')))
  {
    $region = (isset($wowpi_options['region']) && strlen($wowpi_options['region'])>0) ? $wowpi_options['region'] : 'eu';
    $locale = (isset($wowpi_options['locale']) && strlen($wowpi_options['locale'])>0) ? $wowpi_options['locale'] : 'en_GB';
    $wowpi_key = $wowpi_options['api_key'];
    
    $service_url = 'https://'.$region.'.api.battle.net/wow/data/'.$data_type.'/'.$data_requested.'?locale='.$locale.'&apikey='.$wowpi_key;
    
    $curl_response = wowpi_retrieve_data($service_url);
    $decoded = json_decode($curl_response);
    $returned = array();
    $returned_data = array();
    
    if($data_type=='character')
    {
      if($data_requested == 'races')
      {
        $races_obj = $decoded->races;
        if(sizeof($races_obj)>0)
        {
          $races_arr = array();
          foreach($races_obj as $race)
          {
            $races_arr[$race->id] = array(
              'mask'=>$race->mask,
              'side'=>$race->side,
              'name'=>$race->name
            );
          }
          $returned['data'] = $races_arr;  
        }
      }
      if($data_requested == 'classes')
      {
        $classes_obj = $decoded->classes;
        if(sizeof($classes_obj)>0)
        {
          $classes_arr = array();
          foreach($classes_obj as $class)
          {
            $classes_arr[$class->id] = array(
              'mask'=>$class->mask,
              'type'=>$class->powerType,
              'name'=>$class->name
            );
          }
          $returned['data'] = $classes_arr;
        }      
      }
      if($data_requested == 'achievements')
      {
        $returned_data = array();
        $achievements = $decoded->achievements;
        $achievements_arr = array();
        $total_achievement_points = 0;
        foreach($achievements as $achievement)
        {
          if(isset($achievement->categories))
          {
            foreach($achievement->categories as $category)
            {
              $achieve_chunk = $category->achievements;
              foreach($achieve_chunk as $achiev)
              {
                $achievements_arr[$achiev->id] = array(
                    'title'=>$achiev->title,
                    'description'=>$achiev->description,
                    'points'=>$achiev->points,
                    'icon'=>$achiev->icon,
                    'account_wide'=>$achiev->accountWide,
                    'faction_id'=>$achiev->factionId
                  );
                  $total_achievement_points += $achiev->points;          
              }
            }
          }
          else
          {
            $achieve_chunk = $achievement->achievements;
            foreach($achieve_chunk as $achiev)
            {
              $achievements_arr[$achiev->id] = array(
                  'title'=>$achiev->title,
                  'description'=>$achiev->description,
                  'points'=>$achiev->points,
                  'icon'=>$achiev->icon,
                  'account_wide'=>$achiev->accountWide,
                  'faction_id'=>$achiev->factionId
                );
                $total_achievement_points += $achiev->points;          
            }
          }        
        }
        $returned_data = $achievements_arr;
        $returned['data']['total_points'] = $total_achievement_points;
        $returned['data']['achievements'] = $returned_data;
      }
    }
    elseif($data_type=='guild')
    {
      if($data_requested == 'achievements')
      {
        $returned_data = array();
        $achievements = $decoded->achievements;
        //echo '<pre>';print_r($achievements);echo '</pre>';
        $achievements_arr = array();
        $total_achievement_points = 0;
        foreach($achievements as $achievement_group)
        {
          //echo $achievement_group->name;
          $group_achievements = array();
          if(isset($achievement_group->categories))
          {
            foreach($achievement_group->categories as $category)
            {
              $category_achievements = array();
              foreach($category->achievements as $achievement)
              {
                $criteria_arr = array();
                foreach($achievement->criteria as $criteria)
                {
                  $name = isset($criteria->name) ? $criteria->name : '';
                  $criteria_arr[$criteria->id] = array('id'=>$criteria->id,'name'=>$name,'description'=>$criteria->description,'max'=>$criteria->max);
                }
                $name = isset($achievement->name) ? $achievement->name : '';
                $achievements_arr[$achievement->id] = array('id'=>$achievement->id,'name'=>$name,'description'=>$achievement->description,'points'=>$achievement->points,'icon'=>$achievement->icon, 'criteria'=>$criteria_arr);
                $total_achievement_points += $achievement->points;
              }
              //$group_achievements['categories'][$category->id] = array('id'=>$category->id,'name'=>$category->name,'achievements'=>$category_achievements);
            }
          }
          foreach($achievement_group->achievements as $achievement)
          {
            $criteria_arr = array();
            foreach($achievement->criteria as $criteria)
            {
              $name = isset($criteria->name) ? $criteria->name : '';
              $criteria_arr[$criteria->id] = array('id'=>$criteria->id,'name'=>$name,'description'=>$criteria->description,'max'=>$criteria->max);
            }
            $name = isset($achievement->name) ? $achievement->name : '';
            $achievements_arr[$achievement->id] = array('id'=>$achievement->id,'name'=>$name,'description'=>$achievement->description,'points'=>$achievement->points,'icon'=>$achievement->icon, 'criteria'=>$criteria_arr);
            $total_achievement_points += $achievement->points;
          }
          //$achievements_arr[$achievement_group->id] = array('id'=>$achievement_group->id,'name'=>$achievement_group->name, 'achievements'=>$group_achievements);
        }
        $returned['data']['total_points'] = $total_achievement_points;
        $returned['data']['achievements'] = $achievements_arr;
      }
    }
    //echo '<pre>';print_r($returned['data']);echo '</pre>';
    if(isset($returned['data']) && !empty($returned['data'])) $returned['last_update'] = current_time('timestamp');
    update_option('wowpi_'.$data_type.'_'.$data_requested, $returned);
  }  
  $returned = get_option('wowpi_'.$data_type.'_'.$data_requested);
  return $returned['data'];
}

function wowpi_retrieve_data($service_url)
{
  $response = wp_remote_get($service_url);
  if (!is_array($response)) {
    echo 'Error occured during query. Maybe your website doesn\'t allow outgoing connections? <!--'.$service_url.'--> Response code: '. wp_remote_retrieve_response_code( $response );
    return false;
  }
  else
  {
    return $response['body'];
  }
}

function sort_array_by($array = array(),$sort_key, $direction = 'ASC')
{
  $direction = strtoupper($direction);
  $sorted = array();
  if(!empty($array))
  {
    foreach ($array as $key => $row)
    {
      $sorted[$key] = $row[$sort_key]; 
    }
    array_multisort($sorted, constant('SORT_'.$direction), $array);
  }
  return $array;
}

function get_http_response_code($url) {
    $headers = get_headers($url);
    return substr($headers[0], 9, 3);
}