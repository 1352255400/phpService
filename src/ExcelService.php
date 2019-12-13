<?php

namespace phpService;

/**
 * [ExcelService phpExcel解析]
 * @Author   W_wang
 * @email    1352255400@qq.com
 * @DateTime 2018-04-17T13:51:51+0800
 */
class ExcelService
{
    public function __construct()
    {
        set_time_limit(0); //设置页面等待时间
        ini_set('memory_limit', '-1');//不限制内存
    }

    /**
     * 解析Excel
     * @param array $file
     * @param int $sheet
     * @return array
     * @throws \PHPExcel_Exception
     * @throws \PHPExcel_Reader_Exception
     */
    public function import($file = [], $sheet = 0)
    {
        if (is_array($file)) {
            //检查文件
            if (!isset($file['tmp_name']) || empty($file['tmp_name'])) {
                return ['code' => '1000', 'data' => $file, 'msg' => '请上传Excel文件'];
            }
            $file = $file['tmp_name'];
        } else {
            if (!file_exists($file)) {
                return ['code' => '1000', 'data' => $file, 'msg' => '文件不存在'];
            }
        }

        //实例化
        $PHPExcel = new \PHPExcel();

        // 默认用excel2007读取excel，若格式不对，则用之前的版本进行读取 */
        $PHPReader = new \PHPExcel_Reader_Excel2007();
        if (!$PHPReader->canRead($file)) {
            $PHPReader = new \PHPExcel_Reader_Excel5();
            if (!$PHPReader->canRead($file)) {
                return ['code' => '1000', 'data' => $file, 'msg' => '读取Excel文件失败'];
            }
        }

        //读取Excel内容
        $phpExcel = $PHPReader->load($file);
        //$objWriter = new \PHPExcel_Writer_Excel5($PHPExcel);
        //读取excel文件中的第一个工作表
        $sheet = intval($sheet);
        $currentSheet = $phpExcel->getSheet($sheet);
        //取得最大的列号
        $allColumn = $currentSheet->getHighestColumn();
        //取得一共有多少行
        $allRow = $currentSheet->getHighestRow();
        //声明数组
        $excelResult = array();
        //从第一行开始读取数据
        $startRow = 1;
        $endRow = $allRow;
        for ($j = $startRow; $j <= $endRow; $j++) {
            //从A列读取数据
            for ($k = 'A'; $k <= $allColumn; $k++) {
                // 读取单元格
                $excelResult[$j][] = (string)$phpExcel->getActiveSheet()->getCell("$k$j")->getValue();
            }
        }
        return ['code' => '000', 'data' => $excelResult, 'msg' => 'ok'];
    }


