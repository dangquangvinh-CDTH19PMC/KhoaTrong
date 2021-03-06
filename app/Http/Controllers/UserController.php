<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Models\PhongBan;
use App\Models\ChucVu;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;
use App\Models\HopDong;

class UserController extends Controller
{
    public function login()
    {
        return view('login');
    }

    public function doLogin(Request $request)
    {
        // dd($request->all());
        $credentials = $request->validate([
            'username' => ['required'],
            'password' => ['required']
        ]);

        if (Auth::attempt($credentials)) {
            $request->session()->regenerate();
            return redirect()->route('thong_ke');
        }

        return back()->with([
            'error' => 'Tên đăng nhập hoặc mật khẩu không đúng.',
        ]);
    }

    public function logout()
    {
        Auth::logout();
        return redirect()->route('login');
    }
    public function index()
    {
        if(auth()->user()->chucVu->ten_chuc_vu == "Giám đốc")
        {
            $chucVu = ChucVu::where('ten_chuc_vu', 'Giám đốc')->first();
            $users =User::where('chuc_vu_id','!=', $chucVu->id)->get();
        }
        else if(auth()->user()->chucVu->ten_chuc_vu == "Trưởng phòng")
        {
            $phongBan = PhongBan::where('user_id', auth()->user()->id)->first();
            $users = User::where('id','!=', auth()->user()->id)->where('phong_ban_id', $phongBan->id)->get();
        }
        else{
            $users =User::where('id',auth()->user()->id)->get();
        }
        if($users==null)
        {
            return back()->with('error','Không tìm thấy danh sách nhân viên');
        }
        return view('nhan-vien/danh-sach',compact('users'));
    }

