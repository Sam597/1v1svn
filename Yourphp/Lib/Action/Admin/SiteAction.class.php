<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of SiteAction
 *
 * @author BO
 */
class SiteAction extends AdminbaseAction {

    public function index() {
        $SITE = M('Site');
        $sitelist = $SITE->where('level=1')->order('listorder desc')->select();
        $this->assign('sitelist', $sitelist);
        $this->display();
    }

    public function add() {
        $this->display('edit');
    }

    public function edit() {
        $SITE = M('Site');
        $id = $_GET['id'];
        $siteinfo = $SITE->find($id);
        $this->assign('vo', $siteinfo);
        $this->display('edit');
    }

    public function siteinsert() {
        $SITE = M('Site');
        $sitedata = $SITE->create();
        if ($sitedata) {
            $SITE->level = 1;
            $SITE->add();
            $lastid = $SITE->getLastInsID();
            $SITE->where('id=' . $lastid)->setField('siteid', $lastid);
            $this->success('添加成功');
        } else {
            $this->error('添加失败');
        }
    }

    public function siteupdate() {
        $SITE = M('Site');
        $sitedata = $SITE->create();
        if ($sitedata) {
            $SITE->save();
            $this->success('修改成功');
        } else {
            $this->error('修改失败');
        }
    }

    public function arealist() {
        $SITE = M('Site');
        $siteid = $_GET['siteid'];
        $sitelist = $SITE->where('level=2 and siteid=' . $siteid)->order('listorder desc')->select();
        $this->assign('sitelist', $sitelist);
        $this->display('arealist');
    }

    public function areaadd() {
        $siteid = $_GET['siteid'];
        $this->assign('siteid', $siteid); //添加
        $this->display('areaedit');
    }

    public function areaedit() {
        $SITE = M('Site');
        $id = $_GET['id'];
        $areainfo = $SITE->find($id);
        $this->assign('vo', $areainfo); //修改
        $this->assign('siteid', $areainfo['siteid']); //添加
        $this->display('areaedit');
    }

    public function areainsert() {
        $SITE = M('Site');
        $sitedata = $SITE->create();
        if ($sitedata) {
            $SITE->level = 2;
            $SITE->add();
            $lastid = $SITE->getLastInsID();
            $SITE->where('id=' . $lastid)->setField('areaid', $lastid);
            $this->success('添加成功');
        } else {
            $this->error('添加失败');
        }
    }

    public function areaupdate() {
        $SITE = M('Site');
        $sitedata = $SITE->create();
        if ($sitedata) {
            $SITE->save();
            $this->success('修改成功');
        } else {
            $this->error('修改失败');
        }
    }

    public function areadelete() {
        $SITE = M('Site');
        $id = $_GET['id'];
        $areainfo = $SITE->delete($id);
        $this->success('删除成功');

    }

    public function schoollist() {
        $SITE = M('Site');
        $areaid = $_GET['areaid'];
        $sitelist = $SITE->where('level=3 and areaid=' . $areaid)->order('listorder desc')->select();
        $this->assign('sitelist', $sitelist);
        $this->display('schoollist');
    }

    public function schooladd() {
        $siteid = $_GET['siteid'];
        $areaid = $_GET['areaid'];
        $this->assign('siteid', $siteid); //添加
        $this->assign('areaid', $areaid); //添加
        $this->display('schooledit');
    }

    public function schooledit() {
        $SITE = M('Site');
        $id = $_GET['id'];
        $schoolinfo = $SITE->find($id);
        $this->assign('vo', $schoolinfo); //修改
        $this->assign('siteid', $schoolinfo['siteid']); //添加
        $this->assign('areaid', $schoolinfo['areaid']); //添加
        $this->display('schooledit');
    }

