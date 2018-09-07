<?php

function wowpi_show_guild($with = null,$guild_name = null, $realm = null)
{
  global $wowpi_plugin_dir;
  global $wowpi_plugin_url;
  global $wowpi_options;
  
  $guild = wowpi_get_guild(null,$guild_name,$realm);
  if($guild===false)
  {
    echo '<!--Guild doesn\'t exist. Sorry...-->';
    return false;
    die();
  }
  
  //echo '<pre>'; print_r($guild); echo '</pre>';
  
  $guild_members = wowpi_get_guild('members',$guild_name,$realm);
  
  $total_members = sizeof($guild_members);
  
  echo '<div class="wowpi_guild_container">';
  echo '<div class="guild_tabard side_'.strtolower($guild['side']).'">';
  $tabard = wowpi_get_guild_tabard($guild_name,$realm);
  echo '<img src="'.$tabard.'" class="crest" />';
  
  echo '</div>';
  echo '<div class="guild_name">';
  echo $guild['name'];
  echo '</div>';
  echo '<div class="guild_details">';
  echo ucfirst((($guild['side']=='1') ? 'horde' : 'alliance')).' Guild, '.$guild['realm'].', '.$total_members.' members';
  echo '</div>';
  echo '</div>';
  ?>
  <?php
  echo '</div>';
  
  if(isset($with) && !empty($with))
  {
    foreach($with as $content)
    {
      if($content!='nothing' && function_exists('wowpi_show_guild_'.$content))
      {
        $function = 'wowpi_show_guild_'.$content;
        $function($character_name,$realm);
      }
    }
  }
}

function displayCurrentSpecTitle($character_name = null, $realm = null)
	{
		$character_talents = wowpi_get_character('talents',$character_name,$realm);
		return $character_talents[0]['name'];
	}
	
