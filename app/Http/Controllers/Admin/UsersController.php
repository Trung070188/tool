<?php

namespace App\Http\Controllers\Admin;

use App\Models\Role;
use App\Models\UserRole;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use App\Models\User;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;


class UsersController extends AdminBaseController
{

    /**
    * Index page
    * @uri  /xadmin/users/index
    * @throw  NotFoundHttpException
    * @return  View
    */
    public function index()
    {
        $title = 'User';
        $component = 'UserIndex';
        return vue(compact('title', 'component'));
    }

    /**
    * Create new entry
    * @uri  /xadmin/users/create
    * @throw  NotFoundHttpException
    * @return  View
    */
    public function create (Request $req)
    {
        $roles=Role::query()->orderBy('id','desc')->get();
        $jsonData = compact('roles');
        $component = 'UserForm';
        $title = 'Create users';
        return vue(compact('title', 'component'),$jsonData);
    }

    /**
    * @uri  /xadmin/users/edit?id=$id
    * @throw  NotFoundHttpException
    * @return  View
    */
    public function edit (Request $req)
    {
        $id = $req->id;
        $entry = User::with(['roles'])->find($id);
        $roles=Role::query()->orderBy('id','desc')->get();
       @$role=$entry->roles->role_id;

        if (!$entry) {
            throw new NotFoundHttpException();
        }

        /**
        * @var  User $entry
        */
        $jsonData = compact('entry','roles',@'role');
        $title = 'Edit';
        $component = 'UserForm';

        return vue(compact('title', 'component'), $jsonData);
    }

    /**
    * @uri  /xadmin/users/remove
    * @return  array
    */
    public function remove(Request $req)
    {
        $id = $req->id;
        $entry = User::find($id);

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
    * @uri  /xadmin/users/save
    * @return  array
    */
    public function save(Request $req)
    {
        if (!$req->isMethod('POST')) {
            return ['code' => 405, 'message' => 'Method not allow'];
        }

        $data = $req->get('entry');

        $rules = [
    'code' => 'max:100',
    'username' => 'required|max:100',
    'name' => 'required|max:100',
    'birthday' => 'max:20',
    'phone' => 'max:20',
    'email' => 'required|max:50',
    'address' => 'max:255',
    'status' => 'required|numeric',
    'type' => 'numeric',
//    'created_by' => 'numeric',
//    'updated_by' => 'numeric',
];

        $v = Validator::make($data, $rules);

        if ($v->fails()) {
            return [
                'code' => 2,
                'errors' => $v->errors()
            ];
        }

        /**
        * @var  User $entry
        */
        if (isset($data['id'])) {
            $entry = User::find($data['id']);
            if (!$entry) {
                return [
                    'code' => 3,
                    'message' => 'Không tìm thấy',
                ];
            }

            $entry->fill($data);
            $entry->save();
            UserRole::query()->where('user_id',$entry->id)->delete();
                UserRole::create(['user_id'=>$entry->id,'role_id'=>$req->role]);
            return [
                'code' => 0,
                'message' => 'Đã cập nhật',
                'id' => $entry->id
            ];
        } else {
            $entry = new User();
            $entry->fill($data);
            $entry->save();
            UserRole::create(['user_id'=>$entry->id,'role_id'=>$req->role]);
            return [
                'code' => 0,
                'message' => 'Đã thêm',
                'id' => $entry->id
            ];
        }
    }

    /**
    * @param  Request $req
    */
    public function toggleStatus(Request $req)
    {
        $id = $req->get('id');
        $entry = User::find($id);

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
    * Ajax data for index page
    * @uri  /xadmin/users/data
    * @return  array
    */
    public function data(Request $req)
    {
        $query = User::query()->with(['role'])->orderBy('id', 'desc');

        if ($req->keyword) {
            //$query->where('title', 'LIKE', '%' . $req->keyword. '%');
        }

        $query->createdIn($req->created);

        $entries = $query->paginate();
        $data=[];
        foreach ($entries as $entry)
        {
            $role_name='';
           foreach ($entry->role as $role)
           {
               $role_name=$role->name;
           }
           $data[]=[
               'id'=>$entry->id,
               'sap_id'=>$entry->id,
               'username'=>$entry->username,
               'name'=>$entry->name,
               'birthday'=>$entry->birthday,
               'phone'=>$entry->phone,
               'email'=>$entry->email,
               'address'=>$entry->address,
               'status'=>$entry->status,
               'type'=>$entry->type,
               'created_by'=>$entry->created_by,
               'role_name'=>$role_name,
           ];
        }

        return [
            'code' => 0,
            'data' => $data,
            'paginate' => [
                'currentPage' => $entries->currentPage(),
                'lastPage' => $entries->lastPage(),
            ]
        ];
    }

    public function export()
    {
                $keys = [
                            'code' => ['A', 'code'],
                            'username' => ['B', 'username'],
                            'name' => ['C', 'name'],
                            'birthday' => ['D', 'birthday'],
                            'phone' => ['E', 'phone'],
                            'email' => ['F', 'email'],
                            'address' => ['G', 'address'],
                            'status' => ['H', 'status'],
                            'type' => ['I', 'type'],
                            'created_by' => ['J', 'created_by'],
                            'updated_by' => ['K', 'updated_by'],
                            ];

        $query = User::query()->orderBy('id', 'desc');

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
}
