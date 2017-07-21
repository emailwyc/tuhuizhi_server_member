<?php
namespace CrmService\Controller;

interface CrminterfaceController
{

    /**
     * @deprecated 根据openid获取会员信息
     * @传入参数   key_admin、sign、openid
     *
     */
    public function GetUserinfoByOpenid();
    
    /**
     * @deprecated 根据卡号获取会员信息
     * @传入参数   key_admin、sign、card
     * 
     */
    public function GetUserinfoByCard();
    
    
    /**
     * @deprecated 根据手机号获取会员信息
     * @传入参数  key_admin、sign、mobile
     */
    public function GetUserinfoByMobile();
    
    
    
    /**
     * @deprecated  创建会员
     * @传入参数  key_admin、sign、mobile、sex、idnumber、name
     */
    public function createMember();
    
    
    /**
     * @deprecated  修改会员信息
     * @传入参数  key_admin、sign、mobile、sex、idnumber、name、cardno
     */
    public function editMember();
    
    
    
    /**
     * @deprecated  积分扣除
     * @传入参数  key_admin、sign、cardno、scoreno、why
     */
    public function cutScore();
    
    
    
    
    /**
     * @deprecated  积分添加
     * @传入参数  key_admin、sign、cardno、scoreno、scorecode、why、membername
     */
    public function addintegral();
    
    
    /**
     * @deprecated 用户积分详细列表
     */
    public function scorelist();
    
    
    /**
     * @deprecated 欧亚卖场
     * @传入参数 key_admin、sign 、skt、Jlbh、md
     */
    public function billInfo();
}

?>