function wowpi_show_character($with = array(),$character_name=null,$realm=null)
{
  global $wowpi_options;
  global $wowpi_plugin_dir;
  global $wowpi_plugin_url;
  $races = wowpi_general_data('races');
  $classes = wowpi_general_data('classes');
  //echo '<pre>';print_r($classes);echo '</pre>';
  $guild = wowpi_get_character('guild',$character_name,$realm);
  
  
  $character_data = wowpi_get_character(null, $character_name,$realm);
  //print_r($character_data);
  //$character_stats = wowpi_get_character('stats');
  $character_titles = wowpi_get_character('titles',$character_name,$realm);
  
  if(empty($character_data) || (array_key_exists('status',$character_data) && $character_data['status']=='nok'))
  {
    echo $character_data['reason'];
    return true;
    exit;
  }
  $character_name = $character_data['name'];
  $character = $character_name;
  if(isset($character_data['title']['name']))
  {
    $main_title = $character_data['title']['name'];
  }
  $spec_id = $character_data['spec_id'];
  //$wowpi_main_title = $character_titles[$character_titles['main']]['name'];
  $realm = $character_data['realm'];
  $battlegroup = $character_data['battlegroup'];  
  $class = $classes[$character_data['class']]['name'];
  $race = $races[$character_data['race']]['name'];
  $specialization = displayCurrentSpecTitle($character_name,$realm);
  $side = $races[$character_data['race']]['side'];
  $gender = $character_data['gender'];
  $level = $character_data['level'];
  $thumbnail = $character_data['thumbnail'];
  $calc_class = $character_data['calc_class'];
  $faction = $character_data['faction'];
  //$wowpi_guild = array('name' => $guild_data->name, 'members' => $guild_data->members, 'emblem' => $guild_data->emblem, 'icon' => $guild_data->icon, 'icon_color' => $guild_data->iconColor, 'border' => $guild_data->border, 'border_color' => $guild_data->borderColor, 'background' => $guild_data->backgroundColor);
  
  echo '<div class="wowpi_character_container '.strtolower($side).' '.strtolower($race).' '.str_replace(' ','_',strtolower($class)).'"';
  echo ($wowpi_options['styling'] == 'wowpi_faction' ? ' style="background-image: url(\''.$wowpi_plugin_url.'assets/images/theme/wowpi_'.strtolower($side).'.jpg\')"' : '');
  echo '>';
  
  /*
  $class_image = 'assets/images/symbols/class-'.str_replace(' ','',strtolower($class)).'.png';
  if(file_exists($wowpi_plugin_dir.$class_image))
  {
    echo '<img src="'.$wowpi_plugin_url.$class_image.'" class="class_image" />';
  }
  */
  ?>
  <div class="module character">
    <div class="character_name">
      <?php    if(strlen($thumbnail)>0)
		{
		  /*$upload_dir = wp_upload_dir();
		  $image = $upload_dir['basedir'].'/wowpi/character_avatar_'.$thumbnail.'.jpg';
		  if(file_exists($image))
		  {
			echo '<img src="'.$upload_dir['baseurl'].'/wowpi/character_avatar_'.$thumbnail.'.jpg'.'" class="character_image" />';
			}*/
		  $upload_dir = wp_upload_dir();
		  $image = $upload_dir['basedir'].'/wowpi/character_inset_'.$thumbnail.'.jpg';
		  if(file_exists($image))
		  {
			echo '<a href="'.$upload_dir['baseurl'].'/wowpi/character_profile_'.$thumbnail.'.jpg'.'"><img src="'.$upload_dir['baseurl'].'/wowpi/character_inset_'.$thumbnail.'.jpg'.'" class="character_image" /></a>';
		  }
		}
		
		echo '<a href="https://worldofwarcraft.com/en-us/character/aerie-peak/'.$character.'">';
		if(isset($main_title))
		{
			printf($main_title,$character);
		}
  		else echo $character;?>
		</a>
    </div>
    <div class="character_title"><?php printf( __('Level %1$s %2$s %3$s %4$s','wowpi'), $level, $race, $specialization, $class);?></div>
  </div>
  <?php
  if(!empty($with))
  {
    foreach($with as $content)
    {
      if($content!='nothing')
      {
        $function = 'wowpi_show_'.$content;
        echo $function($character_name,$realm,true);
      }
    }
  }
  ?>
   <!--<div class="realm"><?php echo $battlegroup.' / '.$realm;?></div>-->
</div>
<?php  
}

function wowpi_show_achievement_points($character_name = null,$realm = null, $with_title = true)
{
  $character_data = wowpi_get_character(null, $character_name,$realm);  
  $achievement_points = $character_data['achievement_points'];
  $all_achievements = wowpi_general_data('achievements');
  
  $output = '<div class="module achievements">';
  $output .= ($with_title==true) ? '<div class="title">'.wowpi_translate_title('achievement_points').'</div>' : '';
  $output .= '<div class="total">'.$achievement_points.'</div>';
  $output .= '<div class="from">'.__('out of','wowpi').' '.$all_achievements['total_points'].'</div>';
  return $output;
}

function wowpi_show_achievement_feed($character_name = null,$realm = null, $with_title = true,$limit = 10)
{
  global $wowpi_plugin_url;
  $character_achievements = wowpi_get_character('achievements', $character_name, $realm);
  
  $output = '';
  if(!empty($character_achievements))
  {
    $output .= '<div class="module achievement_feed">';
    $output .= ($with_title==true) ? '<div class="title">'.wowpi_translate_title('achievement_feed').'</div>' : '';
    $output .= '<ul>';
    foreach($character_achievements as $achievement)
    {
      //echo '<pre>';print_r($achievement);echo '</pre>';
      if($limit == 0)
      {
        break;
      }
      $output .= '<li style="background-image: url(\''.wowpi_retrieve_image($achievement['icon'], 'icon', 18).'\');"> '.date('d.m',$achievement['completed']).' - '.wowpi_get_tooltip($achievement['id'],'achievement','<strong>'.$achievement['title'].':</strong>').' '.$achievement['description'].' <span class="points">'.$achievement['points'].'</span></li>';
      $limit--;
    }
    $output .= '</ul>';
    $output .= '</div>';
  }
  return $output;
}

