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
    <div class="col-12 mb-3">
        <!-- <a href="{{route('them_moi_bang_luong')}}" class="btn btn-primary" >Thêm mới</a> -->
    </div>
    <div class="col-12">
        <div class="card">
            <h5 class="card-header">Danh sách lương</h5>
            <div class="card-body demo-vertical-spacing demo-only-element">
            <table class="table">
                <thead>
                  <tr>
                    <th scope="col">Tên nhân viên</th>
                    <th scope="col">Tháng</th>
                    <th scope="col">Khen thưởng</th>
                    <th scope="col">Kỷ luật</th>
                    <th scope="col">Tạm ứng </th>
                    <th scope="col">Phụ cấp</th>
                    <th scope="col">Lương nhận thực</th>

                  </tr>
                </thead>
                <tbody>
                @forelse($luongs as $luong)
                <tr>
                    <td>{{ $luong->user->ho_ten}}</td>
                    <td>{{ $luong->thang_nam}}</td>

                    @if($luong->khen_thuong == null)
                    <td>0</td>
                    @else
                    <td>{{ $luong->khen_thuong}}</td>
                    @endif

                    @if($luong->ky_luat == null)
                    <td>0</td>
                    @else
                    <td>{{ $luong->ky_luat}}</td>
                    @endif
                    <td>{{ $luong->tam_ung}}</td>

                    <td>{{ $luong->phu_cap}}</td>

                    <td>{{ $luong->tong_luong}}</td>

                    <td>
                        <a href="{{route('cap_nhat_bang_luong',['id' => $luong->id])}}" ><i class="bx bx-message-square-add"></i></a>
                        <a href="{{route('xoa_bang_luong',['id' => $luong->id])}}" class="ms-3"><i class="bx bx-trash"></i></a>
                    </td>
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