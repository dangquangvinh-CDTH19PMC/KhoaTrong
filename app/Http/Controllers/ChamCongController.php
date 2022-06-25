<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\ChamCong;
use App\Models\User;
use Illuminate\Support\Facades\Validator;
use Carbon\Carbon;
use App\Models\Luong;


class ChamCongController extends Controller
{
    public function index()
    {
        $chamCongs  = ChamCong::all();
      
        return view('cham-cong/danh-sach',compact('chamCongs'));
    }

    public function create()
    {
        $now = Carbon::now()->format('d-m-Y');
        $userDaChamCong = ChamCong::where('ngay_lam','LIKE',"$now%")->get()->pluck('user_id')->unique()->sort();
        $users = User::whereNotIn('id', $userDaChamCong)->get();
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
        if(empty($luongUser))
        {
            Luong::create(
                [
                    'user_id'    =>(integer) $request->user_id,
                    'tong_ngay_lam'   =>  $tongNgayLam ,
                    'tam_ung'       => 0,
                    'phu_cap'                 => 0,
                    'thang_nam'     => Carbon::now()->format('m-Y'),
                    'tong_luong' =>  $tongNgayLam* $user->luong,
                ]
            );
        }
        else{
            $luongUser->tong_ngay_lam = $tongNgayLam;
            $luongUser->tong_luong = $tongNgayLam * $user->luong + (float)$luongUser->phu_cap - (float)$luongUser->tam_ung;
            $luongUser->save();
        }
       }
        return redirect()->route('danh_sach_cham_cong')->with('status','Bạn đã chấm công thành công!');
    }

    public function edit($id)
    {
       $chamCong=ChamCong::find($id);
       $now = Carbon::now()->format('d-m-Y');
       $userDaChamCong = ChamCong::where('ngay_lam','LIKE',"$now%")->get()->pluck('user_id')->unique()->sort();
       
       $users = User::whereNotIn('id', $userDaChamCong)->get();

        if($chamCong==null)
       {
           return redirect()->route('danh_sach_ngay_nghi')->with('error','Không tìm thấy ngày chấm công này');
       }
       return view('cham-cong/cap-nhat', compact('chamCong','users'));   
    }


    public function update(Request $request,$id)
    {
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
           $chamCong = ChamCong::find($id);
           $chamCong->user_id = $request->user_id;
           $chamCong->ngay_lam = Carbon::now()->format('d-m-Y H:m:s');
           $chamCong->save();
        if($chamCong)
        {
            $ngayLam = Carbon::now()->format('m-Y');
            $tongNgayLam = ChamCong::where('user_id',$request->user_id)->where('ngay_lam','LIKE',"%$ngayLam%")->count();
                $user = User::find((integer) $request->user_id)->chucVu;
                if(empty($user))
                {
                    return back()->with('error','Nhân viên này chưa có chức vụ');
                }
                
                $luongUser = Luong::where('user_id',$request->user_id)->where('thang_nam',$ngayLam)->fist();
                $luongUser->tong_ngay_lam = $tongNgayLam;
                $luongUser->tong_luong = $tongNgayLam * $user->luong + (float)$luongUser->phu_cap - (float)$luongUser->tam_ung;
                $luongUser->save();
        }
        return redirect()->route('danh_sach_cham_cong')->with('status','Bạn đã cập nhật thành công! ');

    }
    public function destroy(Request $request)
    {
        try {
            ChamCong::destroy($request->id);
            return redirect()->route('danh_sach_ngay_nghi')->with('error','Xoá thành công');

        } catch (Exception $e) {
            return redirect()->route('danh_sach_ngay_nghi')->with('error','Xoá không thành công');

        }
    }
}