<?php
function smarty_function_pagingurl($params, $template)
{
	return Paging::instance()->getQueryUrl($params);
}
