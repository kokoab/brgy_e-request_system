@extends('layouts.app')

@section('title', 'My Favorites')

@section('content')
<div class="max-w-7xl mx-auto py-8 px-4">
    <h1 class="text-3xl font-bold mb-6">My Favorite Properties</h1>
    
    <div id="favoritesList" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
        <p class="col-span-full text-center text-gray-500">Loading your favorites...</p>
    </div>
</div>

<script>
async function loadFavorites() {
    const token = localStorage.getItem('auth_token');
    if (!token) {
        window.location.href = '/login';
        return;
    }
    
    try {
        const response = await fetch('/api/favorites', {
            headers: {
                'Authorization': `Bearer ${token}`,
                'Content-Type': 'application/json'
            }
        });
        
        if (response.status === 401) {
            window.location.href = '/login';
            return;
        }
        
        const favorites = await response.json();
        const container = document.getElementById('favoritesList');
        
        if (!favorites || favorites.length === 0) {
            container.innerHTML = '<p class="col-span-full text-center text-gray-500">You haven\'t favorited any properties yet. <a href="/" class="text-blue-500 hover:underline">Browse properties</a></p>';
            return;
        }
        
        container.innerHTML = favorites.map(fav => {
            const property = fav.property;
            if (!property) return '';
            
            return `
                <div class="bg-white rounded-lg shadow-md overflow-hidden">
                    <img src="${property.images && property.images.length > 0 ? '/storage/' + property.images[0].image_path : 'https://via.placeholder.com/400x300'}" 
                         alt="${property.title}" class="w-full h-48 object-cover" onerror="this.src='https://via.placeholder.com/400x300'">
                    <div class="p-4">
                        <h3 class="text-xl font-bold mb-2">${property.title}</h3>
                        <p class="text-gray-600 text-sm mb-2">${property.city}</p>
                        <p class="text-2xl font-bold text-blue-600 mb-2">₱${parseFloat(property.price).toLocaleString()}</p>
                        <a href="/properties/${property.id}" class="bg-blue-500 hover:bg-blue-700 text-white px-4 py-2 rounded inline-block mt-2">
                            View Details
                        </a>
                    </div>
                </div>
            `;
        }).filter(html => html).join('');
    } catch (error) {
        console.error('Error loading favorites:', error);
        document.getElementById('favoritesList').innerHTML = 
            '<p class="col-span-full text-center text-red-500">Error loading favorites. Please try again.</p>';
    }
}

document.addEventListener('DOMContentLoaded', loadFavorites);
</script>
@endsection

