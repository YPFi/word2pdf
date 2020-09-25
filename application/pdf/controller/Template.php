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

/**
 * template
 * Class Express
 * @package app\pdf\controller
 */
class Template extends Controller
{
    /**
     * 指定数据表
     * @var string
     */
    protected $table = 'template';

    /**
     * template
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
        $this->title = 'template';
        $query = $this->_query($this->table)->like('name');
        $query->dateBetween('create_at')->order('id desc')->page();
    }

    /**
     * add template
     * @auth true
     * @throws \think\Exception
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     * @throws \think\exception\PDOException
     */
    public function add()
    {
        $this->title = 'add template';
        $this->_form($this->table, 'form');
    }

    /**
     * edit template
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
     * disable template
     * @auth true
     * @throws \think\Exception
     * @throws \think\exception\PDOException
     */
    public function forbid()
    {
        $this->_save($this->table, ['status' => '0']);
    }

    /**
     * use template
     * @auth true
     * @throws \think\Exception
     * @throws \think\exception\PDOException
     */
    public function resume()
    {
        $this->_save($this->table, ['status' => '1']);
    }

    /**
     * delete template
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


    public function exportExcel(){
        $id = input('id');
        $tpl = Db::name("template")->where('id',$id)->find();     //数据库查询
        $path = dirname(__FILE__); //找到当前脚本所在路径
        $code = explode(',',$tpl['code']);
        $head = ['A1','B1','C1','D1','E1','F1','G1','H1','I1','J1','K1','L1','M1','N1','O1','P1','Q1','R1','S1','T1'];

        $PHPExcel = new \PHPExcel();
        $PHPSheet = $PHPExcel->getActiveSheet();
        $PHPSheet->setTitle($tpl['name']); //给当前活动sheet设置名称
        foreach ($code as $K=>$data){
            $PHPSheet->setCellValue($head[$K], $code[$K]);
        }

        $fileName = $tpl['name'].'.xlsx';
        $PHPWriter = \PHPExcel_IOFactory::createWriter($PHPExcel, "Excel2007");
        header('Content-Disposition: attachment;filename='.$fileName);
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        $PHPWriter->save("php://output"); //表示在$path路径下面生成demo.xlsx文件
    }

}
