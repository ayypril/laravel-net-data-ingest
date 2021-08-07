@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-3">
            <div class="card">
                <div class="card-header">{{ __('Login') }}</div>

                <div class="card-body">
                    <div class="social-auth-links text-center mb-4">
                        <a href="/login/discord" class="btn btn-block btn-primary" style="background-color: #7289DA; border-color: #7289DA;">
                            <i class="fab fa-discord mr-2"></i> Sign in with Discord
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
