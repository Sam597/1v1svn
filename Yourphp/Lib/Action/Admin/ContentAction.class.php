<?php

/**
 *
 * Content(内容管理模块)
 *
 * @package      	YOURPHP
 * @author           <web@duoyan.net>
 * @copyright     	Copyright (c) 2008-2011  (http://www.duoyan.net)
 * @license         http://www.duoyan.net/license.txt
 * @version        	duoyan企业网站管理系统 v2.1 2012-10-08 duoyan.net $
 */
if (!defined("Yourphp"))
    exit("Access Denied");

class ContentAction extends AdminbaseAction {

    protected $dao, $fields;

    public function _initialize() {
        parent::_initialize();
        $module = $this->module[$this->moduleid]['name'];
        $this->dao = D($module);
        $fields = F($this->moduleid . '_Field');
        foreach ($fields as $key => $res) {
            $res['setup'] = string2array($res['setup']);
            $this->fields[$key] = $res;
        }
        unset($fields);
        unset($res);
        $this->groupid = $_SESSION['groupid'];
        $this->assign('fields', $this->fields);
    }

    /**
     * 列表
     *
     */
    public function index() {
        $template = file_exists(THEME_PATH . MODULE_NAME . '_index.html') ? MODULE_NAME . ':index' : 'Content:index';
        $this->_list(MODULE_NAME);
        $this->display($template);
    }

    public function add() {
        $vo['catid'] = intval($_GET['catid']);
        $form = new Form($vo);
        $pform = new Pform($vo);
        $form->isadmin = 1;
        $this->assign('form', $form);
        $this->assign('pform', $pform);
        $template = file_exists(THEME_PATH . MODULE_NAME . '_edit.html') ? MODULE_NAME . ':edit' : 'Content:edit';
        $this->display($template);
    }

    public function edit() {

        $id = $_REQUEST ['id'];
        if (MODULE_NAME == 'Page') {
            $Page = D('Page');
            $p = $Page->find($id);
            if (empty($p)) {
                $data['id'] = $id;
                $data['title'] = $this->categorys[$id]['catname'];
                $data['keywords'] = $this->categorys[$id]['keywords'];
                $Page->add($data);
            }
        }
        $vo = $this->dao->getById($id);
        $vo['content'] = htmlspecialchars($vo['content']);
        $form = new Form($vo);
        $pform = new Pform($vo);
        $this->assign('vo', $vo);
        $this->assign('form', $form);
        $this->assign('pform', $pform);
        $template = file_exists(THEME_PATH . MODULE_NAME . '_edit.html') ? MODULE_NAME . ':edit' : 'Content:edit';
        $this->display($template);
    }

