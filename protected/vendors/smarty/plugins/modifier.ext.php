<?php
function smarty_modifier_ext($string)
{
	return substr($string, strrpos($string, '.')+1);
}
