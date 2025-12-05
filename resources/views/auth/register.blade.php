@extends('layouts.app')

@section('title', 'Register')

@section('content')
<div class="max-w-md mx-auto mt-16 mb-16">
    <div class="bg-white rounded-xl shadow-xl p-10" style="border: 2px solid rgba(74, 124, 126, 0.2);">
        <h2 style="color: #2C3E50;" class="text-4xl font-bold text-center mb-8">Create Account</h2>
        
        <form id="registerForm" class="space-y-6">
            <div>
                <label style="color: #2C3E50;" class="block text-sm font-semibold mb-2">Name</label>
                <input type="text" id="name" name="name" required
                       style="border: 2px solid rgba(74, 124, 126, 0.3);" 
                       class="w-full px-4 py-3 rounded-lg focus:outline-none transition-smooth"
                       onfocus="this.style.borderColor='#4A7C7E'" onblur="this.style.borderColor='rgba(74, 124, 126, 0.3)'">
            </div>
            
            <div>
                <label style="color: #2C3E50;" class="block text-sm font-semibold mb-2">Email</label>
                <input type="email" id="email" name="email" required
                       style="border: 2px solid rgba(74, 124, 126, 0.3);" 
                       class="w-full px-4 py-3 rounded-lg focus:outline-none transition-smooth"
                       onfocus="this.style.borderColor='#4A7C7E'" onblur="this.style.borderColor='rgba(74, 124, 126, 0.3)'">
            </div>
            
            <div>
                <label style="color: #2C3E50;" class="block text-sm font-semibold mb-2">Phone (Optional)</label>
                <input type="text" id="phone" name="phone"
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
            
            <div>
                <label style="color: #2C3E50;" class="block text-sm font-semibold mb-2">Confirm Password</label>
                <input type="password" id="password_confirmation" name="password_confirmation" required
                       style="border: 2px solid rgba(74, 124, 126, 0.3);" 
                       class="w-full px-4 py-3 rounded-lg focus:outline-none transition-smooth"
                       onfocus="this.style.borderColor='#4A7C7E'" onblur="this.style.borderColor='rgba(74, 124, 126, 0.3)'">
            </div>
            
            <div id="errorMessage" style="color: #D2691E; background-color: rgba(210, 105, 30, 0.1);" class="text-sm hidden p-3 rounded-lg"></div>
            
            <button type="submit" style="background-color: #4A7C7E; color: white;" class="w-full py-3 px-4 rounded-lg font-bold text-lg shadow-md hover:opacity-90 transition-smooth">
                Register
            </button>
        </form>
        
        <p class="mt-6 text-center text-sm" style="color: rgba(44, 62, 80, 0.7);">
            Already have an account? <a href="/login" style="color: #4A7C7E;" class="font-semibold hover:opacity-80 transition-smooth">Login here</a>
        </p>
    </div>
</div>

<script>
document.getElementById('registerForm').addEventListener('submit', async (e) => {
    e.preventDefault();
    
    const formData = {
        name: document.getElementById('name').value,
        email: document.getElementById('email').value,
        phone: document.getElementById('phone').value,
        password: document.getElementById('password').value,
        password_confirmation: document.getElementById('password_confirmation').value
    };
    
    const errorDiv = document.getElementById('errorMessage');
    
    if (formData.password !== formData.password_confirmation) {
        errorDiv.textContent = 'Passwords do not match';
        errorDiv.classList.remove('hidden');
        return;
    }
    
    try {
        const response = await fetch('/api/register', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
            },
            body: JSON.stringify(formData)
        });
        
        // Try to parse as JSON
        let data;
        try {
            const text = await response.text();
            // Check if it's HTML (starts with <)
            if (text.trim().startsWith('<')) {
                console.error('HTML response received:', text.substring(0, 200));
                throw new Error('Server returned an error page. Please check your input and try again.');
            }
            data = JSON.parse(text);
        } catch (parseError) {
            console.error('Failed to parse response:', parseError);
            errorDiv.textContent = 'Server returned an invalid response. Please try again.';
            errorDiv.classList.remove('hidden');
            return;
        }
        
        if (response.ok) {
            localStorage.setItem('auth_token', data.token);
            localStorage.setItem('user', JSON.stringify(data.user));
            window.location.href = '/';
        } else {
            const errors = data.errors || {};
            const errorMessages = Object.values(errors).flat();
            const errorMsg = errorMessages.length > 0 
                ? errorMessages.join(', ') 
                : (data.message || 'Registration failed');
            errorDiv.textContent = errorMsg;
            errorDiv.classList.remove('hidden');
            console.error('Registration error:', data);
        }
    } catch (error) {
        console.error('Registration exception:', error);
        if (error.message && !error.message.includes('JSON')) {
            errorDiv.textContent = error.message;
        } else {
            errorDiv.textContent = 'An error occurred. Please check your input and try again.';
        }
        errorDiv.classList.remove('hidden');
    }
});
</script>
@endsection