    /**
     * 录入
     *
     */
    public function insert($module = '', $fields = array(), $userid = 0, $username = '', $groupid = 0) {
        $model = $module ? M($module) : $this->dao;
        $fields = $fields ? $fields : $this->fields;

        if ($fields['verifyCode']['status'] && (md5($_POST['verifyCode']) != $_SESSION['verify'])) {
            $this->assign('jumpUrl', 'javascript:history.go(-1);');
            $this->error(L('error_verify'));
        }
        $_POST = checkfield($fields, $_POST);
        if (empty($_POST))
            $this->error(L('do_empty'));

        $_POST['createtime'] = time();
        $_POST['updatetime'] = $_POST['createtime'];
        $_POST['userid'] = $module ? $userid : $_SESSION['userid'];
        $_POST['username'] = $module ? $username : $_SESSION['username'];
        //设置访问组
        $_POST['readgroup'] = is_array($_POST['readgroup']) ? implode(',', $_POST['readgroup']) : '';

        //校区标示
        $_POST['siteid'] = isset($_POST['siteid']) ? $_POST['siteid'] : $_SESSION['siteid'];
        $_POST['schoolid'] = isset($_POST['schoolid']) ? $_POST['schoolid'] : $_SESSION['schoolid'];
        $_POST['areaid'] = isset($_POST['areaid']) ? $_POST['areaid'] : $_SESSION['areaid'];

        if ($_POST['style_color'])
            $_POST['style_color'] = 'color:' . $_POST['style_color'];
        if ($_POST['style_bold'])
            $_POST['style_bold'] = ';font-weight:' . $_POST['style_bold'];
        if ($_POST['style_color'] || $_POST['style_bold'])
            $_POST['title_style'] = $_POST['style_color'] . $_POST['style_bold'];
        $module = $module ? $module : MODULE_NAME;
        if (GROUP_NAME == 'User' || 2 == $_SESSION['groupid'])
            $_POST['status'] = $this->Role[$groupid]['allowpostverify'] ? 1 : 0;

        if (false === $model->create()) {
            $this->error($model->getError());
        }
        $_POST['id'] = $id = $model->add();

        if ($id !== false) {
            $catid = $module == 'Page' ? $id : $_POST['catid'];



            if ($_POST['aid']) {
                $Attachment = M('Attachment');
                $aids = implode(',', $_POST['aid']);
                $data['id'] = $id;
                $data['catid'] = $catid;
                $data['status'] = '1';
                $Attachment->where("aid in (" . $aids . ")")->save($data);
            }

            $tablename = C('DB_PREFIX') . strtolower($module);
            $db = D('');
            $db = DB::getInstance();
            $tables = $db->getTables();
            $Fields = $db->getFields($tablename);

            if (isset($Fields['url'])) {
                $data = '';
                $cat = $this->categorys[$catid];
                $url = geturl($cat, $_POST, $this->Urlrule);
                $data['id'] = $id;
                $data['url'] = $url[0];
                $model->save($data);
            }


            if ($_POST['keywords'] && $module != 'Page') {
                $keywordsarr = explode(',', $_POST['keywords']);
                $i = 0;
                $tagsdata = M('Tags_data');
                $tagsdata->where("id=" . $id)->delete();
                foreach ((array) $keywordsarr as $tagname) {
                    if ($tagname) {
                        $tagidarr = $tagdatas = $where = array();
                        $where['name'] = array('eq', $tagname);
                        $where['moduleid'] = array('eq', $cat['moduleid']);
                        $tagid = M('Tags')->where($where)->field('id')->find();
                        $tagidarr['id'] = $id;
                        if ($tagid) {
                            $num = $tagsdata->where("tagid=" . $tagid[id])->count();
                            $tagdatas['num'] = $num + 1;
                            M('Tags')->where("id=" . $tagid[id])->save($tagdatas);
                            $tagidarr['tagid'] = $tagid['id'];
                        } else {
                            $tagdatas['moduleid'] = $cat['moduleid'];
                            $tagdatas['name'] = $tagname;
                            $tagdatas['slug'] = Pinyin($tagname);
                            $tagdatas['num'] = 1;
                            $tagdatas['lang'] = $_POST['lang'];
                            $tagdatas['module'] = $cat['module'];
                            $tagidarr['tagid'] = M('Tags')->add($tagdatas);
                        }
                        $i++;
                        $tagsdata->add($tagidarr);
                    }
                }
            }

            if ($cat['presentpoint']) {
                $user = M('User');
                if ($cat['presentpoint'] > 0)
                    $user->where("id=" . $_POST['userid'])->setInc('point', $cat['presentpoint']);
                if ($cat['presentpoint'] < 0)
                    $user->where("id=" . $_POST['userid'])->setDec('point', $cat['presentpoint']);
            }

            if ($cat['ishtml'] && $_POST['status']) {
                if ($module != 'Page' && $_POST['status'])
                    $this->create_show($id, $module);
                $arrparentid = array_filter(explode(',', $cat['arrparentid'] . ',' . $cat['id']));
                foreach ($arrparentid as $catid) {
                    if ($this->categorys[$catid]['ishtml'])
                        $this->clisthtml($catid);
                }
            }
            if ($this->sysConfig['HOME_ISHTML'])
                $this->create_index();
            if (GROUP_NAME == 'Admin') {
                $this->assign('jumpUrl', U($module . '/index'));
            } elseif (GROUP_NAME == 'User') {
                $this->assign('jumpUrl', $_SERVER['HTTP_REFERER']);
                //$this->assign ( 'jumpUrl', U(GROUP_NAME.'-'.MODULE_NAME.'/add?moduleid='.$cat['moduleid']) );
            }
            $this->success(L('add_ok'));
        } else {
            $this->error(L('add_error') . ': ' . $model->getDbError());
        }
    }

