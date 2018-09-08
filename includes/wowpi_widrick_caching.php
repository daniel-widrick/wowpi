<?php


# Modes: recurrent | expire
#    recurrent: Define a time to wait before expiring the entire cache
#    -- Provides more consistent sync for members. (EG: updates every 6 hours). (Same time[s] every day).
#    -- Defined in seconds to wait before expiring entire cache
#    -- Can cause spikes in load times on first load[s] after expiration
#    expire: Define a time to wait after after load to expire
#    -- Less consistent.
#    -- Potentially less spikey
#    --Defined in seconds to wait after loading a character to expire cache

#TODO: Settings
define('WOWPI_WIDRICK_CACHING_MODE','recurrent');
define('WOWPI_WIDRICK_CACHING_TIME',86400);
define('WOWPI_WIDRICK_CACHING_BASE_DIR','./'.'wowpicache/');

function wowpi_widrick_character_cache_save($characterHash,$character)
{
	$time = time();
	#$characterHash = $character['region'] . '-' . $character['realm'] . '-' . $character['name'];
	
	if(!file_exists(WOWPI_WIDRICK_CACHING_BASE_DIR))
	{
		clearstatcache();
		#Attempt to create cache directory
		if(mkdir(WOWPI_WIDRICK_CACHING_BASE_DIR) === false )
			return false;
	}

	$char_data = serialize($character);
	if(file_put_contents(WOWPI_WIDRICK_CACHING_BASE_DIR.$characterHash,$char_data))
		return true;
	else
		return false;
}

function wowpi_widrick_character_cache_test($characterHash)
{
	$cacheFile = WOWPI_WIDRICK_CACHING_BASE_DIR.$characterHash;
	if(file_exists($cacheFile))
	{
		switch(WOWPI_WIDRICK_CACHING_MODE)
		{
			case 'expire':
				$expireTime = time() - WOWPI_WIDRICK_CACHING_TIME;
				break;
			case 'recurrent':
				$today = strtotime('today midnight');
				$instance = time() - $today; //Get seconds since midnight
				$expireTime = floor($instance / WOWPI_WIDRICK_CACHING_TIME) * WOWPI_WIDRICK_CACHING_TIME + $today;
				break;
			default:
				echo "Unknown caching mode";
				return false;
		}
		if(filemtime($cacheFile) > $expireTime)
			return true;
	}
	return false;
}

function wowpi_widrick_character_cache_get($characterHash)
{
	if(wowpi_widrick_character_cache_test($characterHash))
	{
		return unserialize(file_get_contents(WOWPI_WIDRICK_CACHING_BASE_DIR.$characterHash));
	}
	else
		return false;
}
