@extends('layouts.dashboard')

@section('title')
    Store Dashboard Settings
@endsection

@section('content')
<div
  class="section-content section-dashboard-home"
  data-aos="fasde-up"
>
  <div class="container-fluid">
    <div class="dashboard-heading">
      <h2 class="dashboard-title">Store Settings</h2>
      <p class="dashboard-subtitle">Make store that profitable</p>
    </div>
    <div class="dashboard-content">
      <div class="row">
        <div class="col-12">
          <form action="{{ route('dashboard-settings-redirect', 'dashboard-settings-store') }}" method="POST" enctype="multipart/form-data">
            @csrf
            <div class="card">
              <div class="card-body">
                <div class="row">
                  <div class="col-md-6">
                    <div class="form-group">
                      <label>Nama Toko</label>
                      <input type="text" class="form-control" name="store_name" value="{{ $user->store_name }}" />
                    </div>
                  </div>
                  <div class="col-md-6">
                    <div class="form-group">
                      <label>Kategori</label>
                      <select name="categories_id" class="form-control" id="">
                        <option value="{{ $user->categories_id }}">Tidak diganti</option>
											@foreach ($categories as $category)
												<option value="{{ $category->id }}">{{ $category->name }}</option>
											@endforeach
										</select>
                    </div>
                  </div>
                  <div class="col-md-6">
                    <div class="form-group">
                      <label>Store status</label>
                      <p class="text-muted">
                        Apakah saat ini toko Anda buka?
                      </p>
                      <div
                        class="custom-control custom-radio custom-control-inline"
                      >
                        <input
                          type="radio"
                          name="store_status"
                          id="openStoreTrue"
                          class="custom-control-input"
                          value="1"
                          {{ $user->store_status == 1 ? 'checked' : '' }}
                        />
                        <label
                          for="openStoreTrue"
                          class="custom-control-label"
                        >
                          Buka
                        </label>
                      </div>
                      <div
                        class="custom-control custom-radio custom-control-inline"
                      >
                        <input
                          type="radio"
                          name="is_store_open"
                          id="openStoreFalse"
                          class="custom-control-input"
                          value="0"
                          {{ $user->store_status == 0 ||$user->store_status == NULL ? 'checked' : '' }}
                        />
                        <label
                          for="openStoreFalse"
                          class="custom-control-label"
                        >
                          Tutup sementara
                        </label>
                      </div>
                    </div>
                  </div>
                </div>
                <div class="row">
                  <div class="col text-right">
                    <button
                      class="btn btn-success px-5"
                      type="submit"
                    >
                      Save Now
                    </button>
                  </div>
                </div>
              </div>
            </div>
          </form>
        </div>
      </div>
    </div>
  </div>
</div>
@endsection