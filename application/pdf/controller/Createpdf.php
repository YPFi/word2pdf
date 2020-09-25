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
 * create pdf
 * Class Express
 * @package app\pdf\controller
 */
class Createpdf extends Controller
{
    /**
     * 指定数据表
     * @var string
     */
    protected $table = 'pdf_file';

    /**
     * create pdf
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
        $this->title = 'create PDF';
        $query = $this->_query($this->table)->like('template');
        $query->dateBetween('create_at')->order('id desc')->page();
    }

    /**
     * create pdf
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
            $ROOT_PATH = str_replace('\\',"/",Env::get('root_path'));;
            $url = $ROOT_PATH . 'public/static/upload/pdf/';
            $tid = input('tid');
            // dump($tid);die;
            $tpl = Db::name('template')->where('id',$tid)->find();
            $data = Db::name('templatecode')->where('tid',$tid)->select();
            $codeName = explode(',',$tpl['code']);
            $item = [];
            $code = [];
            foreach ($codeName as $c){
//                dump($c);
                $item[$c]='';
            }
            foreach($data as $k=>$d){
                $length = count($item);
                $m = 0;
                foreach ($item as $i=>$v){
                    $item[$i] = $data[$k]['code'.$m];
                    if($m <= $length){
                        $m++;
                    }
                    $code[$k] = $item;
                }
            }
            $result = $this->create($tpl,$code,$url);
            if ($result == 1){
                return $this->success('create success');
            }else{
                return $this->error('create error');
            }

        }
        return $this->fetch('form');
    }


    /**
     * disable pdf
     * @auth true
     * @throws \think\Exception
     * @throws \think\exception\PDOException
     */
    public function forbid()
    {
        $this->_save($this->table, ['status' => '0']);
    }

    /**
     * use pdf
     * @auth true
     * @throws \think\Exception
     * @throws \think\exception\PDOException
     */
    public function resume()
    {
        $this->_save($this->table, ['status' => '1']);
    }

    /**
     * delete pdf
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
    
    public function download(){
    	$id = input('id');
    	$ROOT_PATH = str_replace('\\',"/",Env::get('root_path'));
    	$file = Db::name('pdf_file')->where('id',$id)->find();
    	$url = $ROOT_PATH.'public/static'.$file['url'];
    	 header('Content-Description: File Transfer');
		 header('Content-Type: application/octet-stream');

		 header('Content-Transfer-Encoding: binary');
         header('Expires: 0');
         header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
         header('Pragma: public');
         header('Content-Length: ' . filesize($url));
		
    	
    	header('Content-Disposition: attachment;filename='.$url);
    }

    public function create($tpl,$code,$url)
    {
        $pdf = new \TCPDF('P', 'mm', 'A4', true, 'UTF-8', false);
        $pdf->SetAuthor('admin');
        $pdf->SetTitle($tpl['name']);
        $pdf->SetSubject('TCPDF Tutorial');
        $pdf->SetKeywords('TCPDF, PDF, guide');
        $codeName = explode(',',$tpl['code']);
        $pdf->SetDefaultMonospacedFont(PDF_FONT_MONOSPACED);
        $ROOT_PATH = str_replace('\\',"/",Env::get('root_path'));
        $imgUrl = $ROOT_PATH . 'public/pdfHeader/';
        // Define the path to the image that you want to use as watermark.
        $img_file = $imgUrl.'header.png';
//去掉默认的页头页脚。比如那个横线
        $pdf->setPrintHeader(false);
        $pdf->setPrintFooter(false);
        $pdf->SetMargins(0, 0, 0, true);
// set font
        // set image scale factor
        $pdf->setImageScale(PDF_IMAGE_SCALE_RATIO);
        $pdf->SetAutoPageBreak(true, 0);
        $pdf->SetFont('helvetica', '', 9);

        foreach ($code as $k=>$c){
            // add a page
            $str = [];
            foreach ($c as $key=>$v){
                $str[] = '$'.$key;
            }
            $pdf->AddPage();
            extract($c);
//            dump($c);
//            dump($str);
            $pdf->Image($img_file, 0, 0, 210, 297, 'png', '', '', true, 200, '', false, false, 0, false, false, true);

// --- Method (B) ------------------------------------------
// provide image + separate 8-bit mask
// first embed mask image (w, h, x and y will be ignored, the image will be scaled to the target image's size)

            $html =  $tpl['content'];
            $html = str_replace($str,$c,$html);
// define some HTML content with style
            // output the HTML content
            $pdf->writeHTML($html, true, false, true, false, '');

        }



// - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - -


// ---------------------------------------------------------
        $ROOT_PATH = str_replace('\\',"/",Env::get('root_path'));
//Close and output PDF document
        $time = time();
        $names = $url.$time.'.pdf';
        $pdf->Output($names, 'F');
        $fileUrl = "/upload/pdf/".$time.'.pdf';
        $result = Db::name('pdf_file')->insert(['template'=>$tpl['name'], 'url'=>$fileUrl]);
        return $result;
//============================================================+
// END OF FILE
//============================================================+
    }
    


}
