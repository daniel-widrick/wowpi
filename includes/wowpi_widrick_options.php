<?php

$GLOBALS["wowpi_options"] = Array();

function wowpi_widrick_get_option($option)
{
	return $GLOBALS["wowpi_options"][$option];
}

function wowpi_widrick_update_option($option,$value)
{
	$GLOBALS["wowpi_options"][$option] = $value;
}
