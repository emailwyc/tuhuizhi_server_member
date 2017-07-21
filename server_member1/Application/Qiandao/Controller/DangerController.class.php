<?php
// namespace Qiandao\Controller;

// use Think\Controller;
// class DangerController extends Controller{
//     // TODO - Insert your code here
//     public function warning(){
//         if (!$_GET['yes']){
//             echo '<p style="text-align:center;fonts-size:30px;width:100%;">危险动作！！！</p>';
//             echo '<p style="text-align:center;fonts-size:30px;width:100%;">Are you sure？？？';
//             echo '<p style="text-align:center;width:100%;"><a href="'.U('Qiandao/Danger/warning',array('yes'=>'yes')).'">Yeah , I am sure.</a></p>';
//             echo '<p style="text-align:center;width:100%;"><a href="'.U('Qiandao/Danger/warning',array('yes'=>'no')).'">Oops , I am not sure.</a></p>';
//         }else if ($_GET['yes']=='yes'){
//             $db=M('history','sign_');
//             $db2=M('last_history','sign_');
            
//             $db->where('1=1')->delete();
//             $db2->where('1=1')->delete();
            
//             echo '<p style="text-align:center;font-size:30px;width:100%;"><a href="'.U('Qiandao/Danger/warning').'">我还要清。</a></p>';
//         }elseif ($_GET['yes'] == 'no'){
//             echo '<p style="text-align:center;font-size:30px;width:100%;">嗯哼，就知道你不敢清！！！</p>';
//             echo '<p style="text-align:center;font-size:30px;width:100%;"><a href="'.U('Qiandao/Danger/warning').'">不管，我就要清。</a></p>';
            
//         }
        
        
        
        
//     }
// }
?>