    public function schoolinsert() {
        $SITE = M('Site');
        $sitedata = $SITE->create();
        if ($sitedata) {
            $SITE->level = 3;
            $SITE->add();
            $lastid = $SITE->getLastInsID();
            $SITE->where('id=' . $lastid)->setField('schoolid', $lastid);
            $this->success('添加成功');
        } else {
            $this->error('添加失败');
        }
    }

    public function schoolupdate() {
        $SITE = M('Site');
        $sitedata = $SITE->create();
        if ($sitedata) {
            $SITE->save();
            $this->success('修改成功');
        } else {
            $this->error('修改失败');
        }
    }

    //获取站点
    public function get_site() {
        $siteid = $this->_request('siteid');
        $areaid = $this->_request('areaid');
        $schoolid = $this->_request('schoolid');
        $area = M('Site');
        if (!$siteid && !$areaid && !$schoolid) {
            $map['level'] = 1;
        }
        if ($siteid) {
            $map['siteid'] = $siteid;
            $map['level'] = 2;
        }
        if ($areaid) {
            $map['areaid'] = $areaid;
            $map['level'] = 3;
        }
        if ($schoolid) {
            $map['schoolid'] = $schoolid;
        }
        if ($schoolid) {
            $r = $area->where($map)->find();
        } else {
            $r = $area->where($map)->order('listorder desc')->select();
        }
        if ($r)
            $this->ajaxReturn('ok', $r, 'ok');
        else
            $this->ajaxReturn('error', $area->getLastSql(), 'error');
    }

    Public function get_school() {
        $siteid = $this->_request('siteid');
        $areaid = $this->_request('areaid');
        $schoolid = $this->_request('schoolid');
        $levelid = $this->_request('levelid');
        $area = M('Site');
        if ($siteid && $levelid) {
            $map['siteid'] = $siteid;
            $map['level'] = 3;
        }
        $r = $area->where($map)->order('listorder desc')->select();
        if ($r)
            $this->ajaxReturn('ok', $r, 'ok');
        else
            $this->ajaxReturn('error', $area->getLastSql(), 'error');
    }

    //查询站点
    public function get_sitename() {
        $siteid = $this->_request('siteid');
        $area = M('Site');
        $r = $area->where(array('level' => 1, 'siteid' => $siteid))->find();
        if ($r)
            $this->ajaxReturn('ok', $r, 'ok');
        else
            $this->ajaxReturn('error', $area->getLastSql(), 'error');
    }

//获取层级
    public function get_level() {
        $level = M('Level');
        $r = $level->order('listorder')->select();
        if ($r)
            $this->ajaxReturn('ok', $r, 'ok');
        else
            $this->ajaxReturn('error', '', 'error');
    }

    //获取学期
    public function get_term() {
        $term = M('Term');
        $r = $term->order('listorder')->select();
        if ($r)
            $this->ajaxReturn('ok', $r, 'ok');
        else
            $this->ajaxReturn('error', '', 'error');
    }

    //获取年级
    public function get_grade() {
        $gradeid = $this->_request('grade_id');
        $areaid = $this->_request('id');
        $grade = M('Grade');

        $r = $grade->order('listorder')->select();
        if ($r)
            $this->ajaxReturn('ok', $r, 'ok');
        else
            $this->ajaxReturn('error', '', 'error');
    }

    //获取科目
    public function get_course() {
        $grade_id = $this->_request('grade_id');
        $course = M('Course');
        $r = $course->where('grade_id=' . $grade_id)->order('listorder')->select();
        if ($r)
            $this->ajaxReturn('ok', $r, 'ok');
        else
            $this->ajaxReturn('error', '', 'error');
    }

    //获取年级科目
    public function get_gc() {
        $cid = $this->_request('cid');
        $course = M('Course');
        $grade = M('Grade');
        $r = $course->field('grade_id,name')->find($cid);
        $r['gname'] = $grade->getFieldById($r['grade_id'], 'name');
        if ($r)
            $this->ajaxReturn('ok', $r, 'ok');
        else
            $this->ajaxReturn('error', '', 'error');
    }

}

?>
