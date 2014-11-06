<?php

/**
 * 
 * Schoolrel (校课管理)
 * 
 */
if (!defined("Yourphp"))
    exit("Access Denied");

class SchoolrelAction extends AdminbaseAction {

    protected $dao, $Type;

    function _initialize() {
        parent::_initialize();
        $this->dao = M(MODULE_NAME);
        $this->Type = F('Schoolrel');
    }

    public function _before_index() {
        if ($_SESSION['siteid']) {
            $area_obj = M('Area');
            $condition['typeid'] = $_SESSION['siteid'];
            $condition['parentid'] = 0;
            $sitename = $area_obj->where($condition)->field('name')->select();
            $this->assign('sitename', $sitename[0]['name']);
            $condition1['typeid'] = $_SESSION['schoolid'];
            $typename = $area_obj->where($condition1)->field('name')->select();
            $this->assign('schoolname', $typename[0]['name']);
        }
    }

    public function _before_add() {

        $parentid = intval($_GET['siteid']);
        $keyid = intval($_GET['schoolid']);
        $array = array();
        //区域和校区加载
        if ($_SESSION['siteid']) {
            $area_obj = M('Area');
            $condition['typeid'] = $_SESSION['siteid'];
            $condition['parentid'] = 0;
            $sitename = $area_obj->where($condition)->field('name')->select();
            $this->assign('sitename', $sitename[0]['name']);
            $condition1['typeid'] = $_SESSION['schoolid'];
            $typename = $area_obj->where($condition1)->field('name')->select();
            $this->assign('schoolname', $typename[0]['name']);
        }
        $this->assign('siteid', $_SESSION['siteid']);
        $this->assign('schoolid', $_SESSION['schoolid']);

        //班级
        $grade_obj = M('Grade');
        $grade = $grade_obj->select();
        $this->assign('grades', $grade);

        //学期
        $term_obj = M('Term');
        $term = $term_obj->select();
        $this->assign('terms', $term);

        //学期
        $course_obj = M('Course');
        $course = $course_obj->field('name')->select();
        $this->assign('courses', $course);

        $this->assign('username', $_SESSION['username']);
    }

    public function _before_edit() {
        $id = intval($_GET['id']);
        $this->assign('id', $id);
        $schoolrel_obj = M('Schoolrel');
        $condition['id'] = $id;
        $schoolrel = $schoolrel_obj->where($condition)->select();
        $this->assign('schoolrel', $schoolrel[0]);
        $this->assign('course_checked', explode(',', $schoolrel[0]['course']));
        $this->assign('siteid', $_SESSION['siteid']);
        $this->assign('schoolid', $_SESSION['schoolid']);

        //区域和校区加载
        if ($_SESSION['siteid']) {
            $area_obj = M('Area');
            $condition['typeid'] = $_SESSION['siteid'];
            $condition['parentid'] = 0;
            $sitename = $area_obj->where($condition)->field('name')->select();
            $this->assign('sitename', $sitename[0]['name']);
            $condition1['typeid'] = $_SESSION['schoolid'];
            $typename = $area_obj->where($condition1)->field('name')->select();
            $this->assign('typename', $typename[0]['name']);
        }

        //班级
        $grade_obj = M('Grade');
        $grade = $grade_obj->where()->select();
        $this->assign('grades', $grade);

        //学期
        $term_obj = M('Term');
        $term = $term_obj->where()->select();
        $this->assign('terms', $term);

        //科目
        $course_obj = M('Course');
        $course = $course_obj->select();
        $this->assign('courses', $course);

        $this->assign('username', $_SESSION['username']);
    }

    public function insert() {
        $_POST['status'] = 1;
        $name = MODULE_NAME;
        $model = D($name);
        $data['username'] = $_POST['username'];
        $data['siteid'] = $_POST['siteid'];
        $data['schoolid'] = $_POST['schoolid'];
        $data['sitename'] = $_POST['sitename'];
        $data['schoolname'] = $_POST['schoolname'];
        $data['grade'] = $_POST['grade'];
        $data['term'] = $_POST['term'];
        if ($_POST['course']) {
            $course = implode(',', $_POST['course']);
        }
        $data['course'] = $course;
        $typeid = $model->add($data);
        if ($typeid) {
            $this->assign('jumpUrl', U(MODULE_NAME . '/index'));
            $this->success(L('add_ok'));
        } else {
            $this->error(L('add_error') . ': ' . $model->getDbError());
        }
    }


}

?>