    function update($module = '', $fields = array(), $userid = 0, $username = '') {
        $model = $module ? M($module) : $this->dao;
        $fields = $fields ? $fields : $this->fields;
        if ($fields['verifyCode']['status'] && (md5($_POST['verifyCode']) != $_SESSION['verify'])) {
            $this->assign('jumpUrl', 'javascript:history.go(-1);');
            $this->error(L('error_verify'));
        }
        $_POST = checkfield($fields, $_POST);
        if (empty($_POST))
            $this->error(L('do_empty'));

        $_POST['updatetime'] = time();
        if ($_POST['style_color'])
            $_POST['style_color'] = 'color:' . $_POST['style_color'];
        if ($_POST['style_bold'])
            $_POST['style_bold'] = ';font-weight:' . $_POST['style_bold'];
        if ($_POST['style_color'] || $_POST['style_bold'])
            $_POST['title_style'] = $_POST['style_color'] . $_POST['style_bold'];

        $cat = $this->categorys[$_POST['catid']];

        $module = $module ? $module : MODULE_NAME;
        if ('Teacher' == $module) {
            $schoolids = explode(',', $_POST["school_ids"]);
            $Schoolinfo = M('Schoolinfo');
            $cond['id']=array('in', $schoolids);
            $areaids = $Schoolinfo->where($cond)->getField('areaid',true);
            $_POST['area_ids'] = implode(',', $areaids);
        }
        $_POST['url'] = geturl($cat, $_POST, $this->Urlrule);
        $_POST['url'] = $_POST['url'][0];

        $olddata = $model->find($_POST['id']);
        if (false === $model->create()) {
            $this->error($model->getError());
        }
        if (GROUP_NAME == 'User' || 2 == $_SESSION['groupid'])
            $_POST['status'] = $this->Role[$groupid]['allowpostverify'] ? 1 : 0;

        // 更新数据
        $list = $model->save();

        if (false !== $list) {
            $id = $_POST['id'];
            $catid = $module == 'Page' ? $id : $_POST['catid'];

            if ($olddata['keywords'] != $_POST['keywords'] && $module != 'Page') {


                $tagidarr = $tagdatas = $where = array();
                $where['name'] = array('in', $olddata['keywords']);
                $where['moduleid'] = array('eq', $cat['moduleid']);
                $where['lang'] = array('eq', $_POST['lang']);
                M('Tags')->where($where)->setDec('num');

                $tagsdata = M('Tags_data');
                $tagsdata->where("id=" . $id)->delete();

                $keywordsarr = explode(',', $_POST['keywords']);
                foreach ((array) $keywordsarr as $tagname) {
                    if ($tagname) {
                        $tagidarr = $tagdatas = $where = array();
                        $where['name'] = array('eq', $tagname);
                        $where['moduleid'] = array('eq', $cat['moduleid']);
                        $where['lang'] = array('eq', $_POST['lang']);
                        $tagid = M('Tags')->where($where)->field('id')->find();
                        $tagidarr['id'] = $id;
                        if ($tagid['id'] > 0) {
                            M('Tags')->where("id=" . $tagid[id])->setInc('num');
                            ;
                            $tagidarr['tagid'] = $tagid['id'];
                        } else {
                            $tagdatas['moduleid'] = $cat['moduleid'];
                            $tagdatas['name'] = $tagname;
                            $tagdatas['slug'] = Pinyin($tagname);
                            $tagdatas['num'] = 1;
                            $tagdatas['lang'] = $_POST['lang'];
                            $tagdatas['module'] = $cat['module'];
                            $tagidarr['tagid'] = M('Tags')->add($tagdatas);
                        }
                        $tagsdata->add($tagidarr);
                    }
                }
                M('Tags')->where('num<=0')->delete();
            }

            if ($_POST['aid']) {
                $Attachment = M('Attachment');
                $aids = implode(',', $_POST['aid']);
                $data['id'] = $id;
                $data['catid'] = $catid;
                $data['status'] = '1';
                $Attachment->where("aid in (" . $aids . ")")->save($data);
            }
            $cat = $this->categorys[$catid];
            if ($cat['ishtml']) {
                if ($module != 'Page' && $_POST['status'])
                    $this->create_show($_POST['id'], $module);
                $arrparentid = array_filter(explode(',', $cat['arrparentid'] . ',' . $cat['id']));
                foreach ($arrparentid as $catid) {
                    if ($this->categorys[$catid]['ishtml'])
                        $this->clisthtml($catid);
                }
            }
            if ($this->sysConfig['HOME_ISHTML'])
                $this->create_index();
            $this->assign('jumpUrl', $_POST['forward']);
            $this->success(L('edit_ok'));
        } else {
            //错误提示
            $this->success(L('edit_error') . ': ' . $model->getDbError());
        }
    }

