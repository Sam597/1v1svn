<?php

/**
 * 
 * IndexAction.class.php (前台首页)
 *
 * @package      	YOURPHP
 * @author           <admin@duoyan.net>
 * @copyright     	Copyright (c) 2008-2011  (http://www.duoyan.net)
 * @license         http://www.duoyan.net/license.txt
 * @version        	duoyan企业网站管理系统 v2.1 2011-03-01 duoyan.net $
 */
if (!defined("Yourphp"))
    exit("Access Denied");

class IndexAction extends BaseAction {

    public function index() {
        $siteid = SITEID;
        $site_config = M('Sitematerial');
        $site_info = $site_config->where('siteid=' . $siteid)->find();
        $this->site_info = $site_info;
        // 学习中心
        $Site = M('Site');
        $condition['siteid'] = $siteid;
        $condition['level'] = 2;
        $list_school = $Site->field('id,name')->where($condition)->order('listorder desc')->select();
        $Schoolinfo = M('Schoolinfo');
        $condition1['level'] = 3;
        foreach ($list_school as $key => $value) {
            $condition1['areaid'] = $value['id'];
            $level3 = $Site->field('areaid')->where($condition1)->select();
            foreach ($level3 as $v) {
                $condition2['areaid'] = $v['areaid'];
                $info = $Schoolinfo->field('title,thumb,url,phone')->where($condition2)->limit(4)->select();
                foreach ($info as $k1 => $v1) {
                    $list_school[$key]['school'][$k1]['title'] = $v1['title'];
                    $list_school[$key]['school'][$k1]['thumb'] = $v1['thumb'];
                    $list_school[$key]['school'][$k1]['url'] = $v1['url'];
                    $list_school[$key]['school'][$k1]['phone'] = $v1['phone'];
                }
            }
        }
        $this->list_school = $list_school;
        // 王牌咨询
        $this->seo_title = $this->index_title;

        $this->display();
    }

    //文章读取
    public function article($catid, $limit) {
        $art = M('Article');
        $cate = M('Category');
        if ($catid) {
            $catidarr = $cate->field('id')->where('parentid=' . $catid)->select();
            foreach ($catidarr as $v) {
                $catids[] = $v['id'];
            }
            if ($catids) {
                $catidstr = implode(',', $catids);
                $map['catid'] = array('in', $catidstr);
            } else {
                $map['catid'] = $catid;
            }
        }
        $map['siteid'] = SITEID;
        $articlelist = $art->field('title,catid,url')->where($map)->order('createtime desc')->limit(0, $limit)->select();
        foreach ($articlelist as $k => $v) {
            $articlelist[$k]['catname'] = $cate->getFieldById($v['catid'], 'catname');
        }
        return $articlelist;
    }

    //名师之队
    public function teachert($grade) {
        $tea = M('Mingshizhidui');
        $map['grade_id'] = $grade;
        $map['siteid'] = SITEID;
        $teacherlist = $tea->field('title,description,pic')->where($map)->order('listorder')->limit(0, 4)->select();
        return $teaacherlist;
    }

}
