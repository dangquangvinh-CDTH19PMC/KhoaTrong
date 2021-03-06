<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\ChamCong;
use App\Models\User;
use App\Models\Luong;
use App\Models\ChucVu;
use Illuminate\Support\Facades\Validator;
use Carbon\Carbon;
use App\Models\HopDong;
use App\Models\PhongBan;

class ChamCongController extends Controller
{
    public function index()
    {
        if(auth()->user()->chucVu->ten_chuc_vu == "Giám đốc")
        {
            $chucVu = ChucVu::where('ten_chuc_vu', 'Giám đốc')->first();
            $users =User::where('chuc_vu_id','!=', $chucVu->id)->pluck('id');
        }
        else
        {
            $phongBan = PhongBan::where('user_id', auth()->user()->id)->first();
            $users = User::where('phong_ban_id', $phongBan->id)->get()->pluck('id');
        }
        $chamCongs  = ChamCong::whereIn('user_id', $users)->get();
      
        return view('cham-cong/danh-sach',compact('chamCongs'));
    }

    public function create()
    {
        $now = Carbon::now()->format('d-m-Y');
        $userDaChamCong = ChamCong::where('ngay_lam','LIKE',"$now%")->get()->pluck('user_id')->unique()->sort();


        if(auth()->user()->chucVu->ten_chuc_vu == "Giám đốc")
        {
            $chucVu = ChucVu::where('ten_chuc_vu', 'Giám đốc')->first();
            $nguoi_dung =User::where('chuc_vu_id','!=', $chucVu->id);
        }
        else if(auth()->user()->chucVu->ten_chuc_vu == "Trưởng phòng")
        {
            $phongBan = PhongBan::where('user_id', auth()->user()->id)->first();
            $nguoi_dung = User::where('phong_ban_id', $phongBan->id);
        }
        else{
            $nguoi_dung =User::where('id',auth()->user()->id);
        }

        $users = $nguoi_dung->whereNotIn('id', $userDaChamCong)->get();

        return view('cham-cong/them-moi',compact('users'));
    }


    public function store(Request $request)
    {
    // return $request->all();
    $validator = Validator::make($request->all(), [
        'user_id'        => 'required',
        ],
        [   
            'user_id.required'      => 'Chưa chọn nhân viên',
        ]
    );
    if ($validator->fails()) {
        return back()->with('error', $validator->messages()->first());
    }
    $luong = HopDong::where('user_id',$request->user_id)->orderBy('id','desc')->first();
    if($luong != null) {
        $chamCong = new ChamCong();
        $chamCong->user_id = $request->user_id;
        $chamCong->ngay_lam = Carbon::now()->format('d-m-Y H:m:s');
        $chamCong->save();
        if($chamCong)
        {
            $user = User::find((integer) $request->user_id)->chucVu;
            if(empty($user))
            {
                return back()->with('error','Nhân viên này chưa có chức vụ');
            }
            $ngayLam = Carbon::now()->format('m-Y');
            $tongNgayLam = ChamCong::where('user_id',$request->user_id)->where('ngay_lam','LIKE',"%$ngayLam%")->count();
            $luongUser = Luong::where('user_id',$request->user_id)->where('thang_nam',$ngayLam)->first();
            $luong = HopDong::where('user_id',$request->user_id)->orderBy('id','desc')->first();

            if(empty($luongUser))
            {
                Luong::create(
                    [
                        'user_id'    =>(integer) $request->user_id,
                        'tong_ngay_lam'   =>  $tongNgayLam ,
                        'tam_ung'       => 0,
                        'phu_cap'                 => 0,
                        'khen_thuong'       => 0,
                        'ky_luat'                 => 0,
                        'thang_nam'     => Carbon::now()->format('m-Y'),
                        'tong_luong' =>  $tongNgayLam* $luong->luong,
                    ]
                );
            }
            else{
                $luongUser->tong_ngay_lam = $tongNgayLam;
                $luongUser->tong_luong =  (float)$luongUser->tong_ngay_lam * (float)$luong->luong
                + (float)$luongUser->phu_cap - (float)$luongUser->tam_ung
                +(float)$luongUser->khen_thuong - (float)$luongUser->ky_luat;
                $luongUser->save();
            }
        }
        }
        else
        {
            return back()->with('error','Nhân viên này chưa có hợp đồng');
        }
        return redirect()->route('danh_sach_cham_cong')->with('status','Bạn đã chấm công thành công!');
    }

