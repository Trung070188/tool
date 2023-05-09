<?php

namespace App\Http\Controllers\Admin;

use App\Models\Customer;
use Carbon\Carbon;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use App\Models\DebtSettle;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;


class DebtSettlesController extends AdminBaseController
{

    /**
     * Index page
     * @uri  /xadmin/debt_settle/index
     * @throw  NotFoundHttpException
     * @return  View
     */
    public function index(Request $req)
    {
        $title = 'DebtSettle';
        $component = 'DebtSettleIndex';
        $customer = Customer::query()->orderBy('id', 'desc')->get();
        $jsonData = compact('customer');
        return vue(compact('title', 'component'), $jsonData);
    }
    public function debt(Request $req)
    {
        $title = 'DebtSettle';
        $component = 'DebtIndex';
        $customer = Customer::query()->orderBy('id', 'desc')->get();
        $jsonData = compact('customer');
        return vue(compact('title', 'component'), $jsonData);
    }

    public function editDebt(Request $req)
    {

        $id = $req->id;
        $entry = DebtSettle::find($id);

        if (!$entry) {
            throw new NotFoundHttpException();
        }

        /**
         * @var  DebtSettle $entry
         */
        $jsonData = compact('entry');
        $component = 'DebtForm';
        $title = 'Chỉnh sửa công nợ';

        return vue(compact('title', 'component'), $jsonData);
    }

    public function payDetail(Request $req)
    {
        $title = 'PayDetail';
        $component = 'PayDetail';
        $year = $req->year;
        $month =  $req->month;
        $customer = Customer::query()->orderBy('id', 'desc')->get();
        $jsonData = compact('year','month','customer');
        return vue(compact('title','component'),$jsonData);
    }

    /**
     * thống kê công nợ
     */
    public function statistical()
    {
        $title = 'Statistical';
        $component = 'PayStatistical';
        return vue(compact('title', 'component'));
    }

    /**
     * Create new entry
     * @uri  /xadmin/debt_settle/create
     * @throw  NotFoundHttpException
     * @return  View
     */
    public function create(Request $req)
    {
        $component = 'DebtSettleForm';
        $title = 'Create debt settle';
        return vue(compact('title', 'component'));
    }
    public function DebtCreate(Request $req)
    {
        $component = 'DebtForm';
        $title = 'Create debt settle';
        return vue(compact('title', 'component'));
    }

    /**
     * @uri  /xadmin/debt_settle/edit?id=$id
     * @throw  NotFoundHttpException
     * @return  View
     */
    public function edit(Request $req)
    {
        $id = $req->id;
        $entry = DebtSettle::find($id);

        if (!$entry) {
            throw new NotFoundHttpException();
        }

        /**
         * @var  DebtSettle $entry
         */
        $jsonData = compact('entry');
        $title = 'Edit';
        $component = 'DebtSettleForm';

        return vue(compact('title', 'component'), $jsonData);
    }

    /**
     * @uri  /xadmin/debt_settle/remove
     * @return  array
     */
    public function remove(Request $req)
    {
        $id = $req->id;
        $entry = DebtSettle::find($id);

        if (!$entry) {
            throw new NotFoundHttpException();
        }

        $entry->delete();

        return [
            'code' => 0,
            'message' => 'Đã xóa'
        ];
    }

    /**
     * @uri  /xadmin/debt_settle/save
     * @return  array
     */
    public function save(Request $req)
    {
        if (!$req->isMethod('POST')) {
            return ['code' => 405, 'message' => 'Method not allow'];
        }

        $data = $req->get('entry');

        $rules = [
            'customer_id' => 'numeric',
        ];

        $v = Validator::make($data, $rules);

        if ($v->fails()) {
            return [
                'code' => 2,
                'errors' => $v->errors()
            ];
        }

        /**
         * @var  DebtSettle $entry
         */
        if (isset($data['id'])) {
            $entry = DebtSettle::find($data['id']);
            if (!$entry) {
                return [
                    'code' => 3,
                    'message' => 'Không tìm thấy',
                ];
            }
            $entry->fill($data);
            $entry->save();

            return [
                'code' => 0,
                'message' => 'Đã cập nhật',
                'id' => $entry->id
            ];
        } else {
            $entry = new DebtSettle();
            $entry->fill($data);
            $entry->save();

            return [
                'code' => 0,
                'message' => 'Đã thêm',
                'id' => $entry->id
            ];
        }
    }

