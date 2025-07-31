@extends('layouts.app')
@section('title','Reset Password')
@section('content')
<div class="login-container">
    <div class="login-card">
        <div class="form-header">
            <h1 class="form-title">
                <img src="{{ asset('assets/img/dark.png') }}" height="70" alt="company logo">
            </h1>
            <p class="form-subtitle">{{ __('Enter your email to reset your password') }}</p>
            
            @if (session('status'))
                <div class="alert alert-success" role="alert">
                    {{ session('status') }}
                </div>
            @endif
        </div>

        <form method="POST" action="{{ route('password.email') }}">
            @csrf

            <div class="form-group">
                <label for="email" class="form-label">{{ __('Email Address') }}</label>
                <div class="input-wrapper">
                    <input id="email" 
                           type="email" 
                           class="form-input @error('email') is-invalid @enderror" 
                           name="email" 
                           value="{{ old('email') }}"
                           required 
                           autocomplete="email" 
                           autofocus
                           placeholder="Enter your email address">
                    <svg class="input-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 12a4 4 0 10-8 0 4 4 0 008 0zm0 0v1.5a2.5 2.5 0 005 0V12a9 9 0 10-9 9m4.5-1.206a8.959 8.959 0 01-4.5 1.207"/>
                    </svg>
                </div>
                @error('email')
                    <div class="error-message">
                        <svg width="16" height="16" fill="currentColor" viewBox="0 0 16 16">
                            <path d="M8 15A7 7 0 1 1 8 1a7 7 0 0 1 0 14zm0 1A8 8 0 1 0 8 0a8 8 0 0 0 0 16z"/>
                            <path d="M7.002 11a1 1 0 1 1 2 0 1 1 0 0 1-2 0zM7.1 4.995a.905.905 0 1 1 1.8 0l-.35 3.507a.552.552 0 0 1-1.1 0L7.1 4.995z"/>
                        </svg>
                        <strong>{{ $message }}</strong>
                    </div>
                @enderror
                
            </div>

            <button type="submit" class="login-button">
                {{ __('Send Password Reset Link') }}
            </button>
        </form>

        <div class="divider">
            <span>Remember your password?</span>
        </div>

        <div class="register-section">
            @if (Route::has('login'))
                <a href="{{ route('login') }}" class="register-button">
                    <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z"/>
                    </svg>
                    {{ __('Sign In') }}
                </a>
            @endif
        </div>
    </div>
</div>

@endsection
