<?php
/**
 * 
 * User(会员管理文件)
 *
 * @package      	YOURPHP
 * @author           <admin@duoyan.net>
 * @copyright     	Copyright (c) 2008-2011  (http://www.duoyan.net)
 * @license         http://www.duoyan.net/license.txt
 * @version        	duoyan企业网站管理系统 v2.1 2012-10-08 duoyan.net $
 */
if(!defined("Yourphp")) exit("Access Denied");
class UserAction extends AdminbaseAction {

    public $dao,$usergroup;
	function _initialize()
	{
		parent::_initialize();
		$this->dao = D('User');
		$this->usergroup=F('Role');
		$this->assign('usergroup',$this->usergroup);
		
		//区域和校区处理
		$area_obj = M('Area');
		$condition['parentid'] = 0;
		$area = $area_obj->where($condition)->select();
		$this->assign('area',$area);
	}


	function index(){
		import ( '@.ORG.Page' );

		$keyword=$_GET['keyword'];
		$searchtype=$_GET['searchtype'];
		$groupid =intval($_GET['groupid']);

		$this->assign($_GET);
		
		if(!empty($keyword) && !empty($searchtype)){
			$where[$searchtype]=array('like','%'.$keyword.'%');
		}
		if($groupid)$where['groupid']=$groupid;

		$user=$this->dao;
		$count=$user->where($where)->count();
		$page=new Page($count,20);
		$show=$page->show();
		$this->assign("page",$show);
		$list=$user->order('id')->where($where)
		->limit($page->firstRow.','.$page->listRows)->select();

		$this->assign('ulist',$list);
		$this->display();
	}

	function insert(){
		$user=$this->dao;
		$_POST['password'] = sysmd5($_POST['pwd']);
		if($data=$user->create()){
			if(false!==$user->add()){
				$uid=$user->getLastInsID();
				$ru['role_id']=$_POST['groupid'];
				$ru['user_id']=$uid;
				$roleuser=M('RoleUser');
				$roleuser->add($ru);			
				$this->success(L('add_ok'));
			}else{
				$this->error(L('add_error'));
			}
		}else{
			$this->error($user->getError());
		}
	}

	function update(){
		$user=$this->dao;
		$_POST['password'] = $_POST['pwd'] ? sysmd5($_POST['pwd']) : $_POST['opwd'];
		if(!empty($_POST['id'])){
				if(false!==$user->save($_POST)){
					$ru['user_id']=$_POST['id'];
					$ru['role_id']=$_POST['groupid'];
					$roleuser=M('RoleUser');
					$roleuser->where('user_id='.$_POST['id'])->delete();
					$roleuser->where('user_id='.$_POST['id'])->add($ru);
					$this->success(L('edit_ok'));
				}else{
					$this->error(L('edit_error').$user->getDbError());
				}
		}else{
				$this->error(L('do_error'));
		}
		 
	}
 

	function _before_add(){
		$this->assign('rlist',$this->usergroup);	
	}

	function _before_edit(){
		$this->assign('rlist',$this->usergroup);
		$this->assign('area_stauts',1);	
	}


	function delete(){
		$id=$_GET['id'];
		$user=$this->dao;
		if(false!==$user->delete($id)){
			$roleuser=M('RoleUser');
			$roleuser->where('user_id ='.$id)->delete();
			delattach(array('moduleid'=>0,'catid'=>0,'id'=>0,'userid'=>$id));
			$this->success(L('delete_ok'));
		}else{
			$this->error(L('delete_error').$user->getDbError());
		}
	}

	function deleteall(){		
		$ids=$_POST['ids'];
		if(!empty($ids) && is_array($ids)){
			$user=$this->dao;
			$id=implode(',',$ids);
			if(false!==$user->delete($id)){
				$roleuser=M('RoleUser');
				$roleuser->where('user_id in('.$id.')')->delete();
				delattach("moduleid=0 and catid=0 and id=0 and userid in($id)");
				$this->success(L('delete_ok'));
			}else{
				$this->error(L('delete_error'));
			}
		}else{
			$this->error(L('do_empty'));
		}
	}
	
	function ajax_school_info(){
		$typeid=$_GET['area_id'];
		if(empty($typeid)){
			exit;
		}
		$area_obj = M('Area');
		$condition['parentid'] = $typeid;
		$area = $area_obj->where($condition)->select();
		$this->ajaxReturn($area,"TRUE",1);
	}
}
?>