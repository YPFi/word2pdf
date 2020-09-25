<?php


namespace app\pdf\controller;
use library\Controller;
use think\Facade\Env;

class Images extends Controller
{

    public function upload()//上传模块
    {
        $ROOT_PATH = str_replace('\\',"/",Env::get('root_path'));
        $file = request()->file('file');
        if(empty($file)){
            $result["code"] = "1";
            $result["msg"] = "请选择图片";
            $result['data']["src"] = '';
        }else{
            // 移动到框架应用根目录/public/uploads/ 目录下
            $info = $file->validate(['ext'=>'jpg,png,gif'])->move($ROOT_PATH . 'public/static' . DIRECTORY_SEPARATOR . 'upload' );
            if($info){
                $name_path =str_replace('\\',"/",$info->getSaveName());
                //成功上传后 获取上传信息
                $result["code"] = '0';
                $result["msg"] = "上传成功";
                $result['data']["src"] ="/static/upload/".$name_path;
            }else{
                // 上传失败获取错误信息
                $result["code"] = "2";
                $result["msg"] = $file->getError();
                $result['data']["src"] ='';
            }
        }
        return json_encode($result);
    }

}