function wowpi_show_kills($character_name = null,$realm = null, $with_title = true)
{
  $character_data = wowpi_get_character(null, $character_name,$realm);  
  $honorable_kills = $character_data['honorable_kills'];
  
  $output = '<div class="module kills">';
  $output .= ($with_title==true) ? '<div class="title">'.wowpi_translate_title('kills').'</div>' : '';
  $output .= '<div class="total">'.$honorable_kills.'</div>';
  $output .= '</div>';
  
  return $output;
}



function wowpi_show_professions($character_name = null,$realm = null, $with_title = true)
{
  $professions = wowpi_get_character('professions',$character_name,$realm);
  $primary_professions = $professions['primary'];
  $secondary_professions = $professions['secondary'];
  
  $output = '';
  
  if(!empty($primary_professions) || !empty($secondary_professions))
  {
    $output .= '<div class="module professions">';
    if(!empty($primary_professions))
    {
      $output .= '<div class="primary">';
      $output .= ($with_title==true) ? '<div class="title">'.__('Primary professions:','wowpi').'</div>' : '';
      foreach($primary_professions as $profession)
      {
        $output .= '<div><img src="'.wowpi_retrieve_image($profession->icon, 'icon', 18).'" alt="'.$profession->name.'" /> '.$profession->name.'('.$profession->rank.')</div>';
      }
      $output .= '</div>';
    }
    if(!empty($secondary_professions))
    {
      $output .= '<div class="secondary">';
      $output .= ($with_title==true) ? '<div class="title">'.__('Secondary professions:','wowpi').'</div>' : '';
      foreach($secondary_professions as $profession)
      {
        $output .= '<div><img src="'.wowpi_retrieve_image($profession->icon, 'icon', 18).'" alt="'.$profession->name.'" /> '.$profession->name.'('.$profession->rank.')</div>';
      }
      $output .= '</div>';
    }
    $output .= '</div>';
  }
  return $output;
}

function wowpi_show_pvp($character_name = null,$realm = null, $with_title = true)
{
	$pvp = wowpi_get_character('pvp',$character_name, $realm);
	$character_data = wowpi_get_character(null, $character_name,$realm); 
	$character_data['data']['pvp']['brackets'] = array(
      '2v2' => $pvp['brackets']->ARENA_BRACKET_2v2,
      '3v3' => $pvp['brackets']->ARENA_BRACKET_3v3,
      'RBG' => $pvp['brackets']->ARENA_BRACKET_RBG
	);
	
	//if (!empty($pvp['brackets']->ARENA_BRACKET_2v2->rating))
	//{
		//echo '<pre>';print_r($pvp['brackets']->ARENA_BRACKET_2v2->slug); echo'</pre>';
	//}
	$output .='<div class="module pvp">';
	$output .='<div class="title">Season PvP Ratings:</div>';
	
	foreach($character_data['data']['pvp']['brackets'] as $bracket)
	{
		if (!empty($bracket->rating))
		{
			//echo '<pre>';print_r($bracket->slug);echo '</pre>';
			$output .='<div class="pvp-bracket">';
			$output .='<div class="bracket-name">'.$bracket->slug.'</div>';
			$output .='<div class="bracket-rating">'.$bracket->rating.'</div>';
			$output .='</div>';
		}
	}
	
	$output .='</div>';
	
	return $output;
}

function wowpi_show_ilvl($character_name = null, $realm = null, $with_title = true)
{
  $gear = wowpi_get_character('gear',$character_name, $realm);
  $character_data = wowpi_get_character(null, $character_name,$realm);
  $output = '<div class="module gear">';
  $output .= ($with_title==true) ? '<div class="title">'.wowpi_translate_title('ilvl').'</div>' : '';
  $output .= '<div class="itemlvl_total">'.$gear['averageItemLevel'].'</div>';
  $output .= '</div>';
  return $output;
}


