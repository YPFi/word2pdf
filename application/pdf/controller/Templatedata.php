<?php

// +----------------------------------------------------------------------
// | ThinkAdmin
// +----------------------------------------------------------------------
// | 版权所有 2014~2019 广州楚才信息科技有限公司 [ http://www.cuci.cc ]
// +----------------------------------------------------------------------
// | 官方网站: http://demo.thinkadmin.top
// +----------------------------------------------------------------------
// | 开源协议 ( https://mit-license.org )
// +----------------------------------------------------------------------
// | gitee 代码仓库：https://gitee.com/zoujingli/ThinkAdmin
// | github 代码仓库：https://github.com/zoujingli/ThinkAdmin
// +----------------------------------------------------------------------

namespace app\pdf\controller;
use library\Controller;
use think\Db;
use think\facade\Env;

/**
 * temlate data
 * Class Express
 * @package app\pdf\controller
 */
class Templatedata extends Controller
{
    /**
     * 指定数据表
     * @var string
     */
    protected $table = 'templatecode';

    /**
     * temlate data
     * @auth true
     * @menu true
     * @throws \think\Exception
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     * @throws \think\exception\PDOException
     */
    public function index()
    {
        $this->title = 'template code';
        $query = $this->_query($this->table)->like('tid');
        $query->dateBetween('create_at')->order('id desc')->page();
    }

    /**
     * add temlate data
     * @auth true
     * @throws \think\Exception
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     * @throws \think\exception\PDOException
     */
    public function add()
    {
        if (request()->isPost()){
            return $this->success('update success');
        }
        return $this->fetch('form');
    }



    /**
     * edit temlate data
     * @auth true
     * @throws \think\Exception
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     * @throws \think\exception\PDOException
     */
    public function edit()
    {
        $this->_form($this->table, 'form');
    }

    /**
     * 表单数据处理
     * @param array $data
     * @auth true
     */
//    protected function _form_filter(array $data)
//    {
//        if ($this->request->isPost()) {
//            $where = [['express_code', 'eq', $data['express_code']], ['is_deleted', 'eq', '0']];
//            if (!empty($data['id'])) $where[] = ['id ', 'neq', $data['id']];
//            if (Db::name($this->table)->where($where)->count() > 0) {
//                $this->error('该快递编码已经存在，请使用其它编码！');
//            }
//        }
//    }

    /**
     * disable temlate data
     * @auth true
     * @throws \think\Exception
     * @throws \think\exception\PDOException
     */
    public function forbid()
    {
        $this->_save($this->table, ['status' => '0']);
    }

    /**
     * use temlate data
     * @auth true
     * @throws \think\Exception
     * @throws \think\exception\PDOException
     */
    public function resume()
    {
        $this->_save($this->table, ['status' => '1']);
    }

    /**
     * delete temlate data
     * @auth true
     * @throws \think\Exception
     * @throws \think\exception\PDOException
     */
    public function remove()
    {
        $this->_delete($this->table);
    }

    //获取模板类型
    public function getType(){
        $data = Db::name('template_type')->where('status',1)->select();
        if (empty($data)){
            return json(['code'=>400, 'msg'=> '未查询到数据', 'data'=>'']);
        }else{
            return json(['code'=>200, 'msg'=> '查询成功', 'data'=>$data]);
        }
    }

    //获取模板类型
    public function getTpls(){
        $data = Db::name('template')->where('status',1)->select();
        if (empty($data)){
            return json(['code'=>400, 'msg'=> '未查询到数据', 'data'=>'']);
        }else{
            return json(['code'=>200, 'msg'=> '查询成功', 'data'=>$data]);
        }
    }

    //获取模板类型
    public function getTpl(){
        $id = input('id');
        $data = Db::name('template')->where(['status'=>1,'type'=>$id])->select();
        if (empty($data)){
            return json(['code'=>400, 'msg'=> '未查询到数据', 'data'=>'']);
        }else{
            return json(['code'=>200, 'msg'=> '查询成功', 'data'=>$data]);
        }
    }


    public function insertExcel(){
            $objPHPExcel =new \PHPExcel();
            $tid = input('tid');
            if(empty($tid)){
                $result["code"] = "3";
                $result["msg"] = "请选择模板";
            }
            $file = request()->file('file');
            if (!empty($file)){
                //获取表单上传文件
                // $ROOT_PATH = Env::get('root_path');
                $ROOT_PATH = str_replace('\\',"/",Env::get('root_path'));
               
                $info = $file->validate(['ext' => 'xlsx'])->move($ROOT_PATH .'public'. DIRECTORY_SEPARATOR .'static' . DIRECTORY_SEPARATOR . 'upload' );  //上传验证后缀名,以及上传之后移动的地址  E:\wamp\www\bick\public
                
                if($info)
                {
//              echo $info->getFilename();
                    $exclePath = $info->getSaveName();  //获取文件名
                    $file_path = $ROOT_PATH . 'public/static' . DIRECTORY_SEPARATOR . 'upload/' . $exclePath;//上传文件的地址
                    $file_name = str_replace('\\',"/",$file_path);
                    $objReader =\PHPExcel_IOFactory::createReader("Excel2007");
                    $obj_PHPExcel =$objReader->load($file_name, $encode = 'utf-8');  //加载文件内容,编码utf-8
                    $excel_array=$obj_PHPExcel->getSheet(0)->toArray();   //转换为数组格式
                    $codes = $excel_array;
                    $data = [];
                    unset($codes[0]);
                    foreach ($codes as $k=>$c){
                        $length = count($c);
                        for ($i=0;$i < $length; $i++){
                            $data[$k]['code'.$i] = $c[$i];
                        }
                    }
                    foreach ($data as $k=>$v){
                        $data[$k]['tid'] = $tid;
                    }
                    $results = Db::name('templatecode')->limit(100)->insertAll($data);
                    if(empty($results)){
                        $result["code"] = "1";
                        $result["msg"] = "error";
                    }else{
                        $result["code"] = '0';
                        $result["msg"] = "success";
                    }
                }else
                {
                    $result["code"] = "2";
                    $result["msg"] = "上传失败";
                }
            }
            return json_encode($result);
        }


}
