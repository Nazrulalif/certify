@extends('layouts.app')

@section('title', 'Users Management')

@section('page-title', 'Users Management')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('users.index') }}">Users</a></li>
    <li class="breadcrumb-item text-gray-900">Create</li>
@endsection

@push('styles')
@endpush

@push('scripts')
@endpush

@section('content')
    <!--begin::Card-->
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Create New User</h3>
        </div>
        <div class="card-body">
            <!--begin::Form-->
            <form class="form" id="form" method="POST" action="{{ route('users.store') }}" novalidate>
                @csrf

                <x-form.input label="Full Name" name="name" placeholder="Enter full name" autocomplete="name" required
                    autofocus />

                <x-form.input label="Email Address" name="email" type="email" placeholder="Enter email address"
                    autocomplete="email" required />

                <x-form.password label="Password" name="password" placeholder="Enter password" required />

                <x-form.password label="Confirm Password" name="password_confirmation" placeholder="Confirm password"
                    :showMeter="false" hint="" required />

                <x-form.select label="Role" name="role" placeholder="Select Role" :options="[
                    App\Models\User::ROLE_ROOT => 'Root',
                    App\Models\User::ROLE_USER => 'User',
                ]" required />

                <x-form.toggle label="Status" name="status" id="status" switchLabel="Active" :checked="true"
                    hint="Toggle to set user as active or inactive" />

                <!--begin::Actions-->
                <div class="d-flex justify-content-end">
                    <a href="{{ route('users.index') }}" class="btn btn-light me-3">Cancel</a>
                    <button type="submit" id="submit_form" class="btn btn-primary">
                        <span class="indicator-label">Create User</span>
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
