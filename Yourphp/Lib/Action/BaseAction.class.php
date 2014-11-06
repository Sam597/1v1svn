<?php

/**
 * 
 * Base (前台公共模块)
 *
 * @package      	YOURPHP
 * @author           <admin@duoyan.net>
 * @copyright     	Copyright (c) 2008-2011  (http://www.duoyan.net)
 * @license         http://www.duoyan.net/license.txt
 * @version        	duoyan企业网站管理系统 v2.1 2011-03-01 duoyan.net $
 */
if (!defined("Yourphp"))
    exit("Access Denied");

class BaseAction extends Action {

    protected $Config, $sysConfig, $categorys, $module, $moduleid, $mod, $dao, $Type, $Role, $_userid, $_groupid, $_email, $_username, $forward, $user_menu, $Lang, $member_config;

    public function _initialize() {

        $this->sysConfig = F('sys.config');
        $this->module = F('Module');
        $this->Role = F('Role');
        $this->Type = F('Type');
        $this->mod = F('Mod');
        $this->moduleid = $this->mod[MODULE_NAME];
        if (APP_LANG) {
            $this->Lang = F('Lang');
            $this->assign('Lang', $this->Lang);
            if (get_safe_replace($_GET['l'])) {
                if (!$this->Lang[$_GET['l']]['status'])
                    $this->error(L('NO_LANG'));
                $lang = $_GET['l'];
            }else {
                $lang = $this->sysConfig['DEFAULT_LANG'];
            }
            define('LANG_NAME', $lang);
            define('LANG_ID', $this->Lang[$lang]['id']);
            define('SITEID', get_domainid());

            $this->categorys = F('Category_' . $lang);
            $this->Config = F('Config_' . $lang);
            $this->assign('l', $lang);
            $this->assign('langid', LANG_ID);
            $T = F('config_' . $lang, '', APP_PATH . 'Tpl/Home/' . $this->sysConfig['DEFAULT_THEME'] . '/');
            C('TMPL_CACHFILE_SUFFIX', '_' . $lang . '.php');
            cookie('think_language', $lang);
        } else {
            $T = F('config_' . $this->sysConfig['DEFAULT_LANG'], '', APP_PATH . 'Tpl/Home/' . $this->sysConfig['DEFAULT_THEME'] . '/');
            $this->categorys = F('Category');
            $this->Config = F('Config');
            cookie('think_language', $this->sysConfig['DEFAULT_LANG']);
        }
        $this->assign('T', $T);
        $this->assign($this->Config);
        $this->assign('Role', $this->Role);
        $this->assign('Type', $this->Type);
        $this->assign('Module', $this->module);
        $this->assign('Categorys', $this->categorys);
        import("@.ORG.Form");
        $this->assign('form', new Form());

        C('HOME_ISHTML', $this->sysConfig['HOME_ISHTML']);
        C('PAGE_LISTROWS', $this->sysConfig['PAGE_LISTROWS']);
        C('URL_M', $this->sysConfig['URL_MODEL']);
        C('URL_M_PATHINFO_DEPR', $this->sysConfig['URL_PATHINFO_DEPR']);
        C('URL_M_HTML_SUFFIX', $this->sysConfig['URL_HTML_SUFFIX']);
        C('URL_LANG', $this->sysConfig['DEFAULT_LANG']);
        C('DEFAULT_THEME_NAME', $this->sysConfig['DEFAULT_THEME']);


        import("@.ORG.Online");
        $session = new Online();
        if (cookie('auth')) {
            $yourphp_auth_key = sysmd5($this->sysConfig['ADMIN_ACCESS'] . $_SERVER['HTTP_USER_AGENT']);
            list($userid, $groupid, $password) = explode("-", authcode(cookie('auth'), 'DECODE', $yourphp_auth_key));
            $this->_userid = $userid;
            $this->_username = cookie('username');
            $this->_groupid = $groupid;
            $this->_email = cookie('email');
        } else {
            $this->_groupid = cookie('groupid') ? cookie('groupid') : 4;
            $this->_userid = 0;
        }


        foreach ((array) $this->module as $r) {
            if ($r['issearch'])
                $search_module[$r['name']] = L($r['name']);
            if ($r['ispost'] && (in_array($this->_groupid, explode(',', $r['postgroup']))))
                $this->user_menu[$r['id']] = $r;
        }
        if (GROUP_NAME == 'User') {
            $langext = $lang ? '_' . $lang : '';
            $this->member_config = F('member.config' . $langext);
            $this->assign('member_config', $this->member_config);
            $this->assign('user_menu', $this->user_menu);
            if ($this->_groupid == '5' && MODULE_NAME != 'Login') {
                $this->assign('jumpUrl', URL('User-Login/emailcheck'));
                $this->assign('waitSecond', 3);
                $this->success(L('no_regcheckemail'));
                exit;
            }
            $this->assign('header', TMPL_PATH . 'Home/' . THEME_NAME . '/Home_header.html');
        }
        if ($_GET['forward'] || $_POST['forward']) {
            $this->forward = get_safe_replace($_GET['forward'] . $_POST['forward']);
        } else {
            if (MODULE_NAME != 'Register' || MODULE_NAME != 'Login')
                $this->forward = isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : $this->Config['site_url'];
        }
        $this->assign('forward', $this->forward);

        $this->assign('search_module', $search_module);
        $this->assign('module_name', MODULE_NAME);
        $this->assign('action_name', ACTION_NAME);
        $this->assign('mysiteid', SITEID);
        $site_config = M('Sitematerial');
        $site_config_info = $site_config->field('phone_html,index_title,othersite')->where(array('siteid'=>SITEID))->find();
//        $top_phone = $site_config->getFieldbysiteid(SITEID, 'phone_html');
        $map_center = $site_config->getFieldbysiteid(SITEID, 'mapcenter');
        $this->top_phone = $site_config_info['phone_html'];
        $this->index_title = $site_config_info['index_title'];
        $this->top_site = $site_config_info['othersite'];
        $this->map_center = $map_center;
    }

