@extends('master')
@section('main-content')

@if(session('status'))
<div class="alert alert-success alert-dismissible" role="alert">
    {{session('status')}}
    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
</div>
@endif
@if(session('error'))
<div class="alert alert-danger alert-dismissible" role="alert">
    {{session('error')}}
    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
</div>
@endif

<div class="row">
    @if(auth()->user()->chucVu->ten_chuc_vu != "Giám đốc")
    <div class="col-12 mb-3">
        <a href="{{route('them_moi_nghi_viec')}}" class="btn btn-primary" >Thêm mới</a>
    </div>
    @endif
    <div class="col-12">
        <div class="card">
            @if(auth()->user()->chucVu->ten_chuc_vu != "Nhân viên")<h5 class="card-header">Danh sách đơn xin nghỉ /<a href="{{route('danh_sach_nghi_viec_cho_duyet')}}">Danh sách đơn xin nghỉ chờ duyệt</a></h5>@endif
            <div class="card-body demo-vertical-spacing demo-only-element">
            <table class="table">
                <thead>
                  <tr>
                    <th scope="col">Tên nhân viên</th>
                    <th scope="col">Ngày nghỉ</th>
                    <th scope="col">Lý do</th>
                    <th scope="col">Đơn xin nghỉ </th>
                    <th scope="col">Trạng thái </th>
                  </tr>
                </thead>
                <tbody>
                @forelse($nghiViecs as $nghiViec)
                <tr>
                    <td>{{ $nghiViec->user->ho_ten}}</td>
                    <td>{{ $nghiViec->ngay_nghi}}</td>
                    <td>{{ $nghiViec->ly_do}}</td>

                    <td><a href="{{url('/Đơn xin nghỉ', $nghiViec->don_nghi_viec)}}" download>Đơn xin nghỉ việc</td>
                    <td>{{ $nghiViec->trang_thai}}</td>
                    <!-- <td>
                        <a href="{{route('xoa_nghi_viec',['id' => $nghiViec->id])}}" class="ms-3"><i class="bx bx-trash"></i></a>
                    </td> -->
                </tr>
                @empty
                <tr>
                    <td colspan="6" style="text-align: center">Không có dữ liệu</td>
                </tr>
                @endforelse
                </tbody>
              </table>
            </div>
        </div>
    </div>
</div>
@endsection