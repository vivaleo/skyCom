<?php 
class userControl extends skymvc{
	
	public function __construct(){
		parent::__construct();
	}
	
	public function onDefault(){
		$url=APPADMIN."?m=user&a=default";
		$where=" 1=1 ";
		$userid=get('userid','i');
		if($userid){
			$where.=" AND userid=$userid";
			$url.="&userid=$userid";
		}
		
		$nickname=get('nickname','h');
		if($nickname){
			$where.=" AND nickname like '".$nickname."' ";
			$url.="&nickname=".urlencode($nickname);
		}
		$orderby=get('orderby','h');
		$orderby=$orderby?$orderby:"userid DESC";
		$option=array(
			"where"=>$where,
			"start"=>get('per_page','i'),
			"limit"=>20,
			"order"=>$orderby
		);
		$rscount=true;
		$data=M("user")->select($option,$rscount);
		$pagelist=$this->pagelist($rscount,20,$url);
		
		$this->loadConfig("user");
	 
		$this->smarty->assign(array(
			"data"=>$data,
			"rscount"=>$rscount,
			"pagelist"=>$pagelist,
			"user_type_list"=>$this->config_item('user_type_list'),
		));
		$this->smarty->display("user/index.html");
	}
	
	public function onAdd(){
		$userid=get('userid','i');
		$data=M("user")->selectRow(array("where"=>" userid=$userid"));
		$this->loadConfig("user");
		$this->smarty->assign(array(
			"data"=>$data,
			"user_type_list"=>$this->config_item('user_type_list'),
		));
		$this->smarty->display("user/add.html");
	}
	
	public function onSave(){
		$userid=get_post("userid","i");
		$data=M("user")->postData();
		$password=post('password','h');
		$password2=post('password2','h');
		unset($data['password']);
		if(!empty($password)){
			if(empty($password) || $password!=$password2){
				$this->goall("密码不一致",1);
			}
			$data['salt']=rand(1000,9999);
			$data['password']=umd5($password.$data['salt']);
		}
		if($userid){
			$user=M("user")->selectRow("userid=".$userid);
			 
			if($user['username']!=$data['username']){
				if(M("user")->selectRow("username='".$data['username']."'")){
					$this->goall("账号已经存在",1);
				}
			}
			
			if($user['nickname']!=$data['nickname']){
				if(M("user")->selectRow("nickname='".$data['nickname']."'")){
					$this->goall("昵称已经存在",1);
				}
			}
			
			if($user['telephone']!=$data['telephone']){
				if(M("user")->selectRow("telephone='".$data['telephone']."'")){
					$this->goall("手机已经存在",1);
				}
			}
			
			
			M("user")->update($data,array('userid'=>$userid));
		}else{
			 
			if(M("user")->selectRow("username='".$data['username']."'")){
					$this->goall("账号已经存在",1);
				}
			if(M("user")->selectRow("nickname='".$data['nickname']."'")){
					$this->goall("昵称已经存在",1);
				}
			if($data['telephone'] && M("user")->selectRow("telephone='".$data['telephone']."'")){
					$this->goall("手机已经存在",1);
				}
			if(empty($password) || $password!=$password2){
				$this->goall("密码不一致",1);
			}
			if(empty($data["user_head"])) unset($data["user_head"]);
			$data['email']=$email;
			
			M("user")->insert($data);
		}
		$this->goall("保存成功");
		
	}
	
	public function onAuth(){
		$url=APPADMIN."?m=user&a=auth";
		$where=" is_auth=3 ";
		$userid=get('userid','i');
		if($userid){
			$where.=" AND userid=$userid";
			$url.="&userid=$userid";
		}
		
		$nickname=get('nickname','h');
		if($nickname){
			$where.=" AND nickname like '".$nickname."' ";
			$url.="&nickname=".urlencode($nickname);
		}
		$orderby=get('orderby','h');
		$orderby=$orderby?$orderby:"is_auth DESC";
		$option=array(
			"where"=>$where,
			"start"=>get('per_page','i'),
			"limit"=>20,
			"order"=>$orderby
		);
		$rscount=true;
		$data=M("user")->select($option,$rscount);
		$pagelist=$this->pagelist($rscount,20,$url);
		
		$this->loadConfig("user");
	 
		$this->smarty->assign(array(
			"data"=>$data,
			"rscount"=>$rscount,
			"pagelist"=>$pagelist,
			"user_type_list"=>$this->config_item('user_type_list'),
		));
		$this->smarty->display("user/auth.html");
	}
	
	public function OnLogin(){
		$userid=get_post('userid','i');
		$_SESSION['ssuser']=M("user")->selectRow("userid=".$userid);
		$this->goall("切换成功",0,0,"/index.php");
	}
	/*导入用户*/
	public function onImport(){
		set_time_limit(0);
		$c=file_get_contents("user.txt");
		$data=explode("\r\n",$c);
		foreach($data as $k=>$v){
			if(!M("user")->selectRow("username='".$v."' or nickname='".$v."'")){
				M("user")->insert(array(
					"username"=>$v,
					"nickname"=>$v
				));
			}
		}
		echo "导入成功";
	}
	
	/*导出*/
	public function onExcel(){
		header("Content-type:application/vnd.ms-excel");
		header("Content-Disposition:filename=user.xls");
		$data=M("user")->select();
		ob_start();
		echo "用户\t邮箱\t手机\t地址\n";
		if($data){
			foreach($data as $v){
				echo $v['nickname']."\t";
				echo $v['email']."\t";
				echo $v['telephone']."\t";
				echo $v['address']."\t";
				echo "\n";
			}
		}
		$con=ob_get_contents();
		ob_end_clean();
		echo iconv("utf-8","gbk",$con);
	}
}

?>