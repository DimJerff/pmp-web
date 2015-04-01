<?php
function smarty_modifier_json($string)
{
	return CJSON::encode($string);
}
