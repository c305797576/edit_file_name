<?php
/**
 * 创建文件
 * @param string $filename 文件名
 * @return bool true|false
 */
function creat_file(string $filename){
    //检测文件是否存在，不存在则创建
    if (file_exists($filename)){
        return false;
    }
    //检测目录是否存在，不存在则创建
    if (!file_exists(dirname($filename))){
        //创建目录，可以创建多级
        mkdir(dirname($filename),'0777',true);
    }
    //创建文件，两种方式
//    if (touch($filename)){
//        return true;
//    }
//    return false;
    //创建文件
    if (file_put_contents($filename,'')!==false){
        return true;
    }
    return false;
}

/**
 * 删除文件
 * @param string $filename
 * @return string   成功/错误信息
 */
function del_file(string $filename){
    //检测删除的文件是否存在
    if(!file_exists($filename)){
        return '文件不存在';
    }
    //检测是否有权限操作
    if (!is_writable($filename)){
        return '无权限操作';
    }
    //删除文件
    if (unlink($filename)){
        return '删除成功';
    }
    return '删除失败';
}

/**
 * 复制文件
 * @param string $filename  文件名
 * @param string $dest  目标目录
 * @return bool false|true
 */
function copy_file(string $filename,string $dest){
    //检测$dest是否目标并且这个目录是否存在，不存在则创建
    if (!is_dir($dest)){
        mkdir($dest,'0777',true);
    }
    $destName=$dest.DIRECTORY_SEPARATOR.basename($filename);
    //检测目标路径下是否存在同名文件
    if (file_exists($destName)){
        return false;
    }
    if (copy($filename,$destName)){
        return true;
    }
    return false;
}

/**
 * 重命名文件
 * @param string $oldName   旧名称
 * @param string $newName   新名称
 * @return bool false|true
 */
function rename_file(string $oldName,string $newName){
    //检测原文件是否存在
    if (!is_file($oldName)){
        return false;
    }
    //得到源文件所在路径
    $path=dirname($oldName);
    $destName=$path.DIRECTORY_SEPARATOR.$newName;
    if (is_file($destName)){
        return false;
    }
    if (rename($oldName,$newName)){
        return true;
    }
    return false;
}

/**
 * 剪切文件
 * @param string $fileName  文件
 * @param string $dest  目标路径
 * @return bool false|true
 */
function cut_file(string $fileName,string $dest){
    //检测原文件是否存在
    if (!is_file($fileName)){
        return false;
    }
    if (!is_dir($dest)){
        return false;
    }
    //得到目标路径
    $destName=$dest.DIRECTORY_SEPARATOR.$fileName;
    //目标路径是否存在同名文件
    if (is_file($destName)){
        return false;
    }
    if (rename($fileName,$destName)){
        return true;
    }
    return false;
}

/**
 * 读取文件内容
 * @param string $filename  文件名
 * @return bool|string
 */
function read_file(string $filename){
    if (is_file($filename) && is_readable($filename)){
        return file_get_contents($filename);
    }
    return false;
}

/**
 * 读取文件内容以数组形式返回
 * @param string $filename
 * @param bool $skip_empty_lines
 * @return array|bool
 */
function read_file_array(string $filename,bool $skip_empty_lines){
    if (is_file($filename) && is_readable($filename)){
        if ($skip_empty_lines){
            return file($filename,FILE_IGNORE_NEW_LINES|FILE_SKIP_EMPTY_LINES);
        }else{
            return file($filename);
        }
    }
    return false;
}

/**
 * 查找出文件夹下面所有的文件
 * @param $dir  文件夹路径
 * @return array
 */
