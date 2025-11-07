@extends('layouts.app')

@section('title', 'My Profile')

@section('page-title', 'My Profile')

@section('breadcrumb')
    <li class="breadcrumb-item text-gray-900">Edit</li>
@endsection

@push('styles')
@endpush

@push('custom-scripts')
@endpush

@section('content')
    <!--begin::Card-->
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Edit Profile</h3>
        </div>
        <div class="card-body">
            <!--begin::Form-->
            <form class="form" id="form" method="POST" action="{{ route('my-profile.update', $user->id) }}" novalidate>
                @csrf
                @method('PUT')

                <x-form.input label="Full Name" name="name" placeholder="Enter full name" :value="$user->name"
                    autocomplete="name" required autofocus />

                <x-form.input label="Email Address" name="email" type="email" placeholder="Enter email address"
                    :value="$user->email" autocomplete="email" required />

                <x-form.password label="Password" name="password" placeholder="Leave blank to keep current password"
                    hint="Leave blank to keep current password. Use 8 or more characters with a mix of letters, numbers & symbols." />

                <x-form.password label="Confirm Password" name="password_confirmation" placeholder="Confirm password"
                    :showMeter="false" hint="" />

                <!--begin::Actions-->
                <div class="d-flex justify-content-end">
                    <button type="submit" id="submit_form" class="btn btn-primary">
                        <span class="indicator-label">Update</span>
                        <span class="indicator-progress">Please wait...
                            <span class="spinner-border spinner-border-sm ms-2 align-middle"></span>
                        </span>
                    </button>
                </div>
                <!--end::Actions-->
            </form>
            <!--end::Form-->
        </div>
    </div>
    <!--end::Card-->
@endsection
