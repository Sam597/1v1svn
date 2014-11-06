<?php
/**
 * 
 * Empty (空模块)
 *
 * @package      	YOURPHP
 * @author           <admin@duoyan.net>
 * @copyright     	Copyright (c) 2008-2011  (http://www.duoyan.net)
 * @license         http://www.duoyan.net/license.txt
 * @version        	duoyan企业网站管理系统 v2.1 2012-10-08 duoyan.net $
 */
if(!defined("Yourphp")) exit("Access Denied");
class EmptyAction extends Action
{	
	public function _empty()
	{
		R('Admin/Content/'.ACTION_NAME);
	}
}
?>