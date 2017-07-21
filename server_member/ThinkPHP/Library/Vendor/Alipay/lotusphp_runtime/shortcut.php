<?php
namespace Vendor\Alipay;
function C($className)
{
	return LtObjectUtil::singleton($className);
}