    function statusallok() {

        $module = MODULE_NAME;
        $model = M($module);
        $ids = $_POST['ids'];
        if (!empty($ids) && is_array($ids)) {
            $id = implode(',', $ids);
            $data = $model->select($id);
            if ($data) {
                foreach ($data as $key => $r) {
                    $model->save(array(id => $r['id'], status => 1));
                    if ($this->categorys[$r['catid']]['ishtml'] && $r['status'])
                        $this->create_show($r['id'], $module);
                }
                $cat = $this->categorys[$r['catid']];
                if ($cat['ishtml']) {
                    if ($this->sysConfig['HOME_ISHTML'])
                        $this->create_index();
                    $arrparentid = array_filter(explode(',', $cat['arrparentid'] . ',' . $cat['id']));
                    foreach ($arrparentid as $catid) {
                        if ($this->categorys[$catid]['ishtml'])
                            $this->clisthtml($catid);
                    }
                }
                $this->success(L('do_ok'));
            }else {
                $this->error(L('do_error') . ': ' . $model->getDbError());
            }
        } else {
            $this->error(L('do_empty'));
        }
    }

    /* 状态 */

    public function status() {
        $module = MODULE_NAME;
        $model = D($module);
        if ($model->save($_GET)) {
            $_POST = '';
            $_POST = $model->find($_GET['id']);
            $cat = $this->categorys[$_POST['catid']];
            if ($cat['ishtml']) {
                if ($module != 'Page' && $_POST['status'])
                    $this->create_show($_POST['id'], $module);
                if ($this->sysConfig['HOME_ISHTML'])
                    $this->create_index();
                $arrparentid = array_filter(explode(',', $cat['arrparentid'] . ',' . $cat['id']));
                foreach ($arrparentid as $catid) {
                    if ($this->categorys[$catid]['ishtml'])
                        $this->clisthtml($catid);
                }
            }

            $this->success(L('do_ok'));
        }else {
            $this->error(L('do_error'));
        }
    }

    public function teacherselect() {
        if ($_REQUEST['keyword']) {
            $map['title'] = array('like', '%' . $_REQUEST['keyword'] . '%');
            $mapage['title'] = $_REQUEST['keyword'];
        }else
            $map = '1';
        if ($_REQUEST['title']) {
            $map = '';
            $map['title'] = array('like', '%' . $_REQUEST['title'] . '%');
            $mapage['title'] = $_REQUEST['title'];
        }

        $model = M('Teacher');
        //取得满足条件的记录总数
        $count = $model->where($map)->count();
        import("@.ORG.Page");
        $page = new Page($count, 15);
        //分页查询数据

        $voList = $model->field('id,title')->where($map)->limit($page->firstRow . ',' . $page->listRows)->select();

        $mapage[C('VAR_PAGE')] = '{$page}';
        $page->urlrule = U('Content/teacherselect', $mapage);
        //分页显示
        $show = $page->show();

        //模板赋值显示
        $this->assign('list', $voList);
        $this->assign('page', $show);

        $this->display();
    }

    public function get_teachername() {
        if ($_REQUEST['tid']) {
            $model = M('Teacher');
            $tname = $model->getFieldById($_REQUEST['tid'], 'title');
            $this->ajaxReturn('ok', $tname, '');
        }else
            $this->ajaxReturn('error', '', '');
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