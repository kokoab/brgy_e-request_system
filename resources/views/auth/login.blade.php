@extends('layouts.app')

@section('title', 'Login')

@section('content')
<div class="max-w-md mx-auto mt-16 mb-16">
    <div class="bg-white rounded-xl shadow-xl p-10" style="border: 2px solid rgba(74, 124, 126, 0.2);">
        <h2 style="color: #2C3E50;" class="text-4xl font-bold text-center mb-8">Welcome Back</h2>
        
        <form id="loginForm" class="space-y-6">
            <div>
                <label style="color: #2C3E50;" class="block text-sm font-semibold mb-2">Email</label>
                <input type="email" id="email" name="email" required
                       style="border: 2px solid rgba(74, 124, 126, 0.3);" 
                       class="w-full px-4 py-3 rounded-lg focus:outline-none transition-smooth"
                       onfocus="this.style.borderColor='#4A7C7E'" onblur="this.style.borderColor='rgba(74, 124, 126, 0.3)'">
            </div>
            
            <div>
                <label style="color: #2C3E50;" class="block text-sm font-semibold mb-2">Password</label>
                <input type="password" id="password" name="password" required
                       style="border: 2px solid rgba(74, 124, 126, 0.3);" 
                       class="w-full px-4 py-3 rounded-lg focus:outline-none transition-smooth"
                       onfocus="this.style.borderColor='#4A7C7E'" onblur="this.style.borderColor='rgba(74, 124, 126, 0.3)'">
            </div>
            
            <div id="errorMessage" style="color: #D2691E; background-color: rgba(210, 105, 30, 0.1);" class="text-sm hidden p-3 rounded-lg"></div>
            
            <button type="submit" style="background-color: #4A7C7E; color: white;" class="w-full py-3 px-4 rounded-lg font-bold text-lg shadow-md hover:opacity-90 transition-smooth">
                Login
            </button>
        </form>
        
        <p class="mt-6 text-center text-sm" style="color: rgba(44, 62, 80, 0.7);">
            Don't have an account? <a href="/register" style="color: #4A7C7E;" class="font-semibold hover:opacity-80 transition-smooth">Register here</a>
        </p>
    </div>
</div>

<script>
document.getElementById('loginForm').addEventListener('submit', async (e) => {
    e.preventDefault();
    
    const email = document.getElementById('email').value;
    const password = document.getElementById('password').value;
    const errorDiv = document.getElementById('errorMessage');
    
    try {
        const response = await fetch('/api/login', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
            },
            body: JSON.stringify({ email, password })
        });
        
        const data = await response.json();
        
        if (response.ok) {
            localStorage.setItem('auth_token', data.token);
            localStorage.setItem('user', JSON.stringify(data.user));
            window.location.href = '/';
        } else {
            errorDiv.textContent = data.message || 'Login failed';
            errorDiv.classList.remove('hidden');
        }
    } catch (error) {
        errorDiv.textContent = 'An error occurred. Please try again.';
        errorDiv.classList.remove('hidden');
    }
});
</script>
@endsection