    public function index($catid = '', $module = '') {
        $this->Urlrule = F('Urlrule');
        if (empty($catid))
            $catid = intval($_REQUEST['id']);
        //精彩瞬间
        if (44 == $catid) {
            $jingcai = M('Jingcaishunjian');
            $jc_info = $jingcai->field('*,title as btitle')->where('siteid=' . SITEID . ' and catid=44')->order('listorder desc')->find();
            $this->assign($jc_info);
//            redirect($jc_info[0]['url']);
        }
        //核心服务
        if (20 == $catid) {
            $article = M('Article');
            $hx_info = $article->field('*,title as btitle')->where('siteid=' . SITEID . ' and catid=20')->order('listorder desc')->find();
            $this->assign($hx_info);
        }
        //关于我们
        if (8 == $catid) {
            $article = M('Article');
            $gy_info = $article->field('*,title as btitle')->where('siteid=' . SITEID . ' and catid=8')->order('listorder desc')->find();
            $this->assign($gy_info);
//            redirect($gy_info[0]['url']);
        }
        if (56 == $catid) {
            $article = M('Article');
            $gy_info = $article->field('*,title as btitle')->where('siteid=' . SITEID . ' and catid=56')->order('listorder desc')->find();
            $this->assign($gy_info);
//            redirect($gy_info[0]['url']);
        }
        if (57 == $catid) {
            $article = M('Article');
            $gy_info = $article->field('*,title as btitle')->where('siteid=' . SITEID . ' and catid=57')->order('listorder desc')->find();
            $this->assign($gy_info);
//            redirect($gy_info[0]['url']);
        }
        //新闻中心
//        if (1 == $catid) {
//            redirect('/hangye/');
//        }
//        //精彩专题
//        if (36 == $catid) {
//            redirect('/jujiaogaokao/');
//        }
        $p = max(intval($_REQUEST[C('VAR_PAGE')]), 1);
        if ($catid) {
            //成功案例,映射光荣榜单数据
            if (24 == $catid) {
                $cat['listtype'] = 0;
                $catid = 60;
            }
            $cat = $this->categorys[$catid];
            //校区分部，映射总览页面
            if (34 == $catid && !$_REQUEST['search']) {
                $cat['listtype'] = 1;
                $article = M('Schoolinfo');
                $xq_info = $article->field('*,title as btitle')->where('siteid=' . SITEID . ' and catid=34')->order('listorder desc')->find();
                $this->assign($xq_info);
                $xq_infos = $article->field('title,phone,address,point')->where('siteid=' . SITEID . ' and catid=34 and point<>""')->order('listorder desc')->select();
                $this->assign('xq_info', json_encode($xq_infos));

                $Site = M('Site');
                $list_xq_infos = $Site->where('siteid=' . SITEID . ' and level=2')->order('listorder desc')->select();
                foreach ($list_xq_infos as $key => $value) {
                    $list_xq_infos[$key]['area'] = $Site->getFieldById($value['areaid'], 'name');
                    $list_xq_infos[$key]['schoollist'] = $article->field('id,title,phone,address,point,areaid')->where('schoolid !=0 and siteid=' . SITEID . ' and catid=34 and areaid=' . $value['areaid'])->order('listorder desc')->select();
                    //echo $article->getlastsql();
					if (!$list_xq_infos[$key]['schoollist'])
                        unset($list_xq_infos[$key]);
                }
                $this->assign('list_xq_info', $list_xq_infos);
            }
            $bcid = explode(",", $cat['arrparentid']);
            $bcid = $bcid[1];
            if ($bcid == '')
                $bcid = intval($catid);
            if (empty($module))
                $module = $cat['module'];
            $this->assign('module_name', $module);
            unset($cat['id']);
            $this->assign($cat);
            $cat['id'] = $catid;
            $this->assign('catid', $catid);
            $this->assign('bcid', $bcid);
        }
        if ($cat['readgroup'] && $this->_groupid != 1 && !in_array($this->_groupid, explode(',', $cat['readgroup']))) {
            $this->assign('jumpUrl', URL('User-Login/index'));
            $this->error(L('NO_READ'));
        }
        $fields = F($this->mod[$module] . '_Field');
        foreach ($fields as $key => $r) {
            $fields[$key]['setup'] = string2array($fields[$key]['setup']);
        }
        $this->assign('fields', $fields);


        $seo_title = $cat['title'] ? $cat['title'] : $cat['catname'];
        $this->assign('seo_title', $seo_title);
        $this->assign('seo_keywords', $cat['keywords']);
        $this->assign('seo_description', $cat['description']);


        if ($module == 'Guestbook') {
            $where['status'] = array('eq', 1);
            $this->dao = M($module);
            $count = $this->dao->where($where)->count();
            if ($count) {
                import("@.ORG.FPage");
                $listRows = !empty($cat['pagesize']) ? $cat['pagesize'] : C('PAGE_LISTROWS');
                $page = new Page($count, $listRows);
                $page->urlrule = geturl($cat, '');
                $pages = $page->show();
                $field = $this->module[$cat['moduleid']]['listfields'];
                $field = $field ? $field : '*';
                $list = $this->dao->field($field)->where($where)->order('createtime desc,id desc')->limit($page->firstRow . ',' . $page->listRows)->select();
                $this->assign('pages', $pages);
                $this->assign('list', $list);
            }
            $template = $cat['module'] == 'Guestbook' && $cat['template_list'] ? $cat['template_list'] : 'index';
            $this->display(THEME_PATH . $module . '_' . $template . '.html');
        } elseif ($module == 'Feedback') {
            $template = $cat['module'] == 'Feedback' && $cat['template_list'] ? $cat['template_list'] : 'index';

            $this->display(THEME_PATH . $module . '_' . $template . '.html');
        } elseif ($module == 'Page') {
            $modle = M('Page');
            $data = $modle->find($catid);
            unset($data['id']);

            //分页
            $CONTENT_POS = strpos($data['content'], '[page]');
            if ($CONTENT_POS !== false) {
                $urlrule = geturl($cat, '', $this->Urlrule);
                $urlrule[0] = urldecode($urlrule[0]);
                $urlrule[1] = urldecode($urlrule[1]);
                $contents = array_filter(explode('[page]', $data['content']));
                $pagenumber = count($contents);
                for ($i = 1; $i <= $pagenumber; $i++) {
                    $pageurls[$i] = str_replace('{$page}', $i, $urlrule);
                }
                $pages = content_pages($pagenumber, $p, $pageurls);
                //判断[page]出现的位置
                if ($CONTENT_POS < 7) {
                    $data['content'] = $contents[$p];
                } else {
                    $data['content'] = $contents[$p - 1];
                }
                $this->assign('pages', $pages);
            }

            $template = $cat['template_list'] ? $cat['template_list'] : 'index';
            $this->assign($data);
            $this->display(THEME_PATH . $module . '_' . $template . '.html');
        } else {

            if ($catid) {
                $seo_title = $cat['title'] ? $cat['title'] : $cat['catname'];
                $this->assign('seo_title', $seo_title);
                $this->assign('seo_keywords', $cat['keywords']);
                $this->assign('seo_description', $cat['description']);


                $where = " status=1 and siteid=" . SITEID;
                if ($cat['child']) {
                    $where .= " and catid in(" . $cat['arrchildid'] . ")";
                } else {
                    $where .= " and catid=" . $catid;
                }

                //成功案例筛选
                $subcatid = intval($_REQUEST['subcatid']);
                if ($subcatid) {
                    $where .= " and subcatid=" . $subcatid;
                }
                $studenttype = intval($_REQUEST['studenttype']);
                if ($studenttype) {
                    $where .= " and studenttype=" . $studenttype;
                }
//                //映射成功案例首页
//                if ('Chengonganli' == $module) {
//                    $cat['listtype'] = '';
//                    $where = " status=1 and catid=46 and siteid=" . SITEID;
//                }
                //选课中心
                if ('Classproject' == $module) {
                    $grade = intval($_REQUEST['grade']) ? intval($_REQUEST['grade']) : 0;
                    $term = intval($_REQUEST['term']) ? intval($_REQUEST['term']) : 0;
                    $course = $_REQUEST['course'] ? $_REQUEST['course'] : 0;
                    $level = intval($_REQUEST['level']) ? intval($_REQUEST['level']) : 0;
                    $area = intval($_REQUEST['area']) ? intval($_REQUEST['area']) : 0;
                    $school = intval($_REQUEST['school']) ? intval($_REQUEST['school']) : 0;
                    $show = intval($_REQUEST['show']) ? intval($_REQUEST['show']) : 0;
                    $site_tbl = M('Site');
                    $term_tbl = M('Term');
                    $grade_tbl = M('Grade');
                    $level_tbl = M('Level');
                    $course_tbl = M('Course');
                    $teacher_tbl = M('Teacher');
                    if ($grade) {
                        $reason = $grade_tbl->getfieldbyid($grade, 'reason');
                        $where .= " and grade_id=" . $grade;
                    }
                    if ($term) {
                        $where .= " and term_id=" . $term;
                    }
                    if ($course) {
                        $where .= " and course_id in(" . $course . ")";
                    }
                    if ($level) {
                        $where .= " and level_id=" . $level;
                    }
                    if ($area) {
                        $where .= " and find_in_set(" . $area . ",area_ids)";
                    }
                    if ($school) {
                        $where .= " and find_in_set(" . $school . ",school_ids)";
                    }
                    $this->assign('reason', $reason);
                    $this->assign('grade', $grade);
                    $this->assign('term', $term);
                    $this->assign('course', $course);
                    $this->assign('area', $area);
                    $this->assign('school', $school);
                    $this->assign('level', $level);
                    $this->assign('show', $show);
                }
                //教师筛选
                if ('Teacher' == $module) {
                    $grade = intval($_REQUEST['grade']) ? intval($_REQUEST['grade']) : 0;
                    $course = $_REQUEST['course'] ? $_REQUEST['course'] : 0;
                    $level = intval($_REQUEST['level']) ? intval($_REQUEST['level']) : 0;
                    $area = intval($_REQUEST['area']) ? intval($_REQUEST['area']) : 0;
                    $school = intval($_REQUEST['school']) ? intval($_REQUEST['school']) : 0;
                    $show = intval($_REQUEST['show']) ? intval($_REQUEST['show']) : 0;
                    $sex = intval($_REQUEST['sex']) ? intval($_REQUEST['sex']) : 0;
                    $site_tbl = M('Site');
                    $term_tbl = M('Term');
                    $grade_tbl = M('Grade');
                    $level_tbl = M('Level');
                    $course_tbl = M('Course');
                    $teacher_tbl = M('Teacher');
                    if ($grade) {
                        $reason = $grade_tbl->getfieldbyid($grade, 'reason');
                        $where .= " and find_in_set(" . $grade . ",grade_ids)";
                    }
                    if ($course) {
                        $where .= " and find_in_set(" . $course . ",course_ids)";
                    }
                    if ($area) {
                        $where .= " and find_in_set(" . $area . ",area_ids)";
                    }
                    if ($school) {
                        $where .= " and find_in_set(" . $school . ",school_ids)";
                    }
                    if ($sex) {
                        $where .= " and sex=" . $sex;
                    }
                    $this->assign('reason', $reason);
                    $this->assign('grade', $grade);
                    $this->assign('term', $term);
                    $this->assign('course', $course);
                    $this->assign('area', $area);
                    $this->assign('school', $school);
                    $this->assign('level', $level);
                    $this->assign('sex', $sex);
                    $this->assign('show', $show);
                }
                //校区
                if ('Schoolinfo' == $module) {
                    $areaid = intval($_REQUEST['areaid']);
                    if ($areaid) {
                        $where .= " and areaid=" . $areaid;
                        $this->assign('areaid', $areaid);
                    }
                }


                if (empty($cat['listtype'])) {
                    $this->dao = M($module);
                    $count = $this->dao->where($where)->count();
                    if ($count) {
                        import("@.ORG.FPage");
                        $listRows = !empty($cat['pagesize']) ? $cat['pagesize'] : C('PAGE_LISTROWS');
                        $page = new Page($count, $listRows);
                        $page->urlrule = geturl($cat, '', $this->Urlrule);
                        if ($grade)
                            $page->parameter .= "&grade=" . $grade . "&";
                        if ($course)
                            $page->parameter .= "&course=" . $course . "&";
                        if ($school)
                            $page->parameter .= "&school=" . $school . "&";
                        if ($show)
                            $page->parameter .= "&show=1&";
                        if ($area)
                            $page->parameter .= "&area=" . $area . "&";
                        if ($level)
                            $page->parameter .= "&level=" . $level . "&";
                        if ($subcatid)
                            $page->parameter .= "&subcatid=" . $subcatid;
                        $pages = $page->show();

                        $field = $this->module[$this->mod[$module]]['listfields'];
                        $field = $field ? $field : 'id,catid,userid,url,username,title,title_style,keywords,description,thumb,createtime,hits';
                        $list = $this->dao->field($field)->where($where)->order('createtime desc,id desc')->limit($page->firstRow . ',' . $page->listRows)->select();
                        if ('Classproject' == $module) {
                            foreach ($list as $k => $v) {
                                $list[$k]['grade'] = $grade_tbl->getFieldbyid($v['grade_id'], 'name');
                                $list[$k]['term'] = $term_tbl->getFieldbyid($v['term_id'], 'title');
                                $list[$k]['course'] = $course_tbl->getFieldbyid($v['course_id'], 'name');
                                $list[$k]['thumb'] = $v['thumb'] ? $v['thumb'] : '/Yourphp/Tpl/Home/Default/Public/images/pic23.jpg';
                                $list[$k]['level'] = $level_tbl->getFieldbyid($v['level_id'], 'name');
                                $list[$k]['area'] = $site_tbl->getFieldbyid($v['areaid'], 'name');
                                $list[$k]['school'] = $site_tbl->getFieldbyid($v['schoolid'], 'name');
                                $schoolinfo_tbl = M('Schoolinfo');
                                $list[$k]['schoolinfoid'] = $schoolinfo_tbl->getFieldbyschoolid($v['schoolid'], 'id');
                                $list[$k]['teacher'] = $teacher_tbl->getFieldbyid($v['teacher_id'], 'title');
                            }
                        }
                        if ('Teacher' == $module) {
                            foreach ($list as $k => $v) {
//                                $list[$k]['grade'] = $grade_tbl->getFieldbyid($v['grade_id'], 'name');
//                                $list[$k]['term'] = $term_tbl->getFieldbyid($v['term_id'], 'name');
//                                $list[$k]['course'] = $course_tbl->getFieldbyid($v['course_id'], 'name');
//                                $list[$k]['level'] = $level_tbl->getFieldbyid($v['level_id'], 'name');
//                                $list[$k]['area'] = $site_tbl->getFieldbyid($v['areaid'], 'name');
//                                $list[$k]['school'] = $site_tbl->getFieldbyid($v['schoolid'], 'name');
                                $list[$k]['teacher'] = $teacher_tbl->getFieldbyid($v['teacher_id'], 'title');
                                $list[$k]['thumb'] = $v['thumb'] ? $v['thumb'] : '/Yourphp/Tpl/Home/Default/Public/images/pic23.jpg';
                            }
                        }

                        $this->assign('pages', $pages);
                        $this->assign('list', $list);
                    }
                    $template_r = 'list';
                } else {
                    $template_r = 'index';
                }
            } else {
                $template_r = 'list';
            }
            $template = $cat['template_list'] ? $cat['template_list'] : $template_r;
            $this->display($module . ':' . $template);
        }
    }