    /**
     * @param Request $req
     */
    public function toggleStatus(Request $req)
    {
        $id = $req->get('id');
        $entry = DebtSettle::find($id);

        if (!$id) {
            return [
                'code' => 404,
                'message' => 'Not Found'
            ];
        }

        $entry->status = $req->status ? 1 : 0;
        $entry->save();

        return [
            'code' => 200,
            'message' => 'Đã lưu'
        ];
    }

    /**
     * data thông kê chi tiết dữ liệu công nợ theo tháng
     * Ajax data for index page
     * @uri  /xadmin/debt_settle/data
     * @return  array
     */
    public function data(Request $req)
    {
        $query = DB::table('debt_settle')
            ->leftJoin('customers', 'debt_settle.customer_id', '=', 'customers.id')
            ->select('debt_settle.customer_id',
                DB::raw('SUM(debt_settle.cost_incurr) as cost_incurr'),
                DB::raw('SUM(debt_settle.pay_booking) as pay_booking'),
                DB::raw('SUM(debt_settle.pay_debt) as pay_debt'),
                DB::raw('IF(debt_settle.customer_id IS NULL, NULL, customers.id) as customer_id'),
                'debt_settle.created_at as created_at',
                'debt_settle.updated_at as updated_at',
                'debt_settle.id as id',
                DB::raw('IF(debt_settle.customer_id IS NULL, NULL, customers.name) as customer_name'),
                'debt_settle.note as note'
                )
            ->where(function ($q) use ($req) {
                if (!empty($req->month) && !empty($req->year)) {
                    $q->where('debt_settle.year', $req->year)
                        ->where('debt_settle.month', $req->month);
                } elseif (!empty($req->year)) {
                    $q->where('debt_settle.year', $req->year);
                }
            })
            ->groupBy('debt_settle.customer_id');

//        $query->select([
//            DB::raw('IF(debt_settle.customer_id IS NULL, NULL, customers.id) as customer_id'),
//            'debt_settle.created_at as created_at',
//            'debt_settle.updated_at as updated_at',
//            'debt_settle.id as id',
//            DB::raw('IF(debt_settle.customer_id IS NULL, NULL, customers.name) as customer_name'),
//            'debt_settle.pay_booking as pay_booking',
//            'debt_settle.pay_debt as pay_debt',
//            'debt_settle.note as note'
//        ])
//            ->groupBy('debt_settle.id')
//            ->orderBy('id', 'desc');

        if ($req->keyword) {
            $query->where('customers.name', 'LIKE', '%' . $req->keyword . '%')
                ->orWhere('customers.id', $req->keyword);
        }
        if($req->customer)
        {
            $query->where('customers.id', $req->customer);
        }

        return [
            'code' => 0,
            'data' => $query->get(),
//            'paginate' => [
//                'currentPage' => $entries->currentPage(),
//                'lastPage' => $entries->lastPage(),
//            ]
        ];
    }

    /**
     * data khách hàng trả tiền
     */
    public function dataCustomer(Request $req)
    {
        $req->year = $req->year ?? Carbon::now()->year;
        $req->month = $req->month ?? Carbon::now()->month;
        {
            $query = DebtSettle::query()->with(['customer:id,name'])
               ->orderBy('due_date','desc')->orderBy('created_at','desc');
            if($req->year !=0 && $req->month !=0)
            {
                $query->where('year', $req->year)->where('month', $req->month);
            }
            elseif ($req->year !=0)
            {
                $query->where('year', $req->year);
            }
            elseif ($req->month !=0)
            {
                $query->where('month', $req->month);
            }
            if($req->customer_id)
            {
                $query->where('customer_id', $req->customer_id);
            }
            if($req->created)
            {
                $dates = $req->created;
                $date_range = explode('_', $dates);
                $start_date = $date_range[0];
                $start_date = date('Y-m-d 00:00:00', strtotime($start_date));
                $end_date = $date_range[1];
                $end_date = date('Y-m-d 23:59:59', strtotime($end_date));
                $query->whereBetween('created_at', [$start_date, $end_date]);
            }

            $data = [];
            foreach ($query->get()->toArray() as $entry)
            {
                $data[] = [
                    'id' => $entry['id'],
                    'customer_id' => $entry['customer']['id'] ?? '',
                    'customer_name' => $entry['customer']['name'] ?? '',
                    'pay_debt' => $entry['pay_debt'] ?? '',
                    'cost_incurr' => $entry['cost_incurr'] ?? '',
                    'month' => $entry['month'] ?? 'Chưa xác định',
                    'year' => $entry['year'] ?? 'Chưa xác định',
                    'due_date' => $entry['due_date'] ?? '',
                    'created_at' => $entry['created_at'] ?? ''
                ];
            }
            return [
                'code' => 200,
                'data' => $data
            ];
        }
    }