function wowpi_show_gear($character_name = null, $realm = null, $with_title = true)
{
  $gear = wowpi_get_character('gear',$character_name, $realm);
  $character_data = wowpi_get_character(null, $character_name,$realm); 
  
  $output = '<div class="module gear">';
  $output .= ($with_title==true) ? '<div class="title">'.wowpi_translate_title('gear').'</div>' : '';
  $items = array('head','neck','shoulder','back','chest','shirt','tabard','wrist','hands','waist','legs','feet','finger1','finger2','trinket1','trinket2','mainHand','offHand');
  foreach($items as $item)
  {
    if(isset($gear[$item]))
    {
      $advanced = array();
      $the_item = $gear[$item];
      $advanced['lvl'] = $character_data['level'];
      if(isset($the_item->bonusLists)) $advanced['bonus'] = implode(':',$the_item->bonusLists);
      if(isset($the_item->tooltipParams->set)) $advanced['pcs'] = implode(':',$the_item->tooltipParams->set);
      /*
      echo '<pre>';
      print_r($the_item);
      echo '</pre>';
      */
      $output .= wowpi_get_tooltip($the_item->id,'item','<img src="'.wowpi_retrieve_image($the_item->icon).'" class="q'.$the_item->quality.'">',$advanced,'class="q'.$the_item->quality.'" title="'.$the_item->name.'"');
      //echo '<img src="//media.blizzard.com/wow/icons/36/'.$the_item->icon.'.jpg" />';
    }
  }
  $output .= '</div>';
  return $output;
}

function wowpi_show_artifact_weapon($character_name = null, $realm = null, $with_title = true)
{
  global $wowpi_plugin_dir;
  $character_data = wowpi_get_character(null, $character_name,$realm); 
  $spec_id = $character_data['spec_id'];
  //echo $spec_id;
  $traits_spells_json = file_get_contents($wowpi_plugin_dir.'/includes/artifact_traits_spells.json');
  $output = '';
  $traits_spells = json_decode($traits_spells_json,true);

  /*
  echo '<pre>';
  print_r($traits_spells);
  echo '</pre>';
  */

  $gear = wowpi_get_character('gear',$character_name, $realm);
  
  $weapon = $gear['mainHand'];
  //echo '<pre>';print_r($weapon);echo '</pre>';
  
  if(isset($weapon) && !empty($weapon))
  {
    $artifact_traits = $weapon->artifactTraits;
    
    if((!isset($artifact_traits) || empty($artifact_traits)) && isset($gear['offHand']))
    {
      $weapon = $gear['offHand'];
      $artifact_traits = $weapon->artifactTraits;
    }
    
    if(isset($artifact_traits) && !empty($artifact_traits))
    {
      $output .= '<div class="module artifact_weapon">';
      $output .= '<div class="artifact_traits">';
      $output .= ($with_title==true) ? '<div class="title">'.wowpi_translate_title('artifact_traits').'</div>' : '';
      $artifact_id = $weapon->id;
      $artifact_weapon = wowpi_get_artifact($artifact_id);
      
      //echo '<pre>';print_r($artifact_weapon);echo '</pre>';
      
      
      foreach($artifact_traits as $trait)
      {
        $trait_id = $trait->id;
        $rank = $trait->rank - 1;

        if(isset($trait_id) && isset($rank) && isset($traits_spells[$trait_id][$rank])) {
            $spell_id = $traits_spells[$trait_id][$rank];
            $spell = wowpi_get_spell($spell_id);
            $link_name = '<img src="' . wowpi_retrieve_image($spell['icon']) . '" alt="' . $spell['name'] . '" />';
            $output .= wowpi_get_tooltip($spell['id'], 'spell', $link_name, array('rank' => $rank), 'class="_q' . $rank . '"');
        }
      }
      $output .= '</div>';
      $output .= '<div class="artifact_relics">';
      $artifact_relics = $weapon->relics;
      if(isset($artifact_relics) && !empty($artifact_relics))
      {
        $output .= ($with_title==true) ? '<div class="title">'.wowpi_translate_title('artifact_relics').'</div>' : '';
        foreach($artifact_relics as $relic)
        {
          $item = wowpi_get_item($relic->itemId);
          $output .= wowpi_get_tooltip($relic->itemId,'item','<img src="'.wowpi_retrieve_image($item['icon']).'" alt="'.$item['name'].'" />',array('spec'=>$spec_id));
        }
      }
      $output .= '</div>';
      $output .= '</div>';
    }
  }
  
  return $output;
}

