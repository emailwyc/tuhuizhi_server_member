<?php
namespace common;

use Common\core\Singleton;
use service;

/**
 * 获取业务逻辑实例工具类
 * 
 */
class ServiceLocator
{
    /**
     * 取得一个CouponService对象
     *
     * @return \MerAdmin\Service\CouponService
     */
    public static function getCouponService($pre_table = '')
    {
        return Singleton::get("MerAdmin\\Service\\CouponService", $pre_table);
    }
    
    /**
     * 取得一个MemberService对象
     *
     * @return \Member\Service\MemberService
     */
    public static function getMemberService($pre_table = '')
    {
        return Singleton::get("Member\\Service\\MemberService", $pre_table);
    }
    
    /**
     * 取得一个ActivityService对象
     *
     * @return \MerAdmin\Service\ActivityService
     */
    public static function getActivityService($pre_table = '')
    {
        return Singleton::get("MerAdmin\\Service\\ActivityService", $pre_table);
    }
    
    /**
     * 取得一个ActivityTypeService对象
     *
     * @return \MerAdmin\Service\ActivityTypeService
     */
    public static function getActivityTypeService($pre_table = '')
    {
        return Singleton::get("MerAdmin\\Service\\ActivityTypeService", $pre_table);
    }
    
    /**
     * 取得一个ActivityPropertyService对象
     *
     * @return \MerAdmin\Service\ActivityPropertyService
     */
    public static function getActivityPropertyService($pre_table = '')
    {
        return Singleton::get("MerAdmin\\Service\\ActivityPropertyService", $pre_table);
    }
    
    /**
     * 取得一个UserCardPropertyService对象
     *
     * @return \MerAdmin\Service\UserCardService
     */
    public static function getUserCardService($pre_table = '')
    {
        return Singleton::get("MerAdmin\\Service\\UserCardService", $pre_table);
    }
    
    /**
     * 取得一个CommonService对象
     *
     * @return \MerAdmin\Service\CommonService
     */
    public static function getCommonService($pre_table = '')
    {
        return Singleton::get("MerAdmin\\Service\\CommonService", $pre_table);
    }
    
    /**
     * 取得一个OrderService对象
     *
     * @return \MerAdmin\Service\OrderService
     */
    public static function getOrderService($pre_table = '')
    {
        return Singleton::get("MerAdmin\\Service\\OrderService", $pre_table);
    }
    
    /**
     * 取得一个ComplaintService对象
     *
     * @return \MerAdmin\Service\ComplaintService
     */
    public static function getComplaintService($pre_table = '')
    {
        return Singleton::get("MerAdmin\\Service\\ComplaintService", $pre_table);
    }
    
    /**
     * 取得一个VisitorService对象
     *
     * @return \MerAdmin\Service\VisitorService
     */
    public static function getVisitorService($pre_table = '')
    {
        return Singleton::get("MerAdmin\\Service\\VisitorService", $pre_table);
    }

    /**
     * 取得一个HotelService对象
     * @param string $pre_table
     * @return Object
     */
    public static function getHotelService($pre_table = '')
    {
        return Singleton::get("MerAdmin\\Service\\HotelService", $pre_table);
    }

    /**
     * 取得一个ServiceTag对象
     * @param string $pre_table
     * @return Object
     */
    public static function getServiceTagService($pre_table = '')
    {
        return Singleton::get("MerAdmin\\Service\\TotalService", $pre_table);
    }
    /**
     * 取得一个AdminService对象
     *
     * @return \MerAdmin\Service\AdminService
     */
    public static function getAdminService($pre_table = '')
    {
        return Singleton::get("MerAdmin\\Service\\AdminService", $pre_table);
    }

    /**
     * 取得一个VisitorLogService对象
     *
     * @return \MerAdmin\Service\VisitorLogService
     */
    public static function getVisitorLogService($pre_table = '')
    {
        return Singleton::get("MerAdmin\\Service\\VisitorLogService", $pre_table);
    }

    /**
     * 取得一个CustomerService对象
     *
     * @return \MerAdmin\Service\CustomerService
     */
    public static function getCustomerService($pre_table = '')
    {
        return Singleton::get("MerAdmin\\Service\\CustomerService", $pre_table);
    }
    
    /** 取得一个AppletConfigService对象
     *
     * @return \MerAdmin\Service\AppletConfigService
     */
    public static function getAppletConfigService($pre_table = '')
    {
        return Singleton::get("MerAdmin\\Service\\AppletConfigService", $pre_table);
    }
    