    /**
     * @param Request $req
     * @return array|void
     * data thống kê tiền khách hàng booking
     */
    public function dataDebt(Request $req)
    {
        $req->year = $req->year ?? Carbon::now()->year;
        $req->month = $req->month ?? Carbon::now()->month;
        {
            $query = DebtSettle::query()->with(['customer:id,name'])
                ->where('pay_booking', '<>', 0);
            if($req->year !=0 && $req->month !=0)
            {
                $query->where('year', $req->year)->where('month', $req->month);
            }
            elseif ($req->year !=0)
            {
                $query->where('year', $req->year);
            }
            elseif ($req->month !=0)
            {
                $query->where('month', $req->month);
            }
            if($req->created)
            {
                $dates = $req->created;
                $date_range = explode('_', $dates);
                $start_date = $date_range[0];
                $start_date = date('Y-m-d 00:00:00', strtotime($start_date));
                $end_date = $date_range[1];
                $end_date = date('Y-m-d 23:59:59', strtotime($end_date));
                $query->whereBetween('created_at', [$start_date, $end_date]);
            }
            if($req->customer_id)
            {
                $query->where('customer_id', $req->customer_id);
            }
            $data = [];
            foreach ($query->get()->toArray() as $entry) {
                $data[] = [
                    'id' => $entry['id'],
                    'customer_id' => $entry['customer']['id'] ?? '',
                    'customer_name' => $entry['customer']['name'] ?? '',
                    'pay_booking' => $entry['pay_booking'] ?? '',
                    'month' => $entry['month'] ?? 'Chưa xác định',
                    'year' => $entry['year'] ?? 'Chưa xác định',
                    'created_at' => $entry['created_at'] ?? ''
                ];
            }
            return [
                'code' => 200,
                'data' => $data
            ];
        }

    }

    public function dataCreate(Request $req)
    {
        $customers = Customer::query()->orderBy('id', 'desc')->get();
        return [
            'customers' => $customers
        ];
    }

    public function dataEdit(Request $req)
    {
        $customer = Customer::query()->where('id', $req->customer)->first();
        $listCustomers = Customer::query()->orderBy('id', 'desc')->get();

        return [
            'customer' => $customer,
            'listCustomer' => $listCustomers
        ];

    }

    public function export()
    {
        $keys = [
            'customer_id' => ['A', 'customer_id'],
            'pay_booking' => ['B', 'pay_booking'],
            'pay_debt' => ['C', 'pay_debt'],
        ];

        $query = DebtSettle::query()->orderBy('id', 'desc');

        $entries = $query->paginate();
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();

        foreach ($keys as $key => $v) {
            if (is_string($v)) {
                $sheet->setCellValue($v . "1", $key);
            } elseif (is_array($v)) {
                list($c, $n) = $v;
                $sheet->setCellValue($c . "1", $n);
            }
        }

        foreach ($entries as $index => $entry) {
            $idx = $index + 2;
            foreach ($keys as $key => $v) {
                if (is_string($v)) {
                    $sheet->setCellValue("$v$idx", data_get($entry->toArray(), $key));
                } elseif (is_array($v)) {
                    list($c, $n) = $v;
                    $sheet->setCellValue("$c$idx", data_get($entry->toArray(), $key));
                }
            }
        }
        $writer = new Xlsx($spreadsheet);
        // We'll be outputting an excel file
        header('Content-type: application/vnd.ms-excel');
        $filename = uniqid() . '-' . date('Y_m_d H_i') . ".xlsx";

        // It will be called file.xls
        header('Content-Disposition: attachment; filename="' . $filename . '"');

        // Write file to the browser
        $writer->save('php://output');
        die;
    }

    public function dataStatistical(Request $req)
    {
        for($i = 1 ;$i <= 12; $i ++)
        {
            $payments [] = DebtSettle::query()->where('year', $req->year)->where('month', $i)->get()->toArray();

        }
        $dataPayments = []; // khởi tạo mảng chứa tổng pay_booking của từng tháng
        foreach ($payments as $key => $paymentMonth) {
            $bookingTotal = 0; // khởi tạo biến tính tổng pay_booking của mỗi mảng
            $debtTotal = 0;
            foreach ($paymentMonth as $payment) {
                $bookingTotal += $payment['pay_booking']; // tính tổng pay_booking
                $debtTotal += $payment['pay_debt'];

            }
           $dataPayments[] = [
             'month' => $key+1,
             'bookingTotal' => $bookingTotal,
             'debtTotal' => $debtTotal
           ];

        }
       return [
         'code'=> 200,
         'data'=> $dataPayments
       ];




    }
}
