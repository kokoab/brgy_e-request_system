@extends('layouts.app')

@section('title', 'My Properties')

@section('content')
<div class="max-w-7xl mx-auto py-8 px-4">
    <h1 class="text-3xl font-bold mb-6">My Properties</h1>
    
    <div id="propertiesList" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
        <p class="col-span-full text-center text-gray-500">Loading your properties...</p>
    </div>
</div>

<script>
async function loadMyProperties() {
    const token = localStorage.getItem('auth_token');
    if (!token) {
        window.location.href = '/login';
        return;
    }
    
    try {
        const response = await fetch('/api/my-properties', {
            headers: {
                'Authorization': `Bearer ${token}`,
                'Content-Type': 'application/json'
            }
        });
        
        if (response.status === 401) {
            window.location.href = '/login';
            return;
        }
        
        if (!response.ok) {
            const errorData = await response.json().catch(() => ({ message: 'Failed to load properties' }));
            throw new Error(errorData.message || `Server error: ${response.status}`);
        }
        
        const properties = await response.json();
        const container = document.getElementById('propertiesList');
        
        if (!properties || properties.length === 0) {
            container.innerHTML = '<p class="col-span-full text-center text-gray-500">You haven\'t added any properties yet. <a href="/properties/create" class="text-blue-500 hover:underline">Add your first property</a></p>';
            return;
        }
        
        container.innerHTML = properties.map(property => {
            const bookings = property.all_bookings || [];
            const totalBookings = bookings.length;
            const approvedBookings = bookings.filter(b => b.status === 'approved').length;
            
            return `
            <div class="bg-white rounded-xl shadow-lg overflow-hidden border-2" style="border-color: rgba(74, 124, 126, 0.2);">
                <img src="${property.images && property.images.length > 0 ? '/storage/' + property.images[0].image_path : 'https://via.placeholder.com/400x300'}" 
                     alt="${property.title}" class="w-full h-48 object-cover" onerror="this.src='https://via.placeholder.com/400x300'">
                <div class="p-6">
                    <h3 style="color: #2C3E50;" class="text-xl font-bold mb-2">${property.title}</h3>
                    <p style="color: rgba(44, 62, 80, 0.7);" class="text-sm mb-2">${property.city}</p>
                    <p style="color: #D2691E;" class="text-2xl font-bold mb-4">₱${parseFloat(property.price).toLocaleString()}</p>
                    
                    <!-- Booking Statistics -->
                    <div class="mb-4 p-3 rounded-lg" style="background-color: rgba(74, 124, 126, 0.1);">
                        <div class="flex justify-between text-sm">
                            <span style="color: rgba(44, 62, 80, 0.7);">Total Bookings:</span>
                            <strong style="color: #4A7C7E;">${totalBookings}</strong>
                        </div>
                        <div class="flex justify-between text-sm mt-1">
                            <span style="color: rgba(44, 62, 80, 0.7);">Approved:</span>
                            <strong style="color: #4A7C7E;">${approvedBookings}</strong>
                        </div>
                    </div>
                    
                    <!-- Rooms and Occupancy -->
                    ${property.rooms && property.rooms.length > 0 ? `
                    <div class="mb-4">
                        <h4 style="color: #2C3E50;" class="font-semibold mb-2 text-sm">Rooms & Occupancy:</h4>
                        <div class="space-y-2">
                            ${property.rooms.map(room => {
                                const roomBookings = room.all_bookings || [];
                                const approvedRoomBookings = roomBookings.filter(b => b.status === 'approved');
                                const occupancy = room.occupancy_count || 0;
                                const remaining = room.remaining_capacity !== undefined ? room.remaining_capacity : (room.capacity - occupancy);
                                
                                return `
                                <div class="p-2 rounded border" style="border-color: rgba(74, 124, 126, 0.2); background-color: rgba(74, 124, 126, 0.05);">
                                    <div class="flex justify-between items-start mb-1">
                                        <strong style="color: #2C3E50;" class="text-sm">${room.name}</strong>
                                        <span style="color: rgba(44, 62, 80, 0.7);" class="text-xs">${occupancy}/${room.capacity}</span>
                                    </div>
                                    ${approvedRoomBookings.length > 0 ? `
                                    <div class="mt-2 space-y-1">
                                        ${approvedRoomBookings.map(booking => `
                                            <div class="text-xs" style="color: rgba(44, 62, 80, 0.7);">
                                                • ${booking.user ? booking.user.name : booking.name} ${booking.user ? `(${booking.user.email})` : `(${booking.email})`}
                                            </div>
                                        `).join('')}
                                    </div>
                                    ` : '<p class="text-xs" style="color: rgba(44, 62, 80, 0.5);">No occupants</p>'}
                                </div>
                            `;
                            }).join('')}
                        </div>
                    </div>
                    ` : ''}
                    
                    <a href="/properties/${property.id}" style="background-color: #4A7C7E; color: white;" class="w-full px-4 py-2 rounded-lg font-semibold hover:opacity-90 transition-smooth inline-block text-center mt-2">
                        View Details
                    </a>
                </div>
            </div>
        `;
        }).join('');
    } catch (error) {
        console.error('Error loading properties:', error);
        const errorMessage = error.message || 'Error loading properties. Please try again.';
        document.getElementById('propertiesList').innerHTML = 
            `<div class="col-span-full text-center p-4">
                <p style="color: #D2691E;" class="font-semibold mb-2">Error loading properties</p>
                <p style="color: rgba(44, 62, 80, 0.7);" class="text-sm">${errorMessage}</p>
                <button onclick="loadMyProperties()" style="background-color: #4A7C7E; color: white;" class="mt-4 px-4 py-2 rounded-lg font-semibold hover:opacity-90 transition-smooth">
                    Retry
                </button>
            </div>`;
    }
}

document.addEventListener('DOMContentLoaded', loadMyProperties);
</script>
@endsection