    public function create()
    {
        if(auth()->user()->chucVu->ten_chuc_vu == "Giám đốc")
        {
            $phongBans =PhongBan::all();
            $chucVus =ChucVu::whereNotIn('ten_chuc_vu',['Trưởng phòng'])->get();
        }
        else if(auth()->user()->chucVu->ten_chuc_vu == "Trưởng phòng")
        {
            $phongBans =PhongBan::where('id', auth()->user()->phong_ban_id)->get();
            $chucVus =ChucVu::whereNotIn('ten_chuc_vu',['Trưởng phòng','Giám đốc'])->get();
        }
        else
        {
            $phongBans =PhongBan::where('id', auth()->user()->phong_ban_id)->get();
            $chucVus =ChucVu::where('id', auth()->user()->chuc_vu_id)->get();
        }
        return view('nhan-vien/them-moi',compact('phongBans','chucVus'));
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'ma_nhan_vien'  => 'required|max:191|unique:App\Models\User,ma_nhan_vien,NULL,id,deleted_at,NULL',
            'username'      => 'required|max:191|unique:App\Models\User,username,NULL,id,deleted_at,NULL',
            'password'      => 'required|min:6|max:191',
            'ho_ten'        => 'required|max:191',
            'cmnd'          => 'max:12|required',
            'dia_chi'       => 'max:191|required',
            'email'         => 'max:191|regex:/(.+)@(.+)\.(.+)/i|required',
            'ma_bhxh'       => 'max:10|',
            'chuc_vu_id'    => 'required',
            'phong_ban_id'  => 'required',
            ],
            [   
                'ma_nhan_vien.required'   => 'Chưa nhập mã nhân viên',
                'ma_nhan_vien.max'        => 'Mã nhân viên vượt quá 191 ký tự',
                'ma_nhan_vien.unique'     => 'Mã nhân viên đã tồn tại',
                'username.required'       => 'Chưa nhập tên đăng nhập',
                'username.unique'         => 'Tên đăng nhập đã tồn tại',
                'username.max'            => 'Tên đăng nhập vượt quá 191 ký tự',
                'password.required'       => 'Chưa nhập mật khẩu',
                'password.min'            => 'Mật khẩu chưa đủ 6 kí tự',
                'password.max'            => 'Mật khẩu vượt quá 191 kí tự',
                'ho_ten.required'         => 'Chưa nhập họ tên',
                'ho_ten.max'              => 'Họ tên vượt quá 191 kí tự',
                'cmnd.max'                => 'CMND/CCCD vượt quá 12 kí tự',
                'dia_chi.max'             => 'Địa chỉ vượt quá 191 kí tự',
                'email.max'               => 'Email vượt quá 191 kí tự',
                'email.regex'               => 'Email chưa đúng định dang',
                'cmnd.required'             => 'CMND không được để trống',
                'so_dien_thoai.integer'       => 'Số điện thoại không được nhập chữ',
                'chuc_vu_id.required'     => 'Chưa chọn chức vụ',
                'phong_ban_id.required'   => 'Chưa chọn phòng ban',
            ]
        );
        if ($validator->fails()) {
            return back()->with('error', $validator->messages()->first());
        }

        $user = new User();
        $user->ma_nhan_vien = $request->ma_nhan_vien;
        $user->username = $request->username;
        $user->password = Hash::make($request->password);
        $user->ho_ten = $request->ho_ten;
        $user->cmnd = $request->cmnd;
        $user->ngay_sinh = $request->ngay_sinh;
        $user->gioi_tinh = $request->gioi_tinh;
        $user->dia_chi = $request->dia_chi;
        $user->so_dien_thoai = $request->so_dien_thoai;
        $user->email = $request->email;
        $user->ma_bhxh = $request->ma_bhxh;
        $user->ngay_cap = $request->ngay_cap;
        $user->ngay_het_han = $request->ngay_het_han;
        $user->chuc_vu_id = $request->chuc_vu_id;
        $user->ngay_nhan_chuc = $request->ngay_nhan_chuc;
        if($request->chuc_vu_id==2)
        {
            
        }
        $user->phong_ban_id = $request->phong_ban_id;
        
        $user->save();
        return redirect()->route('danh_sach_nhan_vien')->with('status','Thêm mới nhân viên thành công');
    }

    public function edit($id)
    {
        $user = User::find($id);

        if(auth()->user()->chucVu->ten_chuc_vu == "Giám đốc")
        {
            $phongBans =PhongBan::all();
            $chucVus =ChucVu::whereNotIn('ten_chuc_vu',['Trưởng phòng'])->get();
        }
        else if(auth()->user()->chucVu->ten_chuc_vu == "Trưởng phòng")
        {
            $phongBans =PhongBan::where('id', auth()->user()->phong_ban_id)->get();
            $chucVus =ChucVu::where('ten_chuc_vu','Nhân viên')->get();
        }
        else
        {
            $phongBans =PhongBan::where('id', auth()->user()->phong_ban_id)->get();
            $chucVus = ChucVu::where('id', $user->chuc_vu_id)->get();
        }

        return view('nhan-vien/cap-nhat', compact('user','phongBans','chucVus'));   
    }

    public function update(Request $request,$id)
    {
        $validator = Validator::make($request->all(), [
            'ma_nhan_vien'  => "required|unique:App\Models\User,ma_nhan_vien,{$id},id,deleted_at,NULL",
            'ho_ten'        => 'required|max:191',
            'cmnd'          => 'max:191|required',
            'dia_chi'       => 'max:191|required',
            'email'         => 'max:191|required',
            'ma_bhxh'       => 'max:191',
            'chuc_vu_id'    => 'required',
            'phong_ban_id'  => 'required',
            ],
            [   
                'ma_nhan_vien.required'   => 'Chưa nhập mã nhân viên',
                'ma_nhan_vien.max'        => 'Mã nhân viên vượt quá 191 ký tự',
                'ma_nhan_vien.unique'     => 'Mã nhân viên đã tồn tại',
                'ho_ten.required'         => 'Chưa nhập họ tên',
                'ho_ten.max'              => 'Họ tên vượt quá 191 kí tự',
                'cmnd.max'                => 'CMND/CCCD vượt quá 191 kí tự',
                'dia_chi.max'             => 'Địa chỉ vượt quá 191 kí tự',
                'email.max'               => 'Email vượt quá 191 kí tự',
                'ma_bhxh.max'             => 'Mã bhxh vượt quá 191 kí tự',
                'cmnd.required'                => 'CMND/CCCD không được để trống ',
                'dia_chi.required'             => 'Địa chỉ không được để trông',
                'email.required'               => 'Email không được để trống',
                'ma_bhxh.required'             => 'Mã bhxh không được để trống',
                'chuc_vu_id.required'     => 'Chưa chọn chức vụ',
                'phong_ban_id.required'   => 'Chưa chọn phòng ban',
            ]
        );
        if ($validator->fails()) {
            return back()->with('error', $validator->messages()->first());
        }
        $user = User::find($id);
        if($user==null)
        {
            return redirect()->route('danh_sach_nhan_vien')->with('error','Không tìm thấy nhân viên này');
        }
        $user->ma_nhan_vien = $request->ma_nhan_vien;
        $user->ho_ten = $request->ho_ten;
        $user->cmnd = $request->cmnd;
        $user->ngay_sinh = $request->ngay_sinh;
        $user->gioi_tinh = $request->gioi_tinh;
        $user->dia_chi = $request->dia_chi;
        $user->so_dien_thoai = $request->so_dien_thoai;
        $user->email = $request->email;
        $user->ma_bhxh = $request->ma_bhxh;
        $user->ngay_cap = $request->ngay_cap;
        $user->ngay_het_han = $request->ngay_het_han;
        $user->chuc_vu_id = $request->chuc_vu_id;
        $user->ngay_nhan_chuc = $request->ngay_nhan_chuc;
        $user->phong_ban_id = $request->phong_ban_id;
        $user->save();

        return redirect()->route('danh_sach_nhan_vien')->with('status','Cập nhật nhân viên thành công');
    }
    public function destroy(Request $request)
    {
        try {
            $user = User::find($request->id);
            $hopDong = HopDong::where('user_id', $user->id)->first();
            $phongBan = PhongBan::where('user_id', $user->id)->first();
            if($hopDong!=null)
            {
                return redirect()->route('danh_sach_nhan_vien')->with('error','Nhân viên đang còn họp đồng');
            }
            else if($phongBan!=null)
            {
                return redirect()->route('danh_sach_nhan_vien')->with('error','Nhân viên đang làm trưởng phòng');
            }
            else
            {
                User::destroy($request->id);
                return redirect()->route('danh_sach_nhan_vien')->with('status','Xoá thành công');
            }
        } catch (Exception $e) {
            return redirect()->route('danh_sach_nhan_vien')->with('error','Xoá không thành công');

        }
    }
    public function search(Request $request)
    {
        if(empty($request->search))
        {
            $request->search = "";
        }
        if(auth()->user()->chucVu->ten_chuc_vu == "Giám đốc")
        {
            $users =User::where('ho_ten','LIKE',"%$request->search%")
            ->orwhere('ho_ten','LIKE',"%$request->search%")
            ->orwhere('dia_chi','LIKE',"%$request->search%")
            ->orwhere('so_dien_thoai','LIKE',"%$request->search%")->get();
        }
        else if(auth()->user()->chucVu->ten_chuc_vu == "Trưởng phòng")
        {
            $phongBan = PhongBan::where('user_id', auth()->user()->id)->first();
            $users = User::where([['ho_ten','LIKE',"%$request->search%"],['phong_ban_id', $phongBan->id]])
            ->orwhere([['ho_ten','LIKE',"%$request->search%"],['phong_ban_id', $phongBan->id]])
            ->orwhere([['dia_chi','LIKE',"%$request->search%"],['phong_ban_id', $phongBan->id]])
            ->orwhere([['so_dien_thoai','LIKE',"%$request->search%"],['phong_ban_id', $phongBan->id]])->get();    
        }
        else{
            $users =User::where('id',auth()->user()->id)->get();
        }
        if($users==null)
        {
            return back()->with('error','Không tìm thấy danh sách nhân viên');
        }
        return view('nhan-vien/danh-sach',compact('users'));
    }
}