    /**
     * 导出Excel
     * @param string $file_name
     * @param array $expTableData
     * @param string $save_path
     * @return array
     * @throws \PHPExcel_Exception
     * @throws \PHPExcel_Reader_Exception
     * @throws \PHPExcel_Writer_Exception
     */
    public function export($file_name = 'Excel', $expTableData = [], $save_path = '')
    {
        //模拟数据
        /*$expTableData[] = [
            'name' => '测试1',//sheet名称
            'title' => ['标题1', '标题2', '标题3'], //标题
            'data' => [['a1', 'b1', 'c1'], ['aa1', 'bb1', 'cc1']] //内容
        ];
        $expTableData[] = [
            'name' => '测试2',
            'title' => ['标题11', '标题22', '标题33'],
            'data' => [['a2', 'b2', 'c2'], ['aa2', 'bb2', 'cc2']],
            'color' => 'FFCC0001',//字体颜色
            'color_row' => [['row' => 1, 'col_num' => 1], ['row' => 3, 'col_num' => 2]] //row第几行，col_num列数
        ];*/

        //列名
        $cellName = [
            'A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', 'I', 'J', 'K', 'L', 'M', 'N', 'O', 'P', 'Q', 'R', 'S', 'T', 'U',
            'V', 'W', 'X', 'Y', 'Z', 'AA', 'AB', 'AC', 'AD', 'AE', 'AF', 'AG', 'AH', 'AI', 'AJ', 'AK', 'AL', 'AM',
            'AN', 'AO', 'AP', 'AQ', 'AR', 'AS', 'AT', 'AU', 'AV', 'AW', 'AX', 'AY', 'AZ'
        ];

        //实例化
        $obpe = new \PHPExcel();
        //设置文档基本属性
        $obpe_pro = $obpe->getProperties();
        $obpe_pro->setCreator('midoks')->setLastModifiedBy('2013/2/16 15:00')->setTitle('data')->setSubject('beizhu')->setDescription('miaoshu')->setKeywords('keyword')->setCategory('catagory');
        //设置宽度
        //$obpe->getActiveSheet()->getColumnDimension()->setAutoSize(true);
        //$obpe->getActiveSheet()->getColumnDimension('B')->setWidth(10);

        //写入数据
        if (!empty($expTableData)) {
            foreach ($expTableData as $k => $v) {
                $name = isset($v['name']) && !empty($v['name']) ? $v['name'] : 'sheet' . $k;
                $title = isset($v['title']) && !empty($v['title']) ? $v['title'] : [];
                $data = isset($v['data']) && !empty($v['data']) ? $v['data'] : [];
                $color = isset($v['color']) && !empty($v['color']) ? $v['color'] : 'FFCC0001';
                $color_row = isset($v['color_row']) && !empty($v['color_row']) ? $v['color_row'] : [];

                //创建一个新的工作空间(sheet)
                $obpe->createSheet();
                $obpe->setactivesheetindex($k);
                //设置SHEET名称
                $obpe->getActiveSheet()->setTitle($name);
                //设置颜色
                if (!empty($color_row)) {
                    foreach ($color_row as $row) {
                        $row_color = $cellName[0] . $row['row'] . ':' . $cellName[$row['col_num']] . $row['row'];
                        $obpe->setActiveSheetIndex($k)->getStyle($row_color)->getFont()->getColor()->setARGB($color);
                    }
                }

                //5.设置表格头（即excel表格的第一行）
                if (!empty($title)) {
                    foreach ($title as $k_t => $v_t) {
                        $obpe->getActiveSheet()->setCellValue($cellName[$k_t] . '1', $v_t);
                    }
                }

                //6.循环数组，将数据逐一添加到excel表格。
                $i = 0;
                foreach ($data as $item) {
                    $item = array_values($item);
                    foreach ($item as $k_d => $v_d) {
                        $obpe->getActiveSheet()->setCellValue($cellName[$k_d] . ($i + 2), $v_d);
                    }
                    $i++;
                }
            }
        }
        //清空数据
        unset($expTableData);

        //写入类容
        $obwrite = \PHPExcel_IOFactory::createWriter($obpe, 'Excel5');

        //保存文件名称
        $file_name = urlencode($file_name . '.xls');
        //保存文件
        if (!empty($save_path)) {
            //检查目录是否存在
            if (file_exists($save_path)) {
                $save_path = $save_path . '/' . $file_name;
                $save_path = str_replace(['\/'], '/', $save_path);
                $save_path = str_replace(['//'], '/', $save_path);
                $obwrite->save($save_path);
                return ['code' => '000', 'data' => $save_path, 'msg' => 'ok'];
            } else {
                return ['code' => '1000', 'data' => $save_path, 'msg' => '保存目录不存在'];
            }
        } else {
            //直接在浏览器输出下载
            header('Pragma: public');
            header('Expires: 0');
            header('Cache-Control:must-revalidate,post-check=0,pre-check=0');
            header('Content-Type:application/force-download');
            header('Content-Type:application/vnd.ms-execl');
            header('Content-Type:application/octet-stream');
            header('Content-Type:application/download');
            header("Content-Disposition:attachment;filename=" . $file_name);
            header('Content-Transfer-Encoding:binary');
            $obwrite->save('php://output');
        }
    }

}