    public function show($id = '', $module = '') {
        $this->Urlrule = F('Urlrule');
        $p = max(intval($_REQUEST[C('VAR_PAGE')]), 1);
        $id = $id ? $id : intval($_REQUEST['id']);
        $module = $module ? $module : MODULE_NAME;
        $this->assign('module_name', $module);
        $this->dao = M($module);
        $data = $this->dao->find($id);
        if ('Teacher' == $module) {
            $data['grade'] = M('Grade')->getFieldbyid($data['grade_id'], 'name');
            $data['school'] = M('Site')->getFieldbyid($data['schoolid'], 'name');
            $data['course'] = M('Course')->getFieldbyid($data['course_id'], 'name');
        }

        $catid = $data['catid'];
        $cat = $this->categorys[$data['catid']];
        if (empty($cat['ishtml']))
            $this->dao->where("id=" . $id)->setInc('hits'); //添加点击次数
        $bcid = explode(",", $cat['arrparentid']);
        $bcid = $bcid[1];
        if ($bcid == '')
            $bcid = intval($catid);

        if ($data['readgroup']) {
            if ($this->_groupid != 1 && !in_array($this->_groupid, explode(',', $data['readgroup'])))
                $noread = 1;
        }elseif ($cat['readgroup']) {
            if ($this->_groupid != 1 && !in_array($this->_groupid, explode(',', $cat['readgroup'])))
                $noread = 1;
        }
        if ($noread == 1) {
            $this->assign('jumpUrl', URL('User-Login/index'));
            $this->error(L('NO_READ'));
        }

        $chargepoint = $data['readpoint'] ? $data['readpoint'] : $cat['chargepoint'];
        if ($chargepoint && $data['userid'] != $this->_userid) {
            $user = M('User');
            $userdata = $user->find($this->_userid);
            if ($cat['paytype'] == 1 && $userdata['point'] >= $chargepoint) {
                $chargepointok = $user->where("id=" . $this->_userid)->setDec('point', $chargepoint);
            } elseif ($cat['paytype'] == 2 && $userdata['amount'] >= $chargepoint) {
                $chargepointok = $user->where("id=" . $this->_userid)->setDec('amount', $chargepoint);
            } else {
                $this->error(L('NO_READ'));
            }
        }

        $seo_title = $data['title'] . '-' . $cat['catname'];
        $this->assign('seo_title', $seo_title);
        $this->assign('seo_keywords', $data['keywords']);
        $this->assign('seo_description', $data['description']);
        $this->assign('fields', F($cat['moduleid'] . '_Field'));


        $fields = F($this->mod[$module] . '_Field');
        foreach ($data as $key => $c_d) {
            $setup = '';
            $fields[$key]['setup'] = $setup = string2array($fields[$key]['setup']);
            if ($setup['fieldtype'] == 'varchar' && $fields[$key]['type'] != 'text') {
                $data[$key . '_old_val'] = $data[$key];
                $data[$key] = fieldoption($fields[$key], $data[$key]);
            } elseif ($fields[$key]['type'] == 'images' || $fields[$key]['type'] == 'files') {
                if (!empty($data[$key])) {
                    $p_data = explode(':::', $data[$key]);
                    $data[$key] = array();
                    foreach ($p_data as $k => $res) {
                        $p_data_arr = explode('|', $res);
                        $data[$key][$k]['filepath'] = $p_data_arr[0];
                        $data[$key][$k]['filename'] = $p_data_arr[1];
                    }
                    unset($p_data);
                    unset($p_data_arr);
                }
            }
            unset($setup);
        }
        $this->assign('fields', $fields);
        if ('Classproject' == $module) {
            $site_tbl = M('Site');
            $grade_tbl = M('Grade');
            $course_tbl = M('Course');
            $teacher_tbl = M('Teacher');
            $school_tbl = M('Schoolinfo');
            $level_tbl = M('Level');
            $term_tbl = M('Term');
            $data['gradeid'] = $data['grade_id'];
            $data['schoolid'] = $data['schoolid'];
            $data['grade_id'] = $grade_tbl->getFieldbyid($data['grade_id'], 'name');
            $data['course'] = $course_tbl->getFieldbyid($data['course_id'], 'name');
            $data['teacher2id'] = $data['teacher_id'];
            $data['teacher_id'] = $teacher_tbl->getFieldbyid($data['teacher_id'], 'teachername');
            $data['schoolname'] = $site_tbl->getFieldbyid($data['schoolid'], 'name');
            $data['school2id'] = $school_tbl->getFieldbyschoolid($data['schoolid'], 'id');
            $data['level'] = $level_tbl->getFieldbyId($data['level_id'], 'name');
            $data['term'] = $term_tbl->getFieldbyId($data['term_id'], 'title');
        }
        if ('Chengonganli' == $module) {
            $site_tbl = M('Site');
            $grade_tbl = M('Grade');
            $course_tbl = M('Course');
            $teacher_tbl = M('Teacher');
            $school_tbl = M('Schoolinfo');
            $data['grade_id'] = $grade_tbl->getFieldbyid($data['grade_id'], 'name');
            $data['teacherid'] = $data['teacher_id'];
            $data['teacher_id'] = $teacher_tbl->getFieldbyid($data['teacher_id'], 'teachername');
            $data['schoolname'] = $site_tbl->getFieldbyid($data['schoolid'], 'name');
            $data['school2id'] = $school_tbl->getFieldbyschoolid($data['schoolid'], 'id');
        }
        if ('Teacher' == $module) {
            if ($data['school_ids']) {
                $site = M('Site');
                $arr_schoolid = explode(',', $data['school_ids']);
                foreach ($arr_schoolid as $k => $v) {
                    $arr_schoolname[$k] = '<a href="/xiaoqu/show/'.$v.'.html">'.$site->getfieldbyid($v, 'name').'</a>';
                }
                $data['school_ids'] = implode(',', $arr_schoolname);
            }
        }
        if ('Schoolinfo' == $module) {
            $Site = M('Site');
            $Schoolinfo = M('Schoolinfo');
            $list_xq_infos = $Site->where('siteid=' . SITEID . ' and level=2')->order('listorder desc')->select();
            foreach ($list_xq_infos as $key => $value) {
                $list_xq_infos[$key]['area'] = $Site->getFieldById($value['areaid'], 'name');
                $list_xq_infos[$key]['schoollist'] = $Schoolinfo->field('id,title,phone,address,point,areaid')->where('schoolid !=0 and siteid=' . SITEID . ' and catid=34 and areaid=' . $value['areaid'])->order('listorder desc')->select();
                if (!$list_xq_infos[$key]['schoollist'])
                    unset($list_xq_infos[$key]);
            }
            $this->assign('list_xq_info', $list_xq_infos);
        }


        //手动分页
        $CONTENT_POS = strpos($data['content'], '[page]');
        if ($CONTENT_POS !== false) {

            $urlrule = geturl($cat, $data, $this->Urlrule);
            $urlrule = str_replace('%7B%24page%7D', '{$page}', $urlrule);
            $contents = array_filter(explode('[page]', $data['content']));
            $pagenumber = count($contents);
            for ($i = 1; $i <= $pagenumber; $i++) {
                $pageurls[$i] = str_replace('{$page}', $i, $urlrule);
            }
            $pages = content_pages($pagenumber, $p, $pageurls);
            //判断[page]出现的位置是否在文章开始
            if ($CONTENT_POS < 7) {
                $data['content'] = $contents[$p];
            } else {
                $data['content'] = $contents[$p - 1];
            }
            $this->assign('pages', $pages);
        }

        if (!empty($data['template'])) {
            $template = $data['template'];
        } elseif (!empty($cat['template_show'])) {
            $template = $cat['template_show'];
        } else {
            $template = 'show';
        }

        $this->assign('catid', $catid);
        $this->assign($cat);
        $this->assign('bcid', $bcid);
        $this->assign($data);

        $this->display($module . ':' . $template);
    }

