<?php

/**
 * 
 * SearchAction.class.php (前台搜索功能)
 *
 * @package      	YOURPHP
 * @author           <admin@duoyan.net>
 * @copyright     	Copyright (c) 2008-2011  (http://www.duoyan.net)
 * @license         http://www.duoyan.net/license.txt
 * @version        	duoyan企业网站管理系统 v2.1 2011-03-01 duoyan.net $
 */
if (!defined("Yourphp"))
    exit("Access Denied");

class SearchAction extends BaseAction {

    function _initialize() {
        parent::_initialize();
    }

    public function index() {
        $map['title'] = array('like', '%' . $_REQUEST['search'] . '%');
        $Article = M('Article');
        $count = $Article->where($map)->count();
        $Teacher = M('Teacher');
        $count1 = $Teacher->where($map)->count();
        $Classproject = M('Classproject');
        $count2 = $Classproject->where($map)->count();
        $totalrows = $count + $count1 + $count2;
        if ($totalrows) {
            import("@.ORG.FPage");
            $listRows = 20;
            $page = new Page($totalrows, $listRows);
            //$_REQUEST['p'] = '{$page}';
            //$page->urlrule = U('Search/index?search=' . $_REQUEST['search'] . '&p=' . $_REQUEST['p']);
            $pages = $page->show();
            $list = $Article->field('id,title,url,updatetime')->where($map)->order('createtime desc,id desc')->limit($page->firstRow . ',' . $page->listRows)->select();
            $list1 = $Teacher->field('id,title,url,updatetime')->where($map)->order('createtime desc,id desc')->limit($page->firstRow . ',' . $page->listRows)->select();
            $list2 = $Classproject->field('id,title,url,updatetime')->where($map)->order('createtime desc,id desc')->limit($page->firstRow . ',' . $page->listRows)->select();
            $lists = $list ? $list : array();
            $lists1 = $list1 ? $list1 : array();
            $lists2 = $list2 ? $list2 : array();
            $this->list = array_merge($lists, $lists1, $lists2);
            $this->pages = $pages;
        }
        $this->display();
        echo '<div style="display:none">'.$_REQUEST['search'].'</div>';
    }

//    public function index() {
//        //搜索
//        $_REQUEST['id'] = $catid = intval($_REQUEST['id']);
//        $p = max(intval($_REQUEST[C('VAR_PAGE')]), 1);
//        $_REQUEST['keyword'] = $keyword = get_safe_replace($_REQUEST['keyword']);
//        $_REQUEST['module'] = $module = get_safe_replace($_REQUEST['module']);
//        $module = $module ? $module : 'Article';
//        $this->assign($_REQUEST);
//        $this->assign('bcid', 0);
//        $where = " status=1 ";
//
//
//
//        if (APP_LANG) {
//            $lang = LANG_NAME;
//            $langid = LANG_ID;
//            $where .=" and lang= $langid";
//            $this->assign('lang', $lang);
//            $this->assign('langid', $langid);
//        }
//
//        if ($catid) {
//            $cat = $this->categorys[$catid];
//            $bcid = explode(",", $cat['arrparentid']);
//            $bcid = $bcid[1];
//            if ($bcid == '')
//                $bcid = intval($catid);
//            if (empty($module))
//                $module = $cat['module'];
//            unset($cat['id']);
//            $this->assign($cat);
//            $cat['id'] = $catid;
//            $this->assign('catid', $catid);
//            $this->assign('bcid', $bcid);
//
//
//            if ($cat['child']) {
//                $where .= " and catid in(" . $cat['arrchildid'] . ")";
//            } else {
//                $where .= " and catid=" . $catid;
//            }
//        }
//        $seo_title = $cat['title'] ? $cat['title'] : $cat['catname'];
//        $this->assign('seo_title', $keyword . ' ' . $seo_title);
//        $this->assign('seo_keywords', $keyword . $cat['keywords']);
//        $this->assign('seo_description', $keyword . $cat['description']);
//
//
//
//        if ($keyword) {
//
//            if (strstr($keyword, 'or')) {
//                $keydo = ' or ';
//                $keyword_arr = explode('or', $keyword);
//            } elseif (strstr($keyword, ' ')) {
//                $keydo = ' AND ';
//                $keyword_arr = explode(' ', $keyword);
//            }
//
//            if (count($keyword_arr) > 1) {
//                foreach ($keyword_arr as $key => $keywordz) {
//                    $keyword_arr[$key] = ' title like "%' . trim($keywordz) . '%" ';
//                }
//                $where .= ' AND (' . implode($keydo, $keyword_arr) . ')';
//            } else {
//                $where .= ' AND title like "%' . $keyword . '%" ';
//            }
//        }
//        $this->dao = M($module);
//        $count = $this->dao->where($where)->count();
//        $this->assign('count', $count);
//
//        if ($count) {
//            import("@.ORG.Page");
//            $listRows = !empty($cat['pagesize']) ? $cat['pagesize'] : C('PAGE_LISTROWS');
//            $page = new Page($count, $listRows);
//            $_REQUEST['p'] = '{$page}';
//            $page->urlrule = URL('Home-Search/index', $_REQUEST);
//            $pages = $page->show();
//            $field = $this->module[$cat['moduleid']]['listfields'];
//            $field = $field ? $field : 'id,catid,userid,url,username,title,title_style,keywords,description,thumb,createtime,hits';
//            $list = $this->dao->field($field)->where($where)->order('id desc')->limit($page->firstRow . ',' . $page->listRows)->select();
//            $this->assign('pages', $pages);
//            $this->assign('list', $list);
//        }
//
//        $this->display();
//    }
}

