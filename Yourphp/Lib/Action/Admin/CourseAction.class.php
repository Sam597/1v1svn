<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of SiteAction
 *
 * @author Robin
 */
class CourseAction extends AdminbaseAction {

    public function index() {
        $Course = M('Course');
        $courselist = $Course->order('listorder desc')->select();
        foreach ($courselist as $k => $v) {
            $Grade = M('Grade');
            $courselist[$k]['gradename'] = $Grade->getFieldById($v['grade_id'], 'name');
        }
        $this->assign('courselist', $courselist);
        $this->display();
    }

    public function add() {
        $Grade = M('Grade');
        $gradelist = $Grade->order('listorder desc')->select();
        $this->assign('gradelist', $gradelist);
        $this->display('add');
    }

    public function edit() {
        $Course = M('Course');
        $id = $_GET['id'];
        $info = $Course->find($id);
        $Grade = M('Grade');
        $gradelist = $Grade->order('listorder desc')->select();
        $this->assign('gradelist', $gradelist);
        $this->assign('course', $info);
        $this->display('edit');
    }

    public function courseinsert() {
        $Course = M('Course');
        $Course->create();
        if ($Course->add()) {
            $this->success('添加成功');
        } else {
            $this->error('添加失败');
        }
    }

    public function courseupdate() {
        $Course = M('Course');
        $data = $Course->create();
        if ($data) {
            $Course->save();
            $this->success('修改成功');
        } else {
            $this->error('修改失败');
        }
    }

    public function coursedelete() {
        $Course = M('Course');
        $id = $_GET['id'];
        if ($Course - delete($id)) {
            $this->success('删除成功');
        } else {
            $this->error('删除失败');
        }
    }

}

?>
