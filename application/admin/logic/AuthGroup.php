<?php
namespace app\admin\logic;

use think\Collection;
use think\facade\App;


class AuthGroup{

    /**
     * 获取访问授权列表
     * @param $params
     * @return mixed
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function getAccessList($params) {

        if (!defined('MEMBER_ID')) {
            define ( 'MEMBER_ID', 0 );
        }

        $uid = isset($params['uid']) ? $params['uid'] : '';
        $group_id = isset($params['group_id']) ? $params['group_id'] : '';

        $isAdmin = $uid == 1 ? true : false;

        if($isAdmin) {
            $ruleList = App::model('AuthRule')->column('name');
        } else {
            if(empty($uid) && !empty($group_id)) {
                $groupInfo = $this->getInfo($group_id);
            } else {
                //获取用户组权限
                $authGroupAccessInfo = App::model('AuthGroupAccess', 'logic')->getInfoByUid($uid);
                if(empty ($authGroupAccessInfo)) {
                    return false;
                }

                $groupArray = [];
                foreach($authGroupAccessInfo as $val) {
                    $ruleInfo = $this->getInfo($val['group_id']);
                    if(!empty($ruleInfo['rules'])) {
                        $ruleArray = explode(',', $ruleInfo['rules']);
                        $groupArray = array_merge($groupArray, $ruleArray);
                    }
                }

                $groupInfo['rules'] = implode(',', $groupArray);
            }

            if(!empty ($groupInfo['rules'])) {
                $ruleList = App::model('AuthRule')->where('id in (' . $groupInfo['rules'] . ')')->column('name');
            }else{
                $ruleList = [];
            }

        }

        $condition = [
            'm.url' => [
                'neq',
                '',
            ],
        ];

        $menuModel = App::model('Menu');
        $list      = $menuModel->alias('m')->join('AuthRuleNew ar', 'm.url=ar.name', 'LEFT')->field('ar.id as rulesId ,m.id,m.title,m.route_name,m.pid,m.group,m.url,m.hide,m.tip,m.sort,m.is_dev,m.status')->where($condition)->order('m.sort asc')->select();

        //不是admin帐号的话，需要对菜单进行处理，只能看到当前自身权限下的菜单
        if(MEMBER_ID != 1 && empty($uid) && !empty($group_id)) {

            $mangerAuthGroupAccessInfo = App::model('AuthGroupAccess', 'logic')->getInfoByUid(MEMBER_ID);
            if(empty ($mangerAuthGroupAccessInfo)) {
                return false;
            }

            $mangerGroupArray = [];
            foreach($mangerAuthGroupAccessInfo as $val) {
                $mangerRuleInfo = $this->getInfo($val['group_id']);
                if(!empty($mangerRuleInfo['rules'])) {
                    $ruleArray = explode(',', $mangerRuleInfo['rules']);
                    $mangerGroupArray = array_merge($mangerGroupArray, $ruleArray);
                }
            }

            $mangerGroupInfo = implode(',', $mangerGroupArray);

            if(!empty ($mangerGroupInfo)) {
                $mangerRuleList = App::model('AuthRule')->where('id in (' . $mangerGroupInfo . ')')->column('name');
            }else {
                $mangerRuleList = [];
            }

            if(is_object($mangerRuleList)) {
                $mangerRuleList = collection($mangerRuleList)->toArray();
            }

            foreach($list as $key => $val) {
                if(!in_array($val ['url'], $mangerRuleList)) {
                    unset($list[$key]);
                }
            }
        }

        return $this->_buildAccessTreeArray($list, $ruleList);
    }


    private function _buildAccessTreeArray($data, $rule = [], $pId = 0) {

        //根据rule来生成缓存标识
//        if($pId == 0) {
//            $cacheKey        = md5(implode('-', $rule));
//            $accessCacheList = Cache::tag('accesslist')->get($cacheKey);
//        }else {
//            $accessCacheList = '';
//        }

        $tree = [];

        if(true) {
            //if(empty($accessCacheList)) {

            if(is_object($rule)) {
                $rule = collection($rule)->toArray();
            }

            foreach($data as $key => $value) {

                if(empty($value ['rulesId'])) {
                    continue;
                }

                $tmp              = [];
                $tmp['rulesId']   = $value ['rulesId'];
                $tmp['name']      = $value ['title'];
                $tmp['routeName'] = $value ['route_name'];
                $tmp['url']       = $value ['url'];
                $tmp['checked']   = in_array($value ['url'], $rule) ? 1 : 0;
                if($value['pid'] == $pId) {
                    $childRule = $this->_buildAccessTreeArray($data, $rule, $value['id']);
                    if($childRule) {
                        $tmp['childRule'] = $childRule;
                    }
                    $tree[] = $tmp;
                }
            }

//            if($pId == 0) {
//                Cache::tag('accesslist')->set($cacheKey, $tree);
//            }

//        }else {
//            $tree = $accessCacheList;
        }

        return $tree;
    }




}