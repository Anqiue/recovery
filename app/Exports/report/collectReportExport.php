<?php

namespace App\Exports\report;

use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;
use PhpOffice\PhpSpreadsheet\Shared\StringHelper;//string格式转换助手类

class collectReportExport implements FromView
{
    protected $collectReport;
    protected $type;

    public function __construct($collectReport)
    {
        $this->collectReport = $collectReport;
    }
    /**
    * @return \Illuminate\Support\Collection
    */
    public function view(): View
    {
        $data = $this->collectReport;
        foreach ($data['datalist'] as $key=>$val){
            $val['total_price'] = '￥'.$val['total_price'];
            $data['datalist'][$key] = $val;
        }
        $data['total']['all_total_price'] = '￥'.$data['total']['all_total_price'];
        return view('admin.report.operation_report_export', [
            'collectReport' => $data,
        ]);
    }
}
