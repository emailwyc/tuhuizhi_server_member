<?php
namespace MerAdmin\Model;
use common\MSDaoBase;

class ActivityModel extends MSDaoBase{
    const  TABLENAME = 'integral_activity';
    const  SYSTEM_0 = 0;//积分商城
    const  SYSTEM_1 = 1;//活动券
    const  SYSTEM_2 = 2;//新版积分商城
    const  IS_SHOWTIME  = 1;//在展示时间中
    const  NOT_SHOWTIME = 2;//不在展示时间中
    
    public $db;
    
    public function __construct()
    {
        $this->db = M(self::TABLENAME); 
    }
    
    /**
     * 获取活动信息
     *
     * @param int $admin_id
     * @param int $type
     * @param int $buildid
     * @param int $system
     * @return
     */
    public function getAll($admin_id, $activity, $type, $buildid, $system, $notid, $isShowTime)
    {
        $arr['admin_id'] = $admin_id;
        $arr['system'] = $system;
        
        if($type != '')
        {
            $arr['type'] = $type;
        }
        if($activity != '')
        {
            $arr['activity'] = $activity;
        }
        if($buildid != '')
        {
            $arr['buildid'] = $buildid;
        }
        if($notid != 0)
        {
            $arr['id'] = array('NEQ', $notid);
        }
        if($isShowTime == self::IS_SHOWTIME)//c端展示中
        {
            $arr['start_showtime']  = array('LT', date('Y-m-d H:i:s', time()));
            $arr['end_showtime']  = array('GT', date('Y-m-d H:i:s', time()));
        }
        if($isShowTime == self::NOT_SHOWTIME)//c端未展示
        {
            $arr['end_showtime']  = array('LT', date('Y-m-d H:i:s', time()));
        }
        
        $arr = $this->db->where($arr)->select();
        
        return $arr;
    }
    
    /**
     * 获取一条活动信息
     *
     * @param int $admin_id
     * @param int $type
     * @param int $buildid
     * @param int $system
     * @return
     */
    public function getOnce($admin_id, $type, $buildid, $system)
    {
        $arr['admin_id'] = $admin_id;
        $arr['system'] = $system;
    
        if($type != '')
        {
            $arr['type'] = $type;
        }
        if($buildid != '')
        {
            $arr['buildid'] = $buildid;
        }
    
        $arr = $this->db->where($arr)->find();
    
        return $arr;
    }
    
    /**
     * 根据活动id获取一条活动信息
     *
     * @param int $actity
     * @return
     */
    public function getByActity($actity)
    {
        $arr['activity'] = $actity;
        
        $arr = $this->db->where($arr)->find();
    
        return $arr;
    }
    
    /**
     * 按唯一id获取一条活动信息
     *
     * @param int $id
     * @return
     */
    public function getById($id)
    {
        $arr['id'] = $id;
    
        $arr = $this->db->where($arr)->find();
    
        return $arr;
    }
    
    /**
     * 按唯一id删除
     *
     * @param int $id
     * @return
     */
    public function delById($id)
    {
        $arr['id'] = $id;
    
        $res = $this->db->where($arr)->delete();
    
        return $res;
    }
    
    /**
     * 插入一条活动券信息
     *
     * @param array $insert
     * @return int
     */
    public function add($insert)
    {
        $lastid = $this->db->add($insert);
    
        return $lastid;
    }
    
    /**
     * 更新一条活动券信息
     *
     * @param int $id
     * @param arr $arr
     * @return
     */
    public function updateById($id, $arr)
    {
        $this->db->where(array('id'=>$id))->save($arr);
    
        return $id;
    }
}
?>