@extends('layouts.app')
@section('title', 'Register')
@section('content')

<div class="login-container">
    <div class="login-card">
        <div class="form-header">
            <h1 class="form-title">
                <img src="{{ asset('assets/img/logo-dark.svg') }}" height="70" alt="company logo">
            </h1>
            <p class="form-subtitle">Join us today! Please fill in your details.</p>
        </div>

        <form method="POST" action="{{ route('register') }}">
            @csrf

            <div class="form-group">
                <label for="name" class="form-label">{{ __('Full Name') }}</label>
                <div class="input-wrapper">
                    <input id="name" 
                           type="text" 
                           class="form-input @error('name') is-invalid @enderror" 
                           name="name" 
                           value="{{ old('name') }}" 
                           required 
                           autocomplete="name" 
                           autofocus
                           placeholder="Enter your full name">
                    <svg class="input-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                    </svg>
                </div>
                @error('name')
                    <div class="error-message">
                        <svg width="16" height="16" fill="currentColor" viewBox="0 0 16 16">
                            <path d="M8 15A7 7 0 1 1 8 1a7 7 0 0 1 0 14zm0 1A8 8 0 1 0 8 0a8 8 0 0 0 0 16z"/>
                            <path d="M7.002 11a1 1 0 1 1 2 0 1 1 0 0 1-2 0zM7.1 4.995a.905.905 0 1 1 1.8 0l-.35 3.507a.552.552 0 0 1-1.1 0L7.1 4.995z"/>
                        </svg>
                        <strong>{{ $message }}</strong>
                    </div>
                @enderror
            </div>

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

            <div class="form-group">
                <label for="password" class="form-label">{{ __('Password') }}</label>
                <div class="input-wrapper">
                    <input id="password" 
                           type="password" 
                           class="form-input @error('password') is-invalid @enderror" 
                           name="password" 
                           required 
                           autocomplete="new-password"
                           placeholder="Create a strong password">
                    <svg class="input-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
                    </svg>
                </div>
                @error('password')
                    <div class="error-message">
                        <svg width="16" height="16" fill="currentColor" viewBox="0 0 16 16">
                            <path d="M8 15A7 7 0 1 1 8 1a7 7 0 0 1 0 14zm0 1A8 8 0 1 0 8 0a8 8 0 0 0 0 16z"/>
                            <path d="M7.002 11a1 1 0 1 1 2 0 1 1 0 0 1-2 0zM7.1 4.995a.905.905 0 1 1 1.8 0l-.35 3.507a.552.552 0 0 1-1.1 0L7.1 4.995z"/>
                        </svg>
                        <strong>{{ $message }}</strong>
                    </div>
                @enderror
            </div>

            <div class="form-group">
                <label for="password-confirm" class="form-label">{{ __('Confirm Password') }}</label>
                <div class="input-wrapper">
                    <input id="password-confirm" 
                           type="password" 
                           class="form-input" 
                           name="password_confirmation" 
                           required 
                           autocomplete="new-password"
                           placeholder="Confirm your password">
                    <svg class="input-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                </div>
            </div>

            <div class="checkbox-group">
                <div class="checkbox-wrapper">
                    <div class="custom-checkbox">
                        <input type="checkbox" name="terms" id="terms" required>
                        <span class="checkbox-mark"></span>
                    </div>
                    <label class="checkbox-label" for="terms">
                        I agree to the <a href="#" class="forgot-link">Terms of Service</a> and <a href="#" class="forgot-link">Privacy Policy</a>
                    </label>
                </div>
            </div>

            <button type="submit" class="login-button">
                {{ __('Create Account') }}
            </button>
        </form>

        <div class="divider">
            <span>Already have an account?</span>
        </div>

        <div class="register-section">
            <p class="register-text">Ready to sign in?</p>
            @if (Route::has('login'))
                <a href="{{ route('login') }}" class="register-button">
                    <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 16l-4-4m0 0l4-4m-4 4h14m-5 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h7a3 3 0 013 3v1"/>
                    </svg>
                    {{ __('Sign In') }}
                </a>
            @endif
        </div>
    </div>
</div>
@endsection