function wowpi_show_activity($character_name = null, $realm = null, $with_title = true)
{
  $character_activity = wowpi_get_character('feed',$character_name,$realm);
  if(!empty($character_activity))
  {
    echo '<ul>';
    foreach($character_activity as $activity)
    {
      echo '<li>';
      if($activity->type=='BOSSKILL')
      {
        echo '<img src="'.wowpi_retrieve_image($activity->achievement->icon).'" class="q">';
      }
      if($activity->type=='ACHIEVEMENT')
      {
        echo '<img src="'.wowpi_retrieve_image($activity->achievement->icon).'" class="q">';
      }
      if($activity->type=='LOOT')
      {
        echo '<img src="'.wowpi_retrieve_image($activity->itemId).'" class="q">[icondb='.$activity->itemId.']';
      }
      //echo '<img src="//media.blizzard.com/wow/icons/36/'.$activity->achievement->icon.'.jpg" />';
      //echo date('d.m.Y H:i:s', $activity->timestamp);
      echo '</li>';
    }
    echo '</ul>';
  }
}

function wowpi_show_talents($character_name = null, $realm = null, $with_title = true)
{
  $character_talents = wowpi_get_character('talents',$character_name,$realm);
  //echo '<pre>';print_r($character_talents).'</pre>';
  
  
  $output = '';
  $output .= '<div class="module talents">';
  $output .= ($with_title==true) ? '<div class="title">'.wowpi_translate_title('talents').'</div>' : ''; 
  foreach($character_talents as $spec)
  {
    $output .= '<div class="spec'.(($spec['selected']=='1') ? ' selected' : '').'">';
    $output .= '<div class="spec_name">'.$spec['name'].'</div>';
    $output .= '<div class="talents_enum">';
    foreach($spec['talents'] as $talent)
    {
      $output .= wowpi_get_tooltip($talent['id'],'spell','<img src="'.wowpi_retrieve_image($talent['icon']).'" />',array(),'title="'.$talent['name'].'"');
    }
    $output .= '</div>';
    $output .= '</div>';
  }
  $output .= '</div>';
  return $output;
}

function wowpi_translate_title($of_what)
{
  $returned = '';
  switch($of_what) {
    case 'achievement_points' :
      $returned = __('Achievement points:','wowpi');
      break;
    case 'achievement_feed' :
      $returned = __('Latest achievements:','wowpi');
      break;
    case 'kills' :
      $returned = __('Honorable kills:','wowpi');
      break;
    case 'professions' :
      $returned = __('Professions:','wowpi');
      break;
    case 'gear' :
      $returned = __('Gear:','wowpi');
      break;
    case 'artifact_weapon' :
      $returned = __('Artifact Weapon:','wowpi');
      break;
    case 'artifact_traits' :
      $returned = __('Artifact Traits:','wowpi');
      break;
    case 'artifact_relics' :
      $returned = __('Artifact Relics:','wowpi');
      break;
    case 'talents' :
      $returned = __('Talents:','wowpi');
      break;
    case 'ilvl' :
      $returned = __('Item level:');
      break;
  }
  return $returned;
}
