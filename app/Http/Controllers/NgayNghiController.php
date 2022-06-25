<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\NgayNghi;
use App\Models\User;
use Illuminate\Support\Facades\Validator;
use Carbon\Carbon;

class NgayNghiController extends Controller
{
    public function index()
    {
        $ngayNghis  = NgayNghi::where('trang_thai','Duyệt')->get();
      
        return view('ngay-nghi/danh-sach',compact('ngayNghis'));
    }
    public function danh_sach_ngay_nghi_cho_duyet()
    {
        $ngayNghis  = NgayNghi::where('trang_thai','Chưa duyệt')->get();
      
        return view('ngay-nghi/danh-sach-cho-duyet',compact('ngayNghis'));
    }
    

    public function duyet_don_nghi($id)
    {
   
      $update = NgayNghi::find($id);
      $update->trang_thai = 'Duyệt';
      $update->save();
      return redirect()->route('danh_sach_ngay_nghi')->with('status','Cập nhật thành công');

    }

    public function create()
    {
        $users =User::all();
        return view('ngay-nghi/them-moi',compact('users'));
    }

    
    public function store(Request $request)
    {
    // return $request->all();
    $validator = Validator::make($request->all(), [
        'user_id'        => 'required',
        'ngay_bat_dau_nghi'        => 'required',
        'ngay_di_lam_lai'        => 'required',
        'ly_do'        => 'required',

        ],
        [   
            'user_id.required'      => 'Chưa chọn nhân viên',
            'ngay_bat_dau_nghi.required'       => 'Chưa chọn ngày bắt đầu nghỉ',
            'ngay_di_lam_lai.required'       => 'Chưa chọn ngày đi làm lại',
            'ly_do.required'       => 'Chưa nhập lí do',

        ]
    );
    if ($validator->fails()) {
        return back()->with('error', $validator->messages()->first());
    }
        NgayNghi::create(
            [
                'user_id'    =>(integer) $request->user_id,
                'ngay_bat_dau_nghi'   => $request->ngay_bat_dau_nghi,
                'ngay_di_lam_lai'       => $request->ngay_di_lam_lai,
                'ly_do'                 => $request->ly_do,
                // 'total_day'     => Carbon::parse($request->ngay_bat_dau_nghi)->diffInDays(Carbon::parse($request->ngay_di_lam_lai)),
                'trang_thai'        => 'Chưa duyệt'
            ]
        );
        return redirect()->route('danh_sach_ngay_nghi')->with('status','Thêm mới thành công');
    }

    public function edit($id)
    {
       $NgayNghi=NgayNghi::find($id);
       if($NgayNghi==null)
       {
           return redirect()->route('danh_sach_ngay_nghi')->with('error','Không tìm thấy ngày nghỉ này');
       }
       return view('ngay-nghi/cap-nhat', compact('NgayNghi'));   
    }


    public function update(Request $request,$id)
    {
        // return $request->all();
        $validator = Validator::make($request->all(), [
            'user_id'        => 'required',
            'ngay_bat_dau_nghi'        => 'required',
            'ngay_di_lam_lai'        => 'required',
            'ly_do'        => 'required',
    
            ],
            [   
                'user_id.required'      => 'Chưa chọn nhân viên',
                'ngay_bat_dau_nghi.required'       => 'Chưa chọn ngày bắt đầu nghỉ',
                'ngay_di_lam_lai.required'       => 'Chưa chọn ngày đi làm lại',
                'ly_do.required'       => 'Chưa nhập lí do',
    
            ]
        );
        if ($validator->fails()) {
            return back()->with('error', $validator->messages()->first());
        }
      $update = NgayNghi::find($id);
      if(empty($update))
      {
        return redirect()->route('danh_sach_ngay_nghi')->with('error','Không tìm thấy ngày nghỉ này');
      }
      $update->ngay_bat_dau_nghi = $request->ngay_bat_dau_nghi;
      $update->ngay_di_lam_lai = $request->ngay_di_lam_lai;
      $update->ly_do = $request->ly_do;

      $update->save();
      return redirect()->route('danh_sach_ngay_nghi')->with('status','Cập nhật thành công');

    }


    public function destroy(Request $request)
    {
        try {
            NgayNghi::destroy($request->id);
            return redirect()->route('danh_sach_ngay_nghi')->with('status','Xoá thành công');

        } catch (Exception $e) {
            return redirect()->route('danh_sach_ngay_nghi')->with('error','Xoá không thành công');

        }
    }
}