    /**
     * 取得一个AppletShopService对象
     *
     * @return \MerAdmin\Service\AppletShopService
     */
    public static function getAppletShopService($pre_table = '')
    {
        return Singleton::get("MerAdmin\\Service\\AppletShopService", $pre_table);
    }

    /**
     * 取得一个AppletCouponService对象
     *
     * @return \MerAdmin\Service\AppletCouponService
     */
    public static function getAppletCouponService($pre_table = '')
    {
        return Singleton::get("MerAdmin\\Service\\AppletCouponService", $pre_table);
    }

    /**
     * 取得一个AppletCouponLogService对象
     *
     * @return \MerAdmin\Service\AppletCouponLogService
     */
    public static function getAppletCouponLogService($pre_table = '')
    {
        return Singleton::get("MerAdmin\\Service\\AppletCouponLogService", $pre_table);
    }

    /**
     * 取得一个ExcelService对象
     *
     * @return \MerAdmin\Service\ExcelService
     */
    public static function getExcelService($pre_table = '')
    {
        return Singleton::get("MerAdmin\\Service\\ExcelService", $pre_table);
    }
    
    /**
     * 取得一个ActivityPropertyNewService对象
     *
     * @return \MerAdmin\Service\ActivityPropertyNewService
     */
    public static function getActivityPropertyNewService($pre_table = '')
    {
        return Singleton::get("MerAdmin\\Service\\ActivityPropertyNewService", $pre_table);
    }
    
    /**
     * 取得一个IntegralLogService对象
     *
     * @return \MerAdmin\Service\IntegralLogService
     */
    public static function getIntegralLogService($pre_table = '')
    {
        return Singleton::get("MerAdmin\\Service\\IntegralLogService", $pre_table);
    }
    
    /**
     * 取得一个TagsGroupService对象
     *
     * @return \MerAdmin\Service\TagsGroupService
     */
    public static function getTagsGroupService($pre_table = '')
    {
        return Singleton::get("MerAdmin\\Service\\TagsGroupService", $pre_table);
    }
    
    /**
     * 取得一个TagsGroupTagsService对象
     *
     * @return \MerAdmin\Service\TagsGroupTagsService
     */
    public static function getTagsGroupTagsService($pre_table = '')
    {
        return Singleton::get("MerAdmin\\Service\\TagsGroupTagsService", $pre_table);
    }
    
    /**
     * 取得一个TagsService对象
     *
     * @return \MerAdmin\Service\TagsService
     */
    public static function getTagsService($pre_table = '')
    {
        return Singleton::get("MerAdmin\\Service\\TagsService", $pre_table);
    }
    
    /**
     * 取得一个TagsChooseService对象
     *
     * @return \MerAdmin\Service\TagsChooseService
     */
    public static function getTagsChooseService($pre_table = '')
    {
        return Singleton::get("MerAdmin\\Service\\TagsChooseService", $pre_table);
    }
    
    /**
     * 取得一个ActivityDrawService对象
     *
     * @return \MerAdmin\Service\ActivityDrawService
     */
    public static function getActivityDrawService($pre_table = '')
    {
        return Singleton::get("MerAdmin\\Service\\ActivityDrawService", $pre_table);
    }
    
    /** 取得一个CollectionService对象
     *
     * @return \MerAdmin\Service\CollectionService
     */
    public static function getCollectionService($pre_table = '')
    {
        return Singleton::get("MerAdmin\\Service\\CollectionService", $pre_table);
    }

    /**
     * 取得一个TagsThemsService对象
     *
     * @return \MerAdmin\Service\TagsThemsService
     */
    public static function getTagsThemsService($pre_table = '')
    {
        return Singleton::get("MerAdmin\\Service\\TagsThemsService", $pre_table);
    }
    
    /**
     * 取得一个TagsThemsBannersService对象
     *
     * @return \MerAdmin\Service\TagsThemsBannersService
     */
    public static function getTagsThemsBannersService($pre_table = '')
    {
        return Singleton::get("MerAdmin\\Service\\TagsThemsBannersService", $pre_table);
    }
    
    /**
     * 取得一个TagsThemsTagsService对象
     *
     * @return \MerAdmin\Service\TagsThemsTagsService
     */
    public static function getTagsThemsTagsService($pre_table = '')
    {
        return Singleton::get("MerAdmin\\Service\\TagsThemsTagsService", $pre_table);
    }
    
    /**
     * 取得一个ThemgroupBannersService对象
     *
     * @return \MerAdmin\Service\ThemgroupBannersService
     */
    public static function getThemgroupBannersService($pre_table = '')
    {
        return Singleton::get("MerAdmin\\Service\\ThemgroupBannersService", $pre_table);
    }
    
}