    public function down() {

        $module = $module ? $module : MODULE_NAME;
        $id = $id ? $id : intval($_REQUEST['id']);
        $this->dao = M($module);
        $filepath = $this->dao->where("id=" . $id)->getField('file');
        $this->dao->where("id=" . $id)->setInc('downs');

        if (strpos($filepath, ':/')) {
            header("Location: $filepath");
        } else {
            $filepath = '.' . $filepath;
            if (!$filename)
                $filename = basename($filepath);
            $useragent = strtolower($_SERVER['HTTP_USER_AGENT']);
            if (strpos($useragent, 'msie ') !== false)
                $filename = rawurlencode($filename);
            $filetype = strtolower(trim(substr(strrchr($filename, '.'), 1, 10)));
            $filesize = sprintf("%u", filesize($filepath));
            if (ob_get_length() !== false)
                @ob_end_clean();
            header('Pragma: public');
            header('Last-Modified: ' . gmdate('D, d M Y H:i:s') . ' GMT');
            header('Cache-Control: no-store, no-cache, must-revalidate');
            header('Cache-Control: pre-check=0, post-check=0, max-age=0');
            header('Content-Transfer-Encoding: binary');
            header('Content-Encoding: none');
            header('Content-type: ' . $filetype);
            header('Content-Disposition: attachment; filename="' . $filename . '"');
            header('Content-length: ' . $filesize);
            readfile($filepath);
        }
        exit;
    }