function my_scandir($dir)
{
    //检测目录是否存在
    if(is_dir($dir)){
        $files = array();
        $child_dirs = scandir($dir);//接收目录参数，返回目录下所有的子目录和文件数组
        foreach($child_dirs as $child_dir){
            //'.'和'..'是Linux系统中的当前目录和上一级目录，必须排除掉，
            //否则会进入死循环，报segmentation falt 错误
            if($child_dir != '.' && $child_dir != '..'){
                if(is_dir($dir.'/'.$child_dir)){
                    $files[$child_dir] = my_scandir($dir.'/'.$child_dir);
                }else{
                    $files[] = $child_dir;
                }
            }
        }
        return $files;
    }else{
        return $dir;
    }
}
function write_file(string $filename,$data,bool $clearFlag){
    $dirname=dirname($filename);
    //检测目标目录是否存在
    if (file_exists($dirname)){
        mkdir($dirname,'0755',true);
    }
    $srcData='';
    //检测文件是否存在并且可读
    if (is_file($filename)&&is_readable($filename)){
        if (filesize($filename)>0){
            $srcData=file_get_contents($filename);
        }
    }
    //是否数组或对象
    if (is_array($data)||is_object($data)){
        //序列化数组
        $data=serialize($data);
    }
    $data=$srcData.$data;
    if (file_put_contents($filename,$data)!==false){
        return true;
    }
    return false;
}

/**
 * 截断文件到指定大小
 * @param string $filename
 * @param int $length
 * @return bool
 */
function truncate_file(string $filename,int $length){
    if (is_file($filename)){
        $handle=fopen($filename,'r+');
        $length=$length<0?0:$length;
        ftruncate($handle,$length);
        fclose($handle);
        return true;
    }
    return false;
}

/**
 * 查找目录下所有文件名以及文件内容，然后进行替换
 * @param string $dest
 * @param string $seach
 * @param string $replace
 */
function edit_file_name(string $dest,string $seach,string $replace){
    //获取文件夹下面所有的文件
    $fileArr=my_scandir($dest);
    //将目录转化为一维数组
    $fileArr=transform_arr($fileArr);
    //循环文件执行文件内容和文件名的修改
    foreach ($fileArr as $key=>$value){
        //以数组的方式读取文件内容
        $read=read_file_array($dest.DIRECTORY_SEPARATOR.$value,false);
        //替换文件内容
        foreach ($read as $k=>$v){
            $read[$k]=str_replace($seach,$replace,$v);
        }
        file_put_contents($dest.DIRECTORY_SEPARATOR.$value,$read);
        //替换文件名称
        $old=$dest.DIRECTORY_SEPARATOR.$value;//旧文件的路径
        if (strpos($old,$seach) && !is_dir($value)){
            //新文件路径
//            $new=$dest.DIRECTORY_SEPARATOR.dirname($value).DIRECTORY_SEPARATOR.$replace.'.'.pathinfo($old,PATHINFO_EXTENSION);
            $new=str_replace($seach,$replace,$old);
//            //检测原文件是否存在
            if (!is_file($old)){
                continue;
            }
            //重命名文件
            rename($old,$new);
        }
    }
}
echo "<pre/>";
$seach=$_GET['seach'];
$replace=$_GET['replace'];
if (!empty($seach)&&!empty($replace)){
    edit_file_name('../../file',$seach,$replace);
}
//var_dump(read_file_array('../../file/test.php',false));
//$read=read_file_array('../../file/test.php',false);
//        foreach ($read as $k=>$v){
//            $read[$k]=str_replace($seach,$replace,$v);
//        }
//file_put_contents('test.php',$read);
/**
 * 将文件夹的多维数组转换为一维数组
 * @param $data 数组
 * @return array    一维数组
 */
function transform_arr($data){
    $is_reload=false;
    $arr=[];
    foreach ($data as $key=>$value){
        if (is_array($value)){
            //是数组就循环将其中的数据遍历数来添加到一维数组里去
            foreach ($value as $k=>$v){
                //如果里面没有数组就直接添加到数组中去,如果有数组就将数组整个添加到一维数组里去，然后后面再次执行转换方法
                if (is_array($v)){
                    $arr[$key.DIRECTORY_SEPARATOR.$k]=$v;   //将路径作为key,后面好拼接路径
                    $is_reload=true;//是否再次执行方法
                }else{
                    $arr[]=$key.DIRECTORY_SEPARATOR.$v;
                }
            }
        }else{
            //不是数组直接添加到数组中去
            $arr[]=$value;
        }
    }
    if ($is_reload){
        $arr=transform_arr($arr);
    }
    return $arr;
}