    // public function edit($id)
    // {
    //    $chamCong=ChamCong::find($id);
    //    $now = Carbon::now()->format('d-m-Y');
    //    $userDaChamCong = ChamCong::where('ngay_lam','LIKE',"$now%")->get()->pluck('user_id')->unique()->sort();
       
    //    if(auth()->user()->chucVu->ten_chuc_vu == "Giám đốc")
    //     {
    //         $nguoi_dung =User::where('id','>',0);
    //     }
    //     else if(auth()->user()->chucVu->ten_chuc_vu == "Trưởng phòng")
    //     {
    //         $phongBan = PhongBan::where('user_id', auth()->user()->id)->first();
    //         $nguoi_dung = User::where('phong_ban_id', $phongBan->id);
    //     }
    //     else{
    //         $nguoi_dung =User::where('id',auth()->user()->id);
    //     }

    //    $users = $nguoi_dung->whereNotIn('id', $userDaChamCong)->get();

    //     if($chamCong==null)
    //    {
    //        return redirect()->route('danh_sach_cham_cong')->with('error','Không tìm thấy ngày chấm công này');
    //    }
    //    return view('cham-cong/cap-nhat', compact('chamCong','users'));   
    // }


    // public function update(Request $request,$id)
    // {
    //     $validator = Validator::make($request->all(), [
    //         'user_id'        => 'required',
    //         ],
    //         [   
    //             'user_id.required'      => 'Chưa chọn nhân viên',
    //         ]
    //     );
    //     if ($validator->fails()) {
    //         return back()->with('error', $validator->messages()->first());
    //     }
    //        $chamCong = ChamCong::find($id);
    //        $chamCong->user_id = $request->user_id;
    //        $chamCong->ngay_lam = Carbon::now()->format('d-m-Y H:m:s');
    //        $chamCong->save();
    //     if($chamCong)
    //     {
    //         $luong = HopDong::where('user_id',$request->user_id)->orderBy('id','desc')->first();
    //         $ngayLam = Carbon::now()->format('m-Y');
    //         $tongNgayLam = ChamCong::where('user_id',$request->user_id)->where('ngay_lam','LIKE',"%$ngayLam%")->count();
    //         $user = User::find((integer) $request->user_id)->chucVu;
    //         if(empty($user))
    //         {
    //             return back()->with('error','Nhân viên này chưa có chức vụ');
    //         }
            
    //         $luongUser = Luong::where('user_id',$request->user_id)->where('thang_nam',$ngayLam)->first();
    //         $luongUser->tong_ngay_lam = $tongNgayLam;
    //         $luongUser->tong_luong = (float)$luongUser->tong_ngay_lam * (float)$luong->luong
    //         + (float)$luongUser->phu_cap - (float)$luongUser->tam_ung
    //         +(float)$luongUser->khen_thuong - (float)$luongUser->ky_luat;
    //         $luongUser->save();
    //     }
    //     return redirect()->route('danh_sach_cham_cong')->with('status','Bạn đã cập nhật thành công! ');

    // }
    public function destroy($id)
    {
        try {
            ChamCong::destroy($id);
            return redirect()->route('danh_sach_cham_cong')->with('error','Xoá thành công');

        } catch (Exception $e) {
            return redirect()->route('danh_sach_cham_cong')->with('error','Xoá không thành công');

        }
    }
}