    public function hits() {
        $module = $module ? $module : MODULE_NAME;
        $id = $id ? $id : intval($_REQUEST['id']);
        $this->dao = M($module);
        $this->dao->where("id=" . $id)->setInc('hits');

        if ($module == 'Download') {
            $r = $this->dao->find($id);
            echo '$("#hits").html(' . $r['hits'] . ');$("#downs").html(' . $r['downs'] . ');';
        } else {
            $hits = $this->dao->where("id=" . $id)->getField('hits');
            echo '$("#hits").html(' . $hits . ');';
        }
        exit;
    }

    public function verify() {
        header('Content-type: image/jpeg');
        $type = isset($_GET['type']) ? get_safe_replace($_GET['type']) : 'jpeg';
        import("@.ORG.Image");
        Image::buildImageVerify(4, 1, $type);
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
    public function get_schoolinfo() {
        $areaid = $this->_request('areaid');
        $area = M('Site');
        $info = M('Schoolinfo');
        $map['areaid'] = $areaid;
        $map['level'] = 3;
        $r = $area->where($map)->order('listorder desc')->select();
        foreach ($r as $k => $v) {
            $r[$k]['schoolinfo_id'] = $info->getfieldbySchoolid($v['schoolid'], 'id');
        }
        if ($r)
            $this->ajaxReturn('ok', $r, 'ok');
        else
            $this->ajaxReturn('error', $area->getLastSql(), 